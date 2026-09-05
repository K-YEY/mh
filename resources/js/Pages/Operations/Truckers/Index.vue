<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { 
  Truck, 
  Plus, 
  Search, 
  Phone, 
  Mail, 
  MapPin, 
  Gauge, 
  UserCheck, 
  Cpu, 
  FileText,
  X 
} from 'lucide-vue-next'

const { isAr } = useI18n()

const props = defineProps({
  truckers: Array,
  filters: Object,
})

const showCreateModal = ref(false)
const searchQuery = ref(props.filters?.search || '')

const form = useForm({
  code: '',
  name: '',
  trade_name: '',
  fleet_size: 10,
  truck_types: 'تريلات مبردة Reefer + مولدات جينست Genset',
  coverage_areas: 'الإسكندرية، الدلتا، دمياط، مزارع النوبارية، السادات',
  license_number: '',
  tax_number: '',
  commercial_registry: '',
  dispatch_contact: '',
  dispatch_phone: '',
  phone: '',
  email: '',
  address: '',
  notes: '',
})

const submit = () => {
  form.post(route('truckers.store'), {
    onSuccess: () => {
      showCreateModal.value = false
      form.reset()
    }
  })
}

const totalFleet = computed(() => {
  return props.truckers?.reduce((acc, t) => acc + (t.fleet_size || 0), 0) || 0
})

const filteredTruckers = computed(() => {
  let list = props.truckers || []
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim()
    list = list.filter(t => 
      t.code?.toLowerCase().includes(q) ||
      t.name?.toLowerCase().includes(q) ||
      t.truck_types?.toLowerCase().includes(q) ||
      t.coverage_areas?.toLowerCase().includes(q) ||
      t.dispatch_contact?.toLowerCase().includes(q) ||
      t.phone?.toLowerCase().includes(q)
    )
  }
  return list
})
</script>

