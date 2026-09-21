@extends('layouts.lab-admin')

@section('title', 'Detail Jalan')

@section('content_header')

    <div class="d-flex align-items-center">

        <a href="{{ route('rawatjalan.index') }}" class="btn btn-secondary btn-sm mr-3">

            <i class="fas fa-arrow-left"></i>

        </a>

        <h1 class="mb-0">
            Detail Rawat Jalan
        </h1>

    </div>

@stop


@section('content')

    @if (!$pasien)

        <div class="alert alert-warning">
            Data pasien tidak ditemukan.
        </div>
    @else
        {{-- ====================================================== --}}
        {{-- IDENTITAS PASIEN --}}
        {{-- ====================================================== --}}

        <div class="card shadow-sm mb-3">

            <div class="card-header pasien-header">

                <h3 class="card-title mb-0">

                    <i class="fas fa-user-injured mr-2"></i>

                    {{ $pasien->Nama }} --- {{ $pasien->Addr }}

                </h3>

            </div>

            <div class="card-body py-3">

                <div class="row">

                    <div class="col-md-2">

                        <small class="text-muted d-block">
                            ID
                        </small>

                        <strong>
                            {{ $pasien->ID ?? '-' }}
                        </strong>

                    </div>


                    <div class="col">
                        <small class="text-muted d-block" style="font-size:13px">
                            No SEP
                        </small>

                        <div class="font-weight-bold text-info" style="font-size:16px; cursor:pointer;"
                            onclick="showSepDetail('{{ $pasien->NoSEP }}')">
                            {{ $pasien->NoSEP ?? '-' }}
                        </div>
                    </div>

                    <div class="col">
                        <small class="text-muted d-block" style="font-size:13px">
                            No RM
                        </small>

                        <div class="font-weight-bold" style="font-size:16px">
                            {{ $pasien->RegNum }}
                        </div>
                    </div>


                    <div class="col-md-2">

                        <small class="text-muted d-block">
                            PxRS
                        </small>

                        <strong class="text-primary" style="cursor:pointer;" onclick="openUpdatePxRS()"
                            title="Klik untuk mengubah PxRS">

                            <span id="textPxRS">
                                {{ $pasien->PxRS ?? '-' }}
                            </span>

                        </strong>

                    </div>


                    <div class="col">
                        <small class="text-muted d-block" style="font-size:13px">
                            Tanggal Masuk
                        </small>

                        <div style="font-size:16px">
                            {{ $pasien->Tanggal ? date('d/m/Y', strtotime($pasien->Tanggal)) : '-' }}
                        </div>
                    </div>

                    <div class="col">
                        <small class="text-muted d-block" style="font-size:13px">
                            Jam Masuk
                        </small>

                        <div style="font-size:16px">
                            {{ $pasien->Jam_masuk ? date('H:i', strtotime($pasien->Jam_masuk)) : '-' }}
                        </div>
                    </div>

                    <div class="col">

                        <small class="text-muted d-block" style="font-size:13px">
                            Tanggal Bayar
                        </small>

                        <div style="font-size:16px">
                            {{ $pasien->TglByr ? date('d/m/Y', strtotime($pasien->TglByr)) : '-' }}
                        </div>

                    </div>


                </div>

            </div>

        </div>


        {{-- ====================================================== --}}
        {{-- TAB --}}
        {{-- ====================================================== --}}

        <div class="card shadow-sm">

            <div class="card-header p-0 pt-1">

                <ul class="nav nav-tabs" id="inapDetailTabs">

                    <li class="nav-item">

                        <a class="nav-link active" data-toggle="pill" href="#tab-lab">

                            <i class="fas fa-vials mr-1"></i>
                            Lab

                        </a>

                    </li>


                    <li class="nav-item">

                        <a class="nav-link" data-toggle="pill" href="#tab-lain">

                            <i class="fas fa-list-alt mr-1"></i>
                            Lain-lain

                        </a>

                    </li>

                </ul>

            </div>


            <div class="card-body p-0">

                <div class="tab-content">


                    {{-- ====================================================== --}}
                    {{-- TAB LAB --}}
                    {{-- ====================================================== --}}

                    <div class="tab-pane fade show active" id="tab-lab">
                        <div class="p-2 border-bottom bg-light">

                            <button type="button" class="btn btn-success btn-sm" onclick="openInsertLab()"
                                @if (!empty($pasien->TglByr)) disabled @endif
                                title="{{ !empty($pasien->TglByr) ? 'Transaksi sudah dibayar' : 'Tambah Laboratorium' }}">

                                <i class="fas fa-plus-circle mr-1"></i>
                                Tambah Laboratorium

                            </button>

                        </div>

                        <div class="table-responsive">

                            <table class="table table-bordered table-striped mb-0">

                                <thead>

                                    <tr>
                                        <th>ID Lab</th>
                                        <th>Tanggal</th>
                                        <th>Dokter</th>
                                        <th class="text-right">
                                            Total
                                        </th>
                                    </tr>

                                </thead>


                                <tbody>

                                    @forelse ($lab as $l)
                                        @php

                                            $totalLab = collect($labDetail[$l->IDLab] ?? [])->sum('Biaya');

                                        @endphp


                                        <tr class="row-click" onclick="openDetailLab({{ $l->IDLab }})">

                                            <td>
                                                {{ $l->IDLab ?? '-' }}
                                            </td>

                                            <td>
                                                {{ $l->TLab ? date('d/m/Y', strtotime($l->TLab)) : '-' }}
                                            </td>

                                            <td>
                                                {{ $l->Dokter ?? '-' }}
                                            </td>

                                            <td class="text-right font-weight-bold">

                                                Rp
                                                {{ number_format($totalLab, 0, ',', '.') }}

                                            </td>

                                        </tr>

                                    @empty

                                        <tr>

                                            <td colspan="4" class="text-center text-muted py-4">

                                                Tidak ada data laboratorium.

                                            </td>

                                        </tr>
                                    @endforelse

                                </tbody>

                            </table>

                        </div>



                    </div>



                    {{-- ====================================================== --}}
                    {{-- TAB LAIN-LAIN --}}
                    {{-- ====================================================== --}}

                    <div class="tab-pane fade" id="tab-lain">

                        <div class="table-responsive">

                            <table class="table table-bordered table-striped mb-0">

                                <thead>

                                    <tr>

                                        <th>No</th>

                                        <th>Nama</th>

                                        <th>Tanggal</th>

                                        <th class="text-right">
                                            Tarif
                                        </th>

                                        <th class="text-right">
                                            Disc (%)
                                        </th>

                                        <th class="text-right">
                                            Jml Disc
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    @forelse ($lainlain as $l)
                                        <tr class="row-click"
                                            onclick="openEditLain(
                                            '{{ $l->Lain_ID }}',
                                            '{{ $l->TGL ? date('Y-m-d', strtotime($l->TGL)) : '' }}',
                                            @js($l->Lain ?? ''),
                                            '{{ $l->BiayaLain ?? 0 }}',
                                            '{{ ($l->Pot ?? 0) * 100 }}'
                                        )">

                                            <td>
                                                {{ $l->Lain_ID ?? '-' }}
                                            </td>

                                            <td>
                                                {{ $l->Lain ?? '-' }}
                                            </td>

                                            <td>

                                                {{ $l->TGL ? date('d/m/Y', strtotime($l->TGL)) : '-' }}

                                            </td>

                                            <td class="text-right">

                                                Rp
                                                {{ number_format($l->BiayaLain ?? 0, 0, ',', '.') }}

                                            </td>

                                            <td class="text-right">

                                                {{ number_format(($l->Pot ?? 0) * 100, 2) }}%

                                            </td>

                                            <td class="text-right">

                                                Rp
                                                {{ number_format(($l->BiayaLain ?? 0) * ($l->Pot ?? 0), 0, ',', '.') }}

                                            </td>

                                        </tr>

                                    @empty

                                        <tr>

                                            <td colspan="6" class="text-center text-muted py-4">

                                                Data biaya lain-lain belum tersedia.

                                            </td>

                                        </tr>
                                    @endforelse

                                </tbody>


                                <tfoot>

                                    <tr class="font-weight-bold bg-light">

                                        <td colspan="2">

                                            <button type="button" class="btn btn-success btn-sm"
                                                onclick="openInsertLain()">

                                                <i class="fas fa-plus-circle mr-1"></i>

                                                Tambah Lain-lain

                                            </button>

                                        </td>


                                        <td class="text-right">
                                            Total
                                        </td>


                                        <td class="text-right">

                                            Rp
                                            {{ number_format(collect($lainlain)->sum('BiayaLain'), 0, ',', '.') }}

                                        </td>


                                        <td></td>


                                        <td class="text-right">

                                            Rp
                                            {{ number_format(
                                                collect($lainlain)->sum(function ($l) {
                                                    return ($l->BiayaLain ?? 0) * ($l->Pot ?? 0);
                                                }),
                                                0,
                                                ',',
                                                '.',
                                            ) }}

                                        </td>

                                    </tr>

                                </tfoot>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- MODALS --}}
        @include('rawatinap.modal.modal-ri-lab')
        @include('rawatinap.modal.modal-ri-pilih-lab')
        @include('rawatinap.modal.modal-ri-file-lab')
        @include('rawatinap.modal.modal-ri-lain')
        @include('rawatinap.modal.modal-ri-sep')
        @include('rawatinap.modal.modal-ri-pxrs')


    @endif




