<script setup lang="ts">
const props = defineProps<{
    companies: any[];
}>();

const emit = defineEmits<{
    (e: 'create'): void;
    (e: 'edit', company: any): void;
    (e: 'delete', id: number): void;
}>();

const getCompanyTotalDekar = (c: any) => {
    if (!c.production_locations || c.production_locations.length === 0) return 0;
    return c.production_locations.reduce((acc: number, cur: any) => acc + Number(cur.total_area_dekar || 0), 0);
};
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 space-y-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">Firma Yönetimi</h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Grup ve işletme şirketleri yönetimi</p>
            </div>
            <button type="button" @click="emit('create')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                <span class="text-sm leading-none">+</span> Yeni Firma Ekle
            </button>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 my-3 p-2 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Kayıtlı Grup Firmaları</span>
                <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ companies.length }} Firma</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Aktif İşletme Firması</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm">{{ companies.filter(c => c.is_active).length }} Aktif</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Bağlı Üretim Tesisleri</span>
                <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm">{{ companies.reduce((acc, c) => acc + (c.production_locations?.length || 0), 0) }} Tesis</span>
            </div>
            <div class="px-2">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam İşlenen Arazi</span>
                <span class="font-bold font-mono text-slate-700 dark:text-slate-300 text-sm">{{ companies.reduce((acc, c) => acc + getCompanyTotalDekar(c), 0) }} da</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/60 font-bold text-slate-500 uppercase border-b">
                        <th class="p-3">Firma Kodu</th>
                        <th class="p-3">Firma Unvanı</th>
                        <th class="p-3">Vergi No</th>
                        <th class="p-3">Bağlı Üretim Tesisleri</th>
                        <th class="p-3">Durum</th>
                        <th class="p-3 text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="c in companies" :key="c.id">
                        <td class="p-3">
                            <span class="font-bold text-rose-600 bg-rose-50 dark:bg-rose-950/50 px-2 py-1 rounded border border-rose-200 dark:border-rose-900">{{ c.code }}</span>
                        </td>
                        <td class="p-3 font-semibold text-slate-800 dark:text-slate-100">{{ c.name }}</td>
                        <td class="p-3 text-slate-500 font-mono font-semibold">
                            {{ c.tax_number || '-' }}
                        </td>
                        <td class="p-3">
                            <div v-if="c.production_locations?.length" class="flex flex-wrap gap-1 items-center">
                                <span v-for="loc in c.production_locations" :key="loc.id" class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-bold rounded border border-emerald-200 dark:border-emerald-900 text-[11px]">
                                    {{ loc.name }} ({{ loc.total_area_dekar }} da)
                                </span>
                                <span class="text-[11px] font-extrabold text-slate-400 ml-1">Toplam: {{ getCompanyTotalDekar(c) }} da</span>
                            </div>
                            <span v-else class="text-slate-400 italic">Kayıtlı tesis yok</span>
                        </td>
                        <td class="p-3">
                            <span :class="c.is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'" class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase">
                                {{ c.is_active ? 'Aktif' : 'Pasif' }}
                            </span>
                        </td>
                        <td class="p-3 text-right space-x-2">
                            <button type="button" @click="emit('edit', c)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline cursor-pointer">Düzenle</button>
                            <button type="button" @click="emit('delete', c.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline cursor-pointer">Sil</button>
                        </td>
                    </tr>
                    <tr v-if="companies.length === 0">
                        <td colspan="6" class="p-8 text-center text-slate-400">
                            Henüz kayıtlı firma bulunmuyor. Yeni Firma Ekle butonuyla veya Kurulum Sihirbazı ile firmanızı ekleyebilirsiniz.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
