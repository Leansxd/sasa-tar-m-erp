<script setup lang="ts">
import { ref, computed } from 'vue';

const props = defineProps<{
    crewLeaders: any[];
}>();

const emit = defineEmits<{
    (e: 'create'): void;
    (e: 'edit', crewLeader: any): void;
    (e: 'delete', id: number): void;
    (e: 'add-worker', crewLeaderId: number): void;
    (e: 'edit-worker', worker: any): void;
    (e: 'delete-worker', id: number): void;
}>();

const crewSearchQuery = ref('');
const crewCityFilter = ref('');
const crewStatusFilter = ref<'all' | 'active' | 'passive'>('all');

const crewCities = computed(() => {
    const set = new Set<string>();
    (props.crewLeaders || []).forEach((cl: any) => {
        if (cl.origin_city && cl.origin_city.trim()) {
            set.add(cl.origin_city.trim());
        }
    });
    return Array.from(set).sort();
});

const totalCrewWorkerCount = computed(() => {
    let count = 0;
    (props.crewLeaders || []).forEach((cl: any) => {
        count += (cl.workers || []).length;
    });
    return count;
});

const filteredCrewLeaders = computed(() => {
    const q = crewSearchQuery.value.trim().toLowerCase();
    const city = crewCityFilter.value;
    const status = crewStatusFilter.value;

    return (props.crewLeaders || []).map((cl: any) => {
        const clName = `${cl.first_name || ''} ${cl.last_name || ''}`.toLowerCase();
        const clCity = (cl.origin_city || '').toLowerCase();
        const clCari = (cl.dia_cari_code || '').toLowerCase();
        const clMatchesSelf = !q || clName.includes(q) || clCity.includes(q) || clCari.includes(q);

        const allWorkers = cl.workers || [];
        const matchingWorkers = allWorkers.filter((w: any) => {
            const wName = `${w.first_name || ''} ${w.last_name || ''}`.toLowerCase();
            const wTc = (w.identity_number || '').toLowerCase();
            const wMatchesSearch = !q || clMatchesSelf || wName.includes(q) || wTc.includes(q);

            let wMatchesStatus = true;
            if (status === 'active') {
                wMatchesStatus = Boolean(w.is_active);
            } else if (status === 'passive') {
                wMatchesStatus = !w.is_active;
            }

            return wMatchesSearch && wMatchesStatus;
        });

        const matchesCity = !city || cl.origin_city === city;
        if (!matchesCity) return null;

        if (q && !clMatchesSelf && matchingWorkers.length === 0) return null;
        if (status !== 'all' && matchingWorkers.length === 0 && allWorkers.length > 0) return null;

        return {
            ...cl,
            displayWorkers: matchingWorkers
        };
    }).filter(Boolean);
});

