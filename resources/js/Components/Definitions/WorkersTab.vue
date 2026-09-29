<script setup lang="ts">
import { ref, computed } from 'vue';

const props = defineProps<{
    workers: any[];
    crewLeaders: any[];
}>();

const emit = defineEmits<{
    (e: 'create'): void;
    (e: 'edit', worker: any): void;
    (e: 'delete', id: number): void;
    (e: 'openCrewLeaderWorkers', crewLeader: any): void;
}>();

const workerSearchQuery = ref('');
const workerCrewLeaderFilter = ref<string>('all');
const workerPerformanceFilter = ref<string>('all');
const workerStatusFilter = ref<string>('all');

const resetWorkerFilters = () => {
    workerSearchQuery.value = '';
    workerCrewLeaderFilter.value = 'all';
    workerPerformanceFilter.value = 'all';
    workerStatusFilter.value = 'all';
};

const getCrewLeaderWorkers = (crewLeaderId: number) => {
    if (!props.workers) return [];
    return props.workers.filter((w: any) => Number(w.crew_leader_id) === Number(crewLeaderId));
};

const filteredWorkers = computed(() => {
    return (props.workers || []).filter((w: any) => {
        if (workerCrewLeaderFilter.value !== 'all') {
            if (workerCrewLeaderFilter.value === 'independent') {
                if (w.crew_leader_id) return false;
            } else if (String(w.crew_leader_id) !== String(workerCrewLeaderFilter.value)) {
                return false;
            }
        }
        if (workerPerformanceFilter.value !== 'all') {
            if (workerPerformanceFilter.value === '5' && Number(w.performance_rating) !== 5) return false;
            if (workerPerformanceFilter.value === '4plus' && Number(w.performance_rating || 0) < 4) return false;
            if (workerPerformanceFilter.value === '3minus' && Number(w.performance_rating || 0) > 3) return false;
        }
        if (workerStatusFilter.value !== 'all') {
            const isActive = workerStatusFilter.value === 'active';
            if (!!w.is_active !== isActive) return false;
        }
        if (workerSearchQuery.value.trim()) {
            const q = workerSearchQuery.value.trim().toLowerCase();
            const fullName = `${w.first_name || ''} ${w.last_name || ''}`.toLowerCase();
            const tc = (w.identity_number || '').toLowerCase();
            const notes = (w.notes || '').toLowerCase();
            const clName = w.crew_leader ? `${w.crew_leader.first_name || ''} ${w.crew_leader.last_name || ''}`.toLowerCase() : '';
            if (!fullName.includes(q) && !tc.includes(q) && !notes.includes(q) && !clName.includes(q)) return false;
        }
        return true;
    });
});
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">İşçiler & Sicil Havuzu</h2>
                <p class="text-xs text-slate-400">Çavuş ekiplerine bağlı ve bağımsız tarım işçileri, performans puanları ve sicil kayıtları</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="emit('create')" class="bg-rose-600 hover:bg-rose-500 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                    + Yeni İşçi Ekle
                </button>
            </div>
        </div>

        <div class="p-4 bg-slate-50 dark:bg-slate-950/60 rounded-2xl border border-slate-200/70 dark:border-slate-800/80 mb-5 space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="text-xs font-extrabold uppercase text-slate-600 dark:text-slate-300 tracking-wide flex items-center gap-2">
                    <span>Filtreleme & Kategorizasyon</span>
                    <span class="px-2 py-0.5 bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300 rounded-md text-[10px] font-bold">
                        {{ filteredWorkers.length }} / {{ workers?.length || 0 }} İşçi
                    </span>
                </div>
                <button type="button" v-if="workerSearchQuery || workerCrewLeaderFilter !== 'all' || workerPerformanceFilter !== 'all' || workerStatusFilter !== 'all'" @click="resetWorkerFilters" class="text-[11px] font-bold text-rose-600 hover:underline">
                    ✕ Filtreleri Temizle
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 text-xs">
                <div>
                    <label class="block font-bold text-[11px] text-slate-500 mb-1">Arama (İsim / TC / Not)</label>
                    <input v-model="workerSearchQuery" type="text" placeholder="İşçi adı, soyadı, TCKN..." class="w-full border rounded-xl p-2 bg-white dark:bg-slate-900 text-xs focus:ring-2 focus:ring-indigo-500" />
                </div>
                <div>
                    <label class="block font-bold text-[11px] text-slate-500 mb-1">Bağlı Olduğu Çavuş</label>
                    <select v-model="workerCrewLeaderFilter" class="w-full border rounded-xl p-2 bg-white dark:bg-slate-900 text-xs font-medium">
                        <option value="all">Tüm Çavuşlar (Hepsi)</option>
                        <option v-for="cl in crewLeaders" :key="cl.id" :value="String(cl.id)">
                            {{ cl.first_name }} {{ cl.last_name }} ({{ getCrewLeaderWorkers(cl.id).length }} İşçi)
                        </option>
                        <option value="independent">Bağımsız İşçiler</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-[11px] text-slate-500 mb-1">Performans Puanı</label>
                    <select v-model="workerPerformanceFilter" class="w-full border rounded-xl p-2 bg-white dark:bg-slate-900 text-xs font-medium">
                        <option value="all">Tüm Puanlar</option>
                        <option value="5">5 Yıldız (Mükemmel)</option>
                        <option value="4plus">4 ve Üzeri Yıldız</option>
                        <option value="3minus">3 ve Altı (Geliştirilmeli)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-[11px] text-slate-500 mb-1">Çalışma Durumu</label>
                    <select v-model="workerStatusFilter" class="w-full border rounded-xl p-2 bg-white dark:bg-slate-900 text-xs font-medium">
                        <option value="all">Tüm Durumlar</option>
                        <option value="active">Yalnızca Aktifler</option>
                        <option value="passive">Yalnızca Pasifler</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/60 font-bold uppercase border-b">
                        <th class="p-3">İşçi Ad Soyad</th>
                        <th class="p-3">Bağlı Olduğu Çavuş</th>
                        <th class="p-3">TC Kimlik No</th>
                        <th class="p-3">Performans</th>
                        <th class="p-3">Notlar / Sicil</th>
                        <th class="p-3">Durum</th>
                        <th class="p-3 text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="w in filteredWorkers" :key="w.id" class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition">
                        <td class="p-3 font-extrabold text-slate-800 dark:text-slate-100">{{ w.first_name }} {{ w.last_name }}</td>
                        <td class="p-3 font-semibold">
                            <button type="button" v-if="w.crew_leader" @click="emit('openCrewLeaderWorkers', w.crew_leader)" class="px-2 py-0.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:hover:bg-indigo-900 dark:text-indigo-300 font-bold rounded-lg border border-indigo-200 dark:border-indigo-800 text-[11px] transition text-left cursor-pointer">
                                {{ w.crew_leader.first_name }} {{ w.crew_leader.last_name }}
                            </button>
                            <span v-else class="text-slate-400 italic">Bağımsız İşçi</span>
                        </td>
                        <td class="p-3 font-mono text-slate-600 dark:text-slate-400">{{ w.identity_number || '-' }}</td>
                        <td class="p-3 font-bold text-amber-500">
                            <span>Puan: {{ w.performance_rating }}/5</span>
                        </td>
                        <td class="p-3 text-slate-500 dark:text-slate-400 max-w-[220px] truncate">{{ w.notes || '-' }}</td>
                        <td class="p-3">
                            <span :class="w.is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'" class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase">
                                {{ w.is_active ? 'Aktif' : 'Pasif' }}
                            </span>
                        </td>
                        <td class="p-3 text-right space-x-2 shrink-0">
                            <button type="button" @click="emit('edit', w)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                            <button type="button" @click="emit('delete', w.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                        </td>
                    </tr>
                    <tr v-if="filteredWorkers.length === 0">
                        <td colspan="7" class="p-8 text-center text-slate-400 italic">
                            Aranan kriterlere uygun işçi bulunamadı.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
