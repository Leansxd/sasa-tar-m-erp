<script setup lang="ts">
const props = defineProps<{
    deliveryTypes: any[];
}>();

const emit = defineEmits<{
    (e: 'create'): void;
    (e: 'edit', deliveryType: any): void;
}>();
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6">
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Ürün Teslimat Şekli</h2>
                <p class="text-xs text-slate-400">Depo teslimi, müşteri nakliyesi ve şirket aracıyla teslimat modelleri</p>
            </div>
            <button type="button" @click="emit('create')" class="bg-rose-600 hover:bg-rose-500 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                + Yeni Teslim Şekli Ekle
            </button>
        </div>
        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/60 font-bold uppercase border-b">
                        <th class="p-3">Teslimat Tanımı</th>
                        <th class="p-3">Teslimat Kodu</th>
                        <th class="p-3">Taşıyan Taraf</th>
                        <th class="p-3">Nakliye Ücret Şartı</th>
                        <th class="p-3 text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="dt in deliveryTypes" :key="dt.id">
                        <td class="p-3 font-bold text-slate-800 dark:text-slate-100">{{ dt.name }}</td>
                        <td class="p-3 font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ dt.code }}</td>
                        <td class="p-3">
                            <span :class="dt.transport_by === 'customer' ? 'bg-amber-50 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : 'bg-blue-50 text-blue-800 dark:bg-blue-950 dark:text-blue-300'" class="px-2 py-0.5 font-bold rounded text-[11px]">
                                {{ dt.transport_by === 'customer' ? 'Müşteri Kendi Aracıyla' : 'Bizim Şirket Aracımızla' }}
                            </span>
                        </td>
                        <td class="p-3 font-semibold">
                            <span v-if="dt.is_fee_included" class="text-emerald-600 dark:text-emerald-400 font-bold">Fiyata Dahil</span>
                            <span v-else class="text-rose-600 dark:text-rose-400 font-bold">Ek Nakliye Ücretli (₺{{ dt.extra_fee }})</span>
                        </td>
                        <td class="p-3 text-right space-x-2">
                            <button type="button" @click="emit('edit', dt)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
