<?php

namespace Tests\Unit;

use App\Enums\StatusAkun;
use App\Models\DataKependudukan;
use App\Models\User;
use App\Services\RegistrasiWargaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrasiWargaServiceTest extends TestCase
{
    use RefreshDatabase;

    private RegistrasiWargaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RegistrasiWargaService;
    }

    public function test_normalisasi_nama_menghapus_spasi_ekstra_dan_menjadikan_huruf_kapital(): void
    {
        $this->assertSame('BUDI SANTOSO', $this->service->normalisasiNama('   budi   santoso  '));
        $this->assertSame('SITI AMINAH', $this->service->normalisasiNama('siti      aminah'));
        $this->assertSame('AHMAD HIDAYATULLAH', $this->service->normalisasiNama('AHMAD HIDAYATULLAH'));
        $this->assertSame('', $this->service->normalisasiNama(null));
    }

    public function test_cocokkan_dengan_data_kependudukan_berhasil_jika_nik_dan_nama_sesuai(): void
    {
        DataKependudukan::factory()->create([
            'nik' => '3207010101990001',
            'nama_lengkap' => 'Dewi Sartika',
        ]);

        $cocokSamaPersis = $this->service->cocokkanDenganDataKependudukan('3207010101990001', 'Dewi Sartika');
        $cocokDenganSpasiDanCasing = $this->service->cocokkanDenganDataKependudukan('3207010101990001', '   dewi    SARTIKA  ');

        $this->assertTrue($cocokSamaPersis);
        $this->assertTrue($cocokDenganSpasiDanCasing);
    }

    public function test_cocokkan_dengan_data_kependudukan_gagal_jika_nik_atau_nama_tidak_sesuai(): void
    {
        DataKependudukan::factory()->create([
            'nik' => '3207010101990002',
            'nama_lengkap' => 'Joko Widodo',
        ]);

        $nikTidakAda = $this->service->cocokkanDenganDataKependudukan('9999999999999999', 'Joko Widodo');
        $namaTidakCocok = $this->service->cocokkanDenganDataKependudukan('3207010101990002', 'Prabowo Subianto');

        $this->assertFalse($nikTidakAda);
        $this->assertFalse($namaTidakCocok);
    }

    public function test_daftarkan_menetapkan_status_aktif_jika_data_cocok(): void
    {
        DataKependudukan::factory()->create([
            'nik' => '3207010101990003',
            'nama_lengkap' => 'Sri Mulyani',
            'tanggal_lahir' => '1980-08-26',
            'alamat' => 'Jl. Kebon Jeruk No. 5',
            'rt' => '001',
            'rw' => '001',
            'dusun' => 'Dusun Krajan',
        ]);

        $user = $this->service->daftarkan([
            'nik' => '3207010101990003',
            'nama_lengkap' => 'sri mulyani', // Huruf kecil tetap disimpan dan dinormalisasi menjadi capslock
            'tanggal_lahir' => '1980-08-26',
            'alamat' => 'Jl. Kebon Jeruk No. 5',
            'rt' => '001',
            'rw' => '001',
            'dusun' => 'Dusun Krajan',
            'telepon' => '081234567890',
            'password' => 'secret123',
        ]);

        $this->assertSame(StatusAkun::Aktif, $user->status_akun);
        $this->assertSame('SRI MULYANI', $user->nama_lengkap);
        $this->assertNotNull($user->persetujuan_pada);
    }

    public function test_daftarkan_menetapkan_status_menunggu_tinjauan_jika_ada_data_berbeda_dengan_master(): void
    {
        DataKependudukan::factory()->create([
            'nik' => '3207010101990099',
            'nama_lengkap' => 'Bambang Sudarsono',
            'tanggal_lahir' => '1985-05-15',
            'alamat' => 'Jl. Mawar Asli No. 10',
            'rt' => '001',
            'rw' => '001',
            'dusun' => 'Dusun Krajan',
        ]);

        // Alamat berbeda dengan master data
        $user = $this->service->daftarkan([
            'nik' => '3207010101990099',
            'nama_lengkap' => 'Bambang Sudarsono',
            'tanggal_lahir' => '1985-05-15',
            'alamat' => 'Jl. Pindahan Baru No. 88',
            'rt' => '001',
            'rw' => '001',
            'dusun' => 'Dusun Krajan',
            'telepon' => '081234567891',
            'password' => 'secret123',
        ]);

        $this->assertSame(StatusAkun::MenungguTinjauan, $user->status_akun);
        $this->assertNull($user->persetujuan_pada);
    }

    public function test_daftarkan_menetapkan_status_menunggu_tinjauan_jika_data_tidak_cocok(): void
    {
        $user = $this->service->daftarkan([
            'nik' => '3207010101990004',
            'nama_lengkap' => 'Tidak Ada Di Kependudukan',
            'tanggal_lahir' => '1985-05-15',
            'alamat' => 'Jl. Kenari No. 10',
            'rt' => '002',
            'rw' => '001',
            'dusun' => 'Dusun Krajan',
            'telepon' => '081234567891',
            'password' => 'secret123',
        ]);

        $this->assertSame(StatusAkun::MenungguTinjauan, $user->status_akun);
    }

    public function test_ajukan_ulang_mengubah_status_ke_aktif_jika_data_diperbaiki_sesuai(): void
    {
        DataKependudukan::factory()->create([
            'nik' => '3207010101990005',
            'nama_lengkap' => 'Raden Ajeng Kartini',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Jl. Kartini No. 1',
            'rt' => '001',
            'rw' => '001',
            'dusun' => 'Dusun Krajan',
        ]);

        $user = User::factory()->warga()->ditolak()->create([
            'nik' => '3207010101990005',
            'nama_lengkap' => 'Kartini Salah',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Jl. Kartini No. 1',
            'rt' => '001',
            'rw' => '001',
            'dusun' => 'Dusun Krajan',
            'alasan_penolakan' => 'Nama tidak sesuai KTP.',
        ]);

        $this->service->ajukanUlang($user, [
            'nama_lengkap' => 'Raden Ajeng Kartini',
        ]);

        $user->refresh();
        $this->assertSame(StatusAkun::Aktif, $user->status_akun);
        $this->assertSame('RADEN AJENG KARTINI', $user->nama_lengkap);
        $this->assertNull($user->alasan_penolakan);
    }

    public function test_bandingkan_data_mendeteksi_kesesuaian_dan_perbedaan(): void
    {
        $kependudukan = DataKependudukan::factory()->create([
            'nik' => '3207010101990006',
            'nama_lengkap' => 'Cut Nyak Dien',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Jl. Rencong No. 1',
            'rt' => '001',
            'rw' => '002',
            'dusun' => 'Dusun Krajan',
        ]);

        $user = User::factory()->warga()->create([
            'nik' => '3207010101990006',
            'nama_lengkap' => 'cut  nyak  dien', // cocok
            'tanggal_lahir' => '1990-01-01', // cocok
            'alamat' => 'Jl. Diponegoro No. 2', // beda
            'rt' => '01', // cocok (normalized ltrim 0)
            'rw' => '002', // cocok
            'dusun' => 'Dusun Sukasari', // beda
        ]);

        $hasil = $this->service->bandingkanData($user, $kependudukan);

        $this->assertTrue($hasil['nama_lengkap']['sama']);
        $this->assertTrue($hasil['tanggal_lahir']['sama']);
        $this->assertFalse($hasil['alamat']['sama']);
        $this->assertTrue($hasil['rt']['sama']);
        $this->assertTrue($hasil['rw']['sama']);
        $this->assertFalse($hasil['dusun']['sama']);
    }

    public function test_bandingkan_data_menangani_kependudukan_null(): void
    {
        $user = User::factory()->warga()->create([
            'nik' => '3207019999999999',
            'nama_lengkap' => 'Tanpa Kependudukan',
        ]);

        $hasil = $this->service->bandingkanData($user, null);

        $this->assertFalse($hasil['nama_lengkap']['sama']);
        $this->assertNull($hasil['nama_lengkap']['kependudukan']);
    }

    public function test_setujui_pendaftaran_memperbarui_status_dan_pencatat_tinjauan(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $user = User::factory()->warga()->menungguTinjauan()->create();

        $this->service->setujuiPendaftaran($user, $admin);

        $user->refresh();
        $this->assertSame(StatusAkun::Aktif, $user->status_akun);
        $this->assertSame($admin->id, $user->ditinjau_oleh);
        $this->assertNotNull($user->ditinjau_pada);
        $this->assertNull($user->alasan_penolakan);
    }

    public function test_tolak_pendaftaran_memperbarui_status_alasan_dan_pencatat_tinjauan(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $user = User::factory()->warga()->menungguTinjauan()->create();

        $this->service->tolakPendaftaran($user, $admin, 'Dokumen identitas tidak lengkap');

        $user->refresh();
        $this->assertSame(StatusAkun::Ditolak, $user->status_akun);
        $this->assertSame($admin->id, $user->ditinjau_oleh);
        $this->assertNotNull($user->ditinjau_pada);
        $this->assertSame('Dokumen identitas tidak lengkap', $user->alasan_penolakan);
    }
}
