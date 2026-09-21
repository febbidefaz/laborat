<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class SPController extends Controller
{
    public function index()
    {
        return view('sp');
    }

    public function data()
    {
        try {

            $data = DB::select("
                SET NOCOUNT ON;
                EXEC dbo.DaftarPlanLaborat_SP
            ");

            return response()->json([
                'data' => $data ?: []
            ]);

        } catch (\Throwable $e) {

            Log::error('SP LAB DATA ERROR', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return response()->json([
                'data' => [],
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        return 'Detail pasien ID: ' . $id;
    }

    public function detail($no)
    {
        // Ambil data SP berdasarkan NO
        $sp = DB::selectOne("
            SELECT TOP 1
                NO,
                ID,
                TGL,
                KetInap,
                Proses,
                KetCancel
            FROM ERM.dbo.LaboratPlan
            WHERE NO = ?
        ", [$no]);

        if (!$sp) {
            abort(404, 'Surat Permintaan tidak ditemukan');
        }

        // ID pasien dari SP
        $id = $sp->ID;

        // Simpan No SP secara eksplisit
        $noSP = $sp->NO;


        $pasien = DB::selectOne(
            "EXEC dbo.WebPasienRawatInapDetailByID_SP ?",
            [$id]
        );

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

         // Radiologi
        $radiologi = DB::select("EXEC dbo.WebRadiologiBillingByID_SP ?", [$id]);

        $radiologiDetail = [];

        foreach ($radiologi as $r) {
            $radiologiDetail[$r->IDRad] = DB::select(
                "EXEC dbo.WebRadiologiDetailByIDRad_SP ?",
                [$r->IDRad]
            );
        }

        $radiologiDetailFlat = collect($radiologiDetail)->flatten(1);

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


        $upxList = DB::select("
            EXEC dbo.cboUpx_sp
        ");

        // Ket Cancel
        $ketCancelList = DB::select("
            SET NOCOUNT ON;
            EXEC dbo.cboSPLabKetCancel_sp
        ");

        return view('sp.spdetail', compact(
            'sp',
            'noSP',
            'pasien',
            'lab',
            'labDetail',
            'lainlain',
            'dokterList',
            'dokterSpPKList',
            'spLabList',
            'spPaList',
            'upxList',
            'ketCancelList',
            'radiologi',
            'radiologiDetail',
            'radiologiDetailFlat', 
        ));
    }

    public function updateKetCancel(Request $request)
    {
        $request->validate([
            'no'        => 'required',
            'ketCancel' => 'required',
        ]);

        $affected = DB::update("
            UPDATE ERM.dbo.LaboratPlan
            SET KetCancel = ?
            WHERE NO = ?
        ", [
            $request->ketCancel,
            $request->no
        ]);

        if ($affected < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Data SP tidak ditemukan atau tidak ada perubahan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diperbarui.'
        ]);
    }
}