<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Giriş Yap - SASA Tarım ERP" />
        <div class="bg-[#0f1712]/92 border border-[#223328] rounded-2xl p-8 sm:p-10 shadow-[0_20px_50px_rgba(0,0,0,0.6)] relative backdrop-blur-md">

            <div class="mb-7">
                <h3 class="text-2xl font-bold text-white tracking-tight">Kullanıcı Girişi</h3>
                <p class="text-xs text-zinc-400 mt-1">Sisteme erişmek için bilgilerinizi girin</p>
            </div>

            <div v-if="status" class="mb-6 p-3.5 rounded-xl bg-[#1a281e] border border-[#2e4735] text-xs font-semibold text-[#86a789] flex items-center space-x-2">
                <svg class="w-4 h-4 text-[#86a789] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>{{ status }}</span>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <label for="email" class="block text-xs font-semibold text-zinc-300 uppercase tracking-wider mb-2">
                        E-Posta Adresi
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                        <input
                            id="email"
                            type="email"
                            class="block w-full pl-10 pr-4 py-3 bg-[#16221b] border border-[#263a2d] rounded-xl text-white placeholder-zinc-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#3e6b4a] focus:border-[#3e6b4a] transition-all duration-200"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="admin@sasa.com"
                        />
                    </div>
                    <InputError class="mt-1.5" :message="form.errors.email" />
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-xs font-semibold text-zinc-300 uppercase tracking-wider">
                            Şifre
                        </label>
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-xs text-zinc-400 hover:text-white transition-colors duration-150"
                        >
                            Şifremi Unuttum?
                        </Link>
                    </div>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            class="block w-full pl-10 pr-10 py-3 bg-[#16221b] border border-[#263a2d] rounded-xl text-white placeholder-zinc-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#3e6b4a] focus:border-[#3e6b4a] transition-all duration-200"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-zinc-400 hover:text-white transition-colors"
                        >
                            <svg v-if="!showPassword" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                    <InputError class="mt-1.5" :message="form.errors.password" />
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="form.remember"
                            class="w-4 h-4 rounded border-[#2c3f32] bg-[#16221b] text-[#3e6b4a] focus:ring-0 focus:ring-offset-0 cursor-pointer"
                        />
                        <span class="ms-2.5 text-xs text-zinc-300 font-medium">Beni Hatırla</span>
                    </label>
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full flex justify-center items-center py-3.5 px-4 border border-[#3b5e44] rounded-xl text-sm font-bold text-white bg-[#223e2b] hover:bg-[#2b4e37] active:bg-[#1c3323] focus:outline-none focus:ring-2 focus:ring-[#3e6b4a] shadow-lg transition-all duration-150 disabled:opacity-50"
                    >
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ form.processing ? 'Giriş Yapılıyor...' : 'Giriş Yap' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
