<script setup>
import { computed, inject } from 'vue';
import { useForm } from '@inertiajs/vue3';
import VButton from '../ui/VButton.vue';
import VFormField from '../ui/VFormField.vue';

const props = defineProps({
    supplier: { type: Object, default: null },
});

const route = inject('route');

const form = useForm({
    code: props.supplier?.code ?? '',
    name: props.supplier?.name ?? '',
    phone: props.supplier?.phone ?? '',
    email: props.supplier?.email ?? '',
    address: props.supplier?.address ?? '',
    is_active: props.supplier ? Boolean(props.supplier.is_active) : true,
});

const submitLabel = computed(() => (props.supplier ? 'Simpan Perubahan' : 'Simpan Supplier'));

function submit() {
    if (props.supplier) {
        form.put(route('suppliers.update', props.supplier.id));

        return;
    }

    form.post(route('suppliers.store'));
}
</script>

<template>
    <form class="form" @submit.prevent="submit">
        <div class="form__grid">
            <VFormField
                id="code"
                label="Kode (opsional)"
                v-model="form.code"
                :error="form.errors.code"
            />

            <VFormField
                id="name"
                label="Nama Supplier"
                v-model="form.name"
                :error="form.errors.name"
                required
            />

            <VFormField
                id="phone"
                label="Telepon (opsional)"
                v-model="form.phone"
                :error="form.errors.phone"
            />

            <VFormField
                id="email"
                label="Email (opsional)"
                type="email"
                v-model="form.email"
                :error="form.errors.email"
            />

            <VFormField
                id="address"
                label="Alamat (opsional)"
                type="textarea"
                v-model="form.address"
                :error="form.errors.address"
            />

            <div class="form__group">
                <label class="checkbox">
                    <input v-model="form.is_active" type="checkbox" />
                    Supplier aktif
                </label>
            </div>
        </div>

        <div class="form__actions">
            <VButton type="submit" :disabled="form.processing">{{ submitLabel }}</VButton>
            <VButton variant="ghost" :href="route('suppliers.index')">Batal</VButton>
        </div>
    </form>
</template>