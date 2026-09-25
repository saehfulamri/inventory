<script setup>
defineProps({
    id: { type: String, required: true },
    label: { type: String, default: null },
    modelValue: { type: [String, Number], default: null },
    options: { type: Array, default: () => [] }, // [{ value, label }]
    error: { type: String, default: null },
    required: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <div class="form__group">
        <label v-if="label" :for="id">{{ label }}</label>

        <select
            :id="id"
            :value="modelValue"
            :required="required"
            :aria-invalid="error ? 'true' : 'false'"
            :aria-describedby="error ? `${id}-error` : undefined"
            @change="emit('update:modelValue', $event.target.value)"
        >
            <option v-for="option in options" :key="option.value" :value="option.value">
                {{ option.label }}
            </option>
        </select>

        <span v-if="error" :id="`${id}-error`" class="form__error">{{ error }}</span>
    </div>
</template>