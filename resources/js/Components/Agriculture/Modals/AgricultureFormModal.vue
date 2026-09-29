<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps<{
    show: boolean;
    type: string;
    item?: any;
    locations?: any[];
    jobTypes?: any[];
    personnels?: any[];
    fertRecipes?: any[];
    fertRuns?: any[];
    sprayRecipes?: any[];
    waterSources?: any[];
    tradingParties?: any[];
    products?: any[];
    packagings?: any[];
    customerOrders?: any[];
    deliveryTypes?: any[];
    companies?: any[];
    crewLeaders?: any[];
    workers?: any[];
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'saved'): void;
}>();

const modalTitles: Record<string, string> = {
    work_plan: 'İş Planı & Görev',
    fert_run: 'Gübreleme Reçetesi Başlat / Düzenle',
    sulama: 'Sulama / Sisleme Programı',
    spray_app: 'İlaçlama Uygulama Kaydı',
    water_analysis: 'Detaylı Su Analiz Raporu',
    raw_water: 'Kaynak Suyu ve Pompa Kontrolü',
    purification: 'Arıtma Giriş/Çıkış Basınç Kontrolü',
    order: 'Müşteri Siparişi Oluştur',
    order_edit: 'Müşteri Siparişini Düzenle',
    shipment: 'Yeni Sevkiyat & İrsaliye',
    daily_sheet: 'Günlük Çalışma & Hasat Formu',
    market_price: 'Piyasa Hal Fiyatı',
    crew_leader: 'Yeni Çavuş Kayıt Formu',
    worker: 'Yeni İşçi Kayıt Formu',
};

const workPlanForm = useForm({
    id: null as number | null,
    title: '',
    production_location_id: null as any,
    job_type_id: null as any,
    assigned_personnel_id: null as any,
    plan_date: new Date().toISOString().split('T')[0],
    due_date: '',
    status: 'pending',
    description: '',
    completion_notes: '',
});

const fertRunForm = useForm({
    id: null as number | null,
    fertilization_recipe_id: null as any,
    start_date: new Date().toISOString().split('T')[0],
    end_condition: '',
    is_active: true,
});

const irrigationForm = useForm({
    id: null as number | null,
    schedule_date: new Date().toISOString().split('T')[0],
    run_number: 1,
    start_time: '09:00',
    production_location_id: null as any,
    is_fertilized: false,
    fertilization_recipe_id: null as any,
    tank_stage_note: '',
    notes: '',
    valves: [] as any[],
});

const sprayAppForm = useForm({
    id: null as number | null,
    application_date: new Date().toISOString().split('T')[0],
    spraying_recipe_id: null as any,
    purpose: '',
    applied_by_id: null as any,
    covered_area_description: '',
    is_tank_finished: false,
    batch_code: '',
    notes: '',
});

const waterAnalysisForm = useForm({
    id: null as number | null,
    water_source_id: null as any,
    analysis_date: new Date().toISOString().split('T')[0],
    ph_level: '' as any,
    ec_level: '' as any,
    chemical_details: {
        lab_name: '',
        report_no: '',
        calcium: '' as any,
        magnesium: '' as any,
        sodium: '' as any,
        potassium: '' as any,
        bicarbonate: '' as any,
        sulfate: '' as any,
        chloride: '' as any,
        nitrate: '' as any,
        hardness: '' as any,
        sar: '' as any,
        tds: '' as any,
        suitability: 'Sulamaya Uygun (A Sınıfı)',
    },
    notes: '',
});

const rawWaterForm = useForm({
    id: null as number | null,
    water_source_id: null as any,
    control_date: new Date().toISOString().split('T')[0],
    ec_val: '' as any,
    ph_val: '' as any,
    pump_status: 'open',
    pump_fault_note: '',
    active_start_date: new Date().toISOString().split('T')[0],
    passive_start_date: '',
    source_switch_reason: '',
    is_filter_cleaned: false,
    water_tank_level: 'full',
    water_tank_note: '',
    chlorine_tank_level: 'full',
    dosing_pump_mode: 'auto',
    dosing_pump_manual_val: '',
    dosing_pump_fault_note: '',
});

const purificationForm = useForm({
    id: null as number | null,
    water_source_id: null as any,
    control_date: new Date().toISOString().split('T')[0],
    inlet_pressure_bar: '' as any,
    outlet_pressure_bar: '' as any,
    max_threshold_bar: '' as any,
});

const orderForm = useForm({
    id: null as number | null,
    trading_party_id: null as any,
    order_date: new Date().toISOString().split('T')[0],
    requested_delivery_date: '',
    contact_person: '',
    notes: '',
    items: [
        { product_id: null as any, packaging_definition_id: null as any, quantity: 100, unit_price: 60 }
    ],
});

const orderEditForm = useForm({
    id: null as number | null,
    trading_party_id: null as any,
    order_date: '',
    requested_delivery_date: '',
    contact_person: '',
    status: 'pending',
    notes: '',
    items: [] as any[],
});

const shipmentForm = useForm({
    id: null as number | null,
    trading_party_id: null as any,
    customer_order_id: null as any,
    product_id: null as any,
    packaging_definition_id: null as any,
    shipment_date: new Date().toISOString().split('T')[0],
    quantity: '' as any,
    unit_price: '' as any,
    dia_waybill_code: '',
    delivery_type_id: null as any,
    status: 'on_the_way',
    vehicle_plate: '',
    driver_name: '',
    driver_phone: '',
    notes: '',
});

const dailyWorkSheetForm = useForm({
    id: null as number | null,
    work_date: new Date().toISOString().split('T')[0],
    company_id: null as any,
    production_location_id: null as any,
    storage_destination: 'direct_sale',
    notes: '',
    crew_leaders: [
        { crew_leader_id: null as any, absent_worker_count: 0, extra_worker_count: 0, car_count: 1 }
    ],
    harvest_items: [
        { product_id: null as any, packaging_definition_id: null as any, package_count: 0, quantity: 0 }
    ]
});

const marketPriceForm = useForm({
    id: null as number | null,
    product_id: null as any,
    price_date: new Date().toISOString().split('T')[0],
    unit_price: '' as any,
    source_name: 'Antalya Toptancı Hali',
});

const crewLeaderForm = useForm({
    first_name: '',
    last_name: '',
    identity_number: '',
    phone: '',
    origin_city: '',
    daily_wage: '' as any,
    multiplier: 1.5,
    min_car_requirement: 1,
    travel_fee_per_car: '' as any,
    is_food_included: false,
    is_leader_fee_included: true,
    dia_cari_code: '',
});

const workerForm = useForm({
    crew_leader_id: null as any,
    first_name: '',
    last_name: '',
    identity_number: '',
    performance_rating: 5,
    notes: '',
});

