<script setup lang="ts">
const props = defineProps<{
    fertilizationRecipes: any[];
    sprayingRecipes: any[];
}>();

const emit = defineEmits<{
    (e: 'createFert'): void;
    (e: 'createSpray'): void;
    (e: 'editFert', recipe: any): void;
    (e: 'editSpray', recipe: any): void;
    (e: 'deleteFert', id: number): void;
    (e: 'deleteSpray', id: number): void;
    (e: 'toggleActiveFert', id: number): void;
}>();
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6">
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Gübre & İlaç Reçeteleri</h2>
                <p class="text-xs text-slate-400">Çoklu tank solüsyon reçeteleri, pH/EC hedefleri ve ilaçlama kombinasyonları</p>
            </div>
            <div class="space-x-2">
                <button type="button" @click="emit('createFert')" class="bg-emerald-600 hover:bg-emerald-500 text-white px-3 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                    + Gübre Reçetesi Ekle
                </button>
                <button type="button" @click="emit('createSpray')" class="bg-purple-600 hover:bg-purple-500 text-white px-3 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                    + İlaç Reçetesi Ekle
                </button>
            </div>
        </div>
        <div class="space-y-4 text-xs">
            <div v-for="fr in fertilizationRecipes" :key="fr.id" class="border rounded-xl p-4 bg-slate-50 dark:bg-slate-950 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 font-bold px-2 py-0.5 rounded text-[10px]">Gübre Reçetesi</span>
                        <span v-if="fr.is_active" class="bg-emerald-600 text-white font-extrabold px-2 py-0.5 rounded text-[10px]">AKTİF REÇETE</span>
                        <span v-else class="bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold px-2 py-0.5 rounded text-[10px]">PASİF</span>
                    </div>
                    <h3 class="font-extrabold text-sm text-slate-800 dark:text-slate-100 mt-1">{{ fr.name }}</h3>
                    <p class="text-slate-400 mt-0.5">Bitiş Şartı: {{ fr.duration_condition || 'Belirtilmedi' }} | Hedef pH: <span class="font-bold text-slate-700 dark:text-slate-200">{{ fr.water_ph }}</span> | Hedef EC: <span class="font-bold text-slate-700 dark:text-slate-200">{{ fr.water_ec }}</span></p>
                    <div v-if="fr.tanks?.length" class="mt-2 flex flex-wrap gap-1">
                        <span v-for="t in fr.tanks" :key="t.id" class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 rounded font-mono text-[10px]">
                            {{ t.tank_name }} ({{ t.capacity_liters }}L): {{ t.items?.map((i: any) => i.product_name + ' ' + i.quantity + ' ' + i.unit).join(', ') }}
                        </span>
                    </div>
                </div>
                <div class="space-x-2 shrink-0">
                    <button type="button" v-if="!fr.is_active" @click="emit('toggleActiveFert', fr.id)" class="bg-emerald-600 hover:bg-emerald-500 text-white px-3 py-1.5 rounded-lg font-bold">
                        Bu Reçeteyi Aktif Yap
                    </button>
                    <button type="button" @click="emit('editFert', fr)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                    <button type="button" @click="emit('deleteFert', fr.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                </div>
            </div>
            <div v-for="sr in sprayingRecipes" :key="'sr-' + sr.id" class="border rounded-xl p-4 bg-slate-50 dark:bg-slate-950 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="bg-purple-100 dark:bg-purple-950 text-purple-800 dark:text-purple-300 font-bold px-2 py-0.5 rounded text-[10px]">İlaç Reçetesi</span>
                        <span :class="sr.is_active ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400'" class="font-bold px-2 py-0.5 rounded text-[10px]">{{ sr.is_active ? 'Aktif' : 'Pasif' }}</span>
                    </div>
                    <h3 class="font-extrabold text-sm text-slate-800 dark:text-slate-100 mt-1">{{ sr.name }}</h3>
                    <p class="text-slate-400 mt-0.5">Uygulama Amacı: {{ sr.usage_purpose }} | Su Hacmi: {{ sr.water_volume_liters }}L | Yöntem: {{ sr.application_method }}</p>
                    <div v-if="sr.items?.length" class="mt-2 flex flex-wrap gap-1">
                        <span v-for="it in sr.items" :key="it.id" class="px-2 py-0.5 bg-purple-50 dark:bg-purple-950 text-purple-800 dark:text-purple-300 rounded font-mono text-[10px]">
                            {{ it.product_name }} ({{ it.quantity }} {{ it.unit }})
                        </span>
                    </div>
                </div>
                <div class="space-x-2 shrink-0">
                    <button type="button" @click="emit('editSpray', sr)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                    <button type="button" @click="emit('deleteSpray', sr.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                </div>
            </div>
        </div>
    </div>
</template>
