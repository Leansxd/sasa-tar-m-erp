<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, defineAsyncComponent } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, router, usePage, Head } from '@inertiajs/vue3';

const WorkPlanTab = defineAsyncComponent(() => import('@/Components/Agriculture/WorkPlanTab.vue'));
const FertilizationTab = defineAsyncComponent(() => import('@/Components/Agriculture/FertilizationTab.vue'));
const IrrigationTab = defineAsyncComponent(() => import('@/Components/Agriculture/IrrigationTab.vue'));
const SprayingTab = defineAsyncComponent(() => import('@/Components/Agriculture/SprayingTab.vue'));
const WaterAnalysisTab = defineAsyncComponent(() => import('@/Components/Agriculture/WaterAnalysisTab.vue'));
const RawWaterTab = defineAsyncComponent(() => import('@/Components/Agriculture/RawWaterTab.vue'));
const PurificationTab = defineAsyncComponent(() => import('@/Components/Agriculture/PurificationTab.vue'));
const CustomerOrdersTab = defineAsyncComponent(() => import('@/Components/Agriculture/CustomerOrdersTab.vue'));
const ShipmentsTab = defineAsyncComponent(() => import('@/Components/Agriculture/ShipmentsTab.vue'));
const DailyWorkSheetsTab = defineAsyncComponent(() => import('@/Components/Agriculture/DailyWorkSheetsTab.vue'));
const CrewLeadersTab = defineAsyncComponent(() => import('@/Components/Agriculture/CrewLeadersTab.vue'));
const MarketPricesTab = defineAsyncComponent(() => import('@/Components/Agriculture/MarketPricesTab.vue'));
const HarvestReportTab = defineAsyncComponent(() => import('@/Components/Agriculture/HarvestReportTab.vue'));
const StockReviewTab = defineAsyncComponent(() => import('@/Components/Agriculture/StockReviewTab.vue'));
const WorkerCostsReportTab = defineAsyncComponent(() => import('@/Components/Agriculture/WorkerCostsReportTab.vue'));

const CrewPrintModal = defineAsyncComponent(() => import('@/Components/Agriculture/Print/CrewPrintModal.vue'));
const OrderPrintModal = defineAsyncComponent(() => import('@/Components/Agriculture/Print/OrderPrintModal.vue'));
const WaybillPrintModal = defineAsyncComponent(() => import('@/Components/Agriculture/Print/WaybillPrintModal.vue'));
const GenericPrintModal = defineAsyncComponent(() => import('@/Components/Agriculture/Print/GenericPrintModal.vue'));
const TankPrintModal = defineAsyncComponent(() => import('@/Components/Agriculture/Print/TankPrintModal.vue'));

const DailyWorkSheetDetailModal = defineAsyncComponent(() => import('@/Components/Agriculture/Modals/DailyWorkSheetDetailModal.vue'));
const TankLogModal = defineAsyncComponent(() => import('@/Components/Agriculture/Modals/TankLogModal.vue'));
const CommentModal = defineAsyncComponent(() => import('@/Components/Agriculture/Modals/CommentModal.vue'));
const ImagePreviewModal = defineAsyncComponent(() => import('@/Components/Agriculture/Modals/ImagePreviewModal.vue'));
const AgricultureFormModal = defineAsyncComponent(() => import('@/Components/Agriculture/Modals/AgricultureFormModal.vue'));

const props = defineProps<{
    activeCategory?: string;
    activeModule?: string;
    companies: any[];
    locations: any[];
    jobTypes: any[];
    personnels: any[];
    workers: any[];
    products: any[];
    crewLeaders: any[];
    tradingParties: any[];
    deliveryTypes: any[];
    fertRecipes: any[];
    sprayRecipes: any[];
    waterSources: any[];
    packagings: any[];
    marketPrices?: any[];
    workPlans: any[];
    fertRuns: any[];
    irrigationSchedules: any[];
    sprayApplications: any[];
    waterAnalysisLogs: any[];
    rawWaterControls: any[];
    purificationControls: any[];
    customerOrders: any[];
    shipments: any[];
    dailyWorkSheets: any[];
}>();

const currentCat = ref(props.activeCategory || 'tesis');
const currentMod = ref(props.activeModule || 'is_planlama');

