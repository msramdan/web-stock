<section class="content">
    <div class="container-fluid">
        <div class="row">
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
                                    <label for="date">No Surat</label>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <input type="text" name="no_surat" id="no_surat" class="form-control"
                                            value="" />
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top;">
                                    <label for="date">Tanggal</label>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <input type="datetime-local" name="tanggal" id="tanggal" class="form-control"
                                            value="" placeholder="{{ __('Tanggal') }}" />
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

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
                            <!-- Combined Keterangan Textarea -->
                            <tr>
                                <td style="vertical-align: top; width:30%;">
                                    <label for="keterangan">Keterangan</label>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <textarea name="keterangan" id="keterangan" name="keterangan" class="form-control" rows="3"></textarea>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>




            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <table>
                            <tr>
                                <td style="vertical-align: top; width:30%;">
                                    <label for="kode_barang">Barang</label>
                                </td>
                                <td>
                                    <div class="form-group input-group">
                                        <input type="hidden" id="barang_id">
                                        <input type="hidden" id="stock">
                                        <input type="hidden" id="jenis_material" readonly>
                                        <input type="hidden" id="unit_satuan" readonly>
                                        <input type="text" name="kode_barang" id="kode_barang" class="form-control"
                                            readonly="">
                                        <div class="input-group-append">
                                            <span>
                                                <button type="button"
                                                    class="input-group-text btn btn-success form-control"
                                                    id="cari_barang" data-bs-toggle="modal"
                                                    data-bs-target="#modal-item">
                                                    <i class="fa fa-search"></i>
                                                </button>
                                            </span>
                                        </div>
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
                                            <i class="fa fa-cart-plus">Add</i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Barang</th>
                                        <th>Jenis Matrial</th>
                                        <th>Unit Satuan</th>
                                        <th>Qty</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>

                                <tbody id="cart_tabel">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<div class="modal fade" id="modal-item">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Barang</h4>
            </div>
            <div class="modal-body table-responsive">
                <div class="container-fluid">
                    <table class="table table-bordered table-striped" id="example1">
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th>Jenis Matrial</th>
                                <th>Unit Satuan</th>
                                <th>Stock</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tblItem">
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
@endpush

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#example1').DataTable({
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            // Saat modal dibuka (tombol cari diklik)
            $('#modal-item').on('show.bs.modal', function() {
                $('#tblItem').html('<tr><td colspan="5" class="text-center">Loading...</td></tr>');
                // Ambil data via AJAX
                $.ajax({
                    url: '{{ route('listDataBarang') }}', // ganti sesuai rute kamu
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        let html = '';
                        if (data.length === 0) {
                            html =
                                '<tr><td colspan="5" class="text-center">Tidak ada data</td></tr>';
                        } else {
                            data.forEach(item => {
                                html += `
                                    <tr>
                                        <td>${item.kode_barang}</td>
                                        <td>${item.jenis_material}</td>
                                        <td>${item.unit_satuan}</td>
                                        <td>${item.stock}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary pilih-barang"
                                                data-id="${item.id}"
                                                data-nama="${item.kode_barang}"
                                                data-stock="${item.stock}"
                                                data-jenis-material="${item.jenis_material}"
                                                data-unit-satuan="${item.unit_satuan}">
                                                Pilih
                                            </button>
                                        </td>
                                    </tr>`;
                            });
                        }

                        $('#tblItem').html(html);
                    },
                    error: function() {
                        $('#tblItem').html(
                            '<tr><td colspan="5" class="text-danger text-center">Gagal memuat data</td></tr>'
                        );
                    }
                });
            });

            // Saat tombol pilih diklik
            $(document).on('click', '.pilih-barang', function() {
                let id = $(this).data('id');
                let nama = $(this).data('nama');
                let stock = $(this).data('stock');
                let jenis_material = $(this).data('jenis-material');
                let unit_satuan = $(this).data('unit-satuan');

                // Set data ke input form
                $('#barang_id').val(id);
                $('#kode_barang').val(nama);
                $('#stock').val(stock);
                $('#jenis_material').val(jenis_material); // Pastikan ada input ini di form
                $('#unit_satuan').val(unit_satuan); // Pastikan ada input ini di form

                var modal = bootstrap.Modal.getInstance(document.getElementById('modal-item'));
                modal.hide();
            });
        });
    </script>
    <script>
        let cart = [];

        $('#add_cart').on('click', function() {
            const id = $('#barang_id').val();
            const kode = $('#kode_barang').val();
            const qty = parseInt($('#qty').val());
            const jenis_material = $('#jenis_material').val();
            const unit_satuan = $('#unit_satuan').val();
            const stock = parseInt($('#stock').val());

            if (!id || !kode) {
                alert('Silakan pilih barang terlebih dahulu.');
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

            // Validasi stock
            if (newTotalQty > stock) {
                const remainingStock = stock - totalQtyInCart;
                const message = remainingStock > 0 ?
                    `Stok tidak mencukupi. Stok tersedia: ${stock}, sudah di cart: ${totalQtyInCart}, bisa tambah maksimal: ${remainingStock}` :
                    `Stok tidak mencukupi. Stok tersedia: ${stock}, sudah di cart: ${totalQtyInCart}`;
                alert(message);
                return;
            }

            // Update atau tambah item ke cart
            if (existingInCart) {
                existingInCart.qty += qty;
            } else {
                cart.push({
                    id,
                    kode,
                    jenis_material,
                    unit_satuan,
                    qty,
                    stock // Simpan stock reference untuk validasi berikutnya
                });
            }

            renderCartTable();
            clearInput();
        });

        function renderCartTable() {
            const $table = $('#cart_tabel');
            $table.empty();

            if (cart.length === 0) {
                $table.append('<tr><td colspan="6" class="text-center">Cart kosong</td></tr>');
                return;
            }

            cart.forEach((item, index) => {
                $table.append(`
            <tr data-id="${item.id}">
                <td>${index + 1}</td>
                <td>${item.kode}</td>
                <td>${item.jenis_material}</td>
                <td>${item.unit_satuan}</td>
                <td>${item.qty}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-item" data-id="${item.id}">
                        <i class="fa fa-trash"></i> Hapus
                    </button>
                </td>
            </tr>
        `);
            });
        }

        // Hapus item dari cart
        $(document).on('click', '.remove-item', function() {
            const id = $(this).data('id');
            cart = cart.filter(item => item.id !== id.toString());
            renderCartTable();
        });

        // Reset input setelah tambah ke cart
        function clearInput() {
            $('#barang_id').val('');
            $('#kode_barang').val('');
            $('#qty').val(1);
            $('#jenis_material').val('');
            $('#unit_satuan').val('');
        }

        // Validasi saat submit form
        $('#transactionForm').on('submit', function(e) {
            console.log(cart.length);

            e.preventDefault();

            // Validate  fields
            if ($('#no_surat').val() === '') {
                alert('No Surat tidak boleh kosong');
                $('#no_surat').focus();
                return false;
            }

            if ($('#tanggal').val() === '') {
                alert('Tanggal tidak boleh kosong');
                $('#tanggal').focus();
                return false;
            }

            if (cart.length === 0) {
                alert('Minimal 1 item harus dimasukkan ke cart');
                return false;
            }

            const cartData = JSON.stringify(cart);
            $('<input>').attr({
                type: 'hidden',
                name: 'cart_items',
                value: cartData
            }).appendTo('#transactionForm');
            this.submit();
        });
    </script>
@endpush
