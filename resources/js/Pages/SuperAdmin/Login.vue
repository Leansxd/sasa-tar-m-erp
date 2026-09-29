<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    qrUrl?: string;
    secretKey?: string;
    captcha?: {
        token: string;
        svg: string;
    };
}>();

const showQrModal = ref(false);

const currentCaptcha = ref<{ token: string; svg: string }>(
    props.captcha || { token: '', svg: '' }
);
const isRefreshingCaptcha = ref(false);

const form = useForm({
    master_key: '',
    totp_code: '',
    captcha_token: currentCaptcha.value.token,
    captcha_answer: '',
});

const refreshCaptcha = async () => {
    isRefreshingCaptcha.value = true;
    try {
        const res = await fetch(route('master-control.refresh-captcha'));
        const data = await res.json();
        if (data.token && data.svg) {
            currentCaptcha.value = data;
            form.captcha_token = data.token;
            form.captcha_answer = '';
        }
    } catch {} finally {
        isRefreshingCaptcha.value = false;
    }
};

const submit = () => {
    form.post(route('master-control.login'), {
        preserveScroll: true,
        onError: () => {
            refreshCaptcha();
        },
        onFinish: () => {
            form.totp_code = '';
        }
    });
};
</script>

<template>
    <Head title="SASA SaaS Master - Güvenlik Doğrulaması & 2FA" />

    <div class="min-h-screen bg-slate-950 flex items-center justify-center p-4 font-sans select-none relative">
        <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl p-7 shadow-2xl text-slate-100 space-y-5">
            
            <div class="text-center space-y-2.5">
                <div class="w-12 h-12 flex items-center justify-center mx-auto">
                    <img src="/sasaerp.svg" alt="SASA ERP Logo" class="w-12 h-12 object-contain" />
                </div>
                <div>
                    <h1 class="text-sm font-black text-slate-100 uppercase tracking-wider">SASA ERP</h1>
                    <div class="text-[10px] font-bold text-rose-400 tracking-widest uppercase">SAAS MASTER KONTROL</div>
                </div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-950/60 border border-rose-800/60 text-rose-300 text-[11px] font-bold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>2FA + CAPTCHA Koruması Aktif</span>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5 uppercase tracking-wide">1. Master Güvenlik Anahtarı</label>
                    <input
                        v-model="form.master_key"
                        type="password"
                        required
                        autofocus
                        placeholder="••••••••••••"
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-600 text-sm focus:border-rose-500 focus:outline-none transition text-center tracking-widest font-mono"
                    />
                    <div v-if="form.errors.master_key" class="text-rose-400 text-xs font-semibold mt-1 text-center">
                        {{ form.errors.master_key }}
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wide">2. Authenticator Kodu (6 Hane)</label>
                        <button
                            type="button"
                            @click="showQrModal = true"
                            class="text-[10px] text-rose-400 hover:text-rose-300 font-bold underline cursor-pointer"
                        >
                            QR Kod / Kurulum
                        </button>
                    </div>
                    <input
                        v-model="form.totp_code"
                        type="text"
                        required
                        maxlength="6"
                        placeholder="Örn: 582914"
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-600 text-lg font-black focus:border-rose-500 focus:outline-none transition text-center tracking-[0.3em] font-mono"
                    />
                    <div v-if="form.errors.totp_code" class="text-rose-400 text-xs font-semibold mt-1 text-center">
                        {{ form.errors.totp_code }}
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-slate-950/80 border border-slate-800 space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wide">3. Güvenlik Doğrulaması (CAPTCHA)</label>
                        <button
                            type="button"
                            @click="refreshCaptcha"
                            :disabled="isRefreshingCaptcha"
                            class="text-[10px] text-sky-400 hover:text-sky-300 font-bold inline-flex items-center gap-1 cursor-pointer"
                        >
                            <svg :class="{ 'animate-spin': isRefreshingCaptcha }" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span>Yenile</span>
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="h-10 shrink-0 bg-slate-900 border border-slate-800 rounded-lg overflow-hidden flex items-center justify-center p-0.5">
                            <img :src="currentCaptcha.svg" alt="Captcha" class="h-full object-contain" />
                        </div>
                        <input
                            v-model="form.captcha_answer"
                            type="text"
                            required
                            placeholder="Sonucu yazın"
                            class="flex-1 px-3 py-2 rounded-lg bg-slate-900 border border-slate-800 text-white placeholder-slate-600 text-sm font-bold text-center focus:border-sky-500 focus:outline-none transition font-mono"
                        />
                    </div>
                    <div v-if="form.errors.captcha_answer" class="text-rose-400 text-xs font-semibold mt-1 text-center">
                        {{ form.errors.captcha_answer }}
                    </div>
                </div>

                <div v-if="(form.errors as any).security" class="p-3 rounded-xl bg-rose-950/80 border border-rose-800 text-rose-300 text-xs text-center font-semibold">
                    {{ (form.errors as any).security }}
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full py-3 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs shadow-md transition cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    <span>{{ form.processing ? 'Doğrulanıyor...' : '2FA ile Doğrula & Giriş Yap' }}</span>
                </button>
            </form>

            <div class="text-center pt-2 border-t border-slate-800">
                <a href="/login" class="text-xs text-slate-400 hover:text-white transition inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Standart ERP Giriş Ekranına Dön</span>
                </a>
            </div>

        </div>

        <div v-if="showQrModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
            <div class="w-full max-w-sm bg-slate-900 border border-slate-800 rounded-2xl p-6 text-center space-y-4 shadow-2xl text-slate-100">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <h3 class="text-sm font-bold text-white">Google Authenticator Kurulumu</h3>
                    <button type="button" @click="showQrModal = false" class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <p class="text-xs text-slate-400">
                    Telefonunuzdaki Google Authenticator, Microsoft Authenticator veya 2FA uygulamasını açıp aşağıdaki QR kodu taratın:
                </p>

                <div class="p-3 bg-white rounded-xl inline-block shadow-inner mx-auto">
                    <img :src="qrUrl" alt="2FA QR Code" class="w-48 h-48 mx-auto" />
                </div>

                <div class="p-2.5 rounded-lg bg-slate-950 border border-slate-800 text-left space-y-1">
                    <div class="text-[10px] font-bold text-slate-400 uppercase">Manuel Kurulum Anahtarı (Secret):</div>
                    <div class="text-xs font-mono font-bold text-rose-400 break-all select-all">{{ secretKey }}</div>
                </div>

                <button
                    type="button"
                    @click="showQrModal = false"
                    class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition cursor-pointer"
                >
                    Anladım / Kapat
                </button>
            </div>
        </div>

    </div>
</template>
