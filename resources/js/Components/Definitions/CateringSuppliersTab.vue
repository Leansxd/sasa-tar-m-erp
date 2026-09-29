<script setup lang="ts">
const props = defineProps<{
    cateringSuppliers: any[];
}>();

const emit = defineEmits<{
    (e: 'create'): void;
    (e: 'edit', supplier: any): void;
    (e: 'delete', id: number): void;
}>();
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6">
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Yemek Tedarikçileri</h2>
                <p class="text-xs text-slate-400">Harici işçiler ve tesis personeli için yemek hizmeti tedarikçileri</p>
            </div>
            <button type="button" @click="emit('create')" class="bg-rose-600 hover:bg-rose-500 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                + Yeni Yemek Tedarikçisi Ekle
            </button>
        </div>
        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/60 font-bold uppercase border-b">
                        <th class="p-3">Firma Unvanı</th>
                        <th class="p-3">Yetkili & İletişim</th>
                        <th class="p-3">Kişi Başı Öğün Ücreti</th>
                        <th class="p-3">Telefon</th>
                        <th class="p-3 text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="cs in cateringSuppliers" :key="cs.id">
                        <td class="p-3 font-bold text-slate-800 dark:text-slate-100">{{ cs.company_title }}</td>
                        <td class="p-3">
                            <div class="font-semibold text-slate-700 dark:text-slate-300">{{ cs.contact_person || '-' }}</div>
                            <div class="text-[11px] text-slate-400">{{ cs.phone || '-' }}</div>
                        </td>
                        <td class="p-3">
                            <div class="font-black text-emerald-600 dark:text-emerald-400">₺{{ cs.meal_unit_price }} / öğün</div>
                            <div class="text-[11px] text-slate-400 font-bold">{{ cs.is_vat_included ? 'KDV Dahil' : 'KDV Hariç' }}</div>
                        </td>
                        <td class="p-3 font-mono font-semibold text-slate-700 dark:text-slate-300">{{ cs.phone || '-' }}</td>
                        <td class="p-3 text-right space-x-2">
                            <button type="button" @click="emit('edit', cs)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                            <button type="button" @click="emit('delete', cs.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
