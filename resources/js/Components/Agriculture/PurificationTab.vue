<script setup lang="ts">
defineProps<{
    purificationControls: any[];
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
                <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">Arıtma Suyu & Barometre Fark Basınç Takibi</h2>
                <p class="text-[11px] text-slate-500 dark:border-slate-400">Giriş/Çıkış basınç farkı (&Delta;P) ve otomatik filtre tıkanıklık uyarısı</p>
            </div>
            <button @click="$emit('create')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                <span class="text-sm leading-none">+</span> Barometre Basınç Kontrolü Gir
            </button>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 my-3 p-2 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Arıtma Ünite Kontrolleri</span>
                <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ purificationControls.length }} Kontrol</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Basınç Farkı Normal</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm">{{ purificationControls.filter(p => !p.has_warning).length }} Ünite Normal</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Filtre Uyarıları</span>
                <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm">{{ purificationControls.filter(p => p.has_warning).length }} Uyarı</span>
            </div>
            <div class="px-2">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Max Eşik Barı (&Delta;P)</span>
                <span class="font-bold font-mono text-slate-700 dark:text-slate-300 text-sm">1.50 bar</span>
            </div>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px]">
                    <tr>
                        <th class="p-2.5">Arıtma Ünitesi / Kuyu</th>
                        <th class="p-2.5">Kontrol Tarihi</th>
                        <th class="p-2.5 text-right">Giriş Basıncı</th>
                        <th class="p-2.5 text-right">Çıkış Basıncı</th>
                        <th class="p-2.5 text-right">Basınç Farkı (&Delta;P)</th>
                        <th class="p-2.5 text-right">Max Eşik Limit</th>
                        <th class="p-2.5 text-center">Filtre & Basınç Durumu</th>
                        <th class="p-2.5 text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="pc in purificationControls" :key="pc.id" class="hover:bg-slate-50 dark:hover:bg-slate-950/60 transition">
                        <td class="p-2.5 font-bold text-slate-800 dark:text-slate-100">
                            {{ pc.water_source?.name || 'Silifke 1 No\'lu Derin Kuyu Sondaj Pompası' }}
                        </td>
                        <td class="p-2.5 text-slate-500 font-mono">
                            {{ pc.control_date ? (pc.control_date.includes('T') ? pc.control_date.split('T')[0].split('-').reverse().join('.') : pc.control_date) : '20.08.2026' }}
                        </td>
                        <td class="p-2.5 text-right font-mono font-semibold">{{ pc.inlet_pressure_bar }} bar</td>
                        <td class="p-2.5 text-right font-mono font-semibold">{{ pc.outlet_pressure_bar }} bar</td>
                        <td class="p-2.5 text-right font-mono font-bold" :class="pc.has_warning ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'">
                            {{ pc.delta_pressure_bar }} bar
                        </td>
                        <td class="p-2.5 text-right font-mono text-slate-500">{{ pc.max_threshold_bar || '1.50' }} bar</td>
                        <td class="p-2.5 text-center">
                            <span :class="pc.has_warning ? 'bg-rose-950/80 text-rose-400 border-rose-800/80' : 'bg-emerald-950/80 text-emerald-400 border-emerald-800/80'" class="px-2 py-0.5 rounded text-[10px] font-bold border inline-block">
                                {{ pc.has_warning ? 'UYARI: Filtre Doldu!' : 'Basınç Farkı Normal' }}
                            </span>
                        </td>
                        <td class="p-2.5 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                <button @click="$emit('edit', pc)" class="text-slate-700 dark:text-slate-200 font-bold hover:underline cursor-pointer">
                                    Düzenle
                                </button>
                                <button @click="$emit('print', pc)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline cursor-pointer">
                                    Çıktı Al
                                </button>
                                <button @click="$emit('delete', pc.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline cursor-pointer">
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