watch(() => props.activeCategory, (val) => {
    if (val) currentCat.value = val;
});
watch(() => props.activeModule, (val) => {
    if (val) currentMod.value = val;
});

const showModal = ref(false);
const modalType = ref('');
const modalItem = ref<any>(null);

const genericPrintData = ref<any>(null);
const genericPrintType = ref<string>('');
const showGenericPrintModal = ref(false);

const openGenericPrintModal = (type: string, data: any) => {
    genericPrintType.value = type;
    genericPrintData.value = data;
    showGenericPrintModal.value = true;
};

const showCrewPrintModal = ref(false);
const crewPrintData = ref<any>(null);
const openCrewPrintModal = (sheet: any, crew: any) => {
    crewPrintData.value = { sheet, crew };
    showCrewPrintModal.value = true;
};

const showDetailModal = ref(false);
const selectedDetailSheet = ref<any>(null);
const openDetailModal = (sheet: any) => {
    selectedDetailSheet.value = sheet;
    showDetailModal.value = true;
};

const showOrderPrintModal = ref(false);
const orderPrintData = ref<any>(null);
const openOrderPrintModal = (order: any) => {
    orderPrintData.value = order;
    showOrderPrintModal.value = true;
};

const showWaybillPrintModal = ref(false);
const waybillPrintData = ref<any>(null);
const openWaybillPrintModal = (shipment: any) => {
    waybillPrintData.value = shipment;
    showWaybillPrintModal.value = true;
};

const showCommentModal = ref(false);
const commentWorkPlan = ref<any>(null);
const commentForm = useForm({
    work_plan_id: null as any,
    comment: '',
    photo: null as any,
});

const openAddCommentModal = (wp: any) => {
    commentWorkPlan.value = wp;
    commentForm.work_plan_id = wp.id;
    commentForm.comment = '';
    commentForm.photo = null;
    showCommentModal.value = true;
};

const submitCommentForm = () => {
    commentForm.post(route('agriculture.work-plans.comment'), {
        onSuccess: () => {
            showCommentModal.value = false;
        }
    });
};

const previewImageUrl = ref('');
const showImagePreview = ref(false);
const openImagePreview = (url: string) => {
    previewImageUrl.value = url;
    showImagePreview.value = true;
};

const showTankLogModal = ref(false);
const tankLogTarget = ref<any>(null);
const tankLogForm = useForm({
    fertilization_run_id: null as any,
    fertilization_tank_id: null as any,
    prepared_at: new Date().toISOString().slice(0, 16),
    prepared_by_id: props.personnels[0]?.id || null,
    tank_name: '',
    notes: '',
});

const openTankLogModal = (run: any, tank: any) => {
    tankLogTarget.value = { run, tank };
    tankLogForm.fertilization_run_id = run.id;
    tankLogForm.fertilization_tank_id = tank.id;
    tankLogForm.tank_name = tank.tank_name;
    tankLogForm.prepared_at = new Date().toISOString().slice(0, 16);
    tankLogForm.prepared_by_id = props.personnels[0]?.id || null;
    tankLogForm.notes = `${tank.tank_name} solüsyonu yenilendi. Reçete gübreleri hazırlandı ve DIA stok düşüm kaydı oluşturuldu.`;
    showTankLogModal.value = true;
};

const submitTankLogForm = () => {
    tankLogForm.post(route('agriculture.fertilization-tank-logs.store'), {
        onSuccess: () => {
            showTankLogModal.value = false;
        }
    });
};

const showTankPrintModal = ref(false);
const tankPrintData = ref<any>(null);
const openTankPrintModal = (run: any, tank: any) => {
    tankPrintData.value = { run, recipe: run.recipe, tank };
    showTankPrintModal.value = true;
};

const totalOrdersAmount = computed(() => (props.customerOrders || []).reduce((sum: number, o: any) => sum + parseFloat(o.total_amount || 0), 0));
const totalHarvestQuantity = computed(() => {
    let total = 0;
    (props.dailyWorkSheets || []).forEach((sheet: any) => {
        sheet.harvest_items?.forEach((item: any) => {
            total += parseFloat(item.quantity || 0);
        });
    });
    return total;
});

