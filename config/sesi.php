<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Batas Waktu Tidak Aktif per Peran (menit)
    |--------------------------------------------------------------------------
    |
    | Pengguna yang tidak melakukan aktivitas melebihi batas ini akan keluar
    | otomatis dan harus masuk kembali. Admin dan Kepala Desa dibatasi lebih
    | singkat karena dapat melihat data pribadi seluruh warga (UU PDP).
    | SESSION_LIFETIME harus lebih besar dari nilai terbesar di sini (bawaan 240),
    | agar sesi lama masih terbaca dan pengguna melihat pesan "Sesi Anda telah berakhir".
    |
    */

    'batas_tidak_aktif_menit' => [
        'warga' => (int) env('SESI_WARGA_MENIT', 120),
        'admin_desa' => (int) env('SESI_ADMIN_MENIT', 30),
        'kepala_desa' => (int) env('SESI_KEPALA_MENIT', 30),
    ],

    /*
    | Popup peringatan tampil sekian detik sebelum sesi berakhir.
    */

    'peringatan_detik' => 120,

    /*
    | Masa berlaku "Ingat saya di perangkat ini" (khusus warga), dalam hari.
    */

    'ingat_saya_hari' => 30,

];
