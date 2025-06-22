<section class="content">
    <div class="container-fluid">
        <div class="row">
            {{-- Kolom Informasi User, No Surat, Tanggal (Tidak Diubah) --}}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <table>
                            <tr>
                                <td style="vertical-align: top; width:30%;">
                                    <label for="user">User</label>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <input type="text" id="user" name="user"
                                            value="{{ Auth::user()->name }}" class="form-control" readonly>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top;">
                                    <label for="no_surat">No Surat</label>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <input type="text" name="no_surat" id="no_surat" class="form-control"
                                            value="{{ old('no_surat') }}" required />
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top;">
                                    <label for="tanggal">Tanggal</label>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <input type="datetime-local" name="tanggal" id="tanggal" class="form-control"
                                            value="{{ old('tanggal', now()->format('Y-m-d\TH:i')) }}"
                                            placeholder="{{ __('Tanggal') }}" required />
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Kolom Attachment & Keterangan (Tidak Diubah) --}}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <table>
                            <tr>
                                <td style="vertical-align: top; width:30%;">
                                    <label for="attachment">Attachment</label>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <input type="file" name="attachment" class="form-control" id="attachment">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top; width:30%;">
                                    <label for="keterangan">Keterangan</label>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <textarea name="keterangan" id="keterangan" class="form-control" rows="3">{{ old('keterangan') }}</textarea>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Kolom Input Barang & Qty (Tidak Diubah) --}}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <table>
                            <tr>
                                <td style="vertical-align: top; width:30%;">
                                    <label for="kode_barang">Kode Barang</label>
                                </td>
                                <td>
                                    <div class="form-group input-group">
                                        <input type="hidden" id="barang_id">
                                        <input type="hidden" id="nama_barang_hidden">
                                        <input type="hidden" id="stock">
                                        <input type="hidden" id="jenis_material" readonly>
                                        <input type="hidden" id="unit_satuan" readonly>
                                        <input type="text" name="kode_barang_display" id="kode_barang"
                                            class="form-control" readonly placeholder="Pilih Barang...">
                                        <span class="input-group-text btn btn-success" id="cari_barang"
                                            data-bs-toggle="modal" data-bs-target="#modal-item"
                                            style="cursor: pointer;">
                                            <i class="fa fa-search"></i>
                                        </span>
                                    </div>
                                    <div class="mt-1">
                                        <small>Nama Barang: <strong id="nama_barang_display">-</strong></small>
                                        <br>
                                        <small>Stok Tersedia: <strong id="stock_display">-</strong></small>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top; width:30%;">
                                    <label for="qty">Qty</label>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <input type="number" id="qty" value="1" min="0"
                                            step="any" class="form-control"
                                            placeholder="Gunakan titik (.) untuk desimal">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>
                                    <div>
                                        <button type="button" id="add_cart" class="btn btn-primary">
                                            <i class="fa fa-cart-plus"></i> Add
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel Keranjang --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Keranjang Stock In</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        {{-- Header Tabel yang Diperbarui --}}
                                        <th style="width: 5%;" class="text-center">#</th>
                                        <th style="width: 30%;">Nama Barang</th>
                                        <th style="width: 20%;">Qty</th>
                                        <th style="width: 20%;">Harga Satuan</th>
                                        <th style="width: 20%;" class="text-end">Subtotal</th>
                                        <th style="width: 5%;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="cart_tabel">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Keranjang kosong</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-end">Total Keseluruhan</th>
                                        <th class="text-end" id="total_keseluruhan">Rp 0</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Hidden input untuk submit data --}}
<input type="hidden" name="cart_items" id="cart_items_json">

