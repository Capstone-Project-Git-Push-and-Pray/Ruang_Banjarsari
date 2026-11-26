<?php

namespace Tests\Feature\Kepala;

use App\Enums\StatusLaporan;
use App\Models\Kategori;
use App\Models\Pengaduan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitoringLaporanTest extends TestCase
{
    use RefreshDatabase;

    public function test_tab_jenis_menyaring_laporan_dan_menampilkan_jumlah_per_jenis(): void
    {
        $kepala = User::factory()->kepalaDesa()->create();
        Pengaduan::factory()->create(['judul' => 'Jembatan retak']);
        Pengaduan::factory()->aspirasi()->create(['judul' => 'Usulan taman bermain']);
        Pengaduan::factory()->aspirasi()->create(['judul' => 'Usulan pos ronda']);

        $this->actingAs($kepala)->get(route('kepala.pengaduan.index'))
            ->assertOk()
            ->assertSee('Jembatan retak')
            ->assertSee('Usulan taman bermain')
            ->assertViewHas('jumlahPerJenis', ['pengaduan' => 1, 'aspirasi' => 2]);

        $this->actingAs($kepala)->get(route('kepala.pengaduan.index', ['jenis' => 'aspirasi']))
            ->assertOk()
            ->assertSee('Usulan pos ronda')
            ->assertDontSee('Jembatan retak');

        $this->actingAs($kepala)->get(route('kepala.pengaduan.index', ['jenis' => 'pengaduan']))
            ->assertOk()
            ->assertSee('Jembatan retak')
            ->assertDontSee('Usulan taman bermain');
    }

    public function test_filter_pencarian_status_kategori_dusun_dan_tanggal(): void
    {
        $kepala = User::factory()->kepalaDesa()->create();
        $jalan = Kategori::factory()->create(['nama' => 'Jalan']);
        $pelaporKrajan = User::factory()->warga()->create(['nama_lengkap' => 'Rahmat Krajan', 'dusun' => 'Dusun Krajan']);
        $pelaporSukasari = User::factory()->warga()->create(['dusun' => 'Dusun Sukasari']);

        Pengaduan::factory()->create(['user_id' => $pelaporKrajan->id, 'kategori_id' => $jalan->id, 'judul' => 'Jalan desa rusak', 'status' => StatusLaporan::Diproses, 'created_at' => now()->subDays(2)]);
        Pengaduan::factory()->create(['user_id' => $pelaporSukasari->id, 'judul' => 'Sampah menumpuk', 'status' => StatusLaporan::Selesai, 'created_at' => now()->subDays(20)]);

        $this->actingAs($kepala)->get(route('kepala.pengaduan.index', ['q' => 'rahmat']))
            ->assertSee('Jalan desa rusak')->assertDontSee('Sampah menumpuk');
        $this->actingAs($kepala)->get(route('kepala.pengaduan.index', ['status' => 'selesai']))
            ->assertSee('Sampah menumpuk')->assertDontSee('Jalan desa rusak');
        $this->actingAs($kepala)->get(route('kepala.pengaduan.index', ['kategori' => $jalan->id]))
            ->assertSee('Jalan desa rusak')->assertDontSee('Sampah menumpuk');
        $this->actingAs($kepala)->get(route('kepala.pengaduan.index', ['dusun' => 'Dusun Sukasari']))
            ->assertSee('Sampah menumpuk')->assertDontSee('Jalan desa rusak');
        $this->actingAs($kepala)->get(route('kepala.pengaduan.index', ['tanggal_mulai' => now()->subDays(5)->toDateString()]))
            ->assertSee('Jalan desa rusak')->assertDontSee('Sampah menumpuk');
        $this->actingAs($kepala)->get(route('kepala.pengaduan.index', ['q' => 'tidak ada yang cocok']))
            ->assertSee('Data Tidak Ditemukan');
    }

    public function test_tombol_tertunda_menampilkan_laporan_diajukan_lebih_dari_tiga_hari(): void
    {
        $kepala = User::factory()->kepalaDesa()->create();
        Pengaduan::factory()->create(['judul' => 'Lama belum direspons', 'status' => StatusLaporan::Diajukan, 'created_at' => now()->subDays(4)]);
        Pengaduan::factory()->create(['judul' => 'Baru masuk kemarin', 'status' => StatusLaporan::Diajukan, 'created_at' => now()->subDay()]);
        Pengaduan::factory()->create(['judul' => 'Lama tapi sudah ditinjau', 'status' => StatusLaporan::Ditinjau, 'created_at' => now()->subDays(10)]);

        $this->actingAs($kepala)->get(route('kepala.pengaduan.index'))
            ->assertViewHas('jumlahTertunda', 1);

        $this->actingAs($kepala)->get(route('kepala.pengaduan.index', ['tertunda' => '1']))
            ->assertOk()
            ->assertSee('Lama belum direspons')
            ->assertDontSee('Baru masuk kemarin')
            ->assertDontSee('Lama tapi sudah ditinjau');
    }

    public function test_jenis_tidak_dikenal_ditolak(): void
    {
        $kepala = User::factory()->kepalaDesa()->create();

        $this->actingAs($kepala)->get(route('kepala.pengaduan.index', ['jenis' => 'lainnya']))
            ->assertSessionHasErrors('jenis');
    }
}
