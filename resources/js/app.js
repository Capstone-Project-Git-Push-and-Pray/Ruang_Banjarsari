

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

/*
 * Konfirmasi bertema SILAFA untuk formulir bertanda data-konfirmasi.
 * Pengiriman ditahan, popup <x-dialog-konfirmasi> ditampilkan, lalu formulir
 * dikirim ulang setelah pengguna menyetujui. Tanpa popup, kembali ke confirm() browser.
 */
document.addEventListener('submit', (event) => {
    const form = event.target;

    if (! (form instanceof HTMLFormElement) || ! form.dataset.konfirmasi) {
        return;
    }

    if (form.dataset.terkonfirmasi === '1') {
        delete form.dataset.terkonfirmasi;

        return;
    }

    event.preventDefault();
    event.stopImmediatePropagation();

    const submitter = event.submitter;
    const lanjut = () => {
        form.dataset.terkonfirmasi = '1';
        form.requestSubmit(submitter && form.contains(submitter) ? submitter : undefined);
    };

    if (! window.dialogKonfirmasiSiap) {
        if (window.confirm(form.dataset.konfirmasi)) {
            lanjut();
        }

        return;
    }

    const bahaya = form.dataset.konfirmasiJenis === 'bahaya';

    window.dispatchEvent(new CustomEvent('konfirmasi-buka', {
        detail: {
            judul: form.dataset.konfirmasiJudul || 'Konfirmasi Tindakan',
            pesan: form.dataset.konfirmasi,
            tombol: form.dataset.konfirmasiTombol || (bahaya ? 'Lanjutkan' : 'Setuju'),
            bahaya,
            ikon: form.dataset.konfirmasiIkon || (bahaya ? 'hapus' : 'setujui'),
            lanjut,
        },
    }));
}, true);

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
