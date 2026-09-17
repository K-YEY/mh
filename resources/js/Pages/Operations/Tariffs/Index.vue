<script setup>
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { useCurrency } from '@/Composables/useCurrency'
import { Anchor, Search, Clock } from 'lucide-vue-next'

const { isAr } = useI18n()
const { formatMoney, currentCurrency } = useCurrency()

const props = defineProps({
  tariffs: Array,
  ports: Array,
  filters: Object,
})

const polId = ref(props.filters.pol_id || '')
const podId = ref(props.filters.pod_id || '')
const containerType = ref(props.filters.container_type || '40HR')

const searchTariffs = () => {
  router.get(route('tariffs.index'), {
    pol_id: polId.value,
    pod_id: podId.value,
    container_type: containerType.value,
  }, { preserveState: true })
}
</script>

<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-extrabold text-zinc-950 tracking-tight flex items-center gap-2">
          <Anchor class="w-6 h-6 text-zinc-950" />
          <span>{{ isAr ? 'مصفوقة مقارنة واستكشاف أسعار النولون البحري' : 'Ocean Freight Rate Comparison Matrix' }}</span>
        </h1>
        <p class="text-xs text-zinc-500">
          {{ isAr ? 'تقييم أسعار الشراء للخطوط الملاحية، زمن الترانسيت، وأيام السماح المجانية' : 'Evaluate Carrier Buy Rates, Transit Time, & Demurrage Free Days' }}
        </p>
      </div>
    </div>

    <!-- Filter Form Bar Card -->
    <div class="shadcn-card p-5 grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
      <div>
        <label class="block text-xs font-bold text-zinc-700 mb-1">{{ isAr ? 'ميناء الشحن (POL)' : 'Port of Loading (POL)' }}</label>
        <select v-model="polId" class="shadcn-input">
          <option value="">{{ isAr ? 'كل موانئ القيام...' : 'All Origin Ports...' }}</option>
          <option v-for="p in ports" :key="p.id" :value="p.id">{{ p.name }} ({{ p.code }})</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-bold text-zinc-700 mb-1">{{ isAr ? 'ميناء التفريغ (POD)' : 'Port of Discharge (POD)' }}</label>
        <select v-model="podId" class="shadcn-input">
          <option value="">{{ isAr ? 'كل موانئ الوصول...' : 'All Destination Ports...' }}</option>
          <option v-for="p in ports" :key="p.id" :value="p.id">{{ p.name }} ({{ p.code }})</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-bold text-zinc-700 mb-1">{{ isAr ? 'نوع الحاوية' : 'Equipment Type' }}</label>
        <select v-model="containerType" class="shadcn-input">
          <option value="40HR">40' High Reefer (40HR)</option>
          <option value="40RF">40' Standard Reefer (40RF)</option>
          <option value="20RF">20' Reefer (20RF)</option>
          <option value="40HC">40' High Cube Dry (40HC)</option>
        </select>
      </div>

      <button
        @click="searchTariffs"
        class="shadcn-btn-primary w-full gap-2 cursor-pointer"
      >
        <Search class="w-4 h-4 font-bold" />
        <span>{{ isAr ? 'تقييم ومقارنة الأسعار' : 'Evaluate Rates' }}</span>
      </button>
    </div>

    <!-- Rate Comparison Grid Card -->
    <div class="shadcn-card overflow-hidden">
      <table class="w-full text-xs" :class="isAr ? 'text-right' : 'text-left'">
        <thead class="bg-zinc-100 text-zinc-700 uppercase text-[10px] tracking-wider border-b border-zinc-200 font-mono">
          <tr>
            <th class="p-4">{{ isAr ? 'الترتيب' : 'Rank' }}</th>
            <th class="p-4">{{ isAr ? 'خط الشحن' : 'Shipping Line' }}</th>
            <th class="p-4">{{ isAr ? 'المسار (شحن -> تفريغ)' : 'Route (POL -> POD)' }}</th>
            <th class="p-4">{{ isAr ? 'الحاوية' : 'Container' }}</th>
            <th class="p-4">{{ isAr ? 'سعر الشراء' : 'Line Buy Rate' }}</th>
            <th class="p-4">{{ isAr ? 'مدة الإبحار' : 'Transit Time' }}</th>
            <th class="p-4">{{ isAr ? 'أيام السماح المجانية' : 'Free Demurrage' }}</th>
            <th class="p-4" :class="isAr ? 'text-left' : 'text-right'">{{ isAr ? 'التحكم' : 'Action' }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-zinc-200">
          <tr v-for="(t, index) in tariffs" :key="t.id" :class="[index === 0 ? 'bg-zinc-100 border-l-4 border-l-zinc-950 font-bold' : 'hover:bg-zinc-50']">
            <td class="p-4">
              <span v-if="index === 0" class="px-2 py-0.5 rounded text-[10px] font-bold bg-zinc-950 text-white uppercase">
                {{ isAr ? 'أفضل سعر' : 'Best Rate' }}
              </span>
              <span v-else class="text-zinc-500 font-mono font-bold">#{{ index + 1 }}</span>
            </td>
            <td class="p-4">
              <div class="font-bold text-zinc-950 text-sm">{{ t.shipping_line?.legal_name }}</div>
              <div class="text-[11px] text-zinc-500">Code: {{ t.shipping_line?.code }}</div>
            </td>
            <td class="p-4 font-mono font-semibold text-zinc-900">
              {{ t.pol?.name }} ({{ t.pol?.code }}) ← {{ t.pod?.name }} ({{ t.pod?.code }})
            </td>
            <td class="p-4 font-mono font-bold">
              <span class="px-2 py-0.5 rounded bg-zinc-200 text-zinc-900 text-[10px]">
                {{ t.container_type }}
              </span>
            </td>
            <td class="p-4 font-mono text-base font-bold text-zinc-950">
              ${{ Number(t.buy_rate).toFixed(2) }} <span class="text-xs text-zinc-500 font-normal">/ {{ isAr ? 'حاوية' : 'Unit' }}</span>
            </td>
            <td class="p-4 font-mono text-zinc-700">
              <div class="flex items-center gap-1 font-bold">
                <Clock class="w-3.5 h-3.5 text-zinc-950" />
                <span>{{ t.transit_time_days }} {{ isAr ? 'أيام' : 'Days' }}</span>
              </div>
            </td>
            <td class="p-4 font-mono text-zinc-700">
              <span class="font-bold text-zinc-950">{{ t.free_days_demurrage }} {{ isAr ? 'أيام' : 'Days' }}</span> {{ isAr ? 'مجاناً' : 'Free' }}
            </td>
            <td class="p-4" :class="isAr ? 'text-left' : 'text-right'">
              <Link
                :href="route('master-shipments.create', { tariff_id: t.id })"
                class="shadcn-btn-primary text-xs h-8 px-3 cursor-pointer"
              >
                {{ isAr ? 'حجز الخط' : 'Book Carrier' }}
              </Link>
            </td>
          </tr>

          <tr v-if="!tariffs?.length">
            <td colspan="8" class="p-8 text-center text-zinc-500 font-mono">
              {{ isAr ? 'لا توجد أسعار نولون مسجلة لهذه الفلاتر' : 'No freight tariffs found.' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
