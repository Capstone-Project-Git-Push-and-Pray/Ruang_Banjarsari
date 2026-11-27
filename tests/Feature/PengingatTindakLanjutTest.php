<?php

namespace Tests\Feature;

use App\Enums\StatusLaporan;
use App\Models\Pengaduan;
use App\Models\PengingatTindakLanjut;
use App\Models\User;
use App\Notifications\PengingatTindakLanjutNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Js;
use Tests\TestCase;

/**
 * PBI #50 (kirim pengingat) dan PBI #51 (batas pengingat per 24 jam) dari sisi Kepala Desa.
 */
class PengingatTindakLanjutTest extends TestCase
{
    use RefreshDatabase;

    private User $kepala;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kepala = User::factory()->kepalaDesa()->create(['nama_lengkap' => 'Pak Kades']);
    }

    public function test_kepala_desa_mengirim_pengingat_dengan_catatan_ke_semua_admin_desa(): void
    {
        Notification::fake();
        $adminPertama = User::factory()->adminDesa()->create();
        $adminKedua = User::factory()->adminDesa()->create();
        $laporan = $this->laporanTertunda();

        $this->actingAs($this->kepala)
            ->post(route('kepala.pengaduan.pengingat', $laporan), ['catatan' => 'Mohon segera ditinjau.'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('pengingat_tindak_lanjut', [
            'pengaduan_id' => $laporan->id,
            'dikirim_oleh' => $this->kepala->id,
            'catatan' => 'Mohon segera ditinjau.',
            'selesai_pada' => null,
        ]);
        Notification::assertSentTo([$adminPertama, $adminKedua], PengingatTindakLanjutNotification::class);
        Notification::assertNotSentTo($this->kepala, PengingatTindakLanjutNotification::class);
        $this->assertSame(StatusLaporan::Diajukan, $laporan->fresh()->status);
    }

    public function test_pengingat_ditolak_untuk_laporan_yang_tidak_perlu_tindak_lanjut(): void
    {
        $baruMasuk = Pengaduan::factory()->create(['status' => StatusLaporan::Diajukan, 'created_at' => now()->subDay()]);
        $sudahDitinjau = Pengaduan::factory()->create(['status' => StatusLaporan::Ditinjau, 'created_at' => now()->subDays(10)]);

        foreach ([$baruMasuk, $sudahDitinjau] as $laporan) {
            $this->actingAs($this->kepala)
                ->post(route('kepala.pengaduan.pengingat', $laporan))
                ->assertSessionHasErrors('pengingat');
        }

        $this->assertDatabaseCount('pengingat_tindak_lanjut', 0);
    }

    public function test_catatan_pengingat_maksimal_255_karakter(): void
    {
        $this->actingAs($this->kepala)
            ->post(route('kepala.pengaduan.pengingat', $this->laporanTertunda()), ['catatan' => str_repeat('a', 256)])
            ->assertSessionHasErrors('catatan');

        $this->assertDatabaseCount('pengingat_tindak_lanjut', 0);
    }

    public function test_hanya_kepala_desa_yang_dapat_mengirim_pengingat_dan_status_tetap_tidak_bisa_diubah_kepala(): void
    {
        $laporan = $this->laporanTertunda();

        $this->actingAs(User::factory()->adminDesa()->create())
            ->post(route('kepala.pengaduan.pengingat', $laporan))
            ->assertForbidden();

        $this->actingAs($this->kepala)
            ->patch(route('admin.pengaduan.status.update', $laporan), ['status' => StatusLaporan::Ditinjau->value])
            ->assertForbidden();

        $this->assertSame(StatusLaporan::Diajukan, $laporan->fresh()->status);
    }

    public function test_pengingat_hanya_bisa_dikirim_sekali_dalam_24_jam_dan_dicek_di_server(): void
    {
        $laporan = $this->laporanTertunda();
        $pengingat = PengingatTindakLanjut::factory()->create([
            'pengaduan_id' => $laporan->id,
            'dikirim_oleh' => $this->kepala->id,
            'created_at' => now()->subHours(23),
        ]);

        $this->actingAs($this->kepala)
            ->post(route('kepala.pengaduan.pengingat', $laporan))
            ->assertSessionHasErrors('pengingat');
        $this->assertDatabaseCount('pengingat_tindak_lanjut', 1);

        $pengingat->forceFill(['created_at' => now()->subHours(25)])->save();

        $this->actingAs($this->kepala)
            ->post(route('kepala.pengaduan.pengingat', $laporan))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseCount('pengingat_tindak_lanjut', 2);
    }

    public function test_halaman_monitoring_menampilkan_tombol_sesuai_keadaan_pengingat(): void
    {
        $belumDiingatkan = $this->laporanTertunda(['judul' => 'Belum pernah diingatkan']);
        $baruDiingatkan = $this->laporanTertunda(['judul' => 'Baru saja diingatkan']);
        PengingatTindakLanjut::factory()->create([
            'pengaduan_id' => $baruDiingatkan->id,
            'created_at' => now()->subHours(2),
        ]);
        Pengaduan::factory()->create(['judul' => 'Laporan baru kemarin', 'status' => StatusLaporan::Diajukan, 'created_at' => now()->subDay()]);

        $respons = $this->actingAs($this->kepala)->get(route('kepala.pengaduan.index'));

        $respons->assertOk()
            ->assertSee(Js::from(route('kepala.pengaduan.pengingat', $belumDiingatkan))->toHtml(), false)
            ->assertDontSee(Js::from(route('kepala.pengaduan.pengingat', $baruDiingatkan))->toHtml(), false)
            ->assertSee('Sudah diingatkan &middot; 2 jam lalu', false);
        $this->assertSame(1, substr_count($respons->getContent(), '>Ingatkan Admin<') + substr_count(preg_replace('/\s+/', ' ', $respons->getContent()), '</svg> Ingatkan Admin </button>'));
    }

    /**
     * @param  array<string, mixed>  $atribut
     */
    private function laporanTertunda(array $atribut = []): Pengaduan
    {
        return Pengaduan::factory()->create($atribut + [
            'status' => StatusLaporan::Diajukan,
            'created_at' => now()->subDays(4),
        ]);
    }
}
