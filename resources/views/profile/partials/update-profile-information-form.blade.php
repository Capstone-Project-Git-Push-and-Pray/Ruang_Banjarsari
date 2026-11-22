<section>
    <header>
        <h2 class="text-lg font-bold text-gray-900">
            Informasi Profil & Kontak
        </h2>

        <p class="mt-1 text-xs text-gray-600">
            Perbarui data kontak Anda seperti alamat email dan nomor telepon yang aktif.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        @if ($user->isWarga())
            {{-- NIK (Terkunci untuk Warga) --}}
            <div>
                <x-input-label for="nik" value="Nomor Induk Kependudukan (NIK)" />
                <x-text-input id="nik" type="text" class="mt-1 block w-full bg-gray-100 text-gray-600 font-mono cursor-not-allowed" :value="$user->nik" disabled />
                <p class="text-[11px] text-gray-400 mt-1">NIK terdaftar tidak dapat diubah secara mandiri.</p>
            </div>

            {{-- Nama Lengkap (Terkunci untuk Warga) --}}
            <div>
                <x-input-label for="nama_lengkap" value="Nama Lengkap Sesuai KTP" />
                <x-text-input id="nama_lengkap" type="text" class="mt-1 block w-full bg-gray-100 text-gray-600 cursor-not-allowed uppercase" :value="$user->nama_lengkap" disabled />
                <p class="text-[11px] text-gray-400 mt-1">Nama lengkap sesuai identitas resmi kependudukan dan tidak dapat diubah sendiri.</p>
            </div>
        @else
            <div>
                <x-input-label for="name" value="Nama Lengkap" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->nama_lengkap ?? $user->name)" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>
        @endif

        {{-- Email --}}
        <div>
            <x-input-label for="email" value="Alamat Email" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#1F6B4F]">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{-- Nomor Telepon --}}
        <div>
            <x-input-label for="telepon" value="Nomor Telepon / HP" />
            <x-text-input id="telepon" name="telepon" type="tel" class="mt-1 block w-full" :value="old('telepon', $user->telepon)" placeholder="Contoh: 081234567890" />
            <x-input-error class="mt-2" :messages="$errors->get('telepon')" />
        </div>

        {{-- Nomor WhatsApp --}}
        <div>
            <x-input-label for="whatsapp" value="Nomor WhatsApp" />
            <x-text-input id="whatsapp" name="whatsapp" type="tel" class="mt-1 block w-full" :value="old('whatsapp', $user->whatsapp)" placeholder="Contoh: 081234567890" />
            <x-input-error class="mt-2" :messages="$errors->get('whatsapp')" />
        </div>

        <div class="flex items-center gap-4 pt-2">
            <x-primary-button class="bg-[#1F6B4F] hover:bg-[#174B39]">Simpan Perubahan</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="text-xs font-semibold text-emerald-600"
                >Data profil berhasil diperbarui.</p>
            @endif
        </div>
    </form>
</section>
