<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Pengumuman') }}
            </h2>

            <div class="flex gap-2">
                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#hapusSemua">
                    Hapus Semua
                </button>

                <button class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#tambah">
                    + Tambah Pengumuman
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                <div class="overflow-x-auto">
                    <table class="w-full border border-gray-200 text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 border text-center" style="width: 10px;">No</th>
                                <th class="px-3 py-2 border">Judul</th>
                                <th class="px-3 py-2 border">Isi</th>
                                <th class="px-3 py-2 border">Status</th>
                                <th class="px-3 py-2 border">Berlaku Sampai</th>
                                <th class="px-3 py-2 border text-center" style="width: 10px;">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($pengumuman as $item)
                            @php
                                $expired=$item->tanggal_hapus &&
                                $item->tanggal_hapus->lt(\Carbon\Carbon::today());
                            @endphp


                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 border text-center" style="width: 10px;">
                                        {{ $loop->iteration }}
                                    </td>

                                    <td class="px-3 py-2 border font-semibold">
                                        {{ $item->judul }}
                                    </td>

                                    <td class="px-3 py-2 border">
                                        {{ $item->isi }}
                                    </td>

                                    <td class="px-3 py-2 border">
                                        @if ($expired)
                                        <span class="badge bg-danger">Expired</span>
                                        @else
                                        <span class="badge bg-success">Aktif</span>
                                        @endif
                                    </td>

                                    <td class="px-3 py-2 border">
                                        {{ $item->tanggal_hapus
                                            ? $item->tanggal_hapus->format('d M Y')
                                            : 'Tidak terbatas' }}
                                    </td>

                                    <td class="px-3 py-2 border text-center" style="width: 10px;">
                                        <a class="text-warning cursor-pointer" data-bs-toggle="modal"
                                            data-bs-target="#edit{{ $item->id }}">
                                            Edit
                                        </a>
                                        <a class="text-danger cursor-pointer" data-bs-toggle="modal"
                                            data-bs-target="#hapus{{ $item->id }}">
                                            Hapus
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                        Belum ada pengumuman
                                    </td>
                                </tr>
                                @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
    <div class="modal fade" id="tambah" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('pengumuman.store') }}" class="modal-content">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pengumuman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label>Judul *</label>
                        <input type="text" name="judul" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Isi *</label>
                        <textarea name="isi" class="form-control" rows="4" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label>Berlaku Sampai</label>
                        <input type="date" name="tanggal_hapus" class="form-control">
                        <small class="text-muted">
                            Kosongkan jika pengumuman permanen
                        </small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
    @foreach ($pengumuman as $item)
    <div class="modal fade" id="edit{{ $item->id }}" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="pengumuman/{{ $item->id }}" class="modal-content">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title">Edit Pengumuman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label>Judul *</label>
                        <input type="text" name="judul" value="{{ $item->judul }}" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Isi *</label>
                        <textarea name="isi" class="form-control" rows="4" required>{{ $item->isi }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label>Berlaku Sampai</label>
                        <input type="date" name="tanggal_hapus" value="{{ $item->tanggal_hapus?->format('Y-m-d') }}"
                            class="form-control">
                        <small class="text-muted">
                            Kosongkan jika permanen
                        </small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="hapus{{ $item->id }}" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('pengumuman.destroy', $item->id) }}" class="modal-content">
                @csrf
                @method('DELETE')

                <div class="modal-header">
                    <h5 class="modal-title">Hapus Pengumuman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    Yakin ingin menghapus pengumuman
                    <strong>"{{ $item->judul }}"</strong>?
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-danger">
                        Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endforeach
    <div class="modal fade" id="hapusSemua" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="pengumuman/destroyAll" class="modal-content">
                @csrf
                @method('DELETE')

                <div class="modal-header">
                    <h5 class="modal-title">Hapus Semua Pengumuman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body text-danger">
                    Semua histori pengumuman akan dihapus permanen.
                    Tindakan ini tidak bisa dibatalkan.
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-danger">
                        Hapus Semua
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
