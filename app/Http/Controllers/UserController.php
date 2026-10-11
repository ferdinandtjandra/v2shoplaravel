<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $daftar = User::orderBy('id', 'desc')->get();

        return view('user.index', ['daftar' => $daftar]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100',
            'username' => 'required|max:100|unique:users,username',
            'password' => 'required|min:4',
            'role' => 'required|in:bos,karyawan,sales',
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => $request->password,          // otomatis di-hash oleh model User (untuk login)
            'password_terlihat' => $request->password, // otomatis dienkripsi (untuk ditampilkan ke Bos)
            'role' => $request->role,
        ]);

        return redirect()->route('user.index')->with('info', 'Pengguna berhasil disimpan.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        return view('user.edit', ['user' => $user]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:100',
            'username' => 'required|max:100|unique:users,username,' . $id,
            'password' => 'nullable|min:4',
            'role' => 'required|in:bos,karyawan,sales',
        ]);

        $user = User::findOrFail($id);

        // Bos tidak boleh menurunkan role dirinya sendiri (supaya tidak terkunci dari menu Bos)
        if ($user->id == Auth::id() && $request->role != 'bos') {
            return back()->with('error', 'Anda tidak bisa mengubah role Anda sendiri.')->withInput();
        }

        $user->name = $request->name;
        $user->username = $request->username;
        $user->role = $request->role;

        // Password hanya diganti kalau diisi
        if ($request->password != '') {
            $user->password = $request->password;
            $user->password_terlihat = $request->password;
        }

        $user->save();

        return redirect()->route('user.index')->with('info', 'Pengguna berhasil diubah.');
    }

    // Mengaktifkan / menonaktifkan user.
    // User tidak dihapus dari database, karena namanya masih dipakai di data penjualan lama.
    public function ubahAktif($id)
    {
        if ($id == Auth::id()) {
            return redirect()->route('user.index')->with('error', 'Anda tidak bisa menonaktifkan diri sendiri.');
        }

        $user = User::findOrFail($id);
        $user->aktif = ! $user->aktif;
        $user->save();

        return redirect()->route('user.index')->with('info', 'Status pengguna berhasil diubah.');
    }
}
