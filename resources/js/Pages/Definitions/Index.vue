<script setup lang="ts">
import { ref, computed, watch, defineAsyncComponent } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const CompaniesTab = defineAsyncComponent(() => import('@/Components/Definitions/CompaniesTab.vue'));
const PersonnelsTab = defineAsyncComponent(() => import('@/Components/Definitions/PersonnelsTab.vue'));
const ProductionLocationsTab = defineAsyncComponent(() => import('@/Components/Definitions/ProductionLocationsTab.vue'));
const TradingPartiesTab = defineAsyncComponent(() => import('@/Components/Definitions/TradingPartiesTab.vue'));
const DeliveryTypesTab = defineAsyncComponent(() => import('@/Components/Definitions/DeliveryTypesTab.vue'));
const CrewLeadersTab = defineAsyncComponent(() => import('@/Components/Definitions/CrewLeadersTab.vue'));
const WorkersTab = defineAsyncComponent(() => import('@/Components/Definitions/WorkersTab.vue'));
const CateringSuppliersTab = defineAsyncComponent(() => import('@/Components/Definitions/CateringSuppliersTab.vue'));
const ProductsTab = defineAsyncComponent(() => import('@/Components/Definitions/ProductsTab.vue'));
const JobTypesTab = defineAsyncComponent(() => import('@/Components/Definitions/JobTypesTab.vue'));
const UnitsTab = defineAsyncComponent(() => import('@/Components/Definitions/UnitsTab.vue'));
const PackagingsTab = defineAsyncComponent(() => import('@/Components/Definitions/PackagingsTab.vue'));
const RecipesTab = defineAsyncComponent(() => import('@/Components/Definitions/RecipesTab.vue'));
const WaterAndFiltersTab = defineAsyncComponent(() => import('@/Components/Definitions/WaterAndFiltersTab.vue'));
const CrewLeaderWorkersModal = defineAsyncComponent(() => import('@/Components/Definitions/CrewLeaderWorkersModal.vue'));
const DefinitionFormModal = defineAsyncComponent(() => import('@/Components/Definitions/DefinitionFormModal.vue'));

const page = usePage();

const props = defineProps<{
    activeTab?: string;
    companies: any[];
    personnels: any[];
    tradingParties: any[];
    deliveryTypes: any[];
    productionLocations: any[];
    products: any[];
    jobTypes: any[];
    units: any[];
    packagings: any[];
    crewLeaders: any[];
    workers: any[];
    cateringSuppliers: any[];
    fertilizationRecipes: any[];
    sprayingRecipes: any[];
    waterSources: any[];
    filters: any[];
}>();

const getInitialTab = () => {
    if (typeof window !== 'undefined') {
        const params = new URLSearchParams(window.location.search);
        const tab = params.get('tab');
        if (tab) return tab;
    }
    return props.activeTab || 'companies';
};

const currentTab = ref(getInitialTab());

watch(() => page.url, () => {
    const params = new URLSearchParams(window.location.search);
    const tab = params.get('tab');
    if (tab) currentTab.value = tab;
});

const showModal = ref(false);
const modalType = ref('');
const editItemData = ref<any>(null);

const showCrewLeaderWorkersModal = ref(false);
const selectedCrewLeader = ref<any>(null);

const openCrewLeaderWorkersModal = (cl: any) => {
    selectedCrewLeader.value = cl;
    showCrewLeaderWorkersModal.value = true;
};

const closeCrewLeaderWorkersModal = () => {
    showCrewLeaderWorkersModal.value = false;
    selectedCrewLeader.value = null;
};

const openAddWorkerForCrewLeader = (cl: any) => {
    closeCrewLeaderWorkersModal();
    openModal('worker', { crew_leader_id: cl.id });
};

const editWorkerFromModal = (w: any) => {
    closeCrewLeaderWorkersModal();
    openModal('worker', w);
};

const openModal = (type: string, item: any = null) => {
    modalType.value = type;
    editItemData.value = item;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editItemData.value = null;
};

const toggleActiveFertRecipe = (id: number) => useForm({}).post(route('definitions.fertilization-recipes.toggle-active', id));

