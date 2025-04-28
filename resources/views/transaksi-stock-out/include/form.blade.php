{{-- resources/views/transaksi-stock-out/include/form.blade.php --}}
<section class="content">
    <div class="container-fluid">
        <div class="row">
            {{-- Kolom Informasi User, No Surat, Tanggal --}}
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

            {{-- Kolom Attachment & Keterangan --}}
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

            {{-- Kolom Input Barang & Qty --}}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <table>
                            <tr>
                                {{-- 2. Ganti Label "Barang" jadi "Kode Barang" --}}
                                <td style="vertical-align: top; width:30%;">
                                    <label for="kode_barang">Kode Barang</label>
                                </td>
                                <td>
                                    <div class="form-group input-group">
                                        <input type="hidden" id="barang_id">
                                        <input type="hidden" id="nama_barang_hidden"> {{-- Hidden input nama barang --}}
                                        <input type="hidden" id="stock">
                                        <input type="hidden" id="jenis_material" readonly>
                                        <input type="hidden" id="unit_satuan" readonly>
                                        {{-- Input Kode Barang --}}
                                        <input type="text" name="kode_barang_display" id="kode_barang"
                                            class="form-control" readonly placeholder="Pilih Barang...">
                                        {{-- Tombol Cari --}}
                                        <span class="input-group-text btn btn-success" id="cari_barang"
                                            data-bs-toggle="modal" data-bs-target="#modal-item"
                                            style="cursor: pointer;">
                                            <i class="fa fa-search"></i>
                                        </span>
                                    </div>
                                    {{-- Display Nama Barang --}}
                                    <div class="mt-1">
                                        <small>Nama Barang: <strong id="nama_barang_display">-</strong></small>
                                        <br>
                                        <small>Stok Tersedia: <strong id="stock_display">-</strong></small>
                                        {{-- Display Stok --}}
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top; width:30%;">
                                    <label for="qty">Qty</label>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <input type="number" id="qty" value="1" min="1"
                                            class="form-control">
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
                        <h5 class="card-title">Keranjang Stock Out</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            {{-- 3. Samakan kolom tabel cart dengan modal --}}
                            <table class="table table-bordered table-striped table-sm"> {{-- Tambah table-sm --}}
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th style="width: 20%;">Kode Barang</th>
                                        <th style="width: 30%;">Nama Barang</th> {{-- Kolom Baru --}}
                                        <th style="width: 15%;">Jenis Material</th>
                                        <th style="width: 15%;">Unit Satuan</th>
                                        <th style="width: 10%;">Qty</th>
                                        <th style="width: 5%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="cart_tabel">
                                    {{-- Isi cart akan dirender oleh JS --}}
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Keranjang kosong</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Modal Pencarian Barang --}}
<div class="modal fade" id="modal-item">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Pilih Barang</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body table-responsive">
                <div class="container-fluid">
                    {{-- 1. Tambah Kolom Nama Barang di Modal --}}
                    <table class="table table-bordered table-striped" id="modal_table_barang" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th> {{-- Kolom Baru --}}
                                <th>Jenis Material</th>
                                <th>Unit Satuan</th>
                                <th>Stock</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tblItem">
                            {{-- Isi tabel modal akan dirender oleh JS --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        #modal_table_barang th,
        #modal_table_barang td {
            white-space: nowrap;
        }

        #cari_barang {
            height: calc(1.5em + .75rem + 2px);
        }
    </style>
@endpush

