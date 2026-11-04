<script setup>
import { computed, inject } from 'vue';
import { useForm } from '@inertiajs/vue3';
import VButton from '../ui/VButton.vue';
import VFormField from '../ui/VFormField.vue';

const props = defineProps({
    unit: { type: Object, default: null },
});

const route = inject('route');

const form = useForm({
    name: props.unit?.name ?? '',
    symbol: props.unit?.symbol ?? '',
    is_active: props.unit ? Boolean(props.unit.is_active) : true,
});

const submitLabel = computed(() => (props.unit ? 'Simpan Perubahan' : 'Simpan Satuan'));

function submit() {
    if (props.unit) {
        form.put(route('units.update', props.unit.id));

        return;
    }

    form.post(route('units.store'));
}
</script>

<template>
    <form class="form" @submit.prevent="submit">
        <div class="form__grid">
            <VFormField
                id="name"
                label="Nama Satuan"
                v-model="form.name"
                :error="form.errors.name"
                required
            />

            <VFormField
                id="symbol"
                label="Simbol (mis. pcs, box, kg)"
                v-model="form.symbol"
                :error="form.errors.symbol"
                required
            />

            <div class="form__group">
                <label class="checkbox">
                    <input v-model="form.is_active" type="checkbox" />
                    Satuan aktif
                </label>
            </div>
        </div>

        <div class="form__actions">
            <VButton type="submit" :disabled="form.processing">{{ submitLabel }}</VButton>
            <VButton variant="ghost" :href="route('units.index')">Batal</VButton>
        </div>
    </form>
</template>
