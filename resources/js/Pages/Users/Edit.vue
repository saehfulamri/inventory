<script setup>
import { computed, inject } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import VButton from '../../Components/ui/VButton.vue';
import VFormField from '../../Components/ui/VFormField.vue';
import VSelect from '../../Components/ui/VSelect.vue';

const props = defineProps({
    user: { type: Object, required: true },
    roles: { type: Array, required: true },
});

const route = inject('route');

const roleOptions = computed(() => props.roles.map((role) => ({ value: role.value, label: role.label })));

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
    password: '',
});

function submit() {
    form.put(route('users.update', props.user.id));
}
</script>

<template>
    <Head title="Edit Pengguna" />

    <AppLayout>
        <h1 class="page-title">Edit Pengguna</h1>

        <p class="muted">{{ user.name }}</p>

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
                    label="Password Baru"
                    type="password"
                    v-model="form.password"
                    :error="form.errors.password"
                    autocomplete="new-password"
                    hint="Kosongkan jika tidak ingin mengubah password."
                />
            </div>

            <div class="form__actions">
                <VButton type="submit" :disabled="form.processing">Simpan Perubahan</VButton>
                <VButton variant="ghost" :href="route('users.index')">Batal</VButton>
            </div>
        </form>
    </AppLayout>
</template>
