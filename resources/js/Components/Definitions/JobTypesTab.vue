<script setup lang="ts">
const props = defineProps<{
    jobTypes: any[];
}>();

const emit = defineEmits<{
    (e: 'create'): void;
    (e: 'edit', jobType: any): void;
    (e: 'delete', id: number): void;
}>();
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6">
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">İş Tanımları & Form İlişkisi</h2>
                <p class="text-xs text-slate-400">Hasat, fidan dikimi, budama, ilaçlama iş tanımları ve form tipleri</p>
            </div>
            <button type="button" @click="emit('create')" class="bg-rose-600 hover:bg-rose-500 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                + Yeni İş Tanımı Ekle
            </button>
        </div>
        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/60 font-bold uppercase border-b">
                        <th class="p-3">İş Kodu</th>
                        <th class="p-3">İş Tanımı Adı</th>
                        <th class="p-3">Form Tipi</th>
                        <th class="p-3">Kullanılan Birimler</th>
                        <th class="p-3 text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="jt in jobTypes" :key="jt.id">
                        <td class="p-3 font-mono font-bold text-rose-600 dark:text-rose-400">{{ jt.code }}</td>
                        <td class="p-3 font-extrabold text-slate-800 dark:text-slate-100">{{ jt.name }}</td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold rounded text-[11px]">
                                {{ jt.form_type }}
                            </span>
                        </td>
                        <td class="p-3">
                            <div v-if="jt.units?.length" class="flex flex-wrap gap-1">
                                <span v-for="u in jt.units" :key="u.id" class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 rounded font-semibold text-[10px]">
                                    {{ u.name }} ({{ u.symbol }})
                                </span>
                            </div>
                            <span v-else class="text-slate-400">Yevmiye / Standart</span>
                        </td>
                        <td class="p-3 text-right space-x-2">
                            <button type="button" @click="emit('edit', jt)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                            <button type="button" @click="emit('delete', jt.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
