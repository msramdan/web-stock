@extends('layouts.app')

@section('title', 'Tambah Permintaan Barang')

@push('css')
    {{-- CSS untuk DataTable jika belum ada di layout utama atau create.blade.php --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs5/dt-1.12.0/datatables.min.css" />
    <style>
        /* Style tambahan jika diperlukan */
        #modal_table_barang_permintaan th,
        #modal_table_barang_permintaan td {
            white-space: nowrap;
        }

        .search-barang-button {
            /* Penyesuaian tinggi tombol search */
            height: calc(1.5em + .5rem + 2px);
            /* Sesuaikan dengan form-control-sm */
        }

        /* Pastikan kolom qty di cart rata kanan */
        #detailPermintaanTable td.text-end,
        #detailPermintaanTable input.total-harga-detail,
        #detailPermintaanTable input.harga-satuan,
        #detailPermintaanTable input.jumlah-pesanan {
            text-align: right;
        }

        #detailPermintaanTable input.stok-terakhir {
            /* SEBELUMNYA .stok-terakhir, JIKA DIREVISI MENJADI .stok-saat-ini MAKA SESUAIKAN */
            text-align: right;
        }

        /* Pastikan kolom stock di modal rata kanan */
        #modal_table_barang_permintaan td:nth-child(5) {
            /* Kolom stok */
            text-align: right;
        }

        /* TAMBAHKAN STYLE INI UNTUK INPUT READONLY */
        #detailPermintaanTable input[readonly].form-control-sm,
        #detailPermintaanTable input[readonly].form-control {
            background-color: #e9ecef;
            /* Warna abu-abu seperti disabled Bootstrap */
            opacity: 1;
            /* Pastikan opacity tetap 1 agar terbaca jelas */
            cursor: default;
            /* Ubah cursor menjadi default */
        }

        /* Anda juga bisa menargetkan class spesifik jika perlu */
        /*
                    #detailPermintaanTable .nama-barang-display,
                    #detailPermintaanTable .stok-terakhir,
                    #detailPermintaanTable .satuan-barang-display,
                    #detailPermintaanTable .total-harga-detail {
                        background-color: #e9ecef;
                        opacity: 1;
                        cursor: default;
                    }
                    */
    </style>
