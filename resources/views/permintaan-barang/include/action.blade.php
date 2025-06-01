<div class="btn-group btn-group-sm" role="group" aria-label="Aksi Permintaan Barang">
    @can('permintaan barang view')
        <a href="{{ route('permintaan-barang.show', $model->id) }}" class="btn btn-sm btn-info"
            title="{{ __('Lihat Detail') }}">
            <i class="fas fa-eye"></i>
        </a>
    @endcan
    @can('permintaan barang export pdf')
        <a href="{{ route('permintaan-barang.printSpecific', $model->id) }}" class="btn btn-sm btn-light"
            title="{{ __('Cetak PDF') }}" target="_blank">
            <i class="fas fa-file-pdf"></i>
        </a>
    @endcan
    {{-- TOMBOL EXPORT EXCEL PER ITEM BARU --}}
    @can('permintaan barang export excel')
        <a href="{{ route('permintaan-barang.exportItemExcel', $model->id) }}" class="btn btn-sm btn-light"
            title="{{ __('Export Excel Item Ini') }}" target="_blank">
            <i class="fas fa-file-excel"></i>
        </a>
    @endcan
    @can('permintaan barang edit')
        <a href="{{ route('permintaan-barang.edit', $model->id) }}" class="btn btn-sm btn-warning"
            title="{{ __('Edit') }}">
            <i class="fas fa-edit"></i>
        </a>
    @endcan
    @can('permintaan barang delete')
        <form action="{{ route('permintaan-barang.destroy', $model->id) }}" method="POST" style="display: inline;"
            class="form-delete-permintaan">
            @csrf
            @method('DELETE')
            <button type="button" class="btn btn-sm btn-danger delete-permintaan" title="{{ __('Hapus') }}">
                <i class="fas fa-trash"></i>
            </button>
        </form>
    @endcan
</div>
