<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { Anchor, Plus, Globe, Search, Filter, Pencil, Trash2, X, CheckCircle2 } from 'lucide-vue-next'

const { isAr } = useI18n()

const props = defineProps({
  ports: Array,
})

const showCreateModal = ref(false)
const showEditModal = ref(false)
const searchQuery = ref('')
const selectedRegion = ref('ALL')

const regions = [
  { id: 'ALL', nameAr: 'كافة الموانئ', nameEn: 'All Ports', countries: [] },
  { id: 'EG', nameAr: 'موانئ مصر', nameEn: 'Egypt Ports', countries: ['EG'] },
  { id: 'GULF', nameAr: 'الخليج والشرق الأوسط', nameEn: 'Gulf & Middle East', countries: ['SA', 'AE', 'QA', 'KW', 'BH', 'OM', 'IQ', 'JO', 'LB', 'SY', 'LY', 'TN', 'DZ', 'MA', 'SD', 'DJ', 'YE'] },
  { id: 'EU', nameAr: 'أوروبا والمتوسط', nameEn: 'Europe & Med', countries: ['NL', 'BE', 'DE', 'GB', 'ES', 'IT', 'SI', 'HR', 'GR', 'FR', 'TR', 'RU', 'RO', 'BG', 'PL', 'SE', 'DK', 'CY', 'MT'] },
  { id: 'ASIA', nameAr: 'آسيا والشرق الأقصى', nameEn: 'Asia & India', countries: ['CN', 'HK', 'SG', 'MY', 'KR', 'JP', 'TW', 'LK', 'IN', 'PK', 'BD', 'TH', 'VN', 'ID', 'PH'] },
  { id: 'AMER', nameAr: 'الأمريكتين', nameEn: 'Americas', countries: ['US', 'CA', 'BR', 'AR', 'PE', 'CL', 'EC', 'CO', 'MX', 'PA', 'JM', 'DO', 'PR'] },
  { id: 'AFR_OCEAN', nameAr: 'إفريقيا وأوقيانوسيا', nameEn: 'Africa & Oceania', countries: ['ZA', 'KE', 'TZ', 'NG', 'GH', 'CI', 'SN', 'TG', 'BJ', 'AO', 'MZ', 'NA', 'AU', 'NZ'] },
]

const form = useForm({
  code: '',
  name: '',
  country_code: '',
})

