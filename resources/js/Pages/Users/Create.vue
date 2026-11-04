<script setup>
import { inject } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import VButton from '../../Components/ui/VButton.vue';
import VFormField from '../../Components/ui/VFormField.vue';

const props = defineProps({
    roles: { type: Array, required: true },
});

const route = inject('route');

const form = useForm({
    name: '',
    email: '',
    role: 'cashier',
    password: '',
});

function submit() {
    form.post(route('users.store'));
}
</script>

<template>
    <Head title="Tambah Pengguna" />

    <AppLayout>
        <div class="page-header">
            <div class="page-title-group">
                <h1 class="page-title">Tambah Pengguna</h1>
                <p class="muted">Buat akun baru untuk pengguna sistem</p>
            </div>
            <div class="page-actions">
                <Link :href="route('users.index')">
                    <VButton variant="ghost">Kembali</VButton>
                </Link>
            </div>
        </div>

        <section class="panel">
            <form class="form-grid" @submit.prevent="submit">
                <VFormField id="name" label="Nama" v-model="form.name" :error="form.errors.name" required />
                <VFormField id="email" label="Email" type="email" v-model="form.email" :error="form.errors.email" required />
                <div class="form__group">
                    <label class="form__label" for="role">Peran</label>
                    <select id="role" v-model="form.role" class="input">
                        <option v-for="role in roles" :key="role.value" :value="role.value">{{ role.label }}</option>
                    </select>
                    <p v-if="form.errors.role" class="form__error">{{ form.errors.role }}</p>
                </div>
                <VFormField id="password" label="Password" type="password" v-model="form.password" :error="form.errors.password" required />
                <div class="form-actions">
                    <VButton type="submit" :disabled="form.processing">Simpan</VButton>
                </div>
            </form>
        </section>
    </AppLayout>
</template>
