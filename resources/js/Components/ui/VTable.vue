<script setup>
import { computed } from 'vue';

const props = defineProps({
    columns: { type: Array, required: true }, // [{ key, label, primary? }]
    rows: { type: Array, default: () => [] },
});

// Below lg each row is rendered as a card: `photo` becomes the media,
// the primary (or first) column the title, `actions` the footer.
const mediaColumn = computed(() => props.columns.find((column) => column.key === 'photo'));
const actionsColumn = computed(() => props.columns.find((column) => column.key === 'actions'));
const titleColumn = computed(() => props.columns.find((column) => column.primary)
    ?? props.columns.find((column) => column !== mediaColumn.value && column !== actionsColumn.value));
const detailColumns = computed(() => props.columns.filter((column) => ![
    mediaColumn.value,
    actionsColumn.value,
    titleColumn.value,
].includes(column)));
</script>

<template>
    <div v-if="!rows.length" class="table-wrap">
        <slot name="empty">
            <div class="empty-state">
                <p>Belum ada data.</p>
            </div>
        </slot>
    </div>

    <template v-else>
        <div class="table-wrap hidden lg:block print:block">
            <table class="table">
                <thead>
                    <tr>
                        <th v-for="column in columns" :key="column.key">
                            {{ column.label }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, index) in rows" :key="index">
                        <td v-for="column in columns" :key="column.key">
                            <slot :name="column.key" :row="row" :value="row[column.key]">
                                {{ row[column.key] }}
                            </slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <ul class="data-cards lg:hidden print:hidden">
            <li v-for="(row, index) in rows" :key="index" class="data-card">
                <div class="data-card__header">
                    <div v-if="mediaColumn" class="data-card__media">
                        <slot :name="mediaColumn.key" :row="row" :value="row[mediaColumn.key]" />
                    </div>
                    <div v-if="titleColumn" class="data-card__title">
                        <slot :name="titleColumn.key" :row="row" :value="row[titleColumn.key]">
                            {{ row[titleColumn.key] }}
                        </slot>
                    </div>
                </div>

                <dl class="data-card__details">
                    <div v-for="column in detailColumns" :key="column.key" class="data-card__row">
                        <dt>{{ column.label }}</dt>
                        <dd>
                            <slot :name="column.key" :row="row" :value="row[column.key]">
                                {{ row[column.key] }}
                            </slot>
                        </dd>
                    </div>
                </dl>

                <div v-if="actionsColumn" class="data-card__actions">
                    <slot :name="actionsColumn.key" :row="row" :value="row[actionsColumn.key]" />
                </div>
            </li>
        </ul>
    </template>
</template>
