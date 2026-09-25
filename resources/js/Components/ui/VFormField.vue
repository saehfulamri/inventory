<script setup>
const props = defineProps({
    id: { type: String, required: true },
    label: { type: String, default: null },
    type: { type: String, default: 'text' },
    modelValue: { type: [String, Number], default: '' },
    error: { type: String, default: null },
    required: { type: Boolean, default: false },
    autocomplete: { type: String, default: null },
    autofocus: { type: Boolean, default: false },
    placeholder: { type: String, default: null },
    min: { type: [String, Number], default: undefined },
    step: { type: [String, Number], default: undefined },
    rows: { type: [String, Number], default: 3 },
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <div class="form__group">
        <label v-if="label" :for="id">{{ label }}</label>

        <textarea
            v-if="type === 'textarea'"
            :id="id"
            :value="modelValue"
            :placeholder="placeholder"
            :required="required"
            :rows="rows"
            :aria-invalid="error ? 'true' : 'false'"
            :aria-describedby="error ? `${id}-error` : undefined"
            @input="emit('update:modelValue', $event.target.value)"
        />

        <input
            v-else
            :id="id"
            :type="type"
            :value="modelValue"
            :placeholder="placeholder"
            :required="required"
            :min="min"
            :step="step"
            :autocomplete="autocomplete ?? undefined"
            :autofocus="autofocus"
            :aria-invalid="error ? 'true' : 'false'"
            :aria-describedby="error ? `${id}-error` : undefined"
            @input="emit('update:modelValue', $event.target.value)"
        />

        <span v-if="error" :id="`${id}-error`" class="form__error">{{ error }}</span>
    </div>
</template>