<script setup>
import { computed, inject } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import VButton from '../../Components/ui/VButton.vue';
import VFormField from '../../Components/ui/VFormField.vue';
import VSelect from '../../Components/ui/VSelect.vue';

const props = defineProps({
    roles: { type: Array, required: true },
});

const route = inject('route');

const roleOptions = computed(() => props.roles.map((role) => ({ value: role.value, label: role.label })));

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
        <h1 class="page-title">Tambah Pengguna</h1>

        <p class="muted">Buat akun baru untuk pengguna sistem</p>

        <form class="form" @submit.prevent="submit">
            <div class="form__grid">
                <VFormField
                    id="name"
                    label="Nama"
                    v-model="form.name"
                    :error="form.errors.name"
                    autocomplete="name"
                    required
                />

                <VFormField
                    id="email"
                    label="Email"
                    type="email"
                    v-model="form.email"
                    :error="form.errors.email"
                    autocomplete="off"
                    required
                />

                <VSelect
                    id="role"
                    label="Peran"
                    v-model="form.role"
                    :options="roleOptions"
                    :error="form.errors.role"
                    required
                />

                <VFormField
                    id="password"
                    label="Password"
                    type="password"
                    v-model="form.password"
                    :error="form.errors.password"
                    autocomplete="new-password"
                    required
                />
            </div>

            <div class="form__actions">
                <VButton type="submit" :disabled="form.processing">Simpan Pengguna</VButton>
                <VButton variant="ghost" :href="route('users.index')">Batal</VButton>
            </div>
        </form>
    </AppLayout>
</template>
