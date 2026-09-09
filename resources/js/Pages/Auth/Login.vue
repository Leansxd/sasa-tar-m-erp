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
            <div class="mb-10">
                <h2 class="text-3xl font-bold text-white tracking-tight mb-2">Hoş Geldiniz</h2>
                <p class="text-sm text-zinc-400 font-medium">Hesabınıza giriş yaparak operasyonları yönetmeye başlayın.</p>
            </div>

            <div v-if="status" class="mb-6 p-4 rounded-xl bg-[#22c55e]/10 border border-[#22c55e]/20 text-sm font-medium text-[#4ade80] flex items-center space-x-3">
                <span>{{ status }}</span>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <label for="email" class="block text-xs font-semibold text-zinc-400 mb-2">
                        E-Posta Adresi
                    </label>
                    <input
                        id="email"
                        type="email"
                        class="block w-full px-4 py-3 bg-[#131c16] border border-[#203025] rounded-lg text-white placeholder-zinc-600 text-sm focus:outline-none focus:ring-1 focus:ring-[#4ade80] focus:border-[#4ade80] transition-all duration-200"
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
                        <label for="password" class="block text-xs font-semibold text-zinc-400">
                            Şifre
                        </label>
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-xs font-medium text-[#4ade80] hover:text-[#22c55e] transition-colors"
                        >
                            Şifremi Unuttum
                        </Link>
                    </div>
                    <div class="relative">
                        <input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            class="block w-full px-4 py-3 bg-[#131c16] border border-[#203025] rounded-lg text-white placeholder-zinc-600 text-sm focus:outline-none focus:ring-1 focus:ring-[#4ade80] focus:border-[#4ade80] transition-all duration-200"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-zinc-500 hover:text-white transition-colors"
                        >
                            <span class="text-xs font-medium">{{ showPassword ? 'Gizle' : 'Göster' }}</span>
                        </button>
                    </div>
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="flex items-center justify-between pt-2">
                    <label class="flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="form.remember"
                            class="w-4 h-4 rounded border-[#203025] bg-[#131c16] text-[#4ade80] focus:ring-0 focus:ring-offset-0 cursor-pointer"
                        />
                        <span class="ms-3 text-sm text-zinc-400 font-medium hover:text-white transition-colors">Beni Hatırla</span>
                    </label>
                </div>

                <div class="pt-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full flex justify-center items-center py-3.5 px-4 rounded-lg text-sm font-semibold text-black bg-[#4ade80] hover:bg-[#22c55e] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-[#0a0f0c] focus:ring-[#4ade80] transition-all duration-200 disabled:opacity-50"
                    >
                        <span>{{ form.processing ? 'Giriş Yapılıyor...' : 'Giriş Yap' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
