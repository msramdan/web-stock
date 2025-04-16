<td>
    @can('transaksi stock out view')
        <a href="{{ route('transaksi-stock-out.show', $model->id) }}" class="btn btn-outline-success btn-sm">
            <i class="fa fa-eye"></i>
        </a>
        {{-- Tombol Export PDF per Item (Baru) --}}
        <a href="{{ route('transaksi-stock-out.exportItemPdf', $model->id) }}" class="btn btn-outline-danger btn-sm"
            target="_blank" title="Export PDF">
            <i class="fa fa-file-pdf"></i>
        </a>
    @endcan

    {{-- @can('transaksi stock out edit')
        <a href="{{ route('transaksi.edit', $model->id) }}" class="btn btn-outline-primary btn-sm">
            <i class="fa fa-pencil-alt"></i>
        </a>
    @endcan --}}

    @can('transaksi stock out delete')
        <form action="{{ route('transaksi-stock-out.destroy', $model->id) }}" method="post" class="d-inline"
            onsubmit="return confirm('Are you sure to delete this record?')">
            @csrf
            @method('delete')

            <button class="btn btn-outline-danger btn-sm">
                <i class="ace-icon fa fa-trash-alt"></i>
            </button>
        </form>
    @endcan
</td>
