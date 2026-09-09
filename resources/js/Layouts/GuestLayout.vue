<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>

<template>
    <div class="min-h-screen w-full flex bg-[#060907] font-sans text-zinc-200 selection:bg-[#4ade80]/30 selection:text-white">

        <!-- Left Image Section -->
        <div class="hidden lg:flex flex-col justify-center flex-1 relative bg-cover bg-center" style="background-image: url('/loginbackground.jpg');">
            <!-- Overlay -->
            <div class="absolute inset-0 bg-gradient-to-r from-[#060907]/95 via-[#060907]/70 to-transparent"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-[#060907] via-transparent to-transparent"></div>
            
            <div class="relative z-10 p-12 xl:p-16 w-full max-w-4xl">
                <!-- Massive Logo -->
                <ApplicationLogo class="w-full max-w-[480px] xl:max-w-[600px] h-auto drop-shadow-2xl mb-8" />

                <h1 class="text-5xl xl:text-6xl font-bold text-white tracking-tight mb-4 drop-shadow-md">
                    SASA TARIM ERP
                </h1>
                
                <p class="text-lg text-zinc-300 font-medium leading-relaxed max-w-lg mb-8">
                    Saha, parsel, gübreleme reçeteleri ve iş gücü operasyonlarının merkezi yönetim platformu.
                </p>
                
                <div class="flex flex-wrap items-center gap-3">
                    <span class="px-3 py-1.5 rounded-lg bg-black/40 border border-white/10 backdrop-blur-md text-sm text-zinc-300 font-medium">🌱 Parsel & Hasat</span>
                    <span class="px-3 py-1.5 rounded-lg bg-black/40 border border-white/10 backdrop-blur-md text-sm text-zinc-300 font-medium">🧪 Reçete & Tank</span>
                    <span class="px-3 py-1.5 rounded-lg bg-black/40 border border-white/10 backdrop-blur-md text-sm text-zinc-300 font-medium">👥 Puantaj & İş Gücü</span>
                    <span class="px-3 py-1.5 rounded-lg bg-black/40 border border-white/10 backdrop-blur-md text-sm text-zinc-300 font-medium">⚡ Akıllı Otomasyon</span>
                </div>
            </div>
        </div>

        <!-- Right Form Section -->
        <div class="w-full lg:w-[480px] xl:w-[540px] flex flex-col bg-[#0a0f0c] border-l border-[#1a261e] relative z-20 shadow-2xl min-h-screen">
            <div class="w-full max-w-[360px] mx-auto px-6 flex-1 flex flex-col justify-center relative z-10">
                <!-- Mobile Logo -->
                <div class="lg:hidden mb-12 flex justify-center">
                    <ApplicationLogo class="w-56 h-auto" />
                </div>
                
                <slot />
            </div>

            <!-- Footer inside sidebar -->
            <div class="mt-auto w-full p-8 flex items-center justify-between text-xs text-zinc-500 font-medium border-t border-[#1a261e]">
                <p>© 2026 SASA Tarım A.Ş.</p>
                <div class="flex space-x-4">
                    <span class="hover:text-white transition-colors cursor-pointer">Gizlilik</span>
                    <span class="hover:text-white transition-colors cursor-pointer">Destek</span>
                </div>
            </div>
        </div>

    </div>
</template>
