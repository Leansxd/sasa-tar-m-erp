<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps<{
    tenants: any[];
    summaryStats: {
        total_tenants: number;
        active_tenants: number;
        total_users: number;
        total_area_dekar: number;
    };
}>();

const searchQuery = ref('');
const filterStatus = ref<'all' | 'active' | 'passive'>('all');
const viewMode = ref<'table' | 'grid'>('table');

const isCreateModalOpen = ref(false);
const isEditAdminModalOpen = ref(false);
const isLicenseModalOpen = ref(false);
const selectedTenant = ref<any>(null);
const copiedTenantId = ref<number | null>(null);

const createForm = useForm({
    tenant_name: '',
    plan: 'enterprise',
    owner_name: '',
    owner_email: '',
    owner_password: '',
    license_months: '12',
});

const adminEditForm = useForm({
    owner_email: '',
    owner_password: '',
});

const licenseForm = useForm({
    action: 'add_days' as 'add_days' | 'unlimited' | 'custom_date',
    days: 365,
    expires_at: '',
});

const filteredTenants = computed(() => {
    return props.tenants.filter(t => {
        const matchesSearch = 
            t.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            t.slug.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            (t.owner?.email || '').toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            (t.owner?.name || '').toLowerCase().includes(searchQuery.value.toLowerCase());
        
        if (filterStatus.value === 'active') return matchesSearch && t.is_active;
        if (filterStatus.value === 'passive') return matchesSearch && !t.is_active;
        return matchesSearch;
    });
});

const openCreateModal = () => {
    createForm.reset();
    createForm.clearErrors();
    isCreateModalOpen.value = true;
};

const submitCreateTenant = () => {
    createForm.post(route('master-control.tenants.store'), {
        preserveScroll: true,
        onSuccess: () => {
            isCreateModalOpen.value = false;
            createForm.reset();
        }
    });
};

const openEditAdminModal = (tenant: any) => {
    selectedTenant.value = tenant;
    adminEditForm.owner_email = tenant.owner?.email || '';
    adminEditForm.owner_password = '';
    adminEditForm.clearErrors();
    isEditAdminModalOpen.value = true;
};

const submitEditAdmin = () => {
    if (!selectedTenant.value) return;
    adminEditForm.put(route('master-control.tenants.update-admin', selectedTenant.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isEditAdminModalOpen.value = false;
            adminEditForm.reset();
        }
    });
};

const openLicenseModal = (tenant: any) => {
    selectedTenant.value = tenant;
    licenseForm.action = tenant.expires_at ? 'add_days' : 'unlimited';
    licenseForm.days = 365;
    licenseForm.expires_at = tenant.expires_at_raw || '';
    licenseForm.clearErrors();
    isLicenseModalOpen.value = true;
};

const submitLicenseUpdate = () => {
    if (!selectedTenant.value) return;
    licenseForm.put(route('master-control.tenants.update-license', selectedTenant.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isLicenseModalOpen.value = false;
        }
    });
};

const confirmModal = ref<{
    isOpen: boolean;
    title: string;
    description: string;
    confirmText: string;
    cancelText: string;
    type: 'danger' | 'warning' | 'info' | 'primary';
    action: () => void;
}>({
    isOpen: false,
    title: '',
    description: '',
    confirmText: 'Onayla',
    cancelText: 'Vazgeç',
    type: 'primary',
    action: () => {},
});

const openConfirm = (opts: {
    title: string;
    description: string;
    confirmText?: string;
    cancelText?: string;
    type?: 'danger' | 'warning' | 'info' | 'primary';
    action: () => void;
}) => {
    confirmModal.value = {
        isOpen: true,
        title: opts.title,
        description: opts.description,
        confirmText: opts.confirmText || 'Onayla',
        cancelText: opts.cancelText || 'Vazgeç',
        type: opts.type || 'primary',
        action: opts.action,
    };
};

const handleConfirm = () => {
    const fn = confirmModal.value.action;
    confirmModal.value.isOpen = false;
    if (fn) fn();
};

const impersonateTenant = (tenant: any) => {
    openConfirm({
        title: 'ERP Paneline Göz At',
        description: `'${tenant.name}' şirketinin ERP paneline Süper Yönetici yetkisiyle geçiş yapmak istiyor musunuz? Dilediğiniz zaman üst bardan çıkış yapabilirsiniz.`,
        confirmText: 'Panele Giriş Yap',
        type: 'info',
        action: () => {
            router.post(route('master-control.tenants.impersonate', tenant.id));
        }
    });
};

const downloadBackup = (tenant: any) => {
    window.location.href = route('master-control.tenants.backup', tenant.id);
};

const toggleStatus = (tenant: any) => {
    openConfirm({
        title: tenant.is_active ? 'Şirket Erişimini Dondur' : 'Şirket Erişimini Aç',
        description: `'${tenant.name}' şirketinin sisteme erişimini ${tenant.is_active ? 'dondurmak' : 'yeniden aktif hale getirmek'} istediğinize emin misiniz?`,
        confirmText: tenant.is_active ? 'Erişimi Dondur' : 'Erişimi Aç',
        type: tenant.is_active ? 'warning' : 'primary',
        action: () => {
            router.patch(route('master-control.tenants.toggle-status', tenant.id), {}, { preserveScroll: true });
        }
    });
};

