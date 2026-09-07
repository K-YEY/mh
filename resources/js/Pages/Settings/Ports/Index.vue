<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { Anchor, Plus, Globe, Search, Filter } from 'lucide-vue-next'

const { isAr } = useI18n()

const props = defineProps({
  ports: Array,
})

const showCreateModal = ref(false)
const searchQuery = ref('')
const selectedRegion = ref('ALL')

const regions = [
  { id: 'ALL', nameAr: 'كافة الموانئ', nameEn: 'All Ports', countries: [] },
  { id: 'EG', nameAr: 'موانئ مصر', nameEn: 'Egypt Ports', countries: ['EG'] },
  { id: 'GULF', nameAr: 'الخليج والشرق الأوسط', nameEn: 'Gulf & Middle East', countries: ['SA', 'AE', 'QA', 'KW', 'BH', 'OM', 'JO', 'LB', 'SY', 'LY', 'TN', 'DZ', 'MA', 'SD', 'DJ', 'YE'] },
  { id: 'EU', nameAr: 'أوروبا والمتوسط', nameEn: 'Europe & Med', countries: ['NL', 'BE', 'DE', 'GB', 'ES', 'IT', 'SI', 'HR', 'GR', 'FR', 'TR', 'RU', 'RO', 'BG', 'PL', 'SE', 'DK', 'CY', 'MT'] },
  { id: 'ASIA', nameAr: 'آسيا والشرق الأقصى', nameEn: 'Asia & India', countries: ['CN', 'HK', 'SG', 'MY', 'KR', 'JP', 'TW', 'LK', 'IN', 'PK', 'BD', 'TH', 'VN', 'ID', 'PH'] },
  { id: 'AMER', nameAr: 'الأمريكتين', nameEn: 'Americas', countries: ['US', 'CA', 'BR', 'AR', 'PE', 'CL', 'EC', 'CO', 'MX', 'PA', 'JM', 'DO'] },
]

const form = useForm({
  code: '',
  name: '',
  country_code: '',
})

const filteredPorts = computed(() => {
  return (props.ports || []).filter(p => {
    if (selectedRegion.value !== 'ALL') {
      const reg = regions.find(r => r.id === selectedRegion.value)
      if (reg && reg.countries.length && !reg.countries.includes(p.country_code)) {
        return false
      }
    }

    if (searchQuery.value) {
      const q = searchQuery.value.toLowerCase().trim()
      const matchCode = p.code?.toLowerCase().includes(q)
      const matchName = p.name?.toLowerCase().includes(q)
      const matchCountry = p.country_code?.toLowerCase().includes(q)
      if (!matchCode && !matchName && !matchCountry) {
        return false
      }
    }

    return true
  })
})

