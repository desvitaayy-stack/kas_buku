<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. SEEDER TABEL: users (Tetap seperti sebelumnya)
        DB::table('users')->insert([
            [
                'name' => 'Administrator',
                'email' => 'a@a.a',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'admin',
                'recent_login' => now(),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'User Biasa',
                'email' => 'u@u.u',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'user',
                'recent_login' => now(),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // Ambil semua ID user yang tersedia
        $userIds = DB::table('users')->pluck('id')->toArray();


        // 2. SEEDER TABEL: keuangan (Dibuat Bervariasi & Banyak)
        $dataKeuangan = [];

        // Template Keterangan agar data terlihat realistis
        $pemasukanList = [
            'Gaji Bulanan', 'Bonus Proyek', 'Keuntungan Penjualan Toko', 
            'Investasi Cair', 'Cashback Belanja', 'Dana Hibah/Insentif', 
            'Jual Barang Bekas', 'Dividen Saham'
        ];

        $pengeluaranList = [
            'Belanja Bulanan di Supermarket', 'Bayar Kos / Kontrakan', 'Tagihan Listrik & Air',
            'Beli Kuota Internet', 'Servis Kendaraan Rutin', 'Makan & Minum Harian',
            'Nongkrong di Cafe', 'Tiket Bioskop & Hiburan', 'Beli Baju Baru',
            'Biaya Berobat / Medis', 'Transportasi Online', 'Iuran Bulanan / Keamanan'
        ];

        // Kita akan generate 100 data keuangan acak
        for ($i = 0; $i < 100; $i++) {
            
            // Tentukan jenis transaksi secara acak (pemasukan / pengeluaran)
            $jenis = collect(['pemasukan', 'pengeluaran'])->random();
            
            if ($jenis == 'pemasukan') {
                $keterangan = collect($pemasukanList)->random();
                $nominal = rand(500, 10000) * 1000; // Nominal antara Rp 500.000 s/d Rp 10.000.000
            } else {
                $keterangan = collect($pengeluaranList)->random();
                $nominal = rand(20, 1500) * 1000;  // Nominal antara Rp 20.000 s/d Rp 1.500.000
            }

            // --- VARIASI TANGGAL ---
            // Bagi data menjadi 3 zona waktu: Tahun Lalu, Bulan Lalu, Bulan Ini
            $pembagi = $i % 3;
            
            if ($pembagi == 0) {
                // 1. Data Tahun Lalu (Tahun kemarin, bulan acak 1-12, tanggal acak 1-28)
                $tanggal = Carbon::now()->subYear()->startOfYear()
                            ->addMonths(rand(0, 11))
                            ->addDays(rand(0, 27));
            } elseif ($pembagi == 1) {
                // 2. Data Bulan Lalu (Bulan kemarin, tanggal acak 1-28)
                $tanggal = Carbon::now()->subMonth()->startOfMonth()
                            ->addDays(rand(0, 27));
            } else {
                // 3. Data Bulan Ini (Dari awal bulan ini sampai hari ini)
                $hariIni = Carbon::now()->day;
                $tanggal = Carbon::now()->startOfMonth()
                            ->addDays(rand(0, max(0, $hariIni - 1)));
            }

            $dataKeuangan[] = [
                'user_id' => collect($userIds)->random(), // Mengacak user yang melakukan transaksi
                'keterangan' => $keterangan,
                'nominal' => $nominal,
                'jenis' => $jenis,
                'tanggal' => $tanggal->format('Y-m-d'),
                'created_at' => $tanggal->toDateTimeString(), // Menyamakan timestamp dengan tanggal transaksi
                'updated_at' => $tanggal->toDateTimeString(),
            ];
        }

        // Insert data keuangan dalam jumlah besar sekaligus
        DB::table('keuangan')->insert($dataKeuangan);


        // 3. SEEDER TABEL: konfigurasi (Tetap seperti sebelumnya)
        DB::table('konfigurasi')->insert([
            [
                'judul' => 'Sistem Informasi Keuangan Mandiri',
                'profil' => 'Aplikasi pencatatan keuangan internal organisasi.',
                'instagram' => '@keuangan_org',
                'facebook' => 'Keuangan Org Official',
                'tiktok' => '@keuangan_org',
                'telepon' => '081234567890',
                'email' => 'admin@keuanganorg.com',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);


        // 4. SEEDER TABEL: pengumuman (Tetap seperti sebelumnya)
        DB::table('pengumuman')->insert([
            [
                'judul' => 'Pemeliharaan Sistem Terjadwal',
                'isi' => 'Aplikasi tidak dapat diakses pada hari Sabtu pukul 23:00 WIB karena maintenance rutin.',
                'tanggal_hapus' => Carbon::now()->addDays(5)->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'judul' => 'Fitur Eksport Excel Telah Rilis',
                'isi' => 'Sekarang Anda sudah dapat mengunduh laporan keuangan dalam bentuk file Excel melalui menu laporan.',
                'tanggal_hapus' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}