<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LisController extends Controller
{
    public function registrasi(Request $request, LisService $lisService)
    {
        $validator = Validator::make($request->all(), [
            'registrasi' => 'required|array',
            'registrasi.no_registrasi' => 'required|string',
            'registrasi.diagnosa_awal' => 'nullable|string',
            'registrasi.keterangan_klinis' => 'nullable|string',

            'pasien' => 'required|array',
            'pasien.nama' => 'required|string',
            'pasien.no_rm' => 'required|string',
            'pasien.jenis_kelamin' => 'required|in:L,P',
            'pasien.tanggal_lahir' => 'required|date_format:Y-m-d',
            'pasien.nik' => 'nullable|digits:16',
            'pasien.jenis_registrasi' => 'required|in:reguler,cito',

            'kode_dokter_pengirim' => 'required|string',
            'nama_dokter_pengirim' => 'required|string',
            'kode_unit_asal' => 'required|string',
            'nama_unit_asal' => 'required|string',

            'tindakan' => 'required|array|min:1',
            'tindakan.*.kode_tindakan' => 'required|string',
            'tindakan.*.nama_tindakan' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        $data = $request->all();

        $data['registrasi']['kode_rs'] =
            config('services.lis.kode_rs');

        $result = $lisService->registrasi($data);

        $status = $result['status'] ?? 500;

        if ($status < 100 || $status > 599) {
            $status = 500;
        }

        return response()->json($result, $status);
    }

    public function registrasiSP(int $idLab, LisService $lisService)
    {
        $result = $lisService->registrasiFromSP($idLab);
    
        $status = (int) ($result['status'] ?? 500);
    
        if ($status < 100 || $status > 599) {
            $status = 500;
        }
    
        return response()->json($result, $status);
    }

    public function getRegistrasiSP(int $idLab, LisService $lisService)
    {
        $result = $lisService->getRegistrasiFromSP($idLab);

        $status = (int) ($result['status'] ?? 500);

        if ($status < 100 || $status > 599) {
            $status = 500;
        }

        if (!($result['success'] ?? false)) {
            return response()->json($result, $status);
        }

        // Kembalikan JSON murni sesuai format LIS Adam
        return response()->json(
            $result['data'],
            200,
            [],
            JSON_UNESCAPED_UNICODE
        );
    }
    
    public function batalOrder(Request $request, LisService $lisService)
    {
        $validator = Validator::make($request->all(), [
            'pasien_no_lab' => 'required|string|max:80',
            'pasien_no_registrasi' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $result = $lisService->batalOrder(
            $request->input('pasien_no_lab'),
            $request->input('pasien_no_registrasi')
        );

        // Gunakan HTTP 422 untuk penolakan dari LIS,
        // meskipun body LIS mengandung status 200.
        if ($result['success'] === true) {
            $httpStatus = 200;
        } elseif (($result['status'] ?? null) === 503) {
            $httpStatus = 503;
        } elseif (($result['status'] ?? null) === 500) {
            $httpStatus = 500;
        } else {
            $httpStatus = 422;
        }

        return response()->json($result, $httpStatus);
    }


}