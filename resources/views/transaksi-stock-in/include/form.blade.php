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
                                        {{-- Perubahan: type="text", step="any", min="0" --}}
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
                                        <th style="width: 5%;">#</th>
                                        <th style="width: 20%;">Kode Barang</th>
                                        <th style="width: 30%;">Nama Barang</th>
                                        <th style="width: 15%;">Jenis Material</th>
                                        <th style="width: 15%;">Unit Satuan</th>
                                        <th style="width: 10%;" class="text-end">Qty</th>
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
        // Helper function to format number for display (Format Indonesia)
        function formatNumber(num, decimals = 4) {
            const number = parseFloat(num);
            if (isNaN(number)) {
                return '0';
            }
            // Format dengan koma desimal, titik ribuan
            let formatted = number.toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: decimals
            });
            // Hapus ,0000 atau ,00 jika tidak perlu (opsional, tergantung preferensi)
            // formatted = formatted.replace(/,[0]+$/, '');
            return formatted;
        }

        $(document).ready(function() {
            var modalTable;

            // --- Inisialisasi DataTable Modal ---
            $('#modal-item').one('shown.bs.modal', function() {
                modalTable = $('#modal_table_barang').DataTable({
                    processing: true,
                    serverSide: false, // Data diambil sekali via AJAX
                    ajax: {
                        url: "{{ route('listDataBarang') }}",
                        dataSrc: ""
                    },
                    deferRender: true, // Optimasi render
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
                                // Format stok untuk tampilan di modal
                                return formatNumber(data, 4);
                            }
                        },
                        {
                            data: null, // Kolom aksi
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                return `<button type="button" class="btn btn-sm btn-primary pilih-barang"
                                            data-id="${row.id}"
                                            data-kode="${row.kode_barang}"
                                            data-nama-barang="${row.nama_barang}"
                                            data-stock="${row.stock}"
                                            data-jenis-material="${row.jenis_material}"
                                            data-unit-satuan="${row.unit_satuan}">Pilih</button>`;
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
                    // Opsi tambahan untuk performa jika data banyak
                    // scrollY: '300px',
                    // scrollCollapse: true,
                    // paging: true, // Aktifkan paging jika data > 10
                    // pageLength: 10
                });
            });

            // Reload data modal jika dibuka lagi
            $('#modal-item').on('show.bs.modal', function() {
                if ($.fn.dataTable.isDataTable('#modal_table_barang')) {
                    modalTable.ajax.reload(null, false); // false agar tidak reset paging
                }
            });

            // --- Tombol Pilih Barang di Modal ---
            $('#modal_table_barang tbody').on('click', '.pilih-barang', function() {
                let id = $(this).data('id');
                let kode = $(this).data('kode');
                let nama_barang = $(this).data('nama-barang');
                let stock = $(this).data('stock'); // Ambil nilai asli (bisa string atau number)
                let jenis_material = $(this).data('jenis-material');
                let unit_satuan = $(this).data('unit-satuan');

                $('#barang_id').val(id);
                $('#kode_barang').val(kode); // Ini kode_barang_display di form
                $('#nama_barang_hidden').val(nama_barang);
                $('#nama_barang_display').text(nama_barang);
                $('#stock').val(stock); // Simpan nilai asli di hidden input
                $('#stock_display').text(formatNumber(stock, 4)); // Format untuk tampilan
                $('#jenis_material').val(jenis_material);
                $('#unit_satuan').val(unit_satuan);

                // Tutup modal dan fokus ke input Qty
                var modal = bootstrap.Modal.getInstance(document.getElementById('modal-item'));
                modal.hide();
                $('#qty').focus().select(); // Fokus dan pilih isi input Qty
            });

            // --- Logika Keranjang (Cart) ---
            let cart = []; // Array untuk menyimpan item di keranjang

            // Tombol Add Cart diklik
            $('#add_cart').on('click', function() {
                const id = $('#barang_id').val();
                const kode = $('#kode_barang').val(); // Ini kode_barang_display
                const nama_barang = $('#nama_barang_hidden').val();
                // Revisi: Langsung parseFloat dari input type="number"
                const qty = parseFloat($('#qty').val());
                const jenis_material = $('#jenis_material').val();
                const unit_satuan = $('#unit_satuan').val();

                // Validasi dasar
                if (!id || !kode) {
                    alert('Silakan pilih barang terlebih dahulu.');
                    $('#cari_barang').focus(); // Fokus ke tombol cari
                    return;
                }
                if (!nama_barang) {
                    // Seharusnya tidak terjadi jika pemilihan barang benar
                    alert('Nama barang tidak valid. Silakan pilih ulang barang.');
                    $('#cari_barang').focus();
                    return;
                }
                // Validasi Qty
                if (isNaN(qty) || qty <= 0) {
                    alert('Qty harus berupa angka valid lebih besar dari 0.');
                    $('#qty').focus().select();
                    return;
                }

                // Cek apakah item sudah ada di keranjang
                const index = cart.findIndex(item => item.id === id);

                if (index !== -1) {
                    // Jika ada, tambahkan Qty (penjumlahan float)
                    cart[index].qty = parseFloat((cart[index].qty + qty).toFixed(
                    8)); // Gunakan toFixed untuk presisi float
                } else {
                    // Jika baru, tambahkan item baru
                    cart.push({
                        id: id, // Pastikan ID tersimpan
                        kode: kode, // Simpan kode dari display
                        nama_barang: nama_barang,
                        jenis_material: jenis_material,
                        unit_satuan: unit_satuan,
                        qty: qty // Simpan sebagai float
                    });
                }

                renderCartTable(); // Update tampilan tabel keranjang
                clearInputFields(); // Kosongkan input barang & qty
                $('#cari_barang').focus(); // Fokus kembali ke tombol cari barang
            });

            // Fungsi untuk merender ulang tabel keranjang
            function renderCartTable() {
                const $tableBody = $('#cart_tabel');
                $tableBody.empty(); // Kosongkan isi tabel

                if (cart.length === 0) {
                    $tableBody.append(
                        '<tr><td colspan="7" class="text-center text-muted">Keranjang kosong</td></tr>');
                    return;
                }

                // Loop data keranjang dan buat baris tabel
                cart.forEach((item, index) => {
                    const formattedQty = formatNumber(item.qty, 4); // Format Qty untuk tampilan
                    $tableBody.append(`
                        <tr data-id="${item.id}">
                            <td>${index + 1}</td>
                            <td>${item.kode}</td>
                            <td>${item.nama_barang}</td>
                            <td>${item.jenis_material}</td>
                            <td>${item.unit_satuan}</td>
                            <td class="text-end">${formattedQty}</td> {{-- Rata kanan --}}
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm remove-item" data-id="${item.id}" title="Hapus Item">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                });
            }

            // Event listener untuk tombol hapus item di keranjang
            $('#cart_tabel').on('click', '.remove-item', function() {
                const idToRemove = $(this).data('id').toString(); // Pastikan string untuk perbandingan
                cart = cart.filter(item => item.id !== idToRemove); // Hapus item dari array cart
                renderCartTable(); // Render ulang tabel
            });

            // Fungsi untuk membersihkan input setelah item ditambahkan
            function clearInputFields() {
                $('#barang_id').val('');
                $('#kode_barang').val(''); // Input display kode barang
                $('#nama_barang_hidden').val('');
                $('#nama_barang_display').text('-');
                $('#stock').val('');
                $('#stock_display').text('-');
                $('#qty').val('1'); // Reset Qty ke 1
                $('#jenis_material').val('');
                $('#unit_satuan').val('');
            }

            // --- Submit Form Utama ---
            $('#transactionForm').on('submit', function(e) {
                // Validasi Header
                if ($('#no_surat').val().trim() === '') {
                    e.preventDefault(); // Cegah submit
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
                // Validasi Keranjang
                if (cart.length === 0) {
                    e.preventDefault();
                    alert('Minimal 1 item harus dimasukkan ke keranjang');
                    // Fokus ke tombol cari barang agar pengguna bisa memilih item
                    $('#cari_barang').focus();
                    return false;
                }

                // Hapus input cart_items lama jika ada, lalu tambahkan yang baru
                $('input[name="cart_items"]').remove();
                // Pastikan qty dikirim sebagai number/float
                const cartDataForSubmit = cart.map(item => ({
                    ...item,
                    qty: item.qty
                }));
                const cartJson = JSON.stringify(cartDataForSubmit);

                // Tambahkan data keranjang sebagai input hidden
                $('<input>').attr({
                    type: 'hidden',
                    name: 'cart_items',
                    value: cartJson
                }).appendTo('#transactionForm');

                // Disable tombol submit untuk mencegah klik ganda
                $('#submitBtn').prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

                return true; // Lanjutkan proses submit form
            });
        });
    </script>
@endpush
