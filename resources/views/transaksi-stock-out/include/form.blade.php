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
                                <td style="vertical-align: top; width:30%;">
                                    <label for="kode_barang">Kode Barang</label>
                                </td>
                                <td>
                                    <div class="form-group input-group">
                                        <input type="hidden" id="barang_id">
                                        <input type="hidden" id="nama_barang_hidden">
                                        <input type="hidden" id="stock"> {{-- Tetap simpan nilai asli --}}
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
                                        {{-- Display stok --}}
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top; width:30%;">
                                    <label for="qty">Qty</label>
                                </td>
                                <td>
                                    <div class="form-group">
                                        {{-- Perubahan: type="text", step="any", min="0" --}}
                                        <input type="text" inputmode="decimal" id="qty" value="1"
                                            min="0" step="any" class="form-control">
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
                            <table class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th style="width: 20%;">Kode Barang</th>
                                        <th style="width: 30%;">Nama Barang</th>
                                        <th style="width: 15%;">Jenis Material</th>
                                        <th style="width: 15%;">Unit Satuan</th>
                                        <th style="width: 10%;" class="text-end">Qty</th> {{-- text-end untuk angka --}}
                                        <th style="width: 5%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="cart_tabel">
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
                    <table class="table table-bordered table-striped" id="modal_table_barang" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Jenis Material</th>
                                <th>Unit Satuan</th>
                                <th class="text-end">Stock</th> {{-- text-end untuk angka --}}
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

        /* Pastikan kolom qty di cart rata kanan */
        #cart_tabel td:nth-child(6) {
            text-align: right;
        }

        /* Pastikan kolom stock di modal rata kanan */
        #modal_table_barang td:nth-child(5) {
            text-align: right;
        }
    </style>
@endpush

