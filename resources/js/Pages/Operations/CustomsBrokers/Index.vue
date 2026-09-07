<script setup>
import { ref, computed } from 'vue'
import { useForm, router, Link } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { 
  ShieldCheck, 
  Plus, 
  Search, 
  Phone, 
  Mail, 
  Anchor, 
  FileBadge, 
  UserCheck, 
  Building2,
  X,
  Pencil,
  Trash2,
  CheckCircle2
} from 'lucide-vue-next'

const { isAr } = useI18n()

const props = defineProps({
  brokers: Array,
  ports: Array,
  filters: Object,
})

const showCreateModal = ref(false)
const showEditModal = ref(false)
const searchQuery = ref(props.filters?.search || '')

const form = useForm({
  code: '',
  name: '',
  trade_name: '',
  license_number: '',
  operating_ports: 'ميناء الإسكندرية / ميناء الدخيلة',
  tax_number: '',
  commercial_registry: '',
  contact_person: '',
  contact_phone: '',
  phone: '',
  email: '',
  address: '',
  notes: '',
})

const editForm = useForm({
  id: null,
  code: '',
  name: '',
  trade_name: '',
  license_number: '',
  operating_ports: '',
  tax_number: '',
  commercial_registry: '',
  contact_person: '',
  contact_phone: '',
  phone: '',
  email: '',
  address: '',
  notes: '',
  is_active: true,
})

const openEditModal = (broker) => {
  editForm.id = broker.id
  editForm.code = broker.code
  editForm.name = broker.name
  editForm.trade_name = broker.trade_name || ''
  editForm.license_number = broker.license_number || ''
  editForm.operating_ports = broker.operating_ports || ''
  editForm.tax_number = broker.tax_number || ''
  editForm.commercial_registry = broker.commercial_registry || ''
  editForm.contact_person = broker.contact_person || ''
  editForm.contact_phone = broker.contact_phone || ''
  editForm.phone = broker.phone || ''
  editForm.email = broker.email || ''
  editForm.address = broker.address || ''
  editForm.notes = broker.notes || ''
  editForm.is_active = broker.is_active !== false
  showEditModal.value = true
}

const submitCreate = () => {
  form.post(route('customs-brokers.store'), {
    onSuccess: () => {
      showCreateModal.value = false
      form.reset()
    }
  })
}

const submitEdit = () => {
  editForm.put(route('customs-brokers.update', editForm.id), {
    onSuccess: () => {
      showEditModal.value = false
      editForm.reset()
    }
  })
}

const deleteBroker = (broker) => {
  const msg = isAr.value 
    ? `هل أنت متأكد من حذف المخلص الجمركي ${broker.name} (${broker.code})؟` 
    : `Are you sure you want to delete customs broker ${broker.name}?`
  if (confirm(msg)) {
    router.delete(route('customs-brokers.destroy', broker.id))
  }
}

