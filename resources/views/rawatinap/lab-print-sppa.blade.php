<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <title>SP Laborat PA</title>

    <style>
        @page {
            size: 100mm auto;
            margin: 4mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
        }

        .print-box {
            width: 92mm;
            margin: 0 auto;
        }

        .title-row {
            display: flex;
            justify-content: space-between;
            align-items: center;

            background: #dbe8f6;
            padding: 5px 4px;

            font-size: 17px;
            margin-bottom: 2px;
        }

        .title-no {
            font-weight: bold;
            font-size: 13px;
        }

        .patient-meta {
            display: flex;
            gap: 28px;

            font-size: 13px;
            font-weight: bold;

            margin-bottom: 4px;
        }

        .patient-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 7px;
        }

        .address {
            margin-bottom: 7px;
        }

        .room {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .divider {
            border-top: 2px solid #000;
            margin-bottom: 5px;
        }

        .field {
            margin-bottom: 10px;
        }

        .field-label {
            font-weight: bold;
            margin-bottom: 2px;
        }

        .field-value {
            min-height: 16px;
            padding-left: 2px;
        }

        .label-inline {
            display: flex;
            align-items: flex-start;
            margin-bottom: 6px;
        }

        .label-inline .label {
            width: 85px;
            font-weight: bold;
        }

        .label-inline .colon {
            width: 12px;
        }

        .label-inline .value {
            flex: 1;
        }

        .footer-divider {
            border-top: 1px solid #ddd;
            margin: 8px 0 5px;
        }

        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>

<body>

    <div class="print-box">

        {{-- HEADER --}}
        <div class="title-row">

            <div>
                SP Laborat PA
            </div>

            <div class="title-no">
                {{ $header->NO ?? '-' }}
            </div>

        </div>


        {{-- IDENTITAS --}}
        <div class="patient-meta">

            <div>
                {{ $header->RegNum ?? '-' }}
            </div>

            <div>
                {{ !empty($header->Tanggal_Lahir) ? date('d.m.y', strtotime($header->Tanggal_Lahir)) : '-' }}
            </div>

            <div>
                {{ !empty($header->Tanggal_Lahir) ? date('d/m/Y', strtotime($header->Tanggal_Lahir)) : '-' }}
            </div>

        </div>


        <div class="patient-name">
            {{ $header->Nama ?? '-' }}
        </div>


        <div class="address">
            {{ $header->Addr ?? '-' }}
        </div>


        <div class="room">
            {{ $header->RoomName ?? '-' }}
        </div>


        <div class="divider"></div>


        {{-- LOKASI ORGAN --}}
        <div class="field">

            <div class="field-label">
                Lokasi Organ
            </div>

            <div class="field-value">
                {{ $header->LokasiOrgan ?? '' }}
            </div>

        </div>


        {{-- DIAGNOSA KLINIK --}}
        <div class="field">

            <div class="field-label">
                Diagnosa Klinik &nbsp;&nbsp;:
            </div>

            <div class="field-value">
                {{ $header->DiagnosaKlinik ?? '' }}
            </div>

        </div>


        {{-- FIKSATIF --}}
        <div class="field">

            <div class="field-label">
                Fiksatif &nbsp;&nbsp;&nbsp;&nbsp;:
            </div>

            <div class="field-value">
                {{ $header->Fiksatif ?? '' }}
            </div>

        </div>


        {{-- BIOPSI --}}
        <div class="field">

            <div class="field-label">
                Biopsi/ Operasi/ Kerokan &nbsp;&nbsp;:
            </div>

            <div class="field-value">
                {{ $header->Biopsi ?? '' }}
            </div>

        </div>


        {{-- BAHAN --}}
        <div class="field">

            <div class="field-label">
                Sputurn/ Urine/ Smear/ Cairan Bahan Fiksatif :
            </div>

            <div class="field-value">
                {{ $header->Sputurn ?? '' }}
            </div>

        </div>


        {{-- KETERANGAN KLINIK --}}
        <div class="field">

            <div class="field-label">
                Keterangan Klinik :
            </div>

            <div class="field-value">
                {{ $header->KetKlinik ?? '' }}
            </div>

        </div>


        {{-- RIWAYAT LAB --}}
        <div class="field">

            <div class="field-label">
                Riwayat Lab :
            </div>

            <div class="field-value">
                {{ $header->RiwayatLab ?? '' }}
            </div>

        </div>


        <div class="footer-divider"></div>


        {{-- JAM OP --}}
        <div class="label-inline">

            <div class="label">
                Jam Op
            </div>

            <div class="colon">
                :
            </div>

            <div class="value">
                {{ $header->JamOp ?? '' }}
            </div>

        </div>


        {{-- JAM SAMPEL --}}
        <div class="label-inline">

            <div class="label">
                Jam Sampel
            </div>

            <div class="colon">
                :
            </div>

            <div class="value">
                {{ $header->JamSampel ?? '' }}
            </div>

        </div>


        {{-- PENGIRIM --}}
        <div class="label-inline">

            <div class="label">
                Pengirim
            </div>

            <div class="colon">
                :
            </div>

            <div class="value">
                {{ $header->Dokter ?? '-' }}
            </div>

        </div>

    </div>


    <script>
        window.onload = function() {
            window.print();
        };
    </script>

</body>

</html>