<template>
  <div class="space-y-6 pb-12">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-extrabold text-zinc-950 tracking-tight flex items-center gap-2">
          <Truck class="w-6 h-6 text-emerald-600" />
          <span>{{ isAr ? 'جدول شركات النقل البري والمبرد (Inland Haulage)' : 'Inland Reefer Trucking Companies' }}</span>
        </h1>
        <p class="text-xs text-zinc-500">
          {{ isAr ? 'إدارة أساطيل النقل البري لنقل الحاويات المبردة من محطات التعبئة والمزارع إلى الموانئ البحرية' : 'Manage Reefer Trucking Fleets, Genset Generators, Dispatchers & Packhouse Coverage' }}
        </p>
      </div>

      <button
        @click="showCreateModal = true"
        class="shadcn-btn-primary gap-1.5 shadow-xs cursor-pointer text-xs"
      >
        <Plus class="w-4 h-4 font-bold" />
        <span>{{ isAr ? 'إضافة شركة نقل بري' : 'Add Trucking Company' }}</span>
      </button>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="shadcn-card p-4 flex items-center justify-between">
        <div>
          <div class="text-[10px] text-zinc-500 uppercase font-bold">{{ isAr ? 'إجمالي شركات النقل' : 'Trucking Carriers' }}</div>
          <div class="text-2xl font-bold text-zinc-950 font-mono mt-0.5">{{ truckers?.length || 0 }}</div>
        </div>
        <div class="p-2.5 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200">
          <Truck class="w-5 h-5" />
        </div>
      </div>

      <div class="shadcn-card p-4 flex items-center justify-between">
        <div>
          <div class="text-[10px] text-zinc-500 uppercase font-bold">{{ isAr ? 'إجمالي أسطول الشاحنات المتاحة' : 'Total Fleet Size' }}</div>
          <div class="text-2xl font-bold text-zinc-950 font-mono mt-0.5">
            {{ totalFleet }} {{ isAr ? 'شاحنة / تريلا' : 'Trucks' }}
          </div>
        </div>
        <div class="p-2.5 rounded-xl bg-blue-50 text-blue-600 border border-blue-200">
          <Gauge class="w-5 h-5" />
        </div>
      </div>

      <div class="shadcn-card p-4 flex items-center justify-between">
        <div>
          <div class="text-[10px] text-zinc-500 uppercase font-bold">{{ isAr ? 'تجهيزات النقل المبرد' : 'Reefer Equipment' }}</div>
          <div class="text-sm font-bold text-zinc-950 mt-1 font-sans">
            {{ isAr ? 'مولدات جينست (Gensets) • مقطورات مبردة' : 'Genset Generators • 40HR Trailers' }}
          </div>
        </div>
        <div class="p-2.5 rounded-xl bg-amber-50 text-amber-600 border border-amber-200">
          <Cpu class="w-5 h-5" />
        </div>
      </div>
    </div>

    <!-- Search Bar -->
    <div class="flex items-center justify-between gap-4">
      <div class="relative max-w-sm w-full">
        <Search class="w-4 h-4 text-zinc-400 absolute right-3 top-1/2 -translate-y-1/2" />
        <input 
          v-model="searchQuery" 
          type="text" 
          :placeholder="isAr ? 'البحث بالاسم، الكود، نوع الشاحنات، نطاق العمل، أو الديسباتش...' : 'Search by name, code, truck type, coverage, dispatcher...'" 
          class="shadcn-input pr-9 text-xs" 
        />
      </div>

      <div class="text-xs text-zinc-500 font-mono">
        {{ isAr ? `عرض ${filteredTruckers.length} شركة نقل بري` : `Showing ${filteredTruckers.length} hauliers` }}
      </div>
    </div>

    <!-- Truckers Table -->
    <div class="shadcn-card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-xs" :class="isAr ? 'text-right' : 'text-left'">
          <thead class="bg-zinc-100 text-zinc-700 uppercase text-[10px] tracking-wider border-b border-zinc-200 font-mono">
            <tr>
              <th class="px-4 py-3">{{ isAr ? 'كود الناقل' : 'Haulier Code' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'اسم شركة النقل' : 'Carrier Name' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'الأسطول ونوع الشاحنات' : 'Fleet & Equipment' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'نطاق التغطية والمزارع' : 'Operating Coverage' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'مسؤول الحركة والديسباتش' : 'Dispatch Control' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'الحالة' : 'Status' }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-zinc-200 font-sans">
            <tr v-for="t in filteredTruckers" :key="t.id" class="hover:bg-zinc-50 transition-colors">
              <td class="px-4 py-3 font-mono font-bold text-zinc-950">
                {{ t.code }}
              </td>
              <td class="px-4 py-3">
                <div class="font-bold text-zinc-950 text-sm">{{ t.name }}</div>
                <div v-if="t.trade_name" class="text-[11px] text-zinc-500">DBA: {{ t.trade_name }}</div>
                <div class="text-[10px] text-zinc-500 font-mono mt-0.5">
                  {{ isAr ? 'ضريبي:' : 'Tax:' }} {{ t.tax_number || 'N/A' }}
                  <span v-if="t.license_number"> • ترخيص: {{ t.license_number }}</span>
                </div>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-1 font-mono font-bold text-zinc-950">
                  <Gauge class="w-3.5 h-3.5 text-blue-600" />
                  <span>{{ t.fleet_size || 1 }} {{ isAr ? 'تريلا / شاحنة' : 'Trucks' }}</span>
                </div>
                <div class="text-[11px] text-zinc-600 mt-0.5 max-w-[200px] truncate">
                  {{ t.truck_types || (isAr ? 'تريلات مبردة وجينست' : 'Reefers & Gensets') }}
                </div>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-1 text-zinc-800 font-medium">
                  <MapPin class="w-3.5 h-3.5 text-emerald-600 shrink-0" />
                  <span class="truncate max-w-[220px]">{{ t.coverage_areas || (isAr ? 'محافظات الدلتا والموانئ البحرية' : 'Delta & Ocean Ports') }}</span>
                </div>
              </td>
              <td class="px-4 py-3 font-mono text-zinc-600 text-[11px]">
                <div v-if="t.dispatch_contact" class="font-bold text-zinc-900 font-sans flex items-center gap-1">
                  <UserCheck class="w-3.5 h-3.5 text-purple-600" />
                  <span>{{ t.dispatch_contact }}</span>
                </div>
                <div v-if="t.dispatch_phone || t.phone" class="flex items-center gap-1 mt-0.5">
                  <Phone class="w-3 h-3 text-zinc-400" />
                  <span>{{ t.dispatch_phone || t.phone }}</span>
                </div>
                <div v-if="t.email" class="flex items-center gap-1">
                  <Mail class="w-3 h-3 text-zinc-400" />
                  <span>{{ t.email }}</span>
                </div>
              </td>
              <td class="px-4 py-3">
                <span class="px-2 py-0.5 rounded text-[9px] font-mono font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                  {{ isAr ? 'نشط' : 'ACTIVE' }}
                </span>
              </td>
            </tr>

            <tr v-if="!filteredTruckers?.length">
              <td colspan="6" class="p-8 text-center text-zinc-500 font-mono">
                {{ isAr ? 'لا يوجد شركات نقل مسجلة بعد.' : 'No trucking carriers found.' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Trucker Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-xs">
      <div class="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl border border-zinc-200">
        <div class="p-4 border-b border-zinc-200 flex items-center justify-between bg-zinc-50">
          <h3 class="font-bold text-sm text-zinc-950 flex items-center gap-2">
            <Truck class="w-5 h-5 text-emerald-600" />
            <span>{{ isAr ? 'تسجيل شركة نقل بري ومبرد جديدة' : 'Register New Trucking Haulier' }}</span>
          </h3>
          <button @click="showCreateModal = false" class="text-zinc-400 hover:text-zinc-950 text-sm font-bold">✕</button>
        </div>

        <form @submit.prevent="submit" class="p-6 space-y-4">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">
                {{ isAr ? 'كود شركة النقل' : 'Haulier Code' }}
                <span class="text-red-500">*</span>
              </label>
              <input v-model="form.code" type="text" placeholder="e.g. TRK-DELTA" class="shadcn-input font-mono uppercase" required dir="ltr" />
            </div>
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">
                {{ isAr ? 'اسم شركة النقل' : 'Company Name' }}
                <span class="text-red-500">*</span>
              </label>
              <input v-model="form.name" type="text" placeholder="e.g. دلتا لوجيستكس للنقل المبرد" class="shadcn-input" required />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'حجم الأسطول (عدد الشاحنات)' : 'Fleet Size' }}</label>
              <input v-model="form.fleet_size" type="number" min="1" class="shadcn-input font-mono" dir="ltr" />
            </div>
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'رقم ترخيص النقل' : 'Transport License #' }}</label>
              <input v-model="form.license_number" type="text" placeholder="e.g. TRK-MOT-1029" class="shadcn-input font-mono" dir="ltr" />
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'أنواع الشاحنات والتجهيزات' : 'Truck Types / Gensets' }}</label>
            <input v-model="form.truck_types" type="text" placeholder="e.g. تريلات مبردة 40HR + مولدات جينست Genset" class="shadcn-input" />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'نطاق التغطية ومناطق التحميل' : 'Operating Coverage' }}</label>
            <input v-model="form.coverage_areas" type="text" placeholder="e.g. مزارع النوبارية، السادات، ميناء الإسكندرية، دمياط" class="shadcn-input" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'مسؤول الحركة والديسباتش' : 'Dispatcher Name' }}</label>
              <input v-model="form.dispatch_contact" type="text" placeholder="e.g. كابتن / أحمد سمير" class="shadcn-input" />
            </div>
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'هاتف غرفة الحركة' : 'Dispatch Phone' }}</label>
              <input v-model="form.dispatch_phone" type="text" placeholder="+20 101 ..." class="shadcn-input font-mono" dir="ltr" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'الرقم الضريبي' : 'Tax Number' }}</label>
              <input v-model="form.tax_number" type="text" placeholder="e.g. 100-881-229" class="shadcn-input font-mono" dir="ltr" />
            </div>
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'البريد الإلكتروني' : 'Email' }}</label>
              <input v-model="form.email" type="email" placeholder="ops@deltahaul.com" class="shadcn-input font-mono" dir="ltr" />
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'مقر الشركة / الجراج الرئيسي' : 'Yard / Office Address' }}</label>
            <input v-model="form.address" type="text" placeholder="طريق الإسكندرية الصحراوي كم 70" class="shadcn-input" />
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t border-zinc-200">
            <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-lg text-xs font-bold text-zinc-600 hover:bg-zinc-100">
              {{ isAr ? 'إلغاء' : 'Cancel' }}
            </button>
            <button type="submit" :disabled="form.processing" class="shadcn-btn-primary px-6 py-2 cursor-pointer font-bold text-xs">
              {{ isAr ? 'حفظ شركة النقل' : 'Save Carrier' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
