<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserLabController extends Controller
{
    public function index()
    {
        $users = DB::table('UserLab')
            ->orderBy('Nama')
            ->get();

        return view('user-lab.user', compact('users'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'Nama' => 'required',
            'Username' => 'required|unique:UserLab,Username',
            'Password' => 'required',
            'Role' => 'required',
        ]);


        DB::table('UserLab')->insert([

            'Nama' => 
                $request->Nama,

            'Username' => 
                $request->Username,

            'Password' => 
                Hash::make(
                    $request->Password
                ),

            'Role' => 
                $request->Role,

            'Aktif' => 
                $request->Aktif ?? 1,

            'CreatedAt' => 
                now(),

        ]);


        return redirect()
            ->route('userlab.index')
            ->with(
                'success',
                'User Laboratorium berhasil ditambahkan.'
            );
    }



    public function update(Request $request, $id)
    {
        $request->validate([

            'Nama' => 
                'required',

            'Username' => 
                'required',

            'Role' => 
                'required',

            'Aktif' => 
                'required',

        ]);



        $data = [

            'Nama' => 
                $request->Nama,

            'Username' => 
                $request->Username,

            'Role' => 
                $request->Role,

            'Aktif' => 
                $request->Aktif,

        ];



        if ($request->filled('Password')) {

            $data['Password'] =
                Hash::make(
                    $request->Password
                );

        }



        DB::table('UserLab')
            ->where('ID', $id)
            ->update($data);



        return redirect()
            ->route('userlab.index')
            ->with(
                'success',
                'User Laboratorium berhasil diperbarui.'
            );
    }




    public function destroy($id)
    {
        DB::table('UserLab')
            ->where('ID', $id)
            ->delete();


        return redirect()
            ->route('userlab.index')
            ->with(
                'success',
                'User Laboratorium berhasil dihapus.'
            );
    }
}