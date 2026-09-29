<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    dailyWorkSheets: any[];
    crewLeaders: any[];
    totalHarvestQuantity: number;
}>();

const emit = defineEmits<{
    (e: 'print', elementId: string): void;
}>();

const totalCrewWages = computed(() => {
    let total = 0;
    (props.dailyWorkSheets || []).forEach((sheet: any) => {
        sheet.crew_leaders?.forEach((cl: any) => {
            total += parseFloat(cl.calculated_wage_total || 0);
        });
    });
    return total;
});

const groupedCrewLeaderCosts = computed(() => {
    const map = new Map<number, {
        id: number;
        name: string;
        code: string;
        total_days: number;
        total_workers: number;
        total_cars: number;
        total_overtime: number;
        total_meal_fee: number;
        total_travel_fee: number;
        total_wage: number;
    }>();

    (props.dailyWorkSheets || []).forEach((sheet: any) => {
        sheet.crew_leaders?.forEach((cl: any) => {
            const leaderId = cl.crew_leader_id;
            if (!leaderId) return;
            const name = cl.crew_leader ? `${cl.crew_leader.first_name} ${cl.crew_leader.last_name}` : 'Çavuş';
            const code = cl.dia_cari_code || cl.crew_leader?.dia_cari_code || 'CH-001';

            if (!map.has(leaderId)) {
                map.set(leaderId, {
                    id: leaderId,
                    name,
                    code,
                    total_days: 0,
                    total_workers: 0,
                    total_cars: 0,
                    total_overtime: 0,
                    total_meal_fee: 0,
                    total_travel_fee: 0,
                    total_wage: 0,
                });
            }

            const item = map.get(leaderId)!;
            item.total_days += 1;
            item.total_workers += parseInt(cl.worker_count || 0);
            item.total_cars += parseInt(cl.car_count || 0);
            item.total_overtime += parseFloat(cl.overtime_hours || 0);
            item.total_meal_fee += parseFloat(cl.meal_fee || 0);
            item.total_travel_fee += parseFloat(cl.travel_fee || 0);
            item.total_wage += parseFloat(cl.calculated_wage_total || 0);
        });
    });

    return Array.from(map.values());
});
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 space-y-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">
                    İşçi & Çavuş Kümülatif Maliyet Analiz Raporu
                </h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Çavuş bazında gruplanmış kümülatif hakediş dökümleri, yevmiyeler, araç/ulaşım giderleri ve kg başı maliyet</p>
            </div>
            <div class="flex items-center gap-2 no-print">
                <button @click="emit('print', 'isci-maliyet-report-content')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                    Raporu Yazdır
                </button>
            </div>
        </div>

        <div id="isci-maliyet-report-content" class="space-y-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 p-2.5 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
                <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                    <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam İşçilik & Çavuş Hakedişi</span>
                    <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">₺{{ totalCrewWages.toLocaleString('tr-TR') }}</span>
                </div>
                <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                    <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Kg Başı İşçilik Maliyeti</span>
                    <span class="font-bold font-mono text-emerald-600 dark:text-emerald-400 text-sm">₺{{ (totalHarvestQuantity > 0 ? (totalCrewWages / totalHarvestQuantity) : 0).toFixed(2) }} / kg</span>
                </div>
                <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                    <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam Sahada Çalışan İşçi</span>
                    <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm">{{ dailyWorkSheets.reduce((sum, s) => sum + (s.crew_leaders?.reduce((cSum: number, cl: any) => cSum + parseInt(cl.worker_count || 0), 0) || 0), 0) }} Kişi</span>
                </div>
                <div class="px-2">
                    <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Kayıtlı Çavuş Sayısı</span>
                    <span class="font-bold text-slate-700 dark:text-slate-300 text-sm">{{ (crewLeaders || []).length }} Çavuş</span>
                </div>
            </div>

            <div class="space-y-3">
                <h3 class="font-bold text-sm text-slate-800 dark:text-slate-100">Çavuş Bazında Kümülatif Hakediş ve Yevmiye Tablosu</h3>
                <div class="overflow-x-auto border border-slate-200/80 dark:border-slate-800 rounded-xl">
                    <table class="w-full text-left text-xs text-slate-600 dark:text-slate-400">
                        <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px]">
                            <tr>
                                <th class="p-3">ÇAVUŞ ADI</th>
                                <th class="p-3">CARİ KODU</th>
                                <th class="p-3">TOPLAM ÇALIŞTIĞI GÜN</th>
                                <th class="p-3">GETİRDİĞİ TOPLAM İŞÇİ</th>
                                <th class="p-3">SERVİS ARAÇ SAYISI</th>
                                <th class="p-3">TOPLAM MESAİ (SAAT)</th>
                                <th class="p-3">TOPLAM YEMEK BEDELİ (₺)</th>
                                <th class="p-3">TOPLAM ULAŞIM BEDELİ (₺)</th>
                                <th class="p-3">KÜMÜLATİF ÇAVUŞ HAKEDİŞİ (₺)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="cl in groupedCrewLeaderCosts" :key="cl.id" class="hover:bg-slate-50 dark:hover:bg-slate-950/60 transition">
                                <td class="p-3 font-bold text-indigo-600 dark:text-indigo-400 text-sm flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-indigo-400 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                    <span>{{ cl.name }}</span>
                                </td>
                                <td class="p-3 font-semibold text-rose-600 dark:text-rose-400">{{ cl.code }}</td>
                                <td class="p-3 font-bold text-slate-800 dark:text-slate-200">{{ cl.total_days }} Gün</td>
                                <td class="p-3 font-bold text-slate-800 dark:text-slate-200">{{ cl.total_workers }} İşçi / Gün</td>
                                <td class="p-3 text-slate-600 dark:text-slate-300">{{ cl.total_cars }} Araç</td>
                                <td class="p-3 font-bold text-amber-600 dark:text-amber-400">{{ cl.total_overtime }} Saat</td>
                                <td class="p-3 text-slate-600 dark:text-slate-300">₺{{ cl.total_meal_fee.toLocaleString('tr-TR') }}</td>
                                <td class="p-3 text-slate-600 dark:text-slate-300">₺{{ cl.total_travel_fee.toLocaleString('tr-TR') }}</td>
                                <td class="p-3 font-black text-emerald-600 dark:text-emerald-400 text-sm font-mono">₺{{ cl.total_wage.toLocaleString('tr-TR') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