const deleteTenant = (tenant: any) => {
    openConfirm({
        title: 'Şirketi Kalıcı Olarak Sil',
        description: `DİKKAT: '${tenant.name}' şirketine ait tüm veritabanı, kullanıcılar, araziler ve kayıtlar kalıcı olarak silinecektir. Bu işlem geri alınamaz!`,
        confirmText: 'Evet, Şirketi Sil',
        type: 'danger',
        action: () => {
            router.delete(route('master-control.tenants.destroy', tenant.id), { preserveScroll: true });
        }
    });
};

const copyCredentials = (tenant: any) => {
    const text = `Şirket: ${tenant.name}\nPanel Giriş: ${window.location.origin}/login\nYönetici: ${tenant.owner?.email || '-'}`;
    navigator.clipboard.writeText(text);
    copiedTenantId.value = tenant.id;
    setTimeout(() => {
        copiedTenantId.value = null;
    }, 2000);
};

const logoutMaster = () => {
    router.post(route('master-control.logout'));
};
</script>

<template>
    <Head title="SASA SaaS Master - Şirket Provizyon Paneli" />

    <div class="min-h-screen bg-[#090d14] text-slate-100 font-sans antialiased pb-12 selection:bg-rose-500 selection:text-white">
        
        <header class="h-16 bg-[#0f141f] border-b border-slate-800/80 px-4 sm:px-6 lg:px-8 flex items-center justify-between sticky top-0 z-40 shadow-sm backdrop-blur-md">
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 flex items-center justify-center">
                        <img src="/sasaerp.svg" alt="SASA ERP Logo" class="w-full h-full object-contain" />
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs font-black tracking-wider text-slate-100 uppercase">SASA ERP</span>
                        <span class="text-[9px] font-bold text-slate-400 -mt-0.5">TARIM & İŞLETME</span>
                    </div>
                </div>

                <nav class="hidden md:flex items-center gap-2 border-l border-slate-800 pl-5">
                    <div class="px-3 py-1.5 rounded-lg bg-rose-950/50 text-rose-400 border border-rose-900/60 text-xs font-extrabold flex items-center gap-2 shadow-2xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span>Müşteri Provizyon Paneli</span>
                    </div>
                    <a href="/login" class="px-3 py-1.5 rounded-lg text-slate-400 hover:text-slate-100 hover:bg-slate-800/60 text-xs font-bold transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        <span>ERP Giriş Ekranı</span>
                    </a>
                </nav>
            </div>

            <div class="flex items-center gap-3">
                <button
                    type="button"
                    @click="openCreateModal"
                    class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs shadow-md transition cursor-pointer flex items-center gap-2 active:scale-95"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Yeni Müşteri Şirketi Aç</span>
                </button>

                <div class="h-6 w-px bg-slate-800 hidden sm:block"></div>

                <div class="flex items-center gap-2.5 pl-1">
                    <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center font-black text-xs text-rose-400">
                        M
                    </div>
                    <div class="hidden sm:flex flex-col text-left">
                        <span class="text-xs font-bold text-slate-200">Süper Admin</span>
                        <span class="text-[10px] text-slate-400 -mt-0.5">Master Root</span>
                    </div>
                    <button
                        type="button"
                        @click="logoutMaster"
                        class="p-2 text-slate-400 hover:text-rose-400 hover:bg-slate-800/80 rounded-xl transition cursor-pointer"
                        title="Master Oturumunu Kapat"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 space-y-7">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">Müşteri Şirketleri & Bağımsız Hesaplar</h1>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1">Yazılım sattığınız şirketlerin bağımsız veritabanı alanlarını ve lisanslarını yönetin.</p>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="filterStatus = 'all'"
                        :class="[
                            'px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer',
                            filterStatus === 'all' ? 'bg-rose-600 text-white shadow' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white'
                        ]"
                    >
                        Tümü ({{ props.tenants.length }})
                    </button>
                    <button
                        type="button"
                        @click="filterStatus = 'active'"
                        :class="[
                            'px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer',
                            filterStatus === 'active' ? 'bg-emerald-600 text-white shadow' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white'
                        ]"
                    >
                        Aktifler
                    </button>
                    <button
                        type="button"
                        @click="filterStatus = 'passive'"
                        :class="[
                            'px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer',
                            filterStatus === 'passive' ? 'bg-red-600 text-white shadow' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white'
                        ]"
                    >
                        Dondurulanlar
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <div class="p-5 rounded-2xl bg-[#0f141f] border border-slate-800/90 shadow-md flex flex-col justify-between hover:border-slate-700 transition">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">TOPLAM MÜŞTERİ (TENANT)</span>
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                        </div>
                        <div class="text-2xl font-black text-white font-mono mt-2">{{ summaryStats.total_tenants }}</div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
                        <span>Açılan Bağımsız Hesap</span>
                        <span class="text-emerald-400 font-semibold">%100 İzole</span>
                    </div>
                </div>

                <div class="p-5 rounded-2xl bg-[#0f141f] border border-slate-800/90 shadow-md flex flex-col justify-between hover:border-slate-700 transition">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">AKTİF LİSANSLAR</span>
                            <div class="w-8 h-8 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="text-2xl font-black text-white font-mono mt-2">{{ summaryStats.active_tenants }}</div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
                        <span>Erişimi Açık Şirketler</span>
                        <span class="text-rose-400 font-semibold">
                            {{ summaryStats.total_tenants > 0 ? Math.round((summaryStats.active_tenants / summaryStats.total_tenants) * 100) : 0 }}% Aktif
                        </span>
                    </div>
                </div>

                <div class="p-5 rounded-2xl bg-[#0f141f] border border-slate-800/90 shadow-md flex flex-col justify-between hover:border-slate-700 transition">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">TOPLAM SİSTEM KULLANICISI</span>
                            <div class="w-8 h-8 rounded-lg bg-sky-500/10 border border-sky-500/20 text-sky-400 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="text-2xl font-black text-white font-mono mt-2">{{ summaryStats.total_users }}</div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
                        <span>Tüm Şirketlerdeki Personel</span>
                        <span class="text-sky-400 font-semibold">Aktif Hesap</span>
                    </div>
                </div>

                <div class="p-5 rounded-2xl bg-[#0f141f] border border-slate-800/90 shadow-md flex flex-col justify-between hover:border-slate-700 transition">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">TOPLAM İŞLENEN ARAZİ</span>
                            <div class="w-8 h-8 rounded-lg bg-purple-500/10 border border-purple-500/20 text-purple-400 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
                                </svg>
                            </div>
                        </div>
                        <div class="text-2xl font-black text-white font-mono mt-2">{{ summaryStats.total_area_dekar }} <span class="text-xs font-sans text-slate-400">da</span></div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
                        <span>İşlenen Sera & Tarla</span>
                        <span class="text-purple-400 font-semibold">Tarımsal Hacim</span>
                    </div>
                </div>

            </div>

            <div class="rounded-2xl bg-[#0f141f] border border-slate-800 shadow-xl overflow-hidden">
                
                <div class="p-4 sm:p-5 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-sm font-black text-white tracking-wide uppercase">MÜŞTERİ ŞİRKET LİSTESİ & VERİTABANLARI</h2>
                        <p class="text-xs text-slate-400">Sistemde provizyonlanan şirketler, lisans süreleri ve yönetim yetkileri</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Şirket, slug veya e-posta ara..."
                                class="w-64 sm:w-80 pl-9 pr-3 py-2 rounded-xl bg-[#090d14] border border-slate-800 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 transition"
                            />
                            <svg class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>

                        <div class="flex items-center p-0.5 rounded-xl bg-[#090d14] border border-slate-800">
                            <button
                                type="button"
                                @click="viewMode = 'table'"
                                :class="[
                                    'p-1.5 rounded-lg transition cursor-pointer',
                                    viewMode === 'table' ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white'
                                ]"
                                title="Tablo Görünümü"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </button>
                            <button
                                type="button"
                                @click="viewMode = 'grid'"
                                :class="[
                                    'p-1.5 rounded-lg transition cursor-pointer',
                                    viewMode === 'grid' ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white'
                                ]"
                                title="Kart Görünümü"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="filteredTenants.length === 0" class="p-12 text-center space-y-2">
                    <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center mx-auto text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div class="text-sm font-bold text-white">Eşleşen Müşteri Bulunamadı</div>
                    <p class="text-xs text-slate-400">Yeni bir müşteri şirketi ekleyebilirsiniz.</p>
                </div>

                <div v-else-if="viewMode === 'table'" class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="bg-[#090d14] text-[11px] text-slate-400 uppercase tracking-wider border-b border-slate-800">
                            <tr>
                                <th class="py-3 px-4">ŞİRKET BİLGİSİ</th>
                                <th class="py-3 px-4">LİSANS SÜRESİ</th>
                                <th class="py-3 px-4">KURUCU GİRİŞ HESABI</th>
                                <th class="py-3 px-3 text-center">METRİKLER</th>
                                <th class="py-3 px-3 text-center">DURUM</th>
                                <th class="py-3 px-4 text-right">İŞLEMLER</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-medium">
                            <tr v-for="tenant in filteredTenants" :key="tenant.id" class="hover:bg-slate-800/30 transition">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-xs text-slate-200 shrink-0">
                                            {{ tenant.name.substring(0, 2).toUpperCase() }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-white flex items-center gap-1.5 flex-wrap">
                                                <span class="truncate">{{ tenant.name }}</span>
                                                <span v-if="tenant.id === 1" class="text-[9px] px-1.5 py-0.2 rounded bg-amber-500/20 text-amber-300 border border-amber-500/30 font-bold shrink-0">
                                                    Ana Sistem
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-400 font-mono">
                                                <span>{{ tenant.slug }}</span>
                                                <span class="text-slate-600">•</span>
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold uppercase bg-rose-950/60 border border-rose-900/50 text-rose-300">
                                                    {{ tenant.plan }}
                                                </span>
                                                <span class="text-slate-600">•</span>
                                                <span class="text-slate-500">ID: #{{ tenant.id }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <div v-if="!tenant.expires_at" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-emerald-950/50 border border-emerald-800/60 text-emerald-300 text-[10px] font-bold">
                                        <svg class="w-3 h-3 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>Sınırsız (Ömür Boyu)</span>
                                    </div>
                                    <div v-else-if="tenant.is_expired" class="inline-flex flex-col">
                                        <span class="px-2 py-0.5 rounded bg-rose-950/80 border border-rose-800 text-rose-300 text-[10px] font-black uppercase">
                                            Süresi Doldu!
                                        </span>
                                        <span class="text-[10px] font-mono text-slate-500 mt-0.5">{{ tenant.expires_at }}</span>
                                    </div>
                                    <div v-else class="space-y-0.5">
                                        <div class="text-[11px] font-mono font-bold text-slate-200">{{ tenant.expires_at }}</div>
                                        <div class="text-[10px] text-amber-400 font-semibold">{{ tenant.remaining_days }} gün kaldı</div>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="min-w-0">
                                            <div class="text-slate-200 font-semibold truncate">{{ tenant.owner?.name || 'Yönetici' }}</div>
                                            <div class="text-[11px] text-slate-400 font-mono truncate">{{ tenant.owner?.email || '-' }}</div>
                                        </div>
                                        <button
                                            type="button"
                                            @click="copyCredentials(tenant)"
                                            class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-[10px] font-bold transition cursor-pointer flex items-center shrink-0 border border-slate-700/60"
                                            title="Giriş Bilgilerini Kopyala"
                                        >
                                            <svg v-if="copiedTenantId === tenant.id" class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <svg v-else class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-sky-950/60 border border-sky-900/60 text-sky-400 text-[11px] font-bold" title="Kullanıcı Sayısı">
                                            <svg class="w-3 h-3 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            {{ tenant.stats?.users_count || 0 }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-emerald-950/60 border border-emerald-900/60 text-emerald-400 text-[11px] font-bold" title="Saha / Parsel Sayısı">
                                            <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            {{ tenant.stats?.locations_count || 0 }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-amber-950/60 border border-amber-900/60 text-amber-400 text-[11px] font-bold" title="İş Formu Sayısı">
                                            <svg class="w-3 h-3 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            {{ tenant.stats?.work_sheets_count || 0 }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    <span
                                        :class="[
                                            'px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider inline-flex items-center gap-1 border',
                                            tenant.is_active
                                                ? 'bg-emerald-950/60 border-emerald-800/60 text-emerald-400'
                                                : 'bg-red-950/60 border-red-800/60 text-red-400'
                                        ]"
                                    >
                                        <span :class="['w-1.5 h-1.5 rounded-full', tenant.is_active ? 'bg-emerald-400' : 'bg-red-400']"></span>
                                        {{ tenant.is_active ? 'Aktif' : 'Pasif' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        
                                        <button
                                            type="button"
                                            @click="impersonateTenant(tenant)"
                                            class="px-2 py-1.5 rounded-lg bg-sky-950/60 hover:bg-sky-900/80 text-sky-300 text-xs font-bold transition cursor-pointer flex items-center gap-1 border border-sky-800/60"
                                            title="Şirket Paneline Süper Yönetici Olarak Göz At"
                                        >
                                            <svg class="w-3.5 h-3.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <span class="hidden xl:inline">Göz At</span>
                                        </button>

                                        <button
                                            type="button"
                                            @click="openLicenseModal(tenant)"
                                            class="px-2 py-1.5 rounded-lg bg-amber-950/50 hover:bg-amber-900/70 text-amber-300 text-xs font-bold transition cursor-pointer flex items-center gap-1 border border-amber-800/60"
                                            title="Lisans Süresini Uzat / Düzenle"
                                        >
                                            <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="hidden xl:inline">Lisans</span>
                                        </button>

                                        <button
                                            type="button"
                                            @click="downloadBackup(tenant)"
                                            class="px-2 py-1.5 rounded-lg bg-indigo-950/60 hover:bg-indigo-900/80 text-indigo-300 text-xs font-bold transition cursor-pointer flex items-center gap-1 border border-indigo-800/60"
                                            title="Tüm Şirket Veritabanını JSON Yedeği Olarak İndir"
                                        >
                                            <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                            <span class="hidden xl:inline">Yedek</span>
                                        </button>

                                        <button
                                            type="button"
                                            @click="openEditAdminModal(tenant)"
                                            class="px-2 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition cursor-pointer flex items-center gap-1 border border-slate-700/60"
                                            title="Yönetici Giriş ve Şifre Düzenle"
                                        >
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                            </svg>
                                            <span class="hidden xl:inline">Şifre</span>
                                        </button>

                                        <button
                                            type="button"
                                            @click="toggleStatus(tenant)"
                                            :class="[
                                                'p-1.5 rounded-lg text-xs font-bold transition cursor-pointer flex items-center gap-1 border',
                                                tenant.is_active
                                                    ? 'bg-amber-950/40 hover:bg-amber-900/60 text-amber-300 border-amber-800/50'
                                                    : 'bg-emerald-950/40 hover:bg-emerald-900/60 text-emerald-300 border-emerald-800/50'
                                            ]"
                                            :title="tenant.is_active ? 'Şirket Erişimini Dondur' : 'Şirket Erişimini Aç'"
                                        >
                                            <svg v-if="tenant.is_active" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </button>

                                        <button
                                            v-if="tenant.id !== 1"
                                            type="button"
                                            @click="deleteTenant(tenant)"
                                            class="p-1.5 rounded-lg bg-red-950/40 hover:bg-red-900/60 text-red-400 border border-red-800/50 transition cursor-pointer"
                                            title="Şirket Hesabını Kalıcı Olarak Sil"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="p-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="tenant in filteredTenants"
                        :key="tenant.id"
                        class="p-5 rounded-2xl bg-[#090d14] border border-slate-800 hover:border-slate-700 transition flex flex-col justify-between space-y-4 shadow-lg"
                    >
                        <div class="space-y-3">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-xs text-slate-200 shrink-0">
                                        {{ tenant.name.substring(0, 2).toUpperCase() }}
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-white text-sm flex items-center gap-1.5">
                                            <span>{{ tenant.name }}</span>
                                            <span v-if="tenant.id === 1" class="text-[9px] px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300 border border-amber-500/30 font-bold">
                                                Ana Sistem
                                            </span>
                                        </h3>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[11px] font-mono text-slate-400">#{{ tenant.slug }}</span>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-rose-950/60 text-rose-300 border border-rose-900/50">
                                                {{ tenant.plan }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <span
                                    :class="[
                                        'px-2.5 py-1 rounded-full text-[10px] font-bold uppercase border',
                                        tenant.is_active ? 'bg-emerald-950/60 text-emerald-400 border-emerald-800/60' : 'bg-red-950/60 text-red-400 border-red-800/60'
                                    ]"
                                >
                                    {{ tenant.is_active ? 'Aktif' : 'Donduruldu' }}
                                </span>
                            </div>

                            <div class="p-3 rounded-xl bg-[#0f141f] border border-slate-800 flex items-center justify-between text-xs">
                                <span class="text-slate-400 text-[11px]">Lisans Durumu:</span>
                                <span v-if="!tenant.expires_at" class="text-emerald-400 font-bold text-[11px]">Sınırsız</span>
                                <span v-else-if="tenant.is_expired" class="text-rose-400 font-bold text-[11px]">Süresi Doldu ({{ tenant.expires_at }})</span>
                                <span v-else class="text-amber-300 font-bold text-[11px]">{{ tenant.expires_at }} ({{ tenant.remaining_days }} gün)</span>
                            </div>

                            <div class="p-3.5 rounded-xl bg-[#0f141f] border border-slate-800 text-xs space-y-1.5">
                                <div class="text-slate-400 font-semibold text-[10px] uppercase tracking-wider flex items-center justify-between">
                                    <span>Kurucu Yönetici:</span>
                                    <button
                                        type="button"
                                        @click="copyCredentials(tenant)"
                                        class="text-[10px] text-slate-400 hover:text-white flex items-center gap-1 cursor-pointer"
                                    >
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                        </svg>
                                        <span>{{ copiedTenantId === tenant.id ? 'Kopyalandı' : 'Kopyala' }}</span>
                                    </button>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-200 font-bold">{{ tenant.owner?.name || 'Yönetici' }}</span>
                                    <button type="button" @click="openEditAdminModal(tenant)" class="text-[10px] text-rose-400 hover:underline cursor-pointer font-bold">
                                        Şifre Değiştir
                                    </button>
                                </div>
                                <div class="text-[11px] font-mono text-slate-400 truncate">{{ tenant.owner?.email || '-' }}</div>
                            </div>

                            <div class="grid grid-cols-3 gap-2 text-center text-xs">
                                <div class="p-2.5 rounded-xl bg-[#0f141f] border border-slate-800">
                                    <div class="font-bold text-white font-mono">{{ tenant.stats?.users_count || 0 }}</div>
                                    <div class="text-[10px] text-slate-400">Kullanıcı</div>
                                </div>
                                <div class="p-2.5 rounded-xl bg-[#0f141f] border border-slate-800">
                                    <div class="font-bold text-white font-mono">{{ tenant.stats?.locations_count || 0 }}</div>
                                    <div class="text-[10px] text-slate-400">Saha</div>
                                </div>
                                <div class="p-2.5 rounded-xl bg-[#0f141f] border border-slate-800">
                                    <div class="font-bold text-white font-mono">{{ tenant.stats?.work_sheets_count || 0 }}</div>
                                    <div class="text-[10px] text-slate-400">İş Formu</div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-800 flex items-center justify-between gap-1.5 flex-wrap">
                            <button
                                type="button"
                                @click="impersonateTenant(tenant)"
                                class="flex-1 py-1.5 px-2.5 rounded-lg bg-sky-950/60 hover:bg-sky-900/80 text-sky-300 text-xs font-bold transition cursor-pointer flex items-center justify-center gap-1 border border-sky-800/60"
                            >
                                <svg class="w-3.5 h-3.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <span>Göz At</span>
                            </button>
                            <button
                                type="button"
                                @click="openLicenseModal(tenant)"
                                class="py-1.5 px-2.5 rounded-lg bg-amber-950/50 hover:bg-amber-900/70 text-amber-300 text-xs font-bold transition cursor-pointer flex items-center gap-1 border border-amber-800/60"
                            >
                                <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>Lisans</span>
                            </button>
                            <button
                                type="button"
                                @click="downloadBackup(tenant)"
                                class="p-1.5 rounded-lg bg-indigo-950/60 hover:bg-indigo-900/80 text-indigo-300 border border-indigo-800/60 transition cursor-pointer"
                                title="Yedek İndir"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                            </button>
                            <button
                                v-if="tenant.id !== 1"
                                type="button"
                                @click="deleteTenant(tenant)"
                                class="p-1.5 rounded-lg bg-red-950/40 text-red-400 border border-red-800/50 hover:bg-red-900/60 transition cursor-pointer text-xs"
                                title="Şirket Hesabını Sil"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

            </div>

        </main>

        <div v-if="isCreateModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
            <div class="w-full max-w-xl bg-[#0f141f] border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-5 shadow-2xl text-slate-100 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3.5 border-b border-slate-800">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-rose-600/20 border border-rose-600/30 text-rose-400 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-white">Yeni Müşteri Şirket Hesabı Aç</h2>
                            <p class="text-xs text-slate-400">Müşteriye sıfır ve izole bir ERP veritabanı alanı oluşturun.</p>
                        </div>
                    </div>
                    <button type="button" @click="isCreateModalOpen = false" class="p-1.5 text-slate-400 hover:text-white rounded-lg cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitCreateTenant" class="space-y-4">
                    <div class="text-[11px] font-black text-rose-400 uppercase tracking-wider">1. ŞİRKET & LİSANS BİLGİLERİ</div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-1">
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Şirket Ünvanı *</label>
                            <input v-model="createForm.tenant_name" required placeholder="Örn: Akdeniz Tarım Ltd." class="w-full px-3.5 py-2.5 rounded-xl bg-[#090d14] border border-slate-800 text-white text-xs focus:outline-none focus:border-rose-500" />
                            <div v-if="createForm.errors.tenant_name" class="text-rose-400 text-[11px] mt-1">{{ createForm.errors.tenant_name }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Abonelik Paketi</label>
                            <select v-model="createForm.plan" class="w-full px-3.5 py-2.5 rounded-xl bg-[#090d14] border border-slate-800 text-white text-xs focus:outline-none focus:border-rose-500">
                                <option value="starter">Starter (Temel)</option>
                                <option value="growth">Growth (Büyüme)</option>
                                <option value="enterprise">Enterprise (Kurumsal)</option>
                                <option value="unlimited">Unlimited (Limitsiz)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Lisans Süresi</label>
                            <select v-model="createForm.license_months" class="w-full px-3.5 py-2.5 rounded-xl bg-[#090d14] border border-slate-800 text-white text-xs focus:outline-none focus:border-rose-500">
                                <option value="1">1 Aylık</option>
                                <option value="3">3 Aylık</option>
                                <option value="6">6 Aylık</option>
                                <option value="12">1 Yıllık (12 Ay)</option>
                                <option value="24">2 Yıllık (24 Ay)</option>
                                <option value="unlimited">Sınırsız / Ömür Boyu</option>
                            </select>
                        </div>
                    </div>

                    <div class="text-[11px] font-black text-rose-400 uppercase tracking-wider pt-2">2. KURUCU YÖNETİCİ GİRİŞ BİLGİLERİ</div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Ad Soyad *</label>
                            <input v-model="createForm.owner_name" required placeholder="Örn: Mehmet Yılmaz" class="w-full px-3.5 py-2.5 rounded-xl bg-[#090d14] border border-slate-800 text-white text-xs focus:outline-none focus:border-rose-500" />
                            <div v-if="createForm.errors.owner_name" class="text-rose-400 text-[11px] mt-1">{{ createForm.errors.owner_name }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Giriş E-Postası *</label>
                            <input v-model="createForm.owner_email" type="email" required placeholder="mehmet@akdeniz.com" class="w-full px-3.5 py-2.5 rounded-xl bg-[#090d14] border border-slate-800 text-white text-xs focus:outline-none focus:border-rose-500" />
                            <div v-if="createForm.errors.owner_email" class="text-rose-400 text-[11px] mt-1">{{ createForm.errors.owner_email }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">İlk Giriş Şifresi *</label>
                            <input v-model="createForm.owner_password" type="text" required placeholder="Min 4 karakter" class="w-full px-3.5 py-2.5 rounded-xl bg-[#090d14] border border-slate-800 text-white text-xs focus:outline-none focus:border-rose-500" />
                            <div v-if="createForm.errors.owner_password" class="text-rose-400 text-[11px] mt-1">{{ createForm.errors.owner_password }}</div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2.5 pt-4 border-t border-slate-800">
                        <button type="button" @click="isCreateModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-800 text-xs font-bold text-slate-300 hover:bg-slate-700 cursor-pointer">
                            İptal
                        </button>
                        <button type="submit" :disabled="createForm.processing" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs shadow-md cursor-pointer flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <span>{{ createForm.processing ? 'Oluşturuluyor...' : 'Müşteri Hesabını Başlat' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="isLicenseModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
            <div class="w-full max-w-md bg-[#0f141f] border border-slate-800 rounded-2xl p-6 space-y-4 shadow-2xl text-slate-100">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <div class="flex items-center gap-2">
                        <div class="p-2 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-white">Lisans Süresi Yönetimi</h2>
                            <p class="text-[11px] text-slate-400">{{ selectedTenant?.name }}</p>
                        </div>
                    </div>
                    <button type="button" @click="isLicenseModalOpen = false" class="text-slate-400 hover:text-white cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-3 rounded-xl bg-[#090d14] border border-slate-800 flex items-center justify-between text-xs">
                    <span class="text-slate-400">Mevcut Bitiş:</span>
                    <span v-if="!selectedTenant?.expires_at" class="text-emerald-400 font-bold">Sınırsız (Ömür Boyu)</span>
                    <span v-else-if="selectedTenant?.is_expired" class="text-rose-400 font-bold">Süresi Doldu ({{ selectedTenant?.expires_at }})</span>
                    <span v-else class="text-amber-300 font-bold">{{ selectedTenant?.expires_at }} ({{ selectedTenant?.remaining_days }} gün kaldı)</span>
                </div>

                <form @submit.prevent="submitLicenseUpdate" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-2">Lisans İşlemi Seçin</label>
                        <div class="grid grid-cols-3 gap-2 text-xs">
                            <button
                                type="button"
                                @click="licenseForm.action = 'add_days'"
                                :class="[
                                    'py-2 px-3 rounded-xl font-bold border transition cursor-pointer text-center',
                                    licenseForm.action === 'add_days' ? 'bg-amber-600 text-white border-amber-500 shadow' : 'bg-[#090d14] text-slate-400 border-slate-800 hover:text-white'
                                ]"
                            >
                                Süre Ekle
                            </button>
                            <button
                                type="button"
                                @click="licenseForm.action = 'custom_date'"
                                :class="[
                                    'py-2 px-3 rounded-xl font-bold border transition cursor-pointer text-center',
                                    licenseForm.action === 'custom_date' ? 'bg-amber-600 text-white border-amber-500 shadow' : 'bg-[#090d14] text-slate-400 border-slate-800 hover:text-white'
                                ]"
                            >
                                Özel Tarih
                            </button>
                            <button
                                type="button"
                                @click="licenseForm.action = 'unlimited'"
                                :class="[
                                    'py-2 px-3 rounded-xl font-bold border transition cursor-pointer text-center',
                                    licenseForm.action === 'unlimited' ? 'bg-emerald-600 text-white border-emerald-500 shadow' : 'bg-[#090d14] text-slate-400 border-slate-800 hover:text-white'
                                ]"
                            >
                                Sınırsız Yap
                            </button>
                        </div>
                    </div>

                    <div v-if="licenseForm.action === 'add_days'" class="space-y-2">
                        <label class="block text-xs font-semibold text-slate-300">Eklenecek Gün Sayısı</label>
                        <div class="grid grid-cols-4 gap-2 mb-2">
                            <button
                                type="button"
                                @click="licenseForm.days = 30"
                                :class="licenseForm.days === 30 ? 'bg-slate-700 text-amber-300 border-amber-500' : 'bg-[#090d14] text-slate-400 border-slate-800'"
                                class="py-1.5 rounded-lg border text-xs font-bold transition cursor-pointer"
                            >
                                +30 Gün
                            </button>
                            <button
                                type="button"
                                @click="licenseForm.days = 90"
                                :class="licenseForm.days === 90 ? 'bg-slate-700 text-amber-300 border-amber-500' : 'bg-[#090d14] text-slate-400 border-slate-800'"
                                class="py-1.5 rounded-lg border text-xs font-bold transition cursor-pointer"
                            >
                                +90 Gün
                            </button>
                            <button
                                type="button"
                                @click="licenseForm.days = 180"
                                :class="licenseForm.days === 180 ? 'bg-slate-700 text-amber-300 border-amber-500' : 'bg-[#090d14] text-slate-400 border-slate-800'"
                                class="py-1.5 rounded-lg border text-xs font-bold transition cursor-pointer"
                            >
                                +6 Ay
                            </button>
                            <button
                                type="button"
                                @click="licenseForm.days = 365"
                                :class="licenseForm.days === 365 ? 'bg-slate-700 text-amber-300 border-amber-500' : 'bg-[#090d14] text-slate-400 border-slate-800'"
                                class="py-1.5 rounded-lg border text-xs font-bold transition cursor-pointer"
                            >
                                +1 Yıl
                            </button>
                        </div>
                        <input
                            v-model.number="licenseForm.days"
                            type="number"
                            min="1"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-[#090d14] border border-slate-800 text-white text-xs font-mono focus:outline-none focus:border-amber-500"
                            placeholder="Örn: 30"
                        />
                    </div>

                    <div v-else-if="licenseForm.action === 'custom_date'" class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-300">Yeni Bitiş Tarihi *</label>
                        <input
                            v-model="licenseForm.expires_at"
                            type="date"
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl bg-[#090d14] border border-slate-800 text-white text-xs focus:outline-none focus:border-amber-500"
                        />
                    </div>

                    <div v-else class="p-3 rounded-xl bg-emerald-950/40 border border-emerald-800/60 text-emerald-300 text-xs text-center">
                        Şirket süresiz olarak tüm ERP özelliklerine erişebilecektir.
                    </div>

                    <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-800">
                        <button type="button" @click="isLicenseModalOpen = false" class="px-3.5 py-2 rounded-xl bg-slate-800 text-xs font-bold text-slate-300 hover:bg-slate-700 cursor-pointer">
                            İptal
                        </button>
                        <button type="submit" :disabled="licenseForm.processing" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs cursor-pointer shadow-md">
                            {{ licenseForm.processing ? 'Güncelleniyor...' : 'Lisansı Kaydet' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="isEditAdminModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
            <div class="w-full max-w-md bg-[#0f141f] border border-slate-800 rounded-2xl p-6 space-y-4 shadow-2xl text-slate-100">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <h2 class="text-sm font-bold text-white">Yönetici Girişini Güncelle ({{ selectedTenant?.name }})</h2>
                    <button type="button" @click="isEditAdminModalOpen = false" class="text-slate-400 hover:text-white cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitEditAdmin" class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">E-Posta Adresi *</label>
                        <input v-model="adminEditForm.owner_email" type="email" required class="w-full px-3.5 py-2.5 rounded-xl bg-[#090d14] border border-slate-800 text-white text-xs focus:outline-none focus:border-rose-500" />
                        <div v-if="adminEditForm.errors.owner_email" class="text-rose-400 text-[11px] mt-1">{{ adminEditForm.errors.owner_email }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Yeni Şifre (Boş bırakılırsa değişmez)</label>
                        <input v-model="adminEditForm.owner_password" type="text" placeholder="Yeni şifre girin (Örn: 123456)" class="w-full px-3.5 py-2.5 rounded-xl bg-[#090d14] border border-slate-800 text-white text-xs focus:outline-none focus:border-rose-500" />
                        <div v-if="adminEditForm.errors.owner_password" class="text-rose-400 text-[11px] mt-1">{{ adminEditForm.errors.owner_password }}</div>
                    </div>

                    <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-800">
                        <button type="button" @click="isEditAdminModalOpen = false" class="px-3.5 py-2 rounded-xl bg-slate-800 text-xs font-bold text-slate-300 hover:bg-slate-700 cursor-pointer">
                            İptal
                        </button>
                        <button type="submit" :disabled="adminEditForm.processing" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs cursor-pointer">
                            {{ adminEditForm.processing ? 'Kaydediliyor...' : 'Kaydet' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="confirmModal.isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm">
            <div class="w-full max-w-md bg-[#0f141f] border border-slate-800 rounded-2xl p-5 space-y-4 shadow-2xl text-slate-100">
                <div class="flex items-start gap-3">
                    <div
                        :class="[
                            'w-9 h-9 rounded-xl flex items-center justify-center shrink-0 border',
                            confirmModal.type === 'danger' ? 'bg-red-950/50 border-red-800/50 text-red-400' :
                            confirmModal.type === 'warning' ? 'bg-amber-950/50 border-amber-800/50 text-amber-400' :
                            confirmModal.type === 'info' ? 'bg-sky-950/50 border-sky-800/50 text-sky-400' :
                            'bg-rose-950/50 border-rose-800/50 text-rose-400'
                        ]"
                    >
                        <svg v-if="confirmModal.type === 'danger'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <svg v-else-if="confirmModal.type === 'warning'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <svg v-else-if="confirmModal.type === 'info'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>

                    <div class="space-y-1">
                        <h3 class="text-sm font-bold text-white">{{ confirmModal.title }}</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">{{ confirmModal.description }}</p>
                    </div>
                </div>

                <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-800">
                    <button
                        type="button"
                        @click="confirmModal.isOpen = false"
                        class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-300 transition cursor-pointer"
                    >
                        {{ confirmModal.cancelText }}
                    </button>
                    <button
                        type="button"
                        @click="handleConfirm"
                        :class="[
                            'px-4 py-2 rounded-xl text-xs font-bold text-white transition cursor-pointer shadow-md',
                            confirmModal.type === 'danger' ? 'bg-red-600 hover:bg-red-500' :
                            confirmModal.type === 'warning' ? 'bg-amber-600 hover:bg-amber-500' :
                            confirmModal.type === 'info' ? 'bg-sky-600 hover:bg-sky-500' :
                            'bg-rose-600 hover:bg-rose-500'
                        ]"
                    >
                        {{ confirmModal.confirmText }}
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>
