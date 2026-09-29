<script setup lang="ts">
const props = defineProps<{
    waterSources: any[];
    filters: any[];
}>();

const emit = defineEmits<{
    (e: 'createWaterSource'): void;
    (e: 'createFilter'): void;
    (e: 'editWaterSource', source: any): void;
    (e: 'editFilter', filter: any): void;
    (e: 'deleteWaterSource', id: number): void;
    (e: 'deleteFilter', id: number): void;
}>();
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6">
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Su Kaynakları & Filtreler</h2>
                <p class="text-xs text-slate-400">Kuyu pompaları, aktif/pasif döngüler, temizlik periyotları ve bağlı tesis/firma ilişkisi</p>
            </div>
            <div class="space-x-2">
                <button type="button" @click="emit('createWaterSource')" class="bg-blue-600 hover:bg-blue-500 text-white px-3 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                    + Su Kaynağı Ekle
                </button>
                <button type="button" @click="emit('createFilter')" class="bg-amber-600 hover:bg-amber-500 text-white px-3 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                    + Filtre Ekle
                </button>
            </div>
        </div>
        <div class="space-y-6 text-xs">
            <div>
                <h3 class="text-xs font-extrabold uppercase text-slate-500 dark:text-slate-400 mb-2 tracking-wide">Su Kaynakları (Kuyu & Havuz)</h3>
                <div class="space-y-2">
                    <div v-for="ws in waterSources" :key="ws.id" class="border rounded-xl p-4 bg-slate-50 dark:bg-slate-950 flex justify-between items-start">
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-extrabold text-sm text-slate-800 dark:text-slate-100">{{ ws.name }}</h4>
                                <span v-if="ws.production_location?.company" class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold rounded text-[10px]">
                                    {{ ws.production_location.company.name }}
                                </span>
                            </div>
                            <p class="text-slate-400 mt-1">Bağlı Tesis: <span class="font-bold text-slate-700 dark:text-slate-200">{{ ws.production_location?.name || 'Tesis Belirtilmedi' }}</span> | Aktif: {{ ws.active_cycle_minutes }} dk | Pasif: {{ ws.passive_cycle_minutes }} dk | Fotoğraf Doğrulama: <span :class="ws.requires_photo_verification ? 'text-rose-600 font-bold' : 'text-slate-400'">{{ ws.requires_photo_verification ? 'Zorunlu' : 'İsteğe Bağlı' }}</span></p>
                        </div>
                        <div class="space-x-2 shrink-0">
                            <button type="button" @click="emit('editWaterSource', ws)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                            <button type="button" @click="emit('deleteWaterSource', ws.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                        </div>
                    </div>
                    <p v-if="!waterSources.length" class="text-slate-400 italic">Henüz su kaynağı tanımlanmamış.</p>
                </div>
            </div>
            <div>
                <h3 class="text-xs font-extrabold uppercase text-slate-500 dark:text-slate-400 mb-2 tracking-wide">Filtreler & İstasyonlar</h3>
                <div class="space-y-2">
                    <div v-for="f in filters" :key="f.id" class="border rounded-xl p-4 bg-slate-50 dark:bg-slate-950 flex justify-between items-start">
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-extrabold text-sm text-slate-800 dark:text-slate-100">{{ f.name }}</h4>
                                <span v-if="f.production_location?.company" class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold rounded text-[10px]">
                                    {{ f.production_location.company.name }}
                                </span>
                            </div>
                            <p class="text-slate-400 mt-1">Bağlı Tesis: <span class="font-bold text-slate-700 dark:text-slate-200">{{ f.production_location?.name || 'Tesis Belirtilmedi' }}</span> | Temizlik Döngüsü: <span class="font-bold text-emerald-600">{{ f.cleaning_cycle_days }} günde bir</span> | Fotoğraf: <span :class="f.requires_photo_verification ? 'text-rose-600 font-bold' : 'text-slate-400'">{{ f.requires_photo_verification ? 'Zorunlu' : 'İsteğe Bağlı' }}</span></p>
                        </div>
                        <div class="space-x-2 shrink-0">
                            <button type="button" @click="emit('editFilter', f)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                            <button type="button" @click="emit('deleteFilter', f.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                        </div>
                    </div>
                    <p v-if="!filters.length" class="text-slate-400 italic">Henüz filtre tanımlanmamış.</p>
                </div>
            </div>
        </div>
    </div>
</template>
