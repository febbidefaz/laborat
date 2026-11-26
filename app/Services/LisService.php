<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Throwable;

class LisService
{
    public function registrasi(array $data): array
    {
        $url = rtrim(
            config('services.lis.base_url'),
            '/'
        ) . '/adam-lis/bridging_sim_rs/registrasi';
    
        try {
            $response = Http::acceptJson()
                ->asJson()
                ->withHeaders([
                    'x-api-key' => config('services.lis.api_key'),
                ])
                ->connectTimeout(10)
                ->timeout((int) config('services.lis.timeout', 30))
                ->post($url, $data);
    
            $result = $response->json();
    
            // Respons bukan JSON valid
            if (!is_array($result)) {
                return [
                    'success' => false,
                    'status' => 502,
                    'message' => 'Respons LIS bukan JSON valid.',
                ];
            }
    
            // RESPONS SUKSES
            if (
                $response->successful()
                && ($result['success'] ?? false) === true
            ) {
                return [
                    'success' => true,
                    'status' => 200,
                    'message' => $result['message']
                        ?? 'Registrasi Berhasil',
                    'payload' => $result['payload'] ?? [],
                ];
            }
    
            // RESPONS GAGAL
            $message = $result['message'] ?? 'Registrasi LIS gagal';
    
            // Jika message berupa array (gagal mapping)
            if (is_array($message)) {
                $errors = [];
    
                foreach ($message as $item) {
                    $errors[] = [
                        'value' => $item['value'] ?? null,
                        'msg' => $item['msg'] ?? 'Kesalahan mapping',
                        'param' => $item['param'] ?? null,
                    ];
                }
    
                return [
                    'success' => false,
                    'status' => 422,
                    'message' => $errors,
                ];
            }
    
            // Jika message berupa string (gagal umum)
            return [
                'success' => false,
                'status' => 422,
                'message' => (string) $message,
            ];
    
        } catch (ConnectionException $e) {
    
            Log::error('Koneksi LIS gagal', [
                'error' => $e->getMessage(),
            ]);
    
            return [
                'success' => false,
                'status' => 503,
                'message' => 'Tidak dapat terhubung ke server LIS.',
            ];
    
        } catch (Throwable $e) {
    
            Log::error('Kesalahan registrasi LIS', [
                'error' => $e->getMessage(),
            ]);
    
            return [
                'success' => false,
                'status' => 500,
                'message' => 'Terjadi kesalahan integrasi LIS.',
            ];
        }
    }
    
