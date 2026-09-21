<?php

namespace App\Http\Controllers;

use App\Models\UserLab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthLabController extends Controller
{
    public function showLogin()
    {
        if (session('userlab_id')) {
            return redirect()->route('lab.home');
        }

        return view('lab-auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'Username' => 'required|string',
            'Password' => 'required|string',
        ]);

        $user = UserLab::where('Username', $request->Username)
            ->where('Aktif', 1)
            ->first();

        if (!$user || !Hash::check($request->Password, $user->Password)) {
            return back()
                ->withInput($request->only('Username'))
                ->with('error', 'Username atau password salah.');
        }

        $request->session()->regenerate();

        session([
            'userlab_id'       => $user->ID,
            'userlab_nama'     => $user->Nama,
            'userlab_username' => $user->Username,
            'userlab_role'     => $user->Role,
        ]);

        return redirect()->route('lab.home');
    }

    public function logout(Request $request)
    {
        $request->session()->forget([
            'userlab_id',
            'userlab_nama',
            'userlab_username',
            'userlab_role',
        ]);

        $request->session()->regenerateToken();

        return redirect()->route('lab.login');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'password_baru' => 'required|confirmed',
        ]);


        DB::table('UserLab')
            ->where('ID', $request->user_id)
            ->update([
                'Password' => Hash::make(
                    $request->password_baru
                ),
            ]);


        return back()->with(
            'success',
            'Password berhasil diperbarui.'
        );
    }
}