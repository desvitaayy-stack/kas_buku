<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Pengeluaran') }}
            </h2>
            <button type="button" class="btn-sm btn btn-secondary" data-bs-toggle="modal" data-bs-target="#tambah">
                + Tambah
            </button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                <div class="overflow-x-auto">
                    <table class="w-full border border-gray-200 text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 border text-center" style="width: 10px;">No</th>
                                <th class="px-4 py-2 border">Nama</th>
                                <th class="px-4 py-2 border">Keterangan</th>
                                <th class="px-4 py-2 border">Jumlah</th>
                                <th class="px-4 py-2 border">Tanggal</th>
                                <th class="px-4 py-2 border text-center" style="width: 10px;">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($pengeluaran as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border text-center" style="width: 10px;">{{ $loop->iteration }}</td>
                                <td class="px-4 py-2 border">{{ $item->username }}</td>
                                <td class="px-4 py-2 border">{{ $item->keterangan }}</td>
                                <td class="px-4 py-2 border">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                                <td class="px-4 py-2 border">{{ $item->tanggal }}</td>
                                <td class="px-4 py-2 border text-center" style="width: 10px;">
                                    <div class="flex justify-center gap-2">
                                        <a data-bs-toggle="modal" data-bs-target="#edit{{ $item->id_keuangan }}" class="text-warning">Edit</a>
                                        <a data-bs-toggle="modal" data-bs-target="#hapus{{ $item->id_keuangan }}" class="text-danger">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                    Belum ada data pengeluaran
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="tambah" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Tambah pengeluaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Modal Body -->
                <form action="pengeluaran/create" method="post">
                    @csrf
                    <div class="modal-body">
                        <!-- Form bisa ditempatkan di sini -->
                        <div class="mb-3">
                            <label>Nominal *</label>
                            <input type="number" name="nominal" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Keterangan *</label>
                            <input type="text" name="keterangan" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Tanggal</label>
                            <input type="date" name="tanggal" class="form-control">
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    @foreach ($pengeluaran as $item)
    <div class="modal fade" id="edit{{ $item->id_keuangan }}" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Edit pengeluaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Modal Body -->
                <form action="pengeluaran/{{ $item->id_keuangan }}" method="post">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <!-- Form bisa ditempatkan di sini -->
                        <div class="mb-3">
                            <label>Nominal *</label>
                            <input type="number" name="nominal" value="{{ $item->nominal }}" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Keterangan *</label>
                            <textarea name="keterangan" class="form-control" required>{{ $item->keterangan }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label>Tanggal</label>
                            <input type="date" name="tanggal" value="{{ $item->tanggal }}" class="form-control">
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <div class="modal fade" id="hapus{{ $item->id_keuangan }}" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Hapus pengeluaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Modal Body -->
                <form action="pengeluaran/{{ $item->id_keuangan }}" method="post">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <!-- Form bisa ditempatkan di sini -->
                        Yakin ingin menghapus pengeluaran "{{ $item->keterangan }}" dengan nominal Rp {{ number_format($item->nominal, 0, ',', '.') }}?
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    @endforeach
</x-app-layout>
