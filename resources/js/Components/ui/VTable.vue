<script setup>
defineProps({
    columns: { type: Array, required: true }, // [{ key, label }]
    rows: { type: Array, default: () => [] },
});
</script>

<template>
    <div class="table-wrap">
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
                <tr v-if="!rows.length">
                    <td :colspan="columns.length">
                        <slot name="empty">
                            <div class="empty-state">
                                <p>Belum ada data.</p>
                            </div>
                        </slot>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>