@push('js')
    {{-- jQuery sudah ada di layout utama --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        // Helper function to format number for display
        function formatNumber(num, decimals = 4) {
            const number = parseFloat(num);
            if (isNaN(number)) return '0';
            let formatted = number.toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: decimals
            });
            return formatted;
        }

        // Helper function to parse input string to float
        function parseFloatInput(value) {
            if (typeof value !== 'string') value = String(value);
            return parseFloat(value.replace(/\./g, '').replace(',', '.')) || 0;
        }


        $(document).ready(function() {
            var modalTable;

            $('#modal-item').one('shown.bs.modal', function() {
                modalTable = $('#modal_table_barang').DataTable({
                    processing: true,
                    serverSide: false,
                    ajax: {
                        url: "{{ route('listDataBarang') }}",
                        dataSrc: ""
                    },
                    columns: [{
                            data: 'kode_barang'
                        },
                        {
                            data: 'nama_barang'
                        },
                        {
                            data: 'jenis_material'
                        },
                        {
                            data: 'unit_satuan'
                        },
                        {
                            data: 'stock',
                            render: function(data, type, row) {
                                // Format stock for display in modal
                                return formatNumber(data, 4);
                            }
                        },
                        {
                            data: null,
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                return `<button type="button" class="btn btn-sm btn-primary pilih-barang"
                                            data-id="${row.id}" data-kode="${row.kode_barang}" data-nama-barang="${row.nama_barang}"
                                            data-stock="${row.stock}" data-jenis-material="${row.jenis_material}" data-unit-satuan="${row.unit_satuan}">Pilih</button>`;
                            }
                        }
                    ],
                    columnDefs: [{
                            className: "text-end",
                            targets: 4
                        } // Rata kanan kolom stock
                    ],
                    language: {
                        url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                    },
                });
            });

            $('#modal-item').on('show.bs.modal', function() {
                if ($.fn.dataTable.isDataTable('#modal_table_barang')) {
                    modalTable.ajax.reload(null, false);
                }
            });

            // --- Tombol Pilih Barang di Modal ---
            $('#modal_table_barang tbody').on('click', '.pilih-barang', function() {
                let id = $(this).data('id');
                let kode = $(this).data('kode');
                let nama_barang = $(this).data('nama-barang');
                let stock = $(this).data('stock'); // Nilai asli
                let jenis_material = $(this).data('jenis-material');
                let unit_satuan = $(this).data('unit-satuan');

                $('#barang_id').val(id);
                $('#kode_barang').val(kode);
                $('#nama_barang_hidden').val(nama_barang);
                $('#nama_barang_display').text(nama_barang);
                $('#stock').val(stock); // Simpan nilai asli
                $('#stock_display').text(formatNumber(stock, 4)); // Format untuk display
                $('#jenis_material').val(jenis_material);
                $('#unit_satuan').val(unit_satuan);

                var modal = bootstrap.Modal.getInstance(document.getElementById('modal-item'));
                modal.hide();
                $('#qty').focus();
            });

            // --- Logika Keranjang (Cart) ---
            let cart = [];

            $('#add_cart').on('click', function() {
                const id = $('#barang_id').val();
                const kode = $('#kode_barang').val();
                const nama_barang = $('#nama_barang_hidden').val();
                // Perubahan: Parse float dari input Qty
                const qty = parseFloatInput($('#qty').val());
                const jenis_material = $('#jenis_material').val();
                const unit_satuan = $('#unit_satuan').val();
                // Perubahan: Parse float dari input Stok (hidden)
                const stock = parseFloatInput($('#stock').val());

                if (!id || !kode) {
                    alert('Silakan pilih barang terlebih dahulu.');
                    return;
                }
                if (!nama_barang) {
                    alert('Nama barang tidak ditemukan. Coba pilih ulang.');
                    return;
                }
                // Perubahan: Validasi qty harus > 0
                if (isNaN(qty) || qty <= 0) {
                    alert('Qty harus berupa angka valid lebih besar dari 0.');
                    $('#qty').focus();
                    return;
                }

                // --- Validasi Stok ---
                const existingInCart = cart.find(item => item.id === id);
                const totalQtyInCart = existingInCart ? parseFloat(existingInCart.qty) : 0;
                const newTotalQty = parseFloat((totalQtyInCart + qty).toFixed(
                    8)); // Jumlahkan sebagai float

                if (newTotalQty > stock) {
                    const remainingStock = parseFloat((stock - totalQtyInCart).toFixed(8));
                    const message = remainingStock > 0 ?
                        `Stok tidak mencukupi. Stok tersedia: ${formatNumber(stock, 4)}, sudah di keranjang: ${formatNumber(totalQtyInCart, 4)}. Anda hanya bisa menambah ${formatNumber(remainingStock, 4)} lagi.` :
                        `Stok tidak mencukupi. Stok tersedia: ${formatNumber(stock, 4)}, sudah di keranjang: ${formatNumber(totalQtyInCart, 4)}.`;

                    alert(message);
                    if (remainingStock > 0) {
                        // Set input qty ke sisa stok, format dengan koma
                        $('#qty').val(remainingStock.toString().replace('.', ','));
                    } else {
                        $('#qty').val('0');
                    }
                    return; // Hentikan penambahan
                }
                // --- Akhir Validasi Stok ---

                // Update atau tambah item ke cart
                if (existingInCart) {
                    // Perubahan: Penjumlahan float
                    existingInCart.qty = parseFloat((existingInCart.qty + qty).toFixed(8));
                } else {
                    cart.push({
                        id,
                        kode,
                        nama_barang,
                        jenis_material,
                        unit_satuan,
                        qty: qty, // Simpan sebagai float
                        stock: stock // Simpan stok asli
                    });
                }

                renderCartTable();
                clearInput();
                $('#cari_barang').focus();
            });

            function renderCartTable() {
                const $tableBody = $('#cart_tabel');
                $tableBody.empty();

                if (cart.length === 0) {
                    $tableBody.append(
                        '<tr><td colspan="7" class="text-center text-muted">Keranjang kosong</td></tr>');
                    return;
                }

                cart.forEach((item, index) => {
                    // Perubahan: Format qty untuk tampilan
                    const formattedQty = formatNumber(item.qty, 4);
                    $tableBody.append(`
                        <tr data-id="${item.id}">
                            <td>${index + 1}</td>
                            <td>${item.kode}</td>
                            <td>${item.nama_barang}</td>
                            <td>${item.jenis_material}</td>
                            <td>${item.unit_satuan}</td>
                            {{-- Perubahan: Class text-end dan format qty --}}
                            <td class="text-end">${formattedQty}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm remove-item" data-id="${item.id}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                });
            }

            $('#cart_tabel').on('click', '.remove-item', function() {
                const idToRemove = $(this).data('id');
                cart = cart.filter(item => item.id !== idToRemove.toString());
                renderCartTable();
            });

            function clearInput() {
                $('#barang_id').val('');
                $('#kode_barang').val('');
                $('#nama_barang_hidden').val('');
                $('#nama_barang_display').text('-');
                $('#stock').val('');
                $('#stock_display').text('-');
                $('#qty').val('1'); // Reset ke 1
                $('#jenis_material').val('');
                $('#unit_satuan').val('');
            }

            // Submit Form
            $('#transactionForm').on('submit', function(e) {
                if ($('#no_surat').val().trim() === '') {
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

                // Prepare cart data (send as float)
                $('input[name="cart_items"]').remove();
                const cartDataForSubmit = cart.map(item => ({
                    ...item,
                    qty: item.qty // Send as number/float
                }));
                const cartJson = JSON.stringify(cartDataForSubmit);

                $('<input>').attr({
                    type: 'hidden',
                    name: 'cart_items',
                    value: cartJson
                }).appendTo('#transactionForm');

                // Disable submit button
                $('#submitBtn').prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

                return true;
            });

        }); // Akhir Document Ready
    </script>
@endpush
