<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>

<template>
    <div class="min-h-screen relative flex flex-col justify-between bg-[#080d0a] text-zinc-200 font-sans selection:bg-[#2e4c38] selection:text-white overflow-x-hidden bg-cover bg-center bg-fixed" style="background-image: url('/loginbackground.jpg');">

        <div class="absolute inset-0 bg-gradient-to-r from-black/85 via-[#0a120e]/75 to-black/85 backdrop-brightness-[0.82] pointer-events-none"></div>

        <header class="relative z-10 w-full max-w-7xl mx-auto px-6 sm:px-10 pt-8 flex items-center justify-between">
            <div class="flex items-center space-x-2.5 px-3 py-1.5 rounded-full bg-[#121c15]/80 border border-[#243529] backdrop-blur-sm text-xs font-medium text-zinc-300 shadow-sm">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#5ba06d] opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-[#4e7d5a]"></span>
                </span>
                <span>SASA ERP Portal</span>
            </div>
            <div class="text-xs text-zinc-400 font-medium tracking-wide">
                v1.0 Kurumsal Sürüm
            </div>
        </header>

        <main class="relative z-10 w-full max-w-7xl mx-auto px-6 sm:px-10 py-10 flex flex-col lg:flex-row items-center justify-between gap-12 lg:gap-16 my-auto">

            <div class="flex flex-col items-center lg:items-start text-center lg:text-left space-y-6 flex-1 max-w-xl">
                <ApplicationLogo class="w-72 sm:w-80 md:w-96 h-auto drop-shadow-[0_20px_35px_rgba(0,0,0,0.7)] hover:scale-[1.02] transition-transform duration-300" />
                
                <div class="space-y-2">
                    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white drop-shadow">
                        SASA TARIM ERP
                    </h1>
                    <p class="text-sm sm:text-base text-zinc-300/90 font-normal leading-relaxed max-w-md">
                        Saha, parsel, gübreleme reçeteleri ve iş gücü operasyonlarının merkezi yönetim platformu.
                    </p>
                </div>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2 pt-1">
                    <span class="px-3 py-1 rounded-lg bg-[#141e17]/80 border border-[#26382c] text-xs text-zinc-300 font-medium">
                        Parsel & Hasat
                    </span>
                    <span class="px-3 py-1 rounded-lg bg-[#141e17]/80 border border-[#26382c] text-xs text-zinc-300 font-medium">
                        Reçete & Tank
                    </span>
                    <span class="px-3 py-1 rounded-lg bg-[#141e17]/80 border border-[#26382c] text-xs text-zinc-300 font-medium">
                        Puantaj & İş Gücü
                    </span>
                    <span class="px-3 py-1 rounded-lg bg-[#141e17]/80 border border-[#26382c] text-xs text-zinc-300 font-medium">
                        Akıllı Otomasyon
                    </span>
                </div>
            </div>

            <div class="w-full max-w-md flex-shrink-0">
                <slot />
            </div>

        </main>

        <footer class="relative z-10 w-full max-w-7xl mx-auto px-6 sm:px-10 pb-8 text-xs text-zinc-400 flex flex-col sm:flex-row items-center justify-between gap-2">
            <span>© 2026 SASA Tarım A.Ş. Tüm hakları saklıdır.</span>
            <div class="flex items-center space-x-4 text-zinc-400">
                <span>Gizlilik & Güvenlik</span>
                <span>•</span>
                <span>Destek Merkezi</span>
            </div>
        </footer>
    </div>
</template>
