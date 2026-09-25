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
 * Format angka dengan jumlah desimal tertentu (konvensi id-ID).
 *
 * Contoh: formatNumber(1500) => "1.500"
 *
 * @param {number|string|null} value
 * @param {number} [decimals=0]
 * @returns {string}
 */
export function formatNumber(value, decimals = 0) {
    const amount = Number(value) || 0;

    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    }).format(amount);
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

/**
 * Format tanggal menjadi "dd/mm/yyyy" (konvensi tampilan aplikasi).
 * Menerima "yyyy-mm-dd" maupun string timestamp ISO.
 *
 * @param {string|null} value
 * @returns {string}
 */
export function formatDate(value) {
    if (!value) {
        return '';
    }

    if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
        const [year, month, day] = value.split('-');

        return `${day}/${month}/${year}`;
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '';
    }

    const pad = (n) => String(n).padStart(2, '0');

    return `${pad(date.getDate())}/${pad(date.getMonth() + 1)}/${date.getFullYear()}`;
}

/**
 * Tanggal hari ini dalam format "yyyy-mm-dd" (zona waktu lokal), untuk input type="date".
 *
 * @returns {string}
 */
export function todayISO() {
    const date = new Date();
    const pad = (n) => String(n).padStart(2, '0');

    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
}