<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const page = usePage();
const permissions = computed(() => (page.props.auth as any)?.permissions || []);
const isAdmin = computed(() => !!(page.props.auth as any)?.is_admin);

const hasPermission = (mod: string) => isAdmin.value || permissions.value.includes(mod);
const canSeeAgriculture = computed(() => isAdmin.value || ['tesis', 'uretim', 'teknik', 'operasyon', 'raporlar'].some(m => permissions.value.includes(m)));

const flashSuccess = computed(() => (page.props.flash as any)?.success);
const flashError = computed(() => (page.props.flash as any)?.error || (page.props.errors as any)?.error);
const showFlash = ref(true);

watch([flashSuccess, flashError], () => {
    showFlash.value = true;
    if (flashSuccess.value) {
        setTimeout(() => {
            showFlash.value = false;
        }, 4000);
    }
});

const isMobileMenuOpen = ref(false);
const isDark = ref(false);

const agricultureMenu = [
    {
        category: 'tesis',
        title: 'Tesis Yönetimi',
        items: [
            { key: 'is_planlama', label: 'İş Planlama' }
        ]
    },
    {
        category: 'uretim',
        title: 'Üretim',
        items: [
            { key: 'gubreleme', label: 'Gübreleme' },
            { key: 'sulama', label: 'Sulama' },
            { key: 'ilaclama', label: 'İlaçlama' },
            { key: 'su_analizleri', label: 'Su Analizleri' }
        ]
    },
    {
        category: 'teknik',
        title: 'Teknik',
        items: [
            { key: 'kaynak_suyu_kontrol', label: 'Kaynak Suyu Kontrol' },
            { key: 'aritma_suyu_kontrol', label: 'Arıtma Suyu Kontrol' }
        ]
    },
    {
        category: 'operasyon',
        title: 'Operasyon',
        items: [
            { key: 'alinan_siparis', label: 'Alınan Sipariş' },
            { key: 'sevkiyat_teslimat', label: 'Sevkiyat & Teslimat' },
            { key: 'gunluk_isci_formu', label: 'Günlük İşçi Formu' },
            { key: 'isci_ve_cavuslar', label: 'İşçi & Çavuş Yönetimi' }
        ]
    },
    {
        category: 'raporlar',
        title: 'Raporlar',
        items: [
            { key: 'piyasa_fiyatlari', label: 'Piyasa Fiyatları' },
            { key: 'hasat_miktarlari', label: 'Hasat Miktarları' },
            { key: 'isci_maliyet_analizi', label: 'İşçi Maliyet Analizi' },
            { key: 'stok_inceleme', label: 'Stok İnceleme' }
        ]
    }
];

const definitionsMenu = [
    {
        title: 'Firma & Tesis',
        items: [
            { key: 'companies', label: 'Firma Listesi' },
            { key: 'production_locations', label: 'Üretim Yeri & Vana' },
            { key: 'trading_parties', label: 'Alıcı & Satıcı Carileri' },
            { key: 'delivery_types', label: 'Teslimat Şekilleri' }
        ]
    },
    {
        title: 'Personel & Ekip',
        items: [
            { key: 'personnels', label: 'Personel Hiyerarşisi' },
            { key: 'crew_leaders', label: 'Çavuşlar (Ekip Başı)' },
            { key: 'workers', label: 'İşçiler' },
            { key: 'catering_suppliers', label: 'Yemek Tedarikçileri' }
        ]
    },
    {
        title: 'Ürün & Üretim',
        items: [
            { key: 'products', label: 'Ürünler & Kalite Sınıfları' },
            { key: 'job_types', label: 'İş Tanımları & Formlar' },
            { key: 'units', label: 'Birim Tanımları' },
            { key: 'packagings', label: 'Paketleme Şekilleri' }
        ]
    },
    {
        title: 'Reçete & Altyapı',
        items: [
            { key: 'recipes', label: 'Gübre & İlaç Reçeteleri' },
            { key: 'water_and_filters', label: 'Su Kaynakları & Filtreler' }
        ]
    }
];

