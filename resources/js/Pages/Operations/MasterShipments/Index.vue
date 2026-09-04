<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { Ship, Plus, Search } from 'lucide-vue-next'

const { isAr } = useI18n()

const props = defineProps({
  shipments: Object,
  filters: Object,
})

const search = ref(props.filters.search || '')

const handleSearch = () => {
  router.get(route('master-shipments.index'), { search: search.value }, { preserveState: true })
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-extrabold text-zinc-950 tracking-tight flex items-center gap-2">
          <Ship class="w-6 h-6 text-zinc-950" />
          <span>{{ isAr ? 'الشحنات البحرية الماستر (MBL)' : 'Master Ocean Shipments (MBL Level)' }}</span>
        </h1>
        <p class="text-xs text-zinc-500">
          {{ isAr ? 'إدارة رحلات الشحن البحري الزراعية FCL/LCL وحجوزات الخطوط والحاويات' : 'Manage FCL/LCL Agricultural Export Voyages, Line Bookings, & Containers' }}
        </p>
      </div>

      <Link
        :href="route('master-shipments.create')"
        class="shadcn-btn-primary gap-1.5 shadow-xs cursor-pointer"
      >
        <Plus class="w-4 h-4 font-bold" />
        <span>{{ isAr ? 'فتح ملف ماستر جديد' : 'Open Master File' }}</span>
      </Link>
    </div>

    <!-- Search & Filter Bar Card -->
    <div class="shadcn-card p-4 flex items-center gap-4">
      <div class="relative flex-1">
        <Search class="absolute top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" :class="isAr ? 'right-3' : 'left-3'" />
        <input
          v-model="search"
          @keyup.enter="handleSearch"
          type="text"
          :placeholder="isAr ? 'تصفية برقم الملف، الحجز، اسم السفينة، أو البوليصة...' : 'Filter by File #, Booking Ref, Vessel Name, or MBL #...'"
          :style="isAr ? 'padding-right: 2.5rem !important; padding-left: 0.75rem !important;' : 'padding-left: 2.5rem !important; padding-right: 0.75rem !important;'"
          class="shadcn-input"
        />
      </div>
      <button
        @click="handleSearch"
        class="shadcn-btn-secondary cursor-pointer"
      >
        {{ isAr ? 'تصفية النتائج' : 'Filter Results' }}
      </button>
    </div>

    <!-- Master Shipments Data Grid Card -->
    <div class="shadcn-card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-xs" :class="isAr ? 'text-right' : 'text-left'">
          <thead class="bg-zinc-100 text-zinc-700 uppercase text-[10px] tracking-wider border-b border-zinc-200 font-mono">
            <tr>
              <th class="px-5 py-3.5">{{ isAr ? 'رقم الملف والحجز' : 'File & Booking Ref' }}</th>
              <th class="px-5 py-3.5">{{ isAr ? 'الاتجاه ونوع الشحن' : 'Direction & Mode' }}</th>
              <th class="px-5 py-3.5">{{ isAr ? 'خط الشحن والسفينة' : 'Shipping Line & Vessel' }}</th>
              <th class="px-5 py-3.5">{{ isAr ? 'المسار (شحن -> تفريغ)' : 'Routing (POL -> POD)' }}</th>
              <th class="px-5 py-3.5">{{ isAr ? 'المغادرة والوصول' : 'Sailing (ETD/ETA)' }}</th>
              <th class="px-5 py-3.5">{{ isAr ? 'الحاويات' : 'Units' }}</th>
              <th class="px-5 py-3.5">{{ isAr ? 'الحالة' : 'Status' }}</th>
              <th class="px-5 py-3.5" :class="isAr ? 'text-left' : 'text-right'">{{ isAr ? 'التحكم' : 'Action' }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-zinc-200">
            <tr v-for="job in shipments.data" :key="job.id" class="hover:bg-zinc-50 transition-colors">
              <td class="px-5 py-4 font-mono">
                <div class="font-bold text-zinc-950 text-sm">{{ job.file_number }}</div>
                <div class="text-[11px] text-zinc-500">Ref: {{ job.booking_reference || 'N/A' }}</div>
              </td>
              <td class="px-5 py-4">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-zinc-100 text-zinc-950 border border-zinc-300" :class="isAr ? 'ml-1' : 'mr-1'">
                  {{ job.direction }}
                </span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-zinc-200 text-zinc-900 border border-zinc-300">
                  {{ job.shipment_mode }}
                </span>
              </td>
              <td class="px-5 py-4">
                <div class="font-bold text-zinc-950">{{ job.shipping_line?.legal_name }}</div>
                <div class="text-[11px] text-zinc-500 font-mono">{{ job.vessel_name }} (v. {{ job.voyage_number }})</div>
              </td>
              <td class="px-5 py-4">
                <div class="font-semibold text-zinc-950 flex items-center gap-1 font-mono">
                  <span>{{ job.pol?.name }}</span>
                  <span class="text-zinc-400">←</span>
                  <span>{{ job.pod?.name }}</span>
                </div>
              </td>
              <td class="px-5 py-4 text-zinc-700 font-mono text-[11px]">
                <div>{{ isAr ? 'مغادرة:' : 'ETD:' }} {{ job.etd }}</div>
                <div class="text-zinc-500">{{ isAr ? 'وصول:' : 'ETA:' }} {{ job.eta }}</div>
              </td>
              <td class="px-5 py-4 font-mono">
                <span class="px-2 py-0.5 rounded bg-zinc-100 text-zinc-800 text-[11px] font-bold border border-zinc-300">
                  {{ job.containers?.length || 0 }} {{ isAr ? 'حاوية' : 'Units' }}
                </span>
              </td>
              <td class="px-5 py-4">
                <span class="px-2.5 py-0.5 rounded text-[10px] font-mono font-bold uppercase bg-zinc-950 text-white border border-zinc-950">
                  {{ job.status }}
                </span>
              </td>
              <td class="px-5 py-4" :class="isAr ? 'text-left' : 'text-right'">
                <Link
                  :href="route('master-shipments.show', job.id)"
                  class="shadcn-btn-secondary text-xs h-7 px-3 cursor-pointer"
                >
                  {{ isAr ? 'فتح الشحنة' : 'Open File' }}
                </Link>
              </td>
            </tr>

            <tr v-if="!shipments.data?.length">
              <td colspan="8" class="p-8 text-center text-zinc-500 font-mono">
                {{ isAr ? 'لا توجد شحنات بحرية مسجلة حالياً' : 'No master ocean shipments found.' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
