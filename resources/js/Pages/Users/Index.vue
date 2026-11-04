<script setup>
import { inject } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import VButton from '../../Components/ui/VButton.vue';
import VTable from '../../Components/ui/VTable.vue';
import VBadge from '../../Components/ui/VBadge.vue';

const props = defineProps({
    users: { type: Array, required: true },
    roles: { type: Array, required: true },
});

const route = inject('route');

const roleLabel = (role) => props.roles.find((r) => r.value === role)?.label || role;

const columns = [
    { key: 'name', label: 'Nama' },
    { key: 'email', label: 'Email' },
    { key: 'role', label: 'Peran' },
    { key: 'actions', label: 'Aksi' },
];

function removeUser(user) {
    if (confirm(`Hapus pengguna ${user.name}?`)) {
        router.delete(route('users.destroy', user.id));
    }
}
</script>

<template>
    <Head title="Pengguna" />

    <AppLayout>
        <div class="page-header">
            <div class="page-title-group">
                <h1 class="page-title">Pengguna</h1>
                <p class="muted">Kelola akun & peran pengguna sistem</p>
            </div>
            <div class="page-actions">
                <Link :href="route('users.create')">
                    <VButton>Tambah Pengguna</VButton>
                </Link>
            </div>
        </div>

        <section class="panel">
            <VTable :columns="columns" :rows="users">
                <template #role="{ row }">
                    <VBadge>{{ roleLabel(row.role) }}</VBadge>
                </template>
                <template #actions="{ row }">
                    <div class="table-actions">
                        <Link :href="route('users.edit', row.id)" class="text-link">Edit</Link>
                        <button type="button" class="text-link text-danger" @click="removeUser(row)">Hapus</button>
                    </div>
                </template>
            </VTable>
        </section>
    </AppLayout>
</template>
