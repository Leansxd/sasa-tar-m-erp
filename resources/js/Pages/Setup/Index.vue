<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps<{
    initialStep?: number;
    companies: any[];
    productionLocations: any[];
    units: any[];
    packagings: any[];
    products: any[];
    personnels: any[];
    crewLeaders: any[];
    workers: any[];
    jobTypes: any[];
    waterSources: any[];
    filters: any[];
    fertilizationRecipes: any[];
    sprayingRecipes: any[];
    tradingParties: any[];
    deliveryTypes: any[];
    stepStats: Record<string, number>;
}>();

const currentStep = ref(props.initialStep || 1);

const steps = [
    { id: 1, title: 'Firma / Şirket', shortTitle: '1. Şirket', icon: '🏢', desc: 'Ana işletme tüzel kişilikleri' },
    { id: 2, title: 'Üretim Sahaları', shortTitle: '2. Parseller', icon: '🚜', desc: 'Sera, tarla, blok ve vanalar' },
    { id: 3, title: 'Ölçü Birimleri', shortTitle: '3. Birimler', icon: '📦', desc: 'kg, kasa, adet vb. standartlar' },
    { id: 4, title: 'Tarımsal Ürünler', shortTitle: '4. Mahsuller', icon: '🌾', desc: 'Ürün çeşitleri ve kaliteler' },
    { id: 5, title: 'İş Gücü Kadrosu', shortTitle: '5. Personel', icon: '👥', desc: 'Mühendis, çavuş ve işçiler' },
    { id: 6, title: 'Operasyon Tipleri', shortTitle: '6. Görevler', icon: '📋', desc: 'Hasat, budama vb. form tipleri' },
    { id: 7, title: 'Su & Altyapı', shortTitle: '7. Altyapı', icon: '💧', desc: 'Kuyu, havuz ve filtre sistemleri' },
    { id: 8, title: 'Kurulum Özeti', shortTitle: '8. Başlat', icon: '🚀', desc: 'Sistemi devreye al' },
];

const completedSteps = computed(() => {
    const list: number[] = [];
    if (props.companies.length > 0) list.push(1);
    if (props.productionLocations.length > 0) list.push(2);
    if (props.units.length > 0) list.push(3);
    if (props.products.length > 0) list.push(4);
    if (props.personnels.length > 0 || props.crewLeaders.length > 0 || props.workers.length > 0) list.push(5);
    if (props.jobTypes.length > 0) list.push(6);
    if (props.waterSources.length > 0 || props.filters.length > 0 || props.fertilizationRecipes.length > 0) list.push(7);
    if (list.length >= 3) list.push(8);
    return list;
});

const progressPercent = computed(() => {
    return Math.round((completedSteps.value.filter(s => s <= 7).length / 7) * 100);
});

const setStep = (id: number) => {
    currentStep.value = id;
};

