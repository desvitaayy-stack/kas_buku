<?php

namespace App\Http\Controllers;

use App\Models\Konfigurasi;
use App\Models\Pengumuman;
use Illuminate\Http\Request;

class KonfigurasiController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }
        $konfigurasi = Konfigurasi::first();
        return view('konfigurasi', compact('konfigurasi'), [
            'judul' => 'Konfigurasi'
        ]);
    }

    public function update(Request $request)
    {
        $konfigurasi = Konfigurasi::first();

        $request->validate([
            'judul'          => 'required|string|max:255',
            'profil'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'instagram'      => 'nullable|string|max:255',
            'facebook'       => 'nullable|string|max:255',
            'tiktok'         => 'nullable|string|max:255',
            'alamat'         => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'telepon'        => 'nullable|max:20',
        ]);

        // Upload gambar baru jika ada
        $profilWeb = $konfigurasi->profil; // default gambar lama

        if ($request->hasFile('profil')) {
            if ($konfigurasi->profil && file_exists(public_path('img/' . $konfigurasi->profil))) {
                unlink(public_path('img/'.$konfigurasi->profil));
            }
            $file = $request->file('profil');
            $filename = time() . '.' . $file->getClientOriginalName();

            // Pindahkan file ke public/img/
            $file->move(public_path('/img'), $filename);

            // Simpan path baru
            $profilWeb = 'img/' . $filename;
        }

        // Update database
        $konfigurasi->update([
            'judul'           => $request->judul,
            'profil'          => $profilWeb,
            'instagram'       => $request->instagram,
            'facebook'        => $request->facebook,
            'tiktok'          => $request->tiktok,
            'alamat'          => $request->alamat,
            'email'           => $request->email,
            'telepon'         => $request->telepon,
        ]);

        return redirect()->back()->with('success', 'Konfigurasi berhasil diperbarui!');
    }
}
