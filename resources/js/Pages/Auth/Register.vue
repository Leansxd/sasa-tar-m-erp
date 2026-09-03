<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const showPassword = ref(false);
const showConfirm = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const passwordStrength = computed(() => {
    const p = form.password;
    if (!p) return { score: 0, label: '', color: '' };
    let score = 0;
    if (p.length >= 8) score++;
    if (/[A-Z]/.test(p)) score++;
    if (/[0-9]/.test(p)) score++;
    if (/[^A-Za-z0-9]/.test(p)) score++;
    const levels = [
        { label: 'Çok Zayıf', color: 'bg-rose-500' },
        { label: 'Zayıf', color: 'bg-orange-500' },
        { label: 'Orta', color: 'bg-amber-400' },
        { label: 'Güçlü', color: 'bg-emerald-500' },
        { label: 'Çok Güçlü', color: 'bg-emerald-400' },
    ];
    return { score, ...levels[score] };
});

const passwordsMatch = computed(() =>
    form.password_confirmation.length > 0 &&
    form.password === form.password_confirmation
);

const passwordMismatch = computed(() =>
    form.password_confirmation.length > 0 &&
    form.password !== form.password_confirmation
);

const submit = () => {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Kayıt Ol - SASA Tarım ERP" />

        <div class="bg-[#0f1712]/92 border border-[#223328] rounded-2xl p-8 sm:p-10 shadow-[0_20px_50px_rgba(0,0,0,0.6)] relative backdrop-blur-md">

            <div class="mb-7">
                <h3 class="text-2xl font-bold text-white tracking-tight">Kullanıcı Kaydı</h3>
                <p class="text-xs text-zinc-400 mt-1">Yeni hesap oluşturmak için formu doldurun</p>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <label for="name" class="block text-xs font-semibold text-zinc-300 uppercase tracking-wider mb-2">Ad Soyad</label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <input
                            id="name"
                            type="text"
                            v-model="form.name"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="Ad Soyad"
                            class="block w-full pl-10 pr-4 py-3 bg-[#16221b] border border-[#263a2d] rounded-xl text-white placeholder-zinc-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#3e6b4a] focus:border-[#3e6b4a] transition-all duration-200"
                        />
                    </div>
                    <InputError class="mt-1.5" :message="form.errors.name" />
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold text-zinc-300 uppercase tracking-wider mb-2">E-Posta Adresi</label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                        </div>
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                            autocomplete="username"
                            placeholder="ornek@sasa.com"
                            class="block w-full pl-10 pr-4 py-3 bg-[#16221b] border border-[#263a2d] rounded-xl text-white placeholder-zinc-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#3e6b4a] focus:border-[#3e6b4a] transition-all duration-200"
                        />
                    </div>
                    <InputError class="mt-1.5" :message="form.errors.email" />
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-zinc-300 uppercase tracking-wider mb-2">Şifre</label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                            placeholder="En az 8 karakter"
                            class="block w-full pl-10 pr-10 py-3 bg-[#16221b] border border-[#263a2d] rounded-xl text-white placeholder-zinc-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#3e6b4a] focus:border-[#3e6b4a] transition-all duration-200"
                        />
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-zinc-400 hover:text-white transition-colors">
                            <svg v-if="!showPassword" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </button>
                    </div>

                    <div v-if="form.password" class="mt-2.5">
                        <div class="flex space-x-1.5 mb-1.5">
                            <div v-for="i in 4" :key="i" class="h-1 flex-1 rounded transition-all" :class="passwordStrength.score >= i ? passwordStrength.color : 'bg-[#1f2f24]'"></div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-zinc-400">Şifre gücü: <span class="font-medium text-white">{{ passwordStrength.label }}</span></span>
                        </div>
                        <div class="mt-1.5 grid grid-cols-2 gap-1 text-xs">
                            <span :class="form.password.length >= 8 ? 'text-[#86a789]' : 'text-zinc-500'">• 8+ karakter</span>
                            <span :class="/[A-Z]/.test(form.password) ? 'text-[#86a789]' : 'text-zinc-500'">• Büyük harf</span>
                            <span :class="/[0-9]/.test(form.password) ? 'text-[#86a789]' : 'text-zinc-500'">• Rakam</span>
                            <span :class="/[^A-Za-z0-9]/.test(form.password) ? 'text-[#86a789]' : 'text-zinc-500'">• Özel karakter</span>
                        </div>
                    </div>

                    <InputError class="mt-1.5" :message="form.errors.password" />
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-zinc-300 uppercase tracking-wider mb-2">Şifre Tekrar</label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <input
                            id="password_confirmation"
                            :type="showConfirm ? 'text' : 'password'"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Şifrenizi tekrar girin"
                            :class="['block w-full pl-10 pr-10 py-3 bg-[#16221b] border rounded-xl text-white placeholder-zinc-500 text-sm focus:outline-none focus:ring-2 transition-all duration-200',
                                passwordMismatch ? 'border-rose-500 focus:ring-rose-500 focus:border-rose-500' :
                                passwordsMatch ? 'border-[#3e6b4a] focus:ring-[#3e6b4a] focus:border-[#3e6b4a]' :
                                'border-[#263a2d] focus:ring-[#3e6b4a] focus:border-[#3e6b4a]']"
                        />
                        <button type="button" @click="showConfirm = !showConfirm" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-zinc-400 hover:text-white transition-colors">
                            <svg v-if="!showConfirm" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </button>
                    </div>

                    <div v-if="form.password_confirmation" class="mt-1.5 text-xs">
                        <span :class="passwordsMatch ? 'text-[#86a789]' : 'text-rose-400'">
                            {{ passwordsMatch ? '✓ Şifreler eşleşiyor' : '✗ Şifreler eşleşmiyor' }}
                        </span>
                    </div>

                    <InputError class="mt-1.5" :message="form.errors.password_confirmation" />
                </div>

                <div class="pt-2 space-y-3">
                    <button
                        type="submit"
                        :disabled="form.processing || passwordMismatch"
                        class="w-full flex justify-center items-center py-3.5 px-4 border border-[#3b5e44] rounded-xl text-sm font-bold text-white bg-[#223e2b] hover:bg-[#2b4e37] active:bg-[#1c3323] focus:outline-none focus:ring-2 focus:ring-[#3e6b4a] shadow-lg transition-all duration-150 disabled:opacity-50"
                    >
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ form.processing ? 'Kayıt Yapılıyor...' : 'Kayıt Ol' }}</span>
                    </button>

                    <div class="text-center pt-1">
                        <span class="text-xs text-zinc-400">Zaten hesabınız var mı? </span>
                        <Link :href="route('login')" class="text-xs text-[#86a789] hover:underline font-medium">
                            Giriş Yap
                        </Link>
                    </div>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
