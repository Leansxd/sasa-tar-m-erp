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
    { id: 1, title: 'Firma', shortTitle: '1. Firma', desc: 'Şirket ve tüzel kişilik' },
    { id: 2, title: 'Üretim Yeri / Arsa', shortTitle: '2. Arsa & Parsel', desc: 'Sera, parsel ve vanalar' },
    { id: 3, title: 'Ölçü Birimleri', shortTitle: '3. Birimler', desc: 'kg, kasa, adet vb.' },
    { id: 4, title: 'Ürünler', shortTitle: '4. Ürünler', desc: 'Mahsul ve kalite sınıfları' },
    { id: 5, title: 'İş Gücü', shortTitle: '5. Personel', desc: 'Mühendis, çavuş ve işçiler' },
    { id: 6, title: 'İş Tipleri', shortTitle: '6. Görevler', desc: 'Hasat, budama vb. formlar' },
    { id: 7, title: 'Altyapı & Su', shortTitle: '7. Altyapı', desc: 'Su kaynakları ve filtreler' },
    { id: 8, title: 'Özet', shortTitle: '8. Başlat', desc: 'Kurulumu tamamla' },
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

// Form 1: Company
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

// Form 2: Production Location & Sections & Valves
const locationForm = useForm({
    name: '',
    code: '',
    company_id: '',
    total_area_decare: '',
    location_type: 'sera',
    sections: [] as any[],
    valves: [] as any[],
    is_active: true,
});
const newSectionName = ref('');
const newValveName = ref('');