const updateCustomerOrderStatus = (orderId: number, status: string) => {
    router.patch(route('agriculture.customer-orders.update-status', orderId), { status }, { preserveScroll: true });
};

const updateShipmentDeliveryStatus = (shipmentId: number, status: string) => {
    router.patch(route('agriculture.shipment-deliveries.update-status', shipmentId), { status }, { preserveScroll: true });
};

const deleteItem = (routeName: string, id: number, label: string) => {
    if (confirm(`Bu ${label} kaydını silmek istediğinize emin misiniz?`)) {
        router.delete(route(routeName, id), { preserveScroll: true });
    }
};

const openFormModal = (type: string, item: any = null) => {
    modalType.value = type;
    modalItem.value = item;
    showModal.value = true;
};

const openEditWorkPlanModal = (wp: any) => {
    openFormModal('work_plan', wp);
};

const openEditOrderModal = (order: any) => {
    openFormModal('order_edit', order);
};

const getOrderTotalQty = (order: any) => {
    return (order.items || []).reduce((sum: number, it: any) => sum + parseFloat(it.quantity || 0), 0);
};

const getOrderShippedQty = (order: any) => {
    return (order.shipments || []).reduce((sum: number, sh: any) => sum + parseFloat(sh.quantity || 0), 0);
};

const getOrderRemainingQty = (order: any) => {
    const total = getOrderTotalQty(order);
    const shipped = getOrderShippedQty(order);
    return Math.max(0, total - shipped);
};

const openShipmentFromOrder = (order: any) => {
    const remaining = getOrderRemainingQty(order);
    const firstItem = order.items?.[0];
    const shipmentData = {
        customer_order_id: order.id,
        trading_party_id: order.trading_party_id,
        product_id: firstItem?.product_id || (props.products[0]?.id || null),
        packaging_definition_id: firstItem?.packaging_definition_id || (props.packagings[0]?.id || null),
        shipment_date: new Date().toISOString().split('T')[0],
        quantity: remaining > 0 ? remaining : (firstItem?.quantity || 100),
        unit_price: firstItem?.unit_price || 0,
        delivery_type_id: props.deliveryTypes[0]?.id || null,
        vehicle_plate: '',
        driver_name: '',
        driver_phone: '',
        dia_waybill_code: `IRS-${new Date().getFullYear()}-${String(order.id).padStart(4, '0')}`,
        status: 'on_the_way',
        notes: `#ORD-${String(order.id).padStart(4, '0')} nolu siparişe istinaden sevk edildi`,
    };
    openFormModal('shipment', shipmentData);
};

const downloadCsvFile = (filename: string, headers: string[], rows: (string | number)[][]) => {
    const processRow = (row: (string | number)[]) => {
        return row.map(val => {
            const cleanStr = (val === null || val === undefined) ? '' : String(val).replace(/"/g, '""');
            return `"${cleanStr}"`;
        }).join(';');
    };

    const csvContent = '\uFEFF' + [headers.join(';'), ...rows.map(processRow)].join('\r\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.setAttribute('href', url);
    link.setAttribute('download', `${filename}_${new Date().toISOString().slice(0, 10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
};

const exportDailyWorkSheetsToExcel = () => {
    const headers = [
        'Form No',
        'Tarih',
        'Tesis / Üretim Yeri',
        'Çavuş Adı (Taşeron)',
        'Çavuş Kodu',
        'Gelen İşçi Sayısı',
        'Servis Araç Sayısı',
        'Toplam Mesai Saati',
        'Toplam Çavuş Hakedişi (TL)',
        'Toplanan Hasat Detayı (Ürün - Miktar - Kasa)',
        'Toplam Hasat Miktarı (Kg)',
        'Tahmini Hasat Geliri (TL)',
        'Onay Durumu',
        'Onaylayan Yönetici'
    ];

    const rows = (props.dailyWorkSheets || []).map((sheet: any) => {
        const harvestDetails = (sheet.harvest_items || []).map((h: any) => `${h.product?.name || 'Ürün'} (${h.quantity} kg, ${h.box_count || 0} kasa)`).join(' | ');
        const totalKg = (sheet.harvest_items || []).reduce((sum: number, h: any) => sum + parseFloat(h.quantity || 0), 0);
        const totalRevenue = (sheet.harvest_items || []).reduce((sum: number, h: any) => sum + (parseFloat(h.quantity || 0) * parseFloat(h.unit_price || 0)), 0);
        const statusText = sheet.approval_status === 'approved' ? 'Onaylandı' : (sheet.approval_status === 'rejected' ? 'Reddedildi' : 'Onay Bekliyor');

        return [
            `#DW-${String(sheet.id).padStart(4, '0')}`,
            sheet.work_date || '',
            sheet.production_location?.name || 'SASA Tarım Tesisi',
            sheet.labor_head?.name || sheet.labor_head_name || '-',
            sheet.labor_head?.dia_cari_code || '-',
            sheet.worker_count || 0,
            sheet.service_vehicle_count || 0,
            sheet.total_work_hours || 0,
            parseFloat(sheet.total_labor_cost || 0).toFixed(2),
            harvestDetails || '-',
            totalKg.toFixed(2),
            totalRevenue.toFixed(2),
            statusText,
            sheet.approved_by_user?.name || '-'
        ];
    });

    downloadCsvFile('SASA_Tarim_Gunluk_Isci_Hasat_Puantaj', headers, rows);
};

