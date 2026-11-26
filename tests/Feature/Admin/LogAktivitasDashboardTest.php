<?php

namespace Tests\Feature\Admin;

use App\Enums\AksiModerasi;
use App\Enums\AlasanModerasi;
use App\Enums\JenisKonten;
use App\Enums\StatusAkun;
use App\Enums\StatusLaporan;
use App\Http\Middleware\BatasiSesiTidakAktif;
use App\Models\Pengaduan;
use App\Models\RiwayatModerasi;
use App\Models\RiwayatStatus;
use App\Models\Sanksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogAktivitasDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_menampilkan_aktivitas_asli_petugas_dari_yang_terbaru(): void
    {
        $admin = User::factory()->adminDesa()->create(['nama_lengkap' => 'Siti Operator']);
        $kepala = User::factory()->kepalaDesa()->create(['nama_lengkap' => 'Pak Kades']);
        $warga = User::factory()->warga()->create(['nama_lengkap' => 'Budi Warga']);
        $laporan = Pengaduan::factory()->create(['user_id' => $warga->id, 'judul' => 'Lampu jalan padam']);

        $this->riwayatStatus($laporan, $warga, StatusLaporan::Diajukan, now()->subMinutes(50));
        $this->riwayatStatus($laporan, $admin, StatusLaporan::Diproses, now()->subMinutes(40));

        User::factory()->warga()->create([
            'nama_lengkap' => 'Dewi Pendaftar',
            'status_akun' => StatusAkun::Aktif,
            'ditinjau_oleh' => $admin->id,
            'ditinjau_pada' => now()->subMinutes(30),
        ]);

        RiwayatModerasi::create([
            'jenis_konten' => JenisKonten::Komentar,
            'pemilik_id' => $warga->id,
            'aksi' => AksiModerasi::Sembunyikan,
            'alasan' => AlasanModerasi::Spam,
            'isi_konten' => 'Promo pinjaman',
            'dilakukan_oleh' => $admin->id,
        ])->forceFill(['created_at' => now()->subMinutes(20)])->save();

        Sanksi::factory()->create([
            'user_id' => $warga->id,
            'dicabut_pada' => now()->subMinutes(10),
            'jenis_pencabutan' => Sanksi::PENCABUTAN_DIBATALKAN,
            'diubah_oleh' => $kepala->id,
        ]);

        $respons = $this->actingAs($admin)->get(route('admin.dashboard'));

        $respons->assertOk()
            ->assertSeeInOrder([
                'Sanksi Dibatalkan',
                'PAK KADES membatalkan sanksi aspirasi milik BUDI WARGA',
                'Konten Disembunyikan',
                'SITI OPERATOR menyembunyikan komentar milik BUDI WARGA (spam atau promosi)',
                'Pendaftaran Warga Disetujui',
                'SITI OPERATOR menyetujui pendaftaran akun DEWI PENDAFTAR',
                'Status Laporan Diperbarui',
                'SITI OPERATOR mengubah status pengaduan &quot;Lampu jalan padam&quot; menjadi Diproses',
            ], false)
            ->assertDontSee('menjadi Diajukan')
            ->assertDontSee('Pencadangan Database Otomatis');
    }

    public function test_dashboard_menampilkan_keadaan_kosong_saat_belum_ada_aktivitas(): void
    {
        $admin = User::factory()->adminDesa()->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Belum ada aktivitas petugas');
    }

    public function test_pembaruan_otomatis_hanya_untuk_admin_dan_tidak_memperpanjang_sesi(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $warga = User::factory()->warga()->create();
        $aktivitasTerakhir = now()->subMinutes(5)->getTimestamp();

        $this->actingAs($warga)->get(route('admin.dashboard.aktivitas'))->assertForbidden();

        $this->actingAs($admin)
            ->withSession([BatasiSesiTidakAktif::KUNCI_AKTIVITAS => $aktivitasTerakhir])
            ->get(route('admin.dashboard.aktivitas'), [BatasiSesiTidakAktif::HEADER_PEMBARUAN_OTOMATIS => '1'])
            ->assertOk()
            ->assertSee('Belum ada aktivitas petugas')
            ->assertSessionHas(BatasiSesiTidakAktif::KUNCI_AKTIVITAS, $aktivitasTerakhir);
    }

    private function riwayatStatus(Pengaduan $laporan, User $pengubah, StatusLaporan $status, \DateTimeInterface $waktu): void
    {
        RiwayatStatus::create([
            'pengaduan_id' => $laporan->id,
            'status' => $status,
            'diubah_oleh' => $pengubah->id,
        ])->forceFill(['created_at' => $waktu])->save();
    }
}
