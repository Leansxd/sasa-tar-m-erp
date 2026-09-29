<script setup lang="ts">
const props = defineProps<{
    show: boolean;
    workPlan: any;
    commentForm: any;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'submit'): void;
}>();
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-md p-6 border border-slate-200 dark:border-slate-800">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Not / Yorum Ekle</h3>
                <button type="button" @click="emit('close')" class="text-slate-400 font-bold hover:text-slate-600">✕</button>
            </div>
            <form @submit.prevent="emit('submit')" class="space-y-4 text-xs">
                <div>
                    <label class="block font-bold mb-1 text-slate-700 dark:text-slate-300">Görev:</label>
                    <div class="p-2.5 bg-slate-50 dark:bg-slate-950 rounded-xl font-bold text-slate-800 dark:text-slate-200 border">
                        {{ workPlan?.title }}
                    </div>
                </div>
                <div>
                    <label class="block font-bold mb-1 text-slate-700 dark:text-slate-300">Süreç Notu / Yorumunuz:</label>
                    <textarea v-model="commentForm.comment" rows="3" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 text-xs" placeholder="İş durumu, yapılan müdahale veya süreç hakkında açıklama yazın..." required></textarea>
                </div>
                <div>
                    <label class="block font-bold mb-1 text-slate-700 dark:text-slate-300">Görsel Kanıt Yükle (İsteğe Bağlı Fotoğraf):</label>
                    <input type="file" @change="(e: any) => commentForm.photo = e.target.files[0]" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200" accept="image/*" />
                </div>
                <div class="flex justify-end space-x-2 pt-3 border-t">
                    <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl font-bold">İptal</button>
                    <button type="submit" :disabled="commentForm.processing" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl transition">
                        Notu Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
