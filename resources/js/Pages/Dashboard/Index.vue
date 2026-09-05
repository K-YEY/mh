<script setup>
import { Link } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { 
  Ship, 
  Boxes, 
  TrendingUp, 
  DollarSign, 
  ThermometerSnowflake, 
  Plus, 
  ArrowUpRight, 
  Anchor,
  ChevronRight,
  ChevronLeft
} from 'lucide-vue-next'

const { isAr } = useI18n()

const props = defineProps({
  metrics: Object,
  recent_shipments: Array,
  reefer_alerts: Array,
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header Title & Quick Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-extrabold text-zinc-950 tracking-tight">
          {{ isAr ? 'لوحة التحكم الرئيسية والتشغيل' : 'Dashboard Overview' }}
        </h1>
        <p class="text-xs text-zinc-500 mt-0.5">
          {{ isAr ? 'منظومة الشحن البحري الزراعي وسلاسل التبريد المباشرة' : 'Agricultural Export Freight Forwarding & Reefer Cold-Chain Logistics' }}
        </p>
      </div>

      <div class="flex items-center gap-2">
        <Link
          :href="route('master-shipments.create')"
          class="shadcn-btn-primary gap-1.5 shadow-xs cursor-pointer"
        >
          <Plus class="w-4 h-4 font-bold" />
          <span>{{ isAr ? 'حجز شحنة بحرية جديدة' : 'New Ocean Booking' }}</span>
        </Link>
        <Link
          :href="route('tariffs.index')"
          class="shadcn-btn-secondary gap-1.5 cursor-pointer"
        >
          <Anchor class="w-4 h-4 text-zinc-950" />
          <span>{{ isAr ? 'مستكشف النولون' : 'Rate Finder' }}</span>
        </Link>
      </div>
    </div>

    <!-- Monochromatic Shadcn Metric Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Active Shipments Card -->
      <UiCard >
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-zinc-500 uppercase tracking-wider">
            {{ isAr ? 'الحجوزات النشطة' : 'Active Bookings' }}
          </span>
          <Ship class="w-4 h-4 text-zinc-400" />
        </div>
        <div>
          <div class="text-2xl font-bold text-zinc-950 font-mono tracking-tight">{{ metrics.active_shipments }}</div>
          <p class="text-[11px] text-zinc-700 mt-1 flex items-center gap-1 font-medium">
            <ArrowUpRight class="w-3.5 h-3.5" />
            <span>{{ isAr ? 'رحلات ماستر FCL و LCL' : 'Master FCL & LCL Voyages' }}</span>
          </p>
        </div>
      </div>

      <!-- Active Containers Card -->
      <UiCard >
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-zinc-500 uppercase tracking-wider">
            {{ isAr ? 'الحاوية المبردة المفتوحة' : 'Active Reefer Units' }}
          </span>
          <Boxes class="w-4 h-4 text-zinc-400" />
        </div>
        <div>
          <div class="text-2xl font-bold text-zinc-950 font-mono tracking-tight">{{ metrics.active_containers }}</div>
          <p class="text-[11px] text-zinc-700 mt-1 flex items-center gap-1 font-medium">
            <ThermometerSnowflake class="w-3.5 h-3.5" />
            <span>{{ isAr ? 'تتبع سلاسل التبريد' : 'Cold Chain Monitoring' }}</span>
          </p>
        </div>
      </div>

      <!-- Invoiced Revenue Card -->
      <UiCard >
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-zinc-500 uppercase tracking-wider">
            {{ isAr ? 'إجمالي مبيعات الفواتير' : 'Invoiced Sales' }}
          </span>
          <DollarSign class="w-4 h-4 text-zinc-400" />
        </div>
        <div>
          <div class="text-2xl font-bold text-zinc-950 font-mono tracking-tight">${{ metrics.total_revenue.toLocaleString() }}</div>
          <p class="text-[11px] text-zinc-600 mt-1 font-medium">
            {{ isAr ? 'أرصدة حسابات العملاء' : 'Accounts Receivable Ledger' }}
          </p>
        </div>
      </div>

      <!-- Net Margin Card -->
      <UiCard >
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-zinc-300 uppercase tracking-wider">
            {{ isAr ? 'صافي أرباح العمليات' : 'Net Job Margin' }}
          </span>
          <TrendingUp class="w-4 h-4 text-white" />
        </div>
        <div>
          <div class="text-2xl font-bold text-white font-mono tracking-tight">+${{ metrics.net_profit.toLocaleString() }}</div>
          <p class="text-[11px] text-zinc-400 mt-1 font-medium">
            {{ isAr ? 'أرباح وخسائر الرحلات التشغيلية' : 'Total Operational Job P&L' }}
          </p>
        </div>
      </div>
    </div>

    <!-- Cold Treatment Probes Section Card -->
    <UiCard >
      <div class="flex items-center justify-between border-b border-zinc-200 pb-3">
        <div class="flex items-center gap-2.5">
          <ThermometerSnowflake class="w-5 h-5 text-zinc-950" />
          <div>
            <h2 class="text-sm font-bold text-zinc-950">
              {{ isAr ? 'حالة التبريد والحجر الزراعي USDA' : 'Agricultural Cold Chain & USDA Treatment Status' }}
            </h2>
            <p class="text-[11px] text-zinc-500">
              {{ isAr ? 'تسجيل مباشر لحساسات الحرارة الـ 3 للحاويات المبردة' : 'Live Reefer Containers with 3-Probe USDA Protocol Logging' }}
            </p>
          </div>
        </div>
        <span class="px-2.5 py-0.5 rounded text-[10px] font-mono font-bold uppercase bg-zinc-100 text-zinc-950 border border-zinc-300">
          {{ isAr ? 'بروتوكول تبريد فعال' : 'USDA CT Active' }}
        </span>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        <div v-for="c in reefer_alerts" :key="c.id" class="p-3.5 rounded-lg bg-zinc-50 border border-zinc-200 space-y-2.5">
          <div class="flex items-center justify-between">
            <span class="text-xs font-mono font-bold text-zinc-950">{{ c.container_number }}</span>
            <span class="px-2 py-0.2 text-[9px] font-mono font-bold rounded bg-zinc-200 text-zinc-900 border border-zinc-300 uppercase">
              {{ c.current_status }}
            </span>
          </div>

          <div class="grid grid-cols-3 gap-2 text-center py-1.5 bg-white rounded border border-zinc-200 text-xs font-mono">
            <div>
              <div class="text-[9px] text-zinc-500 uppercase font-sans">{{ isAr ? 'الحرارة المطلوبة' : 'Target Temp' }}</div>
              <div class="font-bold text-zinc-950">{{ c.target_temperature }}°C</div>
            </div>
            <div>
              <div class="text-[9px] text-zinc-500 uppercase font-sans">{{ isAr ? 'تدفق التهوية' : 'Vent Flow' }}</div>
              <div class="font-bold text-zinc-950">{{ c.ventilation_flow }} CBM/h</div>
            </div>
            <div>
              <div class="text-[9px] text-zinc-500 uppercase font-sans">{{ isAr ? 'الرطوبة' : 'Humidity' }}</div>
              <div class="font-bold text-zinc-950">{{ c.humidity_percent }}%</div>
            </div>
          </div>

          <!-- CT Probes -->
          <div v-if="c.requires_cold_treatment" class="text-xs bg-white p-2 rounded border border-zinc-200 space-y-1">
            <div class="text-[9px] font-bold text-zinc-700 uppercase font-sans">
              {{ isAr ? 'حساسات التبريد 3 Probes:' : 'USDA Probes:' }}
            </div>
            <div class="flex justify-between font-mono text-[11px] text-zinc-800">
              <span>P1: <strong class="text-zinc-950">{{ c.ct_probe_1 || '+1.1°C' }}</strong></span>
              <span>P2: <strong class="text-zinc-950">{{ c.ct_probe_2 || '+1.2°C' }}</strong></span>
              <span>P3: <strong class="text-zinc-950">{{ c.ct_probe_3 || '+1.1°C' }}</strong></span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Master Ocean Shipments Table Card -->
    <UiCard >
      <div class="p-4 border-b border-zinc-200 flex items-center justify-between bg-zinc-50">
        <div>
          <h2 class="text-sm font-bold text-zinc-950 flex items-center gap-2">
            <Ship class="w-4 h-4 text-zinc-950" />
            <span>{{ isAr ? 'أحدث الشحنات البحرية الماستر (MBL)' : 'Recent Master Ocean Shipments (MBL)' }}</span>
          </h2>
          <p class="text-[11px] text-zinc-500">
            {{ isAr ? 'متابعة الرحلات البحرية وحركة الحاويات المباشرة' : 'Live Status of Ocean Freight Voyages and Container Logistics' }}
          </p>
        </div>
        <Link :href="route('master-shipments.index')" class="text-xs font-bold text-zinc-950 hover:underline flex items-center gap-1">
          <span>{{ isAr ? 'عرض الكل' : 'View All' }}</span>
          <component :is="isAr ? ChevronLeft : ChevronRight" class="w-3.5 h-3.5" />
        </Link>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-xs" :class="isAr ? 'text-right' : 'text-left'">
          <thead class="bg-zinc-100 text-zinc-700 uppercase text-[10px] tracking-wider border-b border-zinc-200 font-mono">
            <tr>
              <th class="px-4 py-3">{{ isAr ? 'رقم الملف والبيليسية' : 'File & MBL #' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'الناقل والسفينة' : 'Carrier & Vessel' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'مسار الشحن (شحن -> تفريغ)' : 'Route (POL -> POD)' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'التغادر / الوصول' : 'ETD / ETA' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'الحاويات' : 'Units' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'الحالة' : 'Status' }}</th>
              <th class="px-4 py-3" :class="isAr ? 'text-left' : 'text-right'">{{ isAr ? 'التحكم' : 'Action' }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-zinc-200 font-sans">
            <tr v-for="job in recent_shipments" :key="job.id" class="hover:bg-zinc-50 transition-colors">
              <td class="px-4 py-3 font-mono">
                <div class="font-bold text-zinc-950">{{ job.file_number }}</div>
                <div class="text-[10px] text-zinc-500">{{ job.master_bl_number || (isAr ? 'في الانتظار' : 'Pending MBL') }}</div>
              </td>
              <td class="px-4 py-3">
                <div class="font-semibold text-zinc-900">{{ job.shipping_line?.legal_name }}</div>
                <div class="text-[10px] text-zinc-500 font-mono">{{ job.vessel_name }} (v. {{ job.voyage_number }})</div>
              </td>
              <td class="px-4 py-3 font-mono text-zinc-950 font-semibold">
                {{ job.pol?.name }} ← {{ job.pod?.name }}
              </td>
              <td class="px-4 py-3 text-zinc-800 font-mono text-[11px]">
                <div>{{ isAr ? 'مغادرة:' : 'ETD:' }} {{ job.etd }}</div>
                <div class="text-zinc-500">{{ isAr ? 'وصول:' : 'ETA:' }} {{ job.eta }}</div>
              </td>
              <td class="px-4 py-3">
                <span class="px-2 py-0.5 rounded bg-zinc-100 text-zinc-800 font-mono text-[10px] font-bold border border-zinc-300">
                  {{ job.containers?.length || 0 }} {{ isAr ? 'حاوية' : 'Units' }}
                </span>
              </td>
              <td class="px-4 py-3">
                <span 
                  class="px-2 py-0.5 rounded text-[9px] font-mono font-bold uppercase border bg-zinc-100 text-zinc-950 border-zinc-300"
                >
                  {{ job.status }}
                </span>
              </td>
              <td class="px-4 py-3" :class="isAr ? 'text-left' : 'text-right'">
                <Link
                  :href="route('master-shipments.show', job.id)"
                  class="shadcn-btn-secondary text-xs h-7 px-2.5 cursor-pointer"
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
