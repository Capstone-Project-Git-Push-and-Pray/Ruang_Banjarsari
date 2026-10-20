<?php

namespace Tests\Unit;

use App\Enums\JenisLaporan;
use App\Enums\Role;
use App\Enums\StatusAkun;
use App\Enums\StatusLaporan;
use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserRoleHelperTest extends TestCase
{
    public function test_enum_role_memiliki_nilai_dan_label_yang_benar(): void
    {
        $this->assertSame('warga', Role::Warga->value);
        $this->assertSame('admin_desa', Role::AdminDesa->value);
        $this->assertSame('kepala_desa', Role::KepalaDesa->value);

        $this->assertSame('Warga', Role::Warga->label());
        $this->assertSame('Admin Desa', Role::AdminDesa->label());
        $this->assertSame('Kepala Desa', Role::KepalaDesa->label());
    }

    public function test_enum_status_akun_memiliki_nilai_yang_benar(): void
    {
        $this->assertSame('aktif', StatusAkun::Aktif->value);
        $this->assertSame('menunggu_tinjauan', StatusAkun::MenungguTinjauan->value);
        $this->assertSame('ditolak', StatusAkun::Ditolak->value);
    }

    public function test_enum_status_laporan_memiliki_nilai_yang_benar(): void
    {
        $this->assertSame('diajukan', StatusLaporan::Diajukan->value);
        $this->assertSame('ditinjau', StatusLaporan::Ditinjau->value);
        $this->assertSame('diproses', StatusLaporan::Diproses->value);
        $this->assertSame('selesai', StatusLaporan::Selesai->value);
        $this->assertSame('ditolak', StatusLaporan::Ditolak->value);
    }

    public function test_enum_jenis_laporan_memiliki_nilai_yang_benar(): void
    {
        $this->assertSame('pengaduan', JenisLaporan::Pengaduan->value);
        $this->assertSame('aspirasi', JenisLaporan::Aspirasi->value);
    }

    public function test_user_role_helpers_berfungsi_dengan_benar(): void
    {
        $userWarga = new User(['role' => Role::Warga, 'status_akun' => StatusAkun::Aktif]);
        $this->assertTrue($userWarga->isWarga());
        $this->assertFalse($userWarga->isAdminDesa());
        $this->assertFalse($userWarga->isKepalaDesa());
        $this->assertTrue($userWarga->isAktif());

        $userAdmin = new User(['role' => Role::AdminDesa, 'status_akun' => StatusAkun::Aktif]);
        $this->assertFalse($userAdmin->isWarga());
        $this->assertTrue($userAdmin->isAdminDesa());
        $this->assertFalse($userAdmin->isKepalaDesa());

        $userKades = new User(['role' => Role::KepalaDesa, 'status_akun' => StatusAkun::Aktif]);
        $this->assertFalse($userKades->isWarga());
        $this->assertFalse($userKades->isAdminDesa());
        $this->assertTrue($userKades->isKepalaDesa());

        $userPending = new User(['role' => Role::Warga, 'status_akun' => StatusAkun::MenungguTinjauan]);
        $this->assertFalse($userPending->isAktif());
    }
}
