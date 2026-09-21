@extends('layouts.lab-admin')
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)

@section('title', 'Rawat Jalan')

@section('content_header')
    <div class="d-flex align-items-center">
        <h1 class="mb-0 mr-2">Data Rawat Jalan</h1>

        <span class="badge shadow-sm px-3 py-2" id="totalPasienHeader"
            style="font-size:14px;font-weight:1000;border-radius:10px;letter-spacing:0.5px;">
            0 Pasien
        </span>
    </div>
@stop

@section('content')

    <div class="card shadow-sm">

        <div class="card-header bg-light">

            <div class="row align-items-center">

                <div class="col-md-3">
                    <div class="form-group row mb-0">
                        <label class="col-sm-4 col-form-label">TGL Awal</label>
                        <div class="col-sm-8">
                            <input type="text" id="tgl1" class="form-control datepicker" value="{{ date('d-m-Y') }}"
                                autocomplete="off">
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group row mb-0">
                        <label class="col-sm-4 col-form-label">TGL Akhir</label>
                        <div class="col-sm-8">
                            <input type="text" id="tgl2" class="form-control datepicker" value="{{ date('d-m-Y') }}"
                                autocomplete="off">
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <button type="button" id="btnFilter" class="btn btn-lab-filter btn-block">
                        <i class="fas fa-search mr-1"></i>
                        Tampilkan
                    </button>
                </div>

            </div>

        </div>

        <div class="card-body p-0">
            <div class="table-wrap">
                <table id="tblRawatJalan" class="table table-hover table-striped table-bordered nowrap">
                    <thead>
                        <tr>
                            <th>PxRS</th>
                            <th>ID</th>
                            <th>NoRM</th>
                            <th>Nama Pasien</th>
                            <th>Tanggal</th>
                            <th>Alamat</th>
                            <th>Bagian</th>
                            <th>Shift</th>
                            <th>NoSEP</th>
                            <th>No JKN</th>
                            <th>No WA</th>
                        </tr>
                    </thead>

                    <tbody></tbody>
                </table>
            </div>
        </div>

    </div>

@stop

@section('css')

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css">

    <style>
        /* =========================================
                   STYLE SAMA DENGAN RAWAT INAP
                ========================================== */

        .table-wrap {
            width: 100%;
            overflow-x: auto;
            overflow-y: auto;
            max-height: 70vh;
            position: relative;
        }


        #tblRawatJalan {
            min-width: 900px !important;
            width: max-content !important;
        }


        #tblRawatJalan th,
        #tblRawatJalan td {
            white-space: nowrap;
            vertical-align: middle;
        }


        /* HEADER TABEL */
        #tblRawatJalan thead th {
            position: sticky;
            top: 0;
            z-index: 2;

            background: #3f66d6 !important;
            color: white !important;
        }


        /* BARIS */
        #tblRawatJalan tbody tr {
            cursor: pointer;
        }


        /* HOVER BARIS */
        #tblRawatJalan tbody tr:hover td {
            background: #eef2ff !important;
        }


        /* BADGE JUMLAH PASIEN */
        #totalPasienHeader {
            background: #3f66d6 !important;
            color: white !important;
        }


        /* TOMBOL FILTER */
        .btn-lab-filter {
            background: #3f66d6 !important;
            border-color: #3f66d6 !important;
            color: white !important;
        }


        .btn-lab-filter:hover,
        .btn-lab-filter:focus {
            background: #3457c0 !important;
            border-color: #3457c0 !important;
            color: white !important;
        }


        /* INPUT FOCUS */
        .form-control:focus {
            border-color: #3f66d6 !important;

            box-shadow:
                0 0 0 .15rem rgba(63, 102, 214, .15) !important;
        }


        /* DATEPICKER */
        .datepicker table tr td.active,
        .datepicker table tr td.active:hover {
            background: #3f66d6 !important;
            background-image: none !important;
            color: white !important;
        }


        .datepicker table tr td.today {
            background: #eef2ff !important;
            color: #3457c0 !important;
        }


        /* SCROLLBAR */
        .table-wrap::-webkit-scrollbar {
            height: 9px;
            width: 9px;
        }


        .table-wrap::-webkit-scrollbar-track {
            background: #eef2ff;
            border-radius: 10px;
        }


        .table-wrap::-webkit-scrollbar-thumb {
            background: #8fa8f2;
            border-radius: 10px;
        }


        .table-wrap::-webkit-scrollbar-thumb:hover {
            background: #3f66d6;
        }
    </style>

