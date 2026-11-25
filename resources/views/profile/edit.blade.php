@php
    $title = 'Profil Saya';
    $inisial = collect(explode(' ', trim((string) $user->nama_lengkap)))->filter()->take(2)->map(fn ($kata) => mb_strtoupper(mb_substr($kata, 0, 1)))->implode('') ?: 'U';
    $namaTampil = $user->isWarga() ? $user->nama_lengkap : \Illuminate\Support\Str::title(\Illuminate\Support\Str::lower((string) $user->nama_lengkap));
@endphp

<x-dynamic-component :component="$user->isAdminDesa() ? 'admin-layout' : 'app-layout'">
    <div class="{{ $user->isAdminDesa() ? '' : 'max-w-5xl mx-auto' }} space-y-6">

        @unless ($user->isAdminDesa())
            <a href="{{ route('dashboard') }}" class="tombol-kembali inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-gray-500 hover:text-gray-900 transition-colors group">
                <svg class="w-4 h-4 text-gray-400 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Beranda</span>
            </a>
        @endunless

        {{-- Pemberitahuan wajib ganti kata sandi sementara --}}
        @if ($user->wajib_ganti_password)
            <div class="flex items-start gap-3 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900">
                <svg class="w-5 h-5 shrink-0 text-amber-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m0 3.75h.008M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                <div class="text-sm">
                    <p class="font-bold">Ganti kata sandi sementara Anda</p>
                    <p class="mt-0.5 text-xs leading-relaxed">Admin desa telah mengatur ulang kata sandi akun Anda. Isi kata sandi sementara pada kolom <strong>Kata Sandi Saat Ini</strong>, lalu buat kata sandi baru sebelum memakai fitur lain.</p>
                </div>
            </div>
        @endif

        {{-- ==================== KARTU IDENTITAS ==================== --}}
        <div class="bg-white rounded-3xl border border-[#E2E8E4] shadow-xs overflow-hidden">
            <div class="relative h-28 sm:h-32 bg-gradient-to-br from-[#1A5B42] via-[#1F6B4F] to-[#2E8B65] overflow-hidden">
                <div class="absolute -right-10 -top-12 w-48 h-48 rounded-full bg-white/10 blur-xl"></div>
                <div class="absolute left-1/3 -bottom-16 w-56 h-56 rounded-full bg-black/10 blur-2xl"></div>
                <div class="absolute inset-0 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:18px_18px] opacity-[0.08]"></div>
            </div>
            <div class="px-6 sm:px-8 pb-6">
                <div class="relative z-10 flex flex-col sm:flex-row sm:items-start gap-4 sm:gap-5 -mt-12 sm:-mt-14">
                    <div class="w-24 h-24 sm:w-28 sm:h-28 shrink-0 rounded-3xl bg-gradient-to-br from-[#1F6B4F] to-[#174B39] ring-4 ring-white shadow-lg flex items-center justify-center">
                        <span class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">{{ $inisial }}</span>
                    </div>
                    <div class="min-w-0 flex-1 sm:pt-[4.25rem]">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="font-extrabold text-xl sm:text-2xl text-gray-900 tracking-tight break-words">{{ $namaTampil }}</h1>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#EAF5EE] text-[#1F6B4F] border border-[#1F6B4F]/20">{{ $user->role->label() }}</span>
                            @if ($user->isWarga())
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $user->status_akun->badgeClasses() }}">{{ $user->status_akun->label() }}</span>
                            @endif
                        </div>
                        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-gray-500">
                            @if ($user->isWarga() && $user->nik)
                                <span class="inline-flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg><span class="font-mono">{{ $user->nik }}</span></span>
                            @endif
                            @if ($user->email)
                                <span class="inline-flex items-center gap-1.5 break-all"><svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>{{ $user->email }}</span>
                            @endif
                            @if ($user->telepon)
                                <span class="inline-flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.95.68l1.5 4.5a1 1 0 01-.5 1.21l-2.26 1.13a11.04 11.04 0 005.52 5.52l1.13-2.26a1 1 0 011.21-.5l4.5 1.5a1 1 0 01.68.95V19a2 2 0 01-2 2h-1C9.72 21 3 14.28 3 6V5z"/></svg>{{ $user->telepon }}</span>
                            @endif
                            <span class="inline-flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>Bergabung {{ $user->created_at?->translatedFormat('d F Y') }}</span>
                        </div>
                    </div>
                </div>

                @if ($user->isWarga())
                    <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                        <div class="rounded-2xl bg-[#F8FAF9] border border-[#E2E8E4] px-4 py-3">
                            <p class="text-gray-500">Alamat</p>
                            <p class="mt-0.5 font-semibold text-gray-800 break-words">{{ $user->alamat ?: '-' }}</p>
                        </div>
                        <div class="rounded-2xl bg-[#F8FAF9] border border-[#E2E8E4] px-4 py-3">
                            <p class="text-gray-500">RT / RW</p>
                            <p class="mt-0.5 font-semibold text-gray-800">{{ $user->rt ?: '-' }} / {{ $user->rw ?: '-' }}</p>
                        </div>
                        <div class="rounded-2xl bg-[#F8FAF9] border border-[#E2E8E4] px-4 py-3">
                            <p class="text-gray-500">Dusun</p>
                            <p class="mt-0.5 font-semibold text-gray-800">{{ $user->dusun ?: '-' }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ==================== FORMULIR ==================== --}}
        <div class="grid grid-cols-1 xl:grid-cols-5 gap-6 items-start">
            <div class="xl:col-span-3 bg-white rounded-3xl border border-[#E2E8E4] shadow-xs p-6 sm:p-8">
                @include('profile.partials.update-profile-information-form')
            </div>
            <div class="xl:col-span-2 space-y-6">
                <div class="bg-white rounded-3xl border border-[#E2E8E4] shadow-xs p-6 sm:p-7">
                    @include('profile.partials.update-password-form')
                </div>
                @if ($user->isWarga())
                    <div class="bg-white rounded-3xl border border-rose-200 shadow-xs p-6 sm:p-7">
                        @include('profile.partials.delete-user-form')
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-dynamic-component>
