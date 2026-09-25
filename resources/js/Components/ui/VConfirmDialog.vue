<script setup>
import { nextTick, ref, watch } from 'vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    title: { type: String, default: 'Konfirmasi' },
    message: { type: String, default: '' },
    confirmLabel: { type: String, default: 'Lanjutkan' },
    cancelLabel: { type: String, default: 'Batal' },
    variant: { type: String, default: 'danger' }, // primary | danger
});

const emit = defineEmits(['confirm', 'cancel']);

const cancelButtonRef = ref(null);
let previouslyFocused = null;

watch(() => props.open, async (open) => {
    if (open) {
        previouslyFocused = document.activeElement;
        await nextTick();
        cancelButtonRef.value?.focus();
        document.addEventListener('keydown', onKeydown);
    } else {
        document.removeEventListener('keydown', onKeydown);
        previouslyFocused?.focus?.();
    }
});

function onKeydown(event) {
    if (event.key === 'Escape') {
        emit('cancel');
    }
}

function onCancel() {
    emit('cancel');
}
</script>

<template>
    <Teleport to="body">
        <div v-if="open" class="backdrop" @click.self="onCancel">
            <div
                class="dialog"
                role="dialog"
                aria-modal="true"
                aria-labelledby="confirm-title"
                aria-describedby="confirm-message"
            >
                <h2 id="confirm-title" class="dialog__title">{{ title }}</h2>
                <p id="confirm-message" class="dialog__message">{{ message }}</p>

                <div class="dialog__actions">
                    <button type="button" ref="cancelButtonRef" class="btn btn--ghost" @click="onCancel">
                        {{ cancelLabel }}
                    </button>
                    <button
                        type="button"
                        class="btn"
                        :class="`btn--${variant}`"
                        @click="emit('confirm')"
                    >
                        {{ confirmLabel }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
.backdrop {
    position: fixed;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    background: rgba(0, 0, 0, 0.4);
    z-index: 50;
}

.dialog {
    width: 100%;
    max-width: 420px;
    background: var(--color-canvas);
    border: 1px solid var(--color-hairline);
    border-radius: var(--radius-lg);
    padding: 28px 24px;
}

.dialog__title {
    margin: 0 0 8px;
    font-size: 20px;
    font-weight: 600;
    letter-spacing: var(--ls-tight);
}

.dialog__message {
    margin: 0 0 24px;
    font-size: 15px;
    color: var(--color-ink-muted-80);
}

.dialog__actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}
</style>