    public function getRegistrasiFromSP(int $idLab): array
    {
        try {
            // 1. Ambil data dari Stored Procedure SQL Server
            $rows = DB::connection('sqlsrv')
                ->select(
                    'EXEC dbo.WebApiLab_SP @IDLab = ?',
                    [$idLab]
                );

            if (empty($rows)) {
                return [
                    'success' => false,
                    'status' => 404,
                    'message' => 'Data laboratorium tidak ditemukan.',
                ];
            }

            // 2. Ambil data utama dari baris pertama
            $lab = $rows[0];

            // 3. Susun daftar tindakan dari seluruh baris SP
            $tindakan = [];

            foreach ($rows as $row) {
                if (
                    $row->kode_tindakan === null ||
                    $row->kode_tindakan === ''
                ) {
                    continue;
                }

                $kode = (string) $row->kode_tindakan;

                // Hindari tindakan duplikat akibat JOIN SP
                if (!isset($tindakan[$kode])) {
                    $tindakan[$kode] = [
                        'kode_tindakan' => $kode,
                        'nama_tindakan' => (string) $row->nama_tindakan,
                    ];
                }
            }

            if (empty($tindakan)) {
                return [
                    'success' => false,
                    'status' => 422,
                    'message' => 'Tidak ada tindakan laboratorium.',
                ];
            }

            // 4. Format tanggal lahir
            $tanggalLahir = null;

            if (!empty($lab->tanggal_lahir)) {
                $tanggalLahir = Carbon::parse(
                    $lab->tanggal_lahir
                )->format('Y-m-d');
            }

            // 5. Normalisasi jenis kelamin
            $jk = strtoupper(trim(
                (string) ($lab->jenis_kelamin ?? '')
            ));

            $jenisKelamin = match ($jk) {
                'L', 'LAKI-LAKI', 'LAKI LAKI', 'M' => 'L',
                'P', 'PEREMPUAN', 'WANITA', 'F' => 'P',
                default => null,
            };

            if ($jenisKelamin === null) {
                return [
                    'success' => false,
                    'status' => 422,
                    'message' => 'Jenis kelamin pasien tidak valid.',
                ];
            }

            // 6. Susun JSON sesuai format LIS ADAM
            $data = [
                'registrasi' => [
                    'no_registrasi' => (string) $lab->no_registrasi,
                    'diagnosa_awal' => (string) ($lab->diagnosa_awal ?: '-'),
                    'keterangan_klinis' => (string) ($lab->keterangan_klinis ?: '-'),
                    'kode_rs' => (string) $lab->kode_rs,
                ],

                'pasien' => [
                    'nama' => (string) $lab->Nama,
                    'no_rm' => (string) $lab->no_rm,
                    'jenis_kelamin' => $jenisKelamin,
                    'alamat' => (string) ($lab->alamat ?? '-'),
                    'no_telphone' => (string) ($lab->no_telphone ?: '-'),
                    'tanggal_lahir' => $tanggalLahir,
                    'nik' => (string) ($lab->nik ?? ''),
                    'ras' => (string) ($lab->ras ?? '-'),
                    'berat_badan' => (string) ($lab->berat_badan ?? '-'),
                    'jenis_registrasi' => (string) $lab->jenis_registrasi,
                    'm_provinsi_id' => (string) ($lab->m_provinsi_id ?? ''),
                    'm_kabupaten_id' => (string) ($lab->m_kabupaten_id ?? ''),
                    'm_kecamatan_id' => (string) ($lab->m_kecamatan_id ?? ''),
                ],

                'kode_dokter_pengirim' =>
                    (string) $lab->kode_dokter_pengirim,

                'nama_dokter_pengirim' =>
                    (string) $lab->nama_dokter_pengirim,

                'kode_unit_asal' =>
                    (string) $lab->kode_unit_asal,

                'nama_unit_asal' =>
                    (string) $lab->nama_unit_asal,

                'kode_penjamin' =>
                    (string) $lab->kode_penjamin,

                'nama_penjamin' =>
                    (string) $lab->nama_penjamin,

                'kode_icdt' =>
                    (string) ($lab->kode_icdt ?? '-'),

                'nama_icdt' =>
                    (string) ($lab->nama_icdt ?? '-'),

                'tindakan' => array_values($tindakan),
            ];

                // 7. Kembalikan JSON hasil Stored Procedure
                return [
                    'success' => true,
                    'status' => 200,
                    'data' => $data,
                ];

        } catch (\Throwable $e) {

            \Log::error('Registrasi LIS dari SP gagal', [
                'id_lab' => $idLab,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 500,
                'message' => 'Gagal memproses registrasi laboratorium.',
            ];
        }
    }

    public function registrasiFromSP(int $idLab): array
    {
        $result = $this->getRegistrasiFromSP($idLab);

        if (!($result['success'] ?? false)) {
            return $result;
        }

        return $this->registrasi($result['data']);
    }
        
    public function batalOrder(string $noLab, string $noRegistrasi): array 
    {

        $baseUrl = trim(
            (string) config('services.lis.base_url', '')
        );

        $apiKey = trim(
            (string) config('services.lis.api_key', '')
        );

        if ($baseUrl === '' || $apiKey === '') {
            return [
                'success' => false,
                'status' => 500,
                'message' => 'Konfigurasi LIS belum lengkap.',
            ];
        }

        $url = rtrim($baseUrl, '/')
            . '/adam-lis/bridging_sim_rs/recyclebin';

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return [
                'success' => false,
                'status' => 500,
                'message' => 'URL endpoint LIS tidak valid.',
            ];
        }

        $data = [
            'pasien_no_lab' => trim($noLab),
            'pasien_no_registrasi' => trim($noRegistrasi),
        ];

        try {

            $response = Http::acceptJson()
                ->asJson()
                ->withHeaders([
                    'x-api-key' => $apiKey,
                ])
                ->connectTimeout(10)
                ->timeout((int) config('services.lis.timeout', 30))
                ->post($url, $data);

            $result = $response->json();

            // Catat status dan pesan tanpa data pasien/API key
            Log::info('Respons batal order LIS', [
                'http_status' => $response->status(),
                'lis_success' => is_array($result)
                    ? ($result['success'] ?? null)
                    : null,
                'lis_status' => is_array($result)
                    ? ($result['status'] ?? null)
                    : null,
            ]);

            if (!is_array($result)) {
                return [
                    'success' => false,
                    'status' => 502,
                    'message' => 'Respons LIS bukan JSON valid.',
                ];
            }

            if (
                $response->successful()
                && ($result['success'] ?? false) === true
            ) {
                return [
                    'success' => true,
                    'status' => (int) ($result['status'] ?? 200),
                    'message' => $result['message']
                        ?? 'Hapus registrasi berhasil',
                    'payload' => $result['payload'] ?? [],
                ];
            }

            return [
                'success' => false,
                'status' => (int) (
                    $result['status'] ?? $response->status()
                ),
                'message' => $result['message']
                    ?? 'Pembatalan order LIS gagal.',
            ];

        } catch (ConnectionException $e) {

            Log::error('Koneksi LIS gagal', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 503,
                'message' => 'Tidak dapat terhubung ke server LIS.',
            ];

        } catch (Throwable $e) {

            Log::error('Batal order LIS gagal', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 500,
                'message' => 'Terjadi kesalahan integrasi LIS.',
            ];
        }
    }

    

}