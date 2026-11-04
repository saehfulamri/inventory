/**
 * Helper filter terpusat untuk halaman Index/Report.
 *
 * Semua halaman filter memakai konvensi yang sama untuk memutuskan apakah
 * sebuah nilai filter "diisi" sehingga layak dikirim sebagai query param:
 * - String kosong (`''`) dan `undefined`/`null` dianggap kosong.
 * - Angka `0` dan boolean `false` TIDAK dianggap kosong (mis. `is_active=0`
 *   adalah filter "Nonaktif" yang valid).
 *
 * @param {unknown} value
 * @returns {boolean}
 */
export function hasValue(value) {
    if (value === undefined || value === null) {
        return false;
    }

    if (typeof value === 'string') {
        return value !== '';
    }

    return true;
}

/**
 * Bangun objek query param dari sebuah objek filter, membuang nilai kosong.
 *
 * @param {Record<string, unknown>} filters
 * @returns {Record<string, unknown>}
 */
export function buildParams(filters) {
    const params = {};

    for (const [key, value] of Object.entries(filters)) {
        if (hasValue(value)) {
            params[key] = value;
        }
    }

    return params;
}

/**
 * Normalisasi nilai filter dari props server menjadi string untuk input
 * (select/date) — `null`/`undefined` menjadi `''`, sisanya di-String-kan.
 *
 * @param {unknown} value
 * @returns {string}
 */
export function filterString(value) {
    return value === undefined || value === null ? '' : String(value);
}

/**
 * Filter status aktif/nonaktif baku (Semua/Aktif/Nonaktif).
 *
 * @returns {{value: string, label: string}[]}
 */
export function activeStatusOptions() {
    return [
        { value: '', label: 'Semua' },
        { value: '1', label: 'Aktif' },
        { value: '0', label: 'Nonaktif' },
    ];
}
