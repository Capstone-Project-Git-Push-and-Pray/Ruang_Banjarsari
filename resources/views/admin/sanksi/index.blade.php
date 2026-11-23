<x-admin-layout>
    @php
        $title = 'Manajemen Sanksi';
        $breadcrumb = 'Manajemen Sanksi';
        $kelasInput = 'block w-full py-2 px-3 text-xs rounded-xl border border-[#E2E8E4] text-gray-800 bg-gray-50/40 focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F]';
    @endphp

    <div class="space-y-6">
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#E2E8E4] shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="font-extrabold text-2xl text-gray-900 tracking-tight">Manajemen Sanksi Warga</h1>
                <p class="text-xs sm:text-sm text-gray-600 mt-2 max-w-3xl">
                    Sanksi diterapkan otomatis saat aspirasi atau komentar dihapus karena spam, kata kasar, informasi palsu, atau data pribadi.
                    Ubah masa berlaku, buka kembali fitur warga yang sudah membaik, atau batalkan sanksi yang keliru.
                </p>
            </div>
            <span class="shrink-0 px-4 py-2 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm font-bold">{{ $jumlahBerlaku }} sanksi berlaku</span>
        </div>

        <form method="GET" action="{{ route('admin.sanksi.index') }}" class="bg-white p-5 rounded-3xl border border-[#E2E8E4] shadow-xs grid grid-cols-1 sm:grid-cols-4 gap-3.5 items-end">
            <div class="sm:col-span-2">
                <label for="q" class="block text-xs font-bold text-gray-700 mb-1.5">Cari Warga</label>
                <input id="q" name="q" type="text" maxlength="100" value="{{ request('q') }}" placeholder="Nama atau NIK..." class="{{ $kelasInput }}">
            </div>
            <div>
                <label for="fitur" class="block text-xs font-bold text-gray-700 mb-1.5">Fitur</label>
                <select id="fitur" name="fitur" class="{{ $kelasInput }}">
                    <option value="">Semua Fitur</option>
                    @foreach (\App\Enums\FiturSanksi::cases() as $fitur)
                        <option value="{{ $fitur->value }}" @selected(request('fitur') === $fitur->value)>{{ $fitur->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="block text-xs font-bold text-gray-700 mb-1.5">Status</label>
                <select id="status" name="status" onchange="this.form.submit()" class="{{ $kelasInput }}">
                    <option value="berlaku">Sedang berlaku</option>
                    <option value="semua" @selected(request('status') === 'semua')>Semua riwayat</option>
                </select>
            </div>
            <div class="sm:col-span-4 flex justify-end">
                <button type="submit" class="px-4 py-2 bg-[#1F6B4F] hover:bg-[#174B39] text-white text-xs rounded-xl font-semibold shadow-xs transition-colors cursor-pointer">Terapkan Filter</button>
            </div>
        </form>

        @forelse ($sanksiList as $sanksi)
            @php
                $status = $sanksi->status();
                $idForm = 'sanksi-'.$sanksi->id;
                $isFormAktif = old('form_sanksi') === $idForm;
                $riwayat = $sanksi->riwayatModerasi;
            @endphp
            <div id="{{ $idForm }}" class="bg-white rounded-3xl p-5 sm:p-6 border border-[#E2E8E4] shadow-xs space-y-4"
                 x-data="{ tindakan: @js($isFormAktif ? old('tindakan') : null), permanen: @js($isFormAktif ? (bool) old('permanen') : $sanksi->permanen) }">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="font-bold text-gray-900">{{ $sanksi->user->nama_lengkap }}
                            <span class="font-mono text-xs text-gray-400 ms-1">{{ $sanksi->user->nik }}</span>
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Fitur <strong class="text-gray-800">{{ $sanksi->fitur->label() }}</strong> &bull; Tingkat {{ $sanksi->tingkat }}
                            &bull; Mulai {{ $sanksi->mulai_pada->format('d M Y, H:i') }}
                        </p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $status->badgeClasses() }}">{{ $status->label() }}</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                    <div>
                        <span class="text-gray-500 block">Masa berlaku</span>
                        <span class="text-gray-900 font-medium">{{ $sanksi->dicabut_pada ? 'Dicabut '.$sanksi->dicabut_pada->format('d M Y, H:i') : $sanksi->keteranganMasaBerlaku() }}</span>
                    </div>
                    <div class="md:col-span-2">
                        <span class="text-gray-500 block">Pemicu</span>
                        <span class="text-gray-900 font-medium">{{ $riwayat?->jenis_konten->label() ?? 'Konten' }} dihapus: {{ $riwayat?->alasan?->label() ?? '-' }}</span>
                        @if ($riwayat)
                            <p class="text-gray-600 mt-1 whitespace-pre-line break-words line-clamp-3">{{ $riwayat->isi_konten }}</p>
                        @endif
                    </div>
                    @if ($sanksi->catatan_admin)
                        <div class="md:col-span-3 bg-gray-50 border border-gray-200 rounded-xl p-3">
                            <span class="text-gray-500">Catatan terakhir{{ $sanksi->diubahOleh ? ' oleh '.$sanksi->diubahOleh->nama_lengkap : '' }}:</span>
                            <span class="text-gray-800">{{ $sanksi->catatan_admin }}</span>
                        </div>
                    @endif
                </div>

                @if ($status !== \App\Enums\StatusSanksi::Dibatalkan)
                    <div class="flex flex-wrap gap-2">
                        @if (! $sanksi->dicabut_pada)
                            <button type="button" @click="tindakan = 'ubah'" class="px-3 py-1.5 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-[11px] font-semibold cursor-pointer">Ubah Masa Berlaku</button>
                            @if ($status === \App\Enums\StatusSanksi::Berlaku)
                                <button type="button" @click="tindakan = 'diakhiri'" class="px-3 py-1.5 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 text-[11px] font-semibold cursor-pointer">Buka Kembali Fitur</button>
                            @endif
                        @endif
                        <button type="button" @click="tindakan = 'dibatalkan'" class="px-3 py-1.5 rounded-lg border border-sky-200 bg-sky-50 text-sky-800 hover:bg-sky-100 text-[11px] font-semibold cursor-pointer">Batalkan (Kesalahan Moderasi)</button>
                    </div>

                    <form x-show="tindakan" x-cloak method="POST" action="{{ route('admin.sanksi.update', $sanksi) }}" class="p-4 rounded-2xl border border-[#E2E8E4] bg-[#F8FAF9] space-y-3">
                        @csrf
                        @method('patch')
                        <input type="hidden" name="form_sanksi" value="{{ $idForm }}">
                        <input type="hidden" name="tindakan" :value="tindakan">

                        <p class="text-xs text-gray-700" x-show="tindakan === 'diakhiri'">Fitur warga dibuka sekarang. Sanksi tetap dihitung sebagai pelanggaran untuk tingkat berikutnya.</p>
                        <p class="text-xs text-gray-700" x-show="tindakan === 'dibatalkan'">Sanksi dibatalkan karena keliru dan tidak dihitung sebagai pelanggaran.</p>

                        <div x-show="tindakan === 'ubah'" class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-end">
                            <div>
                                <label for="berakhir-{{ $idForm }}" class="block text-xs font-bold text-gray-700 mb-1">Berakhir Pada</label>
                                <input id="berakhir-{{ $idForm }}" type="datetime-local" name="berakhir_pada" :disabled="permanen"
                                       value="{{ $isFormAktif ? old('berakhir_pada') : $sanksi->berakhir_pada?->format('Y-m-d\TH:i') }}" class="{{ $kelasInput }} disabled:opacity-50">
                            </div>
                            <label class="inline-flex items-center gap-2 text-xs text-gray-700">
                                <input type="hidden" name="permanen" value="0">
                                <input type="checkbox" name="permanen" value="1" x-model="permanen" class="rounded border-gray-300 text-[#1F6B4F] focus:ring-[#1F6B4F]">
                                Jadikan permanen
                            </label>
                        </div>

                        <div>
                            <label for="catatan-{{ $idForm }}" class="block text-xs font-bold text-gray-700 mb-1">Catatan Alasan Perubahan (wajib, terlihat oleh warga)</label>
                            <textarea id="catatan-{{ $idForm }}" name="catatan_admin" rows="2" maxlength="1000" required class="{{ $kelasInput }}">{{ $isFormAktif ? old('catatan_admin') : '' }}</textarea>
                        </div>
                        @if ($isFormAktif)
                            <x-input-error :messages="array_merge($errors->get('tindakan'), $errors->get('berakhir_pada'), $errors->get('catatan_admin'))" />
                        @endif
                        <div class="flex justify-end gap-2">
                            <button type="button" @click="tindakan = null" class="px-3 py-1.5 text-xs border border-gray-300 rounded-lg text-gray-600 hover:bg-white cursor-pointer">Batal</button>
                            <button type="submit" class="px-3 py-1.5 text-xs bg-[#1F6B4F] hover:bg-[#174B39] text-white font-semibold rounded-lg cursor-pointer">Simpan Keputusan</button>
                        </div>
                    </form>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-3xl p-12 border border-[#E2E8E4] text-center shadow-xs text-sm text-gray-500">
                Tidak ada sanksi yang sesuai dengan filter.
            </div>
        @endforelse

        @if ($sanksiList->hasPages())
            <div class="bg-white p-4 sm:p-5 rounded-3xl border border-[#E2E8E4] shadow-xs">{{ $sanksiList->links() }}</div>
        @endif
    </div>
</x-admin-layout>
