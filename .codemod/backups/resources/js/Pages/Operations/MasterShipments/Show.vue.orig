<script setup>
import { ref, computed, onBeforeUnmount } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { 
  Ship, 
  ArrowRight, 
  ArrowLeft,
  FileCheck2, 
  AlertTriangle, 
  CheckCircle2, 
  Clock, 
  UploadCloud, 
  Camera, 
  ShieldCheck, 
  Truck, 
  Anchor, 
  FileText, 
  Plus, 
  Eye,
  Box,
  Layers,
  ThermometerSnowflake,
  Trash2,
  X,
  DollarSign,
  Building2,
  ExternalLink
} from 'lucide-vue-next'

const { isAr } = useI18n()

const props = defineProps({
  shipment: Object,
  profitability: Object,
  compliance: Object,
  shippers: Array,
  consignees: Array,
  customs_brokers: Array,
  truckers: Array,
  packhouses: Array,
  document_types: Array,
})

// Document Upload Modal State
const showUploadModal = ref(false)
const isScannerOpen = ref(false)
const scannerVideo = ref(null)
const scannerCanvas = ref(null)
const mediaStream = ref(null)
const scannerError = ref('')
const scannedPreviewUrl = ref(null)
const fileInput = ref(null)
const selectedFileName = ref('')

const docForm = useForm({
  document_type_id: '',
  reference_number: '',
  issuing_authority: '',
  issue_date: new Date().toISOString().slice(0, 10),
  expiry_date: '',
  document_file: null,
  scanned_image: null,
  metadata: {},
})

// Active Document Filter Tab
const activeDocTab = ref('ALL')

const documentCategories = computed(() => [
  { id: 'ALL', nameAr: 'كافة المستندات', nameEn: 'All Documents', count: props.shipment.documents?.length || 0 },
  { id: 'AGRO', nameAr: 'الزراعية والفنية (Agro)', nameEn: 'Agro & Technical', codes: ['PHYTO', 'FUMIGATION', 'MRL_TEST', 'QUALITY_INSP', 'DATA_LOGGER'] },
  { id: 'COMMERCIAL', nameAr: 'التجارية والمالية', nameEn: 'Commercial & Invoicing', codes: ['COMM_INV', 'PACKING_LIST'] },
  { id: 'SHIPPING', nameAr: 'الملاحية واللوجستية', nameEn: 'Shipping & Logistics', codes: ['BOL', 'DELIVERY_ORDER', 'CONTAINER_MANIFEST'] },
  { id: 'CUSTOMS', nameAr: 'الجمركية والتراخيص', nameEn: 'Customs & Regulatory', codes: ['COO', 'CUSTOMS_CLEARANCE', 'CUSTOMS_AUTH'] },
])

const filteredDocuments = computed(() => {
  const docs = props.shipment.documents || []
  if (activeDocTab.value === 'ALL') return docs

  const category = documentCategories.value.find(c => c.id === activeDocTab.value)
  if (!category || !category.codes) return docs

  return docs.filter(doc => category.codes.includes(doc.document_type?.code))
})

// Open Upload Modal with pre-selected document code if requested
const openUploadModalFor = (docCode) => {
  const dt = props.document_types?.find(d => d.code === docCode)
  docForm.reset()
  if (dt) {
    docForm.document_type_id = dt.id
    if (dt.code === 'COO') {
      docForm.issuing_authority = 'الغرفة التجارية المصرية'
    } else if (dt.code === 'PHYTO') {
      docForm.issuing_authority = 'الإدارة المركزية للحجر الزراعي'
    } else if (dt.code === 'FUMIGATION') {
      docForm.issuing_authority = 'شركة التبخير والتعقيم المعتمدة'
    } else if (dt.code === 'MRL_TEST') {
      docForm.issuing_authority = 'المعمل المركزي لتحليل متبقيات المبيدات'
    }
  }
  selectedFileName.value = ''
  scannedPreviewUrl.value = null
  showUploadModal.value = true
}

const handleFileSelect = (e) => {
  const file = e.target.files[0]
  if (file) {
    docForm.document_file = file
    docForm.scanned_image = null
    selectedFileName.value = file.name
    scannedPreviewUrl.value = null
  }
}

// Camera Scanner Logic
const startCamera = async () => {
  scannerError.value = ''
  isScannerOpen.value = true
  try {
    const stream = await navigator.mediaDevices.getUserMedia({
      video: { facingMode: { ideal: 'environment' }, width: { ideal: 1920 }, height: { ideal: 1080 } }
    })
    mediaStream.value = stream
    if (scannerVideo.value) {
      scannerVideo.value.srcObject = stream
    }
  } catch (err) {
    scannerError.value = isAr.value 
      ? 'تعذر الوصول إلى الكاميرا. يرجى التحقق من أذونات المتصفح أو رفع ملف PDF/صورة.'
      : 'Could not access camera. Please check browser permissions or use file upload.'
  }
}

const stopCamera = () => {
  if (mediaStream.value) {
    mediaStream.value.getTracks().forEach(track => track.stop())
    mediaStream.value = null
  }
  isScannerOpen.value = false
}

