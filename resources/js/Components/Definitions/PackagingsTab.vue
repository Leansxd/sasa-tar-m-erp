<script setup lang="ts">
const props = defineProps<{
    packagings: any[];
}>();

const emit = defineEmits<{
    (e: 'create'): void;
    (e: 'edit', packaging: any): void;
    (e: 'delete', id: number): void;
}>();
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6">
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Paketleme Şekilleri</h2>
                <p class="text-xs text-slate-400">Çilek kasası, muz kolisi, plastik kaplar ve ambalaj standartları</p>
            </div>
            <button type="button" @click="emit('create')" class="bg-rose-600 hover:bg-rose-500 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                + Yeni Paket Tipi Ekle
            </button>
        </div>
        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/60 font-bold uppercase border-b">
                        <th class="p-3">Paket Adı</th>
                        <th class="p-3">Bağlı Olduğu Ürün</th>
                        <th class="p-3">Kapasite / Net Miktar</th>
                        <th class="p-3 text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="pkg in packagings" :key="pkg.id">
                        <td class="p-3 font-bold text-slate-800 dark:text-slate-100">{{ pkg.name }}</td>
                        <td class="p-3">
                            <span v-if="pkg.product" class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-bold rounded border border-emerald-200 dark:border-emerald-900 text-[11px]">
                                {{ pkg.product.name }}
                            </span>
                            <span v-else class="text-slate-400">Tüm Ürünler</span>
                        </td>
                        <td class="p-3 font-extrabold text-indigo-600 dark:text-indigo-400">{{ pkg.capacity_qty }} {{ pkg.unit?.symbol || 'kg' }}/kap</td>
                        <td class="p-3 text-right space-x-2">
                            <button type="button" @click="emit('edit', pkg)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                            <button type="button" @click="emit('delete', pkg.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
