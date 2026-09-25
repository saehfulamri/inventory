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