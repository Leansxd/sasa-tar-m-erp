<script setup lang="ts">
const props = defineProps<{
    products: any[];
}>();

const emit = defineEmits<{
    (e: 'create'): void;
    (e: 'edit', product: any): void;
    (e: 'delete', id: number): void;
}>();
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6">
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Ürünler & Kalite Sınıfları</h2>
                <p class="text-xs text-slate-400">Üretilen mahsuller, tüketilen girdiler ve kalite alt türleri</p>
            </div>
            <button type="button" @click="emit('create')" class="bg-rose-600 hover:bg-rose-500 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                + Yeni Ürün Ekle
            </button>
        </div>
        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/60 font-bold uppercase border-b">
                        <th class="p-3">Ürün Kodu</th>
                        <th class="p-3">Ürün Adı</th>
                        <th class="p-3">Ürün Tipi</th>
                        <th class="p-3">Alt Tipler & Kalite Sınıfları</th>
                        <th class="p-3">Bağlı Paketler</th>
                        <th class="p-3 text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="p in products" :key="p.id">
                        <td class="p-3 font-mono font-bold text-rose-600 dark:text-rose-400">{{ p.code }}</td>
                        <td class="p-3 font-extrabold text-slate-800 dark:text-slate-100">{{ p.name }}</td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded text-[11px]">
                                {{ p.product_type === 'harvest' ? 'Mahsul (Hasat)' : (p.product_type === 'seedling' ? 'Fide/Fidan' : 'Girdi / Kimyasal') }}
                            </span>
                        </td>
                        <td class="p-3">
                            <div v-if="p.subtypes?.length" class="flex flex-wrap gap-1">
                                <span v-for="st in p.subtypes" :key="st.id" class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-bold rounded border border-emerald-200 dark:border-emerald-900 text-[10px]">
                                    {{ st.name }}
                                </span>
                            </div>
                            <span v-else class="text-slate-400">Tek Kalite</span>
                        </td>
                        <td class="p-3">
                            <div v-if="p.packagings?.length" class="flex flex-wrap gap-1">
                                <span v-for="pkg in p.packagings" :key="pkg.id" class="px-2 py-0.5 bg-purple-50 dark:bg-purple-950 text-purple-700 dark:text-purple-300 font-bold rounded text-[10px]">
                                    {{ pkg.name }}
                                </span>
                            </div>
                            <span v-else class="text-slate-400 italic">Paket Tanımsız</span>
                        </td>
                        <td class="p-3 text-right space-x-2">
                            <button type="button" @click="emit('edit', p)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                            <button type="button" @click="emit('delete', p.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
