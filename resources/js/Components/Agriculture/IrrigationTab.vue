<script setup lang="ts">
defineProps<{
    irrigationSchedules: any[];
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
                <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">Sulama Programı & Vana Süre Matrisi</h2>
                <p class="text-[11px] text-slate-500 dark:border-slate-400">Gübreli / Gübresiz sulama seçimi ve hızlı eşit vana süresi eşitleme</p>
            </div>
            <button @click="$emit('create')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                <span class="text-sm leading-none">+</span> Yeni Sulama Programı Ekle
            </button>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 my-3 p-2 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam Sulama Programı</span>
                <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ irrigationSchedules.length }} Program</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Gübre Kademeli Sulama</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm">{{ irrigationSchedules.filter(i => i.is_fertilized).length }} Kayıt</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Boş Sulama (Sadece Su)</span>
                <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm">{{ irrigationSchedules.filter(i => !i.is_fertilized).length }} Kayıt</span>
            </div>
            <div class="px-2">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Aktif Sulama Vanaları</span>
                <span class="font-bold text-slate-700 dark:text-slate-300 text-sm">4 Vana Faal</span>
            </div>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px]">
                    <tr>
                        <th class="p-2.5">Tesis / Lokasyon</th>
                        <th class="p-2.5">Program Tarihi</th>
                        <th class="p-2.5">Başlangıç Saati</th>
                        <th class="p-2.5">Sulama Tipi</th>
                        <th class="p-2.5">Aktif Vanalar & Süreler</th>
                        <th class="p-2.5">Tank / Saha Notu</th>
                        <th class="p-2.5 text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="isItem in irrigationSchedules" :key="isItem.id" class="hover:bg-slate-50 dark:hover:bg-slate-950/60 transition">
                        <td class="p-2.5 font-bold text-slate-800 dark:text-slate-100">
                            {{ isItem.production_location?.name || isItem.location?.name || 'Silifke Çilek Üretim Serası' }}
                        </td>
                        <td class="p-2.5 font-mono text-slate-500">
                            {{ isItem.schedule_date ? (isItem.schedule_date.includes('T') ? isItem.schedule_date.split('T')[0].split('-').reverse().join('.') : isItem.schedule_date) : '01.09.2026' }}
                        </td>
                        <td class="p-2.5 font-mono font-semibold text-slate-700 dark:text-slate-300">
                            {{ isItem.start_time || '09:00' }} (Sıra {{ isItem.run_number || 1 }})
                        </td>
                        <td class="p-2.5">
                            <span :class="isItem.is_fertilized ? 'bg-emerald-950/80 text-emerald-400 border-emerald-800/80' : 'bg-slate-800 text-slate-300 border-slate-700'" class="px-2 py-0.5 rounded text-[10px] font-bold border inline-block">
                                {{ isItem.is_fertilized ? 'Gübreli Sulama' : 'Boş Sulama' }}
                            </span>
                        </td>
                        <td class="p-2.5">
                            <div class="flex flex-wrap gap-1">
                                <span v-for="v in (isItem.valves || [])" :key="v.id" class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded font-mono text-[10px]">
                                    {{ v.name || ('V-' + v.valve_number) }}: {{ v.duration_minutes }}dk
                                </span>
                                <span v-if="!isItem.valves || isItem.valves.length === 0" class="text-slate-400 italic text-[11px]">4 Vana x 5dk</span>
                            </div>
                        </td>
                        <td class="p-2.5 text-slate-600 dark:text-slate-400 max-w-[220px] truncate">
                            {{ isItem.tank_stage_note || isItem.notes || '-' }}
                        </td>
                        <td class="p-2.5 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                <button @click="$emit('edit', isItem)" class="text-slate-700 dark:text-slate-200 font-bold hover:underline cursor-pointer">
                                    Düzenle
                                </button>
                                <button @click="$emit('print', isItem)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline cursor-pointer">
                                    Çıktı Al
                                </button>
                                <button @click="$emit('delete', isItem.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline cursor-pointer">
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
