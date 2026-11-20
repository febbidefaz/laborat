<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class LisHasilController extends Controller
{
    public function terimaHasil(Request $request)
    {
        // ==========================================
        // 1. VALIDASI FORMAT JSON
        // ==========================================

        $rawJson = $request->getContent();
        $data = json_decode($rawJson, true);

        if (
            json_last_error() !== JSON_ERROR_NONE ||
            !is_array($data) ||
            array_is_list($data)
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Data gagal disimpan karena format JSON tidak valid.'
            ], 400);
        }

        // ==========================================
        // 2. VALIDASI STRUKTUR JSON LIS
        // ==========================================

        $validator = Validator::make($data, [
            'no_registrasi' =>
                'required|string|max:50',

            'no_laboratorium' =>
                'required|string|max:80',

            'waktu_registrasi' =>
                'nullable|date_format:Y-m-d H:i:s',

            'pasien' =>
                'required|array',

            'pasien.nama_pasien' =>
                'required|string|max:200',

            'pasien.no_rm' =>
                'required|string|max:50',

            'pemeriksaan' =>
                'required|array|min:1',

            'pemeriksaan.*.kode_tindakan_simrs' =>
                'required|string|max:50',

            'pemeriksaan.*.kode_pemeriksaan_lis' =>
                'required|string|max:100',

            'pemeriksaan.*.nama_pemeriksaan_lis' =>
                'required|string|max:250',

            'pemeriksaan.*.hasil' =>
                'required|array',

            'pemeriksaan.*.hasil.nilai_hasil' =>
                'nullable|string|max:1000',

            'pemeriksaan.*.hasil.satuan' =>
                'nullable|string|max:80',

            'pemeriksaan.*.hasil.nilai_rujukan' =>
                'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Data gagal disimpan karena ' .
                    $validator->errors()->first()
            ], 422);
        }

        // ==========================================
        // 3. PROSES DATABASE SQL SERVER
        // ==========================================

        try {

            $db = DB::connection('sqlsrv');

            $result = $db->transaction(function () use (
                $db,
                $data,
                $rawJson
            ) {

                $noRegistrasi =
                    (string) $data['no_registrasi'];

                $noLaboratorium =
                    (string) $data['no_laboratorium'];

                // ==================================
                // 4. CEK DUPLIKASI POST
                // ==================================

                $sudahPost = $db
                    ->table('dbo.LIS_HasilInbox')
                    ->where(
                        'NoRegistrasiSIMRS',
                        $noRegistrasi
                    )
                    ->where(
                        'NoLaboratoriumLIS',
                        $noLaboratorium
                    )
                    ->lockForUpdate()
                    ->first();

                if ($sudahPost) {
                    return [
                        'duplicate' => true,
                        'inbox_id' => $sudahPost->InboxID,
                    ];
                }

                // ==================================
                // 5. AMBIL DATA PASIEN DAN HASIL
                // ==================================

                $pasien = $data['pasien'];

                $pemeriksaan = $data['pemeriksaan'];

                // ==================================
                // 6. INSERT LIS_HASILINBOX
                // ==================================

                $inboxId = $db
                    ->table('dbo.LIS_HasilInbox')
                    ->insertGetId([

                        'NoRegistrasiSIMRS' =>
                            $noRegistrasi,

                        'NoLaboratoriumLIS' =>
                            $noLaboratorium,

                        'KodeRS' =>
                            $data['kode_rs'] ?? null,

                        'KodeLab' =>
                            $data['kode_lab'] ?? null,

                        'WaktuRegistrasiLIS' =>
                            $data['waktu_registrasi'] ?? null,

                        'NoRM' =>
                            (string) $pasien['no_rm'],

                        'NamaPasien' =>
                            (string) $pasien['nama_pasien'],

                        'KeteranganHasil' =>
                            $data['keterangan_hasil'] ?? null,

                        'DiagnosaAwal' =>
                            $data['diagnosa_awal'] ?? null,

                        'PayloadJSON' =>
                            $rawJson,

                        'StatusProses' =>
                            'DITERIMA',

                        'JumlahPemeriksaan' =>
                            count($pemeriksaan),

                        'JumlahTersinkron' =>
                            0,

                        'DiterimaPada' =>
                            now(),

                    ], 'InboxID');

                // ==================================
                // 7. INSERT LIS_HASILDETAIL
                // ==================================

                foreach ($pemeriksaan as $index => $item) {

                    $kodeTindakan =
                        (string) $item['kode_tindakan_simrs'];

                    $kodeLis =
                        (string) $item['kode_pemeriksaan_lis'];

                    // Cari mapping hasil
                    $mapping = $db
                        ->table('dbo.LIS_MappingHasil')
                        ->where(
                            'KodeTindakanSIMRS',
                            $kodeTindakan
                        )
                        ->where(
                            'KodePemeriksaanLIS',
                            $kodeLis
                        )
                        ->where('Aktif', 1)
                        ->first();

                    $mappingValid =
                        $mapping &&
                        !empty($mapping->KodeParameterSIMRS);

                    $hasil = $item['hasil'];

                    $db
                        ->table('dbo.LIS_HasilDetail')
                        ->insert([

                            'InboxID' =>
                                $inboxId,

                            'UrutanPayload' =>
                                $index + 1,

                            'KodeTindakanSIMRS' =>
                                $kodeTindakan,

                            'KodePemeriksaanLIS' =>
                                $kodeLis,

                            'NamaPemeriksaanLIS' =>
                                $item['nama_pemeriksaan_lis'],

                            'NamaKategori' =>
                                data_get(
                                    $item,
                                    'kategori_pemeriksaan.nama_kategori'
                                ),

                            'NamaSubKategori' =>
                                data_get(
                                    $item,
                                    'sub_kategori_pemeriksaan.nama_sub_kategori'
                                ),

                            'NomorUrut' =>
                                $item['nomor_urut'] ?? null,

                            'StatusBridgingLIS' =>
                                $item['status_bridging'] ?? null,

                            'StatusDuplo' =>
                                $item['status_duplo'] ?? null,

                            'TotalDuplo' =>
                                $item['total_duplo'] ?? null,

                            'NilaiHasil' =>
                                $hasil['nilai_hasil'] ?? null,

                            'Satuan' =>
                                $hasil['satuan'] ?? null,

                            'NilaiRujukan' =>
                                $hasil['nilai_rujukan'] ?? null,

                            'FlagKode' =>
                                $hasil['flag_kode'] ?? null,

                            'MappingID' =>
                                $mappingValid
                                    ? $mapping->MappingID
                                    : null,

                            'StatusSinkron' =>
                                $mappingValid
                                    ? 'TERPETAKAN'
                                    : 'PENDING',

                            'PesanError' =>
                                $mappingValid
                                    ? null
                                    : 'Mapping belum tersedia.',

                        ]);
                }

                // ==================================
                // 8. TRANSAKSI BERHASIL
                // ==================================

                return [
                    'duplicate' => false,
                    'inbox_id' => $inboxId,
                ];

            }, 3);

            // ======================================
            // 9. JIKA DATA SUDAH PERNAH DI-POST
            // ======================================

            if ($result['duplicate']) {

                return response()->json([
                    'success' => false,
                    'message' =>
                        'Data sudah di POST sebelumnya.'
                ], 409);
            }

            // ======================================
            // 10. RESPONS SUKSES SESUAI LIS
            // ======================================

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil Disimpan',
                'payload' => 'Message from simrs'
            ], 200);

        } catch (Throwable $e) {

            Log::error('POST hasil LIS gagal', [
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            return response()->json([
                'success' => false,
                'message' =>
                    'Data gagal disimpan karena terjadi kesalahan pada database SIMRS.'
            ], 500);
        }
    }

    
    public function updateHasil(Request $request)
    {
        $data = $request->json()->all();

        $validator = \Validator::make($data, [
            'no_registrasi' => 'required|string|max:50',
            'no_laboratorium' => 'required|string|max:80',
            'pasien.no_rm' => 'required|string|max:50',
            'pemeriksaan' => 'required|array|min:1',
            'pemeriksaan.*.kode_tindakan_simrs'
                => 'required|string|max:50',
            'pemeriksaan.*.kode_pemeriksaan_lis'
                => 'required|string|max:100',
            'pemeriksaan.*.hasil'
                => 'required|array',
            'pemeriksaan.*.hasil.nilai_hasil'
                => 'present|nullable|string|max:1000',
            'pemeriksaan.*.hasil.satuan'
                => 'sometimes|nullable|string|max:80',
            'pemeriksaan.*.hasil.nilai_rujukan'
                => 'sometimes|nullable|string|max:1000',
            'pemeriksaan.*.hasil.flag_kode'
                => 'sometimes|nullable|string|max:30',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data gagal disimpan karena ' .
                    $validator->errors()->first()
            ], 422);
        }

        try {
            $db = DB::connection('sqlsrv');

            $jumlahUpdate = $db->transaction(function () use (
                $db, $data
            ) {
                // Cari registrasi yang sudah pernah POST.
                $inboxes = $db->table('dbo.LIS_HasilInbox')
                    ->where(
                        'NoRegistrasiSIMRS',
                        $data['no_registrasi']
                    )
                    ->where(
                        'NoLaboratoriumLIS',
                        $data['no_laboratorium']
                    )
                    ->lockForUpdate()
                    ->get();

                if ($inboxes->count() !== 1) {
                    throw new \DomainException(
                        'Registrasi tidak ditemukan'
                    );
                }

                $inbox = $inboxes->first();

                if ((string) $inbox->NoRM !==
                    (string) $data['pasien']['no_rm']) {
                    throw new \DomainException(
                        'Nomor rekam medis tidak sesuai.'
                    );
                }

                $jumlah = 0;
                $sudahDiproses = [];

                foreach ($data['pemeriksaan'] as $item) {
                    $kodeTindakan =
                        (string) $item['kode_tindakan_simrs'];
                    $kodeLis =
                        (string) $item['kode_pemeriksaan_lis'];

                    $key = $kodeTindakan . '|' . $kodeLis;

                    if (isset($sudahDiproses[$key])) {
                        throw new \DomainException(
                            'Kode pemeriksaan berulang dalam request.'
                        );
                    }

                    $sudahDiproses[$key] = true;

                    $details = $db
                        ->table('dbo.LIS_HasilDetail')
                        ->where('InboxID', $inbox->InboxID)
                        ->where('KodeTindakanSIMRS', $kodeTindakan)
                        ->where('KodePemeriksaanLIS', $kodeLis)
                        ->lockForUpdate()
                        ->get();

                    if ($details->count() !== 1) {
                        throw new \DomainException(
                            'Detail pemeriksaan tidak ditemukan atau ambigu: '
                            . $kodeLis
                        );
                    }

                    $detail = $details->first();
                    $hasil = $item['hasil'];

                    $baru = [
                        'NilaiHasil' => array_key_exists(
                            'nilai_hasil', $hasil
                        ) ? $hasil['nilai_hasil'] : $detail->NilaiHasil,

                        'Satuan' => array_key_exists(
                            'satuan', $hasil
                        ) ? $hasil['satuan'] : $detail->Satuan,

                        'NilaiRujukan' => array_key_exists(
                            'nilai_rujukan', $hasil
                        ) ? $hasil['nilai_rujukan'] : $detail->NilaiRujukan,

                        'FlagKode' => array_key_exists(
                            'flag_kode', $hasil
                        ) ? $hasil['flag_kode'] : $detail->FlagKode,
                    ];

                    // Jika nilai tidak berubah, tidak perlu UPDATE.
                    if (
                        $detail->NilaiHasil === $baru['NilaiHasil'] &&
                        $detail->Satuan === $baru['Satuan'] &&
                        $detail->NilaiRujukan === $baru['NilaiRujukan'] &&
                        $detail->FlagKode === $baru['FlagKode']
                    ) {
                        continue;
                    }

                    // Simpan riwayat sebelum memperbarui detail.
                    $db->table('dbo.LIS_HasilRiwayat')->insert([
                        'InboxID' => $inbox->InboxID,
                        'DetailID' => $detail->DetailID,
                        'KodeTindakanSIMRS' => $kodeTindakan,
                        'KodePemeriksaanLIS' => $kodeLis,

                        'NilaiHasilLama' => $detail->NilaiHasil,
                        'NilaiHasilBaru' => $baru['NilaiHasil'],

                        'SatuanLama' => $detail->Satuan,
                        'SatuanBaru' => $baru['Satuan'],

                        'NilaiRujukanLama' => $detail->NilaiRujukan,
                        'NilaiRujukanBaru' => $baru['NilaiRujukan'],

                        'FlagKodeLama' => $detail->FlagKode,
                        'FlagKodeBaru' => $baru['FlagKode'],
                    ]);

                    // UPDATE hasil tampungan yang sudah ada.
                    $db->table('dbo.LIS_HasilDetail')
                        ->where('DetailID', $detail->DetailID)
                        ->update(array_merge($baru, [
                            'StatusSinkron' => 'DITAHAN',
                            'PesanError' =>
                                'Hasil diperbarui LIS; perlu verifikasi ulang.',
                            'DisinkronPada' => null,
                        ]));

                    $jumlah++;
                }

                if ($jumlah > 0) {
                    $db->table('dbo.LIS_HasilInbox')
                        ->where('InboxID', $inbox->InboxID)
                        ->update([
                            'StatusProses' => 'DITAHAN',
                            'DiprosesPada' => now(),
                        ]);
                }

                return $jumlah;
            });

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil Disimpan',
                'payload' => 'Message from simrs'
            ], 200);

        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data gagal disimpan karena ' .
                    $e->getMessage()
            ], 422);

        } catch (Throwable $e) {
            Log::error('UPDATE hasil LIS gagal', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' =>
                    'Data gagal disimpan karena kesalahan database SIMRS.'
            ], 500);
        }
    }

}
