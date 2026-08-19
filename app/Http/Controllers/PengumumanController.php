<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    /**
     * Tampilkan halaman pengumuman (histori)
     */
    public function index()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }
        $pengumuman = Pengumuman::orderByDesc('id')->get();

        return view('pengumuman', compact('pengumuman'), [
            'judul' => 'Pengumuman'
        ]);
    }

    /**
     * Simpan pengumuman baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'tanggal_hapus' => 'nullable|date|after_or_equal:today',
        ]);

        Pengumuman::create([
            'judul' => $request->judul,
            'isi' => $request->isi,
            'tanggal_hapus' => $request->tanggal_hapus,
        ]);

        return redirect()
            ->route('pengumuman')
            ->with('success', 'Pengumuman berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'tanggal_hapus' => 'nullable|date',
        ]);

        $pengumuman = Pengumuman::findOrFail($id);

        $pengumuman->update([
            'judul' => $request->judul,
            'isi' => $request->isi,
            'tanggal_hapus' => $request->tanggal_hapus,
        ]);

        return redirect()
            ->route('pengumuman')
            ->with('success', 'Pengumuman berhasil diperbarui');
    }

    /**
     * Hapus satu pengumuman
     */
    public function destroy($id)
    {
        $pengumuman = Pengumuman::findOrFail($id);
        $pengumuman->delete();

        return redirect()
            ->route('pengumuman')
            ->with('success', 'Pengumuman berhasil dihapus');
    }

    /**
     * Hapus semua pengumuman
     */
    public function destroyAll()
    {
        Pengumuman::truncate();

        return redirect()
            ->route('pengumuman')
            ->with('success', 'Semua pengumuman berhasil dihapus');
    }
}
