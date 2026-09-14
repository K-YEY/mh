<script setup>
import { ref, computed } from 'vue'
import { useI18n } from '@/Composables/useI18n'
import { 
  CheckCircle2, 
  Clock, 
  Ship, 
  ExternalLink, 
  RefreshCw, 
  ShieldCheck, 
  AlertTriangle, 
  ArrowRight, 
  ArrowLeft,
  Boxes,
  Anchor,
  Truck,
  Building2,
  Calendar,
  Sparkles,
  Layers,
  MapPin,
  Check
} from 'lucide-vue-next'

const props = defineProps({
  containerNumber: {
    type: String,
    default: '',
  },
  isoValidation: {
    type: Object,
    default: null,
  },
  detectedCarrier: {
    type: Object,
    default: null,
  },
  carrierDeepLink: {
    type: String,
    default: null,
  },
  milestones: {
    type: Array,
    default: () => [],
  },
  localContainer: {
    type: Object,
    default: null,
  },
  canSync: {
    type: Boolean,
    default: true,
  }
})

const emit = defineEmits(['synced'])

const { isAr, dir, formatDateTime } = useI18n()

const isSyncing = ref(false)
const localMilestones = ref(props.milestones)
const syncFeedback = ref(null)

// Milestone Phase Icons
const getMilestoneIcon = (code) => {
  switch (code) {
    case 'GATE_IN_EMPTY':
      return Boxes
    case 'GATE_IN_FULL':
      return Truck
    case 'LOADED_VESSEL':
      return Anchor
    case 'DEPARTED':
      return Ship
    case 'TRANSSHIPMENT':
      return Layers
    case 'ARRIVED':
      return MapPin
    case 'DISCHARGED':
      return Boxes
    case 'GATE_OUT_CONSIGNEE':
      return Truck
    case 'EMPTY_RETURNED':
      return CheckCircle2
    default:
      return MapPin
  }
}

// Trigger Live Sync via API Endpoint
const triggerLiveSync = async () => {
  if (isSyncing.value) return
  isSyncing.value = true
  syncFeedback.value = null

  try {
    const res = await fetch(`/operations/tracking/api/${encodeURIComponent(props.containerNumber)}/sync`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      }
    })

    if (res.ok) {
      const data = await res.json()
      if (data.data?.milestones) {
        localMilestones.value = data.data.milestones
        emit('synced', data.data)
      }
      syncFeedback.value = isAr.value ? 'تمت مزامنة مراحل الحاوية بنجاح' : 'Milestones synchronized successfully'
    } else {
      syncFeedback.value = isAr.value ? 'تعذر الاتصال بالمزود الخارجي' : 'API sync failed'
    }
  } catch (e) {
    console.error('Tracking sync error:', e)
    syncFeedback.value = isAr.value ? 'حدث خطأ أثناء المزامنة' : 'Sync error occurred'
  } finally {
    isSyncing.value = false
    setTimeout(() => {
      syncFeedback.value = null
    }, 4000)
  }
}
</script>

