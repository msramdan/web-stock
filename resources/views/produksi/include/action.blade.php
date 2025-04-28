<td>
    @can('produksi view')
        <a href="{{ route('produksi.show', $model->id) }}" class="btn btn-outline-success btn-sm">
            <i class="fa fa-eye"></i>
        </a>
    @endcan

    @can('produksi edit')
        {{-- Edit mungkin perlu logika khusus tergantung status --}}
        {{-- Contoh: hanya bisa edit jika status 'Draft' --}}
        {{-- @if ($model->status == 'Draft') --}}
        <a href="{{ route('produksi.edit', $model->id) }}" class="btn btn-outline-primary btn-sm">
            <i class="fa fa-pencil-alt"></i>
        </a>
        {{-- @endif --}}
    @endcan

    @can('produksi delete')
        {{-- Delete mungkin perlu logika khusus tergantung status --}}
        {{-- Contoh: hanya bisa delete jika status 'Draft' atau 'Cancelled' --}}
        {{-- @if (in_array($model->status, ['Draft', 'Cancelled'])) --}}
        <form action="{{ route('produksi.destroy', $model->id) }}" method="post" class="d-inline"
            onsubmit="return confirm('Apakah Anda yakin ingin menghapus data produksi ini? Tindakan ini mungkin perlu penyesuaian stok manual.')">
            @csrf
            @method('delete')

            <button class="btn btn-outline-danger btn-sm">
                <i class="ace-icon fa fa-trash-alt"></i>
            </button>
        </form>
        {{-- @endif --}}
    @endcan
</td>