@push('js')
    {{-- jQuery sudah ada di layout utama --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            var modalTable;

            // Inisialisasi DataTable saat modal pertama kali dibuka
            $('#modal-item').one('shown.bs.modal', function() {
                modalTable = $('#modal_table_barang').DataTable({
                    processing: true,
                    serverSide: false, // Ubah ke false jika data diambil sekaligus
                    ajax: {
                        url: "{{ route('listDataBarang') }}", // Route untuk ambil data barang
                        dataSrc: "" // Langsung gunakan array data
                    },
                    columns: [{
                            data: 'kode_barang'
                        },
                        {
                            data: 'nama_barang'
                        }, // Kolom baru untuk nama barang
                        {
                            data: 'jenis_material'
                        },
                        {
                            data: 'unit_satuan'
                        },
                        {
                            data: 'stock'
                        },
                        {
                            data: null, // Kolom aksi
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                // Tambahkan data-nama-barang
                                return `<button type="button" class="btn btn-sm btn-primary pilih-barang"
                                            data-id="${row.id}"
                                            data-kode="${row.kode_barang}"
                                            data-nama-barang="${row.nama_barang}"
                                            data-stock="${row.stock}"
                                            data-jenis-material="${row.jenis_material}"
                                            data-unit-satuan="${row.unit_satuan}">
                                            Pilih
                                        </button>`;
                            }
                        }
                    ],
                    language: {
                        url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                    },
                    // destroy: true
                });
            });

            // Jika ingin reload data tiap buka modal
            $('#modal-item').on('show.bs.modal', function() {
                if ($.fn.dataTable.isDataTable('#modal_table_barang')) {
                    modalTable.ajax.reload();
                }
            });


            // --- Tombol Pilih Barang di Modal ---
            $('#modal_table_barang tbody').on('click', '.pilih-barang', function() {
                let id = $(this).data('id');
                let kode = $(this).data('kode');
                let nama_barang = $(this).data('nama-barang');
                let stock = $(this).data('stock');
                let jenis_material = $(this).data('jenis-material');
                let unit_satuan = $(this).data('unit-satuan');

                // Set data ke input form utama
                $('#barang_id').val(id);
                $('#kode_barang').val(kode);
                $('#nama_barang_hidden').val(nama_barang);
                $('#nama_barang_display').text(nama_barang);
                $('#stock').val(stock);
                $('#stock_display').text(stock); // Tampilkan stok
                $('#jenis_material').val(jenis_material);
                $('#unit_satuan').val(unit_satuan);

                var modal = bootstrap.Modal.getInstance(document.getElementById('modal-item'));
                modal.hide();
            });

            // --- Logika Keranjang (Cart) ---
            let cart = [];

            $('#add_cart').on('click', function() {
                const id = $('#barang_id').val();
                const kode = $('#kode_barang').val();
                const nama_barang = $('#nama_barang_hidden').val(); // Ambil nama dari hidden input
                const qty = parseInt($('#qty').val());
                const jenis_material = $('#jenis_material').val();
                const unit_satuan = $('#unit_satuan').val();
                const stock = parseInt($('#stock').val()); // Ambil stok dari hidden input

                if (!id || !kode) {
                    alert('Silakan pilih barang terlebih dahulu.');
                    return;
                }
                if (!nama_barang) {
                    alert('Nama barang tidak ditemukan. Coba pilih ulang.');
                    return;
                }

                if (!qty || qty < 1) {
                    alert('Qty minimal 1.');
                    return;
                }

                // Hitung total qty di cart untuk barang ini (termasuk yang mau ditambah)
                const existingInCart = cart.find(item => item.id === id);
                const totalQtyInCart = existingInCart ? existingInCart.qty : 0;
                const newTotalQty = totalQtyInCart + qty;

                // Validasi stock (PENTING untuk Stock Out)
                if (newTotalQty > stock) {
                    const remainingStock = stock - totalQtyInCart;
                    const message = remainingStock >= 0 ? // >= 0 karena bisa jadi pas
                        `Stok tidak mencukupi. Stok tersedia: ${stock}, sudah di keranjang: ${totalQtyInCart}. Anda hanya bisa menambah ${remainingStock} lagi.` :
                        `Stok tidak mencukupi. Stok tersedia: ${stock}, sudah di keranjang: ${totalQtyInCart}.`;
                    alert(message);
                    // Reset qty ke max yang bisa ditambahkan jika > 0
                    if (remainingStock > 0) {
                        $('#qty').val(remainingStock);
                    } else {
                        $('#qty').val(1); // Atau reset ke 1 jika sudah tidak bisa tambah
                    }
                    return; // Hentikan penambahan ke keranjang
                }


                // Update atau tambah item ke cart
                if (existingInCart) {
                    existingInCart.qty += qty;
                } else {
                    cart.push({
                        id,
                        kode,
                        nama_barang, // Simpan nama barang
                        jenis_material,
                        unit_satuan,
                        qty,
                        stock // Simpan stok awal untuk referensi
                    });
                }

                renderCartTable();
                clearInput();
            });

            function renderCartTable() {
                const $tableBody = $('#cart_tabel');
                $tableBody.empty();

                if (cart.length === 0) {
                    $tableBody.append(
                        '<tr><td colspan="7" class="text-center text-muted">Keranjang kosong</td></tr>'
                        ); // Colspan 7
                    return;
                }

                cart.forEach((item, index) => {
                    $tableBody.append(`
                        <tr data-id="${item.id}">
                            <td>${index + 1}</td>
                            <td>${item.kode}</td>
                            <td>${item.nama_barang}</td>
                            <td>${item.jenis_material}</td>
                            <td>${item.unit_satuan}</td>
                            <td class="text-center">${item.qty}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm remove-item" data-id="${item.id}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                });
            }

            // Hapus item dari cart
            $('#cart_tabel').on('click', '.remove-item', function() {
                const idToRemove = $(this).data('id');
                cart = cart.filter(item => item.id !== idToRemove.toString());
                renderCartTable();
            });

            // Reset input
            function clearInput() {
                $('#barang_id').val('');
                $('#kode_barang').val('');
                $('#nama_barang_hidden').val('');
                $('#nama_barang_display').text('-');
                $('#stock').val('');
                $('#stock_display').text('-'); // Reset display stok
                $('#qty').val(1);
                $('#jenis_material').val('');
                $('#unit_satuan').val('');
            }

            // Validasi Submit Form (Sama seperti Stock In)
            $('#transactionForm').on('submit', function(e) {
                if ($('#no_surat').val() === '') {
                    e.preventDefault();
                    alert('No Surat tidak boleh kosong');
                    $('#no_surat').focus();
                    return false;
                }
                if ($('#tanggal').val() === '') {
                    e.preventDefault();
                    alert('Tanggal tidak boleh kosong');
                    $('#tanggal').focus();
                    return false;
                }
                if (cart.length === 0) {
                    e.preventDefault();
                    alert('Minimal 1 item harus dimasukkan ke keranjang');
                    $('#cari_barang').focus();
                    return false;
                }

                $('input[name="cart_items"]').remove();
                const cartData = JSON.stringify(cart);
                $('<input>').attr({
                    type: 'hidden',
                    name: 'cart_items',
                    value: cartData
                }).appendTo('#transactionForm');
                return true;
            });

        }); // Akhir Document Ready
    </script>
@endpush
