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
        
        <div class="w-full">
            <div class="mb-8">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold mb-3">
                    <span>Güvenli Portal Girişi</span>
                </div>
                <h2 class="text-3xl font-bold text-white tracking-tight mb-2">Hoş Geldiniz</h2>
                <p class="text-sm text-zinc-400 font-medium">Hesabınıza giriş yaparak operasyonları yönetmeye başlayın.</p>
            </div>

            <div v-if="status" class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-sm font-medium text-emerald-400 flex items-center space-x-3 backdrop-blur-md">
                <span>{{ status }}</span>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <label for="email" class="block text-xs font-semibold text-zinc-300 mb-2 tracking-wide uppercase">
                        E-Posta Adresi
                    </label>
                    <input
                        id="email"
                        type="email"
                        class="block w-full px-4 py-3 bg-zinc-950/80 border border-white/15 rounded-xl text-white placeholder-zinc-500 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 transition-all duration-200 shadow-inner"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="admin@sasa.com"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-xs font-semibold text-zinc-300 tracking-wide uppercase">
                            Şifre
                        </label>
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-xs font-semibold text-emerald-400 hover:text-emerald-300 transition-colors"
                        >
                            Şifremi Unuttum
                        </Link>
                    </div>
                    <div class="relative">
                        <input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            class="block w-full px-4 py-3 bg-zinc-950/80 border border-white/15 rounded-xl text-white placeholder-zinc-500 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 transition-all duration-200 shadow-inner"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-zinc-400 hover:text-white transition-colors"
                        >
                            <span class="text-xs font-medium">{{ showPassword ? 'Gizle' : 'Göster' }}</span>
                        </button>
                    </div>
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center cursor-pointer group">
                        <input
                            type="checkbox"
                            v-model="form.remember"
                            class="w-4 h-4 rounded border-white/20 bg-zinc-950 text-emerald-500 focus:ring-0 focus:ring-offset-0 cursor-pointer"
                        />
                        <span class="ms-3 text-sm text-zinc-300 font-medium group-hover:text-white transition-colors">Beni Hatırla</span>
                    </label>
                </div>

                <div class="pt-3">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full flex justify-center items-center py-3.5 px-4 rounded-xl text-sm font-bold text-zinc-950 bg-gradient-to-r from-emerald-400 via-emerald-500 to-teal-400 hover:from-emerald-300 hover:to-teal-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-zinc-950 focus:ring-emerald-400 shadow-[0_0_25px_rgba(52,211,153,0.3)] hover:shadow-[0_0_35px_rgba(52,211,153,0.5)] transition-all duration-300 hover:scale-[1.01] active:scale-[0.99] disabled:opacity-50"
                    >
                        <span>{{ form.processing ? 'Giriş Yapılıyor...' : 'Giriş Yap' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
