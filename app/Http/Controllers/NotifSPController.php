<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotifSPController extends Controller
{
    public function notifSP()
    {
        try {

            $notif = DB::selectOne("
                SET NOCOUNT ON;
                EXEC dbo.AdaSPLabMasukAll_sp
            ");

            $igd   = (int) ($notif->IGD ?? 0);
            $ri    = (int) ($notif->RI ?? 0);
            $rj    = (int) ($notif->RJ ?? 0);
            $pa    = (int) ($notif->PA ?? 0);
            $total = (int) ($notif->Total ?? 0);


            return response()->json([

                'success' => true,

                'total' => $total,

                'data' => [
                    'igd' => $igd,
                    'ri'  => $ri,
                    'rj'  => $rj,
                    'pa'  => $pa,
                ]

            ]);

        } catch (\Throwable $e) {

            Log::error('NOTIF SP LAB ERROR', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);


            return response()->json([

                'success' => false,

                'message' => $e->getMessage(),

                'total' => 0,

                'data' => [
                    'igd' => 0,
                    'ri'  => 0,
                    'rj'  => 0,
                    'pa'  => 0,
                ]

            ], 500);
        }
    }
}