const filteredAgricultureMenu = computed(() => {
    if (isAdmin.value) return agricultureMenu;
    return agricultureMenu.filter(g => permissions.value.includes(g.category));
});

const activeDropdown = ref<string | null>(null);
let closeTimer: any = null;

const openMenu = (menuKey: string) => {
    if (closeTimer) clearTimeout(closeTimer);
    activeDropdown.value = menuKey;
};

const scheduleCloseMenu = () => {
    if (closeTimer) clearTimeout(closeTimer);
    closeTimer = setTimeout(() => {
        activeDropdown.value = null;
    }, 200);
};

const toggleMenu = (menuKey: string) => {
    if (activeDropdown.value === menuKey) {
        activeDropdown.value = null;
    } else {
        openMenu(menuKey);
    }
};

const navigateAgriculture = (cat: string, mod: string) => {
    activeDropdown.value = null;
    router.get(route('agriculture.index'), { cat, mod });
};

const navigateDefinitions = (tabKey: string) => {
    activeDropdown.value = null;
    router.get(route('definitions.index'), { tab: tabKey });
};

const isCurrentAgricultureMod = (modKey: string) => {
    if (!route().current('agriculture.*')) return false;
    const urlParams = new URLSearchParams(window.location.search);
    const currentMod = urlParams.get('mod') || 'is_planlama';
    return currentMod === modKey;
};

const isCurrentDefinitionTab = (tabKey: string) => {
    if (!route().current('definitions.*')) return false;
    const urlParams = new URLSearchParams(window.location.search);
    const currentTab = urlParams.get('tab') || 'companies';
    return currentTab === tabKey;
};

