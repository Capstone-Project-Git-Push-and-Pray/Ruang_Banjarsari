<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/**
 * Halaman Bantuan Reset Akun Warga untuk Admin Desa.
 */
class ResetAkunWargaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->adminDesa()->create();
    }

    public function test_admin_melihat_dan_mencari_akun_warga(): void
    {
        $budi = User::factory()->warga()->create(['nama_lengkap' => 'Budi Santoso']);
        User::factory()->warga()->create(['nama_lengkap' => 'Siti Aminah']);
        User::factory()->kepalaDesa()->create(['nama_lengkap' => 'Kepala Banjarsari']);

        $this->actingAs($this->admin);

        $this->get(route('admin.reset-akun.index'))
            ->assertOk()
            ->assertSee(['BUDI SANTOSO', 'SITI AMINAH'])
            ->assertDontSee('KEPALA BANJARSARI');

        $this->get(route('admin.reset-akun.index', ['q' => $budi->nik]))
            ->assertSee('BUDI SANTOSO')
            ->assertDontSee('SITI AMINAH');
    }

    public function test_admin_mengirim_tautan_reset_ke_email_warga(): void
    {
        Notification::fake();
        $warga = User::factory()->warga()->create();

        $this->actingAs($this->admin)
            ->from(route('admin.reset-akun.index'))
            ->post(route('admin.reset-akun.kirim-tautan', $warga))
            ->assertRedirect(route('admin.reset-akun.index'))
            ->assertSessionHas('success');

        Notification::assertSentTo($warga, ResetPasswordNotification::class);
    }

    public function test_admin_membuat_kata_sandi_sementara_yang_ditampilkan_sekali(): void
    {
        $warga = User::factory()->warga()->create();

        $this->actingAs($this->admin)
            ->from(route('admin.reset-akun.index'))
            ->post(route('admin.reset-akun.kata-sandi-sementara', $warga))
            ->assertRedirect(route('admin.reset-akun.index'));

        $kataSandi = session('kata_sandi_sementara')['kata_sandi'];
        $warga->refresh();

        $this->assertTrue(Hash::check($kataSandi, $warga->password));
        $this->assertFalse(Hash::check('password', $warga->password));
        $this->assertTrue($warga->wajib_ganti_password);

        $this->get(route('admin.reset-akun.index'))->assertSee($kataSandi);
        $this->get(route('admin.reset-akun.index'))->assertDontSee($kataSandi);
    }

    public function test_akun_selain_warga_tidak_dapat_direset_lewat_halaman_ini(): void
    {
        $kepala = User::factory()->kepalaDesa()->create();
        Notification::fake();

        $this->actingAs($this->admin);

        $this->post(route('admin.reset-akun.kata-sandi-sementara', $kepala))->assertNotFound();
        $this->post(route('admin.reset-akun.kirim-tautan', $kepala))->assertNotFound();

        $this->assertTrue(Hash::check('password', $kepala->fresh()->password));
        Notification::assertNothingSent();
    }

    public function test_akun_warga_ditolak_tidak_tampil_dan_tidak_dapat_direset(): void
    {
        Notification::fake();
        User::factory()->warga()->create(['nama_lengkap' => 'Warga Aktif']);
        User::factory()->warga()->menungguTinjauan()->create(['nama_lengkap' => 'Warga Ditinjau']);
        $ditolak = User::factory()->warga()->ditolak()->create(['nama_lengkap' => 'Warga Ditolak']);

        $this->actingAs($this->admin);

        $this->get(route('admin.reset-akun.index'))
            ->assertOk()
            ->assertSee(['WARGA AKTIF', 'WARGA DITINJAU'])
            ->assertDontSee('WARGA DITOLAK');

        $this->post(route('admin.reset-akun.kirim-tautan', $ditolak))->assertNotFound();
        $this->post(route('admin.reset-akun.kata-sandi-sementara', $ditolak))->assertNotFound();

        $this->assertTrue(Hash::check('password', $ditolak->fresh()->password));
        Notification::assertNothingSent();
    }

    public function test_warga_dan_kepala_desa_dilarang_membuka_bantuan_reset_akun(): void
    {
        $warga = User::factory()->warga()->create();

        foreach ([User::factory()->warga()->create(), User::factory()->kepalaDesa()->create()] as $pengguna) {
            $this->actingAs($pengguna);

            $this->get(route('admin.reset-akun.index'))->assertForbidden();
            $this->post(route('admin.reset-akun.kata-sandi-sementara', $warga))->assertForbidden();
        }

        $this->assertFalse($warga->fresh()->wajib_ganti_password);
    }

    public function test_warga_dengan_kata_sandi_sementara_wajib_menggantinya_sebelum_memakai_fitur(): void
    {
        $warga = User::factory()->warga()->create(['wajib_ganti_password' => true]);

        $this->actingAs($warga);

        $this->get(route('warga.dashboard'))->assertRedirect(route('profile.edit'));
        $this->get(route('profile.edit'))->assertOk()->assertSee('Ganti kata sandi sementara Anda');

        $this->from(route('profile.edit'))->put(route('password.update'), [
            'current_password' => 'password',
            'password' => 'kata-sandi-baru-123',
            'password_confirmation' => 'kata-sandi-baru-123',
        ])->assertSessionHasNoErrors();

        $this->assertFalse($warga->fresh()->wajib_ganti_password);
        $this->get(route('warga.dashboard'))->assertOk();
    }

    public function test_reset_lewat_tautan_email_menghapus_kewajiban_ganti_kata_sandi(): void
    {
        $warga = User::factory()->warga()->create(['wajib_ganti_password' => true]);
        $token = Password::createToken($warga);

        $this->post(route('password.store'), [
            'token' => $token,
            'email' => $warga->email,
            'password' => 'kata-sandi-baru-123',
            'password_confirmation' => 'kata-sandi-baru-123',
        ])->assertRedirect(route('login'));

        $this->assertFalse($warga->fresh()->wajib_ganti_password);
    }
}
