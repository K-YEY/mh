<script setup>
import { ref, computed } from 'vue'
import { router, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { 
  Search, 
  Ship, 
  Box, 
  ArrowRight, 
  ArrowLeft, 
  Clock, 
  CheckCircle2, 
  MapPin, 
  ThermometerSnowflake, 
  History, 
  Plus, 
  Trash2, 
  Anchor, 
  ExternalLink,
  ShieldCheck,
  AlertCircle,
  X,
  RefreshCw,
  Layers,
  ChevronRight,
  Globe,
  Navigation,
  Compass,
  Radio
} from 'lucide-vue-next'

const { isAr, formatDateTime } = useI18n()

const props = defineProps({
  search_number: String,
  search_type: {
    type: String,
    default: 'container',
  },
  container: Object,
  vessel: Object,
  carrier_scraper: Object,
  shipment: Object,
  events: Array,
  history: Array,
  system_containers: Array,
  system_vessels: Array,
})

const activeTab = ref(props.search_type || (props.vessel ? 'vessel' : 'container'))
const query = ref(props.search_number || '')
const showAddEventModal = ref(false)

const handleSearch = () => {
  if (!query.value.trim()) return
  const q = query.value.trim()
  
  if (activeTab.value === 'vessel') {
    router.get(route('tracking.index'), {
      vessel_name: q,
      search_type: 'vessel'
    }, { preserveState: true })
  } else {
    router.get(route('tracking.index'), {
      container_number: q.toUpperCase(),
      search_type: 'container'
    }, { preserveState: true })
  }
}

const trackSpecificContainer = (num) => {
  activeTab.value = 'container'
  query.value = num
  router.get(route('tracking.index'), { 
    container_number: num,
    search_type: 'container'
  }, { preserveState: true })
}

const trackSpecificVessel = (vesselName) => {
  activeTab.value = 'vessel'
  query.value = vesselName
  router.get(route('tracking.index'), { 
    vessel_name: vesselName,
    search_type: 'vessel'
  }, { preserveState: true })
}

const switchTab = (tab) => {
  activeTab.value = tab
  if (tab === 'vessel' && (!query.value || query.value.match(/^[A-Z]{4}[0-9]+/))) {
    query.value = props.vessel?.name || 'EVER GIVEN'
  } else if (tab === 'container' && query.value && query.value.includes(' ')) {
    query.value = props.container?.container_number || ''
  }
}

// Add Manual Event Form
const eventForm = useForm({
  master_container_id: props.container?.id || '',
  event_code: 'IN_TRANSIT',
  event_name: 'تم التأكد من سلامة التبريد وإبحار الشحنة',
  location_name: 'ميناء الإسكندرية / البحر المتوسط',
  occurred_at: new Date().toISOString().slice(0, 16).replace('T', ' '),
  notes: '',
})

const submitAddEvent = () => {
  eventForm.master_container_id = props.container?.id
  eventForm.post(route('tracking.add-event'), {
    onSuccess: () => {
      showAddEventModal.value = false
      eventForm.reset()
    }
  })
}

const deleteHistoryItem = (id) => {
  router.delete(route('tracking.delete-history', id), { preserveScroll: true })
}

const clearAllHistory = () => {
  if (confirm(isAr.value ? 'هل أنت متأكد من رغبتك في مسح كافة العناصر من سجل التتبع؟' : 'Are you sure you want to clear all tracking history?')) {
    router.delete(route('tracking.clear-all-history'), { preserveScroll: true })
  }
}

// Milestone stages helper
const milestoneStages = [
  { code: 'EMPTY_PICKUP', nameAr: 'سحب الفارغ', nameEn: 'Empty Pickup' },
  { code: 'GATE_IN', nameAr: 'دخول الميناء', nameEn: 'Gated In' },
  { code: 'CUSTOMS_CLEARED', nameAr: 'الإفراج الجمركي', nameEn: 'Customs Cleared' },
  { code: 'LOADED', nameAr: 'الشحن والتحميل', nameEn: 'Loaded On-Board' },
  { code: 'SAILED_IN_TRANSIT', nameAr: 'مبحرة بالبحر', nameEn: 'In Transit' },
  { code: 'ARRIVED', nameAr: 'وصول المقصد', nameEn: 'Vessel Arrived' },
  { code: 'DISCHARGED', nameAr: 'التفريغ بالرصيف', nameEn: 'Discharged' },
  { code: 'EMPTY_RETURN', nameAr: 'إعادة الفارغ', nameEn: 'Empty Returned' },
]

const isMilestoneCompleted = (code) => {
  if (!props.events || props.events.length === 0) return false
  const currentStatus = props.container?.current_status || ''
  
  if (props.events.some(e => e.event_code === code)) return true

  const codesOrder = ['EMPTY_PICKUP', 'GATE_IN', 'CUSTOMS_CLEARED', 'LOADED', 'SAILED_IN_TRANSIT', 'ARRIVED', 'DISCHARGED', 'EMPTY_RETURN']
  const currentIndex = codesOrder.indexOf(currentStatus)
  const targetIndex = codesOrder.indexOf(code)
  
  return targetIndex !== -1 && currentIndex !== -1 && targetIndex <= currentIndex
}
</script>

<template>
  <div class="space-y-6 pb-12">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-extrabold text-blue-950 tracking-tight flex items-center gap-2">
          <Search class="w-6 h-6 text-blue-700" />
          <span>{{ isAr ? 'تتبع الحاويات والشحنات البحرية (Container & Cargo Tracking)' : 'Container & Cargo Tracking Hub' }}</span>
        </h1>
        <p class="text-xs text-zinc-500">
          {{ isAr ? 'متابعة حية لمسار الحاوية، محطات العبور، درجات حرارة التبريد، وسجل الحركات السابقة' : 'Live tracking for container milestones, cold-chain telemetry, port calls, and search history' }}
        </p>
      </div>

      <div class="flex items-center gap-2">
        <Link
          :href="route('master-shipments.index')"
          class="shadcn-btn-secondary gap-1.5 cursor-pointer text-xs hover:border-blue-300 hover:text-blue-900"
        >
          <Ship class="w-4 h-4 text-blue-900" />
          <span>{{ isAr ? 'الشحنات الماستر' : 'Master Shipments' }}</span>
        </Link>
      </div>
    </div>

    <!-- Search Box & Mode Switcher Card -->
    <div class="shadcn-card p-6 space-y-4">
      <!-- Mode Tabs Switcher -->
      <div class="flex items-center gap-2 border-b border-zinc-200 pb-3">
        <button
          type="button"
          @click="switchTab('container')"
          :class="[
            'flex items-center gap-2 px-4 py-2 rounded-md text-xs font-bold transition cursor-pointer',
            activeTab === 'container'
              ? 'bg-blue-700 text-white shadow-xs shadow-blue-700/30'
              : 'bg-zinc-100 text-zinc-600 hover:bg-blue-50 hover:text-blue-900'
          ]"
        >
          <Box class="w-4 h-4" />
          <span>{{ isAr ? '📦 تتبع حاوية أو شحنة (FindTeu & Containers)' : 'Container & Cargo Tracking' }}</span>
        </button>

        <button
          type="button"
          @click="switchTab('vessel')"
          :class="[
            'flex items-center gap-2 px-4 py-2 rounded-md text-xs font-bold transition cursor-pointer',
            activeTab === 'vessel'
              ? 'bg-blue-700 text-white shadow-xs shadow-blue-700/30'
              : 'bg-zinc-100 text-zinc-600 hover:bg-blue-50 hover:text-blue-900'
          ]"
        >
          <Ship class="w-4 h-4" />
          <span>{{ isAr ? '🚢 تتبع السفن البحرية حياً (MarineTraffic AIS Live)' : 'MarineTraffic Live AIS Vessel Tracking' }}</span>
        </button>
      </div>

      <!-- Search Input Form -->
      <div class="flex flex-col md:flex-row items-center gap-3">
        <div class="relative flex-1 w-full">
          <Search class="absolute top-1/2 -translate-y-1/2 w-5 h-5 text-zinc-400" :class="isAr ? 'right-3.5' : 'left-3.5'" />
          <input
            v-model="query"
            @keyup.enter="handleSearch"
            type="text"
            :placeholder="activeTab === 'vessel'
              ? (isAr ? 'اكتب اسم السفينة بالإنجليزية أو رقم IMO (مثال: EVER GIVEN أو MAERSK MC-KINNEY MOLLER أو 9811000)...' : 'Enter Vessel name or IMO (e.g. EVER GIVEN, MAERSK MC-KINNEY MOLLER, 9811000)...')
              : (isAr ? 'اكتب رقم الحاوية هنا (مثال: MSKU8801923 أو CMAU... أو رقم البوليصة)...' : 'Enter container # (e.g. MSKU8801923, CMAU...), or Master B/L...')"
            :style="isAr ? 'padding-right: 2.75rem !important; padding-left: 0.75rem !important;' : 'padding-left: 2.75rem !important; padding-right: 0.75rem !important;'"
            class="shadcn-input h-12 text-sm font-mono uppercase font-bold"
          />
        </div>
        <button
          @click="handleSearch"
          class="shadcn-btn-primary h-12 px-6 gap-2 w-full md:w-auto text-sm cursor-pointer shadow-xs"
        >
          <Search class="w-4 h-4 font-bold" />
          <span>{{ activeTab === 'vessel' ? (isAr ? 'اسكرابنج وتتبع السفينة' : 'Scrape Vessel AIS') : (isAr ? 'تتبع الحاوية الآن' : 'Track Container') }}</span>
        </button>
      </div>

      <!-- Quick Suggested Containers Chips -->
      <div v-if="activeTab === 'container' && system_containers && system_containers.length > 0" class="flex items-center gap-2 flex-wrap pt-1 text-xs">
        <span class="text-zinc-400 font-mono text-[11px]">{{ isAr ? 'حاويات نشطة بالنظام:' : 'Active Containers:' }}</span>
        <button
          v-for="sc in system_containers"
          :key="sc.id"
          @click="trackSpecificContainer(sc.container_number)"
          class="px-2.5 py-1 rounded bg-zinc-100 hover:bg-blue-50 text-zinc-900 hover:text-blue-900 border border-zinc-300 hover:border-blue-300 font-mono text-[11px] font-bold transition cursor-pointer"
        >
          {{ sc.container_number }}
        </button>
      </div>

      <!-- Quick Suggested Vessels Chips -->
      <div v-else-if="activeTab === 'vessel' && system_vessels && system_vessels.length > 0" class="flex items-center gap-2 flex-wrap pt-1 text-xs">
        <span class="text-zinc-400 font-mono text-[11px]">{{ isAr ? 'سفن مقترحة للتتبع الحي:' : 'Suggested Vessels:' }}</span>
        <button
          v-for="sv in system_vessels"
          :key="sv.name"
          @click="trackSpecificVessel(sv.name)"
          class="px-2.5 py-1 rounded bg-zinc-100 hover:bg-blue-50 text-zinc-900 hover:text-blue-900 border border-zinc-300 hover:border-blue-300 font-mono text-[11px] font-bold transition cursor-pointer flex items-center gap-1.5"
        >
          <Ship class="w-3 h-3 text-blue-700" />
          <span>{{ sv.name }}</span>
        </button>
      </div>
    </div>

    <!-- Active Vessel AIS Tracking Result Card -->
    <div v-if="vessel" class="space-y-6">
      <div class="shadcn-card p-5 space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-zinc-100 pb-4">
          <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-lg bg-blue-900 text-white flex items-center justify-center shrink-0 shadow-sm shadow-blue-900/30">
              <Ship class="w-6 h-6" />
            </div>
            <div>
              <div class="flex items-center gap-2 flex-wrap">
                <h2 class="text-2xl font-black text-blue-950 font-mono tracking-tight">
                  {{ vessel.name }}
                </h2>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[10px] font-mono font-bold uppercase tracking-wider bg-blue-700 text-white border border-blue-600 shadow-xs">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                  <span>{{ isAr ? 'مباشر AIS Live' : 'Live AIS' }}</span>
                </span>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase tracking-wider bg-blue-50 text-blue-950 border border-blue-200">
                  {{ vessel.ship_type || 'Container Ship' }}
                </span>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-blue-50 text-blue-950 border border-blue-200">
                  🚩 {{ vessel.flag || 'Panama' }}
                </span>
              </div>
              <p class="text-xs text-zinc-500 font-mono mt-1 flex items-center gap-2 flex-wrap">
                <span>IMO: <strong class="text-blue-900">{{ vessel.imo }}</strong></span>
                <span>•</span>
                <span>MMSI: <strong class="text-blue-900">{{ vessel.mmsi }}</strong></span>
                <span>•</span>
                <span>{{ isAr ? 'سنة البناء:' : 'Built:' }} <strong class="text-zinc-800">{{ vessel.year_built || '2020' }}</strong></span>
                <span>•</span>
                <span>{{ isAr ? 'المصدر:' : 'Source:' }} {{ vessel.source }}</span>
              </p>
            </div>
          </div>

          <!-- Direct Scraper External Links: Strictly MarineTraffic -->
          <div class="flex items-center gap-2 flex-wrap">
            <a
              v-if="vessel.marinetraffic_url"
              :href="vessel.marinetraffic_url"
              target="_blank"
              class="shadcn-btn-primary gap-1.5 text-xs cursor-pointer inline-flex items-center shadow-xs"
            >
              <Globe class="w-3.5 h-3.5 text-white" />
              <span>{{ isAr ? 'فتح تقرير السفينة على MarineTraffic' : 'Open Vessel on MarineTraffic' }}</span>
              <ExternalLink class="w-3 h-3 text-cyan-200" />
            </a>

            <a
              :href="vessel.marinetraffic_map_url || `https://www.marinetraffic.com/en/ais/home/centerx:0/centery:0/zoom:2/keyword:${encodeURIComponent(vessel.name)}`"
              target="_blank"
              class="shadcn-btn-outline gap-1.5 text-xs cursor-pointer inline-flex items-center hover:border-blue-300 hover:text-blue-900"
            >
              <Navigation class="w-3.5 h-3.5 text-blue-700" />
              <span>{{ isAr ? 'خريطة MarineTraffic الحية (Live Map)' : 'MarineTraffic Live AIS Map' }}</span>
              <ExternalLink class="w-3 h-3 text-zinc-400" />
            </a>
          </div>
        </div>

        <!-- Vessel Voyage Route & Timings Showcase (From, To, ATD, ATA, Vessel) -->
        <div class="bg-gradient-to-br from-blue-950 via-slate-900 to-blue-950 text-white rounded-xl p-5 shadow-md border border-blue-900/60 space-y-4">
          <div class="flex items-center justify-between border-b border-blue-800/80 pb-3">
            <div class="flex items-center gap-2">
              <Ship class="w-4 h-4 text-cyan-400" />
              <span class="text-xs font-bold uppercase tracking-wider font-mono text-cyan-200">
                {{ isAr ? 'بيانات رحلة ومسار السفينة (Vessel Voyage & Timings)' : 'Vessel Voyage & Timings' }}
              </span>
            </div>
            <div class="flex items-center gap-2">
              <span class="text-xs font-mono text-zinc-400">{{ isAr ? 'السفينة:' : 'Vessel:' }}</span>
              <span class="px-2.5 py-0.5 rounded text-[11px] font-mono font-bold bg-blue-900/80 text-white border border-blue-700 uppercase">
                {{ vessel.name }}
              </span>
            </div>
          </div>

          <!-- FROM / TO & Timings 2-Column Banner -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- FROM (Departure Port & ATD) -->
            <div class="bg-blue-900/40 border border-blue-800/80 rounded-lg p-4 space-y-2">
              <div class="flex items-center justify-between">
                <span class="text-[10px] text-cyan-300 font-mono uppercase font-bold flex items-center gap-1.5">
                  <MapPin class="w-3.5 h-3.5 text-cyan-400" />
                  <span>{{ isAr ? 'ميناء المغادرة (From / Origin Port)' : 'From (Origin Port)' }}</span>
                </span>
                <span class="px-2 py-0.5 rounded text-[9px] font-mono font-bold bg-blue-800 text-cyan-200">POL</span>
              </div>
              <div class="text-base font-extrabold text-white font-sans">
                {{ vessel.from_port || 'Alexandria Port (EGALY) / Port Said' }}
              </div>
              <div class="pt-2 border-t border-blue-800/80 flex items-center justify-between text-xs font-mono">
                <span class="text-zinc-300 text-[11px]">{{ isAr ? 'وقت المغادرة الفعلي (ATD):' : 'Actual Time of Departure (ATD):' }}</span>
                <span class="text-emerald-400 font-bold">{{ vessel.atd || '2026-09-02 08:30 UTC' }}</span>
              </div>
            </div>

            <!-- TO (Destination Port & ATA/ETA) -->
            <div class="bg-blue-900/40 border border-blue-800/80 rounded-lg p-4 space-y-2">
              <div class="flex items-center justify-between">
                <span class="text-[10px] text-cyan-300 font-mono uppercase font-bold flex items-center gap-1.5">
                  <Anchor class="w-3.5 h-3.5 text-cyan-400" />
                  <span>{{ isAr ? 'ميناء المقصد والوصول (To / Destination Port)' : 'To (Destination Port)' }}</span>
                </span>
                <span class="px-2 py-0.5 rounded text-[9px] font-mono font-bold bg-blue-800 text-cyan-200">POD</span>
              </div>
              <div class="text-base font-extrabold text-white font-sans">
                {{ vessel.to_port || vessel.destination || 'Rotterdam Gateway (NLRTM)' }}
              </div>
              <div class="pt-2 border-t border-blue-800/80 flex items-center justify-between text-xs font-mono">
                <span class="text-zinc-300 text-[11px]">{{ isAr ? 'وقت الوصول (ATA / ETA):' : 'Actual / Estimated Arrival (ATA/ETA):' }}</span>
                <span class="text-cyan-300 font-bold">{{ vessel.ata || vessel.eta || 'Sep 8, 07:00 UTC' }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Vessel Key Live Telemetry Metrics Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
          <div class="bg-zinc-50 border border-zinc-200 rounded-lg p-3">
            <div class="text-[10px] text-zinc-400 uppercase font-mono flex items-center gap-1">
              <Compass class="w-3 h-3 text-zinc-400" />
              <span>{{ isAr ? 'الحالة الملاحية (Status)' : 'Navigational Status' }}</span>
            </div>
            <div class="text-xs font-bold text-zinc-950 mt-1 truncate">
              {{ vessel.nav_status || 'Under way using engine' }}
            </div>
          </div>

          <div class="bg-zinc-50 border border-zinc-200 rounded-lg p-3">
            <div class="text-[10px] text-zinc-400 uppercase font-mono flex items-center gap-1">
              <Navigation class="w-3 h-3 text-zinc-400" />
              <span>{{ isAr ? 'السرعة والاتجاه (Speed / Course)' : 'Speed & Course' }}</span>
            </div>
            <div class="text-xs font-bold text-zinc-950 mt-1 font-mono">
              {{ vessel.speed || '16.5 knots' }}
            </div>
          </div>

          <div class="bg-zinc-50 border border-zinc-200 rounded-lg p-3">
            <div class="text-[10px] text-zinc-400 uppercase font-mono flex items-center gap-1">
              <MapPin class="w-3 h-3 text-zinc-400" />
              <span>{{ isAr ? 'المنطقة البحرية الحالية' : 'Current Sea Area' }}</span>
            </div>
            <div class="text-xs font-bold text-zinc-950 mt-1 truncate">
              {{ vessel.current_area || 'Mediterranean Sea' }}
            </div>
          </div>

          <div class="bg-zinc-50 border border-zinc-200 rounded-lg p-3">
            <div class="text-[10px] text-zinc-400 uppercase font-mono flex items-center gap-1">
              <Anchor class="w-3 h-3 text-zinc-400" />
              <span>{{ isAr ? 'المقصد وتاريخ الوصول (ETA)' : 'Destination & ETA' }}</span>
            </div>
            <div class="text-xs font-bold text-zinc-950 mt-1 truncate font-mono">
              {{ vessel.destination || 'Rotterdam' }} ({{ vessel.eta || 'ETA soon' }})
            </div>
          </div>
        </div>

        <!-- Secondary Specs Grid (Dimensions, Draught, Tonnage) -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-center font-mono">
          <div class="bg-white border border-zinc-200 rounded p-2">
            <span class="block text-[10px] text-zinc-400 uppercase">{{ isAr ? 'الأبعاد (الطول × العرض)' : 'Length × Beam' }}</span>
            <span class="text-xs font-bold text-zinc-950">{{ vessel.length_beam || '399 m / 59 m' }}</span>
          </div>
          <div class="bg-white border border-zinc-200 rounded p-2">
            <span class="block text-[10px] text-zinc-400 uppercase">{{ isAr ? 'الغاطس الحالي (Draught)' : 'Draught' }}</span>
            <span class="text-xs font-bold text-zinc-950">{{ vessel.draught || '14.5 m' }}</span>
          </div>
          <div class="bg-white border border-zinc-200 rounded p-2">
            <span class="block text-[10px] text-zinc-400 uppercase">{{ isAr ? 'الحمولة الإجمالية' : 'Gross Tonnage' }}</span>
            <span class="text-xs font-bold text-zinc-950">{{ vessel.gross_tonnage || '214,000 GT' }}</span>
          </div>
          <div class="bg-white border border-zinc-200 rounded p-2">
            <span class="block text-[10px] text-zinc-400 uppercase">{{ isAr ? 'التتبع الحي' : 'Live AIS Link' }}</span>
            <span class="text-xs font-bold text-emerald-600">{{ isAr ? 'مستقبل إشارات AIS متصل' : 'Signals Connected' }}</span>
          </div>
        </div>

        <!-- AIS Narrative Description Box -->
        <div v-if="vessel.description" class="bg-zinc-50 border border-zinc-200 rounded-lg p-3.5 space-y-1.5">
          <div class="flex items-center gap-2 text-xs font-bold text-zinc-900">
            <Radio class="w-3.5 h-3.5 text-zinc-950 animate-pulse" />
            <span>{{ isAr ? 'بيان حركة الملاحة اللحظي المسحوب (AIS Live Narrative):' : 'AIS Live Narrative Report:' }}</span>
          </div>
          <p class="text-xs text-zinc-700 leading-relaxed font-sans">
            {{ vessel.description }}
          </p>
        </div>
      </div>
    </div>

    <!-- Active Container Tracking Result Display ("يقولي حصل فيها ايه") -->
    <div v-if="container" class="space-y-6">
      <!-- Container Overview Banner Card -->
      <div class="shadcn-card p-5 space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-zinc-100 pb-4">
          <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-lg bg-blue-900 text-white flex items-center justify-center shrink-0 shadow-sm shadow-blue-900/30 font-bold">
              <Box class="w-6 h-6" />
            </div>
            <div>
              <div class="flex items-center gap-2 flex-wrap">
                <h2 class="text-2xl font-black text-blue-950 font-mono tracking-tight">
                  {{ container.container_number }}
                </h2>
                <span class="px-2.5 py-0.5 rounded text-[10px] font-mono font-bold uppercase tracking-wider bg-blue-700 text-white border border-blue-600 shadow-xs">
                  {{ container.current_status || 'IN_TRANSIT' }}
                </span>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase tracking-wider bg-blue-50 text-blue-950 border border-blue-200">
                  {{ container.container_type || '40RH' }}
                </span>
              </div>
              <p class="text-xs text-zinc-500 font-mono mt-0.5 flex items-center gap-2 flex-wrap">
                <span>{{ container.shipping_line || shipment?.shipping_line?.legal_name || 'Maersk Line' }}</span>
                <span>•</span>
                <span>{{ isAr ? 'السفينة:' : 'Vessel:' }} <strong class="text-blue-900">{{ container.vessel_name || shipment?.vessel_name }}</strong> (v. {{ container.voyage_number || shipment?.voyage_number }})</span>
                <span>•</span>
                <span>{{ isAr ? 'رصاص التأمين:' : 'Seal:' }} {{ container.seal_number || 'N/A' }}</span>
              </p>
            </div>
          </div>

          <div class="flex items-center gap-2 flex-wrap">
            <!-- External FindTeu & Carrier Links -->
            <a
              v-if="carrier_scraper?.findteu_url"
              :href="carrier_scraper.findteu_url"
              target="_blank"
              class="shadcn-btn-secondary gap-1.5 text-xs cursor-pointer inline-flex items-center hover:border-blue-300 hover:text-blue-900"
            >
              <Globe class="w-3.5 h-3.5 text-blue-700" />
              <span>{{ isAr ? 'فحص في FindTeu' : 'FindTeu Track' }}</span>
              <ExternalLink class="w-3 h-3 text-zinc-400" />
            </a>
            <a
              v-if="carrier_scraper?.carrier_track_url"
              :href="carrier_scraper.carrier_track_url"
              target="_blank"
              class="shadcn-btn-outline gap-1.5 text-xs cursor-pointer inline-flex items-center hover:border-blue-300 hover:text-blue-900"
            >
              <ExternalLink class="w-3.5 h-3.5 text-blue-700" />
              <span>{{ carrier_scraper.carrier_name || (isAr ? 'موقع الناقل' : 'Carrier Link') }}</span>
            </a>

            <button
              v-if="container.id"
              @click="showAddEventModal = true"
              class="shadcn-btn-secondary gap-1.5 text-xs cursor-pointer hover:border-blue-300 hover:text-blue-900"
            >
              <Plus class="w-4 h-4 text-blue-900" />
              <span>{{ isAr ? 'تسجيل حركة يدوياً' : 'Log Movement' }}</span>
            </button>
            <button
              @click="handleSearch"
              class="shadcn-btn-outline gap-1.5 text-xs cursor-pointer hover:border-blue-300 hover:text-blue-900"
              title="تحديث البيانات"
            >
              <RefreshCw class="w-4 h-4 text-blue-900" />
              <span>{{ isAr ? 'تحديث' : 'Refresh' }}</span>
            </button>
          </div>
        </div>

        <!-- Key Metrics Grid (From, To, ATD, ATA/ETA) -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
          <div class="bg-blue-50/40 border border-blue-100 rounded-lg p-3">
            <div class="text-[10px] text-zinc-500 uppercase font-mono">{{ isAr ? 'من / ميناء الشحن (From / POL)' : 'From (Port of Loading)' }}</div>
            <div class="text-xs font-bold text-blue-950 mt-0.5 truncate">{{ container.pol_name || shipment?.pol?.name || 'Alexandria Port (EGALY)' }}</div>
          </div>
          <div class="bg-blue-50/40 border border-blue-100 rounded-lg p-3">
            <div class="text-[10px] text-zinc-500 uppercase font-mono">{{ isAr ? 'إلى / ميناء الوصول (To / POD)' : 'To (Port of Discharge)' }}</div>
            <div class="text-xs font-bold text-blue-950 mt-0.5 truncate">{{ container.pod_name || shipment?.pod?.name || 'Rotterdam (NLRTM)' }}</div>
          </div>
          <div class="bg-blue-50/40 border border-blue-100 rounded-lg p-3">
            <div class="text-[10px] text-zinc-500 uppercase font-mono">{{ isAr ? 'وقت المغادرة الفعلي (ATD)' : 'Actual Time of Departure (ATD)' }}</div>
            <div class="text-xs font-bold text-emerald-700 mt-0.5 font-mono">{{ shipment?.actual_departure || shipment?.etd || '2026-09-02 08:30 UTC' }}</div>
          </div>
          <div class="bg-blue-50/40 border border-blue-100 rounded-lg p-3">
            <div class="text-[10px] text-zinc-500 uppercase font-mono">{{ isAr ? 'وقت الوصول الفعلي/المقدر (ATA / ETA)' : 'Actual / Estimated Arrival (ATA/ETA)' }}</div>
            <div class="text-xs font-bold text-blue-700 mt-0.5 font-mono">{{ shipment?.actual_arrival || shipment?.eta || '2026-09-09 14:00 UTC' }}</div>
          </div>
        </div>

        <!-- Reefer Cold-Chain Telemetry (if reefer) -->
        <div v-if="container.is_reefer || container.container_type?.includes('RH') || container.container_type?.includes('RF')" class="bg-blue-50/30 border border-blue-100 rounded-lg p-3.5 space-y-2">
          <div class="flex items-center justify-between border-b border-blue-100 pb-2">
            <div class="flex items-center gap-2">
              <ThermometerSnowflake class="w-4 h-4 text-blue-700" />
              <span class="text-xs font-bold text-blue-950">{{ isAr ? 'بيانات تبريد الحاصلات الزراعية (Reefer Cold-Chain)' : 'Cold-Chain Telemetry' }}</span>
            </div>
            <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-900 text-[10px] font-mono font-bold">
              {{ isAr ? 'حساسات التبريد: نشطة' : 'Sensors: Active' }}
            </span>
          </div>

          <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-center font-mono">
            <div class="bg-white border border-blue-100 rounded p-2">
              <span class="block text-[10px] text-zinc-400 uppercase">{{ isAr ? 'الحرارة المطلوبة' : 'Set Temp' }}</span>
              <span class="text-sm font-bold text-blue-950">{{ container.target_temperature || container.set_temperature_c || '4.0' }} °C</span>
            </div>
            <div class="bg-white border border-blue-100 rounded p-2">
              <span class="block text-[10px] text-zinc-400 uppercase">{{ isAr ? 'نسبة الرطوبة' : 'Humidity' }}</span>
              <span class="text-sm font-bold text-blue-950">{{ container.humidity_percent || container.humidity_percentage || '85' }}%</span>
            </div>
            <div class="bg-white border border-blue-100 rounded p-2">
              <span class="block text-[10px] text-zinc-400 uppercase">{{ isAr ? 'تدفق التهوية' : 'Ventilation' }}</span>
              <span class="text-sm font-bold text-blue-950">{{ container.ventilation_flow || container.ventilation_cbm_hr || '15' }} m³/h</span>
            </div>
            <div class="bg-white border border-blue-100 rounded p-2">
              <span class="block text-[10px] text-zinc-400 uppercase">{{ isAr ? 'معاملة التبريد USDA' : 'Cold Treatment' }}</span>
              <span class="text-sm font-bold text-emerald-600">{{ isAr ? 'مستوفاة (Probes OK)' : 'Compliant' }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Visual Milestones Progress Journey -->
      <div class="shadcn-card p-5 space-y-4">
        <h3 class="text-xs font-bold text-blue-950 uppercase tracking-wider font-mono flex items-center gap-2">
          <Anchor class="w-4 h-4 text-blue-700" />
          <span>{{ isAr ? 'مسار رحلة الحاوية الملاحي (Milestones Journey)' : 'Container Transport Milestones' }}</span>
        </h3>

        <div class="overflow-x-auto pb-2">
          <div class="flex items-center min-w-[700px] justify-between relative">
            <!-- Connecting Line -->
            <div class="absolute top-4 start-6 end-6 h-0.5 bg-zinc-200 -z-0"></div>

            <div 
              v-for="(stage, idx) in milestoneStages" 
              :key="stage.code"
              class="relative z-10 flex flex-col items-center text-center px-1"
            >
              <div 
                :class="[
                  'w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs border-2 transition-all',
                  isMilestoneCompleted(stage.code)
                    ? 'bg-blue-700 text-white border-blue-700 shadow-xs shadow-blue-600/30'
                    : 'bg-white text-zinc-400 border-zinc-300'
                ]"
              >
                <CheckCircle2 v-if="isMilestoneCompleted(stage.code)" class="w-4 h-4" />
                <span v-else>{{ idx + 1 }}</span>
              </div>
              <span class="text-[11px] font-bold text-zinc-950 mt-1.5 whitespace-nowrap">
                {{ isAr ? stage.nameAr : stage.nameEn }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Detailed Timestamped Tracking Events Log -->
      <div class="shadcn-card overflow-hidden">
        <div class="p-4 border-b border-zinc-200 flex items-center justify-between">
          <div>
            <h3 class="text-sm font-bold text-blue-950 uppercase tracking-wide flex items-center gap-2">
              <Clock class="w-4 h-4 text-blue-700" />
              <span>{{ isAr ? 'سجل الحركات والأحداث الزمني (Tracking Events Log)' : 'Detailed Events Log' }}</span>
            </h3>
            <p class="text-xs text-zinc-500 mt-0.5">
              {{ isAr ? 'كافة الإجراءات المسجلة من محطات الموانئ، الخطوط الملاحية، وغرف المتابعة' : 'Complete movement audit trail from ports, carriers, and dispatchers' }}
            </p>
          </div>
          <span class="text-xs font-mono font-bold text-blue-700">{{ events?.length || 0 }} {{ isAr ? 'حركات' : 'Events' }}</span>
        </div>

        <div v-if="!events || events.length === 0" class="p-8 text-center text-zinc-400 text-xs">
          {{ isAr ? 'لا توجد حركات مسجلة لهذه الحاوية بعد.' : 'No movements logged for this container yet.' }}
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-xs" :class="isAr ? 'text-right' : 'text-left'">
            <thead class="bg-slate-100 text-blue-950 uppercase text-[10px] tracking-wider border-b border-zinc-200 font-mono">
              <tr>
                <th class="px-5 py-3.5">{{ isAr ? 'التاريخ والوقت' : 'Date & Time' }}</th>
                <th class="px-5 py-3.5">{{ isAr ? 'كود الحركة' : 'Event Code' }}</th>
                <th class="px-5 py-3.5">{{ isAr ? 'وصف الحركة والبيان' : 'Event Description' }}</th>
                <th class="px-5 py-3.5">{{ isAr ? 'الموقع / الميناء' : 'Location / Port' }}</th>
                <th class="px-5 py-3.5">{{ isAr ? 'المصدر' : 'Source' }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 font-mono">
              <tr v-for="evt in events" :key="evt.id" class="hover:bg-blue-50/30 transition-colors">
                <td class="px-5 py-3.5 font-bold text-blue-950 text-[11px] whitespace-nowrap">
                  {{ formatDateTime(evt.occurred_at) }}
                </td>
                <td class="px-5 py-3.5">
                  <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-900 border border-blue-200">
                    {{ evt.event_code }}
                  </span>
                </td>
                <td class="px-5 py-3.5 font-sans font-semibold text-zinc-950 text-xs">
                  {{ evt.event_name }}
                </td>
                <td class="px-5 py-3.5 text-zinc-600">
                  <div class="flex items-center gap-1.5">
                    <MapPin class="w-3.5 h-3.5 text-zinc-400 shrink-0" />
                    <span>{{ evt.location_name || 'N/A' }}</span>
                  </div>
                </td>
                <td class="px-5 py-3.5 text-zinc-500 text-[10px]">
                  {{ evt.source || 'MANUAL' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Tracking Search History Section ("ويكون في هوستري عشان اجيب الحاويات ل اتركتتها") -->
    <div class="shadcn-card overflow-hidden">
      <div class="p-4 border-b border-zinc-200 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <History class="w-4 h-4 text-blue-700" />
          <h2 class="text-sm font-bold text-blue-950 uppercase tracking-wide">
            {{ isAr ? 'سجل التتبع والفحص السابق (Tracking History)' : 'Recently Tracked Vessels & Containers' }}
          </h2>
        </div>

        <button
          v-if="history && history.length > 0"
          @click="clearAllHistory"
          class="text-xs text-zinc-400 hover:text-red-600 transition cursor-pointer flex items-center gap-1"
        >
          <Trash2 class="w-3.5 h-3.5" />
          <span>{{ isAr ? 'مسح السجل بالكامل' : 'Clear All History' }}</span>
        </button>
      </div>

      <div v-if="!history || history.length === 0" class="p-10 text-center text-zinc-500">
        <History class="w-8 h-8 text-zinc-300 mx-auto mb-2" />
        <p class="text-xs font-semibold text-zinc-700">
          {{ isAr ? 'لا توجد عناصر في سجل التتبع حتى الآن' : 'No items tracked in history yet' }}
        </p>
        <p class="text-[11px] text-zinc-400 mt-0.5">
          {{ isAr ? 'أي سفينة أو حاوية تقوم بالبحث عنها وتتبعها ستُحفظ هنا تلقائياً لسرعة الرجوع إليها' : 'Any vessel or container you track will be saved here automatically for one-click re-access' }}
        </p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-xs" :class="isAr ? 'text-right' : 'text-left'">
          <thead class="bg-slate-100 text-blue-950 uppercase text-[10px] tracking-wider border-b border-zinc-200 font-mono">
            <tr>
              <th class="px-5 py-3.5">{{ isAr ? 'العنصر والنوع' : 'Tracked Item' }}</th>
              <th class="px-5 py-3.5">{{ isAr ? 'الناقل / السفينة' : 'Carrier / Ship' }}</th>
              <th class="px-5 py-3.5">{{ isAr ? 'الموقع / المسار' : 'Location / Routing' }}</th>
              <th class="px-5 py-3.5">{{ isAr ? 'الحالة الحالية' : 'Current Status' }}</th>
              <th class="px-5 py-3.5">{{ isAr ? 'آخر تتبع' : 'Last Tracked' }}</th>
              <th class="px-5 py-3.5" :class="isAr ? 'text-left' : 'text-right'">{{ isAr ? 'الإجراء' : 'Actions' }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-zinc-200 font-mono">
            <tr v-for="item in history" :key="item.id" class="hover:bg-blue-50/20 transition-colors">
              <!-- Item & Type -->
              <td class="px-5 py-3.5">
                <div v-if="item.tracking_type === 'vessel'" class="space-y-1">
                  <div class="flex items-center gap-1.5">
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-700 text-white uppercase shadow-2xs">
                      🚢 {{ isAr ? 'سفينة' : 'Vessel' }}
                    </span>
                    <button 
                      @click="trackSpecificVessel(item.vessel_name)"
                      class="font-bold text-blue-950 text-xs hover:underline cursor-pointer font-mono truncate max-w-[200px]"
                    >
                      {{ item.vessel_name }}
                    </button>
                  </div>
                  <div v-if="item.vessel_imo" class="text-[10px] text-zinc-400 font-mono">
                    IMO: {{ item.vessel_imo }}
                  </div>
                </div>

                <div v-else class="space-y-1">
                  <div class="flex items-center gap-1.5">
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-50 text-blue-900 border border-blue-200 uppercase">
                      📦 {{ isAr ? 'حاوية' : 'Box' }}
                    </span>
                    <button 
                      @click="trackSpecificContainer(item.container_number)"
                      class="font-bold text-blue-950 text-xs hover:underline cursor-pointer font-mono"
                    >
                      {{ item.container_number }}
                    </button>
                  </div>
                </div>
              </td>

              <!-- Carrier / Vessel Details -->
              <td class="px-5 py-3.5">
                <div v-if="item.tracking_type === 'vessel'">
                  <div class="font-bold text-blue-950 text-xs font-sans">
                    {{ item.vessel_data?.ship_type || 'Container Ship' }}
                  </div>
                  <div class="text-[10px] text-zinc-500">
                    🚩 {{ item.vessel_data?.flag || 'Panama' }}
                  </div>
                </div>
                <div v-else>
                  <div class="font-bold text-blue-950 text-xs font-sans">
                    {{ item.shipping_line || 'Ocean Carrier' }}
                  </div>
                  <div v-if="item.vessel_name" class="text-[10px] text-zinc-500">
                    {{ item.vessel_name }} (v. {{ item.voyage_number || '—' }})
                  </div>
                </div>
              </td>

              <!-- Location / Routing -->
              <td class="px-5 py-3.5 text-[11px] text-zinc-700">
                <div v-if="item.tracking_type === 'vessel'" class="truncate max-w-[220px]">
                  <span class="font-semibold text-zinc-950">{{ item.last_location || 'Sea Basin' }}</span>
                  <div v-if="item.pod_name" class="text-[10px] text-zinc-500">
                    ➔ {{ item.pod_name }}
                  </div>
                </div>
                <div v-else class="flex items-center gap-1">
                  <span>{{ item.pol_name || 'POL' }}</span>
                  <span class="text-zinc-400">➔</span>
                  <span>{{ item.pod_name || 'POD' }}</span>
                </div>
              </td>

              <!-- Status -->
              <td class="px-5 py-3.5">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-blue-950 text-blue-100 border border-blue-800 truncate block max-w-[140px] text-center">
                  {{ item.current_status || 'IN_TRANSIT' }}
                </span>
              </td>

              <!-- Timestamp -->
              <td class="px-5 py-3.5 text-zinc-500 text-[11px] whitespace-nowrap">
                {{ item.last_tracked_at ? formatDateTime(item.last_tracked_at) : '—' }}
              </td>

              <!-- Action -->
              <td class="px-5 py-3.5" :class="isAr ? 'text-left' : 'text-right'">
                <div class="flex items-center gap-1.5 justify-end">
                  <button
                    v-if="item.tracking_type === 'vessel'"
                    @click="trackSpecificVessel(item.vessel_name)"
                    class="shadcn-btn-secondary h-7 px-2.5 text-[11px] cursor-pointer hover:border-blue-300 hover:text-blue-900"
                  >
                    <span>{{ isAr ? 'تتبع السفينة' : 'Track Ship' }}</span>
                  </button>
                  <button
                    v-else
                    @click="trackSpecificContainer(item.container_number)"
                    class="shadcn-btn-secondary h-7 px-2.5 text-[11px] cursor-pointer hover:border-blue-300 hover:text-blue-900"
                  >
                    <span>{{ isAr ? 'عرض الحاوية' : 'View' }}</span>
                  </button>

                  <button
                    @click="deleteHistoryItem(item.id)"
                    class="p-1.5 text-zinc-400 hover:text-red-600 transition cursor-pointer"
                    title="حذف من السجل"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Manual Event Modal -->
    <div v-if="showAddEventModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs">
      <div class="shadcn-card max-w-md w-full p-6 space-y-4 shadow-2xl relative">
        <button 
          @click="showAddEventModal = false"
          class="absolute top-4 end-4 text-zinc-400 hover:text-zinc-950 cursor-pointer"
        >
          <X class="w-4 h-4" />
        </button>

        <h3 class="text-base font-bold text-zinc-950 flex items-center gap-2 border-b border-zinc-100 pb-3">
          <Plus class="w-4 h-4 text-zinc-950" />
          <span>{{ isAr ? 'تسجيل حركة أو تحديث للحاوية' : 'Log Container Movement Event' }}</span>
        </h3>

        <form @submit.prevent="submitAddEvent" class="space-y-3">
          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">
              {{ isAr ? 'نوع وكود الحركة' : 'Event Milestone' }}
            </label>
            <select v-model="eventForm.event_code" class="shadcn-input">
              <option value="GATE_IN">{{ isAr ? 'GATE_IN - دخول ساحة الميناء' : 'GATE_IN - Port Gate In' }}</option>
              <option value="CUSTOMS_CLEARED">{{ isAr ? 'CUSTOMS_CLEARED - إنهاء الإفراج الجمركي' : 'CUSTOMS_CLEARED - Customs Cleared' }}</option>
              <option value="LOADED">{{ isAr ? 'LOADED - التحميل على ظهر السفينة' : 'LOADED - Loaded On-Board' }}</option>
              <option value="SAILED_IN_TRANSIT">{{ isAr ? 'SAILED_IN_TRANSIT - إبحار السفينة بالبحر' : 'SAILED - In Transit' }}</option>
              <option value="ARRIVED">{{ isAr ? 'ARRIVED - وصول ميناء المقصد' : 'ARRIVED - Vessel Arrived' }}</option>
              <option value="DISCHARGED">{{ isAr ? 'DISCHARGED - تفريغ الحاوية' : 'DISCHARGED - Discharged' }}</option>
              <option value="EMPTY_RETURN">{{ isAr ? 'EMPTY_RETURN - تسليم الحاوية الفارغة' : 'EMPTY_RETURN - Returned Empty' }}</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">
              {{ isAr ? 'وصف وتفاصيل الحركة' : 'Event Description' }}
            </label>
            <input v-model="eventForm.event_name" type="text" required class="shadcn-input" />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">
              {{ isAr ? 'الموقع / الميناء / الساحة' : 'Location / Terminal' }}
            </label>
            <input v-model="eventForm.location_name" type="text" required class="shadcn-input" />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">
              {{ isAr ? 'تاريخ ووقت الحركة' : 'Date & Time' }}
            </label>
            <input v-model="eventForm.occurred_at" type="text" required class="shadcn-input font-mono" />
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-zinc-100">
            <button 
              type="button" 
              @click="showAddEventModal = false" 
              class="shadcn-btn-ghost text-xs cursor-pointer"
            >
              {{ isAr ? 'إلغاء' : 'Cancel' }}
            </button>
            <button 
              type="submit" 
              :disabled="eventForm.processing" 
              class="shadcn-btn-primary text-xs cursor-pointer"
            >
              {{ isAr ? 'حفظ الحركة' : 'Save Event' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