<template>
  <div v-if="!containerNumber && !isoValidation && !localContainer" class="shadcn-card p-12 text-center space-y-4 bg-white/80 backdrop-blur-md border border-white/60">
    <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 mx-auto flex items-center justify-center shadow-xs">
      <Boxes class="w-7 h-7" />
    </div>
    <div class="space-y-1">
      <h3 class="text-base font-bold text-slate-900">{{ isAr ? 'أدخل رقم الحاوية لبدء التتبع الذكي' : 'Enter Container Number for Hybrid Tracking' }}</h3>
      <p class="text-xs text-slate-500 max-w-md mx-auto">
        {{ isAr ? 'يدعم النظام فحص معيار ISO 6346 آلياً، واكتشاف الخط الملاحي المالك فوراً، وتوليد روابط التتبع المباشرة، ومزامنة سجل الأحداث والمحطات.' : 'Supports instant ISO 6346 validation, auto carrier detection, direct carrier deep-links, and milestone lifecycle synchronization.' }}
      </p>
    </div>
  </div>

  <div v-else class="space-y-6" :dir="dir">
    <!-- Top Hybrid Carrier & ISO 6346 Intelligence Header -->
    <div class="shadcn-card p-6 sm:p-7 space-y-5 bg-gradient-to-r from-white/95 via-white/90 to-white/95 backdrop-blur-2xl">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <!-- Container Number & ISO Diagnostics -->
        <div class="space-y-2">
          <div class="flex flex-wrap items-center gap-2.5">
            <span class="text-2xl sm:text-3xl font-black font-mono tracking-wider text-slate-900">
              {{ isoValidation?.formatted || containerNumber }}
            </span>

            <!-- ISO 6346 Badge -->
            <span 
              v-if="isoValidation"
              :class="[
                isoValidation.is_valid 
                  ? 'bg-emerald-500/10 text-emerald-800 border-emerald-500/25' 
                  : 'bg-amber-500/10 text-amber-800 border-amber-500/25',
                'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono font-bold border backdrop-blur-sm'
              ]"
              :title="isoValidation.error || 'ISO 6346 standard checksum verified'"
            >
              <component :is="isoValidation.is_valid ? ShieldCheck : AlertTriangle" class="w-3.5 h-3.5" />
              <span>{{ isoValidation.is_valid ? (isAr ? 'معيار ISO 6346 سليم' : 'ISO 6346 Valid') : (isAr ? 'تدقيق Check Digit غير متطابق' : 'Invalid Check Digit') }}</span>
            </span>

            <!-- Reefer Indicator -->
            <span 
              v-if="localContainer?.is_reefer"
              class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-mono font-bold bg-sky-500/10 text-sky-700 border border-sky-500/20"
            >
              <Boxes class="w-3 h-3" />
              <span>{{ isAr ? 'حاوية مبردة (Reefer)' : 'Reefer Unit' }}</span>
            </span>
          </div>

          <!-- ISO Detailed Diagnostic Breakdown -->
          <div v-if="isoValidation?.owner_code" class="flex flex-wrap items-center gap-2 text-xs font-mono text-slate-500">
            <span class="px-2 py-0.5 rounded-md bg-[#f2f4f7] border border-slate-200 text-slate-700 font-semibold">
              Owner: <strong>{{ isoValidation.owner_code }}</strong>
            </span>
            <span class="px-2 py-0.5 rounded-md bg-[#f2f4f7] border border-slate-200 text-slate-700 font-semibold">
              Cat: <strong>{{ isoValidation.category }}</strong> (Freight Container)
            </span>
            <span class="px-2 py-0.5 rounded-md bg-[#f2f4f7] border border-slate-200 text-slate-700 font-semibold">
              Serial: <strong>{{ isoValidation.serial }}</strong>
            </span>
            <span 
              class="px-2 py-0.5 rounded-md border font-semibold"
              :class="isoValidation.is_valid ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-rose-50 border-rose-200 text-rose-700'"
            >
              Check Digit: <strong>{{ isoValidation.check_digit }}</strong>
              <span v-if="!isoValidation.is_valid" class="text-[10px]"> (Calc: {{ isoValidation.calculated_check_digit }})</span>
            </span>
          </div>
        </div>

        <!-- Detected Carrier & Direct Free Deep-Link Action Button -->
        <div class="flex flex-wrap items-center gap-3">
          <!-- Official Carrier Deep-Link Button (Free Instant Tracking) -->
          <a
            v-if="carrierDeepLink"
            :href="carrierDeepLink"
            target="_blank"
            rel="noopener noreferrer"
            class="shadcn-btn-primary gap-2 text-xs sm:text-sm py-2.5 px-4 shadow-md hover:shadow-lg transition-all"
            :title="isAr ? 'تتبع مباشر على موقع الخط الملاحي الرسمي بدون تكلفة' : 'Direct free tracking on official shipping line portal'"
          >
            <span>{{ isAr ? 'تتبع فوري عبر الخط الملاحي' : 'Track on Carrier Website' }}</span>
            <ExternalLink class="w-4 h-4" />
          </a>

          <!-- Live API Milestones Sync Button -->
          <button
            v-if="canSync"
            type="button"
            @click="triggerLiveSync"
            :disabled="isSyncing"
            class="shadcn-btn-secondary gap-2 text-xs py-2.5 px-3.5 cursor-pointer shadow-xs disabled:opacity-60"
            :title="isAr ? 'تحديث ومزامنة المراحل مع مزود التتبع' : 'Synchronize milestones from tracking API'"
          >
            <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': isSyncing }" />
            <span>{{ isSyncing ? (isAr ? 'جاري المزامنة...' : 'Syncing...') : (isAr ? 'تحديث المراحل' : 'Sync Milestones') }}</span>
          </button>
        </div>
      </div>

      <!-- Carrier Detection Banner & Sync Feedback Alert -->
      <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-2 text-slate-700">
          <Ship class="w-4 h-4 text-blue-600 shrink-0" />
          <span>{{ isAr ? 'الخط الملاحي المالك للبادئة:' : 'Detected Ocean Carrier:' }}</span>
          <strong class="font-bold text-slate-900 bg-slate-100 px-2.5 py-0.5 rounded-lg border border-slate-200">
            {{ detectedCarrier?.name || localContainer?.master_shipment?.shipping_line?.legal_name || 'Global Marine Line' }}
          </strong>
          <span v-if="detectedCarrier?.scac" class="font-mono text-[11px] text-slate-500">
            (SCAC: {{ detectedCarrier.scac }})
          </span>
        </div>

        <div v-if="syncFeedback" class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200 animate-fade-in flex items-center gap-1.5">
          <Check class="w-3.5 h-3.5 text-emerald-600" />
          <span>{{ syncFeedback }}</span>
        </div>
      </div>
    </div>

    <!-- Milestones Chronological Life-Cycle Timeline Card -->
    <div class="shadcn-card overflow-hidden">
      <!-- Card Header -->
      <div class="p-5 sm:p-6 border-b border-black/[0.04] bg-white/40 backdrop-blur-md flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-xs">
            <Clock class="w-4 h-4" />
          </div>
          <div>
            <h3 class="text-base font-bold text-slate-900">
              {{ isAr ? 'المسار الزمني والمحطات اللوجستية (Milestones Timeline)' : 'Container Transit Milestones Timeline' }}
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">
              {{ isAr ? 'تتبع آلي لكافة مراحل الحاوية من سحب الفارغ حتى الإرجاع' : 'Chronological movement tracking from empty gate-in to return' }}
            </p>
          </div>
        </div>

        <span class="text-xs font-mono font-bold text-slate-500 bg-[#f2f4f7] px-3 py-1 rounded-full border border-slate-200">
          {{ localMilestones.length }} {{ isAr ? 'محطة مسجلة' : 'Milestones' }}
        </span>
      </div>

      <!-- Timeline List -->
      <div class="p-6 sm:p-8">
        <div class="relative">
          <!-- Continuous Connecting Line -->
          <div 
            class="absolute top-4 bottom-4 w-0.5 bg-gradient-to-b from-slate-900 via-sky-400 to-slate-200"
            :class="isAr ? 'right-5.5' : 'left-5.5'"
          ></div>

          <!-- Milestones Items -->
          <div class="space-y-6">
            <div 
              v-for="(item, idx) in localMilestones" 
              :key="item.id || idx"
              class="relative flex items-start gap-4 sm:gap-6 group"
            >
              <!-- Milestone Icon / Node Pin -->
              <div 
                class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 z-10 transition-all shadow-sm group-hover:scale-105"
                :class="[
                  item.is_completed 
                    ? 'bg-slate-900 text-white shadow-md' 
                    : 'bg-white text-slate-400 border-2 border-dashed border-slate-300'
                ]"
              >
                <component :is="getMilestoneIcon(item.milestone_code)" class="w-5 h-5" />
              </div>

              <!-- Milestone Details Card -->
              <div 
                class="flex-1 p-4 sm:p-5 rounded-2xl border transition-all"
                :class="[
                  item.is_completed 
                    ? 'bg-white/80 border-slate-200/80 shadow-xs hover:border-slate-300' 
                    : 'bg-slate-50/70 border-dashed border-slate-200 text-slate-500'
                ]"
              >
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                  <!-- Title & Location -->
                  <div class="space-y-1">
                    <div class="flex items-center gap-2">
                      <span class="text-sm font-bold text-slate-900">
                        {{ isAr && item.milestone_name_ar ? item.milestone_name_ar : item.milestone_name }}
                      </span>

                      <!-- Status Tag -->
                      <span 
                        class="px-2 py-0.5 rounded-full text-[9px] font-mono font-bold uppercase border"
                        :class="[
                          item.is_completed 
                            ? 'bg-emerald-50 text-emerald-700 border-emerald-200' 
                            : 'bg-amber-50 text-amber-700 border-amber-200'
                        ]"
                      >
                        {{ item.is_completed ? (isAr ? 'مكتمل' : 'Completed') : (isAr ? 'متوقع' : 'Estimated') }}
                      </span>
                    </div>

                    <!-- Port / Location -->
                    <div v-if="item.location_name" class="flex items-center gap-1.5 text-xs text-slate-600 font-medium">
                      <MapPin class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                      <span>{{ item.location_name }}</span>
                    </div>

                    <!-- Vessel / Voyage if applicable -->
                    <div v-if="item.vessel_name" class="flex items-center gap-1.5 text-xs text-slate-500 font-mono">
                      <Ship class="w-3.5 h-3.5 text-blue-500 shrink-0" />
                      <span>{{ item.vessel_name }} (v. {{ item.voyage_number || '1' }})</span>
                    </div>
                  </div>

                  <!-- Occurred Date & Time -->
                  <div class="sm:text-end shrink-0 font-mono">
                    <div class="text-xs font-bold text-slate-800 flex items-center gap-1.5 sm:justify-end">
                      <Calendar class="w-3.5 h-3.5 text-slate-400" />
                      <span>{{ formatDateTime(item.occurred_at) }}</span>
                    </div>
                    <div class="text-[10px] text-slate-400 mt-0.5">
                      Source: {{ item.source || 'SYSTEM' }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Empty Milestones Fallback -->
            <div v-if="localMilestones.length === 0" class="py-8 text-center text-slate-400">
              <Boxes class="w-8 h-8 mx-auto mb-2 text-slate-300" />
              <p class="text-sm font-medium">{{ isAr ? 'لا توجد مراحل مسجلة حالياً لهذه الحاوية' : 'No milestone records found for this container' }}</p>
              <button 
                type="button" 
                @click="triggerLiveSync" 
                class="mt-3 shadcn-btn-primary text-xs"
              >
                {{ isAr ? 'مزامنة وتوليد المراحل الآن' : 'Sync Milestones Now' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
