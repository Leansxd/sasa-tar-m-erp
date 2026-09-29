<script setup lang="ts">
const props = defineProps<{
    show: boolean;
    imageUrl: string;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();
</script>

<template>
    <div v-if="show" @click="emit('close')" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 transition-opacity">
        <div class="relative max-w-4xl max-h-[90vh] bg-slate-900 p-2 rounded-2xl shadow-2xl border border-slate-700" @click.stop>
            <button type="button" @click="emit('close')" class="absolute -top-3 -right-3 bg-rose-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold text-sm shadow-lg hover:bg-rose-500 transition">
                ✕
            </button>
            <img :src="imageUrl" alt="SASA ERP Belge Görseli" class="max-h-[80vh] w-auto object-contain rounded-xl" />
            <div class="mt-2 text-center text-xs font-semibold text-slate-300">
                📄 Belge Kanıt Fotoğrafı - SASA Tarım ERP
            </div>
        </div>
    </div>
</template>
