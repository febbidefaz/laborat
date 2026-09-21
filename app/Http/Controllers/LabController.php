<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LabController extends Controller
{   
    // Insert Lab
    public function insertLab(Request $request)
    {
        $request->validate([
            'IDReg'       => 'required|integer',
            'TLab'        => 'required|date',
            'DokterID'    => 'nullable|integer',
            'spPK'        => 'nullable|integer',
            'Note'        => 'nullable|string',
            'KlasID'      => 'nullable|integer',
            'RoomID'      => 'nullable|integer',
            'NoSP'        => 'nullable|integer',
            'NoSPPA'      => 'nullable|integer',
            'Jam_ambil'   => 'nullable',
            'Jam_check'   => 'nullable',

            'items'              => 'required|array|min:1',
            'items.*.LabID'      => 'required|integer',
            'items.*.Prep_ID'    => 'required|integer',
            'items.*.Biaya'      => 'nullable|numeric',
            'items.*.Pot'        => 'nullable|numeric',
            'items.*.NorL'       => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
        
        // ============================================================
        // AMBIL KELAS DAN RUANG TERAKHIR
        // ============================================================

        $lastCheckIn = DB::select("
        DECLARE @KlasID INT;
        DECLARE @RoomID INT;

        EXEC dbo.GetLastInfoChekIN_SP
            @ID = ?,
            @KlasID = @KlasID OUTPUT,
            @RoomID = @RoomID OUTPUT;

        SELECT
            @KlasID AS KlasID,
            @RoomID AS RoomID;
        ", [
            (int) $request->IDReg
        ]);


        $klasID =
            $lastCheckIn[0]->KlasID ?? null;

        $roomID =
            $lastCheckIn[0]->RoomID ?? null;


        if ($klasID === null || $roomID === null) {

            throw new \Exception(
                'Kelas dan ruang terakhir pasien tidak ditemukan.'
            );
        }


        $klasID =
            (int) $klasID;

        $roomID =
            (int) $roomID;


            /* ==========================================
               INSERT HEADER LABORAT
               ========================================== */

            $result = DB::selectOne("
                INSERT INTO dbo.Laborat
                (
                    IDReg,
                    TLab,
                    DokterID,
                    spPK,
                    Note,
                    Tunai,
                    KlasID,
                    RoomID,
                    Jam_ambil,
                    Jam_check,
                    TGL,
                    TLabOrder,
                    NoSP,
                    NoSPPA,
                    posted,
                    Cetak
                )

                OUTPUT INSERTED.IDLab

                VALUES
                (
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    GETDATE(),
                    GETDATE(),
                    ?,
                    ?,
                    0,
                    0
                )
            ", [
                $request->IDReg,
                $request->TLab,
                $request->DokterID,
                $request->spPK,
                $request->Note,
                0,
                $klasID,    
                $roomID,
                $jamAmbil,
                $jamCheck,
                $request->NoSP,
                $request->NoSPPA,
            ]);

            if (!$result || !$result->IDLab) {
                throw new \Exception(
                    'Gagal membuat header pemeriksaan laboratorium.'
                );
            }

            $idLab = $result->IDLab;


            /* ==========================================
               INSERT DETAIL LABORATPAS
               ========================================== */

            foreach ($request->items as $item) {

                DB::table('LaboratPas')->insert([
                    'ID'      => $idLab,
                    'LabID'   => $item['LabID'],
                    'Prep_ID' => $item['Prep_ID'],

                    'Levels'  => null,
                    'Biaya'   => $item['Biaya'] ?? 0,
                    'Pot'     => $item['Pot'] ?? 0,
                    'NorL'    => $item['NorL'] ?? null,

                    'IsOk' => !empty($item['IsOk']) ? 1 : 0,
                ]);
            }


            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pemeriksaan laboratorium berhasil ditambahkan.',
                'IDLab'   => $idLab,
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Info SP Lab
    public function getInfoSP(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'no' => 'required|integer',
        ]);
    
        try {
    
            $data = DB::select("
                SET NOCOUNT ON;
    
                DECLARE @isiA NVARCHAR(2000) = N'';
                DECLARE @Diag NVARCHAR(2000) = N'';
                DECLARE @bb NVARCHAR(10) = N'';
    
                EXEC dbo.GetPasienInfoSPLab26_sp
                    @id   = ?,
                    @no   = ?,
                    @isiA = @isiA OUTPUT,
                    @Diag = @Diag OUTPUT,
                    @bb   = @bb OUTPUT;
    
                SELECT
                    ISNULL(@isiA, '') AS isiA,
                    ISNULL(@Diag, '') AS Diag,
                    ISNULL(@bb, '')   AS BB;
            ", [
                (int) $request->id,
                (int) $request->no
            ]);
    
            $result = $data[0] ?? null;
    
            return response()->json([
                'success' => true,
                'isiA'    => $result->isiA ?? '',
                'Diag'    => $result->Diag ?? '',
                'BB'      => $result->BB ?? '',
            ]);
    
        } catch (\Throwable $e) {
    
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Info SP Lab PA
    public function getInfoSPPA(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'no' => 'required|integer',
        ]);
    
        try {
    
            $data = DB::select("
                SET NOCOUNT ON;
    
                DECLARE @isiA NVARCHAR(MAX) = N'';
    
                EXEC dbo.GetPasienInfoSPPA_sp
                    @id   = ?,
                    @no   = ?,
                    @isiA = @isiA OUTPUT;
    
                SELECT
                    ISNULL(@isiA, '') AS isiA;
            ", [
                (int) $request->id,
                (int) $request->no
            ]);
    
            $result = $data[0] ?? null;
    
            return response()->json([
                'success' => true,
                'isiA'    => $result->isiA ?? '',
            ]);
    
        } catch (\Throwable $e) {
    
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Master Lab
    public function masterLab()
    {
        try {

            $data = DB::select("
                EXEC dbo.cboLaborat_SP
            ");

            return response()->json([
                'success' => true,
                'data'    => $data
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Harga Lab
    public function hargaLab(Request $request)
    {
        $idReg = (int) $request->IDREG;
        $labID = (int) $request->LabID;

        try {

            $hasil = DB::select("
                DECLARE
                    @Price float,
                    @Pelayanan float,
                    @Perujuk float,
                    @JasaRS float,
                    @Pembaca float,
                    @Administrasi float,
                    @Reagen float;

                EXEC dbo.GetPrice_LaboratUpxV2_SP
                    @IDREG = ?,
                    @LabID = ?,
                    @Price_Out = @Price OUTPUT,
                    @Pelayanan = @Pelayanan OUTPUT,
                    @Perujuk = @Perujuk OUTPUT,
                    @JasaRS = @JasaRS OUTPUT,
                    @Pembaca = @Pembaca OUTPUT,
                    @Administrasi = @Administrasi OUTPUT,
                    @Reagen = @Reagen OUTPUT;

                SELECT
                    ISNULL(@Price,0) AS Biaya,
                    ISNULL(@Pelayanan,0) AS JasaPelayanan,
                    ISNULL(@Perujuk,0) AS JasaPerujuk,
                    ISNULL(@JasaRS,0) AS JasaRS,
                    ISNULL(@Pembaca,0) AS Pembaca,
                    ISNULL(@Administrasi,0) AS Administrasi,
                    ISNULL(@Reagen,0) AS Reagen;
            ", [$idReg, $labID]);

            return response()->json([
                'success' => true,
                'data' => $hasil[0] ?? null
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Simpan Lab
    public function simpanLab(Request $request)
    {
            $request->validate([
                'IDREG'         => 'required|integer',
                'Tanggal'       => 'required|date',
                'DokterID'      => 'required|integer',
                'SpPK'          => 'nullable|integer',
                'NoSP'          => 'nullable',
                'NoSPPA'        => 'nullable',
                'NoLIS'         => 'nullable|string|max:45',
                'JamAmbil'      => 'nullable',
                'JamCheck'      => 'nullable',
                'Diagnosa'      => 'nullable|string',
                'NoteSpPK'      => 'nullable|string',
                'Verif'         => 'nullable|integer',

                'items'                     => 'required|array|min:1',
                'items.*.LabID'             => 'required|integer',
                'items.*.IsOk'              => 'nullable|boolean',
                'items.*.Biaya'             => 'nullable|numeric',
                'items.*.Pot'               => 'nullable|numeric',
                'items.*.NorL'              => 'nullable|string',
                'items.*.Levels'            => 'nullable',
                'items.*.JasaPelayanan'     => 'nullable|numeric',
                'items.*.JasaPerujuk'       => 'nullable|numeric',
                'items.*.JasaRS'            => 'nullable|numeric',
                'items.*.Pembaca'           => 'nullable|numeric',
                'items.*.Administrasi'      => 'nullable|numeric',
                'items.*.Reagen'            => 'nullable|numeric',
            ]);

            DB::beginTransaction();

            try {

                /*
                |--------------------------------------------------------------------------
                | JAM
                |--------------------------------------------------------------------------
                */

                $jamAmbil = null;
                $jamCheck = null;

                if ($request->JamAmbil) {
                    $jamAmbil = date(
                        'Y-m-d H:i:s',
                        strtotime($request->Tanggal . ' ' . $request->JamAmbil)
                    );
                }

                if ($request->JamCheck) {
                    $jamCheck = date(
                        'Y-m-d H:i:s',
                        strtotime($request->Tanggal . ' ' . $request->JamCheck)
                    );
                }

                // ============================================================
                // AMBIL KELAS DAN RUANG TERAKHIR
                // ============================================================

                $lastCheckIn = DB::select("
                DECLARE @KlasID INT;
                DECLARE @RoomID INT;

                EXEC dbo.GetLastInfoChekIN_SP
                    @ID = ?,
                    @KlasID = @KlasID OUTPUT,
                    @RoomID = @RoomID OUTPUT;

                SELECT
                    @KlasID AS KlasID,
                    @RoomID AS RoomID;
                ", [
                (int) $request->IDREG
                ]);

                $klasID =
                (int) ($lastCheckIn[0]->KlasID ?? 0);

                $roomID =
                (int) ($lastCheckIn[0]->RoomID ?? 0);


                if (!$klasID || !$roomID) {

                throw new \Exception(
                    'Kelas dan ruang terakhir pasien tidak ditemukan.'
                );
                }



                /*
                |--------------------------------------------------------------------------
                | INSERT HEADER dbo.Laborat
                |--------------------------------------------------------------------------
                */

            $result = DB::selectOne("
                INSERT INTO dbo.Laborat
                (
                    IDReg,
                    TLab,
                    DokterID,
                    spPK,
                    Note,
                    Tunai,
                    KlasID,
                    RoomID,
                    Jam_ambil,
                    Jam_check,
                    TGL,
                    TLabOrder,
                    NoSP,
                    NoSPPA,
                    No_Lab_LIS,
                    posted,
                    Cetak,
                    verif
                )

                OUTPUT INSERTED.IDLab

                VALUES
                (
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    GETDATE(),
                    GETDATE(),
                    ?, ?, ?,
                    0,
                    0,
                    ?
                )
            ", [
                $request->IDREG,
                $request->Tanggal,
                $request->DokterID,
                $request->SpPK ?: null,
                $request->NoteSpPK,

                0,      // Tunai
                null,   // KlasID
                null,   // RoomID

                $jamAmbil,
                $jamCheck,

                $request->NoSP ?: null,
                $request->NoSPPA ?: null,
                $request->NoLIS
                    ? trim($request->NoLIS)
                    : null,
                $request->Verif ?: null,
                
            ]);


            if (!$result || !$result->IDLab) {

                throw new \Exception(
                    'Gagal membuat header Laboratorium.'
                );
            }


            $idLab = $result->IDLab;


            /*
            |--------------------------------------------------------------------------
            | INSERT DETAIL dbo.LaboratPas
            |--------------------------------------------------------------------------
            */

            foreach ($request->items as $index => $item) {

                $biaya = (float) ($item['Biaya'] ?? 0);

                /*
                * Dari tampilan:
                * Pot = 10 berarti 10 %
                *
                * Di database:
                * 0.10 berarti 10 %
                */
                $potPersen = (float) ($item['Pot'] ?? 0);

                $pot = $potPersen / 100;


                DB::table('dbo.LaboratPas')->insert([

                    'ID' => $idLab,

                    'LabID' =>
                        (int) $item['LabID'],

                    'Prep_ID' =>
                        $index + 1,

                    'Levels' =>
                        $item['Levels'] ?? null,

                    'Biaya' =>
                        $biaya,

                    'Pot' =>
                        $pot,

                    'Usr' =>
                        auth()->user()->name ?? 'WEB',

                    'NorL' =>
                        $item['NorL'] ?? null,

                    'IsOk' => !empty($item['IsOk']) ? 1 : 0,

                    'idLevels' => null,

                    'JasaPelayanan' =>
                        (float) ($item['JasaPelayanan'] ?? 0),

                    'JasaPerujuk' =>
                        (float) ($item['JasaPerujuk'] ?? 0),

                    'BHPReagen' =>
                        (float) ($item['Reagen'] ?? 0),

                    'JasaRS' =>
                        (float) ($item['JasaRS'] ?? 0),

                    'Pembaca' =>
                        (float) ($item['Pembaca'] ?? 0),

                    'Reagen' =>
                        (float) ($item['Reagen'] ?? 0),

                    'Analis' => null,

                    'Administrasi' =>
                        (float) ($item['Administrasi'] ?? 0),

                    'Selisih' => 0,
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | SELESAI
            |--------------------------------------------------------------------------
            */

            DB::commit();


            return response()->json([

                'success' => true,

                'message' =>
                    'Pemeriksaan laboratorium berhasil disimpan.',

                'IDLab' =>
                    $idLab

            ]);


        } catch (\Throwable $e) {

            DB::rollBack();


            return response()->json([

                'success' => false,

                'message' => $e->getMessage()

            ], 500);
        }
    }

    public function updateLab(Request $request)
    {
        $request->validate([
            'IDLab'          => 'required|integer',
            'IDREG'          => 'required|integer',
            'Tanggal'        => 'required|date',

            // DOKTER TIDAK LAGI WAJIB DARI FRONTEND
            'DokterID'       => 'nullable|integer',

            'SpPK'           => 'nullable|integer',
            'NoSP'           => 'nullable',
            'NoSPPA'         => 'nullable',
            'NoLIS'          => 'nullable|string|max:45',
            'JamAmbil'       => 'nullable',
            'JamCheck'       => 'nullable',
            'NoteSpPK'       => 'nullable|string',
            'Verif'          => 'nullable|integer',

            'items'                     => 'required|array|min:1',
            'items.*.LabID'             => 'required|integer',
            'items.*.IsOk'              => 'nullable|boolean',
            'items.*.Biaya'             => 'nullable|numeric',
            'items.*.Pot'               => 'nullable|numeric',
            'items.*.NorL'              => 'nullable|string',
            'items.*.Levels'            => 'nullable',
            'items.*.JasaPelayanan'     => 'nullable|numeric',
            'items.*.JasaPerujuk'       => 'nullable|numeric',
            'items.*.JasaRS'            => 'nullable|numeric',
            'items.*.Pembaca'           => 'nullable|numeric',
            'items.*.Administrasi'      => 'nullable|numeric',
            'items.*.Reagen'            => 'nullable|numeric',
        ]);


        DB::beginTransaction();


        try {

            $idLab =
                (int) $request->IDLab;

            $idReg =
                (int) $request->IDREG;


            /*
            |--------------------------------------------------------------------------
            | AMBIL DOKTER
            |--------------------------------------------------------------------------
            |
            | Prioritas:
            | 1. Jika ada NoSP -> ambil dari ERM.dbo.LaboratPlan
            | 2. Jika tidak ada -> gunakan DokterID dari request
            | 3. Jika masih kosong -> pertahankan DokterID lama
            |
            */

            $dokterID =
                $request->DokterID ?: null;


            if ($request->NoSP) {

                $dokter = DB::selectOne("
                    SELECT TOP 1
                        IDDokter
                    FROM ERM.dbo.LaboratPlan
                    WHERE NO = ?
                    AND ID = ?
                ", [
                    (int) $request->NoSP,
                    $idReg
                ]);


                if (
                    $dokter &&
                    $dokter->IDDokter
                ) {

                    $dokterID =
                        (int) $dokter->IDDokter;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | JIKA MASIH KOSONG, PERTAHANKAN DOKTER LAMA
            |--------------------------------------------------------------------------
            */

            if (!$dokterID) {

                $headerLama =
                    DB::table('dbo.Laborat')
                        ->where('IDLab', $idLab)
                        ->where('IDReg', $idReg)
                        ->first();


                if (!$headerLama) {

                    throw new \Exception(
                        'Data laboratorium tidak ditemukan.'
                    );
                }


                $dokterID =
                    $headerLama->DokterID ?? null;
            }


            /*
            |--------------------------------------------------------------------------
            | VALIDASI DOKTER AKHIR
            |--------------------------------------------------------------------------
            */

            if (!$dokterID) {

                throw new \Exception(
                    'Dokter Laboratorium tidak ditemukan.'
                );
            }



            /*
            |--------------------------------------------------------------------------
            | JAM
            |--------------------------------------------------------------------------
            */

            $jamAmbil = null;
            $jamCheck = null;


            if ($request->JamAmbil) {

                $jamAmbil = date(
                    'Y-m-d H:i:s',
                    strtotime(
                        $request->Tanggal .
                        ' ' .
                        $request->JamAmbil
                    )
                );
            }


            if ($request->JamCheck) {

                $jamCheck = date(
                    'Y-m-d H:i:s',
                    strtotime(
                        $request->Tanggal .
                        ' ' .
                        $request->JamCheck
                    )
                );
            }



            /*
            |--------------------------------------------------------------------------
            | UPDATE HEADER
            |--------------------------------------------------------------------------
            */

            $affected = DB::update("
                UPDATE dbo.Laborat
                SET
                    TLab       = ?,
                    DokterID   = ?,
                    spPK       = ?,
                    Note       = ?,
                    Jam_ambil  = ?,
                    Jam_check  = ?,
                    NoSP       = ?,
                    NoSPPA     = ?,
                    No_Lab_LIS = ?,
                    verif      = ?
                WHERE IDLab = ?
                AND IDReg = ?
            ", [

                $request->Tanggal,

                $dokterID,

                $request->SpPK ?: null,

                $request->NoteSpPK,

                $jamAmbil,

                $jamCheck,

                $request->NoSP ?: null,

                $request->NoSPPA ?: null,

                $request->NoLIS
                ? trim($request->NoLIS)
                : null,   

                $request->Verif ?: null,    

                $idLab,

                $idReg                

            ]);


            /*
            |--------------------------------------------------------------------------
            | PASTIKAN HEADER ADA
            |--------------------------------------------------------------------------
            */

            if ($affected === 0) {

                $exists =
                    DB::table('dbo.Laborat')
                        ->where('IDLab', $idLab)
                        ->where('IDReg', $idReg)
                        ->exists();


                if (!$exists) {

                    throw new \Exception(
                        'Data laboratorium yang akan diubah tidak ditemukan.'
                    );
                }
            }



            /*
            |--------------------------------------------------------------------------
            | HAPUS DETAIL LAMA
            |--------------------------------------------------------------------------
            */

            DB::table('dbo.LaboratPas')
                ->where('ID', $idLab)
                ->delete();



            /*
            |--------------------------------------------------------------------------
            | INSERT DETAIL BARU
            |--------------------------------------------------------------------------
            */

            foreach (
                $request->items as $index => $item
            ) {

                $biaya =
                    (float) ($item['Biaya'] ?? 0);


                $potPersen =
                    (float) ($item['Pot'] ?? 0);


                $pot =
                    $potPersen / 100;


                DB::table('dbo.LaboratPas')
                    ->insert([

                        'ID' =>
                            $idLab,

                        'LabID' =>
                            (int) $item['LabID'],

                        'Prep_ID' =>
                            $index + 1,

                        'Levels' =>
                            $item['Levels'] ?? null,

                        'Biaya' =>
                            $biaya,

                        'Pot' =>
                            $pot,

                        'Usr' =>
                            auth()->user()->name ?? 'WEB',

                        'NorL' =>
                            $item['NorL'] ?? null,

                        'IsOk' =>
                            !empty($item['IsOk']) ? 1 : 0,

                        'idLevels' =>
                            null,

                        'JasaPelayanan' =>
                            (float) ($item['JasaPelayanan'] ?? 0),

                        'JasaPerujuk' =>
                            (float) ($item['JasaPerujuk'] ?? 0),

                        'BHPReagen' =>
                            (float) ($item['Reagen'] ?? 0),

                        'JasaRS' =>
                            (float) ($item['JasaRS'] ?? 0),

                        'Pembaca' =>
                            (float) ($item['Pembaca'] ?? 0),

                        'Reagen' =>
                            (float) ($item['Reagen'] ?? 0),

                        'Analis' =>
                            null,

                        'Administrasi' =>
                            (float) ($item['Administrasi'] ?? 0),

                        'Selisih' =>
                            0,
                    ]);
            }



            DB::commit();


            return response()->json([

                'success' =>
                    true,

                'message' =>
                    'Laboratorium berhasil diperbarui.',

                'IDLab' =>
                    $idLab,

                'DokterID' =>
                    $dokterID

            ]);


        } catch (\Throwable $e) {

            DB::rollBack();


            return response()->json([

                'success' =>
                    false,

                'message' =>
                    $e->getMessage()

            ], 500);
        }
    }

    // Detail Lab
    public function detailLab($idLab)
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | HEADER
            |--------------------------------------------------------------------------
            */

            $header = DB::selectOne("
                SELECT
                    L.IDLab,
                    L.IDReg,
                    L.TLab,
                    L.DokterID,
                    L.spPK,
                    L.Note,
                    L.NoSP,
                    L.NoSPPA,
                    L.Jam_ambil,
                    L.Jam_check,
                    L.KlasID,
                    L.RoomID,
                    L.No_Lab_LIS,
                    L.verif,
                    L.Jam_selesai
                FROM dbo.Laborat L
                WHERE L.IDLab = ?
            ", [
                $idLab
            ]);

            if (!$header) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data laboratorium tidak ditemukan.'
                ], 404);
            }


            /*
            |--------------------------------------------------------------------------
            | DETAIL PEMERIKSAAN
            |--------------------------------------------------------------------------
            */

            $items = DB::select("
                SELECT
                    LP.LabID,
                    LP.Prep_ID,
                    LP.Levels,
                    LP.Biaya,
                    LP.Pot,
                    LP.NorL,
                    LP.IsOk,
                    LP.JasaPelayanan,
                    LP.JasaPerujuk,
                    LP.BHPReagen,
                    LP.JasaRS,
                    LP.Pembaca,
                    LP.Reagen,
                    LP.Administrasi,
                    (
                        SELECT COUNT(*)
                        FROM dbo.LaboratPasFoto F
                        WHERE F.ID = LP.ID
                          AND F.LabID = LP.Prep_ID
                    ) AS JumlahFile,
                    SL.Perik AS Nama

                FROM dbo.LaboratPas LP

                LEFT JOIN dbo.StandartLab SL
                    ON SL.ID = LP.LabID

                WHERE LP.ID = ?

                ORDER BY
                    LP.Prep_ID,
                    LP.LabID
            ", [
                $idLab
            ]);


            return response()->json([
                'success' => true,
                'header'  => $header,
                'items'   => $items
            ]);


        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Group Lab
    public function grupLab()
    {
        try {

            $data = DB::select("
                EXEC dbo.cbogrpPeriksaNew_sp
            ");

            return response()->json([
                'success' => true,
                'data'    => $data
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // GENERATE PEMERIKSAAN BERDASARKAN GRUP
    public function generateGrupLab(Request $request)
    {
        $request->validate([
            'grpID' => 'required|integer',
            'IDLab' => 'required|integer',
            'IDREG' => 'required|integer',
        ]);

        DB::beginTransaction();

        try {

            $idLab = (int) $request->IDLab;
            $idReg = (int) $request->IDREG;
            $grpID = (int) $request->grpID;


            // Pastikan IDLab memang milik pasien/registrasi ini
            $header = DB::table('dbo.Laborat')
                ->where('IDLab', $idLab)
                ->where('IDReg', $idReg)
                ->first();

            if (!$header) {

                throw new \Exception(
                    'Data Laboratorium tidak ditemukan.'
                );
            }


            // Jalankan stored procedure
            DB::statement("
                EXEC dbo.GeneratedPeriksaLabNew_sp
                    @grpID = ?,
                    @IDLab = ?,
                    @IDREG = ?
            ", [
                $grpID,
                $idLab,
                $idReg
            ]);


            DB::commit();


            return response()->json([
                'success' => true,
                'message' => 'Grup pemeriksaan berhasil ditambahkan.'
            ]);


        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Group Lab Kelompok
    public function grupKelLab()
    {
        try {

            $data = DB::select("
                EXEC dbo.cbogrpPeriksaKel_sp
            ");

            return response()->json([
                'success' => true,
                'data'    => $data
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    // GENERATE PEMERIKSAAN GRUP KELOMPOK
    public function generateGrupKelLab(Request $request)
    {
        $request->validate([
            'KelgrpID' => 'required|integer',
            'IDLab'    => 'required|integer',
            'IDREG'    => 'required|integer',
        ]);

        DB::beginTransaction();

        try {

            $kelGrpID = (int) $request->KelgrpID;
            $idLab    = (int) $request->IDLab;
            $idReg    = (int) $request->IDREG;


            // ==================================================
            // VALIDASI HEADER LAB
            // ==================================================

            $header = DB::table('dbo.Laborat')
                ->where('IDLab', $idLab)
                ->where('IDReg', $idReg)
                ->first();


            if (!$header) {

                throw new \Exception(
                    'Data Laboratorium tidak ditemukan.'
                );
            }


            // ==================================================
            // GENERATE KELOMPOK
            // ==================================================

            DB::statement("
                EXEC dbo.GeneratedPeriksaLabKel_sp
                    @KelgrpID = ?,
                    @IDLab    = ?,
                    @IDREG    = ?
            ", [
                $kelGrpID,
                $idLab,
                $idReg
            ]);


            DB::commit();


            return response()->json([
                'success' => true,
                'message' =>
                    'Kelompok pemeriksaan berhasil ditambahkan.'
            ]);


        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function printSP($no)
    {
        try {

            $data = DB::select("
                EXEC dbo.LapSPLaborat_SP
                    @N = ?
            ", [
                (int) $no
            ]);

            if (empty($data)) {
                abort(404, 'Data SP Laboratorium tidak ditemukan.');
            }

            $header = $data[0];

            return view('rawatinap.lab-print-sp', [
                'header' => $header,
                'items'  => $data,
            ]);

        } catch (\Throwable $e) {

            abort(500, $e->getMessage());
        }
    }

    public function printSPPA($no, $idLab)
    {
        try {

            $data = DB::select("
                EXEC dbo.LapSPLaboratPA_SP
                    @N = ?,
                    @L = ?
            ", [
                (int) $no,
                (int) $idLab
            ]);

            if (empty($data)) {
                abort(404, 'Data SPPA tidak ditemukan.');
            }

            $header = $data[0];

            return view('rawatinap.lab-print-sppa', [
                'header' => $header,
                'data'   => $data,
            ]);

        } catch (\Throwable $e) {

            abort(500, $e->getMessage());
        }
    }

    public function tarikSP(Request $request)
    {
        $request->validate([
            'NoSP'  => 'required|integer',
            'IDLab' => 'required|integer',
            'IDREG' => 'required|integer',
        ]);

        DB::beginTransaction();

        try {

            $noSP  = (int) $request->NoSP;
            $idLab = (int) $request->IDLab;
            $idReg = (int) $request->IDREG;

            $header = DB::table('dbo.Laborat')
                ->where('IDLab', $idLab)
                ->where('IDReg', $idReg)
                ->first();

            if (!$header) {
                throw new \Exception(
                    'Data Laboratorium tidak ditemukan.'
                );
            }

            DB::statement("
                EXEC dbo.ERM_SPToLab_sp
                    @NO = ?,
                    @IDLab = ?,
                    @IDREG = ?
            ", [
                $noSP,
                $idLab,
                $idReg
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pemeriksaan dari SP berhasil ditarik.'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function selesaiSP(Request $request)
    {
        $request->validate([
            'NoSP' => 'required|integer',
        ]);

        try {

            DB::statement("
                EXEC dbo.LabSPSelesai_SP
                    @NoSP = ?
            ", [
                (int) $request->NoSP
            ]);

            return response()->json([
                'success' => true,
                'message' => 'SP Laboratorium berhasil diselesaikan.'
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function simpanHeaderLab(Request $request)
    {
        $request->validate([
            'IDREG'      => 'required|integer',
            'Tanggal'    => 'required|date',
            'DokterID'   => 'nullable|integer',
            'SpPK'       => 'nullable|integer',
            'NoLIS'      => 'nullable|string|max:45',
            'NoSP'       => 'nullable|integer',
            'NoSPPA'     => 'nullable',
            'JamAmbil'   => 'nullable',
            'JamCheck'   => 'nullable',
            'NoteSpPK'   => 'nullable|string',
        ]);
    
    
        DB::beginTransaction();
    
    
        try {
    
            /*
            |--------------------------------------------------------------------------
            | DOKTER
            |--------------------------------------------------------------------------
            |
            | Ada NoSP     -> dokter otomatis dari LaboratPlan
            | Tidak ada SP -> dokter wajib dari form
            |
            */
    
            $dokterID = null;
    
    
            if ($request->NoSP) {
    
                $dokter = DB::selectOne("
                    SELECT TOP 1
                        IDDokter
                    FROM ERM.dbo.LaboratPlan
                    WHERE NO = ?
                      AND ID = ?
                ", [
                    (int) $request->NoSP,
                    (int) $request->IDREG
                ]);
    
    
                if (
                    !$dokter ||
                    !$dokter->IDDokter
                ) {
    
                    throw new \Exception(
                        'Dokter pada No SP ' .
                        $request->NoSP .
                        ' tidak ditemukan.'
                    );
                }
    
    
                $dokterID =
                    (int) $dokter->IDDokter;
    
            } else {
    
                if (!$request->DokterID) {
    
                    throw new \Exception(
                        'Dokter wajib dipilih jika No SP tidak digunakan.'
                    );
                }
    
    
                $dokterID =
                    (int) $request->DokterID;
            }
    
    
    
            /*
            |--------------------------------------------------------------------------
            | JAM
            |--------------------------------------------------------------------------
            */
    
            $jamAmbil = null;
            $jamCheck = null;
    
    
            if ($request->JamAmbil) {
    
                $jamAmbil = date(
                    'Y-m-d H:i:s',
                    strtotime(
                        $request->Tanggal .
                        ' ' .
                        $request->JamAmbil
                    )
                );
            }
    
    
            if ($request->JamCheck) {
    
                $jamCheck = date(
                    'Y-m-d H:i:s',
                    strtotime(
                        $request->Tanggal .
                        ' ' .
                        $request->JamCheck
                    )
                );
            }

            // ============================================================
            // AMBIL KELAS DAN RUANG TERAKHIR
            // ============================================================

            $lastCheckIn = DB::select("
            DECLARE @KlasID INT;
            DECLARE @RoomID INT;

            EXEC dbo.GetLastInfoChekIN_SP
                @ID = ?,
                @KlasID = @KlasID OUTPUT,
                @RoomID = @RoomID OUTPUT;

            SELECT
                @KlasID AS KlasID,
                @RoomID AS RoomID;
            ", [
            (int) $request->IDREG
            ]);


            $klasID =
            $lastCheckIn[0]->KlasID ?? null;

            $roomID =
            $lastCheckIn[0]->RoomID ?? null;


            if ($klasID === null || $roomID === null) {

            throw new \Exception(
                'Kelas dan ruang terakhir pasien tidak ditemukan.'
            );
            }


            $klasID =
            (int) $klasID;

            $roomID =
            (int) $roomID;
                
    
    
            /*
            |--------------------------------------------------------------------------
            | INSERT HEADER LAB
            |--------------------------------------------------------------------------
            */
    
            $result = DB::selectOne("
                INSERT INTO dbo.Laborat
                (
                    IDReg,
                    TLab,
                    DokterID,
                    spPK,
                    Note,
                    Tunai,
                    KlasID,
                    RoomID,
                    Jam_ambil,
                    Jam_check,
                    TGL,
                    TLabOrder,
                    NoSP,
                    NoSPPA,
                    No_Lab_LIS,
                    posted,
                    Cetak
                )
    
                OUTPUT INSERTED.IDLab
    
                VALUES
                (
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    GETDATE(),
                    GETDATE(),
                    ?, ?, ?,
                    0,
                    0
                )
            ", [
    
                (int) $request->IDREG,
    
                $request->Tanggal,
    
                $dokterID,
    
                $request->SpPK ?: null,
    
                $request->NoteSpPK,
    
                0, 
               
                $klasID,
                
                $roomID,
    
                $jamAmbil,
    
                $jamCheck,
    
                $request->NoSP
                    ? (int) $request->NoSP
                    : null,
    
                $request->NoSPPA
                    ? $request->NoSPPA
                    : null,
                
                $request->NoLIS
                    ? trim($request->NoLIS)
                    : null
            ]);
    
    
            if (
                !$result ||
                !$result->IDLab
            ) {
    
                throw new \Exception(
                    'Gagal membuat header Laboratorium.'
                );
            }
    
    
    
            DB::commit();
    
    
    
            return response()->json([
    
                'success' =>
                    true,
    
                'IDLab' =>
                    $result->IDLab,
    
                'DokterID' =>
                    $dokterID,
    
                'message' =>
                    'Header Laboratorium berhasil dibuat.'
    
            ]);
    
    
        } catch (\Throwable $e) {
    
            DB::rollBack();
    
    
            return response()->json([
    
                'success' =>
                    false,
    
                'message' =>
                    $e->getMessage()
    
            ], 500);
        }
    }

    public function updateDokterFromSP(Request $request)
    {
        $request->validate([
            'IDREG' => 'required|integer',
            'IDLab' => 'required|integer',
            'NoSP'  => 'required|integer',
        ]);

        try {

            DB::statement("
                EXEC dbo.UpdateDokterFromSPLab_sp
                    @id = ?,
                    @IDLab = ?,
                    @sp = ?
            ", [
                (int) $request->IDREG,
                (int) $request->IDLab,
                (int) $request->NoSP,
            ]);

            $data = DB::selectOne("
                SELECT DokterID
                FROM dbo.Laborat
                WHERE IDLab = ?
                AND IDReg = ?
            ", [
                (int) $request->IDLab,
                (int) $request->IDREG,
            ]);

            return response()->json([
                'success'  => true,
                'DokterID' => $data->DokterID ?? null,
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // LIS
    public function listLIS(Request $request)
    {
        $request->validate([
            'NoRM' => 'required'
        ]);
    
        try {
    
            $data = DB::select("
                EXEC dbo.NoLISLabRM_sp
                    @NoRM = ?
            ", [
                $request->NoRM
            ]);
    
    
            return response()->json([
                'success' => true,
                'data'    => $data
            ]);
    
    
        } catch (\Throwable $e) {
    
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function tarikLIS(Request $request)
    {
        $request->validate([
            'IDLab' => 'required|integer',
            'NoLIS' => 'required|string|max:45'
        ]);
    
    
        DB::beginTransaction();
    
    
        try {
    
            $idLab =
                (int) $request->IDLab;
    
            $noLIS =
                trim($request->NoLIS);
    
    
            /*
            |--------------------------------------------------------------------------
            | CEK HEADER
            |--------------------------------------------------------------------------
            */
    
            $header = DB::table('dbo.Laborat')
                ->where('IDLab', $idLab)
                ->first();
    
    
            if (!$header) {
    
                throw new \Exception(
                    'Data Laboratorium tidak ditemukan.'
                );
            }
    
    
            /*
            |--------------------------------------------------------------------------
            | SIMPAN / UPDATE No LIS KE HEADER
            |--------------------------------------------------------------------------
            */
    
            DB::table('dbo.Laborat')
                ->where('IDLab', $idLab)
                ->update([
                    'No_Lab_LIS' => $noLIS
                ]);
    
    
            /*
            |--------------------------------------------------------------------------
            | TARIK HASIL LIS
            |--------------------------------------------------------------------------
            */
    
            DB::statement("
                EXEC dbo.TarikLIS1_sp
                    @IDLab = ?,
                    @noLIS = ?
            ", [
                $idLab,
                $noLIS
            ]);                   
    
            DB::commit();    
    
            return response()->json([
                'success' => true,
                'message' => 'Hasil LIS berhasil ditarik.',
                'IDLab'   => $idLab,
                'NoLIS'   => $noLIS
            ]);
    
    
        } catch (\Throwable $e) {
    
            DB::rollBack();
    
    
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function insertLISSaba(Request $request)
    {
        $request->validate([
            'IDLab' => 'required|integer'
        ]);


        DB::beginTransaction();


        try {

            $idLab =
                (int) $request->IDLab;


            /*
            |--------------------------------------------------------------------------
            | PASTIKAN HEADER LAB ADA
            |--------------------------------------------------------------------------
            */

            $header = DB::table('dbo.Laborat')
                ->where('IDLab', $idLab)
                ->first();


            if (!$header) {

                throw new \Exception(
                    'Data Laboratorium tidak ditemukan.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | KIRIM KE LIS SABA
            |--------------------------------------------------------------------------
            */

            DB::statement("
                EXEC dbo.LabToLISSabaInsert_sp
                    @IDLab = ?
            ", [
                $idLab
            ]);


            DB::commit();


            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dikirim ke LIS Saba.',
                'IDLab'   => $idLab
            ]);


        } catch (\Throwable $e) {

            DB::rollBack();


            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function cekLISSaba(Request $request)
    {
        $request->validate([
            'IDReg' => 'required|integer',
            'IDLab' => 'required|integer',
        ]);

        try {

            $data = DB::select("
                DECLARE @LIS INT;

                EXEC dbo.GetLISSabaKirim_sp
                    @IDReg = ?,
                    @IDLab = ?,
                    @LIS   = @LIS OUTPUT;

                SELECT @LIS AS LIS;
            ", [
                (int) $request->IDReg,
                (int) $request->IDLab
            ]);

            return response()->json([
                'success' => true,
                'LIS'     => (int) ($data[0]->LIS ?? 0)
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateLISSaba(Request $request)
    {
        $request->validate([
            'IDLab' => 'required|integer'
        ]);

        DB::beginTransaction();

        try {

            $idLab =
                (int) $request->IDLab;


            // ====================================================
            // PASTIKAN DATA LAB ADA
            // ====================================================

            $header = DB::table('dbo.Laborat')
                ->where('IDLab', $idLab)
                ->first();


            if (!$header) {

                throw new \Exception(
                    'Data Laboratorium tidak ditemukan.'
                );
            }


            // ====================================================
            // PASTIKAN SUDAH PERNAH DIKIRIM KE LIS SABA
            // ====================================================

            $cek = DB::selectOne("
                SELECT TOP 1
                    ID
                FROM LISSaba.dbo.LIS_ORDER_BCK
                WHERE VISITNO = ?
                AND ONO = ?
            ", [
                $header->IDReg,
                $idLab
            ]);


            if (!$cek) {

                throw new \Exception(
                    'Order belum pernah dikirim ke LIS Saba. Gunakan Insert LIS Saba terlebih dahulu.'
                );
            }


            // ====================================================
            // UPDATE LIS SABA
            // ====================================================

            DB::statement("
                EXEC dbo.LabToLISSabaUpdate_sp
                    @IDLab = ?
            ", [
                $idLab
            ]);


            DB::commit();


            return response()->json([
                'success' => true,
                'message' => 'Order LIS Saba berhasil diperbarui.',
                'IDLab'   => $idLab
            ]);


        } catch (\Throwable $e) {

            DB::rollBack();


            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function tarikLISSaba(Request $request)
    {
        $request->validate([
            'IDLab' => 'required|integer'
        ]);


        DB::beginTransaction();


        try {

            $idLab =
                (int) $request->IDLab;


            // ====================================================
            // CEK HEADER LAB
            // ====================================================

            $header = DB::table('dbo.Laborat')
                ->where('IDLab', $idLab)
                ->first();


            if (!$header) {

                throw new \Exception(
                    'Data Laboratorium tidak ditemukan.'
                );
            }


            // ====================================================
            // TARIK HASIL DARI LIS SABA
            // ====================================================

            DB::statement("
                EXEC dbo.TarikLISSaba_sp
                    @IDLab = ?
            ", [
                $idLab
            ]);         

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Hasil LIS Saba berhasil ditarik.',
                'IDLab'   => $idLab
            ]);


        } catch (\Throwable $e) {

            DB::rollBack();


            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function analisLab()
    {
        try {

            $data = DB::select("
                EXEC dbo.cboAnalisLab_SP
            ");

            return response()->json([
                'success' => true,
                'data'    => $data
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadFotoLab(Request $request)
    {
        $request->validate([

            'IDLab' =>
                'required|integer',

            'Prep_ID' =>
                'required|integer',

            // HANYA PDF
            'foto' =>
                'required|file|mimes:pdf|max:10240',

        ]);


        $idLab =
            (int) $request->IDLab;

        $prepID =
            (int) $request->Prep_ID;


        // ============================================================
        // PASTIKAN PEMERIKSAAN ADA
        // ============================================================

        $pemeriksaan =
            DB::table('dbo.LaboratPas')

                ->where('ID', $idLab)

                ->where('Prep_ID', $prepID)

                ->first();


        if (!$pemeriksaan) {

            return response()->json([

                'success' => false,

                'message' =>
                    'Data pemeriksaan laboratorium tidak ditemukan.'

            ], 404);
        }


        try {

            $file =
                $request->file('foto');


            // ============================================================
            // CARI NOMOR URUT
            // ============================================================

            $jumlahFile =
                DB::table('dbo.LaboratPasFoto')

                    ->where('ID', $idLab)

                    ->where('LabID', $prepID)

                    ->count();


            $urut =
                $jumlahFile + 1;


            do {

                // ========================================================
                // NAMA DATABASE TANPA .pdf
                // ========================================================

                $namaDB =
                    $idLab .
                    '_' .
                    $prepID .
                    '_' .
                    $urut;


                // ========================================================
                // NAMA FILE FISIK DI NAS
                // ========================================================

                $fileName =
                    $namaDB .
                    '.pdf';


                $sudahAda =
                    DB::table('dbo.LaboratPasFoto')

                        ->where('ID', $idLab)

                        ->where('LabID', $prepID)

                        ->where('name', $namaDB)

                        ->exists();


                if ($sudahAda) {

                    $urut++;

                }


            } while ($sudahAda);


            // ============================================================
            // SIMPAN PDF KE NAS
            // ============================================================

            $path =
                Storage::disk('labfoto')
                    ->putFileAs(
                        '',
                        $file,
                        $fileName
                    );


            if (!$path) {

                throw new \Exception(
                    'Gagal menyimpan file PDF ke NAS.'
                );
            }


            // ============================================================
            // SIMPAN DATABASE TANPA .pdf
            // ============================================================

            try {

                DB::table('dbo.LaboratPasFoto')
                    ->insert([

                        'ID' =>
                            $idLab,

                        'LabID' =>
                            $prepID,

                        'name' =>
                            $namaDB,

                    ]);


            } catch (\Throwable $e) {

                Storage::disk('labfoto')
                    ->delete(
                        $fileName
                    );


                throw $e;
            }


            return response()->json([

                'success' =>
                    true,

                'message' =>
                    'File PDF berhasil disimpan.',

                'name' =>
                    $namaDB,

                'IDLab' =>
                    $idLab,

                'Prep_ID' =>
                    $prepID,

            ]);


        } catch (\Throwable $e) {

            return response()->json([

                'success' =>
                    false,

                'message' =>
                    $e->getMessage(),

            ], 500);
        }
    }

    public function listFotoLab($idLab, $prepID)
    {
        try {

            $foto =
                DB::table('dbo.LaboratPasFoto')

                    ->where(
                        'ID',
                        (int) $idLab
                    )

                    ->where(
                        'LabID',
                        (int) $prepID
                    )

                    ->orderBy('name')

                    ->get();


            $data =
                $foto->map(
                    function ($item) use (
                        $idLab,
                        $prepID
                    ) {

                        return [

                            // untuk tampilan
                            'name' =>
                                $item->name . '.pdf',

                            // nama asli DB tanpa .pdf
                            'db_name' =>
                                $item->name,

                            'url' =>
                                route(
                                    'lab.foto.show',
                                    [
                                        'idLab' =>
                                            $idLab,

                                        'prepID' =>
                                            $prepID,

                                        'name' =>
                                            $item->name
                                    ]
                                )

                        ];
                    }
                );


            return response()->json([

                'success' => true,

                'data' =>
                    $data

            ]);


        } catch (\Throwable $e) {

            return response()->json([

                'success' => false,

                'message' =>
                    $e->getMessage()

            ], 500);
        }
    }
    
    public function showFotoLab($idLab, $prepID, $name)
    {
        $idLab =
            (int) $idLab;
    
        $prepID =
            (int) $prepID;
    
    
        // ============================================================
        // NAMA DB TANPA .pdf
        // ============================================================
    
        $name =
            basename($name);
    
        $name =
            preg_replace(
                '/\.pdf$/i',
                '',
                $name
            );
    
    
        $exists =
            DB::table('dbo.LaboratPasFoto')
    
                ->where('ID', $idLab)
    
                ->where('LabID', $prepID)
    
                ->where('name', $name)
    
                ->exists();
    
    
        if (!$exists) {
    
            abort(
                404,
                'File tidak ditemukan.'
            );
        }
    
    
        // ============================================================
        // FILE FISIK SELALU PDF
        // ============================================================
    
        $fileName =
            $name . '.pdf';
    
    
        if (
            !Storage::disk('labfoto')
                ->exists($fileName)
        ) {
    
            abort(
                404,
                'File PDF tidak ditemukan di NAS.'
            );
        }
    
    
        return Storage::disk('labfoto')
            ->response(
                $fileName,
                $fileName,
                [
                    'Content-Type' =>
                        'application/pdf',
    
                    'Content-Disposition' =>
                        'inline; filename="' .
                        $fileName .
                        '"'
                ]
            );
    }

    public function hapusFotoLab(Request $request)
    {
        $request->validate([

            'IDLab' =>
                'required|integer',

            'Prep_ID' =>
                'required|integer',

            'name' =>
                'required|string|max:80',

        ]);


        $idLab =
            (int) $request->IDLab;

        $prepID =
            (int) $request->Prep_ID;


        // hapus .pdf jika dikirim frontend
        $name =
            basename($request->name);

        $name =
            preg_replace(
                '/\.pdf$/i',
                '',
                $name
            );


        try {

            $foto =
                DB::table('dbo.LaboratPasFoto')

                    ->where('ID', $idLab)

                    ->where('LabID', $prepID)

                    ->where('name', $name)

                    ->first();


            if (!$foto) {

                return response()->json([

                    'success' =>
                        false,

                    'message' =>
                        'File tidak ditemukan.'

                ], 404);
            }


            // file fisik punya .pdf
            $fileName =
                $name . '.pdf';


            if (
                Storage::disk('labfoto')
                    ->exists($fileName)
            ) {

                Storage::disk('labfoto')
                    ->delete($fileName);
            }


            DB::table('dbo.LaboratPasFoto')

                ->where('ID', $idLab)

                ->where('LabID', $prepID)

                ->where('name', $name)

                ->delete();


            return response()->json([

                'success' =>
                    true,

                'message' =>
                    'File PDF berhasil dihapus.'

            ]);


        } catch (\Throwable $e) {

            return response()->json([

                'success' =>
                    false,

                'message' =>
                    $e->getMessage()

            ], 500);
        }
    }
    
    // Cari Pasien
    public function cariPasien(Request $request)
    {
        $id = $request->id;

        $pasien = DB::selectOne(
            "EXEC dbo.WebCariPasienByID_SP ?",
            [$id]
        );

        if (!$pasien) {
            return response()->json([
                'success' => false,
                'message' => 'Pasien tidak ditemukan'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $pasien
        ]);
    }
}