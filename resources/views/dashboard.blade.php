<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="container">

            <div class="row mb-4">
                {{-- PENGELUARAN --}}
                <div class="col-md-6 mb-2">
                    <div class="card border-danger shadow">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="text-danger fw-bold">Total Pengeluaran</span>
                            <form method="GET" class="m-0 p-0">
                                <select name="pengeluaran" onchange="this.form.submit()"
                                    class="form-select form-select-sm">
                                    <option value="today" {{ $filterPengeluaran=='today' ? 'selected' : '' }}>Hari Ini
                                    </option>
                                    <option value="month" {{ $filterPengeluaran=='month' ? 'selected' : '' }}>Bulan Ini
                                    </option>
                                    <option value="year" {{ $filterPengeluaran=='year' ? 'selected' : '' }}>Tahun Ini
                                    </option>
                                </select>
                                {{-- Simpan filter pemasukan supaya tidak hilang --}}
                                <input type="hidden" name="pemasukan" value="{{ $filterPemasukan }}">
                            </form>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title text-danger">
                                Rp {{ number_format($pengeluaran, 0, ',', '.') }}
                            </h3>
                        </div>
                    </div>
                </div>

                {{-- PEMASUKAN --}}
                <div class="col-md-6 mb-2">
                    <div class="card border-success shadow">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="text-success fw-bold">Total Pemasukan</span>
                            <form method="GET" class="m-0 p-0">
                                <select name="pemasukan" onchange="this.form.submit()"
                                    class="form-select form-select-sm">
                                    <option value="today" {{ $filterPemasukan=='today' ? 'selected' : '' }}>Hari Ini
                                    </option>
                                    <option value="month" {{ $filterPemasukan=='month' ? 'selected' : '' }}>Bulan Ini
                                    </option>
                                    <option value="year" {{ $filterPemasukan=='year' ? 'selected' : '' }}>Tahun Ini
                                    </option>
                                </select>
                                {{-- Simpan filter pengeluaran supaya tidak hilang --}}
                                <input type="hidden" name="pengeluaran" value="{{ $filterPengeluaran }}">
                            </form>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title text-success">
                                Rp {{ number_format($pemasukan, 0, ',', '.') }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            @if ($pengumuman->count())
            <div class="mb-6">
                <div class="bg-white p-6 rounded shadow">
                    <h1 class="font-semibold mb-2">📢 Pengumuman</h1>

                    @foreach ($pengumuman as $item)
                    <div class="mb-3 last:mb-0">
                        <div class="font-semibold">
                            {{ $item->judul }}
                        </div>

                        <div class="text-sm">
                            {{ $item->isi }}
                        </div>

                        @if ($item->tanggal_hapus)
                        <div class="text-xs text-gray-600 mt-1">
                            Berlaku sampai {{ $item->tanggal_hapus->format('d M Y') }}
                        </div>
                        @endif
                    </div>

                    @if (! $loop->last)
                    <hr class="my-2">
                    @endif
                    @endforeach
                </div>
            </div>
            @endif


            @if (Auth::user()->role === 'admin')
            {{-- RECENT LOGIN --}}
            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-lg font-bold mb-4">
                    Recent Login (Top 3)
                </h3>

                <table class="w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 border">Nama</th>
                            <th class="p-2 border">Email</th>
                            <th class="p-2 border">Terakhir Login</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLogin as $user)
                        <tr>
                            <td class="p-2 border">{{ $user->name }}</td>
                            <td class="p-2 border">{{ $user->email }}</td>
                            <td class="p-2 border">
                                {{ $user->recent_login
                                        ? $user->recent_login->diffForHumans()
                                        : '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center p-4">
                                Tidak ada data
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
