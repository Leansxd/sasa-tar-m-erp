<script setup lang="ts">
const props = defineProps<{
    units: any[];
}>();

const emit = defineEmits<{
    (e: 'create'): void;
    (e: 'edit', unit: any): void;
    (e: 'delete', id: number): void;
}>();
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6">
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Birim Tanımları</h2>
                <p class="text-xs text-slate-400">Kg, Dal, Adet, Dekar, Tünel, Kasa ve Sehpa ölçü birimleri</p>
            </div>
            <button type="button" @click="emit('create')" class="bg-rose-600 hover:bg-rose-500 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                + Yeni Birim Ekle
            </button>
        </div>
        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/60 font-bold uppercase border-b">
                        <th class="p-3">Birim Adı</th>
                        <th class="p-3">Sembol</th>
                        <th class="p-3">Kategori</th>
                        <th class="p-3 text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="u in units" :key="u.id">
                        <td class="p-3 font-bold text-slate-800 dark:text-slate-100">{{ u.name }}</td>
                        <td class="p-3 font-mono font-bold text-rose-600 dark:text-rose-400">{{ u.symbol }}</td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 font-semibold rounded text-[11px]">
                                {{ u.unit_category }}
                            </span>
                        </td>
                        <td class="p-3 text-right space-x-2">
                            <button type="button" @click="emit('edit', u)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                            <button type="button" @click="emit('delete', u.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