@endpush

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>@yield('title')</h3>
                    <p class="text-subtitle text-muted">Buat permintaan barang baru.</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('permintaan-barang.index') }}">Permintaan
                                    Barang</a></li>
                            <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Formulir Permintaan Barang</h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible show fade">
                            <ul class="ms-0 mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>
                                        <p class="mb-0">{{ $error }}</p>
                                    </li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible show fade">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    <form action="{{ route('permintaan-barang.store') }}" method="POST">
                        @csrf
                        @include('permintaan-barang.include.form')
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Simpan Permintaan</button>
                            <a href="{{ route('permintaan-barang.index') }}" class="btn btn-light">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('js')
    {{-- jQuery sudah ada di layout utama, jadi tidak perlu di-include lagi --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/js/all.min.js"></script> {{-- Jika belum ada --}}
    <script src="https://cdn.jsdelivr.net/combine/npm/datatables.net@1.12.0,npm/datatables.net-bs5@1.12.0"></script>


    <script>
        // Helper function to format number for display (Format Indonesia)
        function formatNumber(num, maxDecimals = 4) { // Ubah parameter kedua menjadi maxDecimals
            const number = parseFloat(num);
            if (isNaN(number)) {
                return '0'; // Sesuai helper PHP Anda
            }

            // Format dengan jumlah desimal maksimal dan pemisah Indonesia
            // toLocaleString id-ID sudah menggunakan '.' sebagai pemisah ribuan dan ',' sebagai desimal
            let formatted = number.toLocaleString('id-ID', {
                minimumFractionDigits: 0, // Biarkan minimum 0 agar bisa di-trim
                maximumFractionDigits: maxDecimals
            });

            // Replikasi rtrim(rtrim($formatted, '0'), ',')
            // Cek apakah ada bagian desimal
            if (formatted.includes(',')) {
                let parts = formatted.split(',');
                parts[1] = parts[1].replace(/0+$/, ''); // Hapus trailing zeros dari bagian desimal
                if (parts[1].length === 0) { // Jika tidak ada angka lagi setelah koma
                    formatted = parts[0]; // Ambil bagian integer saja
                } else {
                    formatted = parts[0] + ',' + parts[1];
                }
            }
            return formatted;
        }

        function parseNumber(formattedNum) {
            if (typeof formattedNum === 'number') return formattedNum;
            if (!formattedNum) return 0;
            return parseFloat(formattedNum.replace(/\./g, '').replace(/,/g, '.')) || 0;
        }

        document.addEventListener('DOMContentLoaded', function() {
            let activeRowElement = null; // Untuk menyimpan referensi baris yang aktif
            let modalPermintaanBarangTable;
            let detailRowIndex = $('#detail_permintaan_body tr.detail-row').length; // Hitung baris yang sudah ada

            // --- Fungsi Inisialisasi DataTable Modal ---
            function initializeModalDataTable() {
                if ($.fn.dataTable.isDataTable('#modal_table_barang_permintaan')) {
                    modalPermintaanBarangTable.ajax.reload(null, false);
                } else {
                    modalPermintaanBarangTable = $('#modal_table_barang_permintaan').DataTable({
                        processing: true,
                        serverSide: false, // Diasumsikan data barang tidak terlalu besar
                        ajax: {
                            url: "{{ route('listDataBarang') }}", // Pastikan route ini ada dan mengembalikan JSON data barang
                            dataSrc: "" // Jika response langsung array of objects
                        },
                        deferRender: true,
                        columns: [{
                                data: 'kode_barang'
                            },
                            {
                                data: 'nama_barang'
                            },
                            {
                                data: 'jenis_material'
                            }, // Sesuaikan dengan nama field dari controller
                            {
                                data: 'unit_satuan'
                            }, // Sesuaikan dengan nama field dari controller
                            {
                                data: 'stock',
                                render: function(data, type, row) {
                                    return formatNumber(data, 4); // Format stok dengan 4 desimal
                                }
                            },
                            {
                                data: null,
                                orderable: false,
                                searchable: false,
                                render: function(data, type, row) {
                                    return `<button type="button" class="btn btn-sm btn-primary pilih-barang-permintaan"
                                                data-id="${row.id}"
                                                data-kode="${row.kode_barang}"
                                                data-nama-barang="${row.nama_barang}"
                                                data-stock="${row.stock}"
                                                data-unit-id="${row.unit_satuan_id}"
                                                data-unit-nama="${row.unit_satuan}">Pilih</button>`;
                                }
                            }
                        ],
                        columnDefs: [{
                            className: "text-end",
                            targets: 4 // Kolom stok rata kanan
                        }],
                        language: {
                            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                        }
                    });
                }
            }

            // --- Event Listener untuk Tombol Cari Barang di Setiap Baris ---
            $('#detail_permintaan_body').on('click', '.search-barang-button', function() {
                activeRowElement = $(this).closest('tr.detail-row'); // Simpan baris yang memicu modal
                // Tidak perlu inisialisasi ulang DataTable setiap kali modal dibuka jika sudah ada
                if (!modalPermintaanBarangTable) {
                    initializeModalDataTable();
                } else {
                    modalPermintaanBarangTable.ajax.reload(null, false); // Cukup reload data
                }
            });

            // --- Event Listener untuk Tombol "Pilih" di Modal ---
            $('#modal_table_barang_permintaan tbody').on('click', '.pilih-barang-permintaan', function() {
                if (!activeRowElement) return;

                const id = $(this).data('id');
                const kode = $(this).data('kode');
                const nama_barang = $(this).data('nama-barang');
                const stock = $(this).data('stock'); // Nilai asli
                const unitNama = $(this).data('unit-nama'); // Nama unit satuan dari barang

                activeRowElement.find('.barang-id-input').val(id);
                activeRowElement.find('.nama-barang-display').val(kode + ' - ' + nama_barang);
                activeRowElement.find('.stok-terakhir').val(formatNumber(stock,
                    4)); // Format stok untuk display
                activeRowElement.find('.satuan-barang-display').val(unitNama);

                // Reset input jumlah, harga, total
                activeRowElement.find('.jumlah-pesanan').val('').focus(); // Fokus ke jumlah pesanan
                activeRowElement.find('.harga-satuan').val('');
                activeRowElement.find('.total-harga-detail').val(formatNumber(0));

                calculateTotals(); // Hitung ulang total keseluruhan

                // Tutup modal
                var modal = bootstrap.Modal.getInstance(document.getElementById('modal-item-permintaan'));
                modal.hide();
                activeRowElement = null; // Reset baris aktif
            });


            // --- Fungsi untuk Menambah Baris Detail Baru ---
            $('#add_detail_row').on('click', function() {
                let newRowHtml = `
                    <tr class="detail-row" data-row-index="${detailRowIndex}">
                        <td>
                            <div class="input-group">
                                <input type="hidden" name="details[${detailRowIndex}][barang_id]" class="barang-id-input">
                                <input type="text" class="form-control form-control-sm nama-barang-display" readonly placeholder="Pilih Barang...">
                                <button type="button" class="btn btn-success btn-sm search-barang-button" data-bs-toggle="modal" data-bs-target="#modal-item-permintaan" title="Cari Barang">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </td>
                        <td>
                            <input type="text" name="details[${detailRowIndex}][stok_terakhir]" class="form-control form-control-sm stok-terakhir" readonly>
                        </td>
                        <td>
                            <input type="number" name="details[${detailRowIndex}][jumlah_pesanan]" class="form-control form-control-sm jumlah-pesanan" step="any" min="1" required>
                        </td>
                        <td> <input type="text" name="details[${detailRowIndex}][satuan]" class="form-control form-control-sm satuan-barang-display" readonly required> </td>
                        <td>
                            <input type="number" name="details[${detailRowIndex}][harga_per_satuan]" class="form-control form-control-sm harga-satuan" step="any" min="0" required>
                        </td>
                        <td>
                            <input type="text" name="details[${detailRowIndex}][total_harga]" class="form-control form-control-sm total-harga-detail" readonly value="${formatNumber(0)}">
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove-detail-row"><i class="fas fa-times"></i></button>
                        </td>
                    </tr>
                `;
                $('#detail_permintaan_body').append(newRowHtml);

                detailRowIndex++;
                updateRowIndexes();
            });

            // --- Fungsi Hapus Baris Detail ---
            $('#detail_permintaan_body').on('click', '.remove-detail-row', function() {
                $(this).closest('tr').remove();
                calculateTotals();
                updateRowIndexes(); // Panggil fungsi update index
            });

            // --- Fungsi Kalkulasi per Baris dan Total Keseluruhan ---
            function calculateRowTotal(row) {
                const jumlah = parseNumber($(row).find('.jumlah-pesanan').val()) || 0;
                const harga = parseNumber($(row).find('.harga-satuan').val()) || 0;
                const total = jumlah * harga;
                $(row).find('.total-harga-detail').val(formatNumber(total));
                calculateTotals();
            }

            $('#detail_permintaan_body').on('input change', '.jumlah-pesanan, .harga-satuan', function() {
                calculateRowTotal($(this).closest('tr'));
            });

            $('#include_ppn_checkbox').on('change', function() {
                $('#include_ppn_hidden').val(this.checked ? 'yes' : 'no');
                calculateTotals();
            });

            function calculateTotals() {
                let subTotal = 0;
                $('.total-harga-detail').each(function() {
                    subTotal += parseNumber($(this).val());
                });

                $('#sub_total_pesanan_display').val('Rp ' + formatNumber(subTotal));
                $('#sub_total_pesanan_hidden').val(subTotal);

                let nominalPpn = 0;
                if ($('#include_ppn_checkbox').is(':checked')) {
                    nominalPpn = subTotal * 0.11; // PPN 11%
                }
                $('#nominal_ppn_display').val('Rp ' + formatNumber(nominalPpn));
                $('#nominal_ppn_hidden').val(nominalPpn);

                const totalPesanan = subTotal + nominalPpn;
                $('#total_pesanan_display').val('Rp ' + formatNumber(totalPesanan));
                $('#total_pesanan_hidden').val(totalPesanan);
            }

            // --- Fungsi Update Index Baris (setelah hapus/tambah) ---
            function updateRowIndexes() {
                $('#detail_permintaan_body tr.detail-row').each(function(newIndex) {
                    $(this).attr('data-row-index', newIndex); // Update atribut data-row-index
                    $(this).find('input, select').each(function() {
                        let name = $(this).attr('name');
                        if (name) {
                            name = name.replace(/details\[\d+\]/g, 'details[' + newIndex + ']');
                            $(this).attr('name', name);
                        }
                        // Update error message target jika ada (lebih kompleks, bisa diabaikan jika tidak krusial)
                    });
                });
                // Update detailRowIndex untuk baris berikutnya yang akan ditambah
                detailRowIndex = $('#detail_permintaan_body tr.detail-row').length;
            }

            // Panggil kalkulasi saat halaman dimuat (untuk form edit)
            $('.detail-row').each(function() {
                // Untuk form edit, kita mungkin perlu memicu updateRowData dari script lama
                // Tapi karena sekarang pemilihan barang via modal, stok & satuan diisi saat barang dipilih.
                // Harga dan jumlah akan memicu calculateRowTotal.
                if ($(this).find('.barang-id-input').val()) { // Hanya jika barang sudah ada (mode edit)
                    calculateRowTotal(this);
                }
            });
            calculateTotals(); // Hitung total keseluruhan saat load


            // Inisialisasi modal DataTable saat modal pertama kali akan ditampilkan
            // Ini mencegah DataTable diinisialisasi jika modal tidak pernah dibuka.
            $('#modal-item-permintaan').one('shown.bs.modal', function() {
                initializeModalDataTable();
            });

        }); // Akhir DOMContentLoaded
    </script>
@endpush
