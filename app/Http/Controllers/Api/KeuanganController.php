<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\NotificationHelper;
use App\Models\Keuangan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class KeuanganController extends Controller
{
    private array $selectColumns = [
        'keuangan.id_keuangan',
        'keuangan.user_id',
        'keuangan.keterangan',
        'keuangan.nominal',
        'keuangan.jenis',
        'keuangan.tanggal',
        'keuangan.created_at',
        'keuangan.updated_at',
        'users.name as nama_user',
        'users.email as email_user',
        'users.role as role_user',
    ];

    private function baseQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Keuangan::query()
            ->join('users', 'keuangan.user_id', '=', 'users.id')
            ->select($this->selectColumns);
    }

    private function applyFilters(Request $request, $query)
    {
        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai')) {
            $query->whereBetween('keuangan.tanggal', [$request->tanggal_mulai, $request->tanggal_selesai]);
        } elseif ($request->filled('tanggal')) {
            $query->where('keuangan.tanggal', $request->tanggal);
        }

        if ($request->filled('cari')) {
            $query->where('keuangan.keterangan', 'LIKE', '%' . $request->cari . '%');
        }

        $sortBy  = $request->get('sort_by', 'tanggal');
        $sortDir = $request->get('sort_dir', 'desc');

        $allowedSort = ['tanggal', 'keterangan', 'nominal', 'created_at'];
        if (!in_array($sortBy, $allowedSort)) $sortBy = 'tanggal';
        if (!in_array($sortDir, ['asc', 'desc'])) $sortDir = 'desc';

        return $query->orderBy("keuangan.$sortBy", $sortDir);
    }

    private function cekKepemilikan(Keuangan $keuangan, Request $request): bool
    {
        $user = $request->user();
        if ($user->isAdmin()) return true;
        return $keuangan->user_id === $user->id;
    }

    // Format nominal ke Rupiah ringkas
    private function formatNominal(float $nominal): string
    {
        if ($nominal >= 1_000_000_000) {
            return 'Rp ' . number_format($nominal / 1_000_000_000, 1, ',', '.') . 'M';
        }
        if ($nominal >= 1_000_000) {
            return 'Rp ' . number_format($nominal / 1_000_000, 1, ',', '.') . 'jt';
        }
        if ($nominal >= 1_000) {
            return 'Rp ' . number_format($nominal / 1_000, 0, ',', '.') . 'rb';
        }
        return 'Rp ' . number_format($nominal, 0, ',', '.');
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'keterangan' => 'required|string|max:255',
            'nominal'    => 'required|numeric|min:1',
            'jenis'      => 'required|in:pemasukan,pengeluaran',
            'tanggal'    => 'nullable|date',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }
        try {
            $user = $request->user();
            $data = Keuangan::create([
                'user_id'    => $user->id,
                'keterangan' => $request->keterangan,
                'nominal'    => $request->nominal,
                'jenis'      => $request->jenis,
                'tanggal'    => $request->tanggal ?? now()->toDateString(),
            ]);
            $data->load('user:id,name,email,role');

            // ── Notifikasi ──
            $jenisLabel = $request->jenis === 'pemasukan' ? '💰 Pemasukan Baru' : '💸 Pengeluaran Baru';
            NotificationHelper::broadcast(
                $jenisLabel,
                "{$user->name} menambahkan {$request->jenis}: {$request->keterangan} ({$this->formatNominal((float) $request->nominal)})",
                $user->id
            );

            return response()->json(['success' => true, 'message' => 'Data keuangan berhasil ditambahkan', 'data' => $data], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan data keuangan', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'keterangan' => 'required|string|max:255',
            'nominal'    => 'required|numeric',
            'tanggal'    => 'nullable|date',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }
        try {
            $keuangan = Keuangan::find($id);
            if (!$keuangan) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }
            if (!$this->cekKepemilikan($keuangan, $request)) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak. Anda hanya dapat mengubah data milik Anda sendiri.'], 403);
            }
            $keuangan->update([
                'keterangan' => $request->keterangan,
                'nominal'    => $request->nominal,
                'tanggal'    => $request->tanggal ?? $keuangan->tanggal,
            ]);
            $keuangan->load('user:id,name,email,role');

            // ── Notifikasi ──
            $user = $request->user();
            NotificationHelper::broadcast(
                '✏️ Data Diperbarui',
                "{$user->name} memperbarui {$keuangan->jenis}: {$request->keterangan} ({$this->formatNominal((float) $request->nominal)})",
                $user->id
            );

            return response()->json(['success' => true, 'message' => 'Data keuangan berhasil diubah', 'data' => $keuangan], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengubah data keuangan', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $keuangan = Keuangan::find($id);
            if (!$keuangan) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }
            if (!$this->cekKepemilikan($keuangan, $request)) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak. Anda hanya dapat menghapus data milik Anda sendiri.'], 403);
            }

            // Simpan info sebelum dihapus
            $namaKeterangan = $keuangan->keterangan;
            $nominal        = (float) $keuangan->nominal;
            $jenis          = $keuangan->jenis;
            $user           = $request->user();

            $keuangan->delete();

            // ── Notifikasi ──
            NotificationHelper::broadcast(
                '🗑️ Data Dihapus',
                "{$user->name} menghapus {$jenis}: {$namaKeterangan} ({$this->formatNominal($nominal)})",
                $user->id
            );

            return response()->json(['success' => true, 'message' => 'Data keuangan berhasil dihapus'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus data keuangan', 'error' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $query = $this->baseQuery();
            if ($request->filled('jenis')) {
                $query->where('keuangan.jenis', $request->jenis);
            }
            $data = $this->applyFilters($request, $query)
                        ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengambil data keuangan',
                'data'    => $data->items(),
                'meta'    => [
                    'current_page' => $data->currentPage(),
                    'last_page'    => $data->lastPage(),
                    'per_page'     => $data->perPage(),
                    'total'        => $data->total(),
                    'has_more'     => $data->hasMorePages(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function pemasukan(Request $request): JsonResponse
    {
        try {
            $query = $this->baseQuery()->where('keuangan.jenis', 'pemasukan');
            $data  = $this->applyFilters($request, $query)
                        ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengambil data pemasukan',
                'data'    => $data->items(),
                'meta'    => [
                    'current_page' => $data->currentPage(),
                    'last_page'    => $data->lastPage(),
                    'per_page'     => $data->perPage(),
                    'total'        => $data->total(),
                    'has_more'     => $data->hasMorePages(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function pengeluaran(Request $request): JsonResponse
    {
        try {
            $query = $this->baseQuery()->where('keuangan.jenis', 'pengeluaran');
            $data  = $this->applyFilters($request, $query)
                        ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengambil data pengeluaran',
                'data'    => $data->items(),
                'meta'    => [
                    'current_page' => $data->currentPage(),
                    'last_page'    => $data->lastPage(),
                    'per_page'     => $data->perPage(),
                    'total'        => $data->total(),
                    'has_more'     => $data->hasMorePages(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function cekSaldo(Request $request): JsonResponse
    {
        try {
            $pemasukanQuery   = Keuangan::where('jenis', 'pemasukan');
            $pengeluaranQuery = Keuangan::where('jenis', 'pengeluaran');
            if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai')) {
                $pemasukanQuery->whereBetween('tanggal', [$request->tanggal_mulai, $request->tanggal_selesai]);
                $pengeluaranQuery->whereBetween('tanggal', [$request->tanggal_mulai, $request->tanggal_selesai]);
            } elseif ($request->filled('tanggal')) {
                $pemasukanQuery->where('tanggal', $request->tanggal);
                $pengeluaranQuery->where('tanggal', $request->tanggal);
            }
            $totalPemasukan   = $pemasukanQuery->sum('nominal');
            $totalPengeluaran = $pengeluaranQuery->sum('nominal');
            return response()->json([
                'success' => true,
                'message' => 'Berhasil memuat ringkasan saldo',
                'data'    => [
                    'total_pemasukan'   => (float) $totalPemasukan,
                    'total_pengeluaran' => (float) $totalPengeluaran,
                    'saldo_akhir'       => (float) ($totalPemasukan - $totalPengeluaran),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghitung ringkasan saldo', 'error' => $e->getMessage()], 500);
        }
    }

    public function laporan(Request $request): JsonResponse
    {
        try {
            $startDate = $request->filled('tanggal_mulai') ? $request->tanggal_mulai : '2000-01-01';
            $pemasukanQuery   = Keuangan::where('jenis', 'pemasukan');
            $pengeluaranQuery = Keuangan::where('jenis', 'pengeluaran');
            $awalQuery        = Keuangan::where('tanggal', '<', $startDate);
            if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai')) {
                $pemasukanQuery->whereBetween('tanggal', [$request->tanggal_mulai, $request->tanggal_selesai]);
                $pengeluaranQuery->whereBetween('tanggal', [$request->tanggal_mulai, $request->tanggal_selesai]);
            }
            $totalPemasukan   = $pemasukanQuery->sum('nominal');
            $totalPengeluaran = $pengeluaranQuery->sum('nominal');
            $awalPemasukan    = (clone $awalQuery)->where('jenis', 'pemasukan')->sum('nominal');
            $awalPengeluaran  = (clone $awalQuery)->where('jenis', 'pengeluaran')->sum('nominal');
            $riwayatData = $this->applyFilters($request, $this->baseQuery())->get();
            return response()->json([
                'success' => true,
                'message' => 'Berhasil memuat laporan keuangan',
                'data'    => [
                    'saldo_awal'  => (float) ($awalPemasukan - $awalPengeluaran),
                    'saldo_akhir' => (float) ($totalPemasukan - $totalPengeluaran),
                    'riwayat'     => $riwayatData,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memuat laporan keuangan', 'error' => $e->getMessage()], 500);
        }
    }
}