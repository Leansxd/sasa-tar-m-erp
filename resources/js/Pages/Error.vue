<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    status?: number;
    message?: string;
    details?: string;
}>();

const title = computed(() => {
    return {
        503: 'Servis Bakımda',
        500: 'Sunucu İşlem Hatası',
        404: 'Sayfa veya Kayıt Bulunamadı',
        403: 'Erişim Yetkiniz Bulunmuyor',
        401: 'Oturum Açmanız Gerekiyor',
    }[props.status || 500] || 'Beklenmeyen Bir Hata Oluştu';
});

const defaultDescription = computed(() => {
    return {
        503: 'Sistem şu anda güncelleniyor veya bakım aşamasında. Lütfen birkaç dakika sonra tekrar deneyiniz.',
        500: 'İşleminiz gerçekleştirilirken beklenmeyen bir hata meydana geldi. Sistem hatayı güvenli şekilde yakaladı.',
        404: 'Ulaşmaya çalıştığınız sayfa veya veritabanı kaydı taşınmış ya da silinmiş olabilir.',
        403: 'Bu modüle veya işleme erişmek için gerekli personel yetkilerine sahip değilsiniz.',
        401: 'Bu işlemi yapabilmek için lütfen sisteme tekrar giriş yapınız.',
    }[props.status || 500] || 'İşleminiz tamamlanamadı.';
});

const goBack = () => {
    if (typeof window !== 'undefined') {
        window.history.back();
    }
};
</script>

<template>
    <Head :title="title" />
    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white dark:bg-slate-900 rounded-2xl p-8 border border-slate-200 dark:border-slate-800 shadow-xl text-center space-y-6">
            <div class="w-16 h-16 rounded-2xl mx-auto flex items-center justify-center"
                :class="status === 403 || status === 401 ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-600' : 'bg-rose-100 dark:bg-rose-900/30 text-rose-600'">
                <svg v-if="status === 404" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg v-else-if="status === 403 || status === 401" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <svg v-else class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <div class="space-y-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Durum Kodu: {{ status || 500 }}
                </span>
                <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">
                    {{ title }}
                </h1>
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    {{ message || defaultDescription }}
                </p>
                <div v-if="details" class="mt-3 p-3 bg-slate-100 dark:bg-slate-800/60 rounded-lg text-left text-xs font-mono text-slate-700 dark:text-slate-300 break-words max-h-32 overflow-y-auto">
                    {{ details }}
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 justify-center pt-2">
                <button @click="goBack"
                    class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Geri Dön
                </button>
                <Link href="/dashboard"
                    class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-sm font-semibold text-white shadow-md shadow-emerald-600/20 transition">
                    Ana Sayfaya Git
                </Link>
            </div>
        </div>
    </div>
</template>