@stop

@section('css')

    <style>
        /* WARNA SAMA DENGAN RAWAT INAP */

        .pasien-header,
        #inapDetailTabs .nav-link.active,
        table thead th,
        .modal-modern {

            background: #3f66d6 !important;
            color: white !important;

        }


        table thead th {

            white-space: nowrap;
            vertical-align: middle;

        }


        table tbody td {

            vertical-align: middle;

        }


        .row-click {

            cursor: pointer;

        }


        .row-click:hover td {

            background: #eef2ff !important;

        }


        #inapDetailTabs .nav-link {

            color: #3f66d6;

        }


        #inapDetailTabs .nav-link.active {

            border-color: #3f66d6 !important;

        }


        .kategori-lab {

            background: #eef2ff;

            color: #3457c0;

            font-weight: 700;

        }


        .table-responsive::-webkit-scrollbar {

            height: 9px;

        }


        .table-responsive::-webkit-scrollbar-track {

            background: #eef2ff;

            border-radius: 10px;

        }


        .table-responsive::-webkit-scrollbar-thumb {

            background: #8fa8f2;

            border-radius: 10px;

        }


        .table-responsive::-webkit-scrollbar-thumb:hover {

            background: #3f66d6;

        }

        .sep-print-preview {
            background: #fff;
            color: #000;
            padding: 20px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
        }

        .sep-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .sep-logo img {
            width: 240px;
            height: auto;
        }

        .sep-title {
            flex: 1;
            text-align: center;
            line-height: 1.2;
            font-weight: 800;
        }

        .sep-title div {
            font-size: 28px;
        }

        .sep-title small {
            display: block;
            font-size: 22px;
            margin-top: 5px;
            font-weight: 800;
        }

        .sep-table td {
            border: none !important;
            padding: 2px 4px !important;
            vertical-align: top;
            font-size: 15px;
        }

        .sep-table td:first-child {
            width: 140px;
            white-space: nowrap;
        }

        .sep-table td:nth-child(2) {
            width: 10px;
        }

        .sep-note {
            margin-top: 10px;
            font-size: 13px;
            line-height: 1.6;
        }

        #modalFormLab .modal-dialog {
            max-width: 1500px !important;
        }

        #modalFormLab .form-group {
            margin-bottom: 7px;
        }

        #modalFormLab label {
            font-size: 14px;
            font-weight: 600;
        }

        #modalFormLab .form-control-sm {
            font-size: 14px;
        }

        #modalFormLab .modal-body {
            font-size: 15px;
        }

        .lab-info-box {
            min-height: 95px;
            padding: 8px 10px;
            border: 1px solid #dbe3f5;
            background: #f8faff;
            border-radius: 5px;
        }

        .lab-table-wrap {
            max-height: 420px;
            overflow-y: auto;
        }

        #tableInputLab thead th {
            position: sticky;
            top: 0;
            z-index: 2;

            background: #3f66d6 !important;
            color: white !important;

            vertical-align: middle;
        }

        #tableInputLab td {
            vertical-align: middle;
        }

        #labTotalBiaya,
        #labTotalPotongan {
            font-size: 18px;
        }

        .lab-info-box {
            min-height: 150px;
            max-height: 210px;
            overflow-y: auto;

            padding: 10px 12px;

            border: 1px solid #dbe3f5;
            background: #f8faff;
            border-radius: 5px;
        }

        #labInfoPemeriksaan {
            font-size: 13px;
            line-height: 1.2;
            font-weight: 400;
        }

        #modalFormLab .modal-footer {
            width: 100%;
            border-top: 1px solid #dee2e6;
            padding: 12px 20px;
            background: #fff;
        }

        #modalFormLab .modal-footer .btn {
            min-width: 100px;
        }

        .umur-modern {
            display: flex;
            gap: 6px;
            width: 100%;
        }

        .umur-box {
            flex: 1;
            min-width: 0;

            background: #f7f9ff;
            border: 1px solid #dbe3f5;
            border-radius: 8px;

            padding: 5px 4px;

            text-align: center;

            transition: all .2s ease;
        }

        .umur-box:hover {
            background: #eef2ff;
            border-color: #aebff3;
        }

        .umur-value {
            font-size: 14px;
            font-weight: 700;
            color: #3f66d6;
            line-height: 1.2;
        }

        .umur-label {
            margin-top: 1px;
            font-size: 9px;
            font-weight: 600;
            color: #8a94a6;
            text-transform: uppercase;
            letter-spacing: .2px;
        }

        /* FOTO LAB */
        .foto-lab-card {
            position: relative;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            background: #fff;
            padding: 6px;
            height: 100%;
        }

        .foto-lab-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 6px;
            cursor: pointer;
        }

        .foto-lab-name {
            font-size: 11px;
            margin-top: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .foto-lab-delete {
            position: absolute;
            top: 10px;
            right: 10px;
        }

        #tableInputLab .checkbox-kritis {
            accent-color: #dc3545;
        }
    </style>

@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @include('rawatinap.js.js-ri-global')

    @include('rawatinap.js.js-ri-lab-main')

    @include('rawatinap.js.js-ri-lab-group')

    @include('rawatinap.js.js-ri-lab-sp')

    @include('rawatinap.js.js-ri-lab-lis')

    @include('rawatinap.js.js-ri-lab-file')

    @include('rawatinap.js.js-ri-lain')

    @include('rawatinap.js.js-ri-sep')

    @include('rawatinap.js.js-ri-pxrs')

@stop