const addSection = () => {
    if (!newSectionName.value.trim()) return;
    locationForm.sections.push({ name: newSectionName.value.trim(), code: newSectionName.value.trim() });
    newSectionName.value = '';
};
const removeSection = (idx: number) => {
    locationForm.sections.splice(idx, 1);
};
const addValve = () => {
    if (!newValveName.value.trim()) return;
    locationForm.valves.push({ valve_name: newValveName.value.trim(), valve_code: newValveName.value.trim() });
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

// Form 3: Units
const unitForm = useForm({
    name: '',
    code: '',
    is_active: true,
});
const submitUnit = () => {
    unitForm.post(route('definitions.units.store'), {
        preserveScroll: true,
        onSuccess: () => {
            unitForm.reset();
        }
    });
};

// Form 4: Product & Quality Subtypes
const productForm = useForm({
    name: '',
    code: '',
    company_ids: [] as number[],
    unit_ids: [] as number[],
    subtypes: [] as any[],
    is_active: true,
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

// Form 5: Staff
const activePersonTab = ref<'personnel' | 'crew' | 'worker'>('personnel');

const personnelForm = useForm({
    first_name: '',
    last_name: '',
    phone: '',
    email: '',
    password: '',
    role_title: '',
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
    default_daily_wage: '',
    is_active: true,
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
    daily_wage: '',
    phone: '',
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

// Form 6: Job Type
const jobTypeForm = useForm({
    name: '',
    code: '',
    unit_ids: [] as number[],
    requires_weight: false,
    requires_quality: false,
    is_active: true,
});
const submitJobType = () => {
    jobTypeForm.post(route('definitions.job-types.store'), {
        preserveScroll: true,
        onSuccess: () => {
            jobTypeForm.reset();
        }
    });
};

// Form 7: Water Source
const waterSourceForm = useForm({
    name: '',
    code: '',
    source_type: 'kuyu',
    production_location_id: '',
    capacity_m3_hour: '',
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

    <!-- Arka Plan (Login Sayfası ile Uyumlu Karartılmış Tarım Görseli & Aura) -->
    <div class="fixed inset-0 bg-cover bg-center font-sans select-none overflow-hidden" style="background-image: url('/loginbackground.webp');">
        <!-- Global Karartma & Gradyan Katmanları -->
        <div class="absolute inset-0 bg-[#040805]/85 backdrop-blur-md"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-[#020503] via-transparent to-[#020503]/70"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[700px] bg-gradient-to-tr from-emerald-500/15 via-teal-500/10 to-amber-500/10 blur-[150px] rounded-full pointer-events-none"></div>

        <!-- POPUP MODAL WRAPPER (CENTERED) -->
        <div class="relative z-50 w-full h-full flex items-center justify-center p-3 sm:p-6 md:p-8">
            
            <div class="w-full max-w-5xl bg-[#090f0c]/95 border border-white/15 rounded-3xl shadow-[0_30px_90px_rgba(0,0,0,0.95)] backdrop-blur-2xl flex flex-col max-h-[92vh] overflow-hidden text-zinc-200">
                
                <!-- 1. POPUP HEADER -->
                <div class="px-6 py-5 border-b border-white/10 bg-white/[0.02] flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center shrink-0 shadow-[0_0_15px_rgba(52,211,153,0.2)]">
                            <img src="/sasaerp.svg" alt="SASA Logo" class="w-7 h-7 object-contain" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h1 class="text-base sm:text-lg font-black text-white tracking-tight">Sistem Kurulum Sihirbazı</h1>
                                <span class="px-2 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-[10px] font-bold tracking-wider uppercase">
                                    Adım {{ currentStep }} / 8
                                </span>
                            </div>
                            <p class="text-xs text-zinc-400 font-medium hidden sm:block">İşletmenizin temel verilerini sırasıyla oluşturup kaydedin.</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 text-xs font-semibold text-zinc-300">
                            <span>Tamamlanma:</span>
                            <span class="text-emerald-400 font-bold font-mono">%{{ progressPercent }}</span>
                        </div>

                        <button
                            type="button"
                            @click="exitSetup"
                            class="p-2 rounded-xl bg-white/5 border border-white/10 text-zinc-400 hover:text-white hover:bg-white/10 transition cursor-pointer"
                            title="Kapat ve Dashboard'a Dön"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- 2. POPUP STEP NAVIGATION TAB BAR -->
                <div class="px-6 py-3 border-b border-white/10 bg-black/30 shrink-0 overflow-x-auto no-scrollbar">
                    <div class="flex items-center gap-2 min-w-max">
                        <button
                            v-for="s in steps"
                            :key="s.id"
                            type="button"
                            @click="setStep(s.id)"
                            :class="[
                                'flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all duration-200 cursor-pointer border',
                                currentStep === s.id
                                    ? 'bg-gradient-to-r from-emerald-500/20 to-teal-500/20 border-emerald-400/50 text-emerald-300 shadow-[0_0_15px_rgba(52,211,153,0.2)]'
                                    : completedSteps.includes(s.id)
                                        ? 'bg-white/5 border-white/10 text-zinc-300 hover:bg-white/10'
                                        : 'bg-transparent border-transparent text-zinc-500 hover:text-zinc-300'
                            ]"
                        >
                            <span
                                :class="[
                                    'w-5 h-5 rounded-lg flex items-center justify-center text-[10px] font-black shrink-0',
                                    completedSteps.includes(s.id)
                                        ? 'bg-emerald-500 text-black'
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

                <!-- 3. POPUP MODAL SCROLLABLE BODY -->
                <div class="p-6 sm:p-8 overflow-y-auto flex-1 space-y-6">

                    <!-- STEP 1: FİRMA / ŞİRKET -->
                    <div v-if="currentStep === 1" class="space-y-6">
                        <div class="flex items-center justify-between pb-3 border-b border-white/10">
                            <div>
                                <h2 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                                    <span class="p-1 rounded-md bg-emerald-500/10 text-emerald-400">🏢</span>
                                    1. Adım: Şirket & Firma Tanımlama
                                </h2>
                                <p class="text-xs text-zinc-400 mt-0.5">Tüm araziler, ürünler ve operasyonların bağlı olacağı ana işletme tüzel kişiliğini kaydedin.</p>
                            </div>
                            <span class="px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold">
                                {{ companies.length }} Firma Mevcut
                            </span>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submitCompany" class="p-5 rounded-2xl bg-zinc-950/60 border border-white/15 space-y-4 shadow-xl">
                            <div class="text-xs font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                Yeni Firma Bilgisi
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Firma Adı *</label>
                                    <input v-model="companyForm.name" required placeholder="Örn: SASA Tarım Üretim A.Ş." class="w-full px-4 py-2.5 rounded-xl bg-zinc-900 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Firma Kodu *</label>
                                    <input v-model="companyForm.code" required placeholder="Örn: SASA-01" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Vergi Numarası</label>
                                    <input v-model="companyForm.tax_number" placeholder="Örn: 7480521943" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Dia ERP Şirket Kodu (Opsiyonel)</label>
                                    <input v-model="companyForm.dia_company_code" placeholder="Örn: DIA-SASA-01" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="submit" :disabled="companyForm.processing" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-zinc-950 font-bold text-xs shadow-[0_0_20px_rgba(52,211,153,0.3)] transition cursor-pointer">
                                    + Firmayı Kaydet & Ekle
                                </button>
                            </div>
                        </form>

                        <!-- Liste -->
                        <div v-if="companies.length > 0" class="space-y-2">
                            <h3 class="text-xs font-bold text-zinc-400 uppercase tracking-wide">Sistemde Kayıtlı Firmalar</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div v-for="c in companies" :key="c.id" class="p-3.5 rounded-xl bg-white/5 border border-white/10 flex items-center justify-between">
                                    <div>
                                        <div class="text-xs font-bold text-white">{{ c.name }}</div>
                                        <div class="text-[11px] text-zinc-400 font-mono mt-0.5">Kod: {{ c.code }} | VN: {{ c.tax_number || '-' }}</div>
                                    </div>
                                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2: ÜRETİM YERLERİ & ARSALAR / PARSELLER -->
                    <div v-if="currentStep === 2" class="space-y-6">
                        <div class="flex items-center justify-between pb-3 border-b border-white/10">
                            <div>
                                <h2 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                                    <span class="p-1 rounded-md bg-emerald-500/10 text-emerald-400">🚜</span>
                                    2. Adım: Üretim Yerleri & Arsalar / Parseller
                                </h2>
                                <p class="text-xs text-zinc-400 mt-0.5">Sera, açık tarla veya meyve bahçelerinizi, iç bloklarını ve sulama vanalarını tanımlayın.</p>
                            </div>
                            <span class="px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold">
                                {{ productionLocations.length }} Lokasyon Kayıtlı
                            </span>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submitLocation" class="p-5 rounded-2xl bg-zinc-950/60 border border-white/15 space-y-4 shadow-xl">
                            <div class="text-xs font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                Yeni Üretim Yeri / Parsel Ekle
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Bağlı Firma *</label>
                                    <select v-model="locationForm.company_id" required class="w-full px-4 py-2.5 rounded-xl bg-zinc-900 border border-white/15 text-white text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none">
                                        <option value="">Firma Seçiniz</option>
                                        <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Üretim Yeri / Parsel Adı *</label>
                                    <input v-model="locationForm.name" required placeholder="Örn: 1 Nolu Muz Serası" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Alan (Dekar)</label>
                                    <input v-model="locationForm.total_area_decare" type="number" step="0.1" placeholder="Örn: 35.5" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                            </div>

                            <!-- Bölüm & Vana -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                                <div class="p-3.5 bg-zinc-900/90 rounded-xl border border-white/10 space-y-2">
                                    <label class="block text-xs font-semibold text-zinc-300">İç Bölümler / Bloklar (Opsiyonel)</label>
                                    <div class="flex gap-2">
                                        <input v-model="newSectionName" placeholder="Örn: A Blok, Parsel 1" class="flex-1 px-3 py-1.5 rounded-lg bg-zinc-950 border border-white/15 text-xs text-white" />
                                        <button type="button" @click="addSection" class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-bold text-white cursor-pointer">+ Ekle</button>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 pt-1">
                                        <span v-for="(sec, idx) in locationForm.sections" :key="idx" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-semibold">
                                            {{ sec.name }}
                                            <button type="button" @click="removeSection(idx)" class="text-red-400 font-bold">×</button>
                                        </span>
                                    </div>
                                </div>

                                <div class="p-3.5 bg-zinc-900/90 rounded-xl border border-white/10 space-y-2">
                                    <label class="block text-xs font-semibold text-zinc-300">Sulama Vanaları (Opsiyonel)</label>
                                    <div class="flex gap-2">
                                        <input v-model="newValveName" placeholder="Örn: Vana 1, Vana 2" class="flex-1 px-3 py-1.5 rounded-lg bg-zinc-950 border border-white/15 text-xs text-white" />
                                        <button type="button" @click="addValve" class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-bold text-white cursor-pointer">+ Ekle</button>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 pt-1">
                                        <span v-for="(v, idx) in locationForm.valves" :key="idx" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-teal-500/20 text-teal-300 border border-teal-500/30 text-xs font-semibold">
                                            {{ v.valve_name }}
                                            <button type="button" @click="removeValve(idx)" class="text-red-400 font-bold">×</button>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="submit" :disabled="locationForm.processing" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-zinc-950 font-bold text-xs shadow-[0_0_20px_rgba(52,211,153,0.3)] transition cursor-pointer">
                                    + Üretim Yerini Kaydet & Ekle
                                </button>
                            </div>
                        </form>

                        <!-- Liste -->
                        <div v-if="productionLocations.length > 0" class="space-y-2">
                            <h3 class="text-xs font-bold text-zinc-400 uppercase tracking-wide">Kayıtlı Parsel & Tesisler</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div v-for="l in productionLocations" :key="l.id" class="p-3.5 rounded-xl bg-white/5 border border-white/10 flex items-center justify-between">
                                    <div>
                                        <div class="text-xs font-bold text-white">{{ l.name }}</div>
                                        <div class="text-[11px] text-zinc-400 mt-0.5">{{ l.company?.name || 'Firma Yok' }} | {{ l.total_area_decare || 0 }} Dekar | {{ l.sections?.length || 0 }} Blok</div>
                                    </div>
                                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 3: ÖLÇÜ BİRİMLERİ -->
                    <div v-if="currentStep === 3" class="space-y-6">
                        <div class="flex items-center justify-between pb-3 border-b border-white/10">
                            <div>
                                <h2 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                                    <span class="p-1 rounded-md bg-emerald-500/10 text-emerald-400">📦</span>
                                    3. Adım: Ölçü Birimleri & Paketleme
                                </h2>
                                <p class="text-xs text-zinc-400 mt-0.5">Hasat tartımlarında, stokta ve reçetelerde kullanılacak temel ölçü birimlerini belirleyin.</p>
                            </div>
                            <span class="px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold">
                                {{ units.length }} Birim Mevcut
                            </span>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submitUnit" class="p-5 rounded-2xl bg-zinc-950/60 border border-white/15 space-y-4 shadow-xl">
                            <div class="text-xs font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                Yeni Ölçü Birimi Ekle
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Birim Adı *</label>
                                    <input v-model="unitForm.name" required placeholder="Örn: Kilogram, Kasa, Adet, Ton, Litre" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Birim Kodu / Sembol *</label>
                                    <input v-model="unitForm.code" required placeholder="Örn: kg, ks, adet, ton, lt" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="submit" :disabled="unitForm.processing" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-zinc-950 font-bold text-xs shadow-[0_0_20px_rgba(52,211,153,0.3)] transition cursor-pointer">
                                    + Birimi Kaydet & Ekle
                                </button>
                            </div>
                        </form>

                        <!-- Liste -->
                        <div v-if="units.length > 0" class="space-y-2">
                            <h3 class="text-xs font-bold text-zinc-400 uppercase tracking-wide">Kayıtlı Ölçü Birimleri</h3>
                            <div class="flex flex-wrap gap-2">
                                <span v-for="u in units" :key="u.id" class="px-3.5 py-2 rounded-xl bg-white/5 border border-white/10 text-xs font-bold text-zinc-200">
                                    {{ u.name }} <span class="text-emerald-400 font-mono ml-1">({{ u.code }})</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 4: ÜRÜNLER & KALİTE SINIFLARI -->
                    <div v-if="currentStep === 4" class="space-y-6">
                        <div class="flex items-center justify-between pb-3 border-b border-white/10">
                            <div>
                                <h2 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                                    <span class="p-1 rounded-md bg-emerald-500/10 text-emerald-400">🌾</span>
                                    4. Adım: Tarımsal Ürünler & Çeşitler
                                </h2>
                                <p class="text-xs text-zinc-400 mt-0.5">Üretimi yapılan mahsulleri ve kalite / boy sınıflarını (1. Kalite, 2. Kalite vb.) tanımlayın.</p>
                            </div>
                            <span class="px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold">
                                {{ products.length }} Ürün Kayıtlı
                            </span>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submitProduct" class="p-5 rounded-2xl bg-zinc-950/60 border border-white/15 space-y-4 shadow-xl">
                            <div class="text-xs font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                Yeni Ürün Ekle
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Ürün Adı *</label>
                                    <input v-model="productForm.name" required placeholder="Örn: Anamur Muzu, Avokado" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Ürün Kodu *</label>
                                    <input v-model="productForm.code" required placeholder="Örn: URN-MUZ-01" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                            </div>

                            <!-- Kalite Sınıfları -->
                            <div class="p-3.5 bg-zinc-900/90 rounded-xl border border-white/10 space-y-2">
                                <label class="block text-xs font-semibold text-zinc-300">Kalite / Boy Sınıfları (Opsiyonel)</label>
                                <div class="flex gap-2">
                                    <input v-model="newSubtypeName" placeholder="Örn: 1. Kalite (Duble), 2. Kalite" class="flex-1 px-3 py-1.5 rounded-lg bg-zinc-950 border border-white/15 text-xs text-white" />
                                    <button type="button" @click="addSubtype" class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-bold text-white cursor-pointer">+ Ekle</button>
                                </div>
                                <div class="flex flex-wrap gap-1.5 pt-1">
                                    <span v-for="(sub, idx) in productForm.subtypes" :key="idx" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-semibold">
                                        {{ sub.name }}
                                        <button type="button" @click="removeSubtype(idx)" class="text-red-400 font-bold">×</button>
                                    </span>
                                </div>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="submit" :disabled="productForm.processing" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-zinc-950 font-bold text-xs shadow-[0_0_20px_rgba(52,211,153,0.3)] transition cursor-pointer">
                                    + Ürünü Kaydet & Ekle
                                </button>
                            </div>
                        </form>

                        <!-- Liste -->
                        <div v-if="products.length > 0" class="space-y-2">
                            <h3 class="text-xs font-bold text-zinc-400 uppercase tracking-wide">Kayıtlı Tarımsal Ürünler</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div v-for="p in products" :key="p.id" class="p-3.5 rounded-xl bg-white/5 border border-white/10 flex items-center justify-between">
                                    <div>
                                        <div class="text-xs font-bold text-white">{{ p.name }}</div>
                                        <div class="text-[11px] text-zinc-400 mt-0.5">Kod: {{ p.code }} | {{ p.subtypes?.length || 0 }} Kalite Sınıfı</div>
                                    </div>
                                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 5: PERSONEL, ÇAVUŞ & İŞÇİLER -->
                    <div v-if="currentStep === 5" class="space-y-6">
                        <div class="flex items-center justify-between pb-3 border-b border-white/10">
                            <div>
                                <h2 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                                    <span class="p-1 rounded-md bg-emerald-500/10 text-emerald-400">👥</span>
                                    5. Adım: Personel & İş Gücü Kadrosu
                                </h2>
                                <p class="text-xs text-zinc-400 mt-0.5">Ziraat mühendisleri, saha yöneticileri, ekip başı çavuşlar ve tarım işçilerini ekleyin.</p>
                            </div>
                            <div class="flex gap-2">
                                <span class="px-2.5 py-1 rounded-full bg-white/5 border border-white/10 text-xs font-bold text-zinc-300">
                                    {{ personnels.length }} Personel
                                </span>
                                <span class="px-2.5 py-1 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-xs font-bold">
                                    {{ crewLeaders.length }} Çavuş
                                </span>
                            </div>
                        </div>

                        <!-- Alt Sekme Değiştirici -->
                        <div class="flex border-b border-white/10 gap-3">
                            <button type="button" @click="activePersonTab = 'personnel'" :class="['pb-2 text-xs font-bold transition border-b-2 cursor-pointer', activePersonTab === 'personnel' ? 'border-emerald-400 text-emerald-300' : 'border-transparent text-zinc-400 hover:text-white']">
                                Yönetici & Mühendis
                            </button>
                            <button type="button" @click="activePersonTab = 'crew'" :class="['pb-2 text-xs font-bold transition border-b-2 cursor-pointer', activePersonTab === 'crew' ? 'border-emerald-400 text-emerald-300' : 'border-transparent text-zinc-400 hover:text-white']">
                                Çavuş (Ekip Başı)
                            </button>
                            <button type="button" @click="activePersonTab = 'worker'" :class="['pb-2 text-xs font-bold transition border-b-2 cursor-pointer', activePersonTab === 'worker' ? 'border-emerald-400 text-emerald-300' : 'border-transparent text-zinc-400 hover:text-white']">
                                Saha İşçisi
                            </button>
                        </div>

                        <!-- Personel Form -->
                        <form v-if="activePersonTab === 'personnel'" @submit.prevent="submitPersonnel" class="p-5 rounded-2xl bg-zinc-950/60 border border-white/15 space-y-4 shadow-xl">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Ad *</label>
                                    <input v-model="personnelForm.first_name" required placeholder="Ad" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Soyad *</label>
                                    <input v-model="personnelForm.last_name" required placeholder="Soyad" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Unvan / Görev</label>
                                    <input v-model="personnelForm.role_title" placeholder="Örn: Ziraat Mühendisi, Saha Şefi" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Telefon</label>
                                    <input v-model="personnelForm.phone" placeholder="05XX XXX XX XX" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                            </div>
                            <div class="flex justify-end pt-2">
                                <button type="submit" :disabled="personnelForm.processing" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-zinc-950 font-bold text-xs shadow-[0_0_20px_rgba(52,211,153,0.3)] transition cursor-pointer">
                                    + Personeli Kaydet
                                </button>
                            </div>
                        </form>

                        <!-- Çavuş Form -->
                        <form v-if="activePersonTab === 'crew'" @submit.prevent="submitCrewLeader" class="p-5 rounded-2xl bg-zinc-950/60 border border-white/15 space-y-4 shadow-xl">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Çavuş Adı *</label>
                                    <input v-model="crewLeaderForm.first_name" required placeholder="Ad" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Çavuş Soyadı *</label>
                                    <input v-model="crewLeaderForm.last_name" required placeholder="Soyad" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Varsayılan Günlük Yevmiye (₺)</label>
                                    <input v-model="crewLeaderForm.default_daily_wage" type="number" placeholder="Örn: 1000" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                            </div>
                            <div class="flex justify-end pt-2">
                                <button type="submit" :disabled="crewLeaderForm.processing" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-zinc-950 font-bold text-xs shadow-[0_0_20px_rgba(52,211,153,0.3)] transition cursor-pointer">
                                    + Çavuşu Kaydet
                                </button>
                            </div>
                        </form>

                        <!-- İşçi Form -->
                        <form v-if="activePersonTab === 'worker'" @submit.prevent="submitWorker" class="p-5 rounded-2xl bg-zinc-950/60 border border-white/15 space-y-4 shadow-xl">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Bağlı Çavuş</label>
                                    <select v-model="workerForm.crew_leader_id" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900 border border-white/15 text-white text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none">
                                        <option value="">Bağımsız İşçi</option>
                                        <option v-for="c in crewLeaders" :key="c.id" :value="c.id">{{ c.first_name }} {{ c.last_name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">İşçi Adı *</label>
                                    <input v-model="workerForm.first_name" required placeholder="Ad" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">İşçi Soyadı *</label>
                                    <input v-model="workerForm.last_name" required placeholder="Soyad" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                            </div>
                            <div class="flex justify-end pt-2">
                                <button type="submit" :disabled="workerForm.processing" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-zinc-950 font-bold text-xs shadow-[0_0_20px_rgba(52,211,153,0.3)] transition cursor-pointer">
                                    + İşçiyi Kaydet
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- STEP 6: İŞ VE GÖREV TANIMLARI -->
                    <div v-if="currentStep === 6" class="space-y-6">
                        <div class="flex items-center justify-between pb-3 border-b border-white/10">
                            <div>
                                <h2 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                                    <span class="p-1 rounded-md bg-emerald-500/10 text-emerald-400">📋</span>
                                    6. Adım: İş & Operasyon Tipleri
                                </h2>
                                <p class="text-xs text-zinc-400 mt-0.5">Günlük puantaj ve hasat formlarında seçilecek işleri (Hasat, Budama, İlaçlama vb.) oluşturun.</p>
                            </div>
                            <span class="px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold">
                                {{ jobTypes.length }} İş Tipi Mevcut
                            </span>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submitJobType" class="p-5 rounded-2xl bg-zinc-950/60 border border-white/15 space-y-4 shadow-xl">
                            <div class="text-xs font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                Yeni İş Tanımı Ekle
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">İş / Görev Adı *</label>
                                    <input v-model="jobTypeForm.name" required placeholder="Örn: Hasat Toplama, Ağaç Budama, Çapa" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">İş Kodu *</label>
                                    <input v-model="jobTypeForm.code" required placeholder="Örn: IS-HASAT-01" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="submit" :disabled="jobTypeForm.processing" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-zinc-950 font-bold text-xs shadow-[0_0_20px_rgba(52,211,153,0.3)] transition cursor-pointer">
                                    + İş Tipini Kaydet & Ekle
                                </button>
                            </div>
                        </form>

                        <!-- Liste -->
                        <div v-if="jobTypes.length > 0" class="space-y-2">
                            <h3 class="text-xs font-bold text-zinc-400 uppercase tracking-wide">Kayıtlı İş Tipleri</h3>
                            <div class="flex flex-wrap gap-2">
                                <span v-for="j in jobTypes" :key="j.id" class="px-3.5 py-2 rounded-xl bg-white/5 border border-white/10 text-xs font-bold text-zinc-200">
                                    {{ j.name }} <span class="text-emerald-400 font-mono ml-1">({{ j.code }})</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 7: ALTYAPI & SU KAYNAKLARI -->
                    <div v-if="currentStep === 7" class="space-y-6">
                        <div class="flex items-center justify-between pb-3 border-b border-white/10">
                            <div>
                                <h2 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                                    <span class="p-1 rounded-md bg-emerald-500/10 text-emerald-400">💧</span>
                                    7. Adım: Altyapı & Su Kaynakları
                                </h2>
                                <p class="text-xs text-zinc-400 mt-0.5">Sulamada kullanılan kuyu, gölet, havuz ve filtre sistemlerini sisteme tanımlayın.</p>
                            </div>
                            <span class="px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold">
                                {{ waterSources.length }} Su Kaynağı
                            </span>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submitWaterSource" class="p-5 rounded-2xl bg-zinc-950/60 border border-white/15 space-y-4 shadow-xl">
                            <div class="text-xs font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                Yeni Su Kaynağı Ekle
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Kaynak Adı *</label>
                                    <input v-model="waterSourceForm.name" required placeholder="Örn: 1 Nolu Derin Kuyu" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900 border border-white/15 text-white placeholder-zinc-500 text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Kaynak Türü</label>
                                    <select v-model="waterSourceForm.source_type" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900 border border-white/15 text-white text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none">
                                        <option value="kuyu">Kuyu</option>
                                        <option value="havuz">Havuz / Gölet</option>
                                        <option value="sebeke">Şebeke / Kanal</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-300 mb-1.5 uppercase tracking-wide">Bağlı Parsel</label>
                                    <select v-model="waterSourceForm.production_location_id" class="w-full px-4 py-2.5 rounded-xl bg-zinc-900 border border-white/15 text-white text-xs font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-400 focus:outline-none">
                                        <option value="">Seçiniz</option>
                                        <option v-for="l in productionLocations" :key="l.id" :value="l.id">{{ l.name }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="submit" :disabled="waterSourceForm.processing" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-zinc-950 font-bold text-xs shadow-[0_0_20px_rgba(52,211,153,0.3)] transition cursor-pointer">
                                    + Su Kaynağını Kaydet
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- STEP 8: KURULUM ÖZETİ & BAŞLATMA -->
                    <div v-if="currentStep === 8" class="space-y-6">
                        <div class="text-center max-w-xl mx-auto space-y-2 py-4">
                            <div class="w-16 h-16 rounded-3xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 flex items-center justify-center mx-auto mb-3 shadow-[0_0_30px_rgba(52,211,153,0.25)]">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <h2 class="text-2xl font-black text-white tracking-tight">Tebrikler! Kurulum Özeti Hazır</h2>
                            <p class="text-xs sm:text-sm text-zinc-400 font-medium">Sistem için tanımlanan temel modüllerin durumu aşağıda listelenmiştir.</p>
                        </div>

                        <!-- İstatistik Kartları Grid (Glassmorphic) -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
                            <div class="p-4 rounded-2xl bg-white/5 border border-white/10 text-center backdrop-blur-md">
                                <div class="text-2xl font-black text-emerald-400 font-mono">{{ stepStats.companies_count }}</div>
                                <div class="text-xs font-semibold text-zinc-300 mt-1">Kayıtlı Firma</div>
                            </div>
                            <div class="p-4 rounded-2xl bg-white/5 border border-white/10 text-center backdrop-blur-md">
                                <div class="text-2xl font-black text-emerald-400 font-mono">{{ stepStats.locations_count }}</div>
                                <div class="text-xs font-semibold text-zinc-300 mt-1">Parsel & Tesis</div>
                            </div>
                            <div class="p-4 rounded-2xl bg-white/5 border border-white/10 text-center backdrop-blur-md">
                                <div class="text-2xl font-black text-emerald-400 font-mono">{{ stepStats.products_count }}</div>
                                <div class="text-xs font-semibold text-zinc-300 mt-1">Tarımsal Ürün</div>
                            </div>
                            <div class="p-4 rounded-2xl bg-white/5 border border-white/10 text-center backdrop-blur-md">
                                <div class="text-2xl font-black text-emerald-400 font-mono">{{ stepStats.units_count }}</div>
                                <div class="text-xs font-semibold text-zinc-300 mt-1">Ölçü Birimi</div>
                            </div>
                            <div class="p-4 rounded-2xl bg-white/5 border border-white/10 text-center backdrop-blur-md">
                                <div class="text-2xl font-black text-emerald-400 font-mono">{{ stepStats.personnels_count }}</div>
                                <div class="text-xs font-semibold text-zinc-300 mt-1">Personel / Kadro</div>
                            </div>
                            <div class="p-4 rounded-2xl bg-white/5 border border-white/10 text-center backdrop-blur-md">
                                <div class="text-2xl font-black text-emerald-400 font-mono">{{ stepStats.crew_leaders_count }}</div>
                                <div class="text-xs font-semibold text-zinc-300 mt-1">Çavuş (Ekip Başı)</div>
                            </div>
                            <div class="p-4 rounded-2xl bg-white/5 border border-white/10 text-center backdrop-blur-md">
                                <div class="text-2xl font-black text-emerald-400 font-mono">{{ stepStats.job_types_count }}</div>
                                <div class="text-xs font-semibold text-zinc-300 mt-1">İş / Görev Tipi</div>
                            </div>
                            <div class="p-4 rounded-2xl bg-white/5 border border-white/10 text-center backdrop-blur-md">
                                <div class="text-2xl font-black text-emerald-400 font-mono">{{ stepStats.water_sources_count }}</div>
                                <div class="text-xs font-semibold text-zinc-300 mt-1">Su Kaynağı</div>
                            </div>
                        </div>

                        <!-- Aksiyon Butonları -->
                        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
                            <Link :href="route('dashboard')" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-zinc-950 font-black text-sm text-center shadow-[0_0_30px_rgba(52,211,153,0.35)] transition-all hover:scale-[1.02]">
                                🚀 Kurulumu Tamamla & Dashboard'a Git
                            </Link>
                            <Link :href="route('agriculture.index')" class="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-white/10 hover:bg-white/15 text-white font-bold text-sm text-center transition">
                                🌾 Tarım Operasyonlarına Başla
                            </Link>
                        </div>
                    </div>

                </div>

                <!-- 4. POPUP FOOTER -->
                <div class="px-6 py-4 border-t border-white/10 bg-white/[0.02] flex items-center justify-between shrink-0">
                    <button
                        type="button"
                        @click="prevStep"
                        :disabled="currentStep === 1"
                        class="px-4 py-2.5 rounded-xl border border-white/15 bg-white/5 text-xs font-bold text-zinc-300 hover:bg-white/10 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer flex items-center gap-1.5"
                    >
                        <span>← Önceki</span>
                    </button>

                    <div class="flex items-center gap-2">
                        <button
                            v-if="currentStep < 8"
                            type="button"
                            @click="nextStep"
                            class="text-xs font-semibold text-zinc-400 hover:text-white transition cursor-pointer px-3 py-1"
                        >
                            Bu Adımı Atla
                        </button>
                    </div>

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