const filteredBrokers = computed(() => {
  let list = props.brokers || []
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim()
    list = list.filter(b => 
      b.code?.toLowerCase().includes(q) ||
      b.name?.toLowerCase().includes(q) ||
      b.license_number?.toLowerCase().includes(q) ||
      b.operating_ports?.toLowerCase().includes(q) ||
      b.contact_person?.toLowerCase().includes(q) ||
      b.tax_number?.toLowerCase().includes(q)
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
          <ShieldCheck class="w-6 h-6 text-blue-600" />
          <span>{{ isAr ? 'جدول المخلصين الجمركيين المعتمدين (Customs Brokers)' : 'Customs Brokers Directory' }}</span>
        </h1>
        <p class="text-xs text-zinc-500">
          {{ isAr ? 'إدارة وكلاء ومكاتب التخليص الجمركي، رخص التخليص، الموانئ المعتمدة، ومندوبي المنافذ البحرية' : 'Manage Accredited Customs Brokerage Offices, Licenses, Operating Ports & Port Agents' }}
        </p>
      </div>

      <button
        @click="showCreateModal = true"
        class="shadcn-btn-primary gap-1.5 shadow-xs cursor-pointer text-xs"
      >
        <Plus class="w-4 h-4 font-bold" />
        <span>{{ isAr ? 'إضافة مخلص جمركي جديد' : 'Add Customs Broker' }}</span>
      </button>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="shadcn-card p-4 flex items-center justify-between">
        <div>
          <div class="text-[10px] text-zinc-500 uppercase font-bold">{{ isAr ? 'إجمالي المخلصين' : 'Total Brokers' }}</div>
          <div class="text-2xl font-bold text-zinc-950 font-mono mt-0.5">{{ brokers?.length || 0 }}</div>
        </div>
        <div class="p-2.5 rounded-xl bg-blue-50 text-blue-600 border border-blue-200">
          <ShieldCheck class="w-5 h-5" />
        </div>
      </div>

      <div class="shadcn-card p-4 flex items-center justify-between">
        <div>
          <div class="text-[10px] text-zinc-500 uppercase font-bold">{{ isAr ? 'المخلصين النشطين' : 'Active Brokers' }}</div>
          <div class="text-2xl font-bold text-emerald-600 font-mono mt-0.5">
            {{ brokers?.filter(b => b.is_active)?.length || 0 }}
          </div>
        </div>
        <div class="p-2.5 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200">
          <UserCheck class="w-5 h-5" />
        </div>
      </div>

      <div class="shadcn-card p-4 flex items-center justify-between">
        <div>
          <div class="text-[10px] text-zinc-500 uppercase font-bold">{{ isAr ? 'الموانئ البحرية المعتمدة' : 'Operational Ports' }}</div>
          <div class="text-sm font-bold text-zinc-950 mt-1 font-sans">
            {{ isAr ? 'الإسكندرية • الدخيلة • دمياط • بورسعيد • السخنة' : 'ALX • DKH • DAM • PSD • SOK' }}
          </div>
        </div>
        <div class="p-2.5 rounded-xl bg-amber-50 text-amber-600 border border-amber-200">
          <Anchor class="w-5 h-5" />
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
          :placeholder="isAr ? 'البحث بالاسم، الكود، رقم الرخصة، الميناء، أو المندوب...' : 'Search by name, code, license, port, agent...'" 
          class="shadcn-input pr-9 text-xs" 
        />
      </div>

      <div class="text-xs text-zinc-500 font-mono">
        {{ isAr ? `عرض ${filteredBrokers.length} مخلص جمركي` : `Showing ${filteredBrokers.length} brokers` }}
      </div>
    </div>

    <!-- Customs Brokers Table -->
    <div class="shadcn-card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-xs" :class="isAr ? 'text-right' : 'text-left'">
          <thead class="bg-zinc-100 text-zinc-700 uppercase text-[10px] tracking-wider border-b border-zinc-200 font-mono">
            <tr>
              <th class="px-4 py-3">{{ isAr ? 'كود المخلص' : 'Broker Code' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'اسم مكتب / شركة التخليص' : 'Brokerage Office Name' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'رخصة التخليص والضرائب' : 'License & Tax #' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'الموانئ المعتمدة' : 'Operating Ports' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'مندوب الميناء والاتصال' : 'Port Agent & Contact' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'الحالة' : 'Status' }}</th>
              <th class="px-4 py-3 text-center">{{ isAr ? 'التحكم والإجراءات' : 'Actions' }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-zinc-200 font-sans">
            <tr v-for="b in filteredBrokers" :key="b.id" class="hover:bg-zinc-50 transition-colors">
              <td class="px-4 py-3 font-mono font-bold text-zinc-950">
                {{ b.code }}
              </td>
              <td class="px-4 py-3">
                <div class="font-bold text-zinc-950 text-sm">{{ b.name }}</div>
                <div v-if="b.trade_name" class="text-[11px] text-zinc-500">DBA: {{ b.trade_name }}</div>
                <div v-if="b.address" class="text-[10px] text-zinc-400 truncate max-w-[220px]">{{ b.address }}</div>
              </td>
              <td class="px-4 py-3 font-mono text-zinc-600 text-[11px]">
                <div class="flex items-center gap-1 text-zinc-900 font-bold">
                  <FileBadge class="w-3.5 h-3.5 text-blue-600" />
                  <span>{{ b.license_number || 'LIC-STANDARD' }}</span>
                </div>
                <div>{{ isAr ? 'ضريبي:' : 'Tax:' }} {{ b.tax_number || 'N/A' }}</div>
                <div v-if="b.commercial_registry">{{ isAr ? 'سجل:' : 'CR:' }} {{ b.commercial_registry }}</div>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-1 text-zinc-800 font-medium">
                  <Anchor class="w-3.5 h-3.5 text-amber-600 shrink-0" />
                  <span class="truncate max-w-[200px]">{{ b.operating_ports || (isAr ? 'كافة الموانئ المصرية' : 'All Egyptian Ports') }}</span>
                </div>
              </td>
              <td class="px-4 py-3 font-mono text-zinc-600 text-[11px]">
                <div v-if="b.contact_person" class="font-bold text-zinc-900 font-sans flex items-center gap-1">
                  <UserCheck class="w-3.5 h-3.5 text-emerald-600" />
                  <span>{{ b.contact_person }}</span>
                </div>
                <div v-if="b.contact_phone || b.phone" class="flex items-center gap-1 mt-0.5">
                  <Phone class="w-3 h-3 text-zinc-400" />
                  <span>{{ b.contact_phone || b.phone }}</span>
                </div>
                <div v-if="b.email" class="flex items-center gap-1">
                  <Mail class="w-3 h-3 text-zinc-400" />
                  <span>{{ b.email }}</span>
                </div>
              </td>
              <td class="px-4 py-3">
                <span class="px-2 py-0.5 rounded text-[9px] font-mono font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                  {{ isAr ? 'معتمد' : 'ACCREDITED' }}
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                <div class="flex items-center justify-center gap-2">
                  <button
                    type="button"
                    @click="openEditModal(b)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-500/10 hover:bg-blue-500/20 text-blue-700 text-xs font-bold border border-blue-500/20 transition-all cursor-pointer active:scale-95 shadow-2xs"
                    :title="isAr ? 'تعديل بيانات المخلص' : 'Edit Customs Broker'"
                  >
                    <Pencil class="w-3.5 h-3.5" />
                    <span>{{ isAr ? 'تعديل' : 'Edit' }}</span>
                  </button>
                  <button
                    type="button"
                    @click="deleteBroker(b)"
                    class="inline-flex items-center p-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 transition-all cursor-pointer active:scale-95 border border-rose-500/20"
                    :title="isAr ? 'حذف المخلص' : 'Delete Broker'"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="!filteredBrokers?.length">
              <td colspan="7" class="p-8 text-center text-zinc-500 font-mono">
                {{ isAr ? 'لا يوجد مخلصين جمركيين مسجلين بعد.' : 'No customs brokers found.' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Customs Broker Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-xs">
      <div class="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl border border-zinc-200">
        <div class="p-4 border-b border-zinc-200 flex items-center justify-between bg-zinc-50">
          <h3 class="font-bold text-sm text-zinc-950 flex items-center gap-2">
            <ShieldCheck class="w-5 h-5 text-blue-600" />
            <span>{{ isAr ? 'تسجيل مخلص جمركي معتمد جديد' : 'Register New Customs Broker' }}</span>
          </h3>
          <button @click="showCreateModal = false" class="text-zinc-400 hover:text-zinc-950 text-sm font-bold">✕</button>
        </div>

        <form @submit.prevent="submitCreate" class="p-6 space-y-4">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">
                {{ isAr ? 'كود المخلص' : 'Broker Code' }}
                <span class="text-red-500">*</span>
              </label>
              <input v-model="form.code" type="text" placeholder="e.g. CBRK-ALEX" class="shadcn-input font-mono uppercase" required dir="ltr" />
            </div>
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">
                {{ isAr ? 'اسم مكتب / شركة التخليص' : 'Broker Legal Name' }}
                <span class="text-red-500">*</span>
              </label>
              <input v-model="form.name" type="text" placeholder="e.g. الأهرام للخدمات الجمركية" class="shadcn-input" required />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'رقم رخصة التخليص' : 'License #' }}</label>
              <input v-model="form.license_number" type="text" placeholder="e.g. LIC-8890" class="shadcn-input font-mono" dir="ltr" />
            </div>
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'الرقم الضريبي' : 'Tax Number' }}</label>
              <input v-model="form.tax_number" type="text" placeholder="e.g. 100-290-881" class="shadcn-input font-mono" dir="ltr" />
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'الموانئ والمنافذ المعتمد بها' : 'Operating Ports' }}</label>
            <input v-model="form.operating_ports" type="text" placeholder="e.g. ميناء الإسكندرية / ميناء الدخيلة / ميناء دمياط" class="shadcn-input" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'اسم مندوب / مسؤول الميناء' : 'Port Agent Name' }}</label>
              <input v-model="form.contact_person" type="text" placeholder="e.g. أ / محمد محمود" class="shadcn-input" />
            </div>
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'هاتف مندوب الميناء' : 'Agent Phone' }}</label>
              <input v-model="form.contact_phone" type="text" placeholder="+20 122 ..." class="shadcn-input font-mono" dir="ltr" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'البريد الإلكتروني' : 'Email' }}</label>
              <input v-model="form.email" type="email" placeholder="broker@company.com" class="shadcn-input font-mono" dir="ltr" />
            </div>
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'هاتف المكتب الرئيسي' : 'Office Phone' }}</label>
              <input v-model="form.phone" type="text" placeholder="+20 3 ..." class="shadcn-input font-mono" dir="ltr" />
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'العنوان' : 'Office Address' }}</label>
            <input v-model="form.address" type="text" placeholder="ميناء الدخيلة - مجمع مكاتب المخلصين" class="shadcn-input" />
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t border-zinc-200">
            <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-lg text-xs font-bold text-zinc-600 hover:bg-zinc-100">
              {{ isAr ? 'إلغاء' : 'Cancel' }}
            </button>
            <button type="submit" :disabled="form.processing" class="shadcn-btn-primary px-6 py-2 cursor-pointer font-bold text-xs">
              {{ isAr ? 'حفظ المخلص' : 'Save Broker' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Customs Broker Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-xs">
      <div class="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl border border-zinc-200">
        <div class="p-4 border-b border-zinc-200 flex items-center justify-between bg-zinc-50">
          <h3 class="font-bold text-sm text-zinc-950 flex items-center gap-2">
            <Pencil class="w-4 h-4 text-blue-600" />
            <span>{{ isAr ? 'تعديل بيانات المخلص الجمركي' : 'Edit Customs Broker' }}</span>
          </h3>
          <button @click="showEditModal = false" class="text-zinc-400 hover:text-zinc-950 text-sm font-bold">✕</button>
        </div>

        <form @submit.prevent="submitEdit" class="p-6 space-y-4">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">
                {{ isAr ? 'كود المخلص' : 'Broker Code' }}
                <span class="text-red-500">*</span>
              </label>
              <input v-model="editForm.code" type="text" class="shadcn-input font-mono uppercase" required dir="ltr" />
            </div>
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">
                {{ isAr ? 'اسم مكتب / شركة التخليص' : 'Broker Legal Name' }}
                <span class="text-red-500">*</span>
              </label>
              <input v-model="editForm.name" type="text" class="shadcn-input" required />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'رقم رخصة التخليص' : 'License #' }}</label>
              <input v-model="editForm.license_number" type="text" class="shadcn-input font-mono" dir="ltr" />
            </div>
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'الرقم الضريبي' : 'Tax Number' }}</label>
              <input v-model="editForm.tax_number" type="text" class="shadcn-input font-mono" dir="ltr" />
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'الموانئ والمنافذ المعتمد بها' : 'Operating Ports' }}</label>
            <input v-model="editForm.operating_ports" type="text" class="shadcn-input" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'اسم مندوب / مسؤول الميناء' : 'Port Agent Name' }}</label>
              <input v-model="editForm.contact_person" type="text" class="shadcn-input" />
            </div>
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'هاتف مندوب الميناء' : 'Agent Phone' }}</label>
              <input v-model="editForm.contact_phone" type="text" class="shadcn-input font-mono" dir="ltr" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'البريد الإلكتروني' : 'Email' }}</label>
              <input v-model="editForm.email" type="email" class="shadcn-input font-mono" dir="ltr" />
            </div>
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'هاتف المكتب الرئيسي' : 'Office Phone' }}</label>
              <input v-model="editForm.phone" type="text" class="shadcn-input font-mono" dir="ltr" />
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'العنوان' : 'Office Address' }}</label>
            <input v-model="editForm.address" type="text" class="shadcn-input" />
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t border-zinc-200">
            <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-lg text-xs font-bold text-zinc-600 hover:bg-zinc-100">
              {{ isAr ? 'إلغاء' : 'Cancel' }}
            </button>
            <button type="submit" :disabled="editForm.processing" class="shadcn-btn-primary px-6 py-2 cursor-pointer font-bold text-xs gap-1.5">
              <CheckCircle2 class="w-4 h-4" />
              <span>{{ isAr ? 'حفظ التعديلات' : 'Save Changes' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