const toggleTheme = () => {
    isDark.value = !isDark.value;
    if (isDark.value) {
        document.documentElement.classList.add('dark');
        document.body.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        document.body.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

onMounted(() => {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        isDark.value = true;
        document.documentElement.classList.add('dark');
        document.body.classList.add('dark');
    } else {
        isDark.value = false;
        document.documentElement.classList.remove('dark');
        document.body.classList.remove('dark');
    }

    const handleDocClick = (e: MouseEvent) => {
        if (!(e.target as HTMLElement).closest('.group-dropdown-agriculture') && !(e.target as HTMLElement).closest('.group-dropdown-definitions') && !(e.target as HTMLElement).closest('.group-dropdown-user')) {
            activeDropdown.value = null;
        }
    };
    document.addEventListener('click', handleDocClick);
    onUnmounted(() => {
        document.removeEventListener('click', handleDocClick);
    });
});
</script>

<template>
    <div class="min-h-screen bg-slate-100 dark:bg-slate-950 text-slate-800 dark:text-slate-100 flex flex-col font-sans antialiased transition-colors duration-200">
        <!-- EN ÜST ANA HEADER ÇUBUĞU (FULL WIDTH) -->
        <header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between px-4 sm:px-6 lg:px-8 sticky top-0 z-40 transition-colors shadow-2xs">
            <!-- Sol: Logo + Ana Navigasyon Sekmeleri -->
            <div class="flex items-center gap-4 sm:gap-6">
                <!-- Mobil Menü Açma Butonu -->
                <button @click="isMobileMenuOpen = true" class="p-2 lg:hidden text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                <!-- SASA ERP Logo -->
                <Link :href="route('dashboard')" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 flex items-center justify-center group-hover:scale-105 transition">
                        <img src="/sasaerp.svg" alt="SASA ERP Logo" class="w-full h-full object-contain drop-shadow-xs" />
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs font-black tracking-wider text-slate-900 dark:text-slate-100 uppercase">SASA ERP</span>
                        <span class="text-[9px] font-semibold text-slate-400 -mt-0.5">TARIM & İŞLETME</span>
                    </div>
                </Link>

                <!-- Ana Navigasyon Sekmeleri (Masaüstü) -->
                <nav class="hidden lg:flex items-center gap-1.5 border-l border-slate-200 dark:border-slate-800 pl-4 sm:pl-5">
                    <!-- 1. Ana Menü -->
                    <Link
                        :href="route('dashboard')"
                        :class="[
                            'px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5',
                            route().current('dashboard')
                                ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 font-extrabold border border-rose-200/60 dark:border-rose-900/50 shadow-2xs'
                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/80'
                        ]"
                    >
                        <svg class="w-4 h-4 text-sky-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/>
                        </svg>
                        <span>Ana Menü</span>
                    </Link>

                    <!-- 2. Tarım Operasyonları (Sekme Sekme Açılır Dropdown) -->
                    <div
                        v-if="canSeeAgriculture"
                        class="relative group-dropdown-agriculture"
                        @mouseenter="openMenu('agriculture')"
                        @mouseleave="scheduleCloseMenu"
                    >
                        <button
                            type="button"
                            @click="toggleMenu('agriculture')"
                            :class="[
                                'px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer',
                                route().current('agriculture.*')
                                    ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 font-extrabold border border-emerald-200/60 dark:border-emerald-900/50 shadow-2xs'
                                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/80'
                            ]"
                        >
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M7 20h10"/><path d="M12 20v-8"/><path d="M12 12a5 5 0 0 1 8-3.5 5 5 0 0 1-3.5 8.5H12"/><path d="M12 12a5 5 0 0 0-8-3.5 5 5 0 0 0 3.5 8.5H12"/>
                            </svg>
                            <span>Tarım Operasyonları</span>
                            <svg class="w-3.5 h-3.5 transition-transform duration-200 text-slate-400" :class="activeDropdown === 'agriculture' ? 'rotate-180 text-emerald-500' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Tarım Dropdown Kartı (Sekme Sekme Gruplu) -->
                        <div
                            v-show="activeDropdown === 'agriculture'"
                            class="absolute left-0 mt-1.5 w-[760px] bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl p-5 z-50 transition-all duration-150"
                        >
                            <div class="grid grid-cols-5 gap-4">
                                <div v-for="group in filteredAgricultureMenu" :key="group.category" class="space-y-2.5">
                                    <div class="flex items-center gap-1.5 pb-2 border-b border-slate-100 dark:border-slate-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <h3 class="text-[11px] font-black uppercase tracking-wider text-slate-800 dark:text-slate-200">
                                            {{ group.title }}
                                        </h3>
                                    </div>
                                    <ul class="space-y-1">
                                        <li v-for="item in group.items" :key="item.key">
                                            <button
                                                type="button"
                                                @click="navigateAgriculture(group.category, item.key)"
                                                :class="[
                                                    'w-full text-left px-2.5 py-1.5 rounded-lg text-xs font-medium transition flex items-center justify-between cursor-pointer group',
                                                    isCurrentAgricultureMod(item.key)
                                                        ? 'bg-emerald-50 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 font-bold'
                                                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800'
                                                ]"
                                            >
                                                <span>{{ item.label }}</span>
                                                <span v-if="isCurrentAgricultureMod(item.key)" class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Sistem Tanımlamaları (Sekme Sekme Açılır Dropdown) -->
                    <div
                        v-if="hasPermission('tanimlamalar')"
                        class="relative group-dropdown-definitions"
                        @mouseenter="openMenu('definitions')"
                        @mouseleave="scheduleCloseMenu"
                    >
                        <button
                            type="button"
                            @click="toggleMenu('definitions')"
                            :class="[
                                'px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer',
                                route().current('definitions.*')
                                    ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 font-extrabold border border-rose-200/60 dark:border-rose-900/50 shadow-2xs'
                                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/80'
                            ]"
                        >
                            <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                            <span>Sistem Tanımlamaları</span>
                            <svg class="w-3.5 h-3.5 transition-transform duration-200 text-slate-400" :class="activeDropdown === 'definitions' ? 'rotate-180 text-rose-500' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Tanımlamalar Dropdown Kartı (Sekme Sekme Gruplu) -->
                        <div
                            v-show="activeDropdown === 'definitions'"
                            class="absolute left-0 mt-1.5 w-[720px] bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl p-5 z-50 transition-all duration-150"
                        >
                            <div class="grid grid-cols-4 gap-4">
                                <div v-for="group in definitionsMenu" :key="group.title" class="space-y-2.5">
                                    <div class="flex items-center gap-1.5 pb-2 border-b border-slate-100 dark:border-slate-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        <h3 class="text-[11px] font-black uppercase tracking-wider text-slate-800 dark:text-slate-200">
                                            {{ group.title }}
                                        </h3>
                                    </div>
                                    <ul class="space-y-1">
                                        <li v-for="item in group.items" :key="item.key">
                                            <button
                                                type="button"
                                                @click="navigateDefinitions(item.key)"
                                                :class="[
                                                    'w-full text-left px-2.5 py-1.5 rounded-lg text-xs font-medium transition flex items-center justify-between cursor-pointer group',
                                                    isCurrentDefinitionTab(item.key)
                                                        ? 'bg-rose-50 dark:bg-rose-950/70 text-rose-600 dark:text-rose-400 font-bold'
                                                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800'
                                                ]"
                                            >
                                                <span>{{ item.label }}</span>
                                                <span v-if="isCurrentDefinitionTab(item.key)" class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>

            <!-- Sağ: Tema Değiştirici & Kullanıcı Menüsü -->
            <div class="flex items-center space-x-2 sm:space-x-4">
                <button
                    @click="toggleTheme"
                    type="button"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition text-xs font-semibold shadow-xs cursor-pointer"
                    title="Tema Değiştir (Açık / Koyu)"
                >
                    <svg v-if="isDark" class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg v-else class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <span class="text-[11px] font-medium hidden md:inline">{{ isDark ? 'Açık Mod' : 'Koyu Mod' }}</span>
                </button>

                <div class="relative group-dropdown-user flex items-center border-l border-slate-200 dark:border-slate-800 pl-2 sm:pl-4">
                    <button
                        @click="toggleMenu('user')"
                        type="button"
                        class="flex items-center space-x-2 sm:space-x-3 text-left focus:outline-none transition group cursor-pointer"
                    >
                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-orange-100 dark:bg-orange-950/60 text-orange-700 dark:text-orange-400 font-extrabold flex items-center justify-center text-sm border border-orange-200 dark:border-orange-900/50 group-hover:bg-orange-200 dark:group-hover:bg-orange-900/80 transition">
                            {{ $page.props.auth.user.name.charAt(0) }}
                        </div>
                        <div class="text-left hidden sm:block">
                            <span class="text-[10px] text-slate-400 block font-semibold leading-tight">Hoşgeldin,</span>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-100 block leading-tight group-hover:text-slate-900 dark:group-hover:text-white transition">{{ $page.props.auth.user.name }}</span>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-200 transition" :class="activeDropdown === 'user' ? 'rotate-180 text-orange-500' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div
                        v-show="activeDropdown === 'user'"
                        class="absolute right-0 top-full mt-2 w-48 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xl py-1.5 z-50 transition-all duration-150"
                    >
                        <div class="px-3.5 py-2 border-b border-slate-100 dark:border-slate-800">
                            <div class="text-[11px] font-bold text-slate-800 dark:text-slate-200 truncate">{{ $page.props.auth.user.name }}</div>
                            <div class="text-[10px] text-slate-400 truncate">{{ $page.props.auth.user.email }}</div>
                        </div>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="w-full text-left px-3.5 py-2 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/60 transition flex items-center gap-2 cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span>Çıkış Yap</span>
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- SAYFA BAŞLIĞI VEYA ALT KATEGORİ ÇUBUĞU (SUB-HEADER) -->
        <div v-if="$slots.header" class="px-4 sm:px-6 lg:px-8 pt-6 pb-2 z-30">
            <slot name="header" />
        </div>

        <div v-if="showFlash && (flashSuccess || flashError)" class="fixed top-5 right-5 z-50 max-w-md w-full transition-all duration-300">
            <div v-if="flashSuccess" class="flex items-center gap-3 p-4 rounded-xl bg-emerald-600 text-white shadow-xl">
                <svg class="w-6 h-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <div class="text-sm font-medium flex-1">{{ flashSuccess }}</div>
                <button @click="showFlash = false" class="p-1 hover:bg-emerald-700 rounded-lg">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div v-if="flashError" class="flex items-center gap-3 p-4 rounded-xl bg-rose-600 text-white shadow-xl">
                <svg class="w-6 h-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="text-sm font-medium flex-1">{{ flashError }}</div>
                <button @click="showFlash = false" class="p-1 hover:bg-rose-700 rounded-lg">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>

        <main class="flex-1 p-4 sm:p-6 lg:p-8 min-w-0">
            <slot />
        </main>

        <!-- MOBİL ÇEKMECE MENÜSÜ -->
        <div v-if="isMobileMenuOpen" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex lg:hidden">
            <div class="w-4/5 max-w-xs bg-white dark:bg-slate-900 h-full p-5 flex flex-col justify-between overflow-y-auto shadow-2xl">
                <div>
                    <div class="flex justify-between items-center pb-4 mb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <img src="/sasaerp.svg" alt="SASA ERP Logo" class="w-8 h-8 object-contain" />
                            <span class="font-black text-sm text-slate-800 dark:text-slate-100">SASA ERP</span>
                        </div>
                        <button @click="isMobileMenuOpen = false" class="text-slate-400 hover:text-slate-700 text-2xl font-bold">&times;</button>
                    </div>

                    <nav class="space-y-2 mb-6">
                        <Link
                            :href="route('dashboard')"
                            @click="isMobileMenuOpen = false"
                            :class="[
                                'flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition',
                                route().current('dashboard') ? 'bg-rose-50 dark:bg-rose-950 text-rose-600 dark:text-rose-400' : 'text-slate-600 dark:text-slate-400'
                            ]"
                        >
                            <svg class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/>
                            </svg>
                            <span>Ana Menü</span>
                        </Link>
                        <Link
                            v-if="canSeeAgriculture"
                            :href="route('agriculture.index')"
                            @click="isMobileMenuOpen = false"
                            :class="[
                                'flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition',
                                route().current('agriculture.*') ? 'bg-rose-50 dark:bg-rose-950 text-rose-600 dark:text-rose-400' : 'text-slate-600 dark:text-slate-400'
                            ]"
                        >
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M7 20h10"/><path d="M12 20v-8"/><path d="M12 12a5 5 0 0 1 8-3.5 5 5 0 0 1-3.5 8.5H12"/><path d="M12 12a5 5 0 0 0-8-3.5 5 5 0 0 0 3.5 8.5H12"/></svg>
                            <span>Tarım Operasyonları</span>
                        </Link>
                        <Link
                            v-if="hasPermission('tanimlamalar')"
                            :href="route('definitions.index')"
                            @click="isMobileMenuOpen = false"
                            :class="[
                                'flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition',
                                route().current('definitions.*') ? 'bg-rose-50 dark:bg-rose-950 text-rose-600 dark:text-rose-400' : 'text-slate-600 dark:text-slate-400'
                            ]"
                        >
                            <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                            <span>Sistem Tanımlamaları</span>
                        </Link>
                    </nav>

                    <div v-if="$slots.submenu" class="border-t border-slate-100 dark:border-slate-800 pt-4">
                        <slot name="submenu" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