const deleteItem = (routeName: string, id: number) => {
    if (confirm('Bu kaydı silmek istediğinize emin misiniz?')) {
        useForm({}).delete(route(routeName, id));
    }
};

const menuGroups = [
    {
        title: 'Firma & Tesis Yönetimi',
        items: [
            { key: 'companies', label: 'Firma Listesi' },
            { key: 'production_locations', label: 'Üretim Yeri & Vana' },
            { key: 'trading_parties', label: 'Alıcı & Satıcı Carileri' },
            { key: 'delivery_types', label: 'Ürün Teslimat Şekli' },
        ]
    },
    {
        title: 'Personel & Ekip Yönetimi',
        items: [
            { key: 'personnels', label: 'Personel & Görev Hiyerarşisi' },
            { key: 'crew_leaders', label: 'Çavuşlar (Ekip Başı)' },
            { key: 'workers', label: 'İşçiler' },
            { key: 'catering_suppliers', label: 'Yemek Tedarikçileri' },
        ]
    },
    {
        title: 'Ürün & Üretim Tanımları',
        items: [
            { key: 'products', label: 'Ürünler & Kalite Sınıfları' },
            { key: 'job_types', label: 'İş Tanımları & Formlar' },
            { key: 'units', label: 'Birim Tanımları' },
            { key: 'packagings', label: 'Paketleme Şekilleri' },
        ]
    },
    {
        title: 'Reçete & Altyapı Yönetimi',
        items: [
            { key: 'recipes', label: 'Gübre & İlaç Reçeteleri' },
            { key: 'water_and_filters', label: 'Su Kaynakları & Filtreler' },
        ]
    }
];

const currentTabLabel = computed(() => {
    for (const g of menuGroups) {
        const found = g.items.find((i: any) => i.key === currentTab.value);
        if (found) return found.label;
    }
    return 'Tanım';
});
</script>

