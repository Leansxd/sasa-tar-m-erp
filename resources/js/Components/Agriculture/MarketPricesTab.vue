<script setup lang="ts">
const props = defineProps<{
    marketPrices?: any[];
}>();

const emit = defineEmits<{
    (e: 'create'): void;
    (e: 'edit', marketPrice: any): void;
    (e: 'delete', id: number): void;
}>();

const formatDisplayDate = (val: string) => {
    if (!val) return '-';
    const clean = val.split('T')[0];
    const parts = clean.split('-');
    if (parts.length === 3) {
        return `${parts[2]}.${parts[1]}.${parts[0]}`;
    }
    return clean;
};
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 space-y-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">Piyasa ve Hal Fiyatları Takibi</h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Ürün bazında günlük piyasa ve hal fiyatları</p>
            </div>
            <button @click="emit('create')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                <span class="text-sm leading-none">+</span> Yeni Fiyat Kaydı Gir
            </button>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 my-3 p-2 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam Fiyat Kaydı</span>
                <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ (marketPrices || []).length }} Kayıt</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Çilek Hal Fiyatı</span>
                <span class="font-bold font-mono text-emerald-600 dark:text-emerald-400 text-sm">₺75,00 / kg</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Muz Hal Fiyatı</span>
                <span class="font-bold font-mono text-indigo-600 dark:text-indigo-400 text-sm">₺55,00 / kg</span>
            </div>
            <div class="px-2">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Güncellenme Tarihi</span>
                <span class="font-bold text-slate-700 dark:text-slate-300 text-sm">Bugün</span>
            </div>
        </div>

        <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800">
            <table class="w-full text-left text-xs whitespace-nowrap">
                <thead class="bg-slate-50 dark:bg-slate-950/50 text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="px-3 py-2 font-semibold">Tarih</th>
                        <th class="px-3 py-2 font-semibold">Ürün Adı</th>
                        <th class="px-3 py-2 font-semibold">Kaynak / Borsa</th>
                        <th class="px-3 py-2 font-semibold text-right">Fiyat</th>
                        <th class="px-3 py-2 font-semibold text-right w-24">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60">
                    <tr v-for="mp in [...(marketPrices || [])].sort((a,b) => { const d = new Date(b.price_date).getTime() - new Date(a.price_date).getTime(); return d === 0 ? b.id - a.id : d; })" :key="mp.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-900/50 transition-colors">
                        <td class="px-3 py-2">{{ formatDisplayDate(mp.price_date) }}</td>
                        <td class="px-3 py-2 font-bold text-slate-800 dark:text-slate-100">{{ mp.product?.name }}</td>
                        <td class="px-3 py-2 text-slate-500 dark:text-slate-400">{{ mp.source_name }}</td>
                        <td class="px-3 py-2 font-bold font-mono text-emerald-600 dark:text-emerald-400 text-right">₺{{ mp.unit_price }} / kg</td>
                        <td class="px-3 py-2 text-right">
                            <div class="flex justify-end items-center gap-3">
                                <button @click="emit('edit', mp)" class="text-teal-600 dark:text-teal-400 font-bold hover:underline cursor-pointer">
                                    Düzenle
                                </button>
                                <button @click="emit('delete', mp.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline cursor-pointer">
                                    Sil
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!marketPrices || marketPrices.length === 0">
                        <td colspan="5" class="px-3 py-4 text-center text-slate-500 dark:text-slate-400">Kayıt bulunamadı.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