const editForm = useForm({
  id: null,
  code: '',
  name: '',
  country_code: '',
  is_active: true,
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

const submitCreate = () => {
  form.post(route('ports.store'), {
    onSuccess: () => {
      showCreateModal.value = false
      form.reset()
    }
  })
}

const openEditModal = (port) => {
  editForm.id = port.id
  editForm.code = port.code
  editForm.name = port.name
  editForm.country_code = port.country_code
  editForm.is_active = port.is_active !== false
  showEditModal.value = true
}

const submitEdit = () => {
  editForm.put(route('ports.update', editForm.id), {
    onSuccess: () => {
      showEditModal.value = false
      editForm.reset()
    }
  })
}

const deletePort = (port) => {
  const confirmMsg = isAr.value 
    ? `هل أنت متأكد من حذف الميناء ${port.name} (${port.code}) نهائياً من النظام؟` 
    : `Are you sure you want to permanently delete port ${port.name} (${port.code})?`

  if (confirm(confirmMsg)) {
    router.delete(route('ports.destroy', port.id))
  }
}
</script>

<template>
  <div class="space-y-8">
    <!-- Header with Apple Liquid Aesthetics -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
          <div class="w-9 h-9 rounded-2xl bg-blue-500/10 text-blue-600 flex items-center justify-center">
            <Anchor class="w-5 h-5" />
          </div>
          <span>{{ isAr ? 'دليل الموانئ البحرية العالمية UN/LOCODE' : 'UN/LOCODE Ocean Seaports' }}</span>
        </h1>
        <p class="text-xs text-slate-500 mt-1 font-medium">
          {{ isAr ? `شبكة الموانئ البحرية الدولية المعتمدة للشحن (${ports.length} ميناء عالمي مسجل حول العالم)` : `Global Seaport Network Directory & Identifiers (${ports.length} Registered Ports Worldwide)` }}
        </p>
      </div>

      <button
        @click="showCreateModal = true"
        class="shadcn-btn-primary gap-2 shadow-sm hover:shadow-md cursor-pointer text-xs"
      >
        <Plus class="w-4 h-4 font-bold" />
        <span>{{ isAr ? 'إضافة ميناء بحري جديد' : 'Add Seaport' }}</span>
      </button>
    </div>

    <!-- Search & Region Filters (Apple Liquid Glass Card) -->
    <div class="shadcn-card p-5 space-y-4">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Search Field -->
        <div class="relative flex-1">
          <Search class="w-4 h-4 absolute top-3 text-slate-400" :class="isAr ? 'right-3.5' : 'left-3.5'" />
          <input
            v-model="searchQuery"
            type="text"
            :placeholder="isAr ? 'بحث سريع باسم الميناء، كود UN/LOCODE، أو رمز الدولة...' : 'Search by port name, UN/LOCODE, or country code...'"
            class="shadcn-input text-xs w-full"
            :class="isAr ? 'pr-10 pl-4' : 'pl-10 pr-4'"
          />
        </div>

        <!-- Filter Count Pill -->
        <div class="text-xs font-mono text-slate-600 shrink-0 bg-slate-100/80 px-3.5 py-1.5 rounded-full border border-slate-200 backdrop-blur-sm">
          {{ isAr ? `المعروض: ${filteredPorts.length} من أصل ${ports.length}` : `Showing: ${filteredPorts.length} of ${ports.length}` }}
        </div>
      </div>

      <!-- Region Badges -->
      <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
        <span class="text-[11px] font-bold text-slate-500 shrink-0 flex items-center gap-1.5">
          <Filter class="w-3.5 h-3.5 text-slate-400" />
          {{ isAr ? 'المنطقة الجغرافية:' : 'Region:' }}
        </span>
        <button
          v-for="reg in regions"
          :key="reg.id"
          type="button"
          @click="selectedRegion = reg.id"
          class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all cursor-pointer whitespace-nowrap active:scale-95"
          :class="selectedRegion === reg.id 
            ? 'bg-gradient-to-r from-[#0071e3] to-[#0077ed] text-white shadow-sm font-bold' 
            : 'bg-white/80 text-slate-600 hover:bg-white hover:text-slate-900 border border-slate-200/80'"
        >
          {{ isAr ? reg.nameAr : reg.nameEn }}
        </button>
      </div>
    </div>

    <!-- Ports Table Card with Prominent Edit & Action Controls -->
    <div class="shadcn-card overflow-hidden">
      <div class="p-5 border-b border-black/[0.04] flex items-center justify-between bg-white/40 backdrop-blur-md">
        <div class="flex items-center gap-2.5">
          <div class="w-6 h-6 rounded-lg bg-blue-500/10 text-blue-600 flex items-center justify-center">
            <Globe class="w-3.5 h-3.5" />
          </div>
          <span class="text-xs font-bold text-slate-900 uppercase tracking-wider">
            {{ isAr ? `سجل الموانئ البحرية (${filteredPorts.length} ميناء)` : `Seaports List (${filteredPorts.length} Ports)` }}
          </span>
        </div>
      </div>

      <div class="overflow-x-auto max-h-[650px] overflow-y-auto">
        <table class="w-full text-xs" :class="isAr ? 'text-right' : 'text-left'">
          <thead class="bg-black/[0.02] text-slate-600 uppercase text-[10px] tracking-wider border-b border-black/[0.04] font-mono sticky top-0 z-10 backdrop-blur-md">
            <tr>
              <th class="px-5 py-3.5">UN/LOCODE</th>
              <th class="px-5 py-3.5">{{ isAr ? 'اسم الميناء' : 'Port Name' }}</th>
              <th class="px-5 py-3.5">{{ isAr ? 'رمز الدولة' : 'Country Code' }}</th>
              <th class="px-5 py-3.5">{{ isAr ? 'الحالة' : 'Status' }}</th>
              <th class="px-5 py-3.5 text-center">{{ isAr ? 'التحكم والإجراءات' : 'Actions' }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-black/[0.03] font-sans">
            <tr v-for="p in filteredPorts" :key="p.id" class="hover:bg-blue-500/[0.03] transition-colors">
              <!-- UN/LOCODE -->
              <td class="px-5 py-3.5 font-mono font-bold text-slate-900">
                <span class="bg-slate-100/90 text-slate-800 px-2.5 py-1 rounded-lg border border-slate-200/90 shadow-2xs font-mono">
                  {{ p.code }}
                </span>
              </td>

              <!-- Port Name -->
              <td class="px-5 py-3.5 font-semibold text-slate-900">
                {{ p.name }}
              </td>

              <!-- Country Code -->
              <td class="px-5 py-3.5 font-mono text-blue-900 font-bold">
                <span class="px-2.5 py-0.5 rounded-full bg-blue-500/10 text-blue-700 border border-blue-500/20 text-[11px]">
                  {{ p.country_code }}
                </span>
              </td>

              <!-- Status -->
              <td class="px-5 py-3.5">
                <span 
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold border backdrop-blur-sm"
                  :class="p.is_active !== false 
                    ? 'bg-emerald-500/10 text-emerald-700 border-emerald-500/20' 
                    : 'bg-slate-200 text-slate-600 border-slate-300'"
                >
                  {{ p.is_active !== false ? (isAr ? 'نشط' : 'ACTIVE') : (isAr ? 'معطل' : 'INACTIVE') }}
                </span>
              </td>

              <!-- Action Column with Explicit Edit & Delete Buttons -->
              <td class="px-5 py-3.5 text-center">
                <div class="flex items-center justify-center gap-2">
                  <!-- Edit Button with Clear Arabic & Icon -->
                  <button
                    type="button"
                    @click="openEditModal(p)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-500/10 hover:bg-blue-500/20 text-blue-700 text-xs font-bold border border-blue-500/20 transition-all cursor-pointer active:scale-95 shadow-2xs"
                    :title="isAr ? 'تعديل بيانات الميناء' : 'Edit Port'"
                  >
                    <Pencil class="w-3.5 h-3.5" />
                    <span>{{ isAr ? 'تعديل' : 'Edit' }}</span>
                  </button>

                  <!-- Delete Button -->
                  <button
                    type="button"
                    @click="deletePort(p)"
                    class="inline-flex items-center p-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 transition-all cursor-pointer active:scale-95 border border-rose-500/20"
                    :title="isAr ? 'حذف الميناء' : 'Delete Port'"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="!filteredPorts?.length">
              <td colspan="5" class="p-12 text-center text-slate-500 font-mono">
                {{ isAr ? 'لم يتم العثور على موانئ مطابقة لمعايير البحث' : 'No seaports matched your search filter.' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Modal (Apple Frosted Glass Modal) -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4 animate-fade-down">
      <div class="shadcn-card w-full max-w-md p-7 space-y-5 bg-white/95 border-white shadow-[0_24px_60px_rgba(0,0,0,0.15)] relative">
        <div class="flex items-center justify-between border-b border-black/[0.05] pb-4">
          <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
            <Anchor class="w-4 h-4 text-blue-600" />
            <span>{{ isAr ? 'تسجيل ميناء بحري جديد' : 'Add UN/LOCODE Ocean Seaport' }}</span>
          </h3>
          <button 
            @click="showCreateModal = false" 
            class="p-1.5 rounded-xl text-slate-400 hover:text-slate-800 hover:bg-black/[0.04] transition-colors cursor-pointer"
          >
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="submitCreate" class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-800 mb-1.5">UN/LOCODE (كود 5 أحرف)</label>
            <input 
              v-model="form.code" 
              type="text" 
              placeholder="e.g. EGALY" 
              maxlength="5" 
              required 
              class="shadcn-input font-mono uppercase" 
              dir="ltr" 
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-800 mb-1.5">{{ isAr ? 'اسم الميناء البحري' : 'Port Name' }}</label>
            <input 
              v-model="form.name" 
              type="text" 
              placeholder="e.g. Alexandria Port (ميناء الإسكندرية)" 
              required 
              class="shadcn-input" 
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-800 mb-1.5">{{ isAr ? 'كود الدولة (ISO-2)' : 'Country Code (ISO-2)' }}</label>
            <input 
              v-model="form.country_code" 
              type="text" 
              placeholder="e.g. EG" 
              maxlength="2" 
              required 
              class="shadcn-input font-mono uppercase" 
              dir="ltr" 
            />
          </div>

          <div class="flex justify-end gap-2.5 pt-3 border-t border-black/[0.05]">
            <button 
              type="button" 
              @click="showCreateModal = false" 
              class="shadcn-btn-secondary cursor-pointer text-xs"
            >
              {{ isAr ? 'إلغاء' : 'Cancel' }}
            </button>
            <button 
              type="submit" 
              :disabled="form.processing" 
              class="shadcn-btn-primary cursor-pointer text-xs"
            >
              {{ isAr ? 'حفظ الميناء' : 'Save Seaport' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Modal (Apple Frosted Glass Modal for Editing) -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4 animate-fade-down">
      <div class="shadcn-card w-full max-w-md p-7 space-y-5 bg-white/95 border-white shadow-[0_24px_60px_rgba(0,0,0,0.15)] relative">
        <div class="flex items-center justify-between border-b border-black/[0.05] pb-4">
          <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
            <Pencil class="w-4 h-4 text-blue-600" />
            <span>{{ isAr ? 'تعديل بيانات الميناء البحري' : 'Edit Ocean Seaport' }}</span>
          </h3>
          <button 
            @click="showEditModal = false" 
            class="p-1.5 rounded-xl text-slate-400 hover:text-slate-800 hover:bg-black/[0.04] transition-colors cursor-pointer"
          >
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="submitEdit" class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-800 mb-1.5">UN/LOCODE (كود 5 أحرف)</label>
            <input 
              v-model="editForm.code" 
              type="text" 
              maxlength="5" 
              required 
              class="shadcn-input font-mono uppercase" 
              dir="ltr" 
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-800 mb-1.5">{{ isAr ? 'اسم الميناء البحري' : 'Port Name' }}</label>
            <input 
              v-model="editForm.name" 
              type="text" 
              required 
              class="shadcn-input" 
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-800 mb-1.5">{{ isAr ? 'كود الدولة (ISO-2)' : 'Country Code (ISO-2)' }}</label>
            <input 
              v-model="editForm.country_code" 
              type="text" 
              maxlength="2" 
              required 
              class="shadcn-input font-mono uppercase" 
              dir="ltr" 
            />
          </div>

          <div class="flex items-center gap-2.5 pt-1">
            <label class="flex items-center gap-2 cursor-pointer select-none">
              <input 
                v-model="editForm.is_active" 
                type="checkbox" 
                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer" 
              />
              <span class="text-xs font-bold text-slate-800">{{ isAr ? 'الميناء نشط ومتاح للعمليات' : 'Port is Active' }}</span>
            </label>
          </div>

          <div class="flex justify-end gap-2.5 pt-3 border-t border-black/[0.05]">
            <button 
              type="button" 
              @click="showEditModal = false" 
              class="shadcn-btn-secondary cursor-pointer text-xs"
            >
              {{ isAr ? 'إلغاء' : 'Cancel' }}
            </button>
            <button 
              type="submit" 
              :disabled="editForm.processing" 
              class="shadcn-btn-primary cursor-pointer text-xs gap-1.5"
            >
              <CheckCircle2 class="w-3.5 h-3.5" />
              <span>{{ isAr ? 'حفظ التعديلات' : 'Save Changes' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
