<?php

namespace App\Http\Controllers;

use App\Models\Keuangan;
use App\Models\Pengumuman;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class KeuanganController extends Controller
{

    public function index(Request $request)
    {
        $pengumuman = Pengumuman::aktif()
        ->orderByDesc('id')
        ->get();
        // filter masing-masing card
        $filterPengeluaran = $request->pengeluaran ?? 'today';
        $filterPemasukan   = $request->pemasukan ?? 'today';

        // ================= PENGELUARAN =================
        $pengeluaranQuery = Keuangan::where('jenis', 'pengeluaran');

        if ($filterPengeluaran === 'today') {
            $pengeluaranQuery->whereDate('tanggal', Carbon::today());
        } elseif ($filterPengeluaran === 'month') {
            $pengeluaranQuery->whereMonth('tanggal', Carbon::now()->month)
                            ->whereYear('tanggal', Carbon::now()->year);
        } elseif ($filterPengeluaran === 'year') {
            $pengeluaranQuery->whereYear('tanggal', Carbon::now()->year);
        }

        $pengeluaran = $pengeluaranQuery->sum('nominal');

        // ================= PEMASUKAN =================
        $pemasukanQuery = Keuangan::where('jenis', 'pemasukan');

        if ($filterPemasukan === 'today') {
            $pemasukanQuery->whereDate('tanggal', Carbon::today());
        } elseif ($filterPemasukan === 'month') {
            $pemasukanQuery->whereMonth('tanggal', Carbon::now()->month)
                        ->whereYear('tanggal', Carbon::now()->year);
        } elseif ($filterPemasukan === 'year') {
            $pemasukanQuery->whereYear('tanggal', Carbon::now()->year);
        }

        $pemasukan = $pemasukanQuery->sum('nominal');

        // ================= RECENT LOGIN =================
        $recentLogin = User::orderBy('recent_login', 'desc')
            ->take(3)
            ->get();

        return view('dashboard', compact(
            'pengeluaran',
            'pemasukan',
            'recentLogin',
            'filterPengeluaran',
            'filterPemasukan',
            'pengumuman',
        ), [
            'judul' => 'Dashboard'
        ]);
    }

    public function pemasukan()
    {
        $pemasukan = Keuangan::where('jenis', 'pemasukan')->orderByDesc('id_keuangan')->get();
        return view('pemasukan', compact('pemasukan'), ['judul' => 'Pemasukan']);
    }

    public function pemasukanCreate(Request $request)
    {
        Keuangan::create([
            'username' => $request->user()->name,
            'keterangan' => $request->keterangan,
            'nominal' => $request->nominal,
            'jenis' => 'pemasukan',
            'tanggal' => $request->tanggal ?? now()->toDateString(),
        ]);

        return redirect()->back()->with('success', 'Pemasukan berhasil ditambahkan.');
    }

    public function pemasukanUpdate(Request $request, $id)
    {
        $pemasukan = Keuangan::findOrFail($id);
        $pemasukan->update([
            'keterangan' => $request->keterangan,
            'nominal' => $request->nominal,
            'tanggal' => $request->tanggal ?? now()->toDateString(),
        ]);

        return redirect()->back()->with('success', 'Pemasukan berhasil diperbarui.');
    }

    public function pemasukanDestroy($id)
    {
        $pemasukan = Keuangan::findOrFail($id);
        $pemasukan->delete();

        return redirect()->back()->with('success', 'Pemasukan berhasil dihapus.');
    }

    public function pengeluaran()
    {
        $pengeluaran = Keuangan::where('jenis', 'pengeluaran')->orderByDesc('id_keuangan')->get();
        return view('pengeluaran', compact('pengeluaran'), ['judul' => 'Pengeluaran']);
    }

    public function pengeluaranCreate(Request $request)
    {
        Keuangan::create([
            'username' => $request->user()->name,
            'keterangan' => $request->keterangan,
            'nominal' => $request->nominal,
            'jenis' => 'pengeluaran',
            'tanggal' => $request->tanggal ?? now()->toDateString(),
        ]);

        return redirect()->back()->with('success', 'Pengeluaran berhasil ditambahkan.');
    }

    public function pengeluaranUpdate(Request $request, $id)
    {
        $pengeluaran = Keuangan::findOrFail($id);
        $pengeluaran->update([
            'keterangan' => $request->keterangan,
            'nominal' => $request->nominal,
            'tanggal' => $request->tanggal ?? now()->toDateString(),
        ]);

        return redirect()->back()->with('success', 'pengeluaran berhasil diperbarui.');
    }

    public function pengeluaranDestroy($id)
    {
        $pengeluaran = Keuangan::findOrFail($id);
        $pengeluaran->delete();

        return redirect()->back()->with('success', 'pengeluaran berhasil dihapus.');
    }
}
