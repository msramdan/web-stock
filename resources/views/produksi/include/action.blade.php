<td>
    @can('produksi view')
        <a href="{{ route('produksi.show', $model->id) }}" class="btn btn-outline-success btn-sm">
            <i class="fa fa-eye"></i>
        </a>
    @endcan

    @can('produksi edit')
        <a href="{{ route('produksi.edit', $model->id) }}" class="btn btn-outline-primary btn-sm">
            <i class="fa fa-pencil-alt"></i>
        </a>
    @endcan

    @can('produksi delete')
        <form action="{{ route('produksi.destroy', $model->id) }}" method="post" class="d-inline"
            onsubmit="return confirm('Apakah Anda yakin ingin menghapus data produksi ini? Tindakan ini akan mengembalikan stok bahan baku dan mengurangi stok produk jadi.')">
            @csrf
            @method('delete')
            <button class="btn btn-outline-danger btn-sm">
                <i class="ace-icon fa fa-trash-alt"></i>
            </button>
        </form>
    @endcan
</td>
