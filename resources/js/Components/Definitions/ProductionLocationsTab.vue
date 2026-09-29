<script setup lang="ts">
const props = defineProps<{
    productionLocations: any[];
}>();

const emit = defineEmits<{
    (e: 'create'): void;
    (e: 'edit', location: any): void;
    (e: 'delete', id: number): void;
}>();

const getLocationTypeLabel = (type: string) => {
    switch (type) {
        case 'greenhouse': return 'Sera';
        case 'open_field': return 'Açık Bahçe';
        case 'mixed': return 'Karışık';
        default: return type || 'Tesis';
    }
};
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 space-y-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">Üretim Yeri & Vana Tanımları</h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Bağlı firma, sera/açık bahçe alanları, dekar ve vana matrisi</p>
            </div>
            <button type="button" @click="emit('create')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                <span class="text-sm leading-none">+</span> Yeni Üretim Yeri Ekle
            </button>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 my-3 p-2 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam Tesis Sayısı</span>
                <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ productionLocations.length }} Tesis</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam Dikili Alan</span>
                <span class="font-bold font-mono text-emerald-600 dark:text-emerald-400 text-sm">{{ productionLocations.reduce((acc, l) => acc + parseFloat(l.total_area_dekar || 0), 0) }} da</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Çilek & Muz Seraları</span>
                <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm">{{ productionLocations.filter(l => l.location_type === 'greenhouse').length }} Sera</span>
            </div>
            <div class="px-2">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Tanımlı Otomasyon Vanaları</span>
                <span class="font-bold font-mono text-slate-700 dark:text-slate-300 text-sm">{{ productionLocations.reduce((acc, l) => acc + (l.valves?.length || 0), 0) }} Vana</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/60 font-bold text-slate-500 uppercase border-b">
                        <th class="p-3">Tesis & Sera Adı</th>
                        <th class="p-3">Bağlı Olduğu Firma</th>
                        <th class="p-3">Tesis Tipi</th>
                        <th class="p-3">Alan & Kapasite</th>
                        <th class="p-3">Şube & Depo Kodu</th>
                        <th class="p-3">Bölüm & Vana</th>
                        <th class="p-3 text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="pl in productionLocations" :key="pl.id">
                        <td class="p-3 font-extrabold text-slate-800 dark:text-slate-100">{{ pl.name }}</td>
                        <td class="p-3">
                            <div v-if="pl.company" class="flex items-center gap-1">
                                <span class="font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950 px-2 py-0.5 rounded border border-indigo-200 dark:border-indigo-900 text-[11px]">
                                    {{ pl.company.name }}
                                </span>
                                <span class="text-[10px] text-slate-400">({{ pl.company.code }})</span>
                            </div>
                            <span v-else class="text-slate-400 italic">Firma Atanmamış</span>
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                {{ getLocationTypeLabel(pl.location_type) }}
                            </span>
                        </td>
                        <td class="p-3 font-semibold">
                            <div class="text-rose-600 dark:text-rose-400 font-bold">{{ pl.total_area_dekar }} da</div>
                            <div class="text-[11px] text-slate-400">{{ pl.approx_plant_count ? pl.approx_plant_count.toLocaleString('tr-TR') + ' Fide' : 'Fide belirtilmedi' }}</div>
                        </td>
                        <td class="p-3 text-[11px] text-slate-500 font-mono">
                            <div>Şube: {{ pl.dia_branch_code || '-' }}</div>
                            <div>Depo: {{ pl.dia_warehouse_code || '-' }}</div>
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-bold rounded text-[11px]">
                                {{ pl.sections?.length || 0 }} Bölüm / {{ pl.valves?.length || 0 }} Vana
                            </span>
                        </td>
                        <td class="p-3 text-right space-x-2">
                            <button type="button" @click="emit('edit', pl)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                            <button type="button" @click="emit('delete', pl.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
