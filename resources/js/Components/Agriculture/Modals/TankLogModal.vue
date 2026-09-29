<script setup lang="ts">
const props = defineProps<{
    show: boolean;
    tankLogForm: any;
    personnels: any[];
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'submit'): void;
}>();
</script>

<template>
    <div v-if="show" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Tank Yeniden Hazırlama Kaydı</h3>
                    <p class="text-[11px] text-slate-500">{{ tankLogForm.tank_name }} solüsyon yenileme</p>
                </div>
                <button type="button" @click="emit('close')" class="text-slate-400 hover:text-slate-700 font-bold text-xl leading-none">&times;</button>
            </div>

            <form @submit.prevent="emit('submit')" class="space-y-3.5 text-xs">
                <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl border border-emerald-200 dark:border-emerald-900 text-emerald-900 dark:text-emerald-300 text-[11px] space-y-1">
                    <div class="font-bold">
                        DIA Stok Entegrasyonu:
                    </div>
                    <p class="text-[10px] text-emerald-700 dark:text-emerald-400">Bu kaydı oluşturduğunuzda, reçetede tanımlı {{ tankLogForm.tank_name }} içerisindeki tüm gübreler DIA stoklarından otomatik olarak düşülür.</p>
                </div>

                <div>
                    <label class="block font-bold mb-1">Hazırlanan Tank Adı</label>
                    <input v-model="tankLogForm.tank_name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-extrabold" required readonly />
                </div>

                <div>
                    <label class="block font-bold mb-1">Hazırlama Tarihi ve Saati *</label>
                    <input v-model="tankLogForm.prepared_at" type="datetime-local" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-bold" required />
                </div>

                <div>
                    <label class="block font-bold mb-1">Hazırlayan Görevli Personel *</label>
                    <select v-model="tankLogForm.prepared_by_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-medium" required>
                        <option v-for="p in personnels" :key="p.id" :value="p.id">{{ p.first_name }} {{ p.last_name }} ({{ p.department || 'Personel' }})</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold mb-1">Saha Notları / Açıklama</label>
                    <textarea v-model="tankLogForm.notes" rows="2" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 text-xs" placeholder="Örn: 1000L tank tam dolduruldu, karıştırıcı motor çalıştırıldı..."></textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t">
                    <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl font-bold">İptal</button>
                    <button type="submit" :disabled="tankLogForm.processing" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-xs transition">
                        Kaydet & Stoktan Düş
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
