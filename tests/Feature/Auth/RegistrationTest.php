<?php

namespace Tests\Feature\Auth;

use App\Enums\StatusAkun;
use App\Models\DataKependudukan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Registrasi Akun Warga Baru');
    }

    public function test_warga_registers_with_matching_civil_registry_becomes_active(): void
    {
        DataKependudukan::factory()->create([
            'nik' => '3207011205950001',
            'nama_lengkap' => 'Budi Santoso',
            'tanggal_lahir' => '1995-05-12',
            'alamat' => 'Jl. Merpati No. 10',
            'rt' => '001',
            'rw' => '002',
            'dusun' => 'Dusun Krajan',
        ]);

        $response = $this->post('/register', [
            'nama_lengkap' => 'Budi Santoso',
            'nik' => '3207011205950001',
            'tanggal_lahir' => '1995-05-12',
            'alamat' => 'Jl. Merpati No. 10',
            'rt' => '001',
            'rw' => '002',
            'dusun' => 'Dusun Krajan',
            'telepon' => '081234567890',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'persetujuan' => '1',
        ]);

        $this->assertAuthenticated();

        $user = User::where('nik', '3207011205950001')->first();
        $this->assertNotNull($user);
        $this->assertSame(StatusAkun::Aktif, $user->status_akun);
        $this->assertNotNull($user->persetujuan_pada);

        $response->assertRedirect(route('dashboard'));
    }

    public function test_warga_registers_with_non_matching_name_becomes_menunggu_tinjauan(): void
    {
        DataKependudukan::factory()->create([
            'nik' => '3207011205950002',
            'nama_lengkap' => 'Siti Aminah',
        ]);

        $response = $this->post('/register', [
            'nama_lengkap' => 'Siti Fatimah',
            'nik' => '3207011205950002',
            'tanggal_lahir' => '1995-05-12',
            'alamat' => 'Jl. Merpati No. 12',
            'rt' => '001',
            'rw' => '002',
            'dusun' => 'Dusun Krajan',
            'telepon' => '081234567891',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'persetujuan' => '1',
        ]);

        $this->assertAuthenticated();

        $user = User::where('nik', '3207011205950002')->first();
        $this->assertNotNull($user);
        $this->assertSame(StatusAkun::MenungguTinjauan, $user->status_akun);

        $response->assertRedirect(route('pendaftaran.status'));
    }

    public function test_warga_registers_with_unregistered_nik_becomes_menunggu_tinjauan(): void
    {
        $response = $this->post('/register', [
            'nama_lengkap' => 'Agus Priyono',
            'nik' => '3207011205959999',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Jl. Mawar No. 1',
            'rt' => '003',
            'rw' => '001',
            'dusun' => 'Dusun Krajan',
            'email' => 'agus@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'persetujuan' => '1',
        ]);

        $this->assertAuthenticated();

        $user = User::where('nik', '3207011205959999')->first();
        $this->assertNotNull($user);
        $this->assertSame(StatusAkun::MenungguTinjauan, $user->status_akun);

        $response->assertRedirect(route('pendaftaran.status'));
    }

    public function test_name_normalization_ignores_casing_and_excessive_whitespace(): void
    {
        DataKependudukan::factory()->create([
            'nik' => '3207011205950003',
            'nama_lengkap' => 'Ahmad   Hidayatul   Mustofa',
            'tanggal_lahir' => '1993-04-10',
            'alamat' => 'Jl. Melati No. 5',
            'rt' => '002',
            'rw' => '001',
            'dusun' => 'Dusun Krajan',
        ]);

        $this->post('/register', [
            'nama_lengkap' => '   ahmad  hidayatul mustofa  ',
            'nik' => '3207011205950003',
            'tanggal_lahir' => '1993-04-10',
            'alamat' => 'Jl. Melati No. 5',
            'rt' => '002',
            'rw' => '001',
            'dusun' => 'Dusun Krajan',
            'telepon' => '081234567893',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'persetujuan' => '1',
        ]);

        $user = User::where('nik', '3207011205950003')->first();
        $this->assertNotNull($user);
        $this->assertSame(StatusAkun::Aktif, $user->status_akun);
        $this->assertSame('AHMAD HIDAYATUL MUSTOFA', $user->nama_lengkap);
    }

    public function test_warga_registers_with_matching_name_but_different_address_becomes_menunggu_tinjauan(): void
    {
        DataKependudukan::factory()->create([
            'nik' => '3207011205950033',
            'nama_lengkap' => 'Hendro Santoso',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Jl. Asli Sesuai Kependudukan No. 1',
            'rt' => '001',
            'rw' => '001',
            'dusun' => 'Dusun Krajan',
        ]);

        $response = $this->post('/register', [
            'nama_lengkap' => 'Hendro Santoso',
            'nik' => '3207011205950033',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Jl. Berbeda Dengan Master Data No. 99', // Alamat berbeda
            'rt' => '001',
            'rw' => '001',
            'dusun' => 'Dusun Krajan',
            'telepon' => '081234567833',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'persetujuan' => '1',
        ]);

        $this->assertAuthenticated();

        $user = User::where('nik', '3207011205950033')->first();
        $this->assertNotNull($user);
        // Terdapat data berbeda dengan mastersheet -> Menunggu Tinjauan
        $this->assertSame(StatusAkun::MenungguTinjauan, $user->status_akun);
        $response->assertRedirect(route('pendaftaran.status'));
    }

    public function test_registration_requires_16_digit_nik(): void
    {
        $response = $this->post('/register', [
            'nama_lengkap' => 'Warga Baru',
            'nik' => '12345',
            'tanggal_lahir' => '1995-05-12',
            'alamat' => 'Jl. Merpati No. 10',
            'rt' => '001',
            'rw' => '002',
            'dusun' => 'Dusun Krajan',
            'telepon' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'persetujuan' => '1',
        ]);

        $response->assertSessionHasErrors('nik');
        $this->assertGuest();
    }

    public function test_registration_enforces_one_nik_one_account(): void
    {
        User::factory()->create([
            'nik' => '3207011205950004',
        ]);

        $response = $this->post('/register', [
            'nama_lengkap' => 'Warga Baru',
            'nik' => '3207011205950004',
            'tanggal_lahir' => '1995-05-12',
            'alamat' => 'Jl. Merpati No. 10',
            'rt' => '001',
            'rw' => '002',
            'dusun' => 'Dusun Krajan',
            'telepon' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'persetujuan' => '1',
        ]);

        $response->assertSessionHasErrors('nik');
    }

    public function test_registration_requires_at_least_one_contact_between_email_and_phone(): void
    {
        // 1. Tanpa telepon dan tanpa email -> Gagal
        $response = $this->post('/register', [
            'nama_lengkap' => 'Warga Baru',
            'nik' => '3207011205950005',
            'tanggal_lahir' => '1995-05-12',
            'alamat' => 'Jl. Merpati No. 10',
            'rt' => '001',
            'rw' => '002',
            'dusun' => 'Dusun Krajan',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'persetujuan' => '1',
        ]);

        $response->assertSessionHasErrors(['email', 'telepon']);

        // 2. Hanya mengisi telepon -> Berhasil
        $responsePhoneOnly = $this->post('/register', [
            'nama_lengkap' => 'Warga Baru',
            'nik' => '3207011205950006',
            'tanggal_lahir' => '1995-05-12',
            'alamat' => 'Jl. Merpati No. 10',
            'rt' => '001',
            'rw' => '002',
            'dusun' => 'Dusun Krajan',
            'telepon' => '081234567899',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'persetujuan' => '1',
        ]);

        $responsePhoneOnly->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['nik' => '3207011205950006']);
    }

    public function test_registration_requires_password_minimum_8_characters_and_confirmed(): void
    {
        $response = $this->post('/register', [
            'nama_lengkap' => 'Warga Baru',
            'nik' => '3207011205950007',
            'tanggal_lahir' => '1995-05-12',
            'alamat' => 'Jl. Merpati No. 10',
            'rt' => '001',
            'rw' => '002',
            'dusun' => 'Dusun Krajan',
            'telepon' => '081234567890',
            'password' => '1234567',
            'password_confirmation' => '1234567',
            'persetujuan' => '1',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_registration_requires_terms_acceptance(): void
    {
        $response = $this->post('/register', [
            'nama_lengkap' => 'Warga Baru',
            'nik' => '3207011205950008',
            'tanggal_lahir' => '1995-05-12',
            'alamat' => 'Jl. Merpati No. 10',
            'rt' => '001',
            'rw' => '002',
            'dusun' => 'Dusun Krajan',
            'telepon' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('persetujuan');
    }

    public function test_menunggu_tinjauan_user_can_view_status_page(): void
    {
        $user = User::factory()->warga()->menungguTinjauan()->create();

        $response = $this->actingAs($user)->get('/pendaftaran/status');

        $response->assertStatus(200);
        $response->assertSee('Menunggu Tinjauan');
        $response->assertSee('Pengajuan Laporan dan Aduan Terkunci');
        $response->assertSee('Status Pendaftaran');
        $response->assertDontSee('Dashboard');
    }

    public function test_ditolak_user_can_view_status_page_and_see_rejection_reason(): void
    {
        $user = User::factory()->warga()->ditolak()->create([
            'alasan_penolakan' => 'Foto KTP buram dan nama tidak cocok.',
        ]);

        $response = $this->actingAs($user)->get('/pendaftaran/status');

        $response->assertStatus(200);
        $response->assertSee('Pendaftaran Akun Ditolak');
        $response->assertSee('Foto KTP buram dan nama tidak cocok.');
        $response->assertSee('Perbaiki Data dan Ajukan Ulang');
    }

    public function test_pengajuan_laporan_is_locked_for_non_active_accounts(): void
    {
        $menungguUser = User::factory()->warga()->menungguTinjauan()->create();
        $responseMenunggu = $this->actingAs($menungguUser)->get('/warga/pengaduan/buat');
        $responseMenunggu->assertRedirect(route('pendaftaran.status'));
        $responseMenunggu->assertSessionHas('warning');

        $ditolakUser = User::factory()->warga()->ditolak()->create();
        $responseDitolak = $this->actingAs($ditolakUser)->get('/warga/pengaduan/buat');
        $responseDitolak->assertRedirect(route('pendaftaran.status'));

        $aktifUser = User::factory()->warga()->create();
        $responseAktif = $this->actingAs($aktifUser)->get('/warga/pengaduan/buat');
        $responseAktif->assertStatus(200);
    }

    public function test_ditolak_user_can_edit_and_resubmit_registration_and_becomes_active_when_matched(): void
    {
        DataKependudukan::factory()->create([
            'nik' => '3207011205950009',
            'nama_lengkap' => 'Bambang Sudarsono',
            'tanggal_lahir' => '1995-05-12',
            'alamat' => 'Jl. Merpati No. 10',
            'rt' => '001',
            'rw' => '002',
            'dusun' => 'Dusun Krajan',
        ]);

        $user = User::factory()->warga()->ditolak()->create([
            'nik' => '3207011205950009',
            'nama_lengkap' => 'Bambang Salah Ketik',
            'alasan_penolakan' => 'Nama tidak sesuai NIK.',
        ]);

        $response = $this->actingAs($user)->put('/pendaftaran/ajukan-ulang', [
            'nama_lengkap' => 'Bambang Sudarsono',
            'tanggal_lahir' => '1995-05-12',
            'alamat' => 'Jl. Merpati No. 10',
            'rt' => '001',
            'rw' => '002',
            'dusun' => 'Dusun Krajan',
            'telepon' => '081234567890',
        ]);

        $user->refresh();
        $this->assertSame(StatusAkun::Aktif, $user->status_akun);
        $this->assertNull($user->alasan_penolakan);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_ditolak_user_resubmitting_with_still_unmatched_name_becomes_menunggu_tinjauan(): void
    {
        $user = User::factory()->warga()->ditolak()->create([
            'nik' => '3207011205950010',
            'nama_lengkap' => 'Nama Salah',
            'alasan_penolakan' => 'Data tidak sesuai.',
        ]);

        $response = $this->actingAs($user)->put('/pendaftaran/ajukan-ulang', [
            'nama_lengkap' => 'Nama Masih Salah',
            'tanggal_lahir' => '1995-05-12',
            'alamat' => 'Jl. Merpati No. 10',
            'rt' => '001',
            'rw' => '002',
            'dusun' => 'Dusun Krajan',
            'telepon' => '081234567890',
        ]);

        $user->refresh();
        $this->assertSame(StatusAkun::MenungguTinjauan, $user->status_akun);
        $this->assertNull($user->alasan_penolakan);
        $response->assertRedirect(route('pendaftaran.status'));
    }
}
