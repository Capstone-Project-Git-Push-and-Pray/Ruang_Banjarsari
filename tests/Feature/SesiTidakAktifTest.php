<?php

namespace Tests\Feature;

use App\Http\Middleware\BatasiSesiTidakAktif;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SesiTidakAktifTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_keluar_otomatis_setelah_30_menit_tidak_aktif(): void
    {
        $admin = User::factory()->adminDesa()->create();

        $this->actingAs($admin)->get(route('profile.edit'))->assertOk();

        $this->travel(29)->minutes();
        $this->get(route('profile.edit'))->assertOk();

        $this->travel(31)->minutes();
        $this->get(route('profile.edit'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('sesi_berakhir', true);

        $this->assertGuest();
    }

    public function test_kepala_desa_keluar_otomatis_setelah_30_menit_tidak_aktif(): void
    {
        $this->actingAs(User::factory()->kepalaDesa()->create())->get(route('profile.edit'))->assertOk();

        $this->travel(31)->minutes();
        $this->get(route('profile.edit'))->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_warga_tetap_masuk_sampai_120_menit_tidak_aktif(): void
    {
        $this->actingAs(User::factory()->warga()->create())->get(route('profile.edit'))->assertOk();

        $this->travel(90)->minutes();
        $this->get(route('profile.edit'))->assertOk();

        $this->travel(121)->minutes();
        $this->get(route('profile.edit'))->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_warga_yang_memilih_ingat_saya_tidak_dibatasi_waktu_tidak_aktif(): void
    {
        $warga = User::factory()->warga()->create();
        $namaCookie = Auth::guard('web')->getRecallerName();

        $this->actingAs($warga)->get(route('profile.edit'))->assertOk();

        $this->travel(5)->hours();
        $this->withCookie($namaCookie, 'ingat')
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertDontSee('Sesi akan berakhir');

        $this->assertAuthenticatedAs($warga);
    }

    public function test_ingat_saya_hanya_berlaku_untuk_warga_selama_30_hari(): void
    {
        $namaCookie = Auth::guard('web')->getRecallerName();

        $warga = User::factory()->warga()->create(['nik' => '3207010101900011']);
        $responsWarga = $this->post('/login', ['login' => $warga->nik, 'password' => 'password', 'remember' => '1']);
        $cookieWarga = collect($responsWarga->headers->getCookies())->first(fn ($cookie) => $cookie->getName() === $namaCookie);

        $this->assertNotNull($cookieWarga);
        $this->assertEqualsWithDelta(now()->addDays(30)->getTimestamp(), $cookieWarga->getExpiresTime(), 60);

        $this->post('/logout');

        $admin = User::factory()->adminDesa()->create(['email' => 'operator@desa-banjarsari.test']);
        $responsAdmin = $this->post('/login', ['login' => $admin->email, 'password' => 'password', 'remember' => '1']);

        $this->assertAuthenticatedAs($admin);
        $this->assertNull(collect($responsAdmin->headers->getCookies())->first(fn ($cookie) => $cookie->getName() === $namaCookie));
    }

    public function test_tetap_masuk_memperpanjang_sesi(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $this->actingAs($admin)->get(route('profile.edit'))->assertSee('Sesi akan berakhir');

        $this->travel(25)->minutes();
        $this->postJson(route('sesi.perpanjang'))->assertNoContent();
        $this->assertSame(now()->getTimestamp(), session(BatasiSesiTidakAktif::KUNCI_AKTIVITAS));

        $this->travel(25)->minutes();
        $this->get(route('profile.edit'))->assertOk();
    }

    public function test_halaman_kedaluwarsa_419_diarahkan_ke_login_dengan_pesan(): void
    {
        Route::middleware('web')->post('/uji-token-kedaluwarsa', fn () => abort(419));

        $this->post('/uji-token-kedaluwarsa')
            ->assertRedirect(route('login'))
            ->assertSessionHas('sesi_berakhir', true);

        $this->get(route('login'))->assertSee('Sesi Anda telah berakhir');
    }
}