const captureScan = () => {
  if (!scannerVideo.value || !scannerCanvas.value) return
  const video = scannerVideo.value
  const canvas = scannerCanvas.value
  canvas.width = video.videoWidth || 1280
  canvas.height = video.videoHeight || 720
  const ctx = canvas.getContext('2d')
  ctx.drawImage(video, 0, 0, canvas.width, canvas.height)
  
  const dataUrl = canvas.toDataURL('image/jpeg', 0.92)
  docForm.scanned_image = dataUrl
  docForm.document_file = null
  selectedFileName.value = isAr.value ? `وثيقة_ممسوحة_${new Date().getTime()}.jpg` : `Scanned_Doc_${new Date().getTime()}.jpg`
  scannedPreviewUrl.value = dataUrl
  stopCamera()
}

onBeforeUnmount(() => {
  stopCamera()
})

const submitUpload = () => {
  docForm.post(route('master-shipments.upload-document', props.shipment.id), {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      showUploadModal.value = false
      docForm.reset()
      selectedFileName.value = ''
      scannedPreviewUrl.value = null
    }
  })
}

// Add Container Modal State
const showAddContainerModal = ref(false)
const containerForm = useForm({
  container_number: '',
  container_type: '40RH',
  tare_weight_kg: 4400,
  payload_capacity_kg: 29500,
  seal_number: '',
  set_temperature_c: 4.0,
  humidity_percentage: 85,
  ventilation_cbm_hr: 15,
})

const submitAddContainer = () => {
  containerForm.post(route('master-shipments.add-container', props.shipment.id), {
    preserveScroll: true,
    onSuccess: () => {
      showAddContainerModal.value = false
      containerForm.reset()
    }
  })
}

const complianceBadge = computed(() => {
  const status = props.shipment.compliance_status || 'PENDING_DOCS'
  switch (status) {
    case 'COMPLIANT':
      return {
        text: isAr.value ? 'مستوفاة ومطابقة للإفراج والتصدير' : 'Fully Compliant',
        badgeClass: 'bg-zinc-950 text-white border-zinc-950',
        icon: CheckCircle2,
      }
    case 'NON_COMPLIANT':
      return {
        text: isAr.value ? 'غير مطابقة / مستندات مرفوضة' : 'Non-Compliant',
        badgeClass: 'bg-red-50 text-red-700 border-red-300',
        icon: AlertTriangle,
      }
    default:
      return {
        text: isAr.value ? 'بانتظار استكمال المستندات الإلزامية' : 'Pending Mandatory Documents',
        badgeClass: 'bg-zinc-100 text-zinc-900 border-zinc-300',
        icon: Clock,
      }
  }
})
</script>

