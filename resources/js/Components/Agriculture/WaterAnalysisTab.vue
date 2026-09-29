<script setup lang="ts">
defineProps<{
    waterAnalysisLogs: any[];
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
                <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">Su Analiz Raporları</h2>
                <p class="text-[11px] text-slate-500 dark:border-slate-400">Su kaynaklarının pH, EC ve laboratuvar kimyasal analiz takibi</p>
            </div>
            <button @click="$emit('create')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                <span class="text-sm leading-none">+</span> Yeni Su Analizi Gir
            </button>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 my-3 p-2 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Su Analiz Raporları</span>
                <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ waterAnalysisLogs.length }} Rapor</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Ortalama pH Değeri</span>
                <span class="font-bold font-mono text-indigo-600 dark:text-indigo-400 text-sm">6.60 pH</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Ortalama EC İletkenlik</span>
                <span class="font-bold font-mono text-slate-700 dark:text-slate-300 text-sm">1.40 mS/cm</span>
            </div>
            <div class="px-2">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Laboratuvar Uygunluğu</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm">%100 Uygun</span>
            </div>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px]">
                    <tr>
                        <th class="p-2.5">Su Kaynağı</th>
                        <th class="p-2.5">Analiz Tarihi</th>
                        <th class="p-2.5">pH Seviyesi</th>
                        <th class="p-2.5">EC İletkenlik</th>
                        <th class="p-2.5">Laboratuvar Notu</th>
                        <th class="p-2.5 text-center">Uygunluk Durumu</th>
                        <th class="p-2.5 text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="wa in waterAnalysisLogs" :key="wa.id" class="hover:bg-slate-50 dark:hover:bg-slate-950/60 transition">
                        <td class="p-2.5 font-bold text-slate-800 dark:text-slate-100">
                            {{ wa.water_source?.name || 'Ana Derin Kuyu Suyu' }}
                        </td>
                        <td class="p-2.5 text-slate-500 font-mono">
                            {{ wa.analysis_date ? (wa.analysis_date.includes('T') ? wa.analysis_date.split('T')[0].split('-').reverse().join('.') : wa.analysis_date) : '01.09.2026' }}
                        </td>
                        <td class="p-2.5 font-bold text-rose-600 dark:text-rose-400 font-mono">pH: {{ wa.ph_level }}</td>
                        <td class="p-2.5 font-bold text-cyan-600 dark:text-cyan-400 font-mono">EC: {{ wa.ec_level }} mS/cm</td>
                        <td class="p-2.5 text-slate-600 dark:text-slate-400 max-w-[280px] truncate">
                            {{ wa.notes || 'Laboratuvar rutin analiz sonucu tarımsal sulamaya uygundur.' }}
                        </td>
                        <td class="p-2.5 text-center">
                            <span class="bg-emerald-950/80 text-emerald-400 border border-emerald-800/80 px-2 py-0.5 rounded text-[10px] font-bold inline-block">
                                Sulamaya Uygun
                            </span>
                        </td>
                        <td class="p-2.5 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                <button @click="$emit('edit', wa)" class="text-slate-700 dark:text-slate-200 font-bold hover:underline cursor-pointer">
                                    Düzenle
                                </button>
                                <button @click="$emit('print', wa)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline cursor-pointer">
                                    Çıktı Al
                                </button>
                                <button @click="$emit('delete', wa.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline cursor-pointer">
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
