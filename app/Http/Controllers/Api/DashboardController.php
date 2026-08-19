<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Keuangan;
use App\Models\User;
use App\Models\Pengumuman;
use Carbon\Carbon;

class DashboardController extends Controller
{
    private function applyFilters(Request $request, $query)
    {
        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai')) {
            $query->whereBetween('tanggal', [
                $request->tanggal_mulai,
                $request->tanggal_selesai,
            ]);
        } elseif ($request->filled('tanggal')) {
            $query->where('tanggal', $request->tanggal);
        }

        if ($request->filled('cari')) {
            $query->where('keterangan', 'LIKE', '%' . $request->cari . '%');
        }

        return $query->orderByDesc('id_keuangan');
    }

    private function hitungSaldo(Request $request): array
    {
        $adaRentang  = $request->filled('tanggal_mulai') && $request->filled('tanggal_selesai');
        $adaTanggal  = $request->filled('tanggal');

        // ── Saldo awal: akumulasi SEBELUM periode ──────────────────────────
        $saldoAwal = 0.0;

        if ($adaRentang) {
            $awalPemasukan   = Keuangan::where('jenis', 'pemasukan')
                ->where('tanggal', '<', $request->tanggal_mulai)
                ->sum('nominal');

            $awalPengeluaran = Keuangan::where('jenis', 'pengeluaran')
                ->where('tanggal', '<', $request->tanggal_mulai)
                ->sum('nominal');

            $saldoAwal = (float) $awalPemasukan - (float) $awalPengeluaran;

        } elseif ($adaTanggal) {
            // Filter satu hari: saldo awal = semua transaksi sebelum hari itu
            $awalPemasukan   = Keuangan::where('jenis', 'pemasukan')
                ->where('tanggal', '<', $request->tanggal)
                ->sum('nominal');

            $awalPengeluaran = Keuangan::where('jenis', 'pengeluaran')
                ->where('tanggal', '<', $request->tanggal)
                ->sum('nominal');

            $saldoAwal = (float) $awalPemasukan - (float) $awalPengeluaran;
        }
        // Tanpa filter → saldo_awal tetap 0 (tidak ada "sebelum")

        // ── Mutasi dalam periode ──────────────────────────────────────────
        $pemasukanQuery   = Keuangan::where('jenis', 'pemasukan');
        $pengeluaranQuery = Keuangan::where('jenis', 'pengeluaran');

        if ($adaRentang) {
            $pemasukanQuery->whereBetween('tanggal', [
                $request->tanggal_mulai,
                $request->tanggal_selesai,
            ]);
            $pengeluaranQuery->whereBetween('tanggal', [
                $request->tanggal_mulai,
                $request->tanggal_selesai,
            ]);
        } elseif ($adaTanggal) {
            $pemasukanQuery->where('tanggal', $request->tanggal);
            $pengeluaranQuery->where('tanggal', $request->tanggal);
        }

        $totalPemasukan   = (float) $pemasukanQuery->sum('nominal');
        $totalPengeluaran = (float) $pengeluaranQuery->sum('nominal');
        $mutasiPeriode    = $totalPemasukan - $totalPengeluaran;

        // ── Saldo akhir ───────────────────────────────────────────────────
        $saldoAkhir = $saldoAwal + $mutasiPeriode;

        return [
            'saldo_awal'        => $saldoAwal,
            'total_pemasukan'   => $totalPemasukan,
            'total_pengeluaran' => $totalPengeluaran,
            'mutasi_periode'    => $mutasiPeriode,
            'saldo_akhir'       => $saldoAkhir,
        ];
    }
    
    public function index(Request $request): JsonResponse
    {
        try {
            $saldo = $this->hitungSaldo($request);

            $recentUsers = User::orderByDesc('recent_login')->take(3)->get();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil memuat data dashboard',
                'data'    => [
                    'ringkasan_saldo' => $saldo,
                    'recent_users'    => $recentUsers,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data dashboard',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function cekSaldo(Request $request): JsonResponse
    {
        try {
            $saldo = $this->hitungSaldo($request);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil memuat ringkasan saldo',
                'data'    => $saldo,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghitung ringkasan saldo',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    
    public function laporan(Request $request): JsonResponse
    {
        try {
            $saldo     = $this->hitungSaldo($request);
            $riwayat   = $this->applyFilters($request, Keuangan::query())->get();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil memuat laporan keuangan',
                'data'    => [
                    'saldo_awal'        => $saldo['saldo_awal'],
                    'total_pemasukan'   => $saldo['total_pemasukan'],
                    'total_pengeluaran' => $saldo['total_pengeluaran'],
                    'mutasi_periode'    => $saldo['mutasi_periode'],
                    'saldo_akhir'       => $saldo['saldo_akhir'],
                    'riwayat'           => $riwayat,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat laporan keuangan',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function index1(Request $request)
    {
        $filterPengeluaran = $request->pengeluaran ?? 'today';
        $filterPemasukan   = $request->pemasukan ?? 'today';

        $applyFilter = function ($query, $filter) {
            if ($filter === 'today') {
                $query->whereDate('tanggal', Carbon::today());
            } elseif ($filter === 'month') {
                $query->whereMonth('tanggal', Carbon::now()->month)
                    ->whereYear('tanggal', Carbon::now()->year);
            } elseif ($filter === 'year') {
                $query->whereYear('tanggal', Carbon::now()->year);
            }
        };
        
        $pengeluaranQuery = Keuangan::where('jenis', 'pengeluaran');
        $applyFilter($pengeluaranQuery, $filterPengeluaran);
        $pengeluaran = $pengeluaranQuery->sum('nominal');
        
        $pemasukanQuery = Keuangan::where('jenis', 'pemasukan');
        $applyFilter($pemasukanQuery, $filterPemasukan);
        $pemasukan = $pemasukanQuery->sum('nominal');
        
        $recentLogin = User::orderBy('recent_login', 'desc')
            ->take(3)
            ->get();

        $pengumuman = Pengumuman::aktif()
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Dashboard data retrieved',
            'data' => [
                'pengeluaran' => $pengeluaran,
                'pemasukan'   => $pemasukan,
                'saldo'       => $pemasukan - $pengeluaran,
                'filter' => [
                    'pengeluaran' => $filterPengeluaran,
                    'pemasukan'   => $filterPemasukan
                ],
                'recent_login' => $recentLogin,
                'pengumuman'   => $pengumuman
            ]
        ]);
    }

    public function chart(Request $request): JsonResponse
    {
        try {
            $days = (int) $request->get('days', 30);
            $days = in_array($days, [7, 30, 90]) ? $days : 30;

            // Jika ada filter tanggal aktif, gunakan itu — abaikan days
            $adaRentang = $request->filled('tanggal_mulai') && $request->filled('tanggal_selesai');

            if ($adaRentang) {
                $startDate = Carbon::parse($request->tanggal_mulai)->startOfDay();
                $endDate   = Carbon::parse($request->tanggal_selesai)->endOfDay();
            } else {
                $startDate = Carbon::today()->subDays($days - 1);
                $endDate   = Carbon::today();
            }

            $pemasukan = Keuangan::where('jenis', 'pemasukan')
                ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
                ->selectRaw('tanggal, SUM(nominal) as total')
                ->groupBy('tanggal')
                ->orderBy('tanggal')
                ->get()
                ->keyBy('tanggal');

            $pengeluaran = Keuangan::where('jenis', 'pengeluaran')
                ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
                ->selectRaw('tanggal, SUM(nominal) as total')
                ->groupBy('tanggal')
                ->orderBy('tanggal')
                ->get()
                ->keyBy('tanggal');

            $labels  = [];
            $income  = [];
            $expense = [];

            for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
                $key       = $d->toDateString();
                $labels[]  = $d->format('d M');
                $income[]  = (float) ($pemasukan[$key]->total  ?? 0);
                $expense[] = (float) ($pengeluaran[$key]->total ?? 0);
            }

            return response()->json([
                'success' => true,
                'data'    => compact('labels', 'income', 'expense'),
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
