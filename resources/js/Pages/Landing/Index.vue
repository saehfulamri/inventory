<script setup>
import { Head, Link } from '@inertiajs/vue3';

const features = [
    { title: 'Kasir (POS)', desc: 'Transaksi cepat, keranjang, pembayaran tunai/non-tunai.' },
    { title: 'Inventori', desc: 'Stok, peringatan stok menipis, riwayat pergerakan.' },
    { title: 'Pembelian', desc: 'Pencatatan pembelian dari supplier & update stok otomatis.' },
    { title: 'Laporan', desc: 'Ringkasan penjualan, pembelian, stok & arus barang.' },
    { title: 'Master Data', desc: 'Produk, kategori, satuan, supplier lengkap.' },
    { title: 'Multi Role', desc: 'Akses sesuai peran (Admin, Kasir, Gudang, Manager).' },
];

// Served from /public; bound dynamically so Vite does not try to bundle it.
const logoSrc = '/logo-yayasan-wahana-pp.png';

const categories = ['Alat Tulis', 'Perlengkapan Sekolah', 'Mewarnai & Kreatif', 'Minuman', 'Makanan Ringan', 'Sembako', 'Kebersihan', 'ATK Kantor'];
</script>

<template>
    <Head title="PRESMA MART - Toko Alat Perlengkapan Sekolah & Supermarket" />

    <div class="landing">
        <header class="landing__header">
            <div class="landing__container landing__nav">
                <div class="landing__brand">
                    <img :src="logoSrc" alt="" class="landing__brand-logo" width="32" height="37" />
                    <span class="landing__brand-name">PRESMA MART</span>
                </div>
                <nav class="landing__links">
                    <a href="#fitur">Fitur</a>
                    <a href="#kategori">Kategori</a>
                    <Link :href="route('login')">Masuk</Link>
                    <Link :href="route('login')" class="landing__cta">Mulai Jualan</Link>
                </nav>
            </div>
        </header>

        <main>
            <section class="landing__hero">
                <div class="landing__container landing__hero-grid">
                    <div>
                        <p class="landing__eyebrow">Toko Alat Perlengkapan Sekolah & Supermarket</p>
                        <h1 class="landing__title">Kelola Toko Sekolah & Supermarket Lebih Praktis</h1>
                        <p class="landing__subtitle">
                            PRESMA MART membantu kasir, gudang, dan pemilik toko mengelola penjualan, pembelian, stok, dan laporan dalam satu aplikasi sederhana.
                        </p>
                        <div class="landing__actions">
                            <Link :href="route('login')" class="landing__btn landing__btn--primary">Masuk ke Aplikasi</Link>
                            <a href="#fitur" class="landing__btn landing__btn--ghost">Lihat Fitur</a>
                        </div>
                        <div class="landing__stats">
                            <div><strong>POS</strong><span>Kasir Cepat</span></div>
                            <div><strong>Stok</strong><span>Terpantau Real-time</span></div>
                            <div><strong>Laporan</strong><span>Siap Dipakai</span></div>
                        </div>
                    </div>
                    <div class="landing__card">
                        <div class="landing__card-header">
                            <span>Transaksi Hari Ini</span>
                            <span class="landing__pill">Siap Digunakan</span>
                        </div>
                        <div class="landing__card-body">
                            <div class="landing__row"><span>Keranjang</span><span>3 Item</span></div>
                            <div class="landing__row"><span>Subtotal</span><span>Rp 49.000</span></div>
                            <div class="landing__row"><span>Diskon</span><span>Rp 0</span></div>
                            <div class="landing__divider"></div>
                            <div class="landing__row landing__row--total"><span>Total</span><span>Rp 49.000</span></div>
                            <div class="landing__btn landing__btn--primary landing__btn--full">Bayar (Tunai)</div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="fitur" class="landing__section">
                <div class="landing__container">
                    <div class="landing__section-head">
                        <p class="landing__eyebrow">Fitur Utama</p>
                        <h2 class="landing__section-title">Semua yang Dibutuhkan Toko Anda</h2>
                        <p class="landing__section-subtitle">Dirancang khusus untuk toko alat perlengkapan sekolah dan supermarket skala menengah.</p>
                    </div>
                    <div class="landing__grid">
                        <article v-for="feature in features" :key="feature.title" class="landing__feature">
                            <h3>{{ feature.title }}</h3>
                            <p>{{ feature.desc }}</p>
                        </article>
                    </div>
                </div>
            </section>

            <section id="kategori" class="landing__section landing__section--alt">
                <div class="landing__container">
                    <div class="landing__section-head">
                        <p class="landing__eyebrow">Kategori Produk</p>
                        <h2 class="landing__section-title">Cocok untuk Beragam Kebutuhan</h2>
                        <p class="landing__section-subtitle">Dari alat tulis, perlengkapan sekolah, hingga kebutuhan sembako harian.</p>
                    </div>
                    <div class="landing__chips">
                        <span v-for="cat in categories" :key="cat" class="landing__chip">{{ cat }}</span>
                    </div>
                </div>
            </section>

            <section class="landing__cta-section">
                <div class="landing__container landing__cta-box">
                    <div>
                        <h2 class="landing__section-title">Siap Kelola Toko Lebih Efisien?</h2>
                        <p class="landing__section-subtitle">Masuk dan mulai transaksi, pantau stok, buat laporan dalam hitungan menit.</p>
                    </div>
                    <Link :href="route('login')" class="landing__btn landing__btn--primary landing__btn--large">Masuk Sekarang</Link>
                </div>
            </section>
        </main>

        <footer class="landing__footer">
            <div class="landing__container landing__footer-inner">
                <div class="landing__brand">
                    <img :src="logoSrc" alt="" class="landing__brand-logo" width="32" height="37" />
                    <span class="landing__brand-name">PRESMA MART</span>
                </div>
                <p>© {{ new Date().getFullYear() }} PRESMA MART. Sistem Informasi Inventory Toko Alat Perlengkapan Sekolah & Supermarket.</p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.landing {
    --purple: #6d28d9;
    --orange: #c2410c;
    --orange-dark: #7c2d12;
    --orange-light: #fb923c;
    --glass-bg: rgba(255, 255, 255, 0.62);
    --glass-border: rgba(255, 255, 255, 0.75);
    --glass-shadow: 0 12px 40px rgba(124, 45, 18, 0.16);
    position: relative;
    overflow: hidden;
    color: #2b1408;
    background: linear-gradient(135deg, #fff0e3 0%, #ffe8d6 55%, #f5eeff 100%);
}
.landing::before,
.landing::after {
    content: '';
    position: absolute;
    border-radius: var(--radius-pill);
    filter: blur(90px);
    pointer-events: none;
    z-index: 0;
}
.landing::before { width: 520px; height: 520px; top: -120px; left: -140px; background: rgba(251, 146, 60, 0.5); }
.landing::after { width: 480px; height: 480px; top: 220px; right: -140px; background: rgba(139, 92, 246, 0.3); }
.landing > * { position: relative; z-index: 1; }

.landing__glass,
.landing__card,
.landing__feature,
.landing__chip {
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    box-shadow: var(--glass-shadow);
}
@supports (backdrop-filter: blur(1px)) {
    .landing__glass,
    .landing__card,
    .landing__feature,
    .landing__chip { backdrop-filter: blur(16px) saturate(160%); }
}
@supports not (backdrop-filter: blur(1px)) {
    .landing__glass,
    .landing__card,
    .landing__feature,
    .landing__chip { background: rgba(255, 255, 255, 0.94); }
}

.landing__container { max-width: 1120px; margin: 0 auto; padding: 0 1.5rem; }
.landing__header { position: sticky; top: 0; z-index: 10; background: rgba(255, 255, 255, 0.55); backdrop-filter: blur(14px); border-bottom: 1px solid var(--glass-border); }
.landing__nav { display: flex; align-items: center; justify-content: space-between; gap: 1.5rem; padding-top: 1.1rem; padding-bottom: 1.1rem; }
.landing__brand { display: flex; align-items: center; gap: 0.6rem; font-weight: 700; }
.landing__brand-logo { height: 2.25rem; width: auto; object-fit: contain; }
.landing__brand-name { font-size: 1rem; }
.landing__links { display: flex; align-items: center; gap: 2rem; font-size: 0.98rem; }
.landing__links a { color: #4a2a1a; text-decoration: none; }
.landing__links a:hover { color: var(--orange); }
.landing__cta { padding: 0.65rem 1.4rem; border-radius: var(--radius-pill); background: var(--orange); color: #fff !important; }
.landing__cta:hover { background: var(--orange-dark); color: #fff !important; }
.landing a:focus-visible { outline: 2px solid var(--orange-dark); outline-offset: 3px; }
.landing__hero { padding: 5rem 0 6rem; }
.landing__hero-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 4rem; align-items: center; }
.landing__eyebrow { margin: 0; text-transform: uppercase; letter-spacing: 0.2em; font-size: 0.75rem; color: var(--purple); font-weight: 700; }
.landing__title { font-size: clamp(2rem, 4vw, 3.6rem); line-height: 1.15; margin: 1.25rem 0 1.5rem; }
.landing__subtitle { margin: 0; line-height: 1.7; font-size: 1.05rem; color: #5a3a28; max-width: 560px; }
.landing__actions { display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 2.5rem; }
.landing__btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.8rem 1.6rem; border-radius: var(--radius-pill); text-decoration: none; font-weight: 600; }
.landing__btn--primary { background: linear-gradient(135deg, var(--orange), #ea580c); color: #fff; }
.landing__btn--primary:hover { background: var(--orange-dark); }
.landing__btn--ghost { border: 1px solid rgba(194, 65, 12, 0.4); color: var(--orange-dark); background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(10px); }
.landing__btn--full { width: 100%; margin-top: 0.75rem; }
.landing__btn--large { padding: 0.9rem 1.8rem; }
.landing__stats { display: flex; flex-wrap: wrap; gap: 1.5rem 3rem; margin-top: 3rem; }
.landing__stats div { display: flex; flex-direction: column; gap: 0.25rem; }
.landing__stats span { color: #5a3a28; font-size: 0.95rem; }
.landing__card { border-radius: var(--radius-lg); overflow: hidden; }
.landing__card-header { display: flex; align-items: center; justify-content: space-between; padding: 1.25rem 2rem; background: linear-gradient(90deg, rgba(234, 88, 12, 0.16), rgba(109, 40, 217, 0.1)); }
.landing__pill { padding: 0.25rem 0.7rem; border-radius: var(--radius-pill); background: rgba(109, 40, 217, 0.12); color: var(--purple); font-size: 0.9rem; font-weight: 600; }
.landing__card-body { display: flex; flex-direction: column; gap: 1rem; padding: 2rem; }
.landing__row { display: flex; align-items: center; justify-content: space-between; color: #5a3a28; }
.landing__row--total { font-weight: 700; color: #2b1408; font-size: 1.1rem; }
.landing__divider { height: 1px; background: rgba(194, 65, 12, 0.2); }
.landing__section { padding: 6rem 0; }
.landing__section--alt { background: rgba(255, 255, 255, 0.35); }
.landing__section-head { text-align: center; display: flex; flex-direction: column; align-items: center; gap: 1rem; }
.landing__section-title { font-size: clamp(1.8rem, 3vw, 2.8rem); line-height: 1.2; margin: 0; }
.landing__section-subtitle { margin: 0; line-height: 1.7; color: #5a3a28; max-width: 640px; }
.landing__grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-top: 3.5rem; }
.landing__feature { border-radius: var(--radius-lg); padding: 2rem; display: flex; flex-direction: column; gap: 0.75rem; }
.landing__feature h3 { margin: 0; color: var(--orange); }
.landing__feature p { margin: 0; line-height: 1.65; color: #5a3a28; }
.landing__chips { display: flex; flex-wrap: wrap; justify-content: center; gap: 1rem; margin-top: 3rem; }
.landing__chip { padding: 0.7rem 1.4rem; border-radius: var(--radius-pill); }
.landing__cta-section { padding: 6rem 0; }
.landing__cta-box { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 2rem; width: calc(100% - 3rem); margin: 0 auto; background: linear-gradient(135deg, #7c2d12 0%, #c2410c 55%, #ea580c 100%); color: #fff; border-radius: var(--radius-lg); padding: clamp(2rem, 6vw, 4rem); box-shadow: var(--glass-shadow); }
.landing__cta-box .landing__section-title { color: #fff; }
.landing__cta-box .landing__section-subtitle { color: rgba(255, 255, 255, 0.92); }
.landing__cta-box .landing__btn--primary { background: rgba(255, 255, 255, 0.92); color: var(--orange-dark); }
.landing__cta-box .landing__btn--primary:hover { background: #fff; }
.landing__footer { border-top: 1px solid var(--glass-border); background: rgba(255, 255, 255, 0.5); backdrop-filter: blur(12px); }
.landing__footer-inner p { margin: 0; }
.landing__footer-inner { display: flex; flex-direction: column; align-items: center; gap: 1rem; text-align: center; padding-top: 3rem; padding-bottom: 3rem; color: #5a3a28; }
@media (max-width: 720px) {
    .landing__links { display: none; }
    .landing__hero { padding: 3rem 0 4rem; }
    .landing__section, .landing__cta-section { padding: 4rem 0; }
}
</style>
