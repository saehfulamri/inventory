<script setup>
import { computed, inject } from 'vue';
import { useForm } from '@inertiajs/vue3';
import VButton from '../ui/VButton.vue';
import VFormField from '../ui/VFormField.vue';

const props = defineProps({
    category: { type: Object, default: null },
});

const route = inject('route');

const form = useForm({
    name: props.category?.name ?? '',
    description: props.category?.description ?? '',
    is_active: props.category ? Boolean(props.category.is_active) : true,
});

const submitLabel = computed(() => (props.category ? 'Simpan Perubahan' : 'Simpan Kategori'));

function submit() {
    if (props.category) {
        form.put(route('categories.update', props.category.id));

        return;
    }

    form.post(route('categories.store'));
}
</script>

<template>
    <form class="form" @submit.prevent="submit">
        <div class="form__grid">
            <VFormField
                id="name"
                label="Nama Kategori"
                v-model="form.name"
                :error="form.errors.name"
                required
            />

            <VFormField
                id="description"
                label="Deskripsi (opsional)"
                type="textarea"
                v-model="form.description"
                :error="form.errors.description"
            />

            <div class="form__group">
                <label class="checkbox">
                    <input v-model="form.is_active" type="checkbox" />
                    Kategori aktif
                </label>
            </div>
        </div>

        <div class="form__actions">
            <VButton type="submit" :disabled="form.processing">{{ submitLabel }}</VButton>
            <VButton variant="ghost" :href="route('categories.index')">Batal</VButton>
        </div>
    </form>
</template>
