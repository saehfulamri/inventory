<script setup>
import { computed, inject } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import VButton from '../../Components/ui/VButton.vue';
import VFormField from '../../Components/ui/VFormField.vue';

const route = inject('route');

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

    <div class="auth-card">
        <h1 class="auth-card__title">Masuk</h1>

        <div v-if="formErrors.length" class="flash flash--error" role="alert">
            <ul class="flash__list">
                <li v-for="error in formErrors" :key="error">{{ error }}</li>
            </ul>
        </div>

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
</template>