const loadIrrigationValvesForLocation = () => {
    const loc = props.locations?.find((l: any) => l.id === irrigationForm.production_location_id);
    if (loc && loc.valves && loc.valves.length > 0) {
        irrigationForm.valves = loc.valves.map((v: any) => ({
            location_valve_id: v.id,
            production_location_id: loc.id,
            valve_id: v.id,
            valve_number: v.valve_number,
            name: `${loc.name} > ${v.name} (${v.duty === 'misting' ? 'Sisleme' : 'Sulama'})`,
            duration_minutes: 10,
        }));
    } else {
        irrigationForm.valves = [];
    }
};

const applyEqualDuration = (minutes: number) => {
    irrigationForm.valves.forEach((v: any) => {
        v.duration_minutes = minutes;
    });
};

const addOrderItem = () => {
    orderForm.items.push({
        product_id: props.products?.[0]?.id || null,
        packaging_definition_id: null,
        quantity: 100,
        unit_price: 60
    });
};

const removeOrderItem = (idx: number) => {
    if (orderForm.items.length > 1) {
        orderForm.items.splice(idx, 1);
    }
};

const calculateOrderTotal = computed(() => {
    return orderForm.items.reduce((acc, item) => {
        const q = parseFloat(String(item.quantity)) || 0;
        const p = parseFloat(String(item.unit_price)) || 0;
        return acc + (q * p);
    }, 0);
});

const addOrderEditItem = () => {
    orderEditForm.items.push({
        id: null,
        product_id: props.products?.[0]?.id || null,
        packaging_definition_id: null,
        quantity: 100,
        unit_price: 60
    });
};

const removeOrderEditItem = (idx: number) => {
    if (orderEditForm.items.length > 1) {
        orderEditForm.items.splice(idx, 1);
    }
};

const calculateOrderEditTotal = computed(() => {
    return orderEditForm.items.reduce((acc, item) => {
        const q = parseFloat(String(item.quantity)) || 0;
        const p = parseFloat(String(item.unit_price)) || 0;
        return acc + (q * p);
    }, 0);
});

const getSelectedCrewLeaderRegisteredCount = (crewLeaderId: number | null) => {
    if (!crewLeaderId || !props.crewLeaders) return 0;
    const cl = props.crewLeaders.find((c: any) => c.id === crewLeaderId);
    return cl?.workers?.length || 0;
};

const onDailyWorkSheetCrewLeaderChange = () => {
    const clId = dailyWorkSheetForm.crew_leaders[0]?.crew_leader_id;
    if (clId) {
        dailyWorkSheetForm.crew_leaders[0].absent_worker_count = 0;
        dailyWorkSheetForm.crew_leaders[0].extra_worker_count = 0;
    }
};

