<x-admin-layout>
    @php
        $title = 'Bantuan Reset Akun';
        $breadcrumb = 'Bantuan Reset Akun';
        $kelasInput = 'block w-full py-2 px-3 text-xs rounded-xl border border-[#E2E8E4] text-gray-800 bg-gray-50/40 focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F]';
        $kataSandiBaru = session('kata_sandi_sementara');
    @endphp

    <div class="space-y-6">
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#E2E8E4] shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="font-extrabold text-2xl text-gray-900 tracking-tight">Bantuan Reset Akun Warga</h1>
                <p class="text-xs sm:text-sm text-gray-600 mt-2 max-w-3xl">
                    Bantu warga yang lupa kata sandi. Kirim tautan atur ulang ke email warga, atau buat kata sandi sementara
                    bila warga tidak dapat membuka emailnya. Pastikan identitas warga sudah dicocokkan dengan KTP sebelum mereset.
                </p>
            </div>
            <span class="shrink-0 px-4 py-2 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-sm font-bold">{{ $jumlahMenungguGanti }} menunggu ganti kata sandi</span>
        </div>

        @if ($kataSandiBaru)
            <div x-data="{ tersalin: false }" class="bg-amber-50 border border-amber-200 rounded-3xl p-5 sm:p-6 space-y-3">
                <div class="flex items-start gap-3">
                    <i data-lucide="key-round" class="w-5 h-5 text-amber-700 shrink-0 mt-0.5"></i>
                    <div class="text-xs sm:text-sm text-amber-900">
                        <p class="font-bold">Kata sandi sementara untuk {{ $kataSandiBaru['nama'] }}</p>
                        <p class="mt-1">Sampaikan langsung kepada warga. Kata sandi ini hanya ditampilkan sekali, dan warga wajib menggantinya saat masuk.</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <code x-ref="kataSandi" class="px-4 py-2 rounded-xl bg-white border border-amber-300 font-mono text-base font-bold tracking-wider text-gray-900 select-all">{{ $kataSandiBaru['kata_sandi'] }}</code>
                    <button type="button" @click="navigator.clipboard.writeText($refs.kataSandi.innerText); tersalin = true"
                            class="px-3 py-2 rounded-xl border border-amber-300 bg-white hover:bg-amber-100 text-amber-900 text-xs font-semibold cursor-pointer">
                        <span x-text="tersalin ? 'Tersalin' : 'Salin'"></span>
                    </button>
                </div>
            </div>
        @endif

        <form method="GET" action="{{ route('admin.reset-akun.index') }}" class="bg-white p-5 rounded-3xl border border-[#E2E8E4] shadow-xs grid grid-cols-1 sm:grid-cols-4 gap-3.5 items-end">
            <div class="sm:col-span-3">
                <label for="q" class="block text-xs font-bold text-gray-700 mb-1.5">Cari Warga</label>
                <input id="q" name="q" type="text" maxlength="100" value="{{ request('q') }}" placeholder="Nama, NIK, atau email..." class="{{ $kelasInput }}">
            </div>
            <x-filter-dropdown name="status" label="Status Akun"
                               :selected="request('status', '')"
                               :options="['' => 'Semua Status'] + collect(\App\Enums\StatusAkun::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()])->all()" />
            <div class="sm:col-span-4 flex justify-end">
                <div class="flex flex-col gap-2 w-full sm:w-auto">
                    <button type="submit" class="px-4 py-2 bg-[#1F6B4F] hover:bg-[#174B39] text-white text-xs rounded-xl font-semibold shadow-xs transition-colors cursor-pointer">Terapkan Filter</button>
                    @if (request()->anyFilled(['q', 'status']))
                        <a href="{{ route('admin.reset-akun.index') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl border border-[#E2E8E4] bg-white hover:bg-gray-50 text-gray-600 text-xs font-semibold transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>Hapus Filter</span>
                        </a>
                    @endif
                </div>
            </div>
        </form>

        @forelse ($wargaList as $warga)
            <div class="bg-white rounded-3xl p-5 sm:p-6 border border-[#E2E8E4] shadow-xs flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="font-bold text-gray-900">{{ $warga->nama_lengkap }}</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $warga->status_akun->badgeClasses() }}">{{ $warga->status_akun->label() }}</span>
                        @if ($warga->wajib_ganti_password)
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border bg-amber-50 border-amber-200 text-amber-800">Wajib ganti kata sandi</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 mt-1 break-all">
                        <span class="font-mono">{{ $warga->nik }}</span> &bull; {{ $warga->email }}
                        @if ($warga->whatsapp)
                            &bull; WA {{ $warga->whatsapp }}
                        @endif
                    </p>
                </div>

                <div class="flex flex-wrap gap-2 shrink-0">
                    <form method="POST" action="{{ route('admin.reset-akun.kirim-tautan', $warga) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-xs font-semibold cursor-pointer">
                            <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                            Kirim Tautan ke Email
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.reset-akun.kata-sandi-sementara', $warga) }}"
                          onsubmit="return confirm({{ Js::from('Buat kata sandi sementara untuk '.$warga->nama_lengkap.'? Kata sandi lama tidak dapat dipakai lagi.') }})">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-[#1F6B4F] hover:bg-[#174B39] text-white text-xs font-semibold cursor-pointer">
                            <i data-lucide="key-round" class="w-3.5 h-3.5"></i>
                            Buat Kata Sandi Sementara
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-3xl p-12 border border-[#E2E8E4] text-center shadow-xs text-sm text-gray-500">
                Tidak ada akun warga yang sesuai dengan pencarian.
            </div>
        @endforelse

        @if ($wargaList->hasPages())
            <div class="bg-white p-4 sm:p-5 rounded-3xl border border-[#E2E8E4] shadow-xs">{{ $wargaList->links() }}</div>
        @endif
    </div>
</x-admin-layout>
