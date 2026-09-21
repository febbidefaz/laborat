<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>SP Laboratorium</title>

    <style>
        @page {
            size: 100mm auto;
            margin: 4mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            color: #000;
        }

        .print-box {
            width: 92mm;
            margin: 0 auto;
        }

        .title {
            background: #d9e8f8;
            font-size: 18px;
            padding: 5px 4px;
            margin-bottom: 3px;
        }

        .patient-top {
            display: flex;
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 2px;
        }

        .patient-top>div {
            margin-right: 22px;
        }

        .name-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .patient-name {
            font-weight: bold;
        }

        .address {
            margin-bottom: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .exam-table {
            margin-top: 3px;
        }

        .exam-table thead th {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 4px 3px;
            font-size: 12px;
        }

        .exam-table tbody td {
            padding: 4px 3px;
            border-bottom: 1px solid #ddd;
        }

        .no {
            width: 35px;
            text-align: right;
            padding-right: 8px !important;
        }

        .sp-number {
            width: 70px;
            text-align: right;
            font-weight: bold;
        }

        .info-table {
            margin-top: 6px;
        }

        .info-table td {
            vertical-align: top;
            padding: 3px 2px;
        }

        .info-label {
            width: 75px;
        }

        .colon {
            width: 10px;
        }

        @media print {

            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
            }
        }
    </style>

</head>

<body>

    <div class="print-box">

        <div class="title">
            SP Laborat
        </div>


        <div class="patient-top">

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


        <div class="name-row">

            <div class="patient-name">
                {{ $header->Nama ?? '-' }}
            </div>

            <div>
                BB :
                <strong>
                    {{ $header->BB ?? '-' }}
                </strong>
            </div>

        </div>


        <div class="address">
            {{ $header->Addr ?? '-' }}
        </div>


        <table class="exam-table">

            <thead>

                <tr>

                    <th width="35">
                        No
                    </th>

                    <th style="text-align:left">
                        Pemeriksaan
                    </th>

                    <th class="sp-number">
                        {{ $header->NO ?? '' }}
                    </th>

                </tr>

            </thead>


            <tbody>

                @foreach ($items as $index => $item)
                    <tr>

                        <td class="no">
                            {{ $index + 1 }}
                        </td>

                        <td colspan="2">
                            {{ $item->PeriksaLab ?? '-' }}
                        </td>

                    </tr>
                @endforeach

            </tbody>

        </table>


        <table class="info-table">

            <tr>

                <td class="info-label">
                    Pengirim
                </td>

                <td class="colon">
                    :
                </td>

                <td>
                    {{ $header->Dokter ?? '-' }}
                </td>

            </tr>


            <tr>

                <td class="info-label">
                    Diagnosa
                </td>

                <td class="colon">
                    :
                </td>

                <td>
                    {{ $header->Diagnosa ?? '-' }}
                </td>

            </tr>


            @if (!empty($header->RoomName))
                <tr>

                    <td class="info-label">
                        Ruang
                    </td>

                    <td class="colon">
                        :
                    </td>

                    <td>
                        {{ $header->RoomName }}
                    </td>

                </tr>
            @endif

        </table>

    </div>


    <script>
        window.onload = function() {

            window.print();

        };
    </script>

</body>

</html>
