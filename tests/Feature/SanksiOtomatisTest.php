<?php

namespace Tests\Feature;

use App\Enums\AlasanModerasi;
use App\Enums\FiturSanksi;
use App\Models\Komentar;
use App\Models\Pengaduan;
use App\Models\Sanksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * PBI #46, #47, #48: sanksi bertingkat otomatis dan pembatasan fitur warga.
 */
class SanksiOtomatisTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $warga;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->adminDesa()->create();
        $this->warga = User::factory()->warga()->create();
    }

    private function hapusAspirasiWarga(string $alasan = 'spam'): void
    {
        $aspirasi = Pengaduan::factory()->aspirasi()->create(['user_id' => $this->warga->id]);

        $this->actingAs($this->admin)
            ->delete(route('admin.moderasi.aspirasi.hapus', $aspirasi), ['alasan' => $alasan])
            ->assertSessionHas('success');
    }

    private function hapusKomentarWarga(string $alasan = 'kata_kasar'): void
    {
        $komentar = Komentar::factory()->create([
            'pengaduan_id' => Pengaduan::factory()->aspirasi(),
            'user_id' => $this->warga->id,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.moderasi.komentar.hapus', $komentar), ['alasan' => $alasan])
            ->assertSessionHas('success');
    }

    /**
     * @return list<array{int|null, bool}>
     */
    private function ringkasSanksi(FiturSanksi $fitur): array
    {
        return $this->warga->sanksi()->where('fitur', $fitur)->orderBy('tingkat')->get()
            ->map(fn (Sanksi $sanksi) => [
                $sanksi->permanen ? null : (int) round($sanksi->mulai_pada->diffInDays($sanksi->berakhir_pada)),
                $sanksi->isBerlaku(),
            ])->all();
    }

    public function test_sanksi_aspirasi_bertingkat_dari_peringatan_hingga_permanen(): void
    {
        foreach (range(1, 4) as $pelanggaran) {
            $this->hapusAspirasiWarga();
        }

        $this->assertSame([[0, false], [7, true], [30, true], [null, true]], $this->ringkasSanksi(FiturSanksi::Aspirasi));
    }

    public function test_sanksi_komentar_publik_selalu_sementara(): void
    {
        foreach (range(1, 4) as $pelanggaran) {
            $this->hapusKomentarWarga();
        }

        $this->assertSame([[3, true], [7, true], [30, true], [30, true]], $this->ringkasSanksi(FiturSanksi::KomentarPublik));
    }

    public function test_pesan_admin_menyebut_sanksi_yang_diterapkan(): void
    {
        $komentar = Komentar::factory()->create(['pengaduan_id' => Pengaduan::factory()->aspirasi(), 'user_id' => $this->warga->id]);

        $this->actingAs($this->admin)
            ->delete(route('admin.moderasi.komentar.hapus', $komentar), ['alasan' => 'kata_kasar'])
            ->assertSessionHas('success', fn (string $pesan) => str_contains($pesan, 'Sanksi otomatis tingkat 1 untuk fitur Komentar Publik'));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function alasanBukanPelanggaran(): array
    {
        return [
            'duplikat' => [AlasanModerasi::Duplikat->value],
            'tidak relevan' => [AlasanModerasi::TidakRelevan->value],
        ];
    }

    #[DataProvider('alasanBukanPelanggaran')]
    public function test_penghapusan_tanpa_pelanggaran_tidak_memicu_sanksi(string $alasan): void
    {
        $this->hapusAspirasiWarga($alasan);

        $this->assertDatabaseEmpty('sanksi');
    }

    public function test_pelanggaran_lama_dan_sanksi_dibatalkan_tidak_dihitung(): void
    {
        Sanksi::factory()->create([
            'user_id' => $this->warga->id,
            'mulai_pada' => now()->subDays(Sanksi::MASA_HITUNG_HARI + 1),
            'berakhir_pada' => now()->subDays(Sanksi::MASA_HITUNG_HARI - 6),
        ]);
        Sanksi::factory()->dibatalkan()->create(['user_id' => $this->warga->id]);

        $this->hapusAspirasiWarga();

        $this->assertSame(1, $this->warga->sanksi()->latest('id')->first()->tingkat);
    }

    public function test_sanksi_diakhiri_admin_tetap_dihitung(): void
    {
        Sanksi::factory()->create([
            'user_id' => $this->warga->id,
            'tingkat' => 2,
            'dicabut_pada' => now(),
            'jenis_pencabutan' => Sanksi::PENCABUTAN_DIAKHIRI,
        ]);

        $this->hapusAspirasiWarga();

        $this->assertSame(2, $this->warga->sanksi()->latest('id')->first()->tingkat);
    }

    public function test_warga_dengan_sanksi_aspirasi_tidak_dapat_mengajukan_aspirasi_tetapi_boleh_pengaduan(): void
    {
        Sanksi::factory()->create(['user_id' => $this->warga->id]);
        $data = [
            'kategori_id' => Pengaduan::factory()->create()->kategori_id,
            'judul' => 'Judul laporan baru',
            'deskripsi' => 'Deskripsi laporan baru yang cukup panjang.',
        ];

        $this->actingAs($this->warga)
            ->post(route('warga.pengaduan.store'), [...$data, 'jenis' => 'aspirasi'])
            ->assertSessionHasErrors(['jenis' => 'Fitur aspirasi Anda sedang dibatasi. '.$this->warga->sanksiBerlaku(FiturSanksi::Aspirasi)->keteranganMasaBerlaku().'. Pengaduan tetap dapat diajukan.']);

        $this->post(route('warga.pengaduan.store'), [...$data, 'jenis' => 'pengaduan'])->assertSessionHasNoErrors();

        $this->assertSame(['pengaduan'], Pengaduan::where('user_id', $this->warga->id)->pluck('jenis')->map->value->all());
    }

    public function test_sanksi_aspirasi_yang_berakhir_tidak_membatasi(): void
    {
        Sanksi::factory()->berakhir()->create(['user_id' => $this->warga->id]);

        $this->actingAs($this->warga)->post(route('warga.pengaduan.store'), [
            'jenis' => 'aspirasi',
            'kategori_id' => Pengaduan::factory()->create()->kategori_id,
            'judul' => 'Aspirasi setelah sanksi',
            'deskripsi' => 'Deskripsi aspirasi yang cukup panjang.',
        ])->assertSessionHasNoErrors();
    }

    public function test_warga_dengan_sanksi_komentar_hanya_boleh_berkomentar_pada_pengaduannya(): void
    {
        Sanksi::factory()->komentarPublik()->create(['user_id' => $this->warga->id]);
        $aspirasi = Pengaduan::factory()->aspirasi()->create();
        $pengaduanSendiri = Pengaduan::factory()->create(['user_id' => $this->warga->id]);

        $this->actingAs($this->warga);

        $this->post(route('komentar.store', $aspirasi), ['isi' => 'Komentar publik'])->assertForbidden();
        $this->get(route('warga.aspirasi.show', $aspirasi))
            ->assertSee('Fitur Komentar Publik Anda sedang dibatasi')
            ->assertDontSee('Kirim Komentar');
        $this->post(route('komentar.store', $pengaduanSendiri), ['isi' => 'Komentar pada pengaduan'])
            ->assertSessionHas('status', 'komentar-berhasil-ditambahkan');

        $this->assertSame(['Komentar pada pengaduan'], Komentar::pluck('isi')->all());
    }

    public function test_warga_melihat_fitur_alasan_dan_masa_berakhir_sanksi(): void
    {
        $this->hapusKomentarWarga();
        $sanksi = $this->warga->sanksi()->first();

        $this->actingAs($this->warga);

        $this->get(route('warga.dashboard'))->assertSee(['Fitur Komentar Publik Anda sedang dibatasi', 'Lihat alasan']);
        $this->get(route('warga.sanksi.index'))->assertOk()->assertSee([
            'Fitur Komentar Publik',
            FiturSanksi::KomentarPublik->pembatasan(),
            AlasanModerasi::KataKasar->label(),
            $sanksi->keteranganMasaBerlaku(),
        ]);
    }

    public function test_peringatan_ditampilkan_di_dashboard_tanpa_membatasi(): void
    {
        $this->hapusAspirasiWarga();

        $this->actingAs($this->warga)->get(route('warga.dashboard'))
            ->assertSee('Konten Anda pernah dihapus Admin Desa dalam 30 hari terakhir')
            ->assertDontSee('sedang dibatasi');
    }

    public function test_warga_tanpa_sanksi_melihat_tidak_ada_pembatasan(): void
    {
        $this->actingAs($this->warga)->get(route('warga.sanksi.index'))->assertSee('Tidak ada pembatasan.');
    }
}
