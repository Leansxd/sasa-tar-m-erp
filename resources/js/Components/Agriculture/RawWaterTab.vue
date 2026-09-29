<script setup lang="ts">
defineProps<{
    rawWaterControls: any[];
}>();

defineEmits<{
    (e: 'create'): void;
    (e: 'edit', item: any): void;
    (e: 'print', item: any): void;
    (e: 'delete', id: number): void;
}>();
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 space-y-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">Kaynak Suyu, Depo & Filtre Kontrolleri</h2>
                <p class="text-[11px] text-slate-500 dark:border-slate-400">Pompa durumu, filtre temizlik periyodu uyarısı, klor ve ham su deposu seviyeleri</p>
            </div>
            <button @click="$emit('create')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                <span class="text-sm leading-none">+</span> Günlük Kaynak Suyu Kontrolü Gir
            </button>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 my-3 p-2 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Kaynak Suyu Kontrolleri</span>
                <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ rawWaterControls.length }} Kayıt</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Faal Derin Kuyu Pompaları</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm">{{ rawWaterControls.filter(r => r.pump_status === 'open').length }} Pompa Açık</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Temizlenen Filtreler</span>
                <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm">{{ rawWaterControls.filter(r => r.is_filter_cleaned).length }} Filtre Onaylı</span>
            </div>
            <div class="px-2">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Klor Dozaj Modu</span>
                <span class="font-bold text-slate-700 dark:text-slate-300 text-sm">Otomatik Dozaj</span>
            </div>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px]">
                    <tr>
                        <th class="p-2.5">Kaynak / Pompa Adı</th>
                        <th class="p-2.5">Kontrol Tarihi</th>
                        <th class="p-2.5">Ham Su Deposu</th>
                        <th class="p-2.5">Klor Tankı</th>
                        <th class="p-2.5">Dozaj Pompası</th>
                        <th class="p-2.5">Ölçülen Değerler</th>
                        <th class="p-2.5 text-center">Filtre Durumu</th>
                        <th class="p-2.5 text-center">Pompa Durumu</th>
                        <th class="p-2.5 text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="rw in rawWaterControls" :key="rw.id" class="hover:bg-slate-50 dark:hover:bg-slate-950/60 transition">
                        <td class="p-2.5 font-bold text-slate-800 dark:text-slate-100">
                            {{ rw.water_source?.name || 'Ana Kuyu Suyu Kaynağı' }}
                        </td>
                        <td class="p-2.5 text-slate-500 font-mono">
                            {{ rw.control_date ? (rw.control_date.includes('T') ? rw.control_date.split('T')[0].split('-').reverse().join('.') : rw.control_date) : '01.09.2026' }}
                        </td>
                        <td class="p-2.5 font-semibold text-slate-700 dark:text-slate-300">
                            <div>
                                <span>{{ rw.water_tank_level === 'full' ? 'Dolu (%100)' : (rw.water_tank_level === 'half_plus' ? 'Yarıdan Fazla (%75)' : (rw.water_tank_level === 'half_minus' ? 'Yarıdan Az (%35)' : 'Boş (%0)')) }}</span>
                                <div v-if="rw.water_tank_note" class="text-[10px] text-slate-400 font-normal truncate max-w-[130px]">{{ rw.water_tank_note }}</div>
                            </div>
                        </td>
                        <td class="p-2.5 text-slate-700 dark:text-slate-300">
                            {{ rw.chlorine_tank_level === 'full' ? 'Dolu (%100)' : (rw.chlorine_tank_level === 'half_plus' ? 'Yarıdan Fazla (%75)' : (rw.chlorine_tank_level === 'half_minus' ? 'Yarıdan Az (%35)' : 'Boş (%0)')) }}
                        </td>
                        <td class="p-2.5 font-semibold text-slate-700 dark:text-slate-300">
                            <div>
                                <span>{{ rw.dosing_pump_mode === 'auto' ? 'Otomatik Dozaj' : (rw.dosing_pump_mode === 'manual' ? 'Manuel Mod' : 'Arızalı') }}</span>
                                <div v-if="rw.dosing_pump_mode === 'manual' && rw.dosing_pump_manual_val" class="text-[10px] text-amber-500 font-mono">{{ rw.dosing_pump_manual_val }}</div>
                                <div v-if="rw.dosing_pump_mode === 'faulty' && rw.dosing_pump_fault_note" class="text-[10px] text-rose-500 font-mono">{{ rw.dosing_pump_fault_note }}</div>
                            </div>
                        </td>
                        <td class="p-2.5 font-mono text-slate-800 dark:text-slate-200">
                            EC: {{ rw.ec_val || '1.2' }} | pH: {{ rw.ph_val || '6.8' }}
                        </td>
                        <td class="p-2.5 text-center">
                            <span :class="rw.is_filter_cleaned ? 'bg-emerald-950/80 text-emerald-400 border-emerald-800/80' : 'bg-amber-950/80 text-amber-400 border-amber-800/80'" class="px-2 py-0.5 rounded text-[10px] font-bold border inline-block">
                                {{ rw.is_filter_cleaned ? 'Filtre Temizlendi' : 'Filtre Temizliği Bekliyor' }}
                            </span>
                        </td>
                        <td class="p-2.5 text-center">
                            <div class="flex flex-col items-center gap-0.5">
                                <span :class="rw.pump_status === 'open' ? 'bg-emerald-950/80 text-emerald-400 border-emerald-800/80' : (rw.pump_status === 'closed' ? 'bg-slate-800 text-slate-300 border-slate-700' : 'bg-rose-950/80 text-rose-400 border-rose-800/80')" class="px-2 py-0.5 rounded text-[10px] font-bold border inline-block">
                                    {{ rw.pump_status === 'open' ? 'Açık (Faal)' : (rw.pump_status === 'closed' ? 'Kapalı' : 'Arızalı') }}
                                </span>
                                <span v-if="rw.pump_status === 'faulty' && rw.pump_fault_note" class="text-[9px] text-rose-400 font-medium truncate max-w-[100px]" :title="rw.pump_fault_note">
                                    {{ rw.pump_fault_note }}
                                </span>
                            </div>
                        </td>
                        <td class="p-2.5 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                <button @click="$emit('edit', rw)" class="text-slate-700 dark:text-slate-200 font-bold hover:underline cursor-pointer">
                                    Düzenle
                                </button>
                                <button @click="$emit('print', rw)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline cursor-pointer">
                                    Çıktı Al
                                </button>
                                <button @click="$emit('delete', rw.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline cursor-pointer">
                                    Sil
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
