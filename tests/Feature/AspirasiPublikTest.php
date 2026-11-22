<?php

namespace Tests\Feature;

use App\Enums\StatusLaporan;
use App\Models\Dukungan;
use App\Models\Kategori;
use App\Models\Komentar;
use App\Models\Pengaduan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PBI #38, #39, #40, #42: visibilitas, daftar, detail, dan komentar aspirasi publik.
 */
class AspirasiPublikTest extends TestCase
{
    use RefreshDatabase;

    private User $pengusul;

    private User $wargaLain;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pengusul = User::factory()->warga()->create([
            'nama_lengkap' => 'Budi Santoso Wijaya',
            'nik' => '9999000000000001',
            'telepon' => '081200001111',
            'alamat' => 'Jalan Rahasia Nomor 7',
        ]);
        $this->wargaLain = User::factory()->warga()->create(['nama_lengkap' => 'Siti Aminah']);
    }

    private function buatAspirasi(array $atribut = []): Pengaduan
    {
        return Pengaduan::factory()->aspirasi()->create(['user_id' => $this->pengusul->id, ...$atribut]);
    }

    public function test_daftar_hanya_menampilkan_aspirasi_yang_tidak_disembunyikan(): void
    {
        $this->buatAspirasi(['judul' => 'Usulan Taman Baca']);
        Pengaduan::factory()->create(['user_id' => $this->pengusul->id, 'judul' => 'Pengaduan Rahasia Tetangga']);
        Pengaduan::factory()->aspirasi()->disembunyikan()->create(['judul' => 'Aspirasi Meragukan']);

        $this->actingAs($this->wargaLain)->get(route('warga.aspirasi.index'))
            ->assertOk()
            ->assertSee('Usulan Taman Baca')
            ->assertDontSee('Pengaduan Rahasia Tetangga')
            ->assertDontSee('Aspirasi Meragukan');
    }

    public function test_daftar_dapat_difilter_dan_dicari(): void
    {
        $kategori = Kategori::factory()->create();
        $this->buatAspirasi(['judul' => 'Cocok Semua Filter', 'kategori_id' => $kategori->id, 'status' => StatusLaporan::Ditampung]);
        $this->buatAspirasi(['judul' => 'Cocok Beda Kategori', 'status' => StatusLaporan::Ditampung]);
        $this->buatAspirasi(['judul' => 'Cocok Beda Status', 'kategori_id' => $kategori->id]);
        $this->buatAspirasi(['judul' => 'Lain Kata Kunci', 'deskripsi' => 'x', 'lokasi' => null, 'kategori_id' => $kategori->id, 'status' => StatusLaporan::Ditampung]);

        $this->actingAs($this->wargaLain)
            ->get(route('warga.aspirasi.index', ['q' => 'Cocok', 'kategori_id' => $kategori->id, 'status' => 'ditampung']))
            ->assertSee('Cocok Semua Filter')
            ->assertDontSee('Cocok Beda Kategori')
            ->assertDontSee('Cocok Beda Status')
            ->assertDontSee('Lain Kata Kunci');
    }

    public function test_pencarian_publik_tidak_mengungkap_aspirasi_dari_nik_pengusul(): void
    {
        $this->buatAspirasi(['judul' => 'Usulan Pos Ronda']);

        $this->actingAs($this->wargaLain)
            ->get(route('warga.aspirasi.index', ['q' => $this->pengusul->nik]))
            ->assertDontSee('Usulan Pos Ronda');
    }

    public function test_daftar_dapat_diurutkan_berdasarkan_dukungan_terbanyak(): void
    {
        $populer = $this->buatAspirasi(['judul' => 'Aspirasi Populer', 'created_at' => now()->subDay()]);
        $this->buatAspirasi(['judul' => 'Aspirasi Baru']);
        Dukungan::factory()->create(['pengaduan_id' => $populer->id]);

        $this->actingAs($this->wargaLain);

        $this->get(route('warga.aspirasi.index'))->assertSeeInOrder(['Aspirasi Baru', 'Aspirasi Populer']);
        $this->get(route('warga.aspirasi.index', ['urut' => 'dukungan']))->assertSeeInOrder(['Aspirasi Populer', 'Aspirasi Baru']);
    }

    public function test_halaman_aspirasi_hanya_untuk_warga_aktif(): void
    {
        $this->get(route('warga.aspirasi.index'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->adminDesa()->create())
            ->get(route('warga.aspirasi.index'))->assertForbidden();

        $this->actingAs(User::factory()->warga()->menungguTinjauan()->create())
            ->get(route('warga.aspirasi.index'))->assertRedirect(route('pendaftaran.status'));
    }

    public function test_detail_menampilkan_isi_lengkap_tanpa_data_pribadi_pengusul(): void
    {
        $aspirasi = $this->buatAspirasi(['judul' => 'Usulan Wifi Desa', 'deskripsi' => 'Isi lengkap usulan wifi', 'lokasi' => 'Balai Dusun']);

        $this->actingAs($this->wargaLain)->get(route('warga.aspirasi.show', $aspirasi))
            ->assertOk()
            ->assertSee(['Usulan Wifi Desa', 'Isi lengkap usulan wifi', 'Balai Dusun', 'Budi S. W.'])
            ->assertDontSee($this->pengusul->nama_lengkap)
            ->assertDontSee($this->pengusul->nik)
            ->assertDontSee($this->pengusul->telepon)
            ->assertDontSee($this->pengusul->alamat);
    }

    public function test_detail_publik_mengembalikan_404_untuk_pengaduan_dan_aspirasi_tersembunyi(): void
    {
        $pengaduan = Pengaduan::factory()->create();
        $tersembunyi = Pengaduan::factory()->aspirasi()->disembunyikan()->create();

        $this->actingAs($this->wargaLain);

        $this->get(route('warga.aspirasi.show', $pengaduan))->assertNotFound();
        $this->get(route('warga.aspirasi.show', $tersembunyi))->assertNotFound();
    }

    public function test_warga_lain_tetap_dilarang_membuka_halaman_privat_aspirasi_dan_pengaduan(): void
    {
        $aspirasi = $this->buatAspirasi();
        $pengaduan = Pengaduan::factory()->create(['user_id' => $this->pengusul->id]);

        $this->actingAs($this->wargaLain);

        $this->get(route('warga.pengaduan.show', $aspirasi))->assertForbidden();
        $this->get(route('warga.pengaduan.show', $pengaduan))->assertForbidden();
        $this->get(route('pengaduan.show', $pengaduan))->assertForbidden();
        $this->get(route('pengaduan.show', $aspirasi))->assertRedirect(route('warga.aspirasi.show', $aspirasi));
    }

    public function test_pihak_desa_tetap_dapat_melihat_pengaduan_privat(): void
    {
        $pengaduan = Pengaduan::factory()->create(['judul' => 'Pengaduan Privat Warga']);

        $this->actingAs(User::factory()->adminDesa()->create())
            ->get(route('admin.pengaduan.show', $pengaduan))->assertOk()->assertSee('Pengaduan Privat Warga');
        $this->actingAs(User::factory()->kepalaDesa()->create())
            ->get(route('kepala.pengaduan.show', $pengaduan))->assertOk()->assertSee('Pengaduan Privat Warga');
    }

    public function test_pemilik_melihat_keterangan_visibilitas_laporannya(): void
    {
        $this->actingAs($this->pengusul);

        $this->get(route('warga.pengaduan.show', $this->buatAspirasi()))->assertSee('Lihat halaman publik');
        $this->get(route('warga.pengaduan.show', Pengaduan::factory()->create(['user_id' => $this->pengusul->id])))
            ->assertSee('Pengaduan ini hanya dapat dilihat oleh Anda dan pihak desa.');
        $this->get(route('warga.pengaduan.show', Pengaduan::factory()->aspirasi()->disembunyikan()->create(['user_id' => $this->pengusul->id])))
            ->assertOk()->assertSee('Aspirasi Sedang Disembunyikan');
    }

    public function test_warga_aktif_dapat_berkomentar_pada_aspirasi_warga_lain(): void
    {
        $aspirasi = $this->buatAspirasi();

        $this->actingAs($this->wargaLain)
            ->post(route('komentar.store', $aspirasi), ['isi' => 'Saya setuju dengan usulan ini'])
            ->assertSessionHas('status', 'komentar-berhasil-ditambahkan');

        $this->assertDatabaseHas('komentar', [
            'pengaduan_id' => $aspirasi->id,
            'user_id' => $this->wargaLain->id,
            'isi' => 'Saya setuju dengan usulan ini',
        ]);

        // Warga ketiga membaca komentar dengan nama publik, bukan nama lengkap
        $this->actingAs(User::factory()->warga()->create())
            ->get(route('warga.aspirasi.show', $aspirasi))
            ->assertSee(['Saya setuju dengan usulan ini', 'Siti A.'])
            ->assertDontSee($this->wargaLain->nama_lengkap);
    }

    public function test_warga_belum_aktif_dilarang_berkomentar_pada_aspirasi(): void
    {
        $this->actingAs(User::factory()->warga()->menungguTinjauan()->create())
            ->post(route('komentar.store', $this->buatAspirasi()), ['isi' => 'Komentar terlarang'])
            ->assertForbidden();

        $this->assertDatabaseEmpty('komentar');
    }

    public function test_diskusi_warga_terkunci_selama_aspirasi_disembunyikan(): void
    {
        $aspirasi = Pengaduan::factory()->aspirasi()->disembunyikan()->create(['user_id' => $this->pengusul->id]);

        foreach ([$this->wargaLain, $this->pengusul] as $warga) {
            $this->actingAs($warga)
                ->post(route('komentar.store', $aspirasi), ['isi' => 'Komentar terlarang'])
                ->assertForbidden();
        }

        $this->assertDatabaseEmpty('komentar');
    }

    public function test_isi_komentar_dengan_karakter_khusus_tidak_merusak_atribut_alpine(): void
    {
        $aspirasi = $this->buatAspirasi();
        Komentar::factory()->create([
            'pengaduan_id' => $aspirasi->id,
            'isi' => "baris satu\nbaris dua '\"><script>alert(1)</script>",
        ]);

        $this->actingAs($this->wargaLain)->get(route('warga.aspirasi.show', $aspirasi))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('baris satu\nbaris dua', false);
    }
}