@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/locales/bootstrap-datepicker.id.min.js">
    </script>

    <script>
        let tableRawatJalan;

        const userId = "{{ session('userlab_id') ?? 'guest' }}";
        const keyPrefix = "rawat_jalan_" + userId + "_";

        function simpanFilterRawatJalan() {
            localStorage.setItem(keyPrefix + "tgl_awal", $('#tgl1').val());
            localStorage.setItem(keyPrefix + "tgl_akhir", $('#tgl2').val());
        }

        function loadFilterRawatJalan() {
            let savedAwal = localStorage.getItem(keyPrefix + "tgl_awal");
            let savedAkhir = localStorage.getItem(keyPrefix + "tgl_akhir");

            if (savedAwal) $('#tgl1').val(savedAwal);
            if (savedAkhir) $('#tgl2').val(savedAkhir);
        }

        function toDbDate(tgl) {
            if (!tgl) return '';

            let p = tgl.split('-');

            if (p.length !== 3) return tgl;

            return p[2] + '-' + p[1] + '-' + p[0];
        }

        $(function() {

            $('.datepicker').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'id'
            });

            loadFilterRawatJalan();

            tableRawatJalan = $("#tblRawatJalan").DataTable({
                processing: true,
                stateSave: true,

                stateSaveCallback: function(settings, data) {
                    localStorage.setItem(
                        keyPrefix + "datatable_state",
                        JSON.stringify(data)
                    );
                },

                stateLoadCallback: function(settings) {
                    let savedState = localStorage.getItem(
                        keyPrefix + "datatable_state"
                    );

                    return savedState ? JSON.parse(savedState) : null;
                },

                ajax: {
                    url: "{{ route('rawatjalan.data') }}",
                    data: function(d) {
                        d.tgl_awal = toDbDate($('#tgl1').val());
                        d.tgl_akhir = toDbDate($('#tgl2').val());
                    }
                },

                columns: [{
                        data: 'PxRS',
                        defaultContent: '-'
                    },
                    {
                        data: 'ID',
                        defaultContent: '-'
                    },
                    {
                        data: 'RegNum',
                        defaultContent: '-'
                    },
                    {
                        data: 'Nama',
                        defaultContent: '-'
                    },
                    {
                        data: 'Tanggal',
                        render: function(data) {
                            if (!data) return '-';

                            let tgl = new Date(data);
                            let dd = String(tgl.getDate()).padStart(2, '0');
                            let mm = String(tgl.getMonth() + 1).padStart(2, '0');
                            let yyyy = tgl.getFullYear();

                            return dd + '/' + mm + '/' + yyyy;
                        }
                    },
                    {
                        data: 'Addr',
                        defaultContent: '-'
                    },
                    {
                        data: 'SubLayanan',
                        defaultContent: '-'
                    },
                    {
                        data: 'Alias',
                        defaultContent: '-'
                    },
                    {
                        data: 'NoSEP',
                        defaultContent: '-'
                    },
                    {
                        data: 'NoJKN',
                        defaultContent: '-'
                    },
                    {
                        data: 'NoWA',
                        defaultContent: '-'
                    }
                ],

                createdRow: function(row, data) {
                    $(row).css('cursor', 'pointer');

                    $(row).on('click', function() {
                        simpanFilterRawatJalan();

                        window.location.href =
                            "{{ route('rawatjalan.detail', ':id') }}".replace(':id', data.ID);
                    });
                },

                responsive: false,
                autoWidth: false,
                scrollX: false,

                paging: true,
                pageLength: 200,

                lengthMenu: [
                    [50, 100, 150, 200, -1],
                    [50, 100, 150, 200, "Semua"]
                ],

                ordering: true,
                order: [
                    [1, 'desc']
                ],

                drawCallback: function(settings) {
                    let total = settings.fnRecordsTotal();
                    $('#totalPasienHeader').html(total + ' Pasien');
                },

                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    paginate: {
                        previous: "Sebelumnya",
                        next: "Berikutnya"
                    }
                }
            });

            $('#tgl1,#tgl2').on('change', function() {
                simpanFilterRawatJalan();
            });

            $('#btnFilter').on('click', function() {
                simpanFilterRawatJalan();
                tableRawatJalan.ajax.reload();
            });

        });
    </script>
@stop
