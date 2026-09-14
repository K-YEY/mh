<script setup>
import { Link } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { 
  Ship, 
  Boxes, 
  TrendingUp, 
  DollarSign, 
  Plus, 
  ArrowUpRight, 
  Anchor,
  ChevronRight,
  ChevronLeft,
  FileCheck2,
  Sparkles,
  ThermometerSnowflake,
  ShieldCheck,
  Scale,
  Calendar,
  Layers,
  ArrowRight,
  ArrowLeft
} from 'lucide-vue-next'

const { isAr, dir, formatDateTime } = useI18n()

const props = defineProps({
  metrics: Object,
  recent_shipments: Array,
})
</script>

<template>
  <div class="space-y-8 animate-scale-in">
    <!-- Top Hero Banner (Liquid Frosted Glass with Atmospheric Sky Glow) -->
    <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-r from-white/95 via-white/85 to-white/90 backdrop-blur-2xl border border-white/90 p-6 sm:p-8 shadow-[0_12px_40px_rgba(0,0,0,0.03)]">
      <!-- Faint decorative concentric arcs in background -->
      <div class="pointer-events-none absolute -end-20 -top-20 w-80 h-80 rounded-full border border-sky-200/40 opacity-70"></div>
      <div class="pointer-events-none absolute -end-32 -top-32 w-104 h-104 rounded-full border border-sky-200/30 opacity-60"></div>
      <div class="pointer-events-none absolute -end-44 -top-44 w-128 h-128 rounded-full border border-sky-200/20 opacity-40"></div>

      <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
        <!-- Title & Subtitle with Status Badges -->
        <div class="space-y-3">
          <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-mono font-bold bg-slate-900 text-white shadow-xs">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
              <span>{{ isAr ? 'نظام التشغيل المباشر v2.4' : 'LIVE TELEMETRY v2.4' }}</span>
            </span>

            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-medium bg-sky-50 text-sky-700 border border-sky-200/80">
              <ThermometerSnowflake class="w-3.5 h-3.5 text-sky-500" />
              <span>{{ isAr ? 'سلاسل التبريد USDA نشطة' : 'USDA Cold Treatment Active' }}</span>
            </span>

            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/80">
              <ShieldCheck class="w-3.5 h-3.5 text-emerald-500" />
              <span>{{ isAr ? 'قيود متوازنة 100%' : '100% Zero-Sum Balanced' }}</span>
            </span>
          </div>

          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-snug">
              {{ isAr ? 'لوحة العمليات والرقابة اللوجستية والمالية' : 'Operations & Logistics Command Center' }}
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 font-normal mt-1 leading-relaxed max-w-2xl">
              {{ isAr 
                ? 'إدارة متكاملة لحجوزات الشحن البحري الزراعي، تتبع درجات حرارة الحاويات المبردة، وتوليد القيود المحاسبية ومتابعة الربحية لحظياً.' 
                : 'Centralized telemetry for citrus reefer shipments, automated double-entry journal postings, and real-time voyage profitability.' 
              }}
            </p>
          </div>
        </div>

        <!-- Quick Action Obsidian & Neutral Pill Buttons -->
        <div class="flex items-center gap-2.5 shrink-0">
          <Link
            :href="route('master-shipments.create')"
            class="shadcn-btn-primary gap-2 shadow-md hover:shadow-lg cursor-pointer text-xs sm:text-sm"
          >
            <Plus class="w-4 h-4 font-bold" />
            <span>{{ isAr ? 'حجز شحنة جديدة' : 'New Ocean Booking' }}</span>
          </Link>

          <Link
            :href="route('tariffs.index')"
            class="shadcn-btn-secondary gap-2 cursor-pointer text-xs sm:text-sm shadow-xs"
          >
            <Anchor class="w-4 h-4 text-slate-700" />
            <span>{{ isAr ? 'مستكشف النولون' : 'Rate Finder' }}</span>
          </Link>
        </div>
      </div>
    </div>

    <!-- Liquid Frosted Metric Cards Grid (5 Key Performance Indicators) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4.5">
      <!-- Active Shipments Card -->
      <div class="shadcn-card p-5 space-y-3 relative overflow-hidden group hover:-translate-y-0.5 transition-all">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
            {{ isAr ? 'الحجوزات النشطة' : 'Active Voyages' }}
          </span>
          <div class="w-9 h-9 rounded-2xl bg-blue-500/10 text-blue-600 flex items-center justify-center transition-transform group-hover:scale-110 shadow-xs">
            <Ship class="w-4.5 h-4.5" />
          </div>
        </div>
        <div>
          <div class="text-3xl font-extrabold text-slate-900 font-mono tracking-tight">{{ metrics.active_shipments || 0 }}</div>
          <p class="text-[11px] text-slate-500 mt-1.5 flex items-center gap-1 font-medium">
            <ArrowUpRight class="w-3.5 h-3.5 text-blue-600" />
            <span>{{ isAr ? 'رحلات ماستر FCL / LCL' : 'Master FCL/LCL Voyages' }}</span>
          </p>
        </div>
      </div>

      <!-- Active Containers Card -->
      <div class="shadcn-card p-5 space-y-3 relative overflow-hidden group hover:-translate-y-0.5 transition-all">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
            {{ isAr ? 'الحاويات المبردة' : 'Active Reefers' }}
          </span>
          <div class="w-9 h-9 rounded-2xl bg-sky-500/10 text-sky-600 flex items-center justify-center transition-transform group-hover:scale-110 shadow-xs">
            <Boxes class="w-4.5 h-4.5" />
          </div>
        </div>
        <div>
          <div class="text-3xl font-extrabold text-slate-900 font-mono tracking-tight">{{ metrics.active_containers || 0 }}</div>
          <p class="text-[11px] text-slate-500 mt-1.5 flex items-center gap-1 font-medium">
            <ThermometerSnowflake class="w-3.5 h-3.5 text-sky-600" />
            <span>{{ isAr ? 'تتبع سلاسل التبريد المباشر' : 'Reefer Telemetry Active' }}</span>
          </p>
        </div>
      </div>

      <!-- Pending Phyto Documents Card -->
      <div class="shadcn-card p-5 space-y-3 relative overflow-hidden group hover:-translate-y-0.5 transition-all">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
            {{ isAr ? 'وثائق قيد المراجعة' : 'Pending Vault Docs' }}
          </span>
          <div class="w-9 h-9 rounded-2xl bg-amber-500/10 text-amber-600 flex items-center justify-center transition-transform group-hover:scale-110 shadow-xs">
            <FileCheck2 class="w-4.5 h-4.5" />
          </div>
        </div>
        <div>
          <div class="text-3xl font-extrabold text-slate-900 font-mono tracking-tight">{{ metrics.pending_documents || 0 }}</div>
          <p class="text-[11px] text-slate-500 mt-1.5 flex items-center gap-1 font-medium">
            <ShieldCheck class="w-3.5 h-3.5 text-amber-600" />
            <span>{{ isAr ? 'شهادات الحجر والجمارك' : 'Phytosanitary & Customs' }}</span>
          </p>
        </div>
      </div>

      <!-- Invoiced Revenue Card -->
      <div class="shadcn-card p-5 space-y-3 relative overflow-hidden group hover:-translate-y-0.5 transition-all">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
            {{ isAr ? 'إجمالي المبيعات' : 'Invoiced Revenue' }}
          </span>
          <div class="w-9 h-9 rounded-2xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center transition-transform group-hover:scale-110 shadow-xs">
            <DollarSign class="w-4.5 h-4.5" />
          </div>
        </div>
        <div>
          <div class="text-2xl font-extrabold text-slate-900 font-mono tracking-tight">${{ Number(metrics.total_revenue || 0).toLocaleString() }}</div>
          <p class="text-[11px] text-slate-500 mt-1.5 font-medium">
            {{ isAr ? 'فواتير العملاء المرحلة' : 'Posted AR Invoices' }}
          </p>
        </div>
      </div>

      <!-- Net Margin Card (Signature Obsidian Dark Glass Card) -->
      <div class="shadcn-card p-5 space-y-3 relative overflow-hidden group hover:-translate-y-0.5 transition-all bg-gradient-to-br from-[#1d1e24] via-[#121316] to-[#0c0d0f] text-white border-white/10 shadow-[0_12px_36px_rgba(0,0,0,0.3)]">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold text-slate-300 uppercase tracking-wider">
            {{ isAr ? 'صافي أرباح العمليات' : 'Operating Margin' }}
          </span>
          <div class="w-9 h-9 rounded-2xl bg-white/10 text-emerald-400 flex items-center justify-center backdrop-blur-md transition-transform group-hover:scale-110">
            <TrendingUp class="w-4.5 h-4.5" />
          </div>
        </div>
        <div>
          <div class="text-2xl font-extrabold text-white font-mono tracking-tight">+${{ Number(metrics.net_profit || 0).toLocaleString() }}</div>
          <p class="text-[11px] text-slate-300 mt-1.5 font-medium flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>{{ isAr ? 'أرباح الرحلات المسجلة' : 'Total Job Net Margin' }}</span>
          </p>
        </div>
      </div>
    </div>

    <!-- Quick Shortcuts Pill Dock -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
      <Link
        :href="route('master-shipments.index')"
        class="shadcn-card p-3.5 flex items-center gap-3 hover:bg-white/95 transition-all cursor-pointer group"
      >
        <div class="w-8 h-8 rounded-xl bg-slate-100 group-hover:bg-slate-900 group-hover:text-white transition-colors flex items-center justify-center text-slate-700">
          <Ship class="w-4 h-4" />
        </div>
        <div class="min-w-0">
          <div class="text-xs font-bold text-slate-900 truncate">{{ isAr ? 'الشحنات الماستر' : 'Ocean Shipments' }}</div>
          <div class="text-[10px] text-slate-400 truncate">{{ isAr ? 'متابعة الرحلات' : 'Manage Bookings' }}</div>
        </div>
      </Link>

      <Link
        :href="route('invoices.index')"
        class="shadcn-card p-3.5 flex items-center gap-3 hover:bg-white/95 transition-all cursor-pointer group"
      >
        <div class="w-8 h-8 rounded-xl bg-slate-100 group-hover:bg-slate-900 group-hover:text-white transition-colors flex items-center justify-center text-slate-700">
          <DollarSign class="w-4 h-4" />
        </div>
        <div class="min-w-0">
          <div class="text-xs font-bold text-slate-900 truncate">{{ isAr ? 'الفواتير والمطالبات' : 'Invoices & Bills' }}</div>
          <div class="text-[10px] text-slate-400 truncate">{{ isAr ? 'الفوترة المالية' : 'Accounts Ledger' }}</div>
        </div>
      </Link>

      <Link
        :href="route('documents.vault')"
        class="shadcn-card p-3.5 flex items-center gap-3 hover:bg-white/95 transition-all cursor-pointer group"
      >
        <div class="w-8 h-8 rounded-xl bg-slate-100 group-hover:bg-slate-900 group-hover:text-white transition-colors flex items-center justify-center text-slate-700">
          <FileCheck2 class="w-4 h-4" />
        </div>
        <div class="min-w-0">
          <div class="text-xs font-bold text-slate-900 truncate">{{ isAr ? 'خزنة الشهادات' : 'Phyto Vault' }}</div>
          <div class="text-[10px] text-slate-400 truncate">{{ isAr ? 'الحجر الزراعي' : 'Compliance Docs' }}</div>
        </div>
      </Link>

      <Link
        :href="route('accounting.reports.trial-balance')"
        class="shadcn-card p-3.5 flex items-center gap-3 hover:bg-white/95 transition-all cursor-pointer group"
      >
        <div class="w-8 h-8 rounded-xl bg-slate-100 group-hover:bg-slate-900 group-hover:text-white transition-colors flex items-center justify-center text-slate-700">
          <Scale class="w-4 h-4" />
        </div>
        <div class="min-w-0">
          <div class="text-xs font-bold text-slate-900 truncate">{{ isAr ? 'ميزان المراجعة' : 'Trial Balance' }}</div>
          <div class="text-[10px] text-slate-400 truncate">{{ isAr ? 'القيود الموزونة' : 'Financial Health' }}</div>
        </div>
      </Link>
    </div>

    <!-- Recent Master Ocean Shipments Table Card (Liquid Glass with Squircle Badges) -->
    <div class="shadcn-card overflow-hidden">
      <!-- Table Header -->
      <div class="p-5 sm:p-6 border-b border-black/[0.04] flex items-center justify-between bg-white/40 backdrop-blur-md">
        <div>
          <h2 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
            <div class="w-7 h-7 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-xs">
              <Ship class="w-4 h-4" />
            </div>
            <span>{{ isAr ? 'أحدث الشحنات البحرية الماستر (MBL)' : 'Recent Master Ocean Shipments (MBL)' }}</span>
          </h2>
          <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5">
            {{ isAr ? 'متابعة الرحلات البحرية، حركة الحاويات المبردة وسلاسل التوريد المباشرة' : 'Live Status of Ocean Freight Voyages and Cold-Chain Logistics' }}
          </p>
        </div>

        <Link 
          :href="route('master-shipments.index')" 
          class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-white hover:bg-slate-50 text-slate-800 border border-slate-200 shadow-xs transition-all active:scale-95 cursor-pointer"
        >
          <span>{{ isAr ? 'عرض كافة الشحنات' : 'View All Shipments' }}</span>
          <component :is="isAr ? ArrowLeft : ArrowRight" class="w-3.5 h-3.5" />
        </Link>
      </div>

      <!-- Table Body -->
      <div class="overflow-x-auto">
        <table class="w-full text-xs" :class="isAr ? 'text-right' : 'text-left'">
          <thead class="bg-black/[0.02] text-slate-500 uppercase text-[10px] tracking-wider border-b border-black/[0.04] font-mono">
            <tr>
              <th class="px-5 py-3.5">{{ isAr ? 'رقم الملف والبيليسية' : 'File & MBL #' }}</th>
              <th class="px-5 py-3.5">{{ isAr ? 'الخط الملاحي والسفينة' : 'Carrier & Vessel' }}</th>
              <th class="px-5 py-3.5">{{ isAr ? 'مسار الشحن (شحن -> تفريغ)' : 'Route (POL -> POD)' }}</th>
              <th class="px-5 py-3.5">{{ isAr ? 'المغادرة / الوصول' : 'ETD / ETA' }}</th>
              <th class="px-5 py-3.5">{{ isAr ? 'الحاويات' : 'Units' }}</th>
              <th class="px-5 py-3.5">{{ isAr ? 'الحالة' : 'Status' }}</th>
              <th class="px-5 py-3.5" :class="isAr ? 'text-left' : 'text-right'">{{ isAr ? 'الإجراء' : 'Action' }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-black/[0.03] font-sans">
            <tr 
              v-for="job in recent_shipments" 
              :key="job.id" 
              class="hover:bg-sky-500/[0.04] transition-colors group"
            >
              <td class="px-5 py-3.5 font-mono">
                <div class="font-bold text-slate-900 group-hover:text-blue-600 transition-colors">{{ job.file_number }}</div>
                <div class="text-[10px] text-slate-400">{{ job.master_bl_number || (isAr ? 'في الانتظار' : 'Pending MBL') }}</div>
              </td>
              <td class="px-5 py-3.5">
                <div class="font-semibold text-slate-800">{{ job.shipping_line?.legal_name || '-' }}</div>
                <div class="text-[10px] text-slate-400 font-mono">{{ job.vessel_name || 'Vessel TBA' }} (v. {{ job.voyage_number || '1' }})</div>
              </td>
              <td class="px-5 py-3.5 font-mono text-slate-800 font-medium">
                <div class="flex items-center gap-1.5">
                  <span class="font-semibold">{{ job.pol?.name || 'Origin' }}</span>
                  <span class="text-slate-400">→</span>
                  <span class="font-semibold">{{ job.pod?.name || 'Destination' }}</span>
                </div>
              </td>
              <td class="px-5 py-3.5 text-slate-700 font-mono text-[11px]">
                <div>{{ isAr ? 'مغادرة:' : 'ETD:' }} {{ formatDateTime(job.etd) }}</div>
                <div class="text-slate-400">{{ isAr ? 'وصول:' : 'ETA:' }} {{ formatDateTime(job.eta) }}</div>
              </td>
              <td class="px-5 py-3.5">
                <span class="px-2.5 py-1 rounded-xl bg-[#f2f4f7] text-slate-700 font-mono text-[10px] font-bold border border-slate-200">
                  {{ job.containers?.length || 0 }} {{ isAr ? 'حاوية' : 'Units' }}
                </span>
              </td>
              <td class="px-5 py-3.5">
                <span 
                  class="px-2.5 py-1 rounded-full text-[10px] font-mono font-bold uppercase border bg-blue-50 text-blue-700 border-blue-200"
                >
                  {{ job.status }}
                </span>
              </td>
              <td class="px-5 py-3.5" :class="isAr ? 'text-left' : 'text-right'">
                <Link
                  :href="route('master-shipments.show', job.id)"
                  class="inline-flex items-center justify-center px-3 py-1 rounded-xl text-xs font-semibold bg-[#f2f4f7] hover:bg-slate-900 hover:text-white border border-slate-200 transition-all cursor-pointer active:scale-95"
                >
                  {{ isAr ? 'فحص الرحلة' : 'Inspect' }}
                </Link>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-if="!recent_shipments || recent_shipments.length === 0">
              <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                <Ship class="w-8 h-8 mx-auto mb-2 text-slate-300" />
                <p class="text-sm font-medium">{{ isAr ? 'لا توجد شحنات بحرية مسجلة حالياً' : 'No active ocean voyages found' }}</p>
                <Link :href="route('master-shipments.create')" class="mt-3 inline-block shadcn-btn-primary text-xs">
                  {{ isAr ? 'إنشاء أول شحنة' : 'Create First Booking' }}
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes scaleIn {
  from {
    opacity: 0;
    transform: scale(0.98) translateY(6px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.animate-scale-in {
  animation: scaleIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
