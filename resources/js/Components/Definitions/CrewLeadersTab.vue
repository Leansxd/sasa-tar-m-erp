<script setup lang="ts">
const props = defineProps<{
    crewLeaders: any[];
    workers: any[];
}>();

const emit = defineEmits<{
    (e: 'create'): void;
    (e: 'edit', crewLeader: any): void;
    (e: 'delete', id: number): void;
    (e: 'openWorkers', crewLeader: any): void;
}>();

const getCrewLeaderWorkers = (crewLeaderId: number) => {
    if (!props.workers) return [];
    return props.workers.filter((w: any) => Number(w.crew_leader_id) === Number(crewLeaderId));
};
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6">
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Çavuşlar (Ekip Başları)</h2>
                <p class="text-xs text-slate-400">Yevmiye, çavuş çarpanı, araç başı yol tazminatı ve çalışma şartları</p>
            </div>
            <button type="button" @click="emit('create')" class="bg-rose-600 hover:bg-rose-500 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                + Yeni Çavuş Ekle
            </button>
        </div>
        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/60 font-bold uppercase border-b border-slate-100 dark:border-slate-800 text-slate-500 dark:text-slate-400">
                        <th class="p-3">Çavuş Ad Soyad</th>
                        <th class="p-3">Geldiği İl / İletişim</th>
                        <th class="p-3">Yevmiye & Çarpan</th>
                        <th class="p-3">Araç & Nakliye Şartı</th>
                        <th class="p-3">Yemek Durumu</th>
                        <th class="p-3">Bağlı İşçiler</th>
                        <th class="p-3 text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    <tr v-for="cl in crewLeaders" :key="cl.id" class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition">
                        <td class="p-3">
                            <div class="font-extrabold text-slate-800 dark:text-slate-100">{{ cl.first_name }} {{ cl.last_name }}</div>
                            <div class="text-[11px] text-slate-400 font-mono">TCKN: {{ cl.identity_number || '-' }}</div>
                        </td>
                        <td class="p-3">
                            <div class="font-semibold text-slate-700 dark:text-slate-300">{{ cl.origin_city || 'Şehir Belirtilmedi' }}</div>
                            <div class="text-[11px] text-slate-400 font-mono">{{ cl.phone || '-' }}</div>
                        </td>
                        <td class="p-3">
                            <div class="font-extrabold text-emerald-600 dark:text-emerald-400">₺{{ cl.daily_wage }} / gün</div>
                            <div class="text-[11px] text-slate-400 font-bold">Çarpan: {{ cl.multiplier }}x {{ cl.is_leader_fee_included ? '(Yevmiyeye Dahil)' : '(Ayrı)' }}</div>
                        </td>
                        <td class="p-3">
                            <div class="font-bold text-slate-700 dark:text-slate-300">Min {{ cl.min_car_requirement }} Araç</div>
                            <div class="text-[11px] text-rose-600 dark:text-rose-400 font-bold">₺{{ cl.travel_fee_per_car }} / araç</div>
                        </td>
                        <td class="p-3 font-semibold text-slate-700 dark:text-slate-300">
                            <span :class="cl.is_food_included ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 border-emerald-200 dark:border-emerald-900' : 'text-slate-500 bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700'" class="px-2 py-0.5 rounded-lg text-[10px] font-bold border">
                                {{ cl.is_food_included ? 'Yemek Dahil' : 'Yemek Hariç' }}
                            </span>
                        </td>
                        <td class="p-3">
                            <button type="button" @click="emit('openWorkers', cl)" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-indigo-50 text-slate-700 hover:text-indigo-600 dark:bg-slate-800/90 dark:hover:bg-indigo-950/60 dark:text-slate-300 dark:hover:text-indigo-300 border border-slate-200/80 dark:border-slate-700/80 hover:border-indigo-300 dark:hover:border-indigo-800 transition-all cursor-pointer group shadow-2xs">
                                <span class="w-2 h-2 rounded-full shrink-0" :class="getCrewLeaderWorkers(cl.id).length > 0 ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                                <span class="font-black text-slate-900 dark:text-white">{{ getCrewLeaderWorkers(cl.id).length }}</span>
                                <span class="text-slate-500 dark:text-slate-400 text-[11px]">İşçi</span>
                                <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-indigo-500 dark:group-hover:text-indigo-400 transition-transform group-hover:translate-x-0.5 ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </td>
                        <td class="p-3 text-right space-x-2 shrink-0">
                            <button type="button" @click="emit('openWorkers', cl)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">İşçiler</button>
                            <button type="button" @click="emit('edit', cl)" class="text-slate-600 dark:text-slate-400 font-bold hover:underline">Düzenle</button>
                            <button type="button" @click="emit('delete', cl.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
