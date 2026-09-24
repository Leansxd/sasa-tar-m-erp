<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>

<template>
    <div class="min-h-screen w-full flex relative bg-cover bg-center font-sans text-zinc-200 selection:bg-[#4ade80]/30 selection:text-white" style="background-image: url('/loginbackground.webp');">

        <!-- Global Dark Background Dimmer -->
        <div class="absolute inset-0 bg-[#030604]/45 pointer-events-none"></div>

        <div class="hidden lg:flex flex-col justify-center flex-1 relative h-screen overflow-hidden select-none">
            <div class="absolute inset-0 bg-gradient-to-r from-[#030604]/99 via-[#030604]/90 to-[#030604]/55"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-[#030604]/95 via-transparent to-[#030604]/65"></div>
            
            <!-- Radial Aura Lighting -->
            <div class="absolute top-1/2 left-1/3 -translate-x-1/2 -translate-y-1/2 w-[540px] h-[540px] bg-gradient-to-tr from-emerald-500/15 via-amber-500/10 to-teal-500/15 blur-[130px] rounded-full pointer-events-none"></div>

            <div class="relative z-10 w-full max-w-3xl ml-6 lg:ml-12 xl:ml-20 px-6 flex flex-col items-center text-center">
                <ApplicationLogo class="w-[360px] lg:w-[420px] xl:w-[480px] max-w-full max-h-[40vh] h-auto drop-shadow-[0_25px_60px_rgba(0,0,0,0.95)] mb-3 transition-transform duration-300 hover:scale-[1.03]" />

                <h1 class="sr-only">SASA TARIM ERP</h1>
                
                <p class="text-lg xl:text-xl font-semibold text-white leading-relaxed max-w-xl drop-shadow-[0_4px_20px_rgba(0,0,0,1)] mb-8 tracking-wide">
                    Saha, parsel, gübreleme reçeteleri ve iş gücü operasyonlarının 
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-300 via-teal-200 to-amber-300 font-bold">merkezi yönetim platformu</span>.
                </p>
                
                <!-- 2x2 Glassmorphic Feature Grid -->
                <div class="grid grid-cols-2 gap-3.5 max-w-md w-full">
                    <div class="group inline-flex items-center justify-center gap-3 px-4 py-3.5 rounded-xl bg-gradient-to-b from-white/10 to-white/[0.03] border border-white/15 backdrop-blur-xl text-xs xl:text-sm font-semibold text-white shadow-[0_8px_32px_rgba(0,0,0,0.4)] transition-all duration-300 hover:border-emerald-400/50 hover:shadow-[0_0_25px_rgba(52,211,153,0.25)] hover:-translate-y-0.5">
                        <div class="p-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 group-hover:bg-emerald-500/20 transition-colors">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                            </svg>
                        </div>
                        <span>Parsel & Hasat</span>
                    </div>
                    
                    <div class="group inline-flex items-center justify-center gap-3 px-4 py-3.5 rounded-xl bg-gradient-to-b from-white/10 to-white/[0.03] border border-white/15 backdrop-blur-xl text-xs xl:text-sm font-semibold text-white shadow-[0_8px_32px_rgba(0,0,0,0.4)] transition-all duration-300 hover:border-amber-400/50 hover:shadow-[0_0_25px_rgba(251,191,36,0.25)] hover:-translate-y-0.5">
                        <div class="p-1.5 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-400 group-hover:bg-amber-500/20 transition-colors">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                            </svg>
                        </div>
                        <span>Reçete & Tank</span>
                    </div>
                    
                    <div class="group inline-flex items-center justify-center gap-3 px-4 py-3.5 rounded-xl bg-gradient-to-b from-white/10 to-white/[0.03] border border-white/15 backdrop-blur-xl text-xs xl:text-sm font-semibold text-white shadow-[0_8px_32px_rgba(0,0,0,0.4)] transition-all duration-300 hover:border-sky-400/50 hover:shadow-[0_0_25px_rgba(56,189,248,0.25)] hover:-translate-y-0.5">
                        <div class="p-1.5 rounded-lg bg-sky-500/10 border border-sky-500/20 text-sky-400 group-hover:bg-sky-500/20 transition-colors">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <span>Puantaj & İş Gücü</span>
                    </div>
                    
                    <div class="group inline-flex items-center justify-center gap-3 px-4 py-3.5 rounded-xl bg-gradient-to-b from-white/10 to-white/[0.03] border border-white/15 backdrop-blur-xl text-xs xl:text-sm font-semibold text-white shadow-[0_8px_32px_rgba(0,0,0,0.4)] transition-all duration-300 hover:border-emerald-400/50 hover:shadow-[0_0_25px_rgba(52,211,153,0.25)] hover:-translate-y-0.5">
                        <div class="p-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 group-hover:bg-emerald-500/20 transition-colors">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <span>Akıllı Otomasyon</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full lg:w-[480px] xl:w-[540px] flex flex-col bg-[#060b08]/85 backdrop-blur-2xl border-l border-white/10 relative z-20 shadow-[0_0_50px_rgba(0,0,0,0.8)] min-h-screen">
            <div class="w-full max-w-[370px] mx-auto px-6 flex-1 flex flex-col justify-center relative z-10">
                <div class="lg:hidden mb-10 flex justify-center">
                    <ApplicationLogo class="w-56 h-auto" />
                </div>
                
                <slot />
            </div>

            <div class="mt-auto w-full p-8 flex items-center justify-between text-xs text-zinc-400 font-medium border-t border-white/10">
                <p>© 2026 SASA Tarım A.Ş.</p>
                <div class="flex space-x-4">
                    <span class="hover:text-white transition-colors cursor-pointer">Gizlilik</span>
                    <span class="hover:text-white transition-colors cursor-pointer">Destek</span>
                </div>
            </div>
        </div>

    </div>
</template>
