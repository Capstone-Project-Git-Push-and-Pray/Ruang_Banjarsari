<?php

namespace Tests\Feature\Admin;

use App\Enums\AksiModerasi;
use App\Enums\AlasanModerasi;
use App\Enums\JenisKonten;
use App\Models\Komentar;
use App\Models\Pengaduan;
use App\Models\RiwayatModerasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * PBI #44, #45: sembunyikan, tampilkan kembali, dan hapus konten dengan alasan.
 */
class ModerasiKontenTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $warga;

    private Pengaduan $aspirasi;

    private Komentar $komentar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->adminDesa()->create();
        $this->warga = User::factory()->warga()->create();
        $this->aspirasi = Pengaduan::factory()->aspirasi()->create(['judul' => 'Aspirasi Yang Dimoderasi']);
        $this->komentar = Komentar::factory()->create([
            'pengaduan_id' => $this->aspirasi->id,
            'isi' => 'Komentar yang dimoderasi',
        ]);
    }

    public function test_aspirasi_dapat_disembunyikan_lalu_ditampilkan_kembali(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('admin.moderasi.aspirasi.sembunyikan', $this->aspirasi))
            ->assertSessionHas('success');

        $this->assertSame($this->admin->id, $this->aspirasi->fresh()->disembunyikan_oleh);
        $this->actingAs($this->warga)->get(route('warga.aspirasi.show', $this->aspirasi))->assertNotFound();

        $this->actingAs($this->admin)
            ->patch(route('admin.moderasi.aspirasi.tampilkan', $this->aspirasi))
            ->assertSessionHas('success');

        $this->actingAs($this->warga)->get(route('warga.aspirasi.show', $this->aspirasi))->assertOk();
        $this->assertSame(
            [AksiModerasi::Sembunyikan, AksiModerasi::Tampilkan],
            RiwayatModerasi::orderBy('id')->get()->pluck('aksi')->all()
        );
    }

    public function test_komentar_tersembunyi_hanya_terlihat_oleh_penulis_dan_pihak_desa(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('admin.moderasi.komentar.sembunyikan', $this->komentar))
            ->assertSessionHas('success');

        $halamanPublik = route('warga.aspirasi.show', $this->aspirasi);

        $this->actingAs($this->warga)->get($halamanPublik)->assertDontSee('Komentar yang dimoderasi');
        $this->actingAs($this->aspirasi->user)
            ->get(route('warga.pengaduan.show', $this->aspirasi))->assertDontSee('Komentar yang dimoderasi');
        $this->actingAs($this->warga)->get(route('warga.aspirasi.index'))->assertSee('Komentar (0)');
        $this->actingAs($this->komentar->user)->get($halamanPublik)
            ->assertSee(['Komentar yang dimoderasi', 'Disembunyikan dari publik']);
        $this->actingAs($this->admin)
            ->get(route('admin.aspirasi.show', $this->aspirasi))->assertSee('Komentar yang dimoderasi');
        $this->actingAs(User::factory()->kepalaDesa()->create())
            ->get(route('kepala.pengaduan.show', $this->aspirasi))->assertSee('Komentar yang dimoderasi');
    }

    public function test_komentar_dapat_ditampilkan_kembali(): void
    {
        $komentar = Komentar::factory()->disembunyikan()->create([
            'pengaduan_id' => $this->aspirasi->id,
            'isi' => 'Komentar yang dipulihkan',
        ]);

        $this->actingAs($this->admin)
            ->patch(route('admin.moderasi.komentar.tampilkan', $komentar))
            ->assertSessionHas('success');

        $this->actingAs($this->warga)
            ->get(route('warga.aspirasi.show', $this->aspirasi))->assertSee('Komentar yang dipulihkan');
    }

    public function test_menyembunyikan_konten_yang_sudah_tersembunyi_tidak_dicatat_dua_kali(): void
    {
        $this->actingAs($this->admin);

        $this->patch(route('admin.moderasi.aspirasi.sembunyikan', $this->aspirasi));
        $this->patch(route('admin.moderasi.aspirasi.sembunyikan', $this->aspirasi));

        $this->assertDatabaseCount('riwayat_moderasi', 1);
    }

    public function test_penulis_dilarang_mengubah_atau_menghapus_komentar_tersembunyi(): void
    {
        $komentar = Komentar::factory()->disembunyikan()->create(['pengaduan_id' => $this->aspirasi->id]);

        $this->actingAs($komentar->user);

        $this->put(route('komentar.update', $komentar), ['isi' => 'Bukti diubah'])->assertForbidden();
        $this->delete(route('komentar.destroy', $komentar))->assertForbidden();
        $this->assertModelExists($komentar);
    }

    public function test_aspirasi_dihapus_dengan_alasan_yang_tercatat(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('admin.moderasi.aspirasi.hapus', $this->aspirasi), [
                'alasan' => AlasanModerasi::InformasiPalsu->value,
                'catatan_moderasi' => 'Sudah dicek ke lapangan.',
            ])
            ->assertRedirect(route('admin.aspirasi.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted($this->aspirasi);
        $this->assertDatabaseHas('riwayat_moderasi', [
            'jenis_konten' => JenisKonten::Aspirasi->value,
            'pengaduan_id' => $this->aspirasi->id,
            'pemilik_id' => $this->aspirasi->user_id,
            'aksi' => AksiModerasi::Hapus->value,
            'alasan' => AlasanModerasi::InformasiPalsu->value,
            'catatan' => 'Sudah dicek ke lapangan.',
            'isi_konten' => $this->aspirasi->judul."\n\n".$this->aspirasi->deskripsi,
            'dilakukan_oleh' => $this->admin->id,
        ]);

        $this->get(route('admin.aspirasi.show', $this->aspirasi))->assertNotFound();
        $this->get(route('admin.moderasi.index'))
            ->assertOk()
            ->assertSee(['Aspirasi Yang Dimoderasi', AlasanModerasi::InformasiPalsu->label(), 'Sudah dicek ke lapangan.']);
        $this->actingAs($this->warga)->get(route('warga.aspirasi.index'))->assertDontSee('Aspirasi Yang Dimoderasi');
    }

    public function test_komentar_dihapus_dengan_alasan_yang_tercatat(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('admin.moderasi.komentar.hapus', $this->komentar), ['alasan' => AlasanModerasi::KataKasar->value])
            ->assertSessionHas('success');

        $this->assertSoftDeleted($this->komentar);
        $this->assertDatabaseHas('riwayat_moderasi', [
            'jenis_konten' => JenisKonten::Komentar->value,
            'pengaduan_id' => $this->aspirasi->id,
            'komentar_id' => $this->komentar->id,
            'pemilik_id' => $this->komentar->user_id,
            'alasan' => AlasanModerasi::KataKasar->value,
            'isi_konten' => 'Komentar yang dimoderasi',
        ]);
        $this->actingAs($this->warga)
            ->get(route('warga.aspirasi.show', $this->aspirasi))->assertDontSee('Komentar yang dimoderasi');
    }

    /**
     * @return array<string, array{array<string, string>, array<string, string>}>
     */
    public static function alasanTidakSah(): array
    {
        return [
            'tanpa alasan' => [[], ['alasan' => 'Alasan penghapusan wajib dipilih.']],
            'alasan di luar daftar' => [['alasan' => 'iseng'], ['alasan' => 'Alasan penghapusan yang dipilih tidak valid.']],
            'alasan lainnya tanpa catatan' => [
                ['alasan' => 'lainnya'],
                ['catatan_moderasi' => 'Catatan wajib diisi jika memilih alasan Lainnya.'],
            ],
        ];
    }

    /**
     * @param  array<string, string>  $input
     * @param  array<string, string>  $kesalahan
     */
    #[DataProvider('alasanTidakSah')]
    public function test_konten_tidak_dihapus_jika_alasan_tidak_sah(array $input, array $kesalahan): void
    {
        $this->actingAs($this->admin)
            ->delete(route('admin.moderasi.aspirasi.hapus', $this->aspirasi), $input)
            ->assertSessionHasErrors($kesalahan);

        $this->assertNotSoftDeleted($this->aspirasi);
        $this->assertDatabaseEmpty('riwayat_moderasi');
    }

    public function test_admin_tidak_dapat_menghapus_komentar_aspirasi_tanpa_alasan(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('komentar.destroy', $this->komentar))
            ->assertForbidden();

        $this->assertNotSoftDeleted($this->komentar);
    }

    public function test_pengaduan_privat_dan_komentar_aparatur_tidak_dapat_dimoderasi(): void
    {
        $pengaduan = Pengaduan::factory()->create();
        $komentarPengaduan = Komentar::factory()->create(['pengaduan_id' => $pengaduan->id]);
        $komentarKepala = Komentar::factory()->create([
            'pengaduan_id' => $this->aspirasi->id,
            'user_id' => User::factory()->kepalaDesa(),
        ]);

        $this->actingAs($this->admin);

        $this->patch(route('admin.moderasi.aspirasi.sembunyikan', $pengaduan))->assertForbidden();
        $this->delete(route('admin.moderasi.aspirasi.hapus', $pengaduan), ['alasan' => 'spam'])->assertForbidden();
        $this->patch(route('admin.moderasi.komentar.sembunyikan', $komentarPengaduan))->assertForbidden();
        $this->delete(route('admin.moderasi.komentar.hapus', $komentarKepala), ['alasan' => 'spam'])->assertForbidden();
        $this->assertDatabaseEmpty('riwayat_moderasi');
    }

    public function test_warga_dan_kepala_desa_dilarang_memoderasi(): void
    {
        foreach ([$this->warga, User::factory()->kepalaDesa()->create()] as $pengguna) {
            $this->actingAs($pengguna);

            $this->get(route('admin.moderasi.index'))->assertForbidden();
            $this->patch(route('admin.moderasi.aspirasi.sembunyikan', $this->aspirasi))->assertForbidden();
            $this->delete(route('admin.moderasi.komentar.hapus', $this->komentar), ['alasan' => 'spam'])->assertForbidden();
        }

        $this->assertDatabaseEmpty('riwayat_moderasi');
    }

    public function test_admin_dapat_memfilter_aspirasi_yang_disembunyikan(): void
    {
        Pengaduan::factory()->aspirasi()->disembunyikan()->create(['judul' => 'Aspirasi Tersembunyi']);

        $this->actingAs($this->admin);

        $this->get(route('admin.aspirasi.index', ['visibilitas' => 'disembunyikan']))
            ->assertSee('Aspirasi Tersembunyi')->assertDontSee('Aspirasi Yang Dimoderasi');
        $this->get(route('admin.aspirasi.index', ['visibilitas' => 'tampil']))
            ->assertSee('Aspirasi Yang Dimoderasi')->assertDontSee('Aspirasi Tersembunyi');
    }

    public function test_kategori_milik_aspirasi_terhapus_tidak_dapat_dihapus(): void
    {
        $kategori = $this->aspirasi->kategori;
        $this->aspirasi->delete();

        $this->actingAs($this->admin)
            ->delete(route('admin.kategori.destroy', $kategori))
            ->assertSessionHas('error');

        $this->assertModelExists($kategori);
        $this->assertSoftDeleted($this->aspirasi);
    }
}
