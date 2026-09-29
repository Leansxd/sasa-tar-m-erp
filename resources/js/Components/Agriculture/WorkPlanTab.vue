<script setup lang="ts">
defineProps<{
    workPlans: any[];
}>();

defineEmits<{
    (e: 'create'): void;
    (e: 'edit', item: any): void;
    (e: 'comment', item: any): void;
    (e: 'print', item: any): void;
    (e: 'previewImage', url: string): void;
    (e: 'delete', id: number): void;
}>();
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 space-y-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">Yapılacaklar Listesi (İş Planlama)</h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">İşletme müdürü tarafından atanan işler, hedef tarihler ve görevlilerin fotoğraf doğrulamaları</p>
            </div>
            <button @click="$emit('create')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                <span class="text-sm leading-none">+</span> Yeni İş Planı Oluştur
            </button>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 my-3 p-2 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam İş Planı</span>
                <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ workPlans.length }} Görev</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Bekleyen Görevler</span>
                <span class="font-bold text-amber-600 dark:text-amber-400 text-sm">{{ workPlans.filter(w => w.status === 'pending').length }} İş</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Devam Eden Saha İşleri</span>
                <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm">{{ workPlans.filter(w => w.status === 'in_progress').length }} İş</span>
            </div>
            <div class="px-2">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Tamamlananlar</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm">{{ workPlans.filter(w => w.status === 'completed').length }} İş</span>
            </div>
        </div>

        <div v-if="workPlans.length === 0" class="text-center py-12 border border-slate-100 dark:border-slate-800 rounded-xl bg-slate-50/50 dark:bg-slate-950/40">
            <h3 class="text-sm font-bold text-slate-600 dark:text-slate-300">Henüz İş Planı Oluşturulmadı</h3>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-for="wp in workPlans" :key="wp.id" class="border border-slate-200 dark:border-slate-800/80 rounded-2xl p-4 bg-slate-50/50 dark:bg-slate-900/90 space-y-3">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm">{{ wp.title }}</h3>
                        <span class="text-[10px] text-slate-400">İş Türü: {{ wp.job_type?.name || 'Genel Görev' }}</span>
                    </div>
                    <span :class="wp.status === 'completed' ? 'bg-emerald-950/80 text-emerald-400 border-emerald-800/80' : (wp.status === 'in_progress' ? 'bg-indigo-950/80 text-indigo-300 border-indigo-800/80' : (wp.status === 'cancelled' ? 'bg-rose-950/80 text-rose-400 border-rose-800/80' : 'bg-amber-950/80 text-amber-400 border-amber-800/80'))" class="px-2.5 py-1 rounded-lg text-xs font-semibold border">
                        {{ wp.status === 'completed' ? 'Tamamlandı' : (wp.status === 'in_progress' ? 'Devam Ediyor' : (wp.status === 'cancelled' ? 'İptal Edildi' : 'Beklemede')) }}
                    </span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-950/80 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800/80">
                    <strong>Müdür Talimatı:</strong> {{ wp.description || 'Açıklama girilmedi.' }}
                </p>
                <div class="text-xs space-y-1.5 text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-950/80 p-3 rounded-xl border border-slate-200 dark:border-slate-800/80">
                    <div>Tesis: <strong>{{ wp.production_location?.name || 'Tesis Belirtilmedi' }}</strong></div>
                    <div>Görevli / Sorumlu: <strong>{{ wp.assigned_personnel ? (wp.assigned_personnel.first_name + ' ' + wp.assigned_personnel.last_name) : 'Genel Atama' }}</strong></div>
                    <div>Plan Tarihi: <strong>{{ wp.plan_date }}</strong> | Hedef Bitiş: <strong class="text-rose-600 dark:text-rose-400">{{ wp.due_date || 'Süre Belirtilmedi' }}</strong></div>
                    <div v-if="wp.completion_notes" class="pt-2 border-t border-slate-800 text-emerald-700 dark:text-emerald-400">
                        <strong>Süreç / Sonuç Notu:</strong> {{ wp.completion_notes }}
                    </div>
                    <div v-if="wp.photo_path" class="pt-1">
                        <strong>Fotoğraf Kanıtı:</strong> <button @click="$emit('previewImage', wp.photo_path)" class="text-indigo-600 dark:text-indigo-400 underline font-bold text-[11px]">Fotoğrafı Görüntüle</button>
                    </div>
                    <div v-if="wp.comments && wp.comments.length > 0" class="pt-2 border-t border-slate-800 space-y-1.5">
                        <div class="text-[11px] font-bold text-slate-500 uppercase">Personel Yorum & Süreç Akışı ({{ wp.comments.length }} Not):</div>
                        <div v-for="c in wp.comments" :key="c.id" class="p-2 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 text-[11px]">
                            <div class="flex justify-between font-bold text-slate-700 dark:text-slate-300">
                                <span>{{ c.personnel ? (c.personnel.first_name + ' ' + c.personnel.last_name) : 'Personel' }}</span>
                                <span class="text-[10px] text-slate-400 font-normal">{{ new Date(c.created_at).toLocaleDateString('tr-TR') }}</span>
                            </div>
                            <p class="text-slate-600 dark:text-slate-400 mt-0.5">{{ c.comment }}</p>
                            <div v-if="c.photo_path" class="mt-1">
                                <button @click="$emit('previewImage', c.photo_path)" class="text-rose-600 dark:text-rose-400 font-bold underline text-[10px]">Yorum Görselini Büyüt</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-between items-center gap-2 pt-1 flex-wrap">
                    <button @click="$emit('comment', wp)" class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 px-3 py-1.5 rounded-lg text-xs font-bold transition">
                        Not / Yorum Ekle
                    </button>
                    <div class="flex items-center gap-2">
                        <button @click="$emit('print', wp)" class="bg-teal-600 hover:bg-teal-500 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">
                            Çıktı Al
                        </button>
                        <button @click="$emit('edit', wp)" class="bg-indigo-600 hover:bg-indigo-500 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">
                            Durum & Not Güncelle
                        </button>
                        <button @click="$emit('delete', wp.id)" class="bg-rose-500/10 hover:bg-rose-500/20 text-rose-500 border border-rose-500/30 px-3 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer">
                            Sil
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
