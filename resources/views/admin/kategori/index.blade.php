<x-admin-layout>
    @php
        $title = 'Manajemen Kategori Laporan';
        $breadcrumb = 'Pengaturan Kategori';
    @endphp

    <div class="space-y-6">

        {{-- ==================== 1. HERO COMMAND BANNER ==================== --}}
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#E2E8E4] shadow-xs space-y-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                {{-- Title & Info --}}
                <div class="space-y-2 max-w-3xl">
                    <h1 class="font-extrabold text-2xl sm:text-3xl text-gray-900 tracking-tight leading-snug">
                        Manajemen Kategori Laporan
                    </h1>

                    <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                        Kelola master topik dan kategori pengaduan serta aspirasi warga Desa Banjarsari. Kategori yang aktif akan ditampilkan pada pilihan form pelaporan warga.
                    </p>
                </div>

                {{-- Action Shortcuts --}}
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <a href="{{ route('admin.pengaduan.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#E2E8E4] hover:border-gray-300 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 transition shadow-2xs">
                        <i data-lucide="arrow-left" class="w-4 h-4 text-[#1F6B4F]"></i>
                        <span>Kembali ke Laporan</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- ==================== NOTIFIKASI SUKSES / ERROR ==================== --}}
        @if (session('status'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-5 rounded-3xl flex items-center gap-4 shadow-xs">
                <div class="w-10 h-10 rounded-2xl bg-emerald-100 flex items-center justify-center shrink-0 text-emerald-700 border border-emerald-200">
                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                </div>
                <div>
                    <span class="font-extrabold text-sm text-emerald-950 block">Berhasil!</span>
                    <span class="text-xs text-emerald-800 mt-0.5 block">
                        @if (session('status') === 'kategori-berhasil-ditambahkan')
                            Kategori baru berhasil ditambahkan ke daftar master.
                        @elseif (session('status') === 'kategori-berhasil-diperbarui')
                            Nama kategori berhasil diperbarui.
                        @elseif (session('status') === 'kategori-diaktifkan')
                            Kategori berhasil diaktifkan kembali dan kini dapat dipilih oleh warga.
                        @elseif (session('status') === 'kategori-dinonaktifkan')
                            Kategori berhasil dinonaktifkan. Kategori ini tidak akan muncul pada formulir pengajuan warga baru.
                        @elseif (session('status') === 'kategori-berhasil-dihapus')
                            Kategori berhasil dihapus secara permanen.
                        @else
                            {{ session('status') }}
                        @endif
                    </span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-800 p-5 rounded-3xl flex items-center gap-4 shadow-xs">
                <div class="w-10 h-10 rounded-2xl bg-rose-100 flex items-center justify-center shrink-0 text-rose-700 border border-rose-200">
                    <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                </div>
                <div>
                    <span class="font-extrabold text-sm text-rose-950 block">Perhatian</span>
                    <span class="text-xs text-rose-800 mt-0.5 block">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        {{-- ==================== 2. FORMULIR TAMBAH KATEGORI BARU (PBI #30) ==================== --}}
        <div class="bg-white p-6 sm:p-7 rounded-3xl shadow-xs border border-[#E2E8E4] space-y-4">
            <div class="flex items-center gap-2.5 pb-2 border-b border-gray-100">
                <div class="w-8 h-8 rounded-xl bg-[#EAF5EE] text-[#1F6B4F] flex items-center justify-center shrink-0">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900">
                        Tambah Kategori Baru
                    </h3>
                    <p class="text-[11px] text-gray-500">Tambahkan opsi kategori laporan baru yang relevan dengan kebutuhan desa</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.kategori.store') }}" class="flex flex-col sm:flex-row gap-3 items-start">
                @csrf
                <div class="flex-1 w-full">
                    <input id="nama"
                           name="nama"
                           type="text"
                           class="w-full text-xs rounded-xl border border-[#E2E8E4] py-2.5 px-4 text-gray-800 focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F] bg-gray-50/40"
                           value="{{ old('nama') }}"
                           placeholder="Nama kategori baru (contoh: Jaringan Irigasi, Pengelolaan Sampah, Penerangan Jalan...)"
                           required />
                    <x-input-error class="mt-1" :messages="$errors->get('nama')" />
                </div>
                <div class="shrink-0 w-full sm:w-auto">
                    <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-[#1F6B4F] hover:bg-[#174B39] text-white text-xs font-semibold rounded-xl shadow-xs transition-colors inline-flex items-center justify-center gap-2">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Simpan Kategori</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- ==================== 3. TABEL DAFTAR KATEGORI ==================== --}}
        <div class="bg-white overflow-hidden shadow-xs rounded-3xl border border-[#E2E8E4]">
            <div class="p-5 sm:p-6 border-b border-[#E2E8E4] flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-gray-50 text-gray-600 flex items-center justify-center shrink-0 border border-gray-200">
                        <i data-lucide="tags" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-sm sm:text-base text-gray-900">Daftar Kategori Laporan</h3>
                        <p class="text-[11px] text-gray-500">Total: <strong>{{ $kategoris->total() }}</strong> kategori terdaftar</p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#E2E8E4] text-left text-sm">
                    <thead class="bg-[#F8FAF9] text-gray-500 text-[11px] uppercase tracking-wider font-bold border-b border-[#E2E8E4]">
                        <tr>
                            <th class="px-6 py-4">Nama Kategori</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Penggunaan</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8E4]">
                        @foreach ($kategoris as $kategori)
                            <tr class="hover:bg-[#F8FAF9]/80 transition-colors" x-data="{ editing: false, nama: '{{ addslashes($kategori->nama) }}' }">
                                {{-- Nama Kategori --}}
                                <td class="px-6 py-4">
                                    {{-- Tampilan Normal --}}
                                    <div x-show="!editing" class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-emerald-50 text-[#1F6B4F] flex items-center justify-center shrink-0 border border-emerald-100">
                                            <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                                        </div>
                                        <span class="font-bold text-gray-900 block text-sm">{{ $kategori->nama }}</span>
                                    </div>

                                    {{-- Formulir Edit Nama Kategori --}}
                                    <div x-show="editing" x-cloak class="mt-1">
                                        <form method="POST" action="{{ route('admin.kategori.update', $kategori) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('put')
                                            <input type="text"
                                                   name="nama"
                                                   x-model="nama"
                                                   required
                                                   class="text-xs rounded-xl border border-[#E2E8E4] focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F] py-1.5 px-3">
                                            <button type="submit" class="px-3 py-1.5 bg-[#1F6B4F] text-white text-xs font-semibold rounded-xl hover:bg-[#174B39] shadow-2xs transition">
                                                Simpan
                                            </button>
                                            <button type="button" @click="editing = false" class="px-3 py-1.5 border border-gray-300 text-gray-600 text-xs font-medium rounded-xl hover:bg-gray-50 transition">
                                                Batal
                                            </button>
                                        </form>
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="px-6 py-4">
                                    @if ($kategori->aktif)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                            <span>Aktif</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                            <span>Nonaktif</span>
                                        </span>
                                    @endif
                                </td>

                                {{-- Penggunaan --}}
                                <td class="px-6 py-4 text-xs text-gray-600">
                                    <span class="font-mono font-bold text-gray-900 bg-gray-50 px-2 py-0.5 rounded-md border border-gray-200">{{ $kategori->pengaduan_count }}</span>
                                    <span class="ml-1 text-gray-500">laporan tercatat</span>
                                </td>

                                {{-- Aksi --}}
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- Tombol Ubah Nama --}}
                                        <button type="button"
                                                @click="editing = !editing"
                                                class="p-2 rounded-xl text-gray-500 hover:text-[#1F6B4F] hover:bg-emerald-50 transition-colors border border-transparent hover:border-emerald-100"
                                                title="Ubah Nama Kategori">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>

                                        {{-- Tombol Aktifkan / Nonaktifkan (PBI #30) --}}
                                        <form method="POST" action="{{ route('admin.kategori.toggle', $kategori) }}" class="inline">
                                            @csrf
                                            @method('patch')
                                            @if ($kategori->aktif)
                                                <button type="submit"
                                                        class="px-3 py-1.5 text-xs font-bold rounded-xl border border-amber-300 text-amber-800 bg-amber-50 hover:bg-amber-100 transition shadow-2xs"
                                                        title="Nonaktifkan Kategori">
                                                    Nonaktifkan
                                                </button>
                                            @else
                                                <button type="submit"
                                                        class="px-3 py-1.5 text-xs font-bold rounded-xl border border-emerald-300 text-emerald-800 bg-emerald-50 hover:bg-emerald-100 transition shadow-2xs"
                                                        title="Aktifkan Kategori">
                                                    Aktifkan
                                                </button>
                                            @endif
                                        </form>

                                        {{-- Tombol Hapus (Kriteria: hanya bisa dihapus jika BELUM pernah dipakai laporan) --}}
                                        @if ($kategori->pengaduan_count === 0)
                                            <form method="POST" action="{{ route('admin.kategori.destroy', $kategori) }}" data-konfirmasi="Kategori {{ $kategori->nama }} akan dihapus permanen dan tidak dapat dipilih lagi saat membuat laporan." data-konfirmasi-judul="Hapus kategori ini?" data-konfirmasi-tombol="Hapus" data-konfirmasi-jenis="bahaya" class="inline">
                                                @csrf
                                                @method('delete')
                                                <button type="submit"
                                                        class="p-2 rounded-xl text-rose-600 hover:bg-rose-50 transition-colors border border-transparent hover:border-rose-200"
                                                        title="Hapus Kategori">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-gray-400 bg-gray-50 px-2 py-1 rounded-lg border border-gray-200 cursor-not-allowed" title="Kategori telah dipakai dalam laporan, hanya dapat dinonaktifkan.">
                                                <i data-lucide="lock" class="w-3 h-3 text-gray-400"></i>
                                                <span>Terkunci</span>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-[#E2E8E4] bg-white">
                {{ $kategoris->links() }}
            </div>
        </div>

    </div>
</x-admin-layout>
