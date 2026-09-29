<script setup lang="ts">
const props = defineProps<{
    personnels: any[];
    companies: any[];
}>();

const emit = defineEmits<{
    (e: 'create'): void;
    (e: 'edit', personnel: any): void;
    (e: 'delete', id: number): void;
}>();

const getPersonnelCompanies = (companyIds: any[]) => {
    if (!companyIds || companyIds.length === 0) return [];
    return props.companies.filter(c => companyIds.includes(c.id));
};
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 space-y-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">Personel & Görev Hiyerarşisi</h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Yetkili personeller, ast-üst onay hiyerarşisi, firma ve modül erişim kısıtları</p>
            </div>
            <button type="button" @click="emit('create')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                <span class="text-sm leading-none">+</span> Yeni Personel Ekle
            </button>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 my-3 p-2 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Kayıtlı Personel Sayısı</span>
                <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ personnels.length }} Personel</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Aktif Çalışanlar</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm">{{ personnels.filter(p => p.is_active).length }} Aktif</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Yönetici / Amir Kadrosu</span>
                <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm">{{ personnels.filter(p => !p.parent_id).length }} Amir</span>
            </div>
            <div class="px-2">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Geçmiş Veri İzinli</span>
                <span class="font-bold font-mono text-slate-700 dark:text-slate-300 text-sm">{{ personnels.filter(p => p.can_enter_backdated_data).length }} Personel</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/60 font-bold text-slate-500 uppercase border-b">
                        <th class="p-3">Personel Ad Soyad</th>
                        <th class="p-3">Görev Unvanı & İletişim</th>
                        <th class="p-3">Üst Amiri</th>
                        <th class="p-3">Yetkili Firmalar</th>
                        <th class="p-3">Modül Yetkileri</th>
                        <th class="p-3">Geçmiş Veri</th>
                        <th class="p-3">Durum</th>
                        <th class="p-3 text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="p in personnels" :key="p.id">
                        <td class="p-3">
                            <div class="font-extrabold text-slate-800 dark:text-slate-100">{{ p.first_name }} {{ p.last_name }}</div>
                            <div class="text-[11px] text-slate-400 font-mono">{{ p.user?.email || '-' }}</div>
                        </td>
                        <td class="p-3">
                            <div class="font-semibold text-slate-700 dark:text-slate-200">{{ p.role_title || 'Unvan Belirtilmedi' }}</div>
                            <div class="text-[11px] text-slate-400">{{ p.phone || '-' }}</div>
                        </td>
                        <td class="p-3">
                            <span v-if="p.parent" class="px-2 py-0.5 bg-amber-50 dark:bg-amber-950 text-amber-800 dark:text-amber-300 font-bold rounded border border-amber-200 dark:border-amber-900 text-[11px]">
                                {{ p.parent.first_name }} {{ p.parent.last_name }}
                            </span>
                            <span v-else class="text-slate-400 italic">Üst Amir Yok (Ana Yetkili)</span>
                        </td>
                        <td class="p-3">
                            <div v-if="getPersonnelCompanies(p.company_ids)?.length" class="flex flex-wrap gap-1">
                                <span v-for="comp in getPersonnelCompanies(p.company_ids)" :key="comp.id" class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold rounded border border-indigo-200 dark:border-indigo-900 text-[10px]">
                                    {{ comp.name }} ({{ comp.code }})
                                </span>
                            </div>
                            <span v-else class="text-slate-400 italic">Tüm Firmalar / Tanımsız</span>
                        </td>
                        <td class="p-3">
                            <div v-if="p.permissions?.length" class="flex flex-wrap gap-1 max-w-xs">
                                <span v-for="perm in p.permissions" :key="perm" class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold rounded text-[10px] uppercase">
                                    {{ perm }}
                                </span>
                            </div>
                            <span v-else class="text-slate-400 italic">Yetki Atanmamış</span>
                        </td>
                        <td class="p-3">
                            <span :class="p.can_enter_backdated_data ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'" class="px-2 py-0.5 rounded text-[10px] font-bold">
                                {{ p.can_enter_backdated_data ? 'İzinli' : 'Kısıtlı' }}
                            </span>
                        </td>
                        <td class="p-3">
                            <span :class="p.is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'" class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase">
                                {{ p.is_active ? 'Aktif' : 'Pasif' }}
                            </span>
                        </td>
                        <td class="p-3 text-right space-x-2 shrink-0">
                            <button type="button" @click="emit('edit', p)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                            <button type="button" @click="emit('delete', p.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
