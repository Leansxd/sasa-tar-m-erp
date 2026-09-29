<script setup lang="ts">
const props = defineProps<{
    tradingParties: any[];
}>();

const emit = defineEmits<{
    (e: 'create'): void;
    (e: 'edit', party: any): void;
    (e: 'delete', id: number): void;
}>();
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6">
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Alıcı & Satıcı Carileri</h2>
                <p class="text-xs text-slate-400">Alıcı ve satıcı ticari partnerleri listesi</p>
            </div>
            <div class="space-x-2">
                <button type="button" @click="emit('create')" class="bg-rose-600 hover:bg-rose-500 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                    + Yeni Cari Firma Ekle
                </button>
            </div>
        </div>
        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/60 font-bold uppercase border-b">
                        <th class="p-3">Cari Unvanı</th>
                        <th class="p-3">Cari Türü</th>
                        <th class="p-3">Vergi / TCKN No</th>
                        <th class="p-3">İletişim & Adres</th>
                        <th class="p-3">Nakliye Koşulu</th>
                        <th class="p-3 text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="tp in tradingParties" :key="tp.id">
                        <td class="p-3 font-bold text-slate-800 dark:text-slate-100">{{ tp.name }}</td>
                        <td class="p-3">
                            <span :class="tp.type === 'buyer' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'" class="px-2 py-0.5 font-bold rounded border text-[11px]">
                                {{ tp.type === 'buyer' ? 'Alıcı (Müşteri)' : (tp.type === 'supplier' ? 'Satıcı (Tedarikçi)' : 'Alıcı & Satıcı') }}
                            </span>
                        </td>
                        <td class="p-3 font-mono font-semibold text-slate-700 dark:text-slate-300">
                            {{ tp.tax_number || '-' }}
                        </td>
                        <td class="p-3">
                            <div class="font-medium text-slate-700 dark:text-slate-300">{{ tp.phone || '-' }}</div>
                            <div class="text-[11px] text-slate-400 truncate max-w-[200px]">{{ tp.address || tp.email || '-' }}</div>
                        </td>
                        <td class="p-3 font-semibold">
                            <span v-if="tp.default_transport_fee" class="text-rose-600 dark:text-rose-400">₺{{ tp.default_transport_fee }} / Sabit</span>
                            <span v-else class="text-slate-400">Standart / Ücretsiz</span>
                        </td>
                        <td class="p-3 text-right space-x-2">
                            <button type="button" @click="emit('edit', tp)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                            <button type="button" @click="emit('delete', tp.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