{{-- Modal Pencarian Barang (Tidak Diubah) --}}
<div class="modal fade" id="modal-item">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Pilih Barang</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body table-responsive">
                <table class="table table-bordered table-striped" id="modal_table_barang" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Jenis Material</th>
                            <th>Unit Satuan</th>
                            <th class="text-end">Stock</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tblItem">
                        {{-- Isi di-render oleh DataTable --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('mazer/extensions/sweetalert2/sweetalert2.min.css') }}">
    <style>
        #modal_table_barang th,
        #modal_table_barang td {
            white-space: nowrap;
        }

        #cari_barang {
            height: calc(1.5em + .75rem + 2px);
        }

        .text-end {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="{{ asset('mazer/extensions/sweetalert2/sweetalert2.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            var modalTable;
            let cart = [];

            // Helper Functions
            const formatRupiah = (number) => new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            }).format(number);
            const formatNumber = (num, decimals = 4) => !isNaN(parseFloat(num)) ? parseFloat(num).toLocaleString(
                'id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: decimals
                }) : '0';

            // --- Inisialisasi DataTable & Event Modal (Tidak Diubah) ---
            $('#modal-item').on('shown.bs.modal', function() {
                if (!$.fn.dataTable.isDataTable('#modal_table_barang')) {
                    modalTable = $('#modal_table_barang').DataTable({
                        processing: true,
                        ajax: {
                            url: "{{ route('listDataBarang') }}",
                            dataSrc: ""
                        }, // Pastikan route ini ada
                        columns: [{
                                data: 'kode_barang'
                            }, {
                                data: 'nama_barang'
                            },
                            {
                                data: 'jenis_material'
                            }, {
                                data: 'unit_satuan'
                            },
                            {
                                data: 'stock',
                                render: data => formatNumber(data, 4)
                            },
                            {
                                data: null,
                                orderable: false,
                                searchable: false,
                                render: function(data, type, row) {
                                    return `<button type="button" class="btn btn-sm btn-primary pilih-barang"
                                    data-id="${row.id}" data-kode="${row.kode_barang}"
                                    data-nama-barang="${row.nama_barang}" data-stock="${row.stock}"
                                    data-jenis-material="${row.jenis_material}" data-unit-satuan="${row.unit_satuan}">Pilih</button>`;
                                }
                            }
                        ],
                        columnDefs: [{
                            className: "text-end",
                            targets: 4
                        }],
                        language: {
                            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                        },
                    });
                } else {
                    modalTable.ajax.reload();
                }
            });

            $('#modal_table_barang').on('click', '.pilih-barang', function() {
                $('#barang_id').val($(this).data('id'));
                $('#kode_barang').val($(this).data('kode'));
                $('#nama_barang_hidden').val($(this).data('nama-barang'));
                $('#nama_barang_display').text($(this).data('nama-barang'));
                $('#stock').val($(this).data('stock'));
                $('#stock_display').text(formatNumber($(this).data('stock'), 4));
                $('#jenis_material').val($(this).data('jenis-material'));
                $('#unit_satuan').val($(this).data('unit-satuan'));
                var modal = bootstrap.Modal.getInstance(document.getElementById('modal-item'));
                modal.hide();
                $('#qty').focus().select();
            });

            // --- Logika Keranjang ---

            const renderCartTable = () => {
                const $tableBody = $('#cart_tabel');
                $tableBody.empty();
                if (cart.length === 0) {
                    $tableBody.html(
                        '<tr><td colspan="6" class="text-center text-muted">Keranjang kosong</td></tr>');
                } else {
                    cart.forEach((item, index) => {
                        const rowHtml = `
                            <tr data-id="${item.id}">
                                <td class="text-center">${index + 1}</td>
                                <td>
                                    <div>${item.nama_barang}</div>
                                    <small class="text-muted">${item.kode}</small>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control cart-qty-input" value="${item.qty}" min="0.01" step="any">
                                        <span class="input-group-text">${item.unit_satuan}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control cart-harga-input" value="${item.harga_satuan}" min="0" step="any">
                                    </div>
                                </td>
                                <td class="text-end subtotal-display"></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm remove-item" title="Hapus Item"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>`;
                        $tableBody.append(rowHtml);
                    });
                }
                updateTotals();
            };

            const updateTotals = () => {
                let totalKeseluruhan = 0;
                cart.forEach(item => {
                    const subtotal = (parseFloat(item.qty) || 0) * (parseFloat(item.harga_satuan) || 0);
                    totalKeseluruhan += subtotal;
                    $(`tr[data-id="${item.id}"]`).find('.subtotal-display').text(formatRupiah(
                        subtotal));
                });
                $('#total_keseluruhan').text(formatRupiah(totalKeseluruhan));
                $('#cart_items_json').val(JSON.stringify(cart));
            };

            $('#add_cart').on('click', function() {
                const id = $('#barang_id').val();
                if (!id) {
                    alert('Silakan pilih barang.');
                    return;
                }
                const qty = parseFloat($('#qty').val());
                if (isNaN(qty) || qty <= 0) {
                    alert('Qty harus valid.');
                    return;
                }

                const index = cart.findIndex(item => item.id == id);
                if (index !== -1) {
                    cart[index].qty += qty;
                } else {
                    cart.push({
                        id: id,
                        kode: $('#kode_barang').val(),
                        nama_barang: $('#nama_barang_hidden').val(),
                        unit_satuan: $('#unit_satuan').val(),
                        qty: qty,
                        harga_satuan: 0
                    });
                }
                renderCartTable();
                clearInputFields();
            });

            // PERBAIKAN #2: Ganti event 'input' menjadi 'change'
            // 'change' hanya akan trigger setelah input selesai diubah (misalnya, saat fokus pindah)
            $('#cart_tabel').on('change', '.cart-qty-input, .cart-harga-input', function() {
                const $row = $(this).closest('tr');
                const id = $row.data('id');
                const item = cart.find(i => i.id == id);

                if (item) {
                    item.qty = parseFloat($row.find('.cart-qty-input').val()) || 0;
                    item.harga_satuan = parseFloat($row.find('.cart-harga-input').val()) || 0;
                    // Cukup panggil updateTotals, tidak perlu re-render seluruh tabel
                    updateTotals();
                }
            });

            $('#cart_tabel').on('click', '.remove-item', function() {
                const idToRemove = $(this).closest('tr').data('id');
                cart = cart.filter(item => item.id != idToRemove);
                renderCartTable();
            });

            const clearInputFields = () => {
                $('#barang_id, #kode_barang, #nama_barang_hidden, #stock, #jenis_material, #unit_satuan').val(
                    '');
                $('#nama_barang_display, #stock_display').text('-');
                $('#qty').val('1');
                $('#cari_barang').focus();
            };

            $('#transactionForm').on('submit', function(e) {
                cart = cart.filter(item => item.qty > 0);
                if (cart.length === 0) {
                    e.preventDefault();
                    Swal.fire('Error', 'Keranjang tidak boleh kosong.', 'error');
                    return;
                }

                // Validasi harga satuan
                const itemWithNoPrice = cart.find(item => item.harga_satuan <= 0);
                if (itemWithNoPrice) {
                    e.preventDefault();
                    Swal.fire('Peringatan',
                        `Harga satuan untuk "${itemWithNoPrice.nama_barang}" harus diisi dan tidak boleh nol.`,
                        'warning');
                    return;
                }

                $('#cart_items_json').val(JSON.stringify(cart));
                $('#submitBtn').prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
            });
        });
    </script>
@endpush