const nextStep = () => {
    if (currentStep.value < 8) currentStep.value++;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const companyForm = useForm({
    name: '',
    code: '',
    tax_number: '',
    dia_company_code: '',
    is_active: true,
});
const submitCompany = () => {
    companyForm.post(route('definitions.companies.store'), {
        preserveScroll: true,
        onSuccess: () => {
            companyForm.reset();
        }
    });
};

const locationForm = useForm({
    name: '',
    location_type: 'greenhouse',
    company_id: '',
    total_area_dekar: '',
    dia_branch_code: '',
    dia_warehouse_code: '',
    sections: [] as any[],
    valves: [] as any[],
});
const newSectionName = ref('');
const newValveName = ref('');

const addSection = () => {
    if (!newSectionName.value.trim()) return;
    locationForm.sections.push({ name: newSectionName.value.trim() });
    newSectionName.value = '';
};
const removeSection = (idx: number) => {
    locationForm.sections.splice(idx, 1);
};
const addValve = () => {
    if (!newValveName.value.trim()) return;
    locationForm.valves.push({ name: newValveName.value.trim(), valve_number: 'V-' + (locationForm.valves.length + 1) });
    newValveName.value = '';
};
const removeValve = (idx: number) => {
    locationForm.valves.splice(idx, 1);
};
const submitLocation = () => {
    locationForm.post(route('definitions.production-locations.store'), {
        preserveScroll: true,
        onSuccess: () => {
            locationForm.reset();
            locationForm.sections = [];
            locationForm.valves = [];
        }
    });
};

interface PresetUnit {
    name: string;
    symbol: string;
    unit_category: 'quantity' | 'area' | 'count';
    category_label: string;
    recommended?: boolean;
}

const presetUnits: PresetUnit[] = [
    { name: 'Kilogram', symbol: 'kg', unit_category: 'quantity', category_label: 'Ağırlık & Kütle', recommended: true },
    { name: 'Gram', symbol: 'gr', unit_category: 'quantity', category_label: 'Ağırlık & Kütle', recommended: true },
    { name: 'Ton', symbol: 'ton', unit_category: 'quantity', category_label: 'Ağırlık & Kütle', recommended: true },
    { name: 'Miligram', symbol: 'mg', unit_category: 'quantity', category_label: 'Ağırlık & Kütle' },
    { name: 'Kental', symbol: 'q', unit_category: 'quantity', category_label: 'Ağırlık & Kütle' },

    { name: 'Adet', symbol: 'adt', unit_category: 'count', category_label: 'Sayı & Paket', recommended: true },
    { name: 'Kasa', symbol: 'ksa', unit_category: 'count', category_label: 'Sayı & Paket', recommended: true },
    { name: 'Koli', symbol: 'koli', unit_category: 'count', category_label: 'Sayı & Paket', recommended: true },
    { name: 'Paket', symbol: 'pkt', unit_category: 'count', category_label: 'Sayı & Paket', recommended: true },
    { name: 'Çuval', symbol: 'çvl', unit_category: 'count', category_label: 'Sayı & Paket', recommended: true },
    { name: 'Palet', symbol: 'plt', unit_category: 'count', category_label: 'Sayı & Paket', recommended: true },
    { name: 'Sandık', symbol: 'sdk', unit_category: 'count', category_label: 'Sayı & Paket' },
    { name: 'File', symbol: 'file', unit_category: 'count', category_label: 'Sayı & Paket' },
    { name: 'Demet', symbol: 'dmt', unit_category: 'count', category_label: 'Sayı & Paket', recommended: true },
    { name: 'Bağ', symbol: 'bağ', unit_category: 'count', category_label: 'Sayı & Paket' },
    { name: 'Deste', symbol: 'dst', unit_category: 'count', category_label: 'Sayı & Paket' },
    { name: 'Düzine', symbol: 'dzn', unit_category: 'count', category_label: 'Sayı & Paket' },
    { name: 'Viyol', symbol: 'vyl', unit_category: 'count', category_label: 'Sayı & Paket' },
    { name: 'Rulo', symbol: 'rulo', unit_category: 'count', category_label: 'Sayı & Paket' },
    { name: 'Sepet', symbol: 'spt', unit_category: 'count', category_label: 'Sayı & Paket' },

    { name: 'Litre', symbol: 'lt', unit_category: 'quantity', category_label: 'Hacim & Sıvı', recommended: true },
    { name: 'Mililitre', symbol: 'ml', unit_category: 'quantity', category_label: 'Hacim & Sıvı', recommended: true },
    { name: 'Metreküp', symbol: 'm³', unit_category: 'quantity', category_label: 'Hacim & Sıvı' },
    { name: 'Bidon', symbol: 'bdn', unit_category: 'quantity', category_label: 'Hacim & Sıvı' },
    { name: 'Varil', symbol: 'vrl', unit_category: 'quantity', category_label: 'Hacim & Sıvı' },
    { name: 'Şişe', symbol: 'şş', unit_category: 'quantity', category_label: 'Hacim & Sıvı' },
    { name: 'Teneke', symbol: 'tnk', unit_category: 'quantity', category_label: 'Hacim & Sıvı' },
    { name: 'Tanker', symbol: 'tnkr', unit_category: 'quantity', category_label: 'Hacim & Sıvı' },

    { name: 'Dekar (Dönüm)', symbol: 'da', unit_category: 'area', category_label: 'Alan & Uzunluk', recommended: true },
    { name: 'Hektar', symbol: 'ha', unit_category: 'area', category_label: 'Alan & Uzunluk' },
    { name: 'Metrekare', symbol: 'm²', unit_category: 'area', category_label: 'Alan & Uzunluk' },
    { name: 'Metre', symbol: 'mt', unit_category: 'area', category_label: 'Alan & Uzunluk', recommended: true },
    { name: 'Kilometre', symbol: 'km', unit_category: 'area', category_label: 'Alan & Uzunluk' },
];

const selectedPresetNames = ref<string[]>([]);
const unitCategoryFilter = ref<string>('all');
const showCustomUnitForm = ref(false);

const filteredPresetUnits = computed(() => {
    if (unitCategoryFilter.value === 'all') return presetUnits;
    return presetUnits.filter(u => u.category_label === unitCategoryFilter.value);
});

const isUnitSavedInDb = (name: string) => {
    return props.units.some((u: any) => u.name.toLowerCase().trim() === name.toLowerCase().trim());
};

const togglePresetUnit = (unit: PresetUnit) => {
    if (isUnitSavedInDb(unit.name)) return;
    const idx = selectedPresetNames.value.indexOf(unit.name);
    if (idx >= 0) {
        selectedPresetNames.value.splice(idx, 1);
    } else {
        selectedPresetNames.value.push(unit.name);
    }
};

const selectRecommendedUnits = () => {
    presetUnits.filter(u => u.recommended && !isUnitSavedInDb(u.name)).forEach(u => {
        if (!selectedPresetNames.value.includes(u.name)) {
            selectedPresetNames.value.push(u.name);
        }
    });
};

const selectAllUnits = () => {
    presetUnits.filter(u => !isUnitSavedInDb(u.name)).forEach(u => {
        if (!selectedPresetNames.value.includes(u.name)) {
            selectedPresetNames.value.push(u.name);
        }
    });
};

const clearSelectedUnits = () => {
    selectedPresetNames.value = [];
};

const bulkUnitForm = useForm({
    units: [] as any[],
});

const submitSelectedUnits = () => {
    const unitsToSave = presetUnits.filter(u => selectedPresetNames.value.includes(u.name) && !isUnitSavedInDb(u.name));
    if (unitsToSave.length === 0) {
        if (props.units.length > 0) {
            nextStep();
        }
        return;
    }

    bulkUnitForm.units = unitsToSave.map(u => ({
        name: u.name,
        symbol: u.symbol,
        unit_category: u.unit_category,
    }));

    bulkUnitForm.post(route('definitions.units.store'), {
        preserveScroll: true,
        onSuccess: () => {
            selectedPresetNames.value = [];
            nextStep();
        }
    });
};

const unitForm = useForm({
    name: '',
    symbol: '',
    unit_category: 'quantity',
});
const submitUnit = () => {
    unitForm.post(route('definitions.units.store'), {
        preserveScroll: true,
        onSuccess: () => {
            unitForm.reset();
            showCustomUnitForm.value = false;
        }
    });
};

const deleteExistingUnit = (unit: any) => {
    if (confirm(`'${unit.name}' birimini silmek istediğinize emin misiniz?`)) {
        router.delete(route('definitions.units.destroy', unit.id), {
            preserveScroll: true,
        });
    }
};

const productForm = useForm({
    name: '',
    code: '',
    product_type: 'produced',
    company_ids: [] as number[],
    unit_ids: [] as number[],
    subtypes: [] as any[],
});
const newSubtypeName = ref('');
const addSubtype = () => {
    if (!newSubtypeName.value.trim()) return;
    productForm.subtypes.push({ name: newSubtypeName.value.trim() });
    newSubtypeName.value = '';
};
const removeSubtype = (idx: number) => {
    productForm.subtypes.splice(idx, 1);
};
const submitProduct = () => {
    productForm.post(route('definitions.products.store'), {
        preserveScroll: true,
        onSuccess: () => {
            productForm.reset();
            productForm.subtypes = [];
        }
    });
};

const activePersonTab = ref<'personnel' | 'crew' | 'worker'>('personnel');

const personnelForm = useForm({
    first_name: '',
    last_name: '',
    phone: '',
    email: '',
    password: '',
    role_title: '',
    company_ids: [] as number[],
    permissions: ['tesis', 'uretim'] as string[],
    is_active: true,
});
const submitPersonnel = () => {
    personnelForm.post(route('definitions.personnels.store'), {
        preserveScroll: true,
        onSuccess: () => {
            personnelForm.reset();
        }
    });
};

const crewLeaderForm = useForm({
    first_name: '',
    last_name: '',
    phone: '',
    daily_wage: 1000,
    multiplier: 1.0,
    travel_fee_per_car: 200,
    is_food_included: false,
    is_leader_fee_included: true,
});
const submitCrewLeader = () => {
    crewLeaderForm.post(route('definitions.crew-leaders.store'), {
        preserveScroll: true,
        onSuccess: () => {
            crewLeaderForm.reset();
        }
    });
};

const workerForm = useForm({
    first_name: '',
    last_name: '',
    crew_leader_id: '',
    identity_number: '',
    performance_rating: 5,
    is_active: true,
});
const submitWorker = () => {
    workerForm.post(route('definitions.workers.store'), {
        preserveScroll: true,
        onSuccess: () => {
            workerForm.reset();
        }
    });
};

const jobTypeForm = useForm({
    name: '',
    code: '',
    form_type: 'general',
    description: '',
});
const submitJobType = () => {
    jobTypeForm.post(route('definitions.job-types.store'), {
        preserveScroll: true,
        onSuccess: () => {
            jobTypeForm.reset();
        }
    });
};

const waterSourceForm = useForm({
    name: '',
    production_location_id: '',
    active_cycle_minutes: 60,
    passive_cycle_minutes: 120,
    requires_photo_verification: true,
    is_active: true,
});
const submitWaterSource = () => {
    waterSourceForm.post(route('definitions.water-sources.store'), {
        preserveScroll: true,
        onSuccess: () => {
            waterSourceForm.reset();
        }
    });
};

const exitSetup = () => {
    router.visit(route('dashboard'));
};
</script>

<template>
    <Head title="Sistem Kurulum Sihirbazı - SASA Tarım ERP" />

    <div class="fixed inset-0 bg-cover bg-center font-sans select-none overflow-hidden" style="background-image: url('/loginbackground.webp');">
        <div class="absolute inset-0 bg-[#030704]/90 backdrop-blur-xl"></div>
        <div class="absolute inset-0 bg-gradient-to-tr from-[#020503] via-emerald-950/20 to-[#020503]/80"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-gradient-to-tr from-emerald-500/10 via-teal-500/10 to-amber-500/5 blur-[160px] rounded-full pointer-events-none"></div>

        <div class="relative z-50 w-full h-full flex items-center justify-center p-2 sm:p-5 md:p-8">
            
            <div class="w-full max-w-5xl bg-[#080e0b]/95 border border-emerald-500/20 rounded-3xl shadow-[0_25px_80px_rgba(0,0,0,0.95)] backdrop-blur-2xl flex flex-col max-h-[94vh] overflow-hidden text-zinc-100 ring-1 ring-white/10">
                
                <div class="px-6 py-4 border-b border-white/10 bg-white/[0.03] flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500/20 to-teal-500/10 border border-emerald-400/30 flex items-center justify-center shrink-0 shadow-[0_0_20px_rgba(52,211,153,0.25)]">
                            <img src="/sasaerp.svg" alt="SASA Logo" class="w-7 h-7 object-contain drop-shadow" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2.5">
                                <h1 class="text-base sm:text-lg font-black text-white tracking-tight">SASA Tarım ERP Kurulum Sihirbazı</h1>
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-400/30 text-emerald-300 text-[11px] font-extrabold tracking-wider">
                                    Adım {{ currentStep }} / 8
                                </span>
                            </div>
                            <p class="text-xs text-zinc-400 font-medium hidden sm:block">Kurumsal tarım işletmenizin operasyonel altyapısını adım adım yapılandırın.</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="hidden sm:flex items-center gap-2.5 px-3.5 py-1.5 rounded-xl bg-black/40 border border-emerald-500/20 text-xs font-semibold text-zinc-300 shadow-inner">
                            <span class="text-zinc-400">Genel İlerleme:</span>
                            <div class="w-16 h-2 bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-emerald-400 to-teal-400 rounded-full transition-all duration-500" :style="{ width: progressPercent + '%' }"></div>
                            </div>
                            <span class="text-emerald-400 font-bold font-mono">%{{ progressPercent }}</span>
                        </div>

                        <button
                            type="button"
                            @click="exitSetup"
                            class="p-2.5 rounded-xl bg-white/5 border border-white/10 text-zinc-400 hover:text-white hover:bg-white/10 transition cursor-pointer"
                            title="Kapat ve Dashboard'a Dön"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="px-4 sm:px-6 py-2.5 border-b border-white/10 bg-black/40 shrink-0 overflow-x-auto no-scrollbar">
                    <div class="flex items-center gap-1.5 min-w-max">
                        <button
                            v-for="s in steps"
                            :key="s.id"
                            type="button"
                            @click="setStep(s.id)"
                            :class="[
                                'flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-bold transition-all duration-200 cursor-pointer border',
                                currentStep === s.id
                                    ? 'bg-gradient-to-r from-emerald-500/25 to-teal-500/25 border-emerald-400/60 text-emerald-300 shadow-[0_0_15px_rgba(52,211,153,0.25)] ring-1 ring-emerald-400/40'
                                    : completedSteps.includes(s.id)
                                        ? 'bg-emerald-950/20 border-emerald-500/20 text-zinc-300 hover:bg-white/10'
                                        : 'bg-transparent border-transparent text-zinc-500 hover:text-zinc-300'
                            ]"
                        >
                            <span
                                :class="[
                                    'w-5 h-5 rounded-lg flex items-center justify-center text-[10px] font-black shrink-0 shadow-sm',
                                    completedSteps.includes(s.id)
                                        ? 'bg-emerald-500 text-zinc-950 font-black'
                                        : currentStep === s.id
                                            ? 'bg-emerald-400/20 text-emerald-300 border border-emerald-400/40'
                                            : 'bg-white/10 text-zinc-400'
                                ]"
                            >
                                <span v-if="completedSteps.includes(s.id)">✓</span>
                                <span v-else>{{ s.id }}</span>
                            </span>
                            <span>{{ s.shortTitle }}</span>
                        </button>
                    </div>
                </div>

                <div class="p-5 sm:p-7 overflow-y-auto flex-1 space-y-6">

                    <div v-if="currentStep === 1" class="space-y-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-white/10">
                            <div>
                                <h2 class="text-lg sm:text-xl font-black text-white tracking-tight flex items-center gap-2">
                                    <span class="p-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400">🏢</span>
                                    1. Adım: Şirket & Firma Tanımlama
                                </h2>
                                <p class="text-xs text-zinc-400 mt-0.5">Tüm seralar, araziler ve tarımsal hasatların bağlanacağı ana tüzel kişilik.</p>
                            </div>
                            <span class="self-start sm:self-auto px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold font-mono">
                                {{ companies.length }} Firma Mevcut
                            </span>
                        </div>

                        <form @submit.prevent="submitCompany" class="p-5 rounded-2xl bg-zinc-950/70 border border-emerald-500/20 space-y-4 shadow-xl backdrop-blur-md">
                            <div class="text-xs font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                Yeni Firma Kaydı
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Firma Adı *</label>
                                    <input v-model="companyForm.name" required placeholder="Örn: SASA Tarım Üretim A.Ş." class="w-full px-4 py-2.5 rounded-xl bg-zinc-900/90 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none transition" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Firma Kodu *</label>
                                    <input v-model="companyForm.code" required placeholder="Örn: SASA-01" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900/90 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none transition" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Vergi Numarası</label>
                                    <input v-model="companyForm.tax_number" placeholder="Örn: 7480521943" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900/90 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none transition" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Dia ERP Kodu (Opsiyonel)</label>
                                    <input v-model="companyForm.dia_company_code" placeholder="Örn: DIA-SASA-01" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900/90 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none transition" />
                                </div>
                            </div>

                            <div class="flex justify-end pt-1">
                                <button type="submit" :disabled="companyForm.processing" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-zinc-950 font-bold text-xs shadow-[0_0_20px_rgba(52,211,153,0.3)] transition cursor-pointer">
                                    + Firmayı Kaydet & Ekle
                                </button>
                            </div>
                        </form>

                        <div v-if="companies.length > 0" class="space-y-2">
                            <h3 class="text-xs font-bold text-zinc-400 uppercase tracking-wide">Kayıtlı Firmalar</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div v-for="c in companies" :key="c.id" class="p-3.5 rounded-xl bg-white/[0.03] border border-white/10 flex items-center justify-between hover:border-emerald-500/30 transition">
                                    <div>
                                        <div class="text-xs font-bold text-white">{{ c.name }}</div>
                                        <div class="text-[11px] text-zinc-400 font-mono mt-0.5">Kod: {{ c.code }} | VN: {{ c.tax_number || '-' }}</div>
                                    </div>
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.6)]"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="currentStep === 2" class="space-y-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-white/10">
                            <div>
                                <h2 class="text-lg sm:text-xl font-black text-white tracking-tight flex items-center gap-2">
                                    <span class="p-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400">🚜</span>
                                    2. Adım: Üretim Sahaları & Parseller
                                </h2>
                                <p class="text-xs text-zinc-400 mt-0.5">Sera veya açık tarlalarınızı, iç bloklarını ve sulama vanalarını tanımlayın.</p>
                            </div>
                            <span class="self-start sm:self-auto px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold font-mono">
                                {{ productionLocations.length }} Saha Kayıtlı
                            </span>
                        </div>

                        <form @submit.prevent="submitLocation" class="p-5 rounded-2xl bg-zinc-950/70 border border-emerald-500/20 space-y-4 shadow-xl backdrop-blur-md">
                            <div class="text-xs font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                Yeni Üretim Sahası Ekle
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Bağlı Firma *</label>
                                    <select v-model="locationForm.company_id" required class="w-full px-4 py-2.5 rounded-xl bg-zinc-900/90 border border-white/15 text-white text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none">
                                        <option value="">Firma Seçiniz</option>
                                        <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Saha / Parsel Adı *</label>
                                    <input v-model="locationForm.name" required placeholder="Örn: 1 Nolu Muz Serası" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900/90 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Toplam Alan (Dekar)</label>
                                    <input v-model="locationForm.total_area_dekar" type="number" step="0.1" placeholder="Örn: 42.5" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900/90 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                                <div class="p-3.5 bg-zinc-900/80 rounded-xl border border-white/10 space-y-2">
                                    <label class="block text-xs font-semibold text-zinc-300">İç Bloklar / Bölümler (Opsiyonel)</label>
                                    <div class="flex gap-2">
                                        <input v-model="newSectionName" placeholder="Örn: A Blok, 1. Tünel" class="flex-1 px-3 py-1.5 rounded-lg bg-zinc-950 border border-white/15 text-xs text-white" />
                                        <button type="button" @click="addSection" class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-bold text-white cursor-pointer">+ Ekle</button>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 pt-1">
                                        <span v-for="(sec, idx) in locationForm.sections" :key="idx" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-semibold">
                                            {{ sec.name }}
                                            <button type="button" @click="removeSection(idx)" class="text-red-400 font-bold">×</button>
                                        </span>
                                    </div>
                                </div>

                                <div class="p-3.5 bg-zinc-900/80 rounded-xl border border-white/10 space-y-2">
                                    <label class="block text-xs font-semibold text-zinc-300">Sulama Vanaları (Opsiyonel)</label>
                                    <div class="flex gap-2">
                                        <input v-model="newValveName" placeholder="Örn: Vana 1, Vana 2" class="flex-1 px-3 py-1.5 rounded-lg bg-zinc-950 border border-white/15 text-xs text-white" />
                                        <button type="button" @click="addValve" class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-bold text-white cursor-pointer">+ Ekle</button>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 pt-1">
                                        <span v-for="(v, idx) in locationForm.valves" :key="idx" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-teal-500/20 text-teal-300 border border-teal-500/30 text-xs font-semibold">
                                            {{ v.name }}
                                            <button type="button" @click="removeValve(idx)" class="text-red-400 font-bold">×</button>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end pt-1">
                                <button type="submit" :disabled="locationForm.processing" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-zinc-950 font-bold text-xs shadow-[0_0_20px_rgba(52,211,153,0.3)] transition cursor-pointer">
                                    + Üretim Sahasını Kaydet
                                </button>
                            </div>
                        </form>
                    </div>

                    <div v-if="currentStep === 3" class="space-y-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-white/10">
                            <div>
                                <h2 class="text-lg sm:text-xl font-black text-white tracking-tight flex items-center gap-2">
                                    <span class="p-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                                    </span>
                                    <span>3. Adım: Ölçü Birimleri</span>
                                </h2>
                                <p class="text-xs text-zinc-400 mt-0.5">Hasat tartımlarında, stokta ve reçetelerde kullanılacak standart ve hazır ölçü birimleri.</p>
                            </div>
                            <span class="self-start sm:self-auto px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold font-mono">
                                {{ units.length }} Birim Kayıtlı
                            </span>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                            <div class="lg:col-span-8 bg-zinc-950/70 border border-emerald-500/20 p-5 rounded-2xl space-y-4 shadow-xl backdrop-blur-md flex flex-col justify-between">
                                <div class="space-y-3.5">
                                    <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-zinc-800">
                                        <div class="flex items-center gap-2.5">
                                            <h3 class="text-sm font-black text-white tracking-wide">Birim Seçimi</h3>
                                            <span class="text-[11px] font-mono px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-300 font-bold border border-emerald-500/30">
                                                {{ selectedPresetNames.length }} Seçili
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-1.5 text-xs">
                                            <button
                                                type="button"
                                                @click="selectRecommendedUnits"
                                                class="px-3 py-1.5 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-300 border border-emerald-500/30 text-xs font-bold transition cursor-pointer flex items-center gap-1.5 active:scale-95"
                                            >
                                                <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                <span>Önerilenleri Seç</span>
                                            </button>
                                            <button
                                                type="button"
                                                @click="selectAllUnits"
                                                class="px-3 py-1.5 rounded-xl bg-zinc-800/80 hover:bg-zinc-700 text-zinc-200 text-xs font-bold transition cursor-pointer border border-zinc-700/60 active:scale-95"
                                            >
                                                Tümünü Seç
                                            </button>
                                            <button
                                                v-if="selectedPresetNames.length > 0"
                                                type="button"
                                                @click="clearSelectedUnits"
                                                class="px-2.5 py-1.5 rounded-xl text-rose-400 hover:bg-rose-950/30 text-xs font-semibold transition cursor-pointer"
                                            >
                                                Temizle
                                            </button>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap gap-1 p-1 rounded-xl bg-zinc-900/90 border border-zinc-800 text-xs">
                                        <button
                                            type="button"
                                            @click="unitCategoryFilter = 'all'"
                                            :class="[
                                                'px-3 py-1 rounded-lg font-bold transition cursor-pointer text-xs',
                                                unitCategoryFilter === 'all' ? 'bg-zinc-800 text-white shadow-xs' : 'text-zinc-400 hover:text-zinc-200'
                                            ]"
                                        >
                                            Tümü ({{ presetUnits.length }})
                                        </button>
                                        <button
                                            type="button"
                                            @click="unitCategoryFilter = 'Ağırlık & Kütle'"
                                            :class="[
                                                'px-3 py-1 rounded-lg font-bold transition cursor-pointer text-xs',
                                                unitCategoryFilter === 'Ağırlık & Kütle' ? 'bg-zinc-800 text-white shadow-xs' : 'text-zinc-400 hover:text-zinc-200'
                                            ]"
                                        >
                                            Ağırlık
                                        </button>
                                        <button
                                            type="button"
                                            @click="unitCategoryFilter = 'Sayı & Paket'"
                                            :class="[
                                                'px-3 py-1 rounded-lg font-bold transition cursor-pointer text-xs',
                                                unitCategoryFilter === 'Sayı & Paket' ? 'bg-zinc-800 text-white shadow-xs' : 'text-zinc-400 hover:text-zinc-200'
                                            ]"
                                        >
                                            Sayı & Ambalaj
                                        </button>
                                        <button
                                            type="button"
                                            @click="unitCategoryFilter = 'Hacim & Sıvı'"
                                            :class="[
                                                'px-3 py-1 rounded-lg font-bold transition cursor-pointer text-xs',
                                                unitCategoryFilter === 'Hacim & Sıvı' ? 'bg-zinc-800 text-white shadow-xs' : 'text-zinc-400 hover:text-zinc-200'
                                            ]"
                                        >
                                            Hacim & Sıvı
                                        </button>
                                        <button
                                            type="button"
                                            @click="unitCategoryFilter = 'Alan & Uzunluk'"
                                            :class="[
                                                'px-3 py-1 rounded-lg font-bold transition cursor-pointer text-xs',
                                                unitCategoryFilter === 'Alan & Uzunluk' ? 'bg-zinc-800 text-white shadow-xs' : 'text-zinc-400 hover:text-zinc-200'
                                            ]"
                                        >
                                            Alan & Uzunluk
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-2 max-h-72 overflow-y-auto pr-1">
                                        <div
                                            v-for="pu in filteredPresetUnits"
                                            :key="pu.name"
                                            @click="togglePresetUnit(pu)"
                                            :class="[
                                                'p-2.5 rounded-xl border transition-all duration-150 flex items-center justify-between select-none',
                                                isUnitSavedInDb(pu.name)
                                                    ? 'bg-zinc-900/40 border-zinc-800/60 opacity-60 cursor-default'
                                                    : selectedPresetNames.includes(pu.name)
                                                        ? 'bg-emerald-950/40 border-emerald-500/70 shadow-sm ring-1 ring-emerald-500/30 cursor-pointer'
                                                        : 'bg-zinc-900/80 border-zinc-800/80 hover:border-zinc-700 hover:bg-zinc-800/40 cursor-pointer'
                                            ]"
                                        >
                                            <div class="flex items-center gap-2 min-w-0">
                                                <span
                                                    :class="[
                                                        'w-7 h-7 rounded-lg flex items-center justify-center font-mono font-black text-[10px] shrink-0 border uppercase',
                                                        isUnitSavedInDb(pu.name)
                                                            ? 'bg-zinc-800/60 border-zinc-700 text-zinc-400'
                                                            : selectedPresetNames.includes(pu.name)
                                                                ? 'bg-emerald-500/20 border-emerald-500/40 text-emerald-300'
                                                                : 'bg-zinc-800/70 border-zinc-700/60 text-zinc-300'
                                                    ]"
                                                >
                                                    {{ pu.symbol }}
                                                </span>
                                                <div class="min-w-0">
                                                    <div class="text-xs font-bold text-white flex items-center gap-1 truncate">
                                                        <span class="truncate">{{ pu.name }}</span>
                                                        <span v-if="pu.recommended && !isUnitSavedInDb(pu.name)" class="w-1.5 h-1.5 rounded-full bg-amber-400 shrink-0" title="Öneri"></span>
                                                    </div>
                                                    <div class="text-[10px] text-zinc-500 font-medium truncate">{{ pu.category_label }}</div>
                                                </div>
                                            </div>

                                            <div class="shrink-0 ml-1">
                                                <span v-if="isUnitSavedInDb(pu.name)" class="text-emerald-400">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                </span>
                                                <span v-else-if="selectedPresetNames.includes(pu.name)" class="w-4 h-4 rounded-md bg-emerald-500 text-zinc-950 flex items-center justify-center font-bold">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                </span>
                                                <span v-else class="w-4 h-4 rounded-md border border-zinc-700 bg-zinc-900 flex items-center justify-center"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pt-2 border-t border-zinc-800">
                                        <div class="flex items-center justify-between">
                                            <button
                                                type="button"
                                                @click="showCustomUnitForm = !showCustomUnitForm"
                                                class="text-xs text-emerald-400 hover:text-emerald-300 font-bold transition flex items-center gap-1.5 cursor-pointer"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                </svg>
                                                <span>{{ showCustomUnitForm ? 'Özel Birim Formunu Gizle' : 'Listede Olmayan Özel Birim Ekle' }}</span>
                                            </button>
                                        </div>

                                        <form v-if="showCustomUnitForm" @submit.prevent="submitUnit" class="mt-2.5 p-3 rounded-xl bg-zinc-900 border border-zinc-700/80 grid grid-cols-1 sm:grid-cols-3 gap-2.5 items-end">
                                            <div>
                                                <label class="block text-[10px] font-bold text-zinc-300 mb-1 uppercase">Birim Adı *</label>
                                                <input v-model="unitForm.name" required placeholder="Örn: Bidon, Sandık" class="w-full bg-zinc-950 border border-zinc-700 rounded-lg px-3 py-1.5 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-emerald-500" />
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-zinc-300 mb-1 uppercase">Sembol / Kod *</label>
                                                <input v-model="unitForm.symbol" required placeholder="Örn: bdn, sdk" class="w-full bg-zinc-950 border border-zinc-700 rounded-lg px-3 py-1.5 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-emerald-500" />
                                            </div>
                                            <div>
                                                <button type="submit" :disabled="unitForm.processing" class="w-full py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition cursor-pointer">
                                                    Özel Birimi Ekle
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="pt-3 border-t border-zinc-800 flex items-center justify-between">
                                    <div class="text-xs text-zinc-400">
                                        <span v-if="selectedPresetNames.length > 0" class="text-emerald-400 font-bold">{{ selectedPresetNames.length }} birim seçildi</span>
                                        <span v-else class="text-zinc-500">Seçilen yeni birim yok</span>
                                    </div>

                                    <button
                                        type="button"
                                        @click="submitSelectedUnits"
                                        :disabled="bulkUnitForm.processing"
                                        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-zinc-950 font-black text-xs shadow-lg transition cursor-pointer flex items-center gap-2"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                        <span>{{ selectedPresetNames.length > 0 ? `Seçilen ${selectedPresetNames.length} Birimi Kaydet & İlerle` : 'Birimleri Onayla & İlerle' }}</span>
                                    </button>
                                </div>
                            </div>

                            <div class="lg:col-span-4 bg-zinc-950/40 p-5 rounded-2xl border border-zinc-800 flex flex-col justify-between">
                                <div>
                                    <h4 class="text-xs font-black text-white uppercase tracking-wide mb-3 flex items-center justify-between pb-2 border-b border-zinc-800">
                                        <span>Kayıtlı Birimler</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-zinc-800 text-emerald-400">{{ units.length }} Kayıt</span>
                                    </h4>
                                    <div class="space-y-1.5 overflow-y-auto max-h-72 pr-1">
                                        <div
                                            v-for="u in units"
                                            :key="u.id"
                                            class="p-2 rounded-xl bg-zinc-900/90 border border-zinc-800/80 flex items-center justify-between hover:border-zinc-700 transition"
                                        >
                                            <div class="flex items-center gap-2 min-w-0">
                                                <span class="w-6 h-6 rounded-md bg-zinc-800 text-emerald-400 font-mono font-black text-[10px] flex items-center justify-center shrink-0 uppercase border border-zinc-700/60">
                                                    {{ u.symbol || u.code || '-' }}
                                                </span>
                                                <span class="text-xs font-bold text-white truncate">{{ u.name }}</span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                <button
                                                    type="button"
                                                    @click="deleteExistingUnit(u)"
                                                    class="p-1 rounded-md text-zinc-500 hover:text-rose-400 hover:bg-zinc-800 transition cursor-pointer"
                                                    title="Birimi Sil"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div v-if="units.length === 0" class="text-center py-8 text-xs text-zinc-500">
                                            Henüz kayıtlı ölçü birimi yok.
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 pt-3 border-t border-zinc-800 text-[11px] text-zinc-400 text-center">
                                    En az 1 adet birim ekleyerek ilerleyebilirsiniz.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="currentStep === 4" class="space-y-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-white/10">
                            <div>
                                <h2 class="text-lg sm:text-xl font-black text-white tracking-tight flex items-center gap-2">
                                    <span class="p-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400">🌾</span>
                                    4. Adım: Tarımsal Ürünler & Kalite Sınıfları
                                </h2>
                                <p class="text-xs text-zinc-400 mt-0.5">Üretimi veya tüketimi yapılan mahsulleri ve kalite / boy sınıflarını ekleyin.</p>
                            </div>
                            <span class="self-start sm:self-auto px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold font-mono">
                                {{ products.length }} Ürün Kayıtlı
                            </span>
                        </div>

                        <form @submit.prevent="submitProduct" class="p-5 rounded-2xl bg-zinc-950/70 border border-emerald-500/20 space-y-4 shadow-xl backdrop-blur-md">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Ürün Adı *</label>
                                    <input v-model="productForm.name" required placeholder="Örn: Yerli Muz, Avokado (Hass), Çilek" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900/90 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Ürün Kodu *</label>
                                    <input v-model="productForm.code" required placeholder="Örn: PRD-MUZ-01" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900/90 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                            </div>

                            <div class="p-3.5 bg-zinc-900/80 rounded-xl border border-white/10 space-y-2">
                                <label class="block text-xs font-semibold text-zinc-300">Kalite / Boy Sınıfları (Opsiyonel)</label>
                                <div class="flex gap-2">
                                    <input v-model="newSubtypeName" placeholder="Örn: 1. Kalite (Duble), 2. Kalite, Çıkma" class="flex-1 px-3 py-1.5 rounded-lg bg-zinc-950 border border-white/15 text-xs text-white" />
                                    <button type="button" @click="addSubtype" class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-bold text-white cursor-pointer">+ Ekle</button>
                                </div>
                                <div class="flex flex-wrap gap-1.5 pt-1">
                                    <span v-for="(sub, idx) in productForm.subtypes" :key="idx" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-semibold">
                                        {{ sub.name }}
                                        <button type="button" @click="removeSubtype(idx)" class="text-red-400 font-bold">×</button>
                                    </span>
                                </div>
                            </div>

                            <div class="flex justify-end pt-1">
                                <button type="submit" :disabled="productForm.processing" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-zinc-950 font-bold text-xs shadow-[0_0_20px_rgba(52,211,153,0.3)] transition cursor-pointer">
                                    + Ürünü Kaydet
                                </button>
                            </div>
                        </form>
                    </div>

                    <div v-if="currentStep === 5" class="space-y-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-white/10">
                            <div>
                                <h2 class="text-lg sm:text-xl font-black text-white tracking-tight flex items-center gap-2">
                                    <span class="p-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400">👥</span>
                                    5. Adım: Personel & İş Gücü Kadrosu
                                </h2>
                                <p class="text-xs text-zinc-400 mt-0.5">Yönetici mühendisler, ekip başı çavuşlar ve tarım işçileri.</p>
                            </div>
                            <div class="flex gap-2">
                                <span class="px-2.5 py-1 rounded-full bg-white/5 border border-white/10 text-xs font-bold text-zinc-300 font-mono">
                                    {{ personnels.length }} Personel
                                </span>
                                <span class="px-2.5 py-1 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-xs font-bold font-mono">
                                    {{ crewLeaders.length }} Çavuş
                                </span>
                            </div>
                        </div>

                        <div class="flex border-b border-white/10 gap-4">
                            <button type="button" @click="activePersonTab = 'personnel'" :class="['pb-2.5 text-xs font-extrabold transition border-b-2 cursor-pointer flex items-center gap-1.5', activePersonTab === 'personnel' ? 'border-emerald-400 text-emerald-300' : 'border-transparent text-zinc-400 hover:text-white']">
                                <span>👔</span> Mühendis / Yönetici
                            </button>
                            <button type="button" @click="activePersonTab = 'crew'" :class="['pb-2.5 text-xs font-extrabold transition border-b-2 cursor-pointer flex items-center gap-1.5', activePersonTab === 'crew' ? 'border-emerald-400 text-emerald-300' : 'border-transparent text-zinc-400 hover:text-white']">
                                <span>🧢</span> Çavuş (Ekip Başı)
                            </button>
                            <button type="button" @click="activePersonTab = 'worker'" :class="['pb-2.5 text-xs font-extrabold transition border-b-2 cursor-pointer flex items-center gap-1.5', activePersonTab === 'worker' ? 'border-emerald-400 text-emerald-300' : 'border-transparent text-zinc-400 hover:text-white']">
                                <span>🧤</span> Saha İşçisi
                            </button>
                        </div>

                        <form v-if="activePersonTab === 'personnel'" @submit.prevent="submitPersonnel" class="p-5 rounded-2xl bg-zinc-950/70 border border-emerald-500/20 space-y-4 shadow-xl backdrop-blur-md">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Ad *</label>
                                    <input v-model="personnelForm.first_name" required placeholder="Ad" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900/90 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Soyad *</label>
                                    <input v-model="personnelForm.last_name" required placeholder="Soyad" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900/90 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Unvan / Rol</label>
                                    <input v-model="personnelForm.role_title" placeholder="Örn: Ziraat Mühendisi, Saha Sorumlusu" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900/90 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Telefon</label>
                                    <input v-model="personnelForm.phone" placeholder="05XX XXX XX XX" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900/90 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">E-posta (Giriş İçin)</label>
                                    <input v-model="personnelForm.email" type="email" placeholder="muhendis@sasa.com" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900/90 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Giriş Şifresi</label>
                                    <input v-model="personnelForm.password" type="text" placeholder="Varsayılan: password" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900/90 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                            </div>
                            <div class="flex justify-end pt-1">
                                <button type="submit" :disabled="personnelForm.processing" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-zinc-950 font-bold text-xs shadow-[0_0_20px_rgba(52,211,153,0.3)] transition cursor-pointer">
                                    + Personeli Kaydet
                                </button>
                            </div>
                        </form>

                        <form v-if="activePersonTab === 'crew'" @submit.prevent="submitCrewLeader" class="p-5 rounded-2xl bg-zinc-950/70 border border-emerald-500/20 space-y-4 shadow-xl backdrop-blur-md">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Çavuş Adı *</label>
                                    <input v-model="crewLeaderForm.first_name" required placeholder="Ad" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900/90 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Çavuş Soyadı *</label>
                                    <input v-model="crewLeaderForm.last_name" required placeholder="Soyad" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900/90 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Günlük Yevmiye (₺)</label>
                                    <input v-model="crewLeaderForm.daily_wage" type="number" placeholder="Örn: 1000" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900/90 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                            </div>
                            <div class="flex justify-end pt-1">
                                <button type="submit" :disabled="crewLeaderForm.processing" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-zinc-950 font-bold text-xs shadow-[0_0_20px_rgba(52,211,153,0.3)] transition cursor-pointer">
                                    + Çavuşu Kaydet
                                </button>
                            </div>
                        </form>

                        <form v-if="activePersonTab === 'worker'" @submit.prevent="submitWorker" class="p-5 rounded-2xl bg-zinc-950/70 border border-emerald-500/20 space-y-4 shadow-xl backdrop-blur-md">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Bağlı Çavuş</label>
                                    <select v-model="workerForm.crew_leader_id" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900/90 border border-white/15 text-white text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none">
                                        <option value="">Bağımsız İşçi</option>
                                        <option v-for="c in crewLeaders" :key="c.id" :value="c.id">{{ c.first_name }} {{ c.last_name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">İşçi Adı *</label>
                                    <input v-model="workerForm.first_name" required placeholder="Ad" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900/90 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">İşçi Soyadı *</label>
                                    <input v-model="workerForm.last_name" required placeholder="Soyad" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900/90 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                            </div>
                            <div class="flex justify-end pt-1">
                                <button type="submit" :disabled="workerForm.processing" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-zinc-950 font-bold text-xs shadow-[0_0_20px_rgba(52,211,153,0.3)] transition cursor-pointer">
                                    + İşçiyi Kaydet
                                </button>
                            </div>
                        </form>
                    </div>

                    <div v-if="currentStep === 6" class="space-y-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-white/10">
                            <div>
                                <h2 class="text-lg sm:text-xl font-black text-white tracking-tight flex items-center gap-2">
                                    <span class="p-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400">📋</span>
                                    6. Adım: Tarımsal İş & Görev Tanımları
                                </h2>
                                <p class="text-xs text-zinc-400 mt-0.5">Puantaj ve günlük iş formlarında seçilecek saha görevleri.</p>
                            </div>
                            <span class="self-start sm:self-auto px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold font-mono">
                                {{ jobTypes.length }} İş Tipi Mevcut
                            </span>
                        </div>

                        <form @submit.prevent="submitJobType" class="p-5 rounded-2xl bg-zinc-950/70 border border-emerald-500/20 space-y-4 shadow-xl backdrop-blur-md">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">İş / Görev Adı *</label>
                                    <input v-model="jobTypeForm.name" required placeholder="Örn: Hasat Toplama, Ağaç Budama, Çapa" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900/90 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">İş Kodu *</label>
                                    <input v-model="jobTypeForm.code" required placeholder="Örn: IS-HASAT-01" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900/90 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                            </div>

                            <div class="flex justify-end pt-1">
                                <button type="submit" :disabled="jobTypeForm.processing" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-zinc-950 font-bold text-xs shadow-[0_0_20px_rgba(52,211,153,0.3)] transition cursor-pointer">
                                    + İş Tanımını Kaydet
                                </button>
                            </div>
                        </form>

                        <div v-if="jobTypes.length > 0" class="space-y-2">
                            <h3 class="text-xs font-bold text-zinc-400 uppercase tracking-wide">Kayıtlı İş Tanımları</h3>
                            <div class="flex flex-wrap gap-2">
                                <span v-for="j in jobTypes" :key="j.id" class="px-3.5 py-2 rounded-xl bg-white/[0.03] border border-white/10 text-xs font-bold text-zinc-200">
                                    {{ j.name }} <span class="text-emerald-400 font-mono ml-1">({{ j.code }})</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div v-if="currentStep === 7" class="space-y-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-white/10">
                            <div>
                                <h2 class="text-lg sm:text-xl font-black text-white tracking-tight flex items-center gap-2">
                                    <span class="p-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400">💧</span>
                                    7. Adım: Altyapı & Su Kaynakları
                                </h2>
                                <p class="text-xs text-zinc-400 mt-0.5">Sulamada kullanılan kuyu, gölet, havuz ve filtre istasyonları.</p>
                            </div>
                            <span class="self-start sm:self-auto px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold font-mono">
                                {{ waterSources.length }} Su Kaynağı
                            </span>
                        </div>

                        <form @submit.prevent="submitWaterSource" class="p-5 rounded-2xl bg-zinc-950/70 border border-emerald-500/20 space-y-4 shadow-xl backdrop-blur-md">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Su Kaynağı Adı *</label>
                                    <input v-model="waterSourceForm.name" required placeholder="Örn: 1 Nolu Derin Kuyu, Ana Havuz" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900/90 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Bağlı Saha / Parsel *</label>
                                    <select v-model="waterSourceForm.production_location_id" required class="w-full px-4 py-2.5 rounded-xl bg-zinc-900/90 border border-white/15 text-white text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none">
                                        <option value="">Saha Seçiniz</option>
                                        <option v-for="l in productionLocations" :key="l.id" :value="l.id">{{ l.name }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex justify-end pt-1">
                                <button type="submit" :disabled="waterSourceForm.processing" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-zinc-950 font-bold text-xs shadow-[0_0_20px_rgba(52,211,153,0.3)] transition cursor-pointer">
                                    + Su Kaynağını Kaydet
                                </button>
                            </div>
                        </form>
                    </div>

                    <div v-if="currentStep === 8" class="space-y-6">
                        <div class="text-center max-w-xl mx-auto space-y-2 py-3">
                            <div class="w-16 h-16 rounded-3xl bg-gradient-to-tr from-emerald-500/20 to-teal-400/20 border border-emerald-400/40 text-emerald-300 flex items-center justify-center mx-auto mb-2 shadow-[0_0_35px_rgba(52,211,153,0.3)]">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <h2 class="text-2xl font-black text-white tracking-tight">Tebrikler! Kurulum Altyapısı Hazır</h2>
                            <p class="text-xs sm:text-sm text-zinc-400 font-medium">Sistem için tanımlanan temel modüllerin durumu ve veriler aşağıda özetlenmiştir.</p>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
                            <div class="p-4 rounded-2xl bg-white/[0.03] border border-emerald-500/20 text-center backdrop-blur-md hover:border-emerald-500/40 transition">
                                <div class="text-2xl font-black text-emerald-400 font-mono">{{ stepStats.companies_count }}</div>
                                <div class="text-xs font-semibold text-zinc-300 mt-1">Kayıtlı Firma</div>
                            </div>
                            <div class="p-4 rounded-2xl bg-white/[0.03] border border-emerald-500/20 text-center backdrop-blur-md hover:border-emerald-500/40 transition">
                                <div class="text-2xl font-black text-emerald-400 font-mono">{{ stepStats.locations_count }}</div>
                                <div class="text-xs font-semibold text-zinc-300 mt-1">Parsel & Tesis</div>
                            </div>
                            <div class="p-4 rounded-2xl bg-white/[0.03] border border-emerald-500/20 text-center backdrop-blur-md hover:border-emerald-500/40 transition">
                                <div class="text-2xl font-black text-emerald-400 font-mono">{{ stepStats.products_count }}</div>
                                <div class="text-xs font-semibold text-zinc-300 mt-1">Tarımsal Ürün</div>
                            </div>
                            <div class="p-4 rounded-2xl bg-white/[0.03] border border-emerald-500/20 text-center backdrop-blur-md hover:border-emerald-500/40 transition">
                                <div class="text-2xl font-black text-emerald-400 font-mono">{{ stepStats.units_count }}</div>
                                <div class="text-xs font-semibold text-zinc-300 mt-1">Ölçü Birimi</div>
                            </div>
                            <div class="p-4 rounded-2xl bg-white/[0.03] border border-emerald-500/20 text-center backdrop-blur-md hover:border-emerald-500/40 transition">
                                <div class="text-2xl font-black text-emerald-400 font-mono">{{ stepStats.personnels_count }}</div>
                                <div class="text-xs font-semibold text-zinc-300 mt-1">Personel / Kadro</div>
                            </div>
                            <div class="p-4 rounded-2xl bg-white/[0.03] border border-emerald-500/20 text-center backdrop-blur-md hover:border-emerald-500/40 transition">
                                <div class="text-2xl font-black text-emerald-400 font-mono">{{ stepStats.crew_leaders_count }}</div>
                                <div class="text-xs font-semibold text-zinc-300 mt-1">Çavuş (Ekip Başı)</div>
                            </div>
                            <div class="p-4 rounded-2xl bg-white/[0.03] border border-emerald-500/20 text-center backdrop-blur-md hover:border-emerald-500/40 transition">
                                <div class="text-2xl font-black text-emerald-400 font-mono">{{ stepStats.job_types_count }}</div>
                                <div class="text-xs font-semibold text-zinc-300 mt-1">İş / Görev Tipi</div>
                            </div>
                            <div class="p-4 rounded-2xl bg-white/[0.03] border border-emerald-500/20 text-center backdrop-blur-md hover:border-emerald-500/40 transition">
                                <div class="text-2xl font-black text-emerald-400 font-mono">{{ stepStats.water_sources_count }}</div>
                                <div class="text-xs font-semibold text-zinc-300 mt-1">Su Kaynağı</div>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-2">
                            <Link :href="route('dashboard')" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-zinc-950 font-black text-sm text-center shadow-[0_0_35px_rgba(52,211,153,0.35)] transition-all hover:scale-[1.02]">
                                🚀 Kurulumu Tamamla & Dashboard'a Git
                            </Link>
                            <Link :href="route('agriculture.index')" class="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-white/10 hover:bg-white/15 text-white font-bold text-sm text-center transition">
                                🌾 Tarım Operasyonlarına Başla
                            </Link>
                        </div>
                    </div>

                </div>

                <div class="px-6 py-4 border-t border-white/10 bg-white/[0.02] flex items-center justify-between shrink-0">
                    <button
                        type="button"
                        @click="prevStep"
                        :disabled="currentStep === 1"
                        class="px-4 py-2 rounded-xl border border-white/15 bg-white/5 text-xs font-bold text-zinc-300 hover:bg-white/10 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer flex items-center gap-1.5"
                    >
                        <span>← Önceki</span>
                    </button>

                    <button
                        v-if="currentStep < 8"
                        type="button"
                        @click="nextStep"
                        class="text-xs font-semibold text-zinc-400 hover:text-white transition cursor-pointer px-3 py-1"
                    >
                        Bu Adımı Atla
                    </button>

                    <button
                        v-if="currentStep < 8"
                        type="button"
                        @click="nextStep"
                        class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-zinc-950 text-xs font-black shadow-[0_0_20px_rgba(52,211,153,0.3)] transition cursor-pointer flex items-center gap-1.5 hover:scale-[1.02]"
                    >
                        <span>Sonraki Adım</span>
                        <span>→</span>
                    </button>
                    <Link
                        v-else
                        :href="route('dashboard')"
                        class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-zinc-950 text-xs font-black shadow-[0_0_20px_rgba(52,211,153,0.3)] transition flex items-center gap-1.5"
                    >
                        Tamamla ✓
                    </Link>
                </div>

            </div>

        </div>
    </div>
</template>
