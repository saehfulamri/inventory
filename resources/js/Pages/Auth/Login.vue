<script setup>
import { computed, inject } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import VButton from '../../Components/ui/VButton.vue';
import VFlash from '../../Components/ui/VFlash.vue';
import VFormField from '../../Components/ui/VFormField.vue';

const route = inject('route');

const logos = [
    { src: '/logo-yayasan-wahana-pp.png', alt: 'Logo Yayasan Wahana' },
    { src: '/logo-sma-pp.png', alt: 'Logo SMA' },
    { src: '/logo-smk-pp.png', alt: 'Logo SMK' },
];

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const formErrors = computed(() => [...new Set(Object.values(form.errors).filter(Boolean))]);

function submit() {
    form.post(route('login'));
}
</script>

<template>
    <Head title="Masuk" />

    <div class="login-split">
        <aside class="login-brand" aria-label="Identitas PRESMA MART">
            <div class="login-brand__content">
                <ul class="login-brand__logos">
                    <li v-for="logo in logos" :key="logo.src">
                        <img :src="logo.src" :alt="logo.alt" width="72" height="72" />
                    </li>
                </ul>
                <p class="login-brand__name">PRESMA MART</p>
                <p class="login-brand__tagline">Sistem inventori dan penjualan mini supermarket</p>
            </div>
        </aside>

        <main class="login-panel" id="main">
            <div class="login-panel__inner">
                <h1 class="auth-card__title">Masuk</h1>
                <p class="muted login-panel__intro">Masuk untuk mengelola stok dan penjualan.</p>

                <VFlash v-if="formErrors.length" type="error">
                    <ul class="flash__list">
                        <li v-for="error in formErrors" :key="error">{{ error }}</li>
                    </ul>
                </VFlash>

                <form class="form" @submit.prevent="submit">
                    <VFormField
                        id="email"
                        label="Email"
                        type="email"
                        v-model="form.email"
                        :error="form.errors.email"
                        required
                        autocomplete="username"
                        autofocus
                    />

                    <VFormField
                        id="password"
                        label="Password"
                        type="password"
                        v-model="form.password"
                        :error="form.errors.password"
                        required
                        autocomplete="current-password"
                    />

                    <div class="form__group">
                        <label class="checkbox">
                            <input v-model="form.remember" type="checkbox" />
                            Ingat saya
                        </label>
                    </div>

                    <VButton type="submit" block :disabled="form.processing">Masuk</VButton>
                </form>
            </div>
        </main>
    </div>
</template>
