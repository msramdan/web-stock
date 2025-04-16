<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Http\Requests\Transaksis\{StoreTransaksiRequest, UpdateTransaksiRequest};
use Illuminate\Contracts\View\View;
use Yajra\DataTables\Facades\DataTables;
use App\Generators\Services\ImageService;
use Illuminate\Http\{JsonResponse, RedirectResponse};
use Illuminate\Routing\Controllers\{HasMiddleware, Middleware};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TransaksiStockInController extends Controller implements HasMiddleware
{
    public function __construct(public ImageService $imageService, public string $attachmentPath = '')
    {
        $this->attachmentPath = storage_path('app/public/uploads/attachments/');
    }

    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('permission:transaksi stock in view', only: ['index', 'show']),
            new Middleware('permission:transaksi stock in create', only: ['create', 'store']),
            new Middleware('permission:transaksi stock in edit', only: ['edit', 'update']),
            new Middleware('permission:transaksi stock in delete', only: ['destroy']),
        ];
    }

    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            $transaksi = DB::table('transaksi')
                ->select('transaksi.*', 'users.name as user_name')
                ->join('users', 'users.id', '=', 'transaksi.user_id')
                ->where('transaksi.type', 'In')
                ->orderByDesc('transaksi.tanggal');

            return DataTables::of($transaksi)
                ->addColumn('keterangan', function ($row) {
                    return str($row->keterangan)->limit(100);
                })
                ->addColumn('user', function ($row) {
                    return $row->user_name ?? '-';
                })
                ->addColumn('attachment', function ($row) {
                    if (!$row->attachment) {
                        return '<span class="text-muted">-</span>';
                    }

                    $url = asset('storage/uploads/attachments/' . $row->attachment);

                    return '<a href="' . $url . '" target="_blank" class="btn btn-sm btn-primary">
                                <i class="bi bi-download"></i>
                            </a>';
                })
                ->addColumn('action', 'transaksi-stock-in.include.action')
                ->rawColumns(['attachment', 'action'])
                ->toJson();
        }

        return view('transaksi-stock-in.index');
    }

    public function create(): View
    {
        return view('transaksi-stock-in.create');
    }

    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'no_surat' => 'required|string|max:255|unique:transaksi,no_surat',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
            'cart_items' => 'required|json',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Begin transaction
        DB::beginTransaction();

        try {
            // Handle file upload
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('attachments', 'public');
            }

            // Create transaction using Query Builder
            $transaksiId = DB::table('transaksi')->insertGetId([
                'no_surat' => $request->no_surat,
                'tanggal' => $request->tanggal,
                'type' => 'In',
                'keterangan' => $request->keterangan,
                'attachment' => $attachmentPath,
                'user_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Process cart items
            $cartItems = json_decode($request->cart_items, true);
            $transaksiDetails = [];
            $stockUpdates = [];

            foreach ($cartItems as $item) {
                // Validate item data
                if (!isset($item['id']) || !isset($item['qty']) || $item['qty'] < 1) {
                    throw new \Exception('Invalid cart item data.');
                }

                // Check item existence
                $barang = DB::table('barang')
                    ->where('id', $item['id'])
                    ->first();

                if (!$barang) {
                    throw new \Exception('Barang tidak ditemukan.');
                }

                // Prepare transaction details
                $transaksiDetails[] = [
                    'barang_id' => $item['id'],
                    'qty' => $item['qty'],
                    'transaksi_id' => $transaksiId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Prepare stock updates (using stock_barang field)
                $stockUpdates[$item['id']] = [
                    'stock_barang' => DB::raw('stock_barang + ' . $item['qty']), // Changed to stock_barang
                    'updated_at' => now(),
                ];
            }

            // Bulk insert transaction details
            DB::table('transaksi_detail')->insert($transaksiDetails);

            // Bulk update stock (increment stock_barang)
            foreach ($stockUpdates as $id => $update) {
                DB::table('barang')
                    ->where('id', $id)
                    ->update($update);
            }

            DB::commit();

            return redirect()->route('transaksi-stock-in.index')
                ->with('success', 'Transaksi stock in berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal membuat transaksi stock in: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Transaksi $transaksi): View
    {
        $transaksi->load(['user:id,name',]);

        return view('transaksi-stock-in.show', compact('transaksi'));
    }


    public function edit(Transaksi $transaksi): View
    {
        $transaksi->load(['user:id,name',]);

        return view('transaksi-stock-in.edit', compact('transaksi'));
    }

    public function update(UpdateTransaksiRequest $request, Transaksi $transaksi): RedirectResponse
    {

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaksi $transaksi): RedirectResponse
    {
        try {
            $attachment = $transaksi->attachment;

            $transaksi->delete();

            $this->imageService->delete(image: $this->attachmentPath . $attachment);

            return to_route('transaksi-stock-in.index')->with('success', __('The transaksi was deleted successfully.'));
        } catch (\Exception $e) {
            return to_route('transaksi-stock-in.index')->with('error', __("The transaksi can't be deleted because it's related to another table."));
        }
    }
}
