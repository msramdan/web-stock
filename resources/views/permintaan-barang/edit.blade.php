@extends('layouts.app')

@section('title', 'Edit Permintaan Barang')

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
            height: calc(1.5em + .5rem + 2px);
        }

        #detailPermintaanTable td.text-end,
        #detailPermintaanTable input.total-harga-detail,
        #detailPermintaanTable input.harga-satuan,
        #detailPermintaanTable input.jumlah-pesanan,
        #detailPermintaanTable input.stok-terakhir {
            /* Menambahkan .stok-terakhir di sini */
            text-align: right;
        }

        #modal_table_barang_permintaan td:nth-child(5) {
            /* Kolom stok di modal */
            text-align: right;
        }

        #detailPermintaanTable input[readonly].form-control-sm,
        #detailPermintaanTable input[readonly].form-control {
            background-color: #e9ecef;
            opacity: 1;
            cursor: default;
        }
    </style>
@endpush

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>@yield('title')</h3>
                    <p class="text-subtitle text-muted">Edit data permintaan barang:
                        {{ $permintaanBarang->no_permintaan_barang }}</p>
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
                    <h5 class="card-title">Formulir Edit Permintaan Barang</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('permintaan-barang.update', $permintaanBarang->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        {{-- Memuat form include --}}
                        @include('permintaan-barang.include.form', [
                            'permintaanBarang' => $permintaanBarang,
                            'unitSatuans' => $unitSatuans ?? [],
                        ])
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Update Permintaan</button>
                            <a href="{{ route('permintaan-barang.index') }}" class="btn btn-light">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('js')
    {{-- jQuery sudah ada di layout utama --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/combine/npm/datatables.net@1.12.0,npm/datatables.net-bs5@1.12.0"></script>
    {{-- Tidak perlu Choices.js lagi jika satuan readonly --}}
    {{-- <script src="{{ asset('mazer/extensions/choices.js/public/assets/scripts/choices.min.js') }}"></script> --}}

    <script>
        // Helper function to format number for display (Format Indonesia)
        function formatNumber(num, maxDecimals = 4) {
            const number = parseFloat(num);
            if (isNaN(number)) {
                return '0';
            }
            let formatted = number.toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: maxDecimals
            });
            if (formatted.includes(',')) {
                let parts = formatted.split(',');
                parts[1] = parts[1].replace(/0+$/, '');
                if (parts[1].length === 0) {
                    formatted = parts[0];
                } else {
                    formatted = parts[0] + ',' + parts[1];
                }
            }
            return formatted;
        }

        function parseNumber(formattedNum) {
            if (typeof formattedNum === 'number') return formattedNum;
            if (!formattedNum) return 0;
            return parseFloat(String(formattedNum).replace(/\./g, '').replace(/,/g, '.')) || 0;
        }

        document.addEventListener('DOMContentLoaded', function() {
            let activeRowElement = null;
            let modalPermintaanBarangTable;
            let detailRowIndex = $('#detail_permintaan_body tr.detail-row').length;

            function initializeModalDataTable() {
                if ($.fn.dataTable.isDataTable('#modal_table_barang_permintaan')) {
                    modalPermintaanBarangTable.ajax.reload(null, false);
                } else {
                    modalPermintaanBarangTable = $('#modal_table_barang_permintaan').DataTable({
                        processing: true,
                        serverSide: false,
                        ajax: {
                            url: "{{ route('listDataBarang') }}",
                            dataSrc: ""
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
                            },
                            {
                                data: 'unit_satuan'
                            },
                            {
                                data: 'stock',
                                render: function(data, type, row) {
                                    return formatNumber(data, 4);
                                }
                            },
                            {
                                data: null,
                                orderable: false,
                                searchable: false,
                                render: function(data, type, row) {
                                    return `<button type="button" class="btn btn-sm btn-primary pilih-barang-permintaan"
                                        data-id="${row.id}" data-kode="${row.kode_barang}" data-nama-barang="${row.nama_barang}"
                                        data-stock="${row.stock}" data-unit-nama="${row.unit_satuan}">Pilih</button>`; // data-unit-id dihapus jika tidak dipakai
                                }
                            }
                        ],
                        columnDefs: [{
                            className: "text-end",
                            targets: 4
                        }],
                        language: {
                            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                        }
                    });
                }
            }

            $('#detail_permintaan_body').on('click', '.search-barang-button', function() {
                activeRowElement = $(this).closest('tr.detail-row');
                if (!modalPermintaanBarangTable) {
                    initializeModalDataTable();
                } else {
                    modalPermintaanBarangTable.ajax.reload(null, false);
                }
            });

            $('#modal_table_barang_permintaan tbody').on('click', '.pilih-barang-permintaan', function() {
                if (!activeRowElement) return;
                const id = $(this).data('id');
                const kode = $(this).data('kode');
                const nama_barang = $(this).data('nama-barang');
                const stock = $(this).data('stock');
                const unitNama = $(this).data('unit-nama');

                activeRowElement.find('.barang-id-input').val(id);
                activeRowElement.find('.nama-barang-display').val(kode + ' - ' + nama_barang);
                activeRowElement.find('.stok-terakhir').val(formatNumber(stock, 4));
                activeRowElement.find('.satuan-barang-display').val(
                unitNama); // Mengisi input satuan readonly

                activeRowElement.find('.jumlah-pesanan').val('').focus();
                activeRowElement.find('.harga-satuan').val('');
                activeRowElement.find('.total-harga-detail').val(formatNumber(0, 0)); // 0 desimal untuk Rp
                calculateTotals();
                var modal = bootstrap.Modal.getInstance(document.getElementById('modal-item-permintaan'));
                modal.hide();
                activeRowElement = null;
            });

            $('#add_detail_row').on('click', function() {
                let newRowHtml = `
                    <tr class="detail-row" data-row-index="${detailRowIndex}">
                        <td>
                            <div class="input-group">
                                <input type="hidden" name="details[${detailRowIndex}][barang_id]" class="barang-id-input">
                                <input type="text" class="form-control form-control-sm nama-barang-display" readonly placeholder="Pilih Barang...">
                                <button type="button" class="btn btn-success btn-sm search-barang-button" data-bs-toggle="modal" data-bs-target="#modal-item-permintaan" title="Cari Barang"><i class="fas fa-search"></i></button>
                            </div>
                        </td>
                        <td><input type="text" name="details[${detailRowIndex}][stok_terakhir]" class="form-control form-control-sm stok-terakhir" readonly></td>
                        <td><input type="number" name="details[${detailRowIndex}][jumlah_pesanan]" class="form-control form-control-sm jumlah-pesanan" step="any" min="0.0001" required></td>
                        <td><input type="text" name="details[${detailRowIndex}][satuan]" class="form-control form-control-sm satuan-barang-display" readonly required></td>
                        <td><input type="number" name="details[${detailRowIndex}][harga_per_satuan]" class="form-control form-control-sm harga-satuan" step="any" min="0" required></td>
                        <td><input type="text" name="details[${detailRowIndex}][total_harga]" class="form-control form-control-sm total-harga-detail" readonly value="${formatNumber(0,0)}"></td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-detail-row"><i class="fas fa-times"></i></button></td>
                    </tr>
                `;
                $('#detail_permintaan_body').append(newRowHtml);
                detailRowIndex++;
                updateRowIndexes();
            });

            $('#detail_permintaan_body').on('click', '.remove-detail-row', function() {
                $(this).closest('tr').remove();
                calculateTotals();
                updateRowIndexes();
            });

            function calculateRowTotal(row) {
                const jumlah = parseNumber($(row).find('.jumlah-pesanan').val()) || 0;
                const harga = parseNumber($(row).find('.harga-satuan').val()) || 0;
                const total = jumlah * harga;
                $(row).find('.total-harga-detail').val(formatNumber(total, 0)); // 0 desimal untuk Rp
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
                $('#sub_total_pesanan_display').val('Rp ' + formatNumber(subTotal, 0)); // 0 desimal untuk Rp
                $('#sub_total_pesanan_hidden').val(subTotal);
                let nominalPpn = 0;
                if ($('#include_ppn_checkbox').is(':checked')) {
                    nominalPpn = subTotal * 0.11;
                }
                $('#nominal_ppn_display').val('Rp ' + formatNumber(nominalPpn, 2)); // PPN boleh 2 desimal
                $('#nominal_ppn_hidden').val(nominalPpn);
                const totalPesanan = subTotal + nominalPpn;
                $('#total_pesanan_display').val('Rp ' + formatNumber(totalPesanan,
                2)); // Total Pesanan boleh 2 desimal
                $('#total_pesanan_hidden').val(totalPesanan);
            }

            function updateRowIndexes() {
                $('#detail_permintaan_body tr.detail-row').each(function(newIndex) {
                    $(this).attr('data-row-index', newIndex);
                    $(this).find('input, select').each(
                function() { //Meskipun select satuan sudah tidak ada, ini tidak error
                        let name = $(this).attr('name');
                        if (name) {
                            name = name.replace(/details\[\d+\]/g, 'details[' + newIndex + ']');
                            $(this).attr('name', name);
                        }
                    });
                });
                detailRowIndex = $('#detail_permintaan_body tr.detail-row').length;
            }

            // REVISI: Logika inisialisasi form untuk edit
            function initializeFormValuesForEdit() {
                let hasDetails = false;
                $('.detail-row').each(function() {
                    const row = $(this);
                    const barangIdInput = row.find('.barang-id-input').val();

                    if (barangIdInput && barangIdInput.trim() !==
                        '') { // Cek jika barang_id ada (data dari server)
                        hasDetails = true;
                        let jumlah = parseNumber(row.find('.jumlah-pesanan').val()) || 0;
                        let harga = parseNumber(row.find('.harga-satuan').val()) || 0;
                        // Nilai jumlah dan harga sudah di-render oleh PHP dengan old() atau $detail->...
                        // Pastikan value dari PHP adalah angka mentah atau mudah di-parseNumber()

                        const totalForRow = jumlah * harga;
                        row.find('.total-harga-detail').val(formatNumber(totalForRow,
                        0)); // Set total per baris
                    } else if (!barangIdInput || barangIdInput.trim() === '') {
                        // Jika ini baris template kosong (misal hanya 1 baris template di form edit yg belum diisi)
                        row.find('.total-harga-detail').val(formatNumber(0, 0));
                    }
                });

                // Panggil calculateTotals() hanya sekali setelah semua baris diproses
                calculateTotals();
            }

            initializeFormValuesForEdit(); // Panggil fungsi ini untuk halaman edit

            $('#modal-item-permintaan').one('shown.bs.modal', function() {
                initializeModalDataTable();
            });
        });
    </script>
@endpush
