/**
 * Format nilai angka menjadi Rupiah untuk keperluan tampilan (indonesia).
 *
 * Contoh: formatCurrency(150000) => "Rp 150.000"
 * Nilai desimal dipotong (truncate) sesuai konvensi tampilan dashboard/produk.
 *
 * @param {number|string|null} value
 * @returns {string}
 */
export function formatCurrency(value) {
    const amount = Number(value) || 0;

    return `Rp ${new Intl.NumberFormat('id-ID').format(Math.trunc(amount))}`;
}

/**
 * Format angka kuantitas stok dengan 3 angka desimal (konvensi id-ID).
 *
 * Contoh: formatQuantity(15) => "15,000"
 *
 * @param {number|string|null} value
 * @returns {string}
 */
export function formatQuantity(value) {
    const amount = Number(value) || 0;

    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 3,
        maximumFractionDigits: 3,
    }).format(amount);
}

/**
 * Format tanggal ISO menjadi "dd/mm/yyyy HH:mm" (konvensi tampilan aplikasi).
 *
 * @param {string|null} value
 * @returns {string}
 */
export function formatDateTime(value) {
    if (!value) {
        return '';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '';
    }

    const pad = (n) => String(n).padStart(2, '0');

    return `${pad(date.getDate())}/${pad(date.getMonth() + 1)}/${date.getFullYear()} ${pad(date.getHours())}:${pad(date.getMinutes())}`;
}