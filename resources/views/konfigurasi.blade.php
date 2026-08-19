<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Konfigurasi Profil & Media Sosial') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            {{-- Form konfigurasi, enctype untuk file upload --}}
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <form action="konfigurasi/update" method="post" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label for="judul" class="form-label">Judul Website</label>
                                        <input type="text" class="form-control" value="{{ $konfigurasi->judul }}"
                                            name="judul" id="judul">
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label for="profil" class="form-label">Profil Website (Gambar)</label>
                                        <input type="file" class="form-control" name="profil" id="profil">

                                        @if ($konfigurasi->profil)
                                        <div class="mt-2">
                                            <p class="mb-1">Gambar saat ini:</p>
                                            <img src="{{ asset($konfigurasi->profil) }}" alt="Profil Website"
                                                class="img-thumbnail" style="max-width: 200px;">
                                        </div>
                                        @endif
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="instagram" class="form-label">Instagram</label>
                                        <input type="text" class="form-control" value="{{ $konfigurasi->instagram }}"
                                            name="instagram" id="instagram">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="facebook" class="form-label">Facebook</label>
                                        <input type="text" class="form-control" value="{{ $konfigurasi->facebook }}"
                                            name="facebook" id="facebook">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="tiktok" class="form-label">Tiktok</label>
                                        <input type="text" class="form-control" value="{{ $konfigurasi->tiktok }}"
                                            name="tiktok" id="tiktok">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" value="{{ $konfigurasi->email }}"
                                            name="email" id="email">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Telepon</label>
                                        <input type="text" class="form-control" value="{{ $konfigurasi->telepon }}"
                                            name="telepon" id="telepon">
                                    </div>

                                    <div class="col-12 col-md-6 mt-5">
                                        <button type="submit" class="btn btn-primary float-end">Simpan</button>
                                    </div>

                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