watch(() => props.show, (newVal) => {
    if (!newVal) return;
    const item = props.item;
    const type = props.type;

    if (type === 'work_plan') {
        if (item) {
            workPlanForm.id = item.id;
            workPlanForm.title = item.title || '';
            workPlanForm.production_location_id = item.production_location_id;
            workPlanForm.job_type_id = item.job_type_id;
            workPlanForm.assigned_personnel_id = item.assigned_personnel_id;
            workPlanForm.plan_date = item.plan_date ? item.plan_date.split('T')[0] : '';
            workPlanForm.due_date = item.due_date ? item.due_date.split('T')[0] : '';
            workPlanForm.status = item.status || 'pending';
            workPlanForm.description = item.description || '';
            workPlanForm.completion_notes = item.completion_notes || '';
        } else {
            workPlanForm.reset();
            workPlanForm.id = null;
            workPlanForm.production_location_id = props.locations?.[0]?.id || null;
            workPlanForm.job_type_id = props.jobTypes?.[0]?.id || null;
            workPlanForm.assigned_personnel_id = props.personnels?.[0]?.id || null;
            workPlanForm.plan_date = new Date().toISOString().split('T')[0];
            workPlanForm.status = 'pending';
        }
    } else if (type === 'fert_run') {
        if (item) {
            fertRunForm.id = item.id;
            fertRunForm.fertilization_recipe_id = item.fertilization_recipe_id;
            fertRunForm.start_date = item.start_date ? item.start_date.split('T')[0] : '';
            fertRunForm.end_condition = item.end_condition || '';
            fertRunForm.is_active = item.is_active !== undefined ? Boolean(item.is_active) : true;
        } else {
            fertRunForm.reset();
            fertRunForm.id = null;
            fertRunForm.fertilization_recipe_id = props.fertRecipes?.[0]?.id || null;
            fertRunForm.start_date = new Date().toISOString().split('T')[0];
            fertRunForm.end_condition = '';
            fertRunForm.is_active = true;
        }
    } else if (type === 'sulama') {
        if (item) {
            irrigationForm.id = item.id;
            irrigationForm.schedule_date = item.schedule_date ? item.schedule_date.split('T')[0] : '';
            irrigationForm.run_number = item.run_number || 1;
            irrigationForm.start_time = item.start_time || '09:00';
            const firstValve = item.valves?.[0];
            const locId = item.production_location_id || firstValve?.valve?.production_location_id || props.locations?.[0]?.id || null;
            irrigationForm.production_location_id = locId;
            irrigationForm.is_fertilized = Boolean(item.is_fertilized);
            irrigationForm.fertilization_recipe_id = item.fertilization_recipe_id || props.fertRecipes?.[0]?.id || null;
            irrigationForm.tank_stage_note = item.tank_stage_note || '';
            irrigationForm.notes = item.notes || '';
            irrigationForm.valves = item.valves?.map((v: any) => ({
                location_valve_id: v.location_valve_id || v.valve_id,
                valve_id: v.valve_id || v.location_valve_id,
                valve_number: v.valve?.valve_number || v.valve_number || 'V',
                name: v.valve?.name || v.name || 'Vana',
                duration_minutes: v.duration_minutes || 10,
            })) || [];
        } else {
            irrigationForm.reset();
            irrigationForm.id = null;
            irrigationForm.schedule_date = new Date().toISOString().split('T')[0];
            irrigationForm.run_number = 1;
            irrigationForm.start_time = '09:00';
            irrigationForm.production_location_id = props.locations?.[0]?.id || null;
            irrigationForm.is_fertilized = false;
            irrigationForm.fertilization_recipe_id = props.fertRecipes?.[0]?.id || null;
            loadIrrigationValvesForLocation();
        }
    } else if (type === 'spray_app') {
        if (item) {
            sprayAppForm.id = item.id;
            sprayAppForm.application_date = item.application_date ? item.application_date.split('T')[0] : '';
            sprayAppForm.spraying_recipe_id = item.spraying_recipe_id;
            sprayAppForm.purpose = item.purpose || '';
            sprayAppForm.applied_by_id = item.applied_by_id;
            sprayAppForm.covered_area_description = item.covered_area_description || '';
            sprayAppForm.is_tank_finished = Boolean(item.is_tank_finished);
            sprayAppForm.batch_code = item.batch_code || '';
            sprayAppForm.notes = item.notes || '';
        } else {
            sprayAppForm.reset();
            sprayAppForm.id = null;
            sprayAppForm.application_date = new Date().toISOString().split('T')[0];
            sprayAppForm.spraying_recipe_id = props.sprayRecipes?.[0]?.id || null;
            sprayAppForm.applied_by_id = props.personnels?.[0]?.id || null;
        }
    } else if (type === 'water_analysis') {
        if (item) {
            waterAnalysisForm.id = item.id;
            waterAnalysisForm.water_source_id = item.water_source_id;
            waterAnalysisForm.analysis_date = item.analysis_date ? item.analysis_date.split('T')[0] : '';
            waterAnalysisForm.ph_level = item.ph_level;
            waterAnalysisForm.ec_level = item.ec_level;
            waterAnalysisForm.notes = item.notes || '';
            waterAnalysisForm.chemical_details = {
                lab_name: item.chemical_details?.lab_name || '',
                report_no: item.chemical_details?.report_no || '',
                calcium: item.chemical_details?.calcium || '',
                magnesium: item.chemical_details?.magnesium || '',
                sodium: item.chemical_details?.sodium || '',
                potassium: item.chemical_details?.potassium || '',
                bicarbonate: item.chemical_details?.bicarbonate || '',
                sulfate: item.chemical_details?.sulfate || '',
                chloride: item.chemical_details?.chloride || '',
                nitrate: item.chemical_details?.nitrate || '',
                hardness: item.chemical_details?.hardness || '',
                sar: item.chemical_details?.sar || '',
                tds: item.chemical_details?.tds || '',
                suitability: item.chemical_details?.suitability || 'Sulamaya Uygun (A Sınıfı)',
            };
        } else {
            waterAnalysisForm.reset();
            waterAnalysisForm.id = null;
            waterAnalysisForm.water_source_id = props.waterSources?.[0]?.id || null;
            waterAnalysisForm.analysis_date = new Date().toISOString().split('T')[0];
        }
    } else if (type === 'raw_water') {
        if (item) {
            rawWaterForm.id = item.id;
            rawWaterForm.water_source_id = item.water_source_id;
            rawWaterForm.control_date = item.control_date ? item.control_date.split('T')[0] : '';
            rawWaterForm.ec_val = item.ec_val;
            rawWaterForm.ph_val = item.ph_val;
            rawWaterForm.pump_status = item.pump_status || 'open';
            rawWaterForm.pump_fault_note = item.pump_fault_note || '';
            rawWaterForm.active_start_date = item.active_start_date ? item.active_start_date.split('T')[0] : '';
            rawWaterForm.passive_start_date = item.passive_start_date ? item.passive_start_date.split('T')[0] : '';
            rawWaterForm.source_switch_reason = item.source_switch_reason || '';
            rawWaterForm.is_filter_cleaned = Boolean(item.is_filter_cleaned);
            rawWaterForm.water_tank_level = item.water_tank_level || 'full';
            rawWaterForm.water_tank_note = item.water_tank_note || '';
            rawWaterForm.chlorine_tank_level = item.chlorine_tank_level || 'full';
            rawWaterForm.dosing_pump_mode = item.dosing_pump_mode || 'auto';
            rawWaterForm.dosing_pump_manual_val = item.dosing_pump_manual_val || '';
            rawWaterForm.dosing_pump_fault_note = item.dosing_pump_fault_note || '';
        } else {
            rawWaterForm.reset();
            rawWaterForm.id = null;
            rawWaterForm.water_source_id = props.waterSources?.[0]?.id || null;
            rawWaterForm.control_date = new Date().toISOString().split('T')[0];
            rawWaterForm.active_start_date = new Date().toISOString().split('T')[0];
        }
    } else if (type === 'purification') {
        if (item) {
            purificationForm.id = item.id;
            purificationForm.water_source_id = item.water_source_id;
            purificationForm.control_date = item.control_date ? item.control_date.split('T')[0] : '';
            purificationForm.inlet_pressure_bar = item.inlet_pressure_bar;
            purificationForm.outlet_pressure_bar = item.outlet_pressure_bar;
            purificationForm.max_threshold_bar = item.max_threshold_bar || '';
        } else {
            purificationForm.reset();
            purificationForm.id = null;
            purificationForm.water_source_id = props.waterSources?.[0]?.id || null;
            purificationForm.control_date = new Date().toISOString().split('T')[0];
        }
    } else if (type === 'order') {
        orderForm.reset();
        orderForm.id = null;
        orderForm.trading_party_id = props.tradingParties?.[0]?.id || null;
        orderForm.order_date = new Date().toISOString().split('T')[0];
        orderForm.items = [
            { product_id: props.products?.[0]?.id || null, packaging_definition_id: null, quantity: 100, unit_price: 60 }
        ];
    } else if (type === 'order_edit' && item) {
        orderEditForm.id = item.id;
        orderEditForm.trading_party_id = item.trading_party_id;
        orderEditForm.order_date = item.order_date ? item.order_date.split('T')[0] : '';
        orderEditForm.requested_delivery_date = item.requested_delivery_date ? item.requested_delivery_date.split('T')[0] : '';
        orderEditForm.contact_person = item.contact_person || '';
        orderEditForm.status = item.status || 'pending';
        orderEditForm.notes = item.notes || '';
        orderEditForm.items = item.items?.map((it: any) => ({
            id: it.id,
            product_id: it.product_id,
            packaging_definition_id: it.packaging_definition_id,
            quantity: it.quantity,
            unit_price: it.unit_price,
        })) || [];
    } else if (type === 'shipment') {
        if (item) {
            shipmentForm.id = item.id;
            shipmentForm.trading_party_id = item.trading_party_id;
            shipmentForm.customer_order_id = item.customer_order_id;
            shipmentForm.product_id = item.product_id;
            shipmentForm.packaging_definition_id = item.packaging_definition_id;
            shipmentForm.shipment_date = item.shipment_date ? item.shipment_date.split('T')[0] : '';
            shipmentForm.quantity = item.quantity;
            shipmentForm.unit_price = item.unit_price;
            shipmentForm.dia_waybill_code = item.dia_waybill_code || '';
            shipmentForm.delivery_type_id = item.delivery_type_id;
            shipmentForm.status = item.status || 'on_the_way';
            shipmentForm.vehicle_plate = item.vehicle_plate || '';
            shipmentForm.driver_name = item.driver_name || '';
            shipmentForm.driver_phone = item.driver_phone || '';
            shipmentForm.notes = item.notes || '';
        } else {
            shipmentForm.reset();
            shipmentForm.id = null;
            shipmentForm.trading_party_id = props.tradingParties?.[0]?.id || null;
            shipmentForm.product_id = props.products?.[0]?.id || null;
            shipmentForm.shipment_date = new Date().toISOString().split('T')[0];
        }
    } else if (type === 'daily_sheet') {
        dailyWorkSheetForm.reset();
        dailyWorkSheetForm.id = null;
        dailyWorkSheetForm.work_date = new Date().toISOString().split('T')[0];
        dailyWorkSheetForm.company_id = props.companies?.[0]?.id || null;
        dailyWorkSheetForm.production_location_id = props.locations?.[0]?.id || null;
        dailyWorkSheetForm.crew_leaders = [
            { crew_leader_id: props.crewLeaders?.[0]?.id || null, absent_worker_count: 0, extra_worker_count: 0, car_count: 1 }
        ];
        dailyWorkSheetForm.harvest_items = [
            { product_id: props.products?.[0]?.id || null, packaging_definition_id: null, package_count: 0, quantity: 0 }
        ];
    } else if (type === 'market_price') {
        marketPriceForm.reset();
        marketPriceForm.id = null;
        marketPriceForm.product_id = props.products?.[0]?.id || null;
        marketPriceForm.price_date = new Date().toISOString().split('T')[0];
    } else if (type === 'crew_leader') {
        crewLeaderForm.reset();
    } else if (type === 'worker') {
        workerForm.reset();
        workerForm.crew_leader_id = item?.crew_leader_id || props.crewLeaders?.[0]?.id || null;
    }
});

