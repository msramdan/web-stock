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

class TransaksiStockOutController extends Controller implements HasMiddleware
{
    public function __construct(public ImageService $imageService, public string $attachmentPath = '')
    {
        $this->attachmentPath = storage_path('app/public/uploads/attachments/');
    }

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('permission:transaksi stock out view', only: ['index', 'show']),
            new Middleware('permission:transaksi stock out create', only: ['create', 'store']),
            new Middleware('permission:transaksi stock out edit', only: ['edit', 'update']),
            new Middleware('permission:transaksi stock out delete', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            $transaksi = DB::table('transaksi')
                ->select('transaksi.*', 'users.name as user_name')
                ->join('users', 'users.id', '=', 'transaksi.user_id')
                ->where('transaksi.type', 'Out')
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

        return view('transaksi-stock-out.index');
    }

    public function create(): View
    {
        return view('transaksi-stock-out.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransaksiRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $validated['user_id'] = auth()->id();

        $validated['attachment'] = $this->imageService->upload(name: 'attachment', path: $this->attachmentPath);

        Transaksi::create($validated);

        return to_route('transaksi-stock-out.index')->with('success', __('The transaksi was created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaksi $transaksi): View
    {
        $transaksi->load(['user:id,name',]);

        return view('transaksi-stock-out.show', compact('transaksi'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaksi $transaksi): View
    {
        $transaksi->load(['user:id,name',]);

        return view('transaksi-stock-out.edit', compact('transaksi'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTransaksiRequest $request, Transaksi $transaksi): RedirectResponse
    {
        $validated = $request->validated();

        $validated['attachment'] = $this->imageService->upload(name: 'attachment', path: $this->attachmentPath, defaultImage: $transaksi?->attachment);

        $transaksi->update($validated);

        return to_route('transaksi-stock-out.index')->with('success', __('The transaksi was updated successfully.'));
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

            return to_route('transaksi-stock-out.index')->with('success', __('The transaksi was deleted successfully.'));
        } catch (\Exception $e) {
            return to_route('transaksi-stock-out.index')->with('error', __("The transaksi can't be deleted because it's related to another table."));
        }
    }
}