const menuHierarchy = [
    {
        category: 'tesis',
        title: 'Tesis Yönetimi',
        items: [
            { key: 'is_planlama', label: 'İş Planlama' },
        ]
    },
    {
        category: 'uretim',
        title: 'Üretim',
        items: [
            { key: 'gubreleme', label: 'Gübreleme' },
            { key: 'sulama', label: 'Sulama' },
            { key: 'ilaclama', label: 'İlaçlama' },
            { key: 'su_analizleri', label: 'Su Analizleri' },
        ]
    },
    {
        category: 'teknik',
        title: 'Teknik',
        items: [
            { key: 'kaynak_suyu_kontrol', label: 'Kaynak Suyu Kontrol' },
            { key: 'aritma_suyu_kontrol', label: 'Arıtma Suyu Kontrol' },
        ]
    },
    {
        category: 'operasyon',
        title: 'Operasyon',
        items: [
            { key: 'alinan_siparis', label: 'Alınan Sipariş' },
            { key: 'sevkiyat_teslimat', label: 'Sevkiyat & Teslimat' },
            { key: 'gunluk_isci_formu', label: 'Günlük İşçi Formu' },
            { key: 'isci_ve_cavuslar', label: 'İşçi & Çavuş Yönetimi' },
        ]
    },
    {
        category: 'raporlar',
        title: 'Raporlar',
        items: [
            { key: 'piyasa_fiyatlari', label: 'Piyasa Fiyatları' },
            { key: 'hasat_miktarlari', label: 'Hasat Miktarları' },
            { key: 'isci_maliyet_analizi', label: 'İşçi Maliyet Analizi' },
            { key: 'stok_inceleme', label: 'Stok İnceleme' },
        ]
    }
];

const page = usePage();
const permissions = computed(() => (page.props.auth as any)?.permissions || []);
const isAdmin = computed(() => !!(page.props.auth as any)?.is_admin);

const filteredMenuHierarchy = computed(() => {
    if (isAdmin.value) return menuHierarchy;
    return menuHierarchy.filter(g => permissions.value.includes(g.category));
});

const openDropdown = ref<string | null>(null);

const selectModule = (catKey: string, modKey: string) => {
    currentCat.value = catKey;
    currentMod.value = modKey;
    openDropdown.value = null;
    router.get(route('agriculture.index'), { cat: catKey, mod: modKey }, { preserveState: false, preserveScroll: true });
};

onMounted(() => {
    const handleDocumentClick = (e: MouseEvent) => {
        if (!(e.target as HTMLElement).closest('.group-dropdown-container')) {
            openDropdown.value = null;
        }
    };
    document.addEventListener('click', handleDocumentClick);
    onUnmounted(() => {
        document.removeEventListener('click', handleDocumentClick);
    });
});

