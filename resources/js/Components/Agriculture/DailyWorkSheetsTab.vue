<script setup lang="ts">
import { ref, computed } from 'vue';

const props = defineProps<{
    dailyWorkSheets: any[];
    locations: any[];
}>();

const emit = defineEmits<{
    (e: 'create'): void;
    (e: 'detail', sheet: any): void;
    (e: 'print-crew', payload: { sheet: any; crewLeader: any }): void;
    (e: 'delete', id: number): void;
    (e: 'export-excel'): void;
}>();

const searchDailyWorkSheet = ref('');
const selectedLocationFilter = ref('all');

const formatSheetDate = (dateVal: any) => {
    if (!dateVal) return '-';
    const s = String(dateVal).split('T')[0];
    const parts = s.split('-');
    if (parts.length === 3) {
        return `${parts[2]}.${parts[1]}.${parts[0]}`;
    }
    return s;
};

const filteredDailyWorkSheets = computed(() => {
    return (props.dailyWorkSheets || []).filter((dws: any) => {
        if (selectedLocationFilter.value !== 'all' && String(dws.production_location_id) !== String(selectedLocationFilter.value)) {
            return false;
        }
        if (!searchDailyWorkSheet.value.trim()) return true;
        const q = searchDailyWorkSheet.value.toLowerCase();
        const dateStr = formatSheetDate(dws.work_date).toLowerCase();
        const locName = (dws.production_location?.name || '').toLowerCase();
        const crewNames = (dws.crew_leaders || []).map((cl: any) => `${cl.crew_leader?.first_name} ${cl.crew_leader?.last_name}`).join(' ').toLowerCase();
        const productNames = (dws.harvest_items || []).map((h: any) => h.product?.name || '').join(' ').toLowerCase();
        return dateStr.includes(q) || locName.includes(q) || crewNames.includes(q) || productNames.includes(q);
    });
});
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 space-y-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">
                    Günlük İşçi & Hasat Kayıtları
                </h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Saha hasatları, çavuş puantajları ve hakediş bordroları</p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="emit('export-excel')" title="Tüm puantaj kayıtlarını Excel CSV olarak indir" class="bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:hover:bg-emerald-900 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs cursor-pointer">
                    Excel / CSV Aktar
                </button>
                <button @click="emit('create')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                    <span class="text-sm leading-none">+</span> Yeni Form Ekle
                </button>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 p-2.5 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam Puantaj Kaydı</span>
                <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ dailyWorkSheets.length }} Form</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Onaylanan Kayıtlar</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm">{{ dailyWorkSheets.filter(d => d.status === 'approved').length }} Onaylı</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Onay Bekleyenler</span>
                <span class="font-bold text-amber-600 dark:text-amber-400 text-sm">{{ dailyWorkSheets.filter(d => d.status === 'pending' || !d.status).length }} Beklemede</span>
            </div>
            <div class="px-2">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam İşçi Hakedişi</span>
                <span class="font-bold font-mono text-indigo-600 dark:text-indigo-400 text-sm">₺{{ dailyWorkSheets.reduce((sum, d) => sum + (d.crew_leaders?.reduce((s: number, c: any) => s + Number(c.calculated_wage_total || 0), 0) || 0), 0).toLocaleString('tr-TR') }}</span>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-2 mb-2">
            <div class="flex items-center gap-2">
                <select v-model="selectedLocationFilter" class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg px-2.5 py-1 text-xs text-slate-700 dark:text-slate-300 font-semibold focus:outline-none">
                    <option value="all">Tüm Tesisler</option>
                    <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                </select>
            </div>

            <div class="relative w-full sm:w-64">
                <input v-model="searchDailyWorkSheet" type="text" placeholder="Çavuş, ürün veya tesis ara..." class="w-full bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg px-2.5 py-1 text-xs focus:ring-1 focus:ring-slate-500 focus:outline-none" />
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 text-[11px] font-black uppercase tracking-wider text-slate-400 bg-slate-50/50 dark:bg-slate-950/40">
                        <th class="py-3.5 px-5 w-[250px]">Tarih & Tesis</th>
                        <th class="py-3.5 px-4 w-[180px]">Çavuş & İşgücü</th>
                        <th class="py-3.5 px-4 min-w-[220px]">Toplanan Hasat</th>
                        <th class="py-3.5 px-4 w-[160px] text-right">Hakediş / Ciro</th>
                        <th class="py-3.5 px-4 w-[130px] text-center">Durum</th>
                        <th class="py-3.5 px-5 w-[150px] text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    <tr 
                        v-for="dws in filteredDailyWorkSheets" 
                        :key="dws.id" 
                        class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition duration-150 group"
                    >
                        <td class="py-4 px-5">
                            <div class="flex items-center gap-2">
                                <span class="font-black text-slate-800 dark:text-slate-100 text-xs tracking-tight">
                                    {{ formatSheetDate(dws.work_date) }}
                                </span>
                            </div>
                            <div class="font-bold text-slate-700 dark:text-slate-300 text-xs mt-1">
                                {{ dws.production_location?.name }}
                            </div>
                            <div class="text-[11px] text-slate-400 mt-0.5 truncate max-w-[220px]">
                                {{ dws.company?.name || 'SASA Tarım' }} • <span class="text-slate-500">{{ dws.submitted_by ? (dws.submitted_by.first_name + ' ' + dws.submitted_by.last_name) : 'Yönetici' }}</span>
                            </div>
                        </td>

                        <td class="py-4 px-4 align-middle">
                            <div v-for="cl in dws.crew_leaders" :key="cl.id">
                                <div class="font-black text-slate-800 dark:text-slate-200 text-xs">
                                    {{ cl.crew_leader?.first_name }} {{ cl.crew_leader?.last_name }}
                                </div>
                                <div class="text-[11px] text-slate-500 font-medium mt-0.5">
                                    {{ cl.worker_count }} İşçi • {{ cl.car_count }} Araç <span v-if="cl.overtime_hours > 0" class="text-amber-500">• +{{ cl.overtime_hours }}s</span>
                                </div>
                            </div>
                            <div v-if="!dws.crew_leaders || dws.crew_leaders.length === 0" class="text-slate-400 text-xs italic">
                                İşçi kaydı yok
                            </div>
                        </td>

                        <td class="py-4 px-4 align-middle">
                            <div v-for="item in dws.harvest_items" :key="item.id">
                                <div class="font-black text-slate-800 dark:text-slate-200 text-xs">
                                    {{ item.product?.name }} <span v-if="item.product_subtype" class="text-slate-400 font-normal">({{ item.product_subtype?.name }})</span>
                                </div>
                                <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">
                                    {{ Number(item.quantity).toLocaleString('tr-TR') }} {{ item.unit_symbol || 'kg' }} <span class="text-slate-400 font-normal">({{ item.package_count }} Kasa • ₺{{ item.unit_price }}/kg)</span>
                                </div>
                            </div>
                            <div v-if="!dws.harvest_items || dws.harvest_items.length === 0" class="text-slate-400 text-xs italic">
                                Hasat kaydı yok
                            </div>
                        </td>

                        <td class="py-4 px-4 text-right align-middle">
                            <div class="text-[11px] text-slate-400">
                                Hakediş: <strong class="text-slate-700 dark:text-slate-300 font-extrabold">₺{{ Number(dws.crew_leaders?.reduce((sum: number, cl: any) => sum + Number(cl.calculated_wage_total || 0), 0) || 0).toLocaleString('tr-TR') }}</strong>
                            </div>
                            <div class="text-xs font-black text-slate-900 dark:text-slate-100 mt-0.5">
                                Ciro: ₺{{ Number(dws.harvest_items?.reduce((sum: number, h: any) => sum + Number(h.total_revenue || 0), 0) || 0).toLocaleString('tr-TR') }}
                            </div>
                        </td>

                        <td class="py-4 px-4 text-center align-middle">
                            <span v-if="dws.status === 'approved'" class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 font-extrabold px-2.5 py-1 rounded-xl text-[11px] border border-emerald-200 dark:border-emerald-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Onaylandı
                            </span>
                            <span v-else-if="dws.status === 'rejected'" class="inline-flex items-center gap-1.5 bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 font-extrabold px-2.5 py-1 rounded-xl text-[11px] border border-rose-200 dark:border-rose-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Reddedildi
                            </span>
                            <span v-else class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 font-extrabold px-2.5 py-1 rounded-xl text-[11px] border border-amber-200 dark:border-amber-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Onay Bekliyor
                            </span>
                        </td>

                        <td class="py-4 px-5 text-right align-middle">
                            <div class="flex items-center justify-end gap-1.5">
                                <button 
                                    @click="emit('detail', dws)" 
                                    class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 px-2.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1 cursor-pointer"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <span>İncele</span>
                                </button>

                                <button 
                                    v-if="dws.crew_leaders?.[0]" 
                                    @click="emit('print-crew', { sheet: dws, crewLeader: dws.crew_leaders[0] })" 
                                    title="İmzalı Çavuş Hakediş Fişi"
                                    class="bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 dark:hover:bg-rose-900 text-rose-600 dark:text-rose-300 border border-rose-200 dark:border-rose-800 px-2.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1 cursor-pointer"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span>Fiş</span>
                                </button>
                                <button
                                    @click="emit('delete', dws.id)" 
                                    title="Formu kalıcı olarak sil"
                                    class="bg-rose-100 hover:bg-rose-200 dark:bg-rose-950 dark:hover:bg-rose-900 text-rose-700 dark:text-rose-300 border border-rose-300 dark:border-rose-800 px-2.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1 cursor-pointer"
                                >
                                    <span>Sil</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="filteredDailyWorkSheets.length === 0">
                        <td colspan="6" class="p-12 text-center text-slate-400 text-xs">
                            Aramanıza uygun kayıt bulunamadı.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