const submitWorkPlan = () => {
    workPlanForm.post(route('agriculture.work-plans.store'), {
        onSuccess: () => { emit('saved'); emit('close'); }
    });
};

const submitFertRun = () => {
    fertRunForm.post(route('agriculture.fertilization-runs.store'), {
        onSuccess: () => { emit('saved'); emit('close'); }
    });
};

const submitIrrigation = () => {
    irrigationForm.post(route('agriculture.irrigation-schedules.store'), {
        onSuccess: () => { emit('saved'); emit('close'); }
    });
};

const submitSprayApp = () => {
    sprayAppForm.post(route('agriculture.spraying-applications.store'), {
        onSuccess: () => { emit('saved'); emit('close'); }
    });
};

const submitWaterAnalysis = () => {
    waterAnalysisForm.post(route('agriculture.water-analyses.store'), {
        onSuccess: () => { emit('saved'); emit('close'); }
    });
};

const submitRawWater = () => {
    rawWaterForm.post(route('agriculture.raw-water-controls.store'), {
        forceFormData: true,
        onSuccess: () => { emit('saved'); emit('close'); }
    });
};

const submitPurification = () => {
    purificationForm.post(route('agriculture.purification-controls.store'), {
        onSuccess: () => { emit('saved'); emit('close'); }
    });
};

const submitOrder = () => {
    orderForm.post(route('agriculture.customer-orders.store'), {
        onSuccess: () => { emit('saved'); emit('close'); }
    });
};

const submitEditOrder = () => {
    if (orderEditForm.id) {
        orderEditForm.put(route('agriculture.customer-orders.update', orderEditForm.id), {
            onSuccess: () => { emit('saved'); emit('close'); }
        });
    }
};

const submitShipment = () => {
    shipmentForm.post(route('agriculture.shipment-deliveries.store'), {
        onSuccess: () => { emit('saved'); emit('close'); }
    });
};

const submitDailyWorkSheet = () => {
    dailyWorkSheetForm.post(route('agriculture.daily-work-sheets.store'), {
        onSuccess: () => { emit('saved'); emit('close'); }
    });
};

const submitMarketPrice = () => {
    marketPriceForm.post(route('agriculture.market-prices.store'), {
        onSuccess: () => { emit('saved'); emit('close'); }
    });
};

const submitCrewLeader = () => {
    crewLeaderForm.post(route('definitions.crew-leaders.store'), {
        onSuccess: () => { emit('saved'); emit('close'); }
    });
};

