<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Http\Requests\Companies\{StoreCompanyRequest, UpdateCompanyRequest};
use Illuminate\Contracts\View\View;
use Yajra\DataTables\Facades\DataTables;
use App\Generators\Services\ImageService;
use Illuminate\Http\{JsonResponse, RedirectResponse};
use Illuminate\Routing\Controllers\{HasMiddleware, Middleware};
use Illuminate\Http\Request;

class CompanyController extends Controller implements HasMiddleware
{
    public function __construct(public ImageService $imageService, public string $logoPerusahaanPath = '')
    {
        $this->logoPerusahaanPath = storage_path('app/public/uploads/logo-perusahaans/');
    }

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('permission:company view', only: ['index', 'show']),
            new Middleware('permission:company create', only: ['create', 'store']),
            new Middleware('permission:company edit', only: ['edit', 'update']),
            new Middleware('permission:company delete', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            $companies = Company::query();

            return Datatables::of($companies)
                ->addColumn('alamat', function ($row) {
                    return str($row->alamat)->limit(100);
                })
                ->addColumn('logo_perusahaan', function ($row) {
                    if (!$row->logo_perusahaan) {
                        return 'https://dummyimage.com/150x100/cccccc/000000&text=No+Image';
                    }
                    return asset('storage/uploads/photo-perusahaans/' . $row->logo_perusahaan);
                })
                ->addColumn('action', 'company.include.action')
                ->toJson();
        }

        return view('company.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('company.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $validated['logo_perusahaan'] = $this->imageService->upload(name: 'logo_perusahaan', path: $this->logoPerusahaanPath);

        Company::create($validated);

        return to_route('company.index')->with('success', __('The company was created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company): View
    {
        return view('company.show', compact('company'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company): View
    {
        return view('company.edit', compact('company'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        $validated = $request->validated();

        $validated['logo_perusahaan'] = $this->imageService->upload(name: 'logo_perusahaan', path: $this->logoPerusahaanPath, defaultImage: $company?->logo_perusahaan);

        $company->update($validated);

        return to_route('company.index')->with('success', __('The company was updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company): RedirectResponse
    {
        try {
            $logoPerusahaan = $company->logo_perusahaan;

            $company->delete();

            $this->imageService->delete(image: $this->logoPerusahaanPath . $logoPerusahaan);

            return to_route('company.index')->with('success', __('The company was deleted successfully.'));
        } catch (\Exception $e) {
            return to_route('company.index')->with('error', __("The company can't be deleted because it's related to another table."));
        }
    }

    public function updateSession(Request $request)
    {
        $value = $request->input('selectedValue');
        if (!is_numeric($value)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid company ID'
            ]);
        }

        session()->forget('sessionCompany');
        session(['sessionCompany' => $value]);
        return response()->json([
            'success' => true
        ]);
    }
}
