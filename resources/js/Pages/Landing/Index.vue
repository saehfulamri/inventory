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

const categories = ['Alat Tulis', 'Perlengkapan Sekolah', 'Mewarnai & Kreatif', 'Minuman', 'Makanan Ringan', 'Sembako', 'Kebersihan', 'ATK Kantor'];
</script>

<template>
    <Head title="School Mart - Toko Alat Perlengkapan Sekolah & Supermarket" />

    <div class="landing">
        <header class="landing__header">
            <div class="landing__container landing__nav">
                <div class="landing__brand">
                    <span class="landing__brand-mark">SM</span>
                    <span class="landing__brand-name">School Mart</span>
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
                            School Mart membantu kasir, gudang, dan pemilik toko mengelola penjualan, pembelian, stok, dan laporan dalam satu aplikasi sederhana.
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
                    <span class="landing__brand-mark">SM</span>
                    <span class="landing__brand-name">School Mart</span>
                </div>
                <p>© {{ new Date().getFullYear() }} School Mart. Sistem Informasi Inventory Toko Alat Perlengkapan Sekolah & Supermarket.</p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.landing { background: #f8fafc; color: #0f172a; }
.landing__container { max-width: 1120px; margin: 0 auto; padding: 0 1.5rem; }
.landing__header { position: sticky; top: 0; background: rgba(248,250,252,0.9); backdrop-filter: blur(8px); border-bottom: 1px solid #e2e8f0; z-index: 10; }
.landing__nav { display: flex; align-items: center; justify-content: space-between; padding-top: 0.9rem; padding-bottom: 0.9rem; }
.landing__brand { display: flex; align-items: center; gap: 0.6rem; font-weight: 700; }
.landing__brand-mark { display: inline-flex; align-items: center; justify-content: center; width: 2rem; height: 2rem; border-radius: 0.5rem; background: #0f766e; color: #fff; }
.landing__brand-name { font-size: 1rem; }
.landing__links { display: flex; align-items: center; gap: 1.2rem; font-size: 0.98rem; }
.landing__links a { color: #334155; text-decoration: none; }
.landing__links a:hover { color: #0f766e; }
.landing__cta { padding: 0.55rem 1rem; border-radius: 999px; background: #0f766e; color: #fff !important; }
.landing__hero { padding: 4rem 0 3rem; }
.landing__hero-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2.5rem; align-items: center; }
.landing__eyebrow { text-transform: uppercase; letter-spacing: 0.2em; font-size: 0.75rem; color: #0f766e; font-weight: 600; }
.landing__title { font-size: clamp(2rem, 4vw, 3.6rem); line-height: 1.05; margin: 0.8rem 0; }
.landing__subtitle { font-size: 1.05rem; color: #475569; max-width: 560px; }
.landing__actions { display: flex; flex-wrap: wrap; gap: 0.8rem; margin-top: 1.8rem; }
.landing__btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.7rem 1.4rem; border-radius: 999px; text-decoration: none; font-weight: 600; }
.landing__btn--primary { background: #0f766e; color: #fff; }
.landing__btn--ghost { border: 1px solid #cbd5f5; color: #0f172a; background: #fff; }
.landing__btn--full { width: 100%; margin-top: 1rem; }
.landing__btn--large { padding: 0.9rem 1.8rem; }
.landing__stats { display: flex; flex-wrap: wrap; gap: 1.6rem; margin-top: 2rem; }
.landing__stats div { display: flex; flex-direction: column; }
.landing__stats span { color: #475569; font-size: 0.95rem; }
.landing__card { background: #fff; border-radius: 1rem; box-shadow: 0 40px 60px -40px rgba(15,118,110,0.4); border: 1px solid #e2e8f0; overflow: hidden; }
.landing__card-header { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.6rem; background: linear-gradient(90deg, rgba(15,118,110,0.08), transparent); }
.landing__pill { padding: 0.25rem 0.7rem; border-radius: 999px; background: rgba(15,118,110,0.12); color: #0f766e; font-size: 0.9rem; }
.landing__card-body { display: flex; flex-direction: column; gap: 0.8rem; padding: 1.4rem 1.6rem; }
.landing__row { display: flex; align-items: center; justify-content: space-between; color: #475569; }
.landing__row--total { font-weight: 700; color: #0f172a; font-size: 1.1rem; }
.landing__divider { height: 1px; background: #e2e8f0; }
.landing__section { padding: 3rem 0; }
.landing__section--alt { background: #fff; }
.landing__section-head { text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.6rem; }
.landing__section-title { font-size: clamp(1.8rem, 3vw, 2.8rem); margin: 0; }
.landing__section-subtitle { color: #475569; max-width: 640px; }
.landing__grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.2rem; margin-top: 2.4rem; }
.landing__feature { background: #fff; border-radius: 0.9rem; border: 1px solid #e2e8f0; padding: 1.6rem; display: flex; flex-direction: column; gap: 0.6rem; }
.landing__section--alt .landing__feature { background: #f8fafc; }
.landing__feature h3 { margin: 0; }
.landing__feature p { margin: 0; color: #475569; }
.landing__chips { display: flex; flex-wrap: wrap; justify-content: center; gap: 0.8rem; margin-top: 2rem; }
.landing__chip { padding: 0.6rem 1.2rem; border-radius: 999px; background: #f1f5f9; border: 1px solid #e2e8f0; }
.landing__cta-section { padding: 3rem 0 4rem; }
.landing__cta-box { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1.6rem; background: radial-gradient(circle at top right, rgba(15,118,110,0.16), transparent), #0f766e; color: #fff; border-radius: 1.2rem; padding: clamp(2rem, 6vw, 3.2rem); }
.landing__cta-box .landing__section-title { color: #fff; }
.landing__cta-box .landing__section-subtitle { color: rgba(255,255,255,0.85); }
.landing__cta-box .landing__btn--primary { background: #fff; color: #0f766e; }
.landing__footer { border-top: 1px solid #e2e8f0; background: #fff; }
.landing__footer-inner { display: flex; flex-direction: column; align-items: center; gap: 0.8rem; text-align: center; padding-top: 1.6rem; padding-bottom: 1.6rem; color: #475569; }
@media (max-width: 720px) {
    .landing__links { display: none; }
}
</style>