const submitWorker = () => {
    workerForm.post(route('definitions.workers.store'), {
        onSuccess: () => { emit('saved'); emit('close'); }
    });
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl w-full max-w-xl p-7 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-5 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">
                        {{ modalTitles[type] || 'Kayıt Formu' }}
                    </h3>
                    <p class="text-xs text-slate-500">Lütfen operasyonel alanları eksiksiz doldurunuz.</p>
                </div>
                <button type="button" @click="emit('close')" class="text-slate-400 hover:text-slate-700 font-bold text-2xl leading-none">&times;</button>
            </div>

            <form v-if="type === 'work_plan'" @submit.prevent="submitWorkPlan" class="space-y-4 text-xs">
                <div>
                    <label class="block font-bold mb-1">İş Başlığı / Görev Adı *</label>
                    <input v-model="workPlanForm.title" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 text-xs font-semibold" required placeholder="Örn: Silifke Tünel A Damlama Boruları ve Debi Testi" />
                </div>

                <div class="p-3 bg-slate-50 dark:bg-slate-950 rounded-xl border border-slate-200 dark:border-slate-800 space-y-2">
                    <label class="block font-bold text-slate-800 dark:text-slate-200 text-xs">Görevin Güncel Durumu *</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <button type="button" @click="workPlanForm.status = 'pending'" :class="workPlanForm.status === 'pending' ? 'bg-amber-600 text-white font-black ring-2 ring-amber-400' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 hover:bg-slate-100'" class="py-2 px-3 rounded-xl text-xs font-bold transition text-center cursor-pointer">
                            Beklemede
                        </button>
                        <button type="button" @click="workPlanForm.status = 'in_progress'" :class="workPlanForm.status === 'in_progress' ? 'bg-indigo-600 text-white font-black ring-2 ring-indigo-400' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 hover:bg-slate-100'" class="py-2 px-3 rounded-xl text-xs font-bold transition text-center cursor-pointer">
                            Devam Ediyor
                        </button>
                        <button type="button" @click="workPlanForm.status = 'completed'" :class="workPlanForm.status === 'completed' ? 'bg-emerald-600 text-white font-black ring-2 ring-emerald-400 shadow-sm' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 hover:bg-slate-100'" class="py-2 px-3 rounded-xl text-xs font-bold transition text-center cursor-pointer">
                            ✓ Tamamlandı
                        </button>
                        <button type="button" @click="workPlanForm.status = 'cancelled'" :class="workPlanForm.status === 'cancelled' ? 'bg-rose-600 text-white font-black ring-2 ring-rose-400' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 hover:bg-slate-100'" class="py-2 px-3 rounded-xl text-xs font-bold transition text-center cursor-pointer">
                            İptal Edildi
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold mb-1">Tesis / Saha Bölgesi</label>
                        <select v-model="workPlanForm.production_location_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 text-xs font-medium">
                            <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Yapılan İş Türü</label>
                        <select v-model="workPlanForm.job_type_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 text-xs font-medium">
                            <option :value="null">Genel Saha İşi</option>
                            <option v-for="jt in jobTypes" :key="jt.id" :value="jt.id">{{ jt.name }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold mb-1">Görevli / Sorumlu Personel</label>
                    <select v-model="workPlanForm.assigned_personnel_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 text-xs font-medium">
                        <option :value="null">Genel Atama / Tüm Ekip</option>
                        <option v-for="p in personnels" :key="p.id" :value="p.id">{{ p.first_name }} {{ p.last_name }} ({{ p.department || 'Personel' }})</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold mb-1">Planlama Başlangıç Tarihi *</label>
                        <input v-model="workPlanForm.plan_date" type="date" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 text-xs" required />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Hedef Bitiş Tarihi</label>
                        <input v-model="workPlanForm.due_date" type="date" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 text-xs" />
                    </div>
                </div>

                <div>
                    <label class="block font-bold mb-1">Müdür Talimatı / Görev Açıklaması</label>
                    <textarea v-model="workPlanForm.description" rows="2" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 text-xs" placeholder="İşletme müdürü tarafından verilen çalışma talimatı ve detaylar..."></textarea>
                </div>

                <div class="p-3 bg-emerald-50/70 dark:bg-emerald-950/40 rounded-xl border border-emerald-200 dark:border-emerald-900/60 space-y-1.5">
                    <label class="block font-bold text-emerald-900 dark:text-emerald-300 text-xs">Süreç / Tamamlama Sonuç Notu</label>
                    <textarea v-model="workPlanForm.completion_notes" rows="2" class="w-full border rounded-xl p-2.5 bg-white dark:bg-slate-950 text-xs" placeholder="İş tamamlandığında veya süreç esnasında sahada elde edilen sonuçlar ve açıklamalar..."></textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t">
                    <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl font-bold">İptal</button>
                    <button type="submit" :disabled="workPlanForm.processing" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl shadow-xs transition">
                        {{ workPlanForm.id ? 'Güncellemeyi Kaydet' : 'İş Planı Oluştur' }}
                    </button>
                </div>
            </form>

            <form v-if="type === 'fert_run'" @submit.prevent="submitFertRun" class="space-y-4 text-xs">
                <div>
                    <label class="block font-bold mb-1">Daha Önce Tanımlanmış Gübre Reçetesi</label>
                    <select v-model="fertRunForm.fertilization_recipe_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required>
                        <option v-for="fr in fertRecipes" :key="fr.id" :value="fr.id">{{ fr.name }} (Hedef pH: {{ fr.water_ph }}, EC: {{ fr.water_ec }})</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold mb-1">Reçete Uygulama Durumu</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="fertRunForm.is_active = true"
                            :class="fertRunForm.is_active ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
                            class="py-2 rounded-xl text-xs transition">
                            Aktif Uygulanan Reçete
                        </button>
                        <button type="button" @click="fertRunForm.is_active = false"
                            :class="!fertRunForm.is_active ? 'bg-slate-700 text-white font-bold' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
                            class="py-2 rounded-xl text-xs transition">
                            Pasif / Tamamlandı
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block font-bold mb-1">Reçete Başlangıç Tarihi</label>
                    <input v-model="fertRunForm.start_date" type="date" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                </div>
                <div>
                    <label class="block font-bold mb-1 text-rose-600 dark:text-rose-400">Reçete Bitiş Şartı (Spesifik bir tarih olamaz)</label>
                    <input v-model="fertRunForm.end_condition" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-bold" placeholder="Örn: Çiçeklenme başlayana kadar / İlk meyve tutumuna kadar" required />
                </div>
                <div class="flex justify-end space-x-2 pt-3 border-t">
                    <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl">İptal</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-xs transition">
                        {{ fertRunForm.id ? 'Değişiklikleri Kaydet' : 'Reçeteyi Başlat' }}
                    </button>
                </div>
            </form>

            <form v-if="type === 'sulama'" @submit.prevent="submitIrrigation" class="space-y-4 text-xs">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block font-bold mb-1">Sulama Tarihi</label>
                        <input v-model="irrigationForm.schedule_date" type="date" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Sulama Sıra No</label>
                        <input v-model="irrigationForm.run_number" type="number" min="1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Başlangıç Saati</label>
                        <input v-model="irrigationForm.start_time" type="time" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Üretim Yeri / Sera Seçimi</label>
                        <select v-model="irrigationForm.production_location_id" @change="loadIrrigationValvesForLocation" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-bold" required>
                            <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                        </select>
                    </div>
                </div>

                <div class="p-3.5 bg-slate-50 dark:bg-slate-950 rounded-xl border border-slate-200 dark:border-slate-800 space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input v-model="irrigationForm.is_fertilized" type="checkbox" class="rounded text-rose-600" />
                            <span class="font-black text-rose-600 dark:text-rose-400">Gübre Solüsyonu Dahil mi?</span>
                        </label>
                    </div>

                    <div v-if="irrigationForm.is_fertilized" class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold text-[11px] text-slate-500 mb-1">Tank Vana Kademe Seviyesi</label>
                            <input v-model="irrigationForm.tank_stage_note" type="text" placeholder="Örn: A Tankı 1. Kademe Solüsyon (%100 Dolu)" class="w-full border rounded-xl p-2 bg-white dark:bg-slate-900" />
                        </div>
                        <div>
                            <label class="block font-bold text-[11px] text-slate-500 mb-1">Saha Notları / Açıklama</label>
                            <input v-model="irrigationForm.notes" type="text" placeholder="Günün sulama notu..." class="w-full border rounded-xl p-2 bg-white dark:bg-slate-900" />
                        </div>
                    </div>
                </div>

                <div class="border border-slate-200 dark:border-slate-800 rounded-2xl p-4 bg-slate-50/50 dark:bg-slate-950/40 space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-200 dark:border-slate-800">
                        <div>
                            <h4 class="font-black text-xs text-slate-800 dark:text-slate-100 uppercase tracking-wider">Sera & Vana Çalışma Süre Matrisi</h4>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="applyEqualDuration(5)" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold rounded-lg border border-indigo-200 dark:border-indigo-800 text-[11px] transition cursor-pointer">5 dk</button>
                            <button type="button" @click="applyEqualDuration(10)" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold rounded-lg border border-indigo-200 dark:border-indigo-800 text-[11px] transition cursor-pointer">10 dk</button>
                            <button type="button" @click="applyEqualDuration(15)" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold rounded-lg border border-indigo-200 dark:border-indigo-800 text-[11px] transition cursor-pointer">15 dk</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        <div v-for="(v, idx) in irrigationForm.valves" :key="idx" class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 flex items-center justify-between gap-2 shadow-2xs">
                            <div>
                                <div class="font-extrabold text-xs text-slate-800 dark:text-slate-100">{{ v.name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono mt-0.5">Vana No: {{ v.valve_number }}</div>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                <input v-model.number="v.duration_minutes" type="number" min="1" max="180" class="w-16 border rounded-lg p-1.5 text-center font-bold text-xs bg-slate-50 dark:bg-slate-950" required />
                                <span class="text-[11px] font-bold text-slate-400">dk</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="emit('close')" class="px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl font-bold">İptal</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white font-extrabold rounded-xl shadow-xs transition">Sulamayı Kaydet</button>
                </div>
            </form>

            <form v-if="type === 'spray_app'" @submit.prevent="submitSprayApp" class="space-y-4 text-xs">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <div>
                        <label class="block font-bold mb-1">Uygulama Tarihi</label>
                        <input v-model="sprayAppForm.application_date" type="date" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">İlaç Reçetesi</label>
                        <select v-model="sprayAppForm.spraying_recipe_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required>
                            <option v-for="sr in sprayRecipes" :key="sr.id" :value="sr.id">{{ sr.name }}</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <div>
                        <label class="block font-bold mb-1">Kullanım Amacı</label>
                        <input v-model="sprayAppForm.purpose" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Parti / Şarj No</label>
                        <input v-model="sprayAppForm.batch_code" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-mono" placeholder="Örn: BATCH-2026-01" />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <div>
                        <label class="block font-bold mb-1">Uygulayan Personel</label>
                        <select v-model="sprayAppForm.applied_by_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                            <option :value="null">Seçilmedi</option>
                            <option v-for="p in personnels" :key="p.id" :value="p.id">{{ p.first_name }} {{ p.last_name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Kaplanan Alan</label>
                        <input v-model="sprayAppForm.covered_area_description" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                </div>
                <div>
                    <label class="block font-bold mb-1">Uygulama Notu</label>
                    <textarea v-model="sprayAppForm.notes" rows="2" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="Hava şartları, rüzgar durumu veya notlar..."></textarea>
                </div>
                <div class="p-2.5 bg-slate-50 dark:bg-slate-950 rounded-xl border">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input v-model="sprayAppForm.is_tank_finished" type="checkbox" class="rounded text-rose-600" />
                        <span class="font-bold">Tank Bitti</span>
                    </label>
                </div>
                <div class="flex justify-end space-x-2 pt-3 border-t">
                    <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl">İptal</button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl">İlaçlamayı Kaydet</button>
                </div>
            </form>

            <form v-if="type === 'water_analysis'" @submit.prevent="submitWaterAnalysis" class="space-y-4 text-xs">
                <div class="p-3.5 bg-slate-50 dark:bg-slate-950/60 rounded-xl border border-slate-200 dark:border-slate-800 space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div class="md:col-span-2">
                            <label class="block font-bold mb-1">Su Kaynağı *</label>
                            <select v-model="waterAnalysisForm.water_source_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-bold" required>
                                <option v-for="ws in waterSources" :key="ws.id" :value="ws.id">{{ ws.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Numune Tarihi *</label>
                            <input v-model="waterAnalysisForm.analysis_date" type="date" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-mono font-bold" required />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Rapor No</label>
                            <input v-model="waterAnalysisForm.chemical_details.report_no" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-mono" placeholder="Örn: LAB-2026-084" />
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Laboratuvar Adı</label>
                        <input v-model="waterAnalysisForm.chemical_details.lab_name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                    </div>
                </div>

                <div class="p-3.5 bg-slate-50 dark:bg-slate-950/60 rounded-xl border border-slate-200 dark:border-slate-800 space-y-3">
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                        <div>
                            <label class="block font-bold mb-1 text-rose-600 dark:text-rose-400">pH Seviyesi *</label>
                            <input v-model="waterAnalysisForm.ph_level" type="number" step="0.01" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-bold font-mono text-rose-600 dark:text-rose-400" required placeholder="6.5" />
                        </div>
                        <div>
                            <label class="block font-bold mb-1 text-cyan-600 dark:text-cyan-400">EC İletkenlik *</label>
                            <input v-model="waterAnalysisForm.ec_level" type="number" step="0.01" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-bold font-mono text-cyan-600 dark:text-cyan-400" required placeholder="1.40" />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">TDS (ppm)</label>
                            <input v-model="waterAnalysisForm.chemical_details.tds" type="number" step="1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-mono" />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Sertlik (°F)</label>
                            <input v-model="waterAnalysisForm.chemical_details.hardness" type="number" step="0.1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-mono" />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">SAR</label>
                            <input v-model="waterAnalysisForm.chemical_details.sar" type="number" step="0.01" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-mono" />
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold mb-1">Uygunluk Sınıfı</label>
                        <select v-model="waterAnalysisForm.chemical_details.suitability" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-bold text-emerald-600 dark:text-emerald-400">
                            <option value="Sulamaya Uygun (A Sınıfı)">✓ Sulamaya Uygun (A Sınıfı)</option>
                            <option value="Şartlı Uygun (B Sınıfı - Asit Desteği Gerekli)">⚠️ Şartlı Uygun (B Sınıfı - Asit Gerekli)</option>
                            <option value="Yüksek Klor / Sodyum Riski">⚠️ Yüksek Klor / Sodyum Riski</option>
                            <option value="Yüksek EC / Tuzluluk (Ters Osmoz Gerekli)">✕ Yüksek Tuzluluk (Arıtma Gerekli)</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block font-bold mb-1">Laboratuvar Açıklaması</label>
                        <input v-model="waterAnalysisForm.notes" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                    </div>
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t">
                    <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl font-bold">İptal</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white font-bold rounded-xl transition">
                        {{ waterAnalysisForm.id ? 'Değişiklikleri Kaydet' : 'Analiz Raporunu Kaydet' }}
                    </button>
                </div>
            </form>

            <form v-if="type === 'raw_water'" @submit.prevent="submitRawWater" class="space-y-4 text-xs">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold mb-1">Kontrol Tarihi</label>
                        <input v-model="rawWaterForm.control_date" type="date" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-bold" required />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Su Kaynağı</label>
                        <select v-model="rawWaterForm.water_source_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-bold" required>
                            <option v-for="ws in waterSources" :key="ws.id" :value="ws.id">{{ ws.name }}</option>
                        </select>
                    </div>
                </div>

                <div class="p-3 bg-slate-50 dark:bg-slate-950 rounded-xl border border-slate-200 dark:border-slate-800 space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold mb-1">Ölçülen EC Değeri (mS/cm)</label>
                            <input v-model="rawWaterForm.ec_val" type="number" step="0.01" min="0" max="20" class="w-full border rounded-xl p-2.5 dark:bg-slate-900 font-extrabold text-indigo-600 dark:text-indigo-400" placeholder="1.80" required />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Ölçülen pH Değeri</label>
                            <input v-model="rawWaterForm.ph_val" type="number" step="0.01" min="0" max="14" class="w-full border rounded-xl p-2.5 dark:bg-slate-900 font-extrabold text-rose-600 dark:text-rose-400" placeholder="6.60" required />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t">
                    <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl font-bold">İptal</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white font-extrabold rounded-xl shadow-xs transition">
                        {{ rawWaterForm.id ? 'Değişiklikleri Kaydet' : 'Kontrolü Kaydet' }}
                    </button>
                </div>
            </form>

            <form v-if="type === 'purification'" @submit.prevent="submitPurification" class="space-y-4 text-xs">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <div>
                        <label class="block font-bold mb-1">Kontrol Tarihi</label>
                        <input v-model="purificationForm.control_date" type="date" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Su Kaynağı</label>
                        <select v-model="purificationForm.water_source_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required>
                            <option v-for="ws in waterSources" :key="ws.id" :value="ws.id">{{ ws.name }}</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block font-bold mb-1">Giriş Basıncı (bar)</label>
                        <input v-model="purificationForm.inlet_pressure_bar" type="number" step="0.1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Çıkış Basıncı (bar)</label>
                        <input v-model="purificationForm.outlet_pressure_bar" type="number" step="0.1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Eşik Farkı (bar)</label>
                        <input v-model="purificationForm.max_threshold_bar" type="number" step="0.1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="1.50" />
                    </div>
                </div>
                <div class="flex justify-end space-x-2 pt-3 border-t">
                    <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl">İptal</button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl">Basınç Kaydet</button>
                </div>
            </form>

            <form v-if="type === 'order'" @submit.prevent="submitOrder" class="space-y-4 text-xs">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                    <div>
                        <label class="block font-bold mb-1">Alıcı Müşteri</label>
                        <select v-model="orderForm.trading_party_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required>
                            <option v-for="tp in tradingParties" :key="tp.id" :value="tp.id">{{ tp.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Sipariş Tarihi</label>
                        <input v-model="orderForm.order_date" type="date" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Teslim Tarihi</label>
                        <input v-model="orderForm.requested_delivery_date" type="date" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                    </div>
                </div>

                <div class="p-3 bg-slate-50 dark:bg-slate-950 rounded-xl border border-slate-200 dark:border-slate-800 space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-slate-800 dark:text-slate-200 uppercase text-[11px]">Sipariş Kalemleri & Ürünler</span>
                        <button type="button" @click="addOrderItem" class="text-rose-600 hover:text-rose-500 font-bold text-xs flex items-center gap-1">
                            + Yeni Ürün Ekle
                        </button>
                    </div>

                    <div v-for="(item, idx) in orderForm.items" :key="idx" class="p-3 bg-white dark:bg-slate-900 rounded-xl border space-y-2">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-2 items-center">
                            <div>
                                <label class="block text-[11px] text-slate-500 mb-1">Ürün</label>
                                <select v-model="item.product_id" class="w-full border rounded-xl p-2 dark:bg-slate-950 text-xs" required>
                                    <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] text-slate-500 mb-1">Paketleme</label>
                                <select v-model="item.packaging_definition_id" class="w-full border rounded-xl p-2 dark:bg-slate-950 text-xs">
                                    <option :value="null">Seçiniz</option>
                                    <option v-for="pkg in packagings" :key="pkg.id" :value="pkg.id">{{ pkg.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] text-slate-500 mb-1">Miktar</label>
                                <input v-model="item.quantity" type="number" min="0.1" step="0.1" class="w-full border rounded-xl p-2 dark:bg-slate-950 text-xs" required />
                            </div>
                            <div>
                                <label class="block text-[11px] text-slate-500 mb-1">Birim Fiyat (₺)</label>
                                <div class="flex items-center gap-1">
                                    <input v-model="item.unit_price" type="number" min="0" step="0.1" class="w-full border rounded-xl p-2 dark:bg-slate-950 text-xs" required />
                                    <button v-if="orderForm.items.length > 1" type="button" @click="removeOrderItem(idx)" class="text-rose-500 hover:text-rose-700 px-2 font-bold text-sm">✕</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-2 border-t font-black text-sm">
                        <span>HESAPLANAN GENEL TOPLAM:</span>
                        <span class="text-emerald-600 text-base">₺{{ calculateOrderTotal.toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }}</span>
                    </div>
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t">
                    <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl font-bold">İptal</button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white font-bold rounded-xl transition">
                        Sipariş Oluştur (₺{{ calculateOrderTotal.toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }})
                    </button>
                </div>
            </form>

            <form v-if="type === 'order_edit'" @submit.prevent="submitEditOrder" class="space-y-4 text-xs">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                    <div>
                        <label class="block font-bold mb-1">Alıcı Müşteri</label>
                        <select v-model="orderEditForm.trading_party_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required>
                            <option v-for="tp in tradingParties" :key="tp.id" :value="tp.id">{{ tp.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Sipariş Tarihi</label>
                        <input v-model="orderEditForm.order_date" type="date" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Teslim Tarihi</label>
                        <input v-model="orderEditForm.requested_delivery_date" type="date" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                    </div>
                </div>

                <div class="p-3 bg-slate-50 dark:bg-slate-950 rounded-xl border border-slate-200 dark:border-slate-800 space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-slate-800 dark:text-slate-200 uppercase text-[11px]">Sipariş Kalemleri (Düzenle)</span>
                        <button type="button" @click="addOrderEditItem" class="text-rose-600 hover:text-rose-500 font-bold text-xs flex items-center gap-1">+ Kalem Ekle</button>
                    </div>

                    <div v-for="(item, idx) in orderEditForm.items" :key="idx" class="p-3 bg-white dark:bg-slate-900 rounded-xl border space-y-2">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-2 items-center">
                            <div>
                                <label class="block text-[11px] text-slate-500 mb-1">Ürün</label>
                                <select v-model="item.product_id" class="w-full border rounded-xl p-2 dark:bg-slate-950 text-xs" required>
                                    <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] text-slate-500 mb-1">Paketleme</label>
                                <select v-model="item.packaging_definition_id" class="w-full border rounded-xl p-2 dark:bg-slate-950 text-xs">
                                    <option :value="null">Seçiniz</option>
                                    <option v-for="pkg in packagings" :key="pkg.id" :value="pkg.id">{{ pkg.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] text-slate-500 mb-1">Miktar</label>
                                <input v-model="item.quantity" type="number" min="0.1" step="0.1" class="w-full border rounded-xl p-2 dark:bg-slate-950 text-xs" required />
                            </div>
                            <div>
                                <label class="block text-[11px] text-slate-500 mb-1">Birim Fiyat (₺)</label>
                                <div class="flex items-center gap-1">
                                    <input v-model="item.unit_price" type="number" min="0" step="0.1" class="w-full border rounded-xl p-2 dark:bg-slate-950 text-xs" required />
                                    <button v-if="orderEditForm.items.length > 1" type="button" @click="removeOrderEditItem(idx)" class="text-rose-500 hover:text-rose-700 px-2 font-bold text-sm">✕</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-2 border-t font-black text-sm">
                        <span>YENİ TOPLAM:</span>
                        <span class="text-emerald-600 text-base">₺{{ calculateOrderEditTotal.toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }}</span>
                    </div>
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t">
                    <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl font-bold">İptal</button>
                    <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 dark:bg-indigo-600 text-white font-bold rounded-xl transition">
                        Kaydet (₺{{ calculateOrderEditTotal.toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }})
                    </button>
                </div>
            </form>

            <form v-if="type === 'shipment'" @submit.prevent="submitShipment" class="space-y-4 text-xs">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                    <div>
                        <label class="block font-bold mb-1">Alıcı Müşteri</label>
                        <select v-model="shipmentForm.trading_party_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required>
                            <option v-for="tp in tradingParties" :key="tp.id" :value="tp.id">{{ tp.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Bağlı Sipariş</label>
                        <select v-model="shipmentForm.customer_order_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                            <option :value="null">Bağımsız Sevkiyat</option>
                            <option v-for="o in customerOrders" :key="o.id" :value="o.id">
                                #ORD-{{ String(o.id).padStart(4, '0') }} - {{ o.trading_party?.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Sevkiyat Tarihi</label>
                        <input v-model="shipmentForm.shipment_date" type="date" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
                    <div>
                        <label class="block font-bold mb-1">Ürün</label>
                        <select v-model="shipmentForm.product_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required>
                            <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Paketleme</label>
                        <select v-model="shipmentForm.packaging_definition_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                            <option :value="null">Standart / Kasa</option>
                            <option v-for="pkg in packagings" :key="pkg.id" :value="pkg.id">{{ pkg.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Miktar (Kg)</label>
                        <input v-model="shipmentForm.quantity" type="number" min="0.1" step="0.1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Birim Fiyat (₺)</label>
                        <input v-model="shipmentForm.unit_price" type="number" min="0" step="0.1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                    <div>
                        <label class="block font-bold mb-1">İrsaliye No</label>
                        <input v-model="shipmentForm.dia_waybill_code" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-mono" placeholder="IRS-2026-001" />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Teslimat Şekli</label>
                        <select v-model="shipmentForm.delivery_type_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                            <option :value="null">Seçiniz</option>
                            <option v-for="dt in deliveryTypes" :key="dt.id" :value="dt.id">{{ dt.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Durum</label>
                        <select v-model="shipmentForm.status" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-bold">
                            <option value="on_the_way">Yolda</option>
                            <option value="delivered">Teslim Edildi</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t">
                    <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl font-bold">İptal</button>
                    <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 dark:bg-emerald-600 text-white font-bold rounded-xl transition">
                        Sevkiyatı & İrsaliyeyi Kaydet
                    </button>
                </div>
            </form>

            <form v-if="type === 'daily_sheet'" @submit.prevent="submitDailyWorkSheet" class="space-y-4 text-xs">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                    <div>
                        <label class="block font-bold mb-1">Tarih</label>
                        <input v-model="dailyWorkSheetForm.work_date" type="date" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Şirket</label>
                        <select v-model="dailyWorkSheetForm.company_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                            <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Tesis / Bölge</label>
                        <select v-model="dailyWorkSheetForm.production_location_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                            <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                        </select>
                    </div>
                </div>

                <div class="p-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-xl space-y-3">
                    <div v-for="(cl, index) in dailyWorkSheetForm.crew_leaders" :key="'cl-'+index" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2.5 items-end">
                        <div>
                            <label class="block font-bold mb-1">Çavuş</label>
                            <select v-model="cl.crew_leader_id" @change="onDailyWorkSheetCrewLeaderChange" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                                <option :value="null">Seçiniz</option>
                                <option v-for="leader in crewLeaders" :key="leader.id" :value="leader.id">
                                    {{ leader.first_name }} {{ leader.last_name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold mb-1 text-rose-600 dark:text-rose-400">Gelmeyen İşçi</label>
                            <input v-model="cl.absent_worker_count" type="number" min="0" placeholder="0" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                        </div>
                        <div>
                            <label class="block font-bold mb-1 text-emerald-600 dark:text-emerald-400">Ekstra İşçi</label>
                            <input v-model="cl.extra_worker_count" type="number" min="0" placeholder="0" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Araç Sayısı</label>
                            <input v-model="cl.car_count" type="number" min="0" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/50 rounded-xl space-y-3">
                    <div v-for="(hi, index) in dailyWorkSheetForm.harvest_items" :key="'hi-'+index" class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Ürün</label>
                            <select v-model="hi.product_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                                <option :value="null">Seçiniz</option>
                                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Ambalaj Sayısı</label>
                            <input v-model="hi.package_count" type="number" min="0" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Toplam KG</label>
                            <input v-model="hi.quantity" type="number" min="0" step="0.1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t">
                    <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl font-bold">İptal</button>
                    <button type="submit" :disabled="dailyWorkSheetForm.processing" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 dark:bg-indigo-600 text-white font-bold rounded-xl transition">Formu Kaydet</button>
                </div>
            </form>

            <form v-if="type === 'market_price'" @submit.prevent="submitMarketPrice" class="space-y-4 text-xs">
                <div>
                    <label class="block font-bold mb-1">Ürün Seçimi</label>
                    <select v-model="marketPriceForm.product_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required>
                        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block font-bold mb-1">Tarih</label>
                        <input v-model="marketPriceForm.price_date" type="date" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Birim Fiyat (TL/Kg)</label>
                        <input v-model="marketPriceForm.unit_price" type="number" step="0.1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                </div>
                <div>
                    <label class="block font-bold mb-1">Kaynak Adı</label>
                    <input v-model="marketPriceForm.source_name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                </div>
                <div class="flex justify-end space-x-2 pt-3 border-t">
                    <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl font-bold">İptal</button>
                    <button type="submit" :disabled="marketPriceForm.processing" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl transition">Piyasa Fiyatı Kaydet</button>
                </div>
            </form>

            <form v-if="type === 'crew_leader'" @submit.prevent="submitCrewLeader" class="space-y-4 text-xs">
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block font-bold mb-1">Adı</label>
                        <input v-model="crewLeaderForm.first_name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Soyadı</label>
                        <input v-model="crewLeaderForm.last_name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block font-bold mb-1">TC Kimlik</label>
                        <input v-model="crewLeaderForm.identity_number" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Telefon</label>
                        <input v-model="crewLeaderForm.phone" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block font-bold mb-1">Günlük Yevmiye (TL)</label>
                        <input v-model="crewLeaderForm.daily_wage" type="number" step="1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Çavuş Çarpanı</label>
                        <input v-model="crewLeaderForm.multiplier" type="number" step="0.1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                </div>
                <div class="flex justify-end space-x-2 pt-3 border-t">
                    <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl font-bold">İptal</button>
                    <button type="submit" :disabled="crewLeaderForm.processing" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl transition">Kaydet</button>
                </div>
            </form>

            <form v-if="type === 'worker'" @submit.prevent="submitWorker" class="space-y-4 text-xs">
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block font-bold mb-1">İşçi Adı</label>
                        <input v-model="workerForm.first_name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">İşçi Soyadı</label>
                        <input v-model="workerForm.last_name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                </div>
                <div>
                    <label class="block font-bold mb-1">TC Kimlik</label>
                    <input v-model="workerForm.identity_number" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                </div>
                <div>
                    <label class="block font-bold mb-1">Performans Notu (1-5)</label>
                    <input v-model="workerForm.performance_rating" type="number" min="1" max="5" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                </div>
                <div class="flex justify-end space-x-2 pt-3 border-t">
                    <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl font-bold">İptal</button>
                    <button type="submit" :disabled="workerForm.processing" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl transition">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</template>
