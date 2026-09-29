<script setup lang="ts">
defineProps<{
    fertRuns: any[];
}>();

defineEmits<{
    (e: 'create'): void;
    (e: 'edit', item: any): void;
    (e: 'toggleActive', id: number): void;
    (e: 'printTank', run: any, tank: any): void;
    (e: 'printGeneric', run: any): void;
    (e: 'logTank', run: any, tank: any): void;
    (e: 'delete', id: number): void;
}>();

const formatDisplayDate = (val: string) => {
    if (!val) return '-';
    const clean = val.split('T')[0];
    const parts = clean.split('-');
    if (parts.length === 3) {
        return `${parts[2]}.${parts[1]}.${parts[0]}`;
    }
    return clean;
};
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 space-y-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">Gübreleme Programı & Tank Takibi</h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Reçete aktifleştirme, sözel bitiş şartı ve çalışanlar için tank hazırlama döküm çıktısı</p>
            </div>
            <button @click="$emit('create')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                <span class="text-sm leading-none">+</span> Yeni Reçete Aktifleştir
            </button>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 my-2 p-2 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Programlanan Reçeteler</span>
                <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ fertRuns.length }} Reçete</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Aktif Uygulanan Reçete</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm">{{ fertRuns.filter(f => f.is_active).length }} Aktif</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Hedef pH Seviyesi</span>
                <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">6.50 pH</span>
            </div>
            <div class="px-2">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Hedef EC İletkenlik</span>
                <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">1.80 mS/cm</span>
            </div>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-950/80 text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px]">
                    <tr>
                        <th class="p-2">Reçete Adı</th>
                        <th class="p-2">Tesis</th>
                        <th class="p-2">Başlangıç</th>
                        <th class="p-2">pH / EC</th>
                        <th class="p-2">Bitiş Şartı</th>
                        <th class="p-2 text-center">Durum</th>
                        <th class="p-2 text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="fr in fertRuns" :key="fr.id" class="hover:bg-slate-50 dark:hover:bg-slate-950/40 transition">
                        <td class="p-2 font-bold text-slate-800 dark:text-slate-100">
                            {{ fr.recipe?.name || 'Topraksız Çilek Büyütme Reçetesi' }}
                        </td>
                        <td class="p-2 text-slate-600 dark:text-slate-300">
                            {{ fr.production_location?.name || 'SASA Tarım Tesisi' }}
                        </td>
                        <td class="p-2 font-mono text-slate-500 text-[11px]">
                            {{ formatDisplayDate(fr.start_date) }}
                        </td>
                        <td class="p-2 font-mono text-slate-700 dark:text-slate-300 text-[11px]">
                            pH: {{ fr.recipe?.water_ph || '6.5' }} | EC: {{ fr.recipe?.water_ec || '1.8' }}
                        </td>
                        <td class="p-2 font-semibold text-rose-500 text-[11px]">
                            {{ fr.end_condition || fr.recipe?.duration_condition || '-' }}
                        </td>
                        <td class="p-2 text-center">
                            <button type="button" @click="$emit('toggleActive', fr.id)"
                                    :class="fr.is_active ? 'bg-emerald-950/80 text-emerald-400 border-emerald-800/80 hover:bg-emerald-900/80' : 'bg-slate-800 text-slate-400 border-slate-700 hover:bg-slate-700'"
                                    class="px-2 py-0.5 rounded text-[10px] font-bold border inline-flex items-center gap-1 cursor-pointer transition">
                                <span class="w-1.5 h-1.5 rounded-full" :class="fr.is_active ? 'bg-emerald-400' : 'bg-slate-500'"></span>
                                {{ fr.is_active ? 'Aktif' : 'Pasif (Aktif Yap)' }}
                            </button>
                        </td>
                        <td class="p-2 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2 text-[11px]">
                                <button v-if="!fr.is_active" @click="$emit('toggleActive', fr.id)" class="text-emerald-500 font-bold hover:underline cursor-pointer">
                                    Aktif Yap
                                </button>
                                <button @click="$emit('edit', fr)" class="text-slate-700 dark:text-slate-200 font-semibold hover:underline cursor-pointer">
                                    Düzenle
                                </button>
                                <button v-if="fr.recipe?.tanks && fr.recipe.tanks[0]" @click="$emit('printTank', fr, fr.recipe.tanks[0])" class="text-slate-700 dark:text-slate-200 font-semibold hover:underline cursor-pointer">
                                    Çıktı
                                </button>
                                <button v-else @click="$emit('printGeneric', fr)" class="text-slate-700 dark:text-slate-200 font-semibold hover:underline cursor-pointer">
                                    Çıktı
                                </button>
                                <button @click="$emit('delete', fr.id)" class="text-rose-500 font-semibold hover:underline cursor-pointer">
                                    Sil
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- AKTİF REÇETENİN TANK HAZIRLAMA & STOK KARTLARI -->
        <div v-if="fertRuns.filter(f => f.is_active).length > 0" class="space-y-2.5 pt-1">
            <div v-for="fr in fertRuns.filter(f => f.is_active)" :key="'tanks-' + fr.id" class="space-y-2.5">
                <div class="flex items-center justify-between flex-wrap gap-2 pb-1.5 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="px-1.5 py-0.5 bg-emerald-600 text-white text-[9px] font-bold rounded">AKTİF REÇETE</span>
                        <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100">
                            {{ fr.recipe?.name || 'Topraksız Çilek Reçetesi' }}
                        </h3>
                        <span class="text-slate-400 text-[11px]">({{ fr.end_condition || fr.recipe?.duration_condition || '-' }})</span>
                    </div>
                    <div class="text-[11px] font-mono text-slate-500 dark:text-slate-400">
                        Hedef: pH {{ fr.recipe?.water_ph || '6.5' }} | EC {{ fr.recipe?.water_ec || '1.8' }} mS/cm
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-stretch">
                    <div v-for="tank in (fr.recipe?.tanks || [{ id: 1, tank_name: 'A Tankı (Solüsyon)', capacity_liters: 1000, items: [] }])" :key="'tank-card-' + tank.id" class="rounded-lg border border-slate-200 dark:border-slate-800 p-3 bg-slate-50/40 dark:bg-slate-950/20 flex flex-col justify-between h-full">
                        <div>
                            <div class="flex items-center justify-between gap-2 pb-1.5 border-b border-slate-100 dark:border-slate-800">
                                <div>
                                    <span class="font-bold text-xs text-slate-900 dark:text-slate-100">{{ tank.tank_name }}</span>
                                    <span class="text-[10px] text-slate-400 ml-1.5">({{ tank.capacity_liters }} L Hacim)</span>
                                </div>
                                <span class="px-1.5 py-0.5 bg-emerald-950/60 text-emerald-400 text-[9px] font-bold rounded border border-emerald-800/60">
                                    {{ (fr.tankLogs || []).filter((tl: any) => tl.tank_name === tank.tank_name || tl.fertilization_tank_id === tank.id).length }} Kez Hazırlandı
                                </span>
                            </div>

                            <div class="space-y-0.5 pt-1.5">
                                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Reçete Karışımı:</div>
                                <div v-if="tank.items?.length" class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs">
                                    <div v-for="(it, iIdx) in tank.items" :key="iIdx" class="py-1 flex items-center justify-between gap-2">
                                        <span class="text-slate-700 dark:text-slate-300 font-medium text-[11px] truncate">{{ it.product_name }}</span>
                                        <span class="font-bold text-slate-900 dark:text-slate-100 font-mono text-[11px] shrink-0">{{ it.quantity }} {{ it.unit }}</span>
                                    </div>
                                </div>
                                <div v-else class="text-xs text-slate-400 italic py-0.5">Solüsyon ürün karışımları girilmemiş.</div>
                            </div>
                        </div>

                        <div class="pt-2 mt-auto space-y-2">
                            <div class="pt-1.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-500">
                                <span class="text-[10px] font-semibold text-slate-400 uppercase">Son Hazırlama:</span>
                                <template v-if="(fr.tankLogs || []).filter((tl: any) => tl.tank_name === tank.tank_name || tl.fertilization_tank_id === tank.id).length">
                                    <span class="font-medium text-slate-700 dark:text-slate-300">
                                        {{ formatDisplayDate((fr.tankLogs || []).filter((tl: any) => tl.tank_name === tank.tank_name || tl.fertilization_tank_id === tank.id)[0]?.prepared_at) }}
                                        <span class="text-slate-400 font-normal">({{ (fr.tankLogs || []).filter((tl: any) => tl.tank_name === tank.tank_name || tl.fertilization_tank_id === tank.id)[0]?.prepared_by?.first_name || 'Personel' }})</span>
                                    </span>
                                </template>
                                <template v-else>
                                    <span class="text-slate-400 italic">Henüz hazırlanmadı</span>
                                </template>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button" @click="$emit('logTank', fr, tank)" class="flex-1 py-1.5 px-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-lg transition text-center cursor-pointer">
                                    + Hazırla & Kaydet
                                </button>
                                <button type="button" @click="$emit('printTank', fr, tank)" class="flex-1 py-1.5 px-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-semibold text-xs rounded-lg transition text-center cursor-pointer">
                                    Çalışan Çıktısı Al
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="p-3 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200 dark:border-slate-800 text-center text-xs text-slate-400">
            Şu anda aktif uygulanan bir gübreleme reçetesi bulunmuyor. Yukarıdaki tablodan bir reçetenin durumundaki <strong>'Pasif (Aktif Yap)'</strong> butonuna tıklayarak başlatabilirsiniz.
        </div>
    </div>
</template>
