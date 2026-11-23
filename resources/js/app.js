

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

/*
 * Pertahankan posisi gulir setelah formulir bertanda data-pertahankan-gulir dikirim
 * (misalnya komentar), agar halaman tidak melompat kembali ke atas setelah dimuat ulang.
 */
const KUNCI_POSISI_GULIR = 'silafa:posisi-gulir';

document.addEventListener('submit', (event) => {
    if (event.defaultPrevented || ! event.target.matches('form[data-pertahankan-gulir]')) {
        return;
    }

    sessionStorage.setItem(KUNCI_POSISI_GULIR, JSON.stringify({
        halaman: location.pathname + location.search,
        y: window.scrollY,
    }));
});

const pulihkanPosisiGulir = (hapus) => {
    const tersimpan = JSON.parse(sessionStorage.getItem(KUNCI_POSISI_GULIR) || 'null');

    if (! tersimpan || tersimpan.halaman !== location.pathname + location.search) {
        return;
    }

    window.scrollTo({ top: tersimpan.y, behavior: 'instant' });

    if (hapus) {
        sessionStorage.removeItem(KUNCI_POSISI_GULIR);
    }
};

pulihkanPosisiGulir(false);
window.addEventListener('load', () => pulihkanPosisiGulir(true));