<template>
    <Head title="Sistem Tanımlamaları" />

    <AuthenticatedLayout>
        <template #header>
            <div class="w-full flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 border border-rose-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-sm font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight">
                                Sistem Tanımlamaları
                            </h1>
                            <span class="text-slate-300 dark:text-slate-700">/</span>
                            <span class="text-xs font-bold text-rose-600 dark:text-rose-400">
                                {{ currentTabLabel }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 leading-tight">Firma, personel, reçete, su kaynağı ve operasyonel parametreler</p>
                    </div>
                </div>
            </div>
        </template>

        <div class="space-y-8">
            <CompaniesTab
                v-if="currentTab === 'companies'"
                :companies="companies"
                @create="openModal('company')"
                @edit="(c) => openModal('company', c)"
                @delete="(id) => deleteItem('definitions.companies.destroy', id)"
            />

            <PersonnelsTab
                v-if="currentTab === 'personnels'"
                :personnels="personnels"
                :companies="companies"
                @create="openModal('personnel')"
                @edit="(p) => openModal('personnel', p)"
                @delete="(id) => deleteItem('definitions.personnels.destroy', id)"
            />

            <ProductionLocationsTab
                v-if="currentTab === 'production_locations'"
                :production-locations="productionLocations"
                @create="openModal('production_location')"
                @edit="(pl) => openModal('production_location', pl)"
                @delete="(id) => deleteItem('definitions.production-locations.destroy', id)"
            />

            <TradingPartiesTab
                v-if="currentTab === 'trading_parties'"
                :trading-parties="tradingParties"
                @create="openModal('trading_party')"
                @edit="(tp) => openModal('trading_party', tp)"
                @delete="(id) => deleteItem('definitions.trading-parties.destroy', id)"
            />

            <DeliveryTypesTab
                v-if="currentTab === 'delivery_types'"
                :delivery-types="deliveryTypes"
                @create="openModal('delivery_type')"
                @edit="(dt) => openModal('delivery_type', dt)"
            />

            <CrewLeadersTab
                v-if="currentTab === 'crew_leaders'"
                :crew-leaders="crewLeaders"
                :workers="workers"
                @create="openModal('crew_leader')"
                @edit="(cl) => openModal('crew_leader', cl)"
                @delete="(id) => deleteItem('definitions.crew-leaders.destroy', id)"
                @open-workers="openCrewLeaderWorkersModal"
            />

            <WorkersTab
                v-if="currentTab === 'workers'"
                :workers="workers"
                :crew-leaders="crewLeaders"
                @create="openModal('worker')"
                @edit="(w) => openModal('worker', w)"
                @delete="(id) => deleteItem('definitions.workers.destroy', id)"
                @open-crew-leader-workers="openCrewLeaderWorkersModal"
            />

            <CateringSuppliersTab
                v-if="currentTab === 'catering_suppliers'"
                :catering-suppliers="cateringSuppliers"
                @create="openModal('catering_supplier')"
                @edit="(cs) => openModal('catering_supplier', cs)"
                @delete="(id) => deleteItem('definitions.catering-suppliers.destroy', id)"
            />

            <ProductsTab
                v-if="currentTab === 'products'"
                :products="products"
                @create="openModal('product')"
                @edit="(p) => openModal('product', p)"
                @delete="(id) => deleteItem('definitions.products.destroy', id)"
            />

            <JobTypesTab
                v-if="currentTab === 'job_types'"
                :job-types="jobTypes"
                @create="openModal('job_type')"
                @edit="(jt) => openModal('job_type', jt)"
                @delete="(id) => deleteItem('definitions.job-types.destroy', id)"
            />

            <UnitsTab
                v-if="currentTab === 'units'"
                :units="units"
                @create="openModal('unit')"
                @edit="(u) => openModal('unit', u)"
                @delete="(id) => deleteItem('definitions.units.destroy', id)"
            />

            <PackagingsTab
                v-if="currentTab === 'packagings'"
                :packagings="packagings"
                @create="openModal('packaging')"
                @edit="(pkg) => openModal('packaging', pkg)"
                @delete="(id) => deleteItem('definitions.packagings.destroy', id)"
            />

            <RecipesTab
                v-if="currentTab === 'recipes'"
                :fertilization-recipes="fertilizationRecipes"
                :spraying-recipes="sprayingRecipes"
                @create-fert="openModal('fert_recipe')"
                @create-spray="openModal('spray_recipe')"
                @edit-fert="(fr) => openModal('fert_recipe', fr)"
                @edit-spray="(sr) => openModal('spray_recipe', sr)"
                @delete-fert="(id) => deleteItem('definitions.fertilization-recipes.destroy', id)"
                @delete-spray="(id) => deleteItem('definitions.spraying-recipes.destroy', id)"
                @toggle-active-fert="toggleActiveFertRecipe"
            />

            <WaterAndFiltersTab
                v-if="currentTab === 'water_and_filters'"
                :water-sources="waterSources"
                :filters="filters"
                @create-water-source="openModal('water_source')"
                @create-filter="openModal('filter')"
                @edit-water-source="(ws) => openModal('water_source', ws)"
                @edit-filter="(f) => openModal('filter', f)"
                @delete-water-source="(id) => deleteItem('definitions.water-sources.destroy', id)"
                @delete-filter="(id) => deleteItem('definitions.filters.destroy', id)"
            />
        </div>

        <DefinitionFormModal
            :show="showModal"
            :type="modalType"
            :item="editItemData"
            :companies="companies"
            :personnels="personnels"
            :units="units"
            :products="products"
            :crew-leaders="crewLeaders"
            :production-locations="productionLocations"
            :water-sources="waterSources"
            @close="closeModal"
            @saved="closeModal"
        />


        <CrewLeaderWorkersModal
            :show="showCrewLeaderWorkersModal"
            :crew-leader="selectedCrewLeader"
            :workers="workers"
            @close="closeCrewLeaderWorkersModal"
            @add-worker="openAddWorkerForCrewLeader"
            @edit-worker="editWorkerFromModal"
            @delete-worker="(id) => deleteItem('definitions.workers.destroy', id)"
        />
    </AuthenticatedLayout>
</template>