const printPage = (elementId?: string) => {
    let targetEl: HTMLElement | null = null;
    if (elementId) {
        targetEl = document.getElementById(elementId);
    } else {
        targetEl = document.querySelector('.print-document') as HTMLElement;
    }

    if (!targetEl) {
        const prevTitle = document.title;
        document.title = "";
        window.print();
        document.title = prevTitle;
        return;
    }

    const iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.right = '0';
    iframe.style.bottom = '0';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = '0';
    document.body.appendChild(iframe);

    const doc = iframe.contentWindow?.document;
    if (!doc) {
        window.print();
        return;
    }

    const tailwindLink = document.querySelector('link[rel="stylesheet"]')?.outerHTML || '';
    const styleTags = Array.from(document.querySelectorAll('style')).map(s => s.outerHTML).join('\n');

    doc.open();
    doc.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title></title>
            <meta charset="utf-8">
            ${tailwindLink}
            ${styleTags}
            <style>
                @page { 
                    size: A4 portrait; 
                    margin: 8mm 12mm; 
                }
                * { box-sizing: border-box; }
                body { 
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif !important; 
                    background: white !important; 
                    color: #0f172a !important; 
                    margin: 0 !important; 
                    padding: 0 !important; 
                }
                .no-print { display: none !important; }
                .print-document { 
                    width: 100% !important; 
                    max-width: 100% !important; 
                    border: none !important; 
                    box-shadow: none !important; 
                    padding: 0 !important; 
                }
            </style>
        </head>
        <body>
            <div class="print-document">
                ${targetEl.innerHTML}
            </div>
        </body>
        </html>
    `);
    doc.close();

    if (iframe.contentWindow) {
        iframe.contentWindow.document.title = "";
    }

    iframe.contentWindow?.focus();
    setTimeout(() => {
        iframe.contentWindow?.print();
        setTimeout(() => {
            document.body.removeChild(iframe);
        }, 1000);
    }, 300);
};

const canApprove = (dws: any) => {
    if (dws.status !== 'submitted') return false;
    if (isAdmin.value) return true;
    const currentPersonnel = (page.props.auth as any).personnel;
    if (currentPersonnel && dws.submitted_by?.parent_personnel_id === currentPersonnel.id) {
        return true;
    }
    return false;
};

const handleApproval = (id: number, action: string) => {
    let rejectionNote = '';
    if (action === 'reject') {
        const res = window.prompt('Formu reddetmek için bir açıklama giriniz:');
        if (res === null) return;
        if (!res.trim()) {
            alert('Red açıklaması girmek zorunludur.');
            return;
        }
        rejectionNote = res;
    }
    
    router.post(route('agriculture.daily-work-sheets.approve', id), {
        action: action,
        rejection_note: rejectionNote
    });
};
</script>

<template>
    <Head :title="(filteredMenuHierarchy.flatMap(g => g.items).find(i => i.key === currentMod)?.label || 'Genel') + ' - Tarım Operasyonları'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="w-full flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 20h10"/><path d="M12 20v-8"/><path d="M12 12a5 5 0 0 1 8-3.5 5 5 0 0 1-3.5 8.5H12"/><path d="M12 12a5 5 0 0 0-8-3.5 5 5 0 0 0 3.5 8.5H12"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-sm font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight">
                                Tarım Operasyonları
                            </h1>
                            <span class="text-slate-300 dark:text-slate-700">/</span>
                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                {{ filteredMenuHierarchy.flatMap(g => g.items).find(i => i.key === currentMod)?.label || 'Genel' }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 leading-tight">Üretim sahaları, gübreleme, sulama ve operasyon yönetimi</p>
                    </div>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <WorkPlanTab
                v-if="currentMod === 'is_planlama'"
                :work-plans="workPlans"
                @create="openFormModal('work_plan')"
                @edit="openEditWorkPlanModal"
                @comment="openAddCommentModal"
                @print="(wp) => openGenericPrintModal('is_planlama', wp)"
                @preview-image="openImagePreview"
                @delete="(id) => deleteItem('agriculture.work-plans.destroy', id, 'iş planı')"
            />

            <FertilizationTab
                v-if="currentMod === 'gubreleme'"
                :fert-runs="fertRuns"
                @create="openFormModal('fert_run')"
                @edit="(fr) => openFormModal('fert_run', fr)"
                @toggle-active="(id) => router.post(route('agriculture.fertilization-runs.toggle-active', id), {}, { preserveScroll: true })"
                @print-tank="openTankPrintModal"
                @print-generic="(fr) => openGenericPrintModal('gubreleme', fr)"
                @log-tank="openTankLogModal"
                @delete="(id) => deleteItem('agriculture.fertilization-runs.destroy', id, 'gübreleme reçetesi')"
            />

            <IrrigationTab
                v-if="currentMod === 'sulama'"
                :irrigation-schedules="irrigationSchedules"
                @create="openFormModal('sulama')"
                @edit="(is) => openFormModal('sulama', is)"
                @print="(is) => openGenericPrintModal('sulama', is)"
                @delete="(id) => deleteItem('agriculture.irrigation-schedules.destroy', id, 'sulama programı')"
            />

            <SprayingTab
                v-if="currentMod === 'ilaclama'"
                :spray-applications="sprayApplications"
                :spray-recipes="sprayRecipes"
                @create="openFormModal('spray_app')"
                @edit="(sa) => openFormModal('spray_app', sa)"
                @print="(sa) => openGenericPrintModal('ilaclama', sa)"
                @delete="(id) => deleteItem('agriculture.spraying-applications.destroy', id, 'ilaçlama')"
            />

            <WaterAnalysisTab
                v-if="currentMod === 'su_analizleri'"
                :water-analysis-logs="waterAnalysisLogs"
                @create="openFormModal('water_analysis')"
                @edit="(wa) => openFormModal('water_analysis', wa)"
                @print="(wa) => openGenericPrintModal('su_analizi', wa)"
                @delete="(id) => deleteItem('agriculture.water-analyses.destroy', id, 'su analizi')"
            />

            <RawWaterTab
                v-if="currentMod === 'kaynak_suyu_kontrol'"
                :raw-water-controls="rawWaterControls"
                @create="openFormModal('raw_water')"
                @edit="(rw) => openFormModal('raw_water', rw)"
                @print="(rw) => openGenericPrintModal('kaynak_suyu_kontrol', rw)"
                @delete="(id) => deleteItem('agriculture.raw-water-controls.destroy', id, 'kaynak suyu kontrol')"
            />

            <PurificationTab
                v-if="currentMod === 'aritma_suyu_kontrol'"
                :purification-controls="purificationControls"
                @create="openFormModal('purification')"
                @edit="(pc) => openFormModal('purification', pc)"
                @print="(pc) => openGenericPrintModal('aritma_suyu_kontrol', pc)"
                @delete="(id) => deleteItem('agriculture.purification-controls.destroy', id, 'arıtma kontrol')"
            />

            <CustomerOrdersTab
                v-if="currentMod === 'alinan_siparis'"
                :customer-orders="customerOrders"
                @create="openFormModal('order')"
                @edit="openEditOrderModal"
                @print="openOrderPrintModal"
                @ship="openShipmentFromOrder"
                @update-status="updateCustomerOrderStatus"
                @delete="(id) => deleteItem('agriculture.customer-orders.destroy', id, 'sipariş')"
            />

            <ShipmentsTab
                v-if="currentMod === 'sevkiyat_teslimat'"
                :shipments="shipments"
                @create="openFormModal('shipment')"
                @print-waybill="openWaybillPrintModal"
                @update-status="updateShipmentDeliveryStatus"
                @delete="(id) => deleteItem('agriculture.shipment-deliveries.destroy', id, 'sevkiyat')"
            />

            <DailyWorkSheetsTab
                v-if="currentMod === 'gunluk_isci_formu'"
                :daily-work-sheets="dailyWorkSheets"
                :locations="locations"
                @create="openFormModal('daily_sheet')"
                @detail="openDetailModal"
                @print-crew="({ sheet, crewLeader }) => openCrewPrintModal(sheet, crewLeader)"
                @delete="(id) => deleteItem('agriculture.daily-work-sheets.destroy', id, 'günlük işçi formu')"
                @export-excel="exportDailyWorkSheetsToExcel"
            />

            <CrewLeadersTab
                v-if="currentMod === 'isci_ve_cavuslar'"
                :crew-leaders="crewLeaders"
                @create="openFormModal('crew_leader')"
                @edit="(cl) => openFormModal('crew_leader', cl)"
                @delete="(id) => deleteItem('definitions.crew-leaders.destroy', id, 'çavuş')"
                @add-worker="(clId) => openFormModal('worker', { crew_leader_id: clId })"
                @edit-worker="(w) => openFormModal('worker', w)"
                @delete-worker="(id) => deleteItem('definitions.workers.destroy', id, 'işçi')"
            />

            <MarketPricesTab
                v-if="currentMod === 'piyasa_fiyatlari'"
                :market-prices="marketPrices"
                @create="openFormModal('market_price')"
                @edit="(mp) => openFormModal('market_price', mp)"
                @delete="(id) => deleteItem('agriculture.market-prices.destroy', id, 'fiyat kaydı')"
            />

            <HarvestReportTab
                v-if="currentMod === 'hasat_miktarlari'"
                :daily-work-sheets="dailyWorkSheets"
                :total-harvest-quantity="totalHarvestQuantity"
                :total-orders-amount="totalOrdersAmount"
                @print="(elId) => printPage(elId)"
            />

            <StockReviewTab
                v-if="currentMod === 'stok_inceleme'"
                :daily-work-sheets="dailyWorkSheets"
                :shipments="shipments"
            />

            <WorkerCostsReportTab
                v-if="currentMod === 'isci_maliyet_analizi'"
                :daily-work-sheets="dailyWorkSheets"
                :crew-leaders="crewLeaders"
                :total-harvest-quantity="totalHarvestQuantity"
                @print="(elId) => printPage(elId)"
            />
        </div>

        <AgricultureFormModal
            :show="showModal"
            :type="modalType"
            :item="modalItem"
            :locations="locations"
            :job-types="jobTypes"
            :personnels="personnels"
            :fert-recipes="fertRecipes"
            :fert-runs="fertRuns"
            :spray-recipes="sprayRecipes"
            :water-sources="waterSources"
            :trading-parties="tradingParties"
            :products="products"
            :packagings="packagings"
            :customer-orders="customerOrders"
            :delivery-types="deliveryTypes"
            :companies="companies"
            :crew-leaders="crewLeaders"
            :workers="workers"
            @close="showModal = false"
            @saved="showModal = false"
        />

        <CommentModal
            :show="showCommentModal"
            :work-plan="commentWorkPlan"
            :comment-form="commentForm"
            @close="showCommentModal = false"
            @submit="submitCommentForm"
        />

        <ImagePreviewModal
            :show="showImagePreview"
            :image-url="previewImageUrl"
            @close="showImagePreview = false"
        />

        <CrewPrintModal
            :show="showCrewPrintModal"
            :data="crewPrintData"
            @close="showCrewPrintModal = false"
            @print="printPage()"
        />

        <DailyWorkSheetDetailModal
            :show="showDetailModal"
            :sheet="selectedDetailSheet"
            :can-approve-fn="canApprove"
            @close="showDetailModal = false"
            @approve="(id, action) => { handleApproval(id, action); showDetailModal = false; }"
            @print-crew="(sheet, cl) => openCrewPrintModal(sheet, cl)"
        />

        <OrderPrintModal
            :show="showOrderPrintModal"
            :data="orderPrintData"
            @close="showOrderPrintModal = false"
            @print="printPage()"
        />

        <WaybillPrintModal
            :show="showWaybillPrintModal"
            :data="waybillPrintData"
            @close="showWaybillPrintModal = false"
            @print="printPage()"
        />

        <GenericPrintModal
            :show="showGenericPrintModal"
            :type="genericPrintType"
            :data="genericPrintData"
            @close="showGenericPrintModal = false"
            @print="printPage()"
        />

        <TankLogModal
            :show="showTankLogModal"
            :tank-log-form="tankLogForm"
            :personnels="personnels"
            @close="showTankLogModal = false"
            @submit="submitTankLogForm"
        />

        <TankPrintModal
            :show="showTankPrintModal"
            :data="tankPrintData"
            @close="showTankPrintModal = false"
            @print="(elId) => printPage(elId)"
        />
    </AuthenticatedLayout>
</template>
