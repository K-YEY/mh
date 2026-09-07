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
  ChevronLeft
} from 'lucide-vue-next'

const { isAr, formatDateTime } = useI18n()

const props = defineProps({
  metrics: Object,
  recent_shipments: Array,
})
</script>

<template>
  <div class="space-y-8">
    <!-- Header Title & Quick Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
          <span>{{ isAr ? 'لوحة التحكم والتشغيل المباشر' : 'Operations Dashboard' }}</span>
        </h1>
        <p class="text-xs text-slate-500 mt-1 font-medium">
          {{ isAr ? 'منظومة الشحن البحري الزراعي وسلاسل التبريد المباشرة' : 'Agricultural Export Freight Forwarding & Reefer Cold-Chain Logistics' }}
        </p>
      </div>

      <div class="flex items-center gap-2.5">
        <Link
          :href="route('master-shipments.create')"
          class="shadcn-btn-primary gap-2 shadow-sm hover:shadow-md cursor-pointer text-xs"
        >
          <Plus class="w-4 h-4 font-bold" />
          <span>{{ isAr ? 'حجز شحنة بحرية جديدة' : 'New Ocean Booking' }}</span>
        </Link>
        <Link
          :href="route('tariffs.index')"
          class="shadcn-btn-secondary gap-2 cursor-pointer text-xs"
        >
          <Anchor class="w-4 h-4 text-slate-700" />
          <span>{{ isAr ? 'مستكشف النولون' : 'Rate Finder' }}</span>
        </Link>
      </div>
    </div>

    <!-- Apple Liquid Glass Metric Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
      <!-- Active Shipments Card -->
      <div class="shadcn-card p-5 space-y-3 relative overflow-hidden group">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">
            {{ isAr ? 'الحجوزات النشطة' : 'Active Bookings' }}
          </span>
          <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center transition-transform group-hover:scale-110">
            <Ship class="w-4 h-4" />
          </div>
        </div>
        <div>
          <div class="text-3xl font-extrabold text-slate-900 font-mono tracking-tight">{{ metrics.active_shipments }}</div>
          <p class="text-[11px] text-slate-600 mt-1.5 flex items-center gap-1 font-medium">
            <ArrowUpRight class="w-3.5 h-3.5 text-blue-600" />
            <span>{{ isAr ? 'رحلات ماستر FCL و LCL' : 'Master FCL & LCL Voyages' }}</span>
          </p>
        </div>
      </div>

      <!-- Active Containers Card -->
      <div class="shadcn-card p-5 space-y-3 relative overflow-hidden group">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">
            {{ isAr ? 'الحاويات المبردة' : 'Active Reefer Units' }}
          </span>
          <div class="w-8 h-8 rounded-xl bg-sky-500/10 text-sky-600 flex items-center justify-center transition-transform group-hover:scale-110">
            <Boxes class="w-4 h-4" />
          </div>
        </div>
        <div>
          <div class="text-3xl font-extrabold text-slate-900 font-mono tracking-tight">{{ metrics.active_containers }}</div>
          <p class="text-[11px] text-slate-600 mt-1.5 flex items-center gap-1 font-medium">
            <Boxes class="w-3.5 h-3.5 text-sky-600" />
            <span>{{ isAr ? 'تتبع سلاسل التبريد النشطة' : 'Cold Chain Monitoring' }}</span>
          </p>
        </div>
      </div>

      <!-- Invoiced Revenue Card -->
      <div class="shadcn-card p-5 space-y-3 relative overflow-hidden group">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">
            {{ isAr ? 'إجمالي المبيعات' : 'Invoiced Sales' }}
          </span>
          <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center transition-transform group-hover:scale-110">
            <DollarSign class="w-4 h-4" />
          </div>
        </div>
        <div>
          <div class="text-3xl font-extrabold text-slate-900 font-mono tracking-tight">${{ metrics.total_revenue.toLocaleString() }}</div>
          <p class="text-[11px] text-slate-600 mt-1.5 font-medium">
            {{ isAr ? 'أرصدة حسابات العملاء' : 'Accounts Receivable Ledger' }}
          </p>
        </div>
      </div>

      <!-- Net Margin Card (Apple Dark Liquid Card) -->
      <div class="shadcn-card p-5 space-y-3 relative overflow-hidden group bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 text-white border-white/10 shadow-[0_12px_36px_rgba(15,23,42,0.25)]">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-300 uppercase tracking-wider">
            {{ isAr ? 'صافي أرباح العمليات' : 'Net Job Margin' }}
          </span>
          <div class="w-8 h-8 rounded-xl bg-white/10 text-emerald-400 flex items-center justify-center backdrop-blur-md transition-transform group-hover:scale-110">
            <TrendingUp class="w-4 h-4" />
          </div>
        </div>
        <div>
          <div class="text-3xl font-extrabold text-white font-mono tracking-tight">+${{ metrics.net_profit.toLocaleString() }}</div>
          <p class="text-[11px] text-slate-300 mt-1.5 font-medium flex items-center gap-1">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
            <span>{{ isAr ? 'أرباح وخسائر الرحلات التشغيلية' : 'Total Operational Job P&L' }}</span>
          </p>
        </div>
      </div>
    </div>

    <!-- Recent Master Ocean Shipments Table Card (Apple Liquid Glass Table) -->
    <div class="shadcn-card overflow-hidden">
      <div class="p-5 border-b border-black/[0.04] flex items-center justify-between bg-white/40 backdrop-blur-md">
        <div>
          <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
            <div class="w-6 h-6 rounded-lg bg-blue-500/10 text-blue-600 flex items-center justify-center">
              <Ship class="w-3.5 h-3.5" />
            </div>
            <span>{{ isAr ? 'أحدث الشحنات البحرية الماستر (MBL)' : 'Recent Master Ocean Shipments (MBL)' }}</span>
          </h2>
          <p class="text-[11px] text-slate-500 mt-0.5">
            {{ isAr ? 'متابعة الرحلات البحرية وحركة الحاويات المباشرة' : 'Live Status of Ocean Freight Voyages and Container Logistics' }}
          </p>
        </div>
        <Link :href="route('master-shipments.index')" class="text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1 transition-colors">
          <span>{{ isAr ? 'عرض الكل' : 'View All' }}</span>
          <component :is="isAr ? ChevronLeft : ChevronRight" class="w-3.5 h-3.5" />
        </Link>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-xs" :class="isAr ? 'text-right' : 'text-left'">
          <thead class="bg-black/[0.02] text-slate-600 uppercase text-[10px] tracking-wider border-b border-black/[0.04] font-mono">
            <tr>
              <th class="px-5 py-3.5">{{ isAr ? 'رقم الملف والبيليسية' : 'File & MBL #' }}</th>
              <th class="px-5 py-3.5">{{ isAr ? 'الناقل والسفينة' : 'Carrier & Vessel' }}</th>
              <th class="px-5 py-3.5">{{ isAr ? 'مسار الشحن (شحن -> تفريغ)' : 'Route (POL -> POD)' }}</th>
              <th class="px-5 py-3.5">{{ isAr ? 'المغادرة / الوصول' : 'ETD / ETA' }}</th>
              <th class="px-5 py-3.5">{{ isAr ? 'الحاويات' : 'Units' }}</th>
              <th class="px-5 py-3.5">{{ isAr ? 'الحالة' : 'Status' }}</th>
              <th class="px-5 py-3.5" :class="isAr ? 'text-left' : 'text-right'">{{ isAr ? 'التحكم' : 'Action' }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-black/[0.03] font-sans">
            <tr v-for="job in recent_shipments" :key="job.id" class="hover:bg-blue-500/[0.03] transition-colors">
              <td class="px-5 py-3.5 font-mono">
                <div class="font-bold text-slate-900">{{ job.file_number }}</div>
                <div class="text-[10px] text-slate-500">{{ job.master_bl_number || (isAr ? 'في الانتظار' : 'Pending MBL') }}</div>
              </td>
              <td class="px-5 py-3.5">
                <div class="font-semibold text-slate-800">{{ job.shipping_line?.legal_name }}</div>
                <div class="text-[10px] text-slate-500 font-mono">{{ job.vessel_name }} (v. {{ job.voyage_number }})</div>
              </td>
              <td class="px-5 py-3.5 font-mono text-slate-900 font-semibold">
                {{ job.pol?.name }} ← {{ job.pod?.name }}
              </td>
              <td class="px-5 py-3.5 text-slate-700 font-mono text-[11px]">
                <div>{{ isAr ? 'مغادرة:' : 'ETD:' }} {{ formatDateTime(job.etd) }}</div>
                <div class="text-slate-500">{{ isAr ? 'وصول:' : 'ETA:' }} {{ formatDateTime(job.eta) }}</div>
              </td>
              <td class="px-5 py-3.5">
                <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 font-mono text-[10px] font-bold border border-slate-200">
                  {{ job.containers?.length || 0 }} {{ isAr ? 'حاوية' : 'Units' }}
                </span>
              </td>
              <td class="px-5 py-3.5">
                <span 
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase border bg-blue-500/10 text-blue-700 border-blue-500/20 backdrop-blur-sm"
                >
                  {{ job.status }}
                </span>
              </td>
              <td class="px-5 py-3.5" :class="isAr ? 'text-left' : 'text-right'">
                <Link
                  :href="route('master-shipments.show', job.id)"
                  class="shadcn-btn-secondary text-xs h-7 px-3 rounded-xl cursor-pointer hover:border-blue-300"
                >
                  {{ isAr ? 'فحص الشحنة' : 'Inspect' }}
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
