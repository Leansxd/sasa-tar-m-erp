<script setup lang="ts">
defineProps<{
    sprayApplications: any[];
    sprayRecipes: any[];
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
                <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">İlaçlama Uygulamaları & İlaç Reçete Takibi</h2>
                <p class="text-[11px] text-slate-500 dark:border-slate-400">İlaçlama formülasyonu, uygulama amacı, yapılan alan ve tank devamlılığı</p>
            </div>
            <button @click="$emit('create')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                <span class="text-sm leading-none">+</span> Yeni İlaçlama Uygulaması Ekle
            </button>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 my-3 p-2 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam İlaçlama Kaydı</span>
                <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ sprayApplications.length }} Kayıt</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Tamamlanan Tanklar</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm">{{ sprayApplications.filter(s => s.is_tank_finished).length }} Tank</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Devam Eden Tanklar</span>
                <span class="font-bold text-amber-600 dark:text-amber-400 text-sm">{{ sprayApplications.filter(s => !s.is_tank_finished).length }} Tank</span>
            </div>
            <div class="px-2">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">İlaç Formülasyon Çeşidi</span>
                <span class="font-bold text-slate-700 dark:text-slate-300 text-sm">{{ sprayRecipes.length }} Reçete</span>
            </div>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px]">
                    <tr>
                        <th class="p-2.5">Uygulama Tarihi & Kod</th>
                        <th class="p-2.5">İlaç Reçetesi</th>
                        <th class="p-2.5">Kullanım Amacı / Hedef</th>
                        <th class="p-2.5">Kaplanan Alan</th>
                        <th class="p-2.5">Uygulayan Personel</th>
                        <th class="p-2.5 text-center">Tank Durumu</th>
                        <th class="p-2.5 text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="sa in sprayApplications" :key="sa.id" class="hover:bg-slate-50 dark:hover:bg-slate-950/60 transition">
                        <td class="p-2.5 font-mono">
                            <div class="font-bold text-slate-800 dark:text-slate-100">
                                {{ sa.application_date ? (sa.application_date.includes('T') ? sa.application_date.split('T')[0].split('-').reverse().join('.') : sa.application_date) : '01.09.2026' }}
                            </div>
                            <div class="text-[10px] text-slate-400">{{ sa.batch_code || 'ILAC-2026-01' }}</div>
                        </td>
                        <td class="p-2.5 font-bold text-indigo-600 dark:text-indigo-400">
                            {{ sa.recipe?.name || 'Kırmızı Örümcek & Mantar Koruma Reçetesi' }}
                        </td>
                        <td class="p-2.5 text-slate-700 dark:text-slate-300">
                            {{ sa.purpose || 'Haşere ve Kırmızı Örümcek Mücadelesi' }}
                        </td>
                        <td class="p-2.5 font-semibold text-rose-600 dark:text-rose-400">
                            {{ sa.covered_area_description || 'Sera 1 - 4. Tünele kadar' }}
                        </td>
                        <td class="p-2.5 text-slate-600 dark:text-slate-400">
                            {{ sa.applied_by ? (sa.applied_by.first_name + ' ' + sa.applied_by.last_name) : 'Saha Operatörü' }}
                        </td>
                        <td class="p-2.5 text-center">
                            <span :class="sa.is_tank_finished ? 'bg-emerald-950/80 text-emerald-400 border-emerald-800/80' : 'bg-amber-950/80 text-amber-400 border-amber-800/80'" class="px-2 py-0.5 rounded text-[10px] font-bold border inline-block">
                                {{ sa.is_tank_finished ? 'Tank Tamamlandı' : 'Ertesi Gün Devam' }}
                            </span>
                        </td>
                        <td class="p-2.5 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                <button @click="$emit('edit', sa)" class="text-slate-700 dark:text-slate-200 font-bold hover:underline cursor-pointer">
                                    Düzenle
                                </button>
                                <button @click="$emit('print', sa)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline cursor-pointer">
                                    Çıktı Al
                                </button>
                                <button @click="$emit('delete', sa.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline cursor-pointer">
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