const resetCrewFilters = () => {
    crewSearchQuery.value = '';
    crewCityFilter.value = '';
    crewStatusFilter.value = 'all';
};
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 space-y-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">İşçi & Çavuş Yönetimi</h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Çavuş (Ekip Başı) ve onlara bağlı işçilerin (Ekip) tanımlamaları, yevmiye ve dia cari eşleşmeleri.</p>
            </div>
            <button @click="emit('create')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                <span class="text-sm leading-none">+</span> Yeni Çavuş Ekle
            </button>
        </div>

        <div class="p-3 bg-slate-50 dark:bg-slate-950/70 border border-slate-200/80 dark:border-slate-800/80 rounded-xl space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                <div class="sm:col-span-5 relative">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input 
                        v-model="crewSearchQuery" 
                        type="text" 
                        placeholder="Çavuş, işçi adı, TC kimlik veya cari kodu..." 
                        class="w-full pl-8 pr-8 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-xs text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 transition"
                    />
                    <button 
                        v-if="crewSearchQuery" 
                        @click="crewSearchQuery = ''" 
                        class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="sm:col-span-4">
                    <select 
                        v-model="crewCityFilter" 
                        class="w-full py-2 px-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500 transition cursor-pointer"
                    >
                        <option value="">Tüm Şehirler / Geldiği Yer</option>
                        <option v-for="c in crewCities" :key="c" :value="c">{{ c }}</option>
                    </select>
                </div>

                <div class="sm:col-span-3">
                    <select 
                        v-model="crewStatusFilter" 
                        class="w-full py-2 px-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500 transition cursor-pointer"
                    >
                        <option value="all">Tüm İşçiler</option>
                        <option value="active">Sadece Aktif İşçiler</option>
                        <option value="passive">Sadece Pasif İşçiler</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-200/50 dark:border-slate-800/50 text-[11px] text-slate-500 dark:text-slate-400">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Toplam Çavuş: <strong class="text-slate-700 dark:text-slate-200">{{ (crewLeaders || []).length }}</strong>
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        Toplam İşçi: <strong class="text-slate-700 dark:text-slate-200">{{ totalCrewWorkerCount }}</strong>
                    </span>
                    <span v-if="crewSearchQuery || crewCityFilter || crewStatusFilter !== 'all'" class="text-amber-600 dark:text-amber-400 font-medium">
                        (Eşleşen: {{ filteredCrewLeaders.length }} Çavuş)
                    </span>
                </div>
                <button 
                    v-if="crewSearchQuery || crewCityFilter || crewStatusFilter !== 'all'" 
                    @click="resetCrewFilters" 
                    class="text-xs text-rose-500 hover:text-rose-600 dark:hover:text-rose-400 font-medium hover:underline cursor-pointer flex items-center gap-1"
                >
                    Filtreleri Temizle
                </button>
            </div>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px]">
                    <tr>
                        <th class="p-2.5">Ad Soyad</th>
                        <th class="p-2.5">Geldiği Yer</th>
                        <th class="p-2.5">Yevmiye</th>
                        <th class="p-2.5">Çavuş Çarpanı</th>
                        <th class="p-2.5">Araç Şartı / Yol Ücreti</th>
                        <th class="p-2.5">Cari Kodu</th>
                        <th class="p-2.5 text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-if="filteredCrewLeaders.length === 0">
                        <td colspan="7" class="p-8 text-center text-slate-400 dark:text-slate-500">
                            <svg class="w-8 h-8 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <p class="font-medium text-xs">Aradığınız kriterlere uygun çavuş veya işçi kaydı bulunamadı.</p>
                        </td>
                    </tr>
                    <template v-for="cl in (filteredCrewLeaders || [])" :key="'cl-' + cl.id">
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-950/60 transition bg-slate-50/30">
                            <td class="p-2.5 font-bold text-slate-800 dark:text-slate-100">
                                {{ cl.first_name }} {{ cl.last_name }}
                                <div class="text-[10px] text-slate-400 font-normal">Kayıtlı İşçi: {{ (cl.workers || []).length }}<span v-if="(cl.displayWorkers || []).length !== (cl.workers || []).length" class="text-emerald-500 font-semibold ml-1">(Filtrelenen: {{ (cl.displayWorkers || []).length }})</span></div>
                            </td>
                            <td class="p-2.5 text-slate-700 dark:text-slate-300">{{ cl.origin_city || '-' }}</td>
                            <td class="p-2.5 font-mono text-slate-700 dark:text-slate-300">₺{{ cl.daily_wage }}</td>
                            <td class="p-2.5 font-mono text-slate-700 dark:text-slate-300">{{ cl.multiplier }}x <span class="text-[10px] text-slate-400">({{ cl.is_leader_fee_included ? 'Dahil' : 'Hariç' }})</span></td>
                            <td class="p-2.5 text-slate-700 dark:text-slate-300">
                                Min {{ cl.min_car_requirement }} Araç<br/>
                                <span class="text-xs">₺{{ cl.travel_fee_per_car }} / Araç</span>
                            </td>
                            <td class="p-2.5 font-mono text-indigo-600 dark:text-indigo-400">{{ cl.dia_cari_code || 'Tanımsız' }}</td>
                            <td class="p-2.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="emit('add-worker', cl.id)" class="text-emerald-600 dark:text-emerald-400 font-bold hover:underline cursor-pointer">
                                        İşçi Ekle
                                    </button>
                                    <button @click="emit('edit', cl)" class="text-slate-700 dark:text-slate-200 font-bold hover:underline cursor-pointer">
                                        Düzenle
                                    </button>
                                    <button @click="emit('delete', cl.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline cursor-pointer">
                                        Sil
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="(cl.displayWorkers || cl.workers || []).length > 0">
                            <td colspan="7" class="p-0 border-0">
                                <div class="pl-8 py-2 bg-slate-50/50 dark:bg-slate-900/50">
                                    <table class="w-full text-left text-[11px] border-collapse bg-white dark:bg-slate-950 rounded-lg shadow-sm border border-slate-200 dark:border-slate-800">
                                        <thead class="bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-400 uppercase font-semibold">
                                            <tr>
                                                <th class="p-2 rounded-tl-lg">İşçi Adı Soyadı</th>
                                                <th class="p-2">TC Kimlik</th>
                                                <th class="p-2">Performans (1-5)</th>
                                                <th class="p-2">Durum</th>
                                                <th class="p-2 text-right rounded-tr-lg">İşlemler</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                            <tr v-for="w in (cl.displayWorkers || cl.workers)" :key="'w-' + w.id" class="hover:bg-slate-50 dark:hover:bg-slate-900 transition">
                                                <td class="p-2 font-medium text-slate-700 dark:text-slate-200">{{ w.first_name }} {{ w.last_name }}</td>
                                                <td class="p-2 font-mono text-slate-500">{{ w.identity_number || '-' }}</td>
                                                <td class="p-2">
                                                    <div class="flex text-amber-400">
                                                        <span v-for="i in 5" :key="'s'+i">{{ i <= w.performance_rating ? '★' : '☆' }}</span>
                                                    </div>
                                                </td>
                                                <td class="p-2">
                                                    <span :class="w.is_active ? 'text-emerald-600' : 'text-slate-400'" class="font-semibold">{{ w.is_active ? 'Aktif' : 'Pasif' }}</span>
                                                </td>
                                                <td class="p-2 text-right">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <button @click="emit('edit-worker', w)" class="text-slate-600 hover:text-slate-900 dark:hover:text-slate-100 hover:underline cursor-pointer">Düzenle</button>
                                                        <button @click="emit('delete-worker', w.id)" class="text-rose-500 hover:text-rose-700 hover:underline cursor-pointer">Sil</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</template>
