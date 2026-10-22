/**
 * Peta label & varian badge untuk status transaksi (Penerimaan/Penjualan).
 *
 * Nilai status mengikuti enum backend (`PurchaseStatus`, `SaleStatus`) dan
 * `PaymentMethod`. Dipakai lintas halaman agar tidak ada salinan yang berbeda.
 */

export const statusLabels = {
    draft: 'Draft',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
};

export const statusVariants = {
    draft: 'warning',
    completed: 'success',
    cancelled: 'danger',
};

export const paymentMethodLabels = {
    cash: 'Tunai',
    transfer: 'Transfer',
    qris: 'QRIS',
    card: 'Kartu',
};

/**
 * Bangun map `{ value: label }` dari prop options server (`[{ value, label }]`).
 *
 * @param {{value: string, label: string}[]} options
 * @returns {Record<string, string>}
 */
export function labelMapFrom(options) {
    const map = {};

    for (const option of options) {
        map[option.value] = option.label;
    }

    return map;
}