<template>
  <div class="space-y-6 pb-12">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div class="flex items-center gap-3">
        <Link 
          :href="route('master-shipments.index')" 
          class="p-2 rounded-lg bg-zinc-100 text-zinc-600 hover:text-zinc-950 border border-zinc-200 transition-colors"
        >
          <component :is="isAr ? ArrowRight : ArrowLeft" class="w-4 h-4" />
        </Link>
        <div>
          <div class="flex items-center gap-2">
            <Ship class="w-5 h-5 text-zinc-950" />
            <h1 class="text-xl font-extrabold text-zinc-950 tracking-tight font-mono">
              {{ shipment.file_number || shipment.shipment_reference_number }}
            </h1>
            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase tracking-wider bg-zinc-100 text-zinc-900 border border-zinc-300">
              {{ shipment.direction }} / {{ shipment.shipment_mode }}
            </span>
            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase tracking-wider bg-zinc-950 text-white border border-zinc-950">
              {{ shipment.status }}
            </span>
          </div>
          <p class="text-xs text-zinc-500 font-mono mt-0.5 flex items-center gap-2">
            <span>{{ shipment.vessel_name }} (v. {{ shipment.voyage_number }})</span>
            <span>•</span>
            <span>{{ shipment.pol?.name || shipment.pol?.name_ar }} ➔ {{ shipment.pod?.name || shipment.pod?.name_ar }}</span>
          </p>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <button 
          @click="openUploadModalFor(null)"
          class="shadcn-btn-primary gap-1.5 shadow-xs cursor-pointer text-xs"
        >
          <UploadCloud class="w-4 h-4 font-bold" />
          <span>{{ isAr ? 'رفع / مسح مستند جديد' : 'Upload / Scan Doc' }}</span>
        </button>
        <button 
          @click="showAddContainerModal = true"
          class="shadcn-btn-secondary gap-1.5 cursor-pointer text-xs"
        >
          <Box class="w-4 h-4 text-zinc-950" />
          <span>{{ isAr ? 'إضافة حاوية' : 'Add Container' }}</span>
        </button>
      </div>
    </div>

    <!-- Compliance Readiness Card -->
    <div class="shadcn-card p-5 space-y-4">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-zinc-100 pb-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-zinc-950 text-white flex items-center justify-center shrink-0 shadow-xs">
            <component :is="complianceBadge.icon" class="w-5 h-5" />
          </div>
          <div>
            <div class="flex items-center gap-2">
              <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider font-mono">
                {{ isAr ? 'المطابقة والامتثال الزراعي والجمركي' : 'Agro & Customs Compliance' }}
              </span>
              <span :class="['px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase tracking-wider border', complianceBadge.badgeClass]">
                {{ complianceBadge.text }}
              </span>
            </div>
            <h3 class="text-sm font-bold text-zinc-950 mt-0.5">
              {{ isAr ? 'جاهزية الإفراج وإذن التسليم الملاحي (D/O Readiness)' : 'Delivery Order & Export Release Readiness' }}
            </h3>
          </div>
        </div>

        <div class="flex items-center gap-4">
          <div class="text-end">
            <span class="text-3xl font-black text-zinc-950 font-mono">
              {{ compliance?.readiness_percentage || 0 }}%
            </span>
            <p class="text-[10px] text-zinc-400 font-mono">
              {{ isAr ? 'نسبة استيفاء المستندات الإلزامية' : 'Mandatory Docs Fulfilled' }}
            </p>
          </div>
          <div class="w-32 bg-zinc-100 rounded-full h-2.5 overflow-hidden border border-zinc-200">
            <div 
              class="h-full bg-zinc-950 transition-all duration-500"
              :style="{ width: `${compliance?.readiness_percentage || 0}%` }"
            ></div>
          </div>
        </div>
      </div>

      <!-- Missing Mandatory Checklist Alert -->
      <div 
        v-if="compliance?.missing_mandatory_documents && compliance.missing_mandatory_documents.length > 0"
        class="p-3 bg-zinc-50 border border-zinc-200 rounded-lg flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs text-zinc-900"
      >
        <div class="flex items-center gap-2">
          <AlertTriangle class="w-4 h-4 text-zinc-950 shrink-0" />
          <span>
            <strong class="font-bold">{{ isAr ? 'مستندات إلزامية مطلوبة للاعتماد:' : 'Missing Mandatory Documents:' }}</strong>
            {{ compliance.missing_mandatory_documents.map(d => isAr ? d.name_ar : d.name_en).join(' ، ') }}
          </span>
        </div>
        <div class="flex items-center gap-1.5 flex-wrap">
          <button 
            v-for="missing in compliance.missing_mandatory_documents"
            :key="missing.code"
            @click="openUploadModalFor(missing.code)"
            class="shadcn-btn-secondary h-7 px-2.5 text-[11px] font-mono cursor-pointer"
          >
            + {{ isAr ? 'رفع' : 'Upload' }} {{ missing.code }}
          </button>
        </div>
      </div>
    </div>

    <!-- Logistics Partners & Key Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <!-- Shipping Line -->
      <div class="shadcn-card p-4 space-y-2">
        <div class="flex items-center justify-between text-zinc-400 border-b border-zinc-100 pb-2">
          <span class="text-[10px] font-bold uppercase tracking-wider font-mono">{{ isAr ? 'الخط الملاحي والناقل' : 'Shipping Line / Carrier' }}</span>
          <Ship class="w-4 h-4 text-zinc-950" />
        </div>
        <div class="text-sm font-bold text-zinc-950">
          {{ shipment.shipping_line?.legal_name || '—' }}
        </div>
        <div class="text-xs text-zinc-500 font-mono space-y-1">
          <div>{{ isAr ? 'رقم الحجز:' : 'Booking Ref:' }} <span class="text-zinc-950 font-semibold">{{ shipment.booking_reference || '—' }}</span></div>
          <div>{{ isAr ? 'بوليصة الماستر:' : 'Master B/L:' }} <span class="text-zinc-950 font-semibold">{{ shipment.master_bl_number || '—' }}</span></div>
        </div>
      </div>

      <!-- Customs Broker -->
      <div class="shadcn-card p-4 space-y-2">
        <div class="flex items-center justify-between text-zinc-400 border-b border-zinc-100 pb-2">
          <span class="text-[10px] font-bold uppercase tracking-wider font-mono">{{ isAr ? 'المخلص الجمركي المعتمد' : 'Customs Broker' }}</span>
          <ShieldCheck class="w-4 h-4 text-zinc-950" />
        </div>
        <div class="text-sm font-bold text-zinc-950">
          {{ shipment.customs_broker?.legal_name || (isAr ? 'لم يحدد بعد' : 'Not Assigned') }}
        </div>
        <div class="text-xs text-zinc-500 font-mono space-y-1">
          <div>{{ isAr ? 'كود الجهة:' : 'Code:' }} <span class="text-zinc-950 font-semibold">{{ shipment.customs_broker?.code || '—' }}</span></div>
          <div>{{ isAr ? 'الهاتف:' : 'Phone:' }} <span dir="ltr" class="text-zinc-950 font-semibold">{{ shipment.customs_broker?.phone || '—' }}</span></div>
        </div>
      </div>

      <!-- Trucker -->
      <div class="shadcn-card p-4 space-y-2">
        <div class="flex items-center justify-between text-zinc-400 border-b border-zinc-100 pb-2">
          <span class="text-[10px] font-bold uppercase tracking-wider font-mono">{{ isAr ? 'شركة النقل البري والمبرد' : 'Inland Trucking Carrier' }}</span>
          <Truck class="w-4 h-4 text-zinc-950" />
        </div>
        <div class="text-sm font-bold text-zinc-950">
          {{ shipment.trucker?.legal_name || (isAr ? 'لم يحدد بعد' : 'Not Assigned') }}
        </div>
        <div class="text-xs text-zinc-500 font-mono space-y-1">
          <div>{{ isAr ? 'كود الجهة:' : 'Code:' }} <span class="text-zinc-950 font-semibold">{{ shipment.trucker?.code || '—' }}</span></div>
          <div>{{ isAr ? 'الهاتف:' : 'Phone:' }} <span dir="ltr" class="text-zinc-950 font-semibold">{{ shipment.trucker?.phone || '—' }}</span></div>
        </div>
      </div>
    </div>

    <!-- Documents & Compliance Hub -->
    <div class="shadcn-card overflow-hidden">
      <!-- Title & Header Bar -->
      <div class="p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-zinc-200">
        <div>
          <h2 class="text-sm font-bold text-zinc-950 uppercase tracking-wide flex items-center gap-2">
            <FileText class="w-4 h-4 text-zinc-950" />
            <span>{{ isAr ? 'سجل مستندات الشحنة والشهادات الزراعية' : 'Shipment Documents & Agro Certificates Vault' }}</span>
          </h2>
          <p class="text-xs text-zinc-500 mt-0.5">
            {{ isAr ? 'تتبع دورة المستندات الإلزامية: شهادة المنشأ (COO)، الصحة النباتية (Phyto)، التبخير، والتحاليل' : 'Track mandatory origin, phytosanitary, fumigation, and lab analysis certificates' }}
          </p>
        </div>

        <button 
          @click="openUploadModalFor(null)"
          class="shadcn-btn-primary gap-1.5 h-8 text-xs cursor-pointer"
        >
          <Plus class="w-3.5 h-3.5 font-bold" />
          <span>{{ isAr ? 'إضافة مستند' : 'Add Document' }}</span>
        </button>
      </div>

      <!-- Categories Filter Tabs -->
      <div class="p-3 bg-zinc-50 border-b border-zinc-200 flex items-center gap-2 overflow-x-auto">
        <button
          v-for="cat in documentCategories"
          :key="cat.id"
          @click="activeDocTab = cat.id"
          :class="[
            'px-3 py-1 text-xs rounded font-medium whitespace-nowrap transition cursor-pointer',
            activeDocTab === cat.id
              ? 'bg-zinc-950 text-white font-semibold shadow-xs'
              : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 border border-zinc-300'
          ]"
        >
          {{ isAr ? cat.nameAr : cat.nameEn }}
        </button>
      </div>

      <!-- Documents Data Table -->
      <div v-if="filteredDocuments.length === 0" class="p-12 text-center text-zinc-500">
        <FileText class="w-8 h-8 text-zinc-300 mx-auto mb-2" />
        <p class="text-xs font-semibold text-zinc-700">
          {{ isAr ? 'لا توجد مستندات مرفوعة في هذا القسم' : 'No documents found in this section' }}
        </p>
        <p class="text-[11px] text-zinc-400 mt-0.5">
          {{ isAr ? 'انقر على "إضافة مستند" لرفع ملف أو إجراء مسح ضوئي حي بالكاميرا' : 'Click "Add Document" to upload or scan a new file' }}
        </p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-xs" :class="isAr ? 'text-right' : 'text-left'">
          <thead class="bg-zinc-100 text-zinc-700 uppercase text-[10px] tracking-wider border-b border-zinc-200 font-mono">
            <tr>
              <th class="px-5 py-3.5">{{ isAr ? 'نوع المستند والشهادة' : 'Document Type' }}</th>
              <th class="px-5 py-3.5">{{ isAr ? 'الرقم المرجعي' : 'Ref #' }}</th>
              <th class="px-5 py-3.5">{{ isAr ? 'جهة الإصدار' : 'Issuing Authority' }}</th>
              <th class="px-5 py-3.5">{{ isAr ? 'التواريخ (إصدار / انتهاء)' : 'Dates' }}</th>
              <th class="px-5 py-3.5">{{ isAr ? 'الحالة' : 'Status' }}</th>
              <th class="px-5 py-3.5" :class="isAr ? 'text-left' : 'text-right'">{{ isAr ? 'الملف' : 'File' }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-zinc-200">
            <tr v-for="doc in filteredDocuments" :key="doc.id" class="hover:bg-zinc-50 transition-colors">
              <td class="px-5 py-3.5 font-medium text-zinc-950 flex items-center gap-2">
                <FileCheck2 class="w-4 h-4 text-zinc-950 shrink-0" />
                <div>
                  <div class="font-bold">{{ isAr ? doc.document_type?.name_ar : doc.document_type?.name_en }}</div>
                  <span class="font-mono text-[10px] text-zinc-500 font-normal">{{ doc.document_type?.code }}</span>
                </div>
              </td>
              <td class="px-5 py-3.5 font-mono font-semibold text-zinc-950">
                {{ doc.reference_number || '—' }}
              </td>
              <td class="px-5 py-3.5 text-zinc-600">
                {{ doc.issuing_authority || '—' }}
              </td>
              <td class="px-5 py-3.5 font-mono text-[11px] text-zinc-600">
                <div>{{ isAr ? 'إصدار:' : 'Issued:' }} {{ doc.issue_date || '—' }}</div>
                <div v-if="doc.expiry_date" class="text-zinc-900 font-bold">
                  {{ isAr ? 'ينتهي:' : 'Expires:' }} {{ doc.expiry_date }}
                </div>
              </td>
              <td class="px-5 py-3.5">
                <span 
                  :class="[
                    'px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase tracking-wider border',
                    doc.status === 'VERIFIED' ? 'bg-zinc-950 text-white border-zinc-950' :
                    doc.status === 'REJECTED' ? 'bg-red-50 text-red-700 border-red-300' :
                    'bg-zinc-100 text-zinc-800 border-zinc-300'
                  ]"
                >
                  {{ doc.status }}
                </span>
              </td>
              <td class="px-5 py-3.5" :class="isAr ? 'text-left' : 'text-right'">
                <a 
                  v-if="doc.file_path"
                  :href="`/storage/${doc.file_path}`" 
                  target="_blank"
                  class="shadcn-btn-secondary h-7 px-2.5 text-[11px] gap-1 cursor-pointer"
                >
                  <Eye class="w-3.5 h-3.5" />
                  <span>{{ isAr ? 'معاينة' : 'View' }}</span>
                </a>
                <span v-else class="text-zinc-400 font-mono">—</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Containers & Reefer Telemetry Grid -->
    <div class="shadcn-card p-5 space-y-4">
      <div class="flex items-center justify-between border-b border-zinc-100 pb-3">
        <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wide flex items-center gap-2">
          <Box class="w-4 h-4 text-zinc-950" />
          <span>{{ isAr ? 'حاويات الشحنة وإعدادات الريفر' : 'Containers & Reefer Telemetry' }}</span>
        </h3>
        <span class="text-xs text-zinc-500 font-mono font-bold">{{ shipment.containers?.length || 0 }} {{ isAr ? 'حاويات' : 'Units' }}</span>
      </div>

      <div v-if="!shipment.containers || shipment.containers.length === 0" class="text-center py-6 text-xs text-zinc-400 font-mono">
        {{ isAr ? 'لم تتم إضافة أي حاويات بعد.' : 'No containers added yet.' }}
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        <div 
          v-for="c in shipment.containers" 
          :key="c.id"
          class="bg-zinc-50 rounded-lg border border-zinc-200 p-3.5 space-y-2.5 text-xs"
        >
          <div class="flex items-center justify-between">
            <span class="font-mono font-bold text-zinc-950 text-sm">{{ c.container_number }}</span>
            <span class="px-2 py-0.5 bg-zinc-200 text-zinc-900 rounded text-[10px] font-mono font-bold border border-zinc-300">
              {{ c.container_type }}
            </span>
          </div>

          <div class="grid grid-cols-3 gap-2 pt-2 border-t border-zinc-200 font-mono text-center">
            <div class="bg-white border border-zinc-200 rounded p-1.5">
              <span class="block text-[9px] text-zinc-400 uppercase">{{ isAr ? 'الحرارة' : 'Temp' }}</span>
              <span class="font-bold text-zinc-950 text-xs">{{ c.set_temperature_c }} °C</span>
            </div>
            <div class="bg-white border border-zinc-200 rounded p-1.5">
              <span class="block text-[9px] text-zinc-400 uppercase">{{ isAr ? 'الرطوبة' : 'Humidity' }}</span>
              <span class="font-bold text-zinc-950 text-xs">{{ c.humidity_percentage }}%</span>
            </div>
            <div class="bg-white border border-zinc-200 rounded p-1.5">
              <span class="block text-[9px] text-zinc-400 uppercase">{{ isAr ? 'التهوية' : 'Vent' }}</span>
              <span class="font-bold text-zinc-950 text-xs">{{ c.ventilation_cbm_hr }} m³/h</span>
            </div>
          </div>
          <div class="text-[11px] text-zinc-500 font-mono">
            {{ isAr ? 'رقم الرصاص:' : 'Seal No:' }} <span class="font-bold text-zinc-950">{{ c.seal_number || '—' }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- House Shipments List -->
    <div class="shadcn-card p-5 space-y-4">
      <div class="flex items-center justify-between border-b border-zinc-100 pb-3">
        <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wide flex items-center gap-2">
          <Layers class="w-4 h-4 text-zinc-950" />
          <span>{{ isAr ? 'الشحنات الفرعية (House Shipments)' : 'Associated House Shipments' }}</span>
        </h3>
        <span class="text-xs text-zinc-500 font-mono font-bold">{{ shipment.house_shipments?.length || 0 }} {{ isAr ? 'بوالص هاوس' : 'House B/Ls' }}</span>
      </div>

      <div v-if="!shipment.house_shipments || shipment.house_shipments.length === 0" class="text-center py-6 text-xs text-zinc-400 font-mono">
        {{ isAr ? 'لا توجد بوالص هاوس مرتبطة بهذه الشحنة الماستر.' : 'No house shipments associated with this master.' }}
      </div>

      <div v-else class="divide-y divide-zinc-200 text-xs">
        <div 
          v-for="h in shipment.house_shipments" 
          :key="h.id"
          class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
        >
          <div>
            <div class="font-mono font-bold text-zinc-950 text-sm">
              {{ h.house_bl_number }}
            </div>
            <div class="text-zinc-500 text-xs mt-0.5">
              {{ isAr ? 'الشاحن:' : 'Shipper:' }} {{ h.shipper?.legal_name || '—' }} | 
              {{ isAr ? 'المستلم:' : 'Consignee:' }} {{ h.consignee?.legal_name || '—' }}
            </div>
          </div>

          <div class="flex items-center gap-3">
            <span 
              :class="[
                'px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase tracking-wider border',
                h.delivery_order_status === 'RELEASED' ? 'bg-zinc-950 text-white border-zinc-950' :
                'bg-zinc-100 text-zinc-800 border-zinc-300'
              ]"
            >
              {{ isAr ? 'إذن التسليم:' : 'D/O:' }} {{ h.delivery_order_status }}
            </span>
            <Link 
              :href="route('house-shipments.show', h.id)"
              class="shadcn-btn-secondary h-7 px-3 text-xs cursor-pointer"
            >
              {{ isAr ? 'عرض' : 'View' }}
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Profitability & Cost Breakdown (if available) -->
    <div v-if="profitability" class="shadcn-card p-5 space-y-4">
      <div class="flex items-center justify-between border-b border-zinc-100 pb-3">
        <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wide flex items-center gap-2">
          <DollarSign class="w-4 h-4 text-zinc-950" />
          <span>{{ isAr ? 'المؤشرات المالية والربحية التقديرية' : 'Estimated Profitability & Revenue' }}</span>
        </h3>
        <span class="text-xs text-zinc-500 font-mono">{{ profitability.currency || 'USD' }}</span>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-center">
        <div class="bg-zinc-50 border border-zinc-200 rounded-lg p-3">
          <div class="text-[10px] text-zinc-400 uppercase font-mono">{{ isAr ? 'الإيرادات' : 'Revenue' }}</div>
          <div class="text-base font-bold text-zinc-950 font-mono mt-0.5">{{ profitability.estimated_revenue || '0.00' }}</div>
        </div>
        <div class="bg-zinc-50 border border-zinc-200 rounded-lg p-3">
          <div class="text-[10px] text-zinc-400 uppercase font-mono">{{ isAr ? 'التكاليف' : 'Costs' }}</div>
          <div class="text-base font-bold text-zinc-950 font-mono mt-0.5">{{ profitability.allocated_costs || '0.00' }}</div>
        </div>
        <div class="bg-zinc-50 border border-zinc-200 rounded-lg p-3">
          <div class="text-[10px] text-zinc-400 uppercase font-mono">{{ isAr ? 'مجمل الربح' : 'Gross Profit' }}</div>
          <div class="text-base font-bold text-zinc-950 font-mono mt-0.5">{{ profitability.estimated_gross_profit || '0.00' }}</div>
        </div>
        <div class="bg-zinc-50 border border-zinc-200 rounded-lg p-3">
          <div class="text-[10px] text-zinc-400 uppercase font-mono">{{ isAr ? 'هامش الربح' : 'Margin' }}</div>
          <div class="text-base font-bold text-zinc-950 font-mono mt-0.5">{{ profitability.profit_margin_pct || '0' }}%</div>
        </div>
      </div>
    </div>

    <!-- Upload & Camera Scanner Modal -->
    <div v-if="showUploadModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs">
      <div class="shadcn-card max-w-lg w-full p-6 space-y-4 shadow-2xl relative max-h-[90vh] overflow-y-auto">
        <button 
          @click="showUploadModal = false"
          class="absolute top-4 end-4 text-zinc-400 hover:text-zinc-950 cursor-pointer"
        >
          <X class="w-4 h-4" />
        </button>

        <h3 class="text-base font-bold text-zinc-950 flex items-center gap-2 border-b border-zinc-100 pb-3">
          <UploadCloud class="w-4 h-4 text-zinc-950" />
          <span>{{ isAr ? 'رفع أو مسح مستند ومطابقة الشحنة' : 'Upload or Scan Shipment Document' }}</span>
        </h3>

        <form @submit.prevent="submitUpload" class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">
              {{ isAr ? 'نوع المستند والشهادة' : 'Document Type' }}
              <span class="text-red-500">*</span>
            </label>
            <select 
              v-model="docForm.document_type_id"
              required
              class="shadcn-input"
            >
              <option value="" disabled>{{ isAr ? 'اختر نوع المستند...' : 'Select document type...' }}</option>
              <option v-for="dt in document_types" :key="dt.id" :value="dt.id">
                {{ dt.code }} - {{ isAr ? dt.name_ar : dt.name_en }}
              </option>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">
                {{ isAr ? 'الرقم المرجعي / رقم الشهادة' : 'Reference Number' }}
              </label>
              <input 
                v-model="docForm.reference_number"
                type="text"
                placeholder="e.g. PHYTO-EG-2026-99"
                class="shadcn-input font-mono"
                dir="ltr"
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">
                {{ isAr ? 'جهة الإصدار' : 'Issuing Authority' }}
              </label>
              <input 
                v-model="docForm.issuing_authority"
                type="text"
                placeholder="الحجر الزراعي / المعمل / الغرفة"
                class="shadcn-input"
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">
                {{ isAr ? 'تاريخ الإصدار' : 'Issue Date' }}
              </label>
              <input 
                v-model="docForm.issue_date"
                type="date"
                class="shadcn-input font-mono"
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">
                {{ isAr ? 'تاريخ الانتهاء (إن وجد)' : 'Expiry Date' }}
              </label>
              <input 
                v-model="docForm.expiry_date"
                type="date"
                class="shadcn-input font-mono"
              />
            </div>
          </div>

          <!-- Attachment Area: File Upload OR Camera Scanner -->
          <div class="pt-2 border-t border-zinc-100 space-y-2">
            <label class="block text-xs font-bold text-zinc-800">
              {{ isAr ? 'مرفق الوثيقة (ملف PDF أو مسح بالكاميرا) *' : 'Document Attachment *' }}
            </label>

            <div class="flex items-center gap-3">
              <button 
                type="button"
                @click="fileInput.click()"
                class="flex-1 shadcn-btn-secondary gap-2 text-xs py-2 h-auto cursor-pointer"
              >
                <UploadCloud class="w-4 h-4 text-zinc-950" />
                <span>{{ isAr ? 'ملف PDF / صورة' : 'Upload File' }}</span>
              </button>

              <button 
                type="button"
                @click="startCamera"
                class="flex-1 shadcn-btn-secondary gap-2 text-xs py-2 h-auto cursor-pointer"
              >
                <Camera class="w-4 h-4 text-zinc-950" />
                <span>{{ isAr ? 'مسح بالكاميرا' : 'Camera Scan' }}</span>
              </button>

              <input 
                ref="fileInput"
                type="file" 
                accept="application/pdf,image/*" 
                class="hidden" 
                @change="handleFileSelect"
              />
            </div>

            <!-- Selected File or Scanned Preview Display -->
            <div v-if="selectedFileName" class="p-3 bg-zinc-50 border border-zinc-200 rounded-lg flex items-center justify-between text-xs text-zinc-900 font-mono">
              <div class="flex items-center gap-2 truncate">
                <CheckCircle2 class="w-4 h-4 text-zinc-950 shrink-0" />
                <span class="truncate font-semibold">{{ selectedFileName }}</span>
              </div>
              <button 
                type="button"
                @click="selectedFileName = ''; docForm.document_file = null; docForm.scanned_image = null; scannedPreviewUrl = null"
                class="text-zinc-400 hover:text-red-600 transition-colors"
              >
                <Trash2 class="w-4 h-4" />
              </button>
            </div>

            <div v-if="scannedPreviewUrl" class="mt-2 text-center">
              <img :src="scannedPreviewUrl" class="max-h-36 rounded border border-zinc-200 mx-auto shadow-xs" />
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-zinc-100">
            <button 
              type="button"
              @click="showUploadModal = false"
              class="shadcn-btn-ghost text-xs cursor-pointer"
            >
              {{ isAr ? 'إلغاء' : 'Cancel' }}
            </button>
            <button 
              type="submit"
              :disabled="docForm.processing || (!docForm.document_file && !docForm.scanned_image)"
              class="shadcn-btn-primary text-xs cursor-pointer disabled:opacity-50"
            >
              {{ docForm.processing ? (isAr ? 'جاري الحفظ...' : 'Uploading...') : (isAr ? 'حفظ واعتماد المستند' : 'Save Document') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Live Camera Scanner Overlay Modal -->
    <div v-if="isScannerOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/80 backdrop-blur-md">
      <div class="shadcn-card max-w-lg w-full p-5 space-y-3 shadow-2xl relative bg-zinc-950 text-white border-zinc-800">
        <button 
          @click="stopCamera"
          class="absolute top-4 end-4 text-zinc-400 hover:text-white cursor-pointer"
        >
          <X class="w-4 h-4" />
        </button>

        <h4 class="text-sm font-bold flex items-center gap-2">
          <Camera class="w-4 h-4 text-white" />
          <span>{{ isAr ? 'المسح الضوئي الحي للمستند' : 'Live Document Camera Scanner' }}</span>
        </h4>

        <div v-if="scannerError" class="p-3 bg-red-950/60 border border-red-800 rounded text-xs text-red-200">
          {{ scannerError }}
        </div>

        <div class="relative bg-black rounded border border-zinc-800 aspect-video flex items-center justify-center overflow-hidden">
          <video ref="scannerVideo" autoplay playsinline class="w-full h-full object-cover"></video>
          <div class="absolute inset-4 border-2 border-dashed border-white/50 rounded pointer-events-none flex items-center justify-center">
            <span class="text-[10px] bg-black/80 px-2 py-0.5 rounded text-white font-mono">
              {{ isAr ? 'ضع المستند داخل الإطار' : 'Align document within frame' }}
            </span>
          </div>
        </div>

        <canvas ref="scannerCanvas" class="hidden"></canvas>

        <div class="flex items-center justify-between pt-2">
          <button 
            type="button"
            @click="stopCamera"
            class="text-xs text-zinc-400 hover:text-white cursor-pointer"
          >
            {{ isAr ? 'إلغاء' : 'Cancel' }}
          </button>
          <button 
            type="button"
            @click="captureScan"
            class="shadcn-btn-primary bg-white text-zinc-950 hover:bg-zinc-200 text-xs font-bold gap-1.5 cursor-pointer"
          >
            <Camera class="w-4 h-4" />
            <span>{{ isAr ? 'التقاط وحفظ الصورة' : 'Capture Document' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Add Container Modal -->
    <div v-if="showAddContainerModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs">
      <div class="shadcn-card max-w-md w-full p-6 space-y-4 shadow-2xl relative">
        <button 
          @click="showAddContainerModal = false"
          class="absolute top-4 end-4 text-zinc-400 hover:text-zinc-950 cursor-pointer"
        >
          <X class="w-4 h-4" />
        </button>

        <h3 class="text-sm font-bold text-zinc-950 flex items-center gap-2 border-b border-zinc-100 pb-3">
          <Box class="w-4 h-4 text-zinc-950" />
          <span>{{ isAr ? 'إضافة حاوية ريفر للشحنة' : 'Add Reefer Container' }}</span>
        </h3>

        <form @submit.prevent="submitAddContainer" class="space-y-3">
          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">
              {{ isAr ? 'رقم الحاوية (ISO Container No)' : 'Container Number' }}
              <span class="text-red-500">*</span>
            </label>
            <input 
              v-model="containerForm.container_number"
              type="text" 
              required 
              placeholder="e.g. MSKU9876543" 
              class="shadcn-input font-mono uppercase"
              dir="ltr"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">
                {{ isAr ? 'نوع الحاوية' : 'Type' }}
              </label>
              <select 
                v-model="containerForm.container_type"
                class="shadcn-input font-mono"
              >
                <option value="40RH">40' Reefer High Cube (40RH)</option>
                <option value="20RF">20' Reefer Standard (20RF)</option>
                <option value="40HC">40' High Cube Dry (40HC)</option>
                <option value="20GP">20' General Purpose (20GP)</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">
                {{ isAr ? 'رقم الرصاص (Seal No)' : 'Seal Number' }}
              </label>
              <input 
                v-model="containerForm.seal_number"
                type="text" 
                placeholder="e.g. ML-EG-998811" 
                class="shadcn-input font-mono"
                dir="ltr"
              />
            </div>
          </div>

          <div class="grid grid-cols-3 gap-2">
            <div>
              <label class="block text-[10px] font-bold text-zinc-800 mb-1">
                {{ isAr ? 'الحرارة (°C)' : 'Temp (°C)' }}
              </label>
              <input 
                v-model="containerForm.set_temperature_c"
                type="number" 
                step="0.1" 
                class="shadcn-input font-mono text-xs"
              />
            </div>
            <div>
              <label class="block text-[10px] font-bold text-zinc-800 mb-1">
                {{ isAr ? 'الرطوبة (%)' : 'Humidity (%)' }}
              </label>
              <input 
                v-model="containerForm.humidity_percentage"
                type="number" 
                class="shadcn-input font-mono text-xs"
              />
            </div>
            <div>
              <label class="block text-[10px] font-bold text-zinc-800 mb-1">
                {{ isAr ? 'التهوية (m³/h)' : 'Vent (m³/h)' }}
              </label>
              <input 
                v-model="containerForm.ventilation_cbm_hr"
                type="number" 
                class="shadcn-input font-mono text-xs"
              />
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-zinc-100">
            <button 
              type="button"
              @click="showAddContainerModal = false"
              class="shadcn-btn-ghost text-xs cursor-pointer"
            >
              {{ isAr ? 'إلغاء' : 'Cancel' }}
            </button>
            <button 
              type="submit"
              :disabled="containerForm.processing"
              class="shadcn-btn-primary text-xs cursor-pointer"
            >
              {{ isAr ? 'حفظ الحاوية' : 'Save Container' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
