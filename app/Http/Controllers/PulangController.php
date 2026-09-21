<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PulangController extends Controller
{
    public function index()
    {
        return view('pulang');
    }

    public function data(Request $request)
    {
        try {
            $request->validate([
                'tgl_awal'  => 'nullable|date',
                'tgl_akhir' => 'nullable|date|after_or_equal:tgl_awal',
            ]);
    
            $tglAwal = $request->tgl_awal ?? date('Y-m-d');
            $tglAkhir = $request->tgl_akhir ?? date('Y-m-d');
    
            $data = DB::select("
                SET NOCOUNT ON;
                EXEC dbo.WebDaftarPasienPulang_SP ?, ?
            ", [
                $tglAwal,
                $tglAkhir
            ]);
    
            return response()->json([
                'data' => $data ?: []
            ], 200);
    
        } catch (\Exception $e) {
            Log::error('PULANG DATA ERROR : ' . $e->getMessage());
    
            return response()->json([
                'data' => [],
                'message' => 'Data tidak ditemukan atau gagal dimuat.'
            ], 200);
        }
    }

    public function show($id)
    {
        return 'Detail pasien ID: ' . $id;
    }

    public function detail($id)
    {
        $pasien = DB::selectOne("EXEC dbo.WebPasienRawatInapDetailByID_SP ?", [$id]);

        if (!$pasien) {
            abort(404, 'Pasien tidak ditemukan');
        }
    
        // LAB
        $lab = DB::select(
            "EXEC dbo.WeblaboratByIDReg_SP ?",
            [$id]
        );
    
        $labDetail = [];
    
        foreach ($lab as $l) {
            $labDetail[$l->IDLab] = DB::select(
                "EXEC dbo.WebLaboratDetailByIDLab_SP ?",
                [$l->IDLab]
            );
        }
    
        // LAIN-LAIN
        $lainlain = DB::select(
            "EXEC dbo.WebLainBillingByID_SP ?",
            [$id]
        );

        $dokterList = DB::select("
            SET NOCOUNT ON;
            EXEC dbo.cboDokter_SP
        ");

        $dokterSpPKList = DB::select("
            SET NOCOUNT ON;
            EXEC dbo.cboDokterSpPK_SP
        ");

        $spLabList = DB::select("
            SET NOCOUNT ON;
            EXEC dbo.cboSPLabNew_SP ?
        ", [
            $pasien->RegNum
        ]);

        $spPaList = DB::select("
            SET NOCOUNT ON;
            EXEC dbo.cboSPLabPA_SP
        ");

        //Get Upx
        $upxList = DB::select("EXEC dbo.cboUpx_sp");
            
        return view('pulang.pulangdetail', compact(
            'pasien',
            'lab',
            'labDetail',
            'lainlain',
            'dokterList',
            'dokterSpPKList',
            'spLabList',
            'spPaList',
            'upxList'
        ));
        
    }
}
