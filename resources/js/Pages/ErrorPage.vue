<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    status: { type: Number, required: true },
});

const content = computed(() => ({
    403: { title: 'Akses ditolak', description: 'Anda tidak memiliki izin untuk mengakses halaman ini.' },
    404: { title: 'Halaman tidak ditemukan', description: 'Maaf, halaman yang Anda cari tidak ditemukan.' },
    419: { title: 'Sesi berakhir', description: 'Sesi Anda berakhir. Silakan masuk kembali.' },
    429: { title: 'Terlalu banyak permintaan', description: 'Terlalu banyak permintaan. Coba lagi nanti.' },
    500: { title: 'Terjadi kesalahan', description: 'Terjadi kesalahan pada server. Silakan coba lagi.' },
    503: { title: 'Layanan tidak tersedia', description: 'Layanan sedang dalam perawatan. Silakan coba lagi nanti.' },
})[props.status] ?? { title: `Kesalahan (${props.status})`, description: 'Terjadi kesalahan yang tidak terduga.' });
</script>

<template>
    <div class="error-page">
        <Head :title="content.title" />

        <h1 class="error-page__title">{{ content.title }}</h1>
        <p class="error-page__description">{{ content.description }}</p>
    </div>
</template>

<style scoped>
.error-page {
    max-width: 420px;
    margin: 48px auto 0;
    text-align: center;
}

.error-page__title {
    margin: 0 0 8px;
    font-size: 28px;
    font-weight: 600;
    letter-spacing: -0.02em;
}

.error-page__description {
    margin: 0;
    font-size: 15px;
    color: var(--color-ink-muted-80);
}
</style>