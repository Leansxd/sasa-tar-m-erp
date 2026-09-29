<script setup lang="ts">
const props = defineProps<{
    show: boolean;
    crewLeader: any;
    workers: any[];
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'addWorker', crewLeader: any): void;
    (e: 'editWorker', worker: any): void;
    (e: 'deleteWorker', id: number): void;
}>();

const getCrewLeaderWorkers = (crewLeaderId: number) => {
    if (!props.workers) return [];
    return props.workers.filter((w: any) => Number(w.crew_leader_id) === Number(crewLeaderId));
};
</script>

<template>
    <Transition name="modal">
        <div v-if="show && crewLeader" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl w-full max-w-3xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="p-6 bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white flex justify-between items-start">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-500 text-white">Çavuş Ekip Kadrosu</span>
                            <span class="text-xs text-slate-300 font-bold">{{ crewLeader.origin_city }}</span>
                        </div>
                        <h3 class="text-xl font-black mt-1 text-white">
                            {{ crewLeader.first_name }} {{ crewLeader.last_name }}
                        </h3>
                        <div class="flex flex-wrap items-center gap-3 mt-2 text-xs text-slate-300 font-medium">
                            <span class="flex items-center gap-1">{{ crewLeader.phone || '-' }}</span>
                            <span>•</span>
                            <span>TCKN: <span class="font-mono text-slate-200">{{ crewLeader.identity_number || '-' }}</span></span>
                            <span>•</span>
                            <span class="text-emerald-400 font-extrabold">₺{{ crewLeader.daily_wage }}/gün ({{ crewLeader.multiplier }}x)</span>
                            <span>•</span>
                            <span class="text-amber-300 font-bold">Min {{ crewLeader.min_car_requirement }} Araç (₺{{ crewLeader.travel_fee_per_car }})</span>
                        </div>
                    </div>
                    <button type="button" @click="emit('close')" class="text-slate-400 hover:text-white text-2xl font-bold p-1 leading-none cursor-pointer">&times;</button>
                </div>

                <div class="p-6 max-h-[60vh] overflow-y-auto space-y-4">
                    <div class="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-slate-800">
                        <div class="text-xs text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wide flex items-center gap-2">
                            <span>Kayıtlı Ekip İşçileri</span>
                            <span class="px-2.5 py-0.5 bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 rounded-full font-extrabold text-[11px] border border-indigo-200 dark:border-indigo-800">
                                {{ getCrewLeaderWorkers(crewLeader.id).length }} İşçi
                            </span>
                        </div>
                        <button type="button" @click="emit('addWorker', crewLeader)" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1 cursor-pointer">
                            + Bu Çavuşa İşçi Ekle
                        </button>
                    </div>

                    <div v-if="getCrewLeaderWorkers(crewLeader.id).length > 0" class="overflow-x-auto rounded-2xl border border-slate-200/80 dark:border-slate-800">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-950/60 font-bold text-slate-500 uppercase border-b border-slate-200/80 dark:border-slate-800">
                                    <th class="p-3">İşçi Ad Soyad</th>
                                    <th class="p-3">TC Kimlik No</th>
                                    <th class="p-3">Performans</th>
                                    <th class="p-3">Sicil / Notlar</th>
                                    <th class="p-3">Durum</th>
                                    <th class="p-3 text-right">İşlem</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <tr v-for="w in getCrewLeaderWorkers(crewLeader.id)" :key="w.id" class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                                    <td class="p-3 font-extrabold text-slate-800 dark:text-slate-100">
                                        {{ w.first_name }} {{ w.last_name }}
                                    </td>
                                    <td class="p-3 font-mono text-slate-600 dark:text-slate-400">
                                        {{ w.identity_number || '-' }}
                                    </td>
                                    <td class="p-3">
                                        <span class="font-bold text-amber-500">★ {{ w.performance_rating }}/5</span>
                                    </td>
                                    <td class="p-3 text-slate-500 dark:text-slate-400 max-w-[200px] truncate">
                                        {{ w.notes || '-' }}
                                    </td>
                                    <td class="p-3">
                                        <span :class="w.is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'" class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase">
                                            {{ w.is_active ? 'Aktif' : 'Pasif' }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-right space-x-2 shrink-0">
                                        <button type="button" @click="emit('editWorker', w)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                                        <button type="button" @click="emit('deleteWorker', w.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="text-center py-10 bg-slate-50 dark:bg-slate-950/40 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800">
                        <p class="text-xs font-bold text-slate-700 dark:text-slate-200">Bu çavuşa kayıtlı işçi bulunmuyor</p>
                        <p class="text-[11px] text-slate-400 mt-1">Yukarıdaki "+ Bu Çavuşa İşçi Ekle" butonunu kullanarak ekip işçilerini sisteme dahil edebilirsiniz.</p>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 dark:bg-slate-950 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                    <button type="button" @click="emit('close')" class="px-5 py-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 rounded-xl text-xs font-bold transition cursor-pointer">
                        Kapat
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