const submit = () => {
  form.post(route('ports.store'), {
    onSuccess: () => {
      showCreateModal.value = false
      form.reset()
    }
  })
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-extrabold text-zinc-950 tracking-tight flex items-center gap-2">
          <Anchor class="w-6 h-6 text-zinc-950" />
          <span>{{ isAr ? 'دليل الموانئ البحرية العالمية UN/LOCODE' : 'UN/LOCODE Ocean Seaports' }}</span>
        </h1>
        <p class="text-xs text-zinc-500">
          {{ isAr ? `شبكة الموانئ البحرية الدولية المعتمدة للشحن (${ports.length} ميناء عالمي مسجل)` : `Global Seaport Network Directory & Identifiers (${ports.length} Registered Ports)` }}
        </p>
      </div>

      <button
        @click="showCreateModal = true"
        class="shadcn-btn-primary gap-1.5 shadow-xs cursor-pointer text-xs"
      >
        <Plus class="w-4 h-4 font-bold" />
        <span>{{ isAr ? 'إضافة ميناء جديد' : 'Add Seaport' }}</span>
      </button>
    </div>

    <!-- Search & Region Filters -->
    <div class="shadcn-card p-4 space-y-3">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
        <!-- Search -->
        <div class="relative flex-1">
          <Search class="w-4 h-4 absolute top-2.5 text-zinc-400" :class="isAr ? 'right-3' : 'left-3'" />
          <input
            v-model="searchQuery"
            type="text"
            :placeholder="isAr ? 'بحث باسم الميناء، كود UN/LOCODE، أو رمز الدولة...' : 'Search by port name, UN/LOCODE, or country code...'"
            class="shadcn-input text-xs w-full"
            :class="isAr ? 'pr-9 pl-3' : 'pl-9 pr-3'"
          />
        </div>

        <!-- Filter Count -->
        <div class="text-xs font-mono text-zinc-500 shrink-0">
          {{ isAr ? `المعروض: ${filteredPorts.length} من أصل ${ports.length}` : `Showing: ${filteredPorts.length} of ${ports.length}` }}
        </div>
      </div>

      <!-- Region Badges -->
      <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs font-sans">
        <span class="text-[11px] font-bold text-zinc-500 shrink-0 flex items-center gap-1">
          <Filter class="w-3.5 h-3.5" />
          {{ isAr ? 'المنطقة:' : 'Region:' }}
        </span>
        <button
          v-for="reg in regions"
          :key="reg.id"
          type="button"
          @click="selectedRegion = reg.id"
          class="px-2.5 py-1 rounded text-xs font-bold transition-all cursor-pointer whitespace-nowrap"
          :class="selectedRegion === reg.id ? 'bg-zinc-950 text-white shadow-xs' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200'"
        >
          {{ isAr ? reg.nameAr : reg.nameEn }}
        </button>
      </div>
    </div>

    <!-- Ports Table Card -->
    <div class="shadcn-card overflow-hidden">
      <div class="p-4 border-b border-zinc-200 flex items-center justify-between bg-zinc-50">
        <div class="flex items-center gap-2">
          <Globe class="w-4 h-4 text-zinc-950" />
          <span class="text-xs font-bold text-zinc-950 uppercase tracking-wider">
            {{ isAr ? `قائمة الموانئ (${filteredPorts.length} ميناء)` : `Seaports List (${filteredPorts.length} Ports)` }}
          </span>
        </div>
      </div>

      <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
        <table class="w-full text-xs" :class="isAr ? 'text-right' : 'text-left'">
          <thead class="bg-zinc-100 text-zinc-700 uppercase text-[10px] tracking-wider border-b border-zinc-200 font-mono sticky top-0 z-10">
            <tr>
              <th class="px-4 py-3">UN/LOCODE</th>
              <th class="px-4 py-3">{{ isAr ? 'اسم الميناء' : 'Port Name' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'رمز الدولة' : 'Country Code' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'الحالة' : 'Status' }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-zinc-200 font-sans">
            <tr v-for="p in filteredPorts" :key="p.id" class="hover:bg-zinc-50 transition-colors">
              <td class="px-4 py-3 font-mono font-bold text-zinc-950">
                <span class="bg-zinc-100 px-2 py-0.5 rounded border border-zinc-200">
                  {{ p.code }}
                </span>
              </td>
              <td class="px-4 py-3 font-bold text-zinc-950">
                {{ p.name }}
              </td>
              <td class="px-4 py-3 font-mono text-zinc-800 font-bold">
                <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-900 border border-blue-200 text-[11px]">
                  {{ p.country_code }}
                </span>
              </td>
              <td class="px-4 py-3">
                <span class="px-2 py-0.5 rounded text-[9px] font-mono font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                  {{ isAr ? 'نشط' : 'ACTIVE' }}
                </span>
              </td>
            </tr>

            <tr v-if="!filteredPorts?.length">
              <td colspan="4" class="p-8 text-center text-zinc-500 font-mono">
                {{ isAr ? 'لم يتم العثور على موانئ مطابقة لمعايير البحث' : 'No seaports matched your search filter.' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4">
      <div class="shadcn-card w-full max-w-md p-6 space-y-4 bg-white border-zinc-200 shadow-2xl">
        <div class="flex items-center justify-between border-b border-zinc-200 pb-3">
          <h3 class="text-sm font-bold text-zinc-950">{{ isAr ? 'تسجيل ميناء بحري جديد' : 'Add UN/LOCODE Ocean Seaport' }}</h3>
          <button @click="showCreateModal = false" class="text-zinc-400 hover:text-zinc-950 font-bold">✕</button>
        </div>

        <form @submit.prevent="submit" class="space-y-3">
          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">UN/LOCODE (كود 5 أحرف)</label>
            <input v-model="form.code" type="text" placeholder="e.g. EGALY" required class="shadcn-input font-mono uppercase" dir="ltr" />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'اسم الميناء البحرى' : 'Port Name' }}</label>
            <input v-model="form.name" type="text" placeholder="e.g. Alexandria Port (ميناء الإسكندرية)" required class="shadcn-input" />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'كود الدولة (ISO-2)' : 'Country Code (ISO-2)' }}</label>
            <input v-model="form.country_code" type="text" placeholder="e.g. EG" required class="shadcn-input font-mono uppercase" dir="ltr" />
          </div>

          <div class="flex justify-end gap-2 pt-2 border-t border-zinc-200">
            <button type="button" @click="showCreateModal = false" class="shadcn-btn-secondary cursor-pointer">{{ isAr ? 'إلغاء' : 'Cancel' }}</button>
            <button type="submit" :disabled="form.processing" class="shadcn-btn-primary cursor-pointer">{{ isAr ? 'حفظ الميناء' : 'Save Seaport' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
