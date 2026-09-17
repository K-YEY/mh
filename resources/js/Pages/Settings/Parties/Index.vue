<script setup>
import { ref, computed } from 'vue'
import { useForm, router, Link } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { 
  Building2, 
  Plus, 
  Search, 
  Phone, 
  Mail, 
  ShieldCheck, 
  Truck, 
  Anchor, 
  X, 
  Pencil, 
  Trash2, 
  CheckCircle2, 
  BookOpen, 
  FileSpreadsheet, 
  Copy, 
  Check, 
  Sparkles,
  ArrowUpRight,
  ChevronRight,
  Globe2,
  PackageCheck
} from 'lucide-vue-next'

const { isAr } = useI18n()

const props = defineProps({
  parties: Array,
  accounts: Array,
})

const showCreateModal = ref(false)
const showEditModal = ref(false)
const searchQuery = ref('')
const selectedRole = ref('ALL')
const copiedId = ref(null)

// Logical role categories
const roleCategories = [
  { value: 'ALL', labelAr: 'الكل', labelEn: 'All' },
  { value: 'CUSTOMER', labelAr: 'العملاء والشاحنون', labelEn: 'Customers' },
  { value: 'SHIPPING_LINE', labelAr: 'خطوط الشحن', labelEn: 'Shipping Lines' },
  { value: 'CUSTOMS_BROKER', labelAr: 'التخليص الجمركي', labelEn: 'Customs' },
  { value: 'TRUCKER', labelAr: 'شركات النقل', labelEn: 'Truckers' },
  { value: 'OVERSEAS_AGENT', labelAr: 'وكلاء الشحن', labelEn: 'Agents' },
]

const form = useForm({
  code: '',
  legal_name: '',
  role: 'CUSTOMER',
  tax_number: '',
  phone: '',
  email: '',
  address: '',
  account_id: '',
  auto_create_account: true,
})

const editForm = useForm({
  id: null,
  code: '',
  legal_name: '',
  role: 'CUSTOMER',
  tax_number: '',
  phone: '',
  email: '',
  address: '',
  account_id: '',
  auto_create_account: false,
  is_active: true,
})

const filteredParties = computed(() => {
  return (props.parties || []).filter(party => {
    if (selectedRole.value !== 'ALL') {
      const hasRole = party.roles?.some(r => r.role === selectedRole.value)
      if (!hasRole) return false
    }

    if (searchQuery.value) {
      const q = searchQuery.value.toLowerCase()
      const matchName = party.legal_name?.toLowerCase().includes(q)
      const matchCode = party.code?.toLowerCase().includes(q)
      const matchTax = party.tax_number?.toLowerCase().includes(q)
      const matchAcc = party.account?.code?.includes(q) || party.account?.name_ar?.toLowerCase().includes(q)
      if (!matchName && !matchCode && !matchTax && !matchAcc) return false
    }

    return true
  })
})

const roleBadge = (role) => {
  switch (role) {
    case 'CUSTOMER':
      return { bg: 'bg-emerald-50 text-emerald-700', label: isAr.value ? 'عميل' : 'Customer' }
    case 'SHIPPING_LINE':
      return { bg: 'bg-blue-50 text-blue-700', label: isAr.value ? 'خط ملاحي' : 'Carrier' }
    case 'CUSTOMS_BROKER':
      return { bg: 'bg-cyan-50 text-cyan-700', label: isAr.value ? 'مخلص جمركي' : 'Customs' }
    case 'TRUCKER':
      return { bg: 'bg-amber-50 text-amber-700', label: isAr.value ? 'نقل بري' : 'Trucker' }
    case 'OVERSEAS_AGENT':
      return { bg: 'bg-purple-50 text-purple-700', label: isAr.value ? 'وكيل' : 'Agent' }
    default:
      return { bg: 'bg-zinc-100 text-zinc-700', label: isAr.value ? 'جهة' : 'Partner' }
  }
}

const getRoleIcon = (role) => {
  switch (role) {
    case 'SHIPPING_LINE': return Anchor
    case 'TRUCKER': return Truck
    case 'CUSTOMS_BROKER': return ShieldCheck
    case 'CUSTOMER': return Building2
    default: return Globe2
  }
}

const copyAccount = (text, id) => {
  navigator.clipboard.writeText(text)
  copiedId.value = id
  setTimeout(() => { copiedId.value = null }, 1800)
}

const submitCreate = () => {
  form.post(route('parties.store'), {
    onSuccess: () => {
      showCreateModal.value = false
      form.reset()
      form.auto_create_account = true
    }
  })
}

const openEditModal = (party) => {
  editForm.id = party.id
  editForm.code = party.code
  editForm.legal_name = party.legal_name
  editForm.role = party.roles?.[0]?.role || 'CUSTOMER'
  editForm.tax_number = party.tax_number || ''
  editForm.phone = party.phone || ''
  editForm.email = party.email || ''
  editForm.address = party.address || ''
  editForm.account_id = party.account_id || ''
  editForm.auto_create_account = false
  editForm.is_active = party.is_active !== false
  showEditModal.value = true
}

const submitEdit = () => {
  editForm.put(route('parties.update', editForm.id), {
    onSuccess: () => {
      showEditModal.value = false
    }
  })
}

const deleteParty = (party) => {
  if (confirm(isAr.value ? `حذف (${party.legal_name})؟` : `Delete (${party.legal_name})?`)) {
    router.delete(route('parties.destroy', party.id))
  }
}
</script>

<template>
  <div class="max-w-7xl mx-auto space-y-6 pb-20">
    <!-- Apple Serene Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-2">
      <div>
        <h1 class="text-2xl font-bold text-zinc-950 tracking-tight flex items-center gap-2.5">
          <Building2 class="w-6 h-6 text-zinc-900" />
          <span>{{ isAr ? 'حسابات الشركات والجهات' : 'Company & Partner Accounts' }}</span>
        </h1>
        <p class="text-xs text-zinc-500 mt-1">
          {{ isAr ? 'كل شركة بحساب مالي مستقل في الدفتر المساعد مع إمكانية استخراج كشف الحساب بضغطة زر' : 'Isolated financial accounts for each client, shipping line, customs broker, and trucker' }}
        </p>
      </div>

      <div class="flex items-center gap-2">
        <Link 
          :href="route('charge-items.index')" 
          class="px-3.5 py-2 rounded-xl text-xs font-semibold text-zinc-700 bg-zinc-100 hover:bg-zinc-200 transition"
        >
          {{ isAr ? 'خطوات التكاليف' : 'Charge Steps' }}
        </Link>
        <button 
          @click="showCreateModal = true"
          class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-zinc-950 hover:bg-zinc-800 shadow-sm transition cursor-pointer"
        >
          <Plus class="w-4 h-4" />
          <span>{{ isAr ? 'إضافة شركة جديدة' : 'Add Company' }}</span>
        </button>
      </div>
    </div>

    <!-- Apple Segmented Filter & Search Bar -->
    <div class="bg-white/80 backdrop-blur-md p-2 rounded-2xl border border-zinc-200/80 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <!-- Calm Segmented Tabs -->
      <div class="flex items-center gap-1 overflow-x-auto p-1 scrollbar-none">
        <button
          v-for="cat in roleCategories"
          :key="cat.value"
          @click="selectedRole = cat.value"
          :class="[
            'px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition cursor-pointer',
            selectedRole === cat.value
              ? 'bg-zinc-950 text-white shadow-xs'
              : 'text-zinc-600 hover:text-zinc-950 hover:bg-zinc-100'
          ]"
        >
          {{ isAr ? cat.labelAr : cat.labelEn }}
          <span 
            v-if="cat.value !== 'ALL'" 
            class="ms-1 text-[10px] opacity-75 font-mono"
          >
            ({{ (props.parties || []).filter(p => p.roles?.some(r => r.role === cat.value)).length }})
          </span>
        </button>
      </div>

      <!-- Clean Search -->
      <div class="relative min-w-[240px] px-1">
        <Search class="w-3.5 h-3.5 text-zinc-400 absolute start-3.5 top-1/2 -translate-y-1/2" />
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="isAr ? 'بحث بالاسم، الكود، أو رقم الحساب...' : 'Search by name, code, or account...'"
          class="w-full bg-zinc-100/80 border-none rounded-xl text-xs ps-8 pe-8 py-2 text-zinc-900 placeholder:text-zinc-400 focus:bg-white focus:ring-2 focus:ring-zinc-950/10 transition"
        />
        <button 
          v-if="searchQuery" 
          @click="searchQuery = ''" 
          class="absolute end-3.5 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600"
        >
          <X class="w-3.5 h-3.5" />
        </button>
      </div>
    </div>

    <!-- Cards Grid (Clean, Spacious, Apple Minimalist) -->
    <div v-if="filteredParties.length === 0" class="bg-white rounded-3xl p-16 text-center border border-zinc-200/80">
      <Building2 class="w-10 h-10 text-zinc-300 mx-auto mb-2" />
      <div class="text-sm font-bold text-zinc-800">
        {{ isAr ? 'لا توجد شركات مطابقة' : 'No companies found' }}
      </div>
      <p class="text-xs text-zinc-500 mt-1">
        {{ isAr ? 'جرب البحث باسم آخر أو أضف شركة جديدة.' : 'Try another query or register a new company.' }}
      </p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="party in filteredParties"
        :key="party.id"
        class="bg-white rounded-2xl p-5 border border-zinc-200/80 hover:border-zinc-300 hover:shadow-md transition-all duration-200 flex flex-col justify-between space-y-4 group"
      >
        <div class="space-y-3">
          <!-- Top Line: Role Icon + Role Badge + Party Code -->
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-xl bg-zinc-100 flex items-center justify-center text-zinc-700 group-hover:bg-zinc-950 group-hover:text-white transition-colors">
                <component :is="getRoleIcon(party.roles?.[0]?.role)" class="w-4 h-4" />
              </div>
              <span class="font-mono text-xs font-bold text-zinc-700 bg-zinc-100 px-2 py-0.5 rounded-lg">
                {{ party.code }}
              </span>
            </div>

            <span 
              class="px-2.5 py-0.5 rounded-full text-[11px] font-bold"
              :class="roleBadge(party.roles?.[0]?.role).bg"
            >
              {{ roleBadge(party.roles?.[0]?.role).label }}
            </span>
          </div>

          <!-- Company Name -->
          <div>
            <h3 class="text-base font-bold text-zinc-950 leading-snug tracking-tight">
              {{ party.legal_name }}
            </h3>
            <p v-if="party.tax_number" class="text-[11px] text-zinc-400 font-mono mt-0.5">
              {{ isAr ? 'ضريبي:' : 'Tax:' }} {{ party.tax_number }}
            </p>
          </div>

          <!-- Dedicated Account Card -->
          <div 
            v-if="party.account"
            class="bg-zinc-50 hover:bg-zinc-100/80 rounded-xl p-3 border border-zinc-200/70 transition flex items-center justify-between gap-2"
          >
            <div class="flex items-center gap-2 overflow-hidden">
              <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
              <span class="font-mono text-xs font-bold text-zinc-950 bg-white px-1.5 py-0.5 rounded border border-zinc-200 shadow-2xs shrink-0">
                {{ party.account.code }}
              </span>
              <span class="text-xs text-zinc-700 truncate font-medium">
                {{ isAr ? party.account.name_ar : party.account.name_en }}
              </span>
            </div>

            <button
              @click="copyAccount(party.account.code, party.id)"
              class="text-zinc-400 hover:text-zinc-700 p-1 cursor-pointer shrink-0"
              :title="isAr ? 'نسخ رقم الحساب' : 'Copy account code'"
            >
              <Check v-if="copiedId === party.id" class="w-3.5 h-3.5 text-emerald-600" />
              <Copy v-else class="w-3.5 h-3.5" />
            </button>
          </div>

          <!-- Unlinked Prompt -->
          <div 
            v-else
            class="bg-amber-50/60 rounded-xl p-2.5 border border-amber-200/60 flex items-center justify-between text-xs text-amber-900"
          >
            <span class="text-[11px] font-medium">{{ isAr ? 'حساب مجمع عام' : 'General control account' }}</span>
            <button
              type="button"
              @click="openEditModal(party)"
              class="text-[11px] font-bold underline hover:text-amber-950 cursor-pointer"
            >
              {{ isAr ? 'تخصيص حساب' : 'Assign sub-account' }}
            </button>
          </div>
        </div>

        <!-- Bottom Action Row: Statement Button + Edit -->
        <div class="pt-3 border-t border-zinc-100 flex items-center justify-between gap-2">
          <!-- Big Calm Statement Button -->
          <Link
            :href="route('ledger.statement', { party_id: party.id })"
            class="flex-1 inline-flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl text-xs font-bold text-zinc-950 bg-zinc-100 hover:bg-zinc-200 transition cursor-pointer"
          >
            <FileSpreadsheet class="w-3.5 h-3.5 text-zinc-700" />
            <span>{{ isAr ? 'كشف الحساب' : 'Statement' }}</span>
          </Link>

          <button
            type="button"
            @click="openEditModal(party)"
            class="p-2 rounded-xl text-zinc-500 hover:text-zinc-950 hover:bg-zinc-100 transition cursor-pointer"
            :title="isAr ? 'تعديل' : 'Edit'"
          >
            <Pencil class="w-3.5 h-3.5" />
          </button>
          <button
            type="button"
            @click="deleteParty(party)"
            class="p-2 rounded-xl text-zinc-400 hover:text-red-600 hover:bg-red-50 transition cursor-pointer"
            :title="isAr ? 'حذف' : 'Delete'"
          >
            <Trash2 class="w-3.5 h-3.5" />
          </button>
        </div>
      </div>
    </div>

    <!-- Apple-Style Simple Modal: Add Party -->
    <div 
      v-if="showCreateModal" 
      class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-zinc-200 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-zinc-100 pb-3">
          <h3 class="text-base font-bold text-zinc-950">
            {{ isAr ? 'إضافة شركة / جهة جديدة' : 'Add New Company' }}
          </h3>
          <button 
            @click="showCreateModal = false" 
            class="w-7 h-7 rounded-full bg-zinc-100 hover:bg-zinc-200 flex items-center justify-center text-zinc-600 cursor-pointer"
          >
            ✕
          </button>
        </div>

        <form @submit.prevent="submitCreate" class="space-y-4 text-xs">
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
              <label class="block font-bold text-zinc-800 mb-1">{{ isAr ? 'الكود' : 'Code' }} *</label>
              <input 
                v-model="form.code"
                type="text" 
                required 
                placeholder="CLI-001" 
                class="w-full bg-zinc-100/80 rounded-xl px-3 py-2 text-xs font-mono uppercase focus:bg-white focus:ring-2 focus:ring-zinc-950/10"
              />
            </div>
            <div class="sm:col-span-2">
              <label class="block font-bold text-zinc-800 mb-1">{{ isAr ? 'اسم الشركة' : 'Company Name' }} *</label>
              <input 
                v-model="form.legal_name"
                type="text" 
                required 
                placeholder="شركة الفيروز للشحن والتصدير" 
                class="w-full bg-zinc-100/80 rounded-xl px-3 py-2 text-xs focus:bg-white focus:ring-2 focus:ring-zinc-950/10"
              />
            </div>
          </div>

          <div>
            <label class="block font-bold text-zinc-800 mb-1">{{ isAr ? 'نوع الجهة' : 'Role' }} *</label>
            <select 
              v-model="form.role" 
              class="w-full bg-zinc-100/80 rounded-xl px-3 py-2 text-xs focus:bg-white focus:ring-2 focus:ring-zinc-950/10 cursor-pointer"
              required
            >
              <option value="CUSTOMER">{{ isAr ? 'عميل / شركة شاحنة ومستوردة' : 'Customer / Shipper' }}</option>
              <option value="SHIPPING_LINE">{{ isAr ? 'خط ملاحي (Carrier)' : 'Shipping Line' }}</option>
              <option value="CUSTOMS_BROKER">{{ isAr ? 'مكتب تخليص جمركي' : 'Customs Broker' }}</option>
              <option value="TRUCKER">{{ isAr ? 'شركة نقل بري وشاحنات' : 'Trucking Company' }}</option>
              <option value="OVERSEAS_AGENT">{{ isAr ? 'وكيل شحن خارجي' : 'Overseas Agent' }}</option>
              <option value="PACKHOUSE">{{ isAr ? 'محطة تعبئة' : 'Packhouse' }}</option>
            </select>
          </div>

          <!-- Apple Toggle for Automatic Sub-Account -->
          <div class="bg-zinc-50 rounded-2xl p-4 border border-zinc-200/80 space-y-2">
            <label class="flex items-start gap-3 cursor-pointer">
              <input 
                type="checkbox" 
                v-model="form.auto_create_account" 
                class="mt-1 w-4 h-4 rounded text-zinc-950 focus:ring-0 cursor-pointer"
              />
              <div>
                <span class="text-xs font-bold text-zinc-950 block">
                  {{ isAr ? 'إنشاء حساب مالي فرعي تلقائياً للشركة' : 'Auto-create dedicated financial account' }}
                </span>
                <span class="text-[11px] text-zinc-500 block mt-0.5 leading-relaxed">
                  {{ isAr ? 'يقوم بإنشاء حساب مستقل في الدليل (مثل 11210 للعملاء، 21100 للخطوط، 21300 للجمارك) لعزل الحسابات تماماً.' : 'Creates an isolated GL sub-account under the appropriate parent.' }}
                </span>
              </div>
            </label>

            <div v-if="!form.auto_create_account" class="pt-2">
              <label class="block font-semibold text-zinc-700 mb-1">
                {{ isAr ? 'أو اختر حساباً يدوياً:' : 'Or pick an account:' }}
              </label>
              <select 
                v-model="form.account_id" 
                class="w-full bg-white rounded-xl px-3 py-2 text-xs border border-zinc-200 cursor-pointer"
              >
                <option value="">{{ isAr ? '-- اختر الحساب من الدليل --' : '-- Choose Account --' }}</option>
                <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
                  [{{ acc.code }}] {{ isAr ? acc.name_ar : acc.name_en }}
                </option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-zinc-800 mb-1">{{ isAr ? 'الرقم الضريبي (اختياري)' : 'Tax ID' }}</label>
              <input v-model="form.tax_number" type="text" class="w-full bg-zinc-100/80 rounded-xl px-3 py-2 text-xs font-mono" />
            </div>
            <div>
              <label class="block font-bold text-zinc-800 mb-1">{{ isAr ? 'رقم الهاتف (اختياري)' : 'Phone' }}</label>
              <input v-model="form.phone" type="text" class="w-full bg-zinc-100/80 rounded-xl px-3 py-2 text-xs font-mono" />
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-zinc-100">
            <button 
              type="button" 
              @click="showCreateModal = false" 
              class="px-4 py-2 rounded-xl text-xs font-semibold text-zinc-600 hover:bg-zinc-100 cursor-pointer"
            >
              {{ isAr ? 'إلغاء' : 'Cancel' }}
            </button>
            <button 
              type="submit" 
              :disabled="form.processing"
              class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-zinc-950 hover:bg-zinc-800 cursor-pointer disabled:opacity-50"
            >
              {{ isAr ? 'حفظ الشركة' : 'Save Company' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Modal -->
    <div 
      v-if="showEditModal" 
      class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-zinc-200 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-zinc-100 pb-3">
          <h3 class="text-base font-bold text-zinc-950">
            {{ editForm.legal_name }} ({{ editForm.code }})
          </h3>
          <button 
            @click="showEditModal = false" 
            class="w-7 h-7 rounded-full bg-zinc-100 hover:bg-zinc-200 flex items-center justify-center text-zinc-600 cursor-pointer"
          >
            ✕
          </button>
        </div>

        <form @submit.prevent="submitEdit" class="space-y-4 text-xs">
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
              <label class="block font-bold text-zinc-800 mb-1">{{ isAr ? 'الكود' : 'Code' }} *</label>
              <input v-model="editForm.code" type="text" required class="w-full bg-zinc-100/80 rounded-xl px-3 py-2 text-xs font-mono uppercase" />
            </div>
            <div class="sm:col-span-2">
              <label class="block font-bold text-zinc-800 mb-1">{{ isAr ? 'اسم الشركة' : 'Company Name' }} *</label>
              <input v-model="editForm.legal_name" type="text" required class="w-full bg-zinc-100/80 rounded-xl px-3 py-2 text-xs" />
            </div>
          </div>

          <div>
            <label class="block font-bold text-zinc-800 mb-1">{{ isAr ? 'نوع الجهة' : 'Role' }} *</label>
            <select v-model="editForm.role" class="w-full bg-zinc-100/80 rounded-xl px-3 py-2 text-xs cursor-pointer" required>
              <option value="CUSTOMER">{{ isAr ? 'عميل / شركة شاحنة ومستوردة' : 'Customer / Shipper' }}</option>
              <option value="SHIPPING_LINE">{{ isAr ? 'خط ملاحي (Carrier)' : 'Shipping Line' }}</option>
              <option value="CUSTOMS_BROKER">{{ isAr ? 'مكتب تخليص جمركي' : 'Customs Broker' }}</option>
              <option value="TRUCKER">{{ isAr ? 'شركة نقل بري وشاحنات' : 'Trucking Company' }}</option>
              <option value="OVERSEAS_AGENT">{{ isAr ? 'وكيل شحن خارجي' : 'Overseas Agent' }}</option>
              <option value="PACKHOUSE">{{ isAr ? 'محطة تعبئة' : 'Packhouse' }}</option>
            </select>
          </div>

          <!-- Financial Account Selection -->
          <div class="bg-zinc-50 rounded-2xl p-4 border border-zinc-200/80 space-y-2">
            <label class="block font-bold text-zinc-950 text-xs">
              {{ isAr ? 'الحساب المالي المرتبط بالشركة' : 'Linked Financial Account' }}
            </label>
            <select 
              v-model="editForm.account_id" 
              class="w-full bg-white rounded-xl px-3 py-2 text-xs border border-zinc-200 cursor-pointer"
            >
              <option value="">{{ isAr ? '-- غير محدد (يستخدم الحساب المجمع الافتراضي) --' : '-- Default Control Account --' }}</option>
              <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
                [{{ acc.code }}] {{ isAr ? acc.name_ar : acc.name_en }} ({{ acc.type }})
              </option>
            </select>

            <div v-if="!editForm.account_id" class="pt-1">
              <label class="flex items-center gap-2 cursor-pointer">
                <input 
                  type="checkbox" 
                  v-model="editForm.auto_create_account" 
                  class="w-4 h-4 rounded text-zinc-950" 
                />
                <span class="text-xs font-semibold text-zinc-800">
                  {{ isAr ? 'توليد وفتح حساب مستقل لهذه الشركة الآن' : 'Create dedicated sub-account now' }}
                </span>
              </label>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-zinc-800 mb-1">{{ isAr ? 'الرقم الضريبي' : 'Tax ID' }}</label>
              <input v-model="editForm.tax_number" type="text" class="w-full bg-zinc-100/80 rounded-xl px-3 py-2 text-xs font-mono" />
            </div>
            <div>
              <label class="block font-bold text-zinc-800 mb-1">{{ isAr ? 'رقم الهاتف' : 'Phone' }}</label>
              <input v-model="editForm.phone" type="text" class="w-full bg-zinc-100/80 rounded-xl px-3 py-2 text-xs font-mono" />
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-zinc-100">
            <button 
              type="button" 
              @click="showEditModal = false" 
              class="px-4 py-2 rounded-xl text-xs font-semibold text-zinc-600 hover:bg-zinc-100 cursor-pointer"
            >
              {{ isAr ? 'إلغاء' : 'Cancel' }}
            </button>
            <button 
              type="submit" 
              :disabled="editForm.processing"
              class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-zinc-950 hover:bg-zinc-800 cursor-pointer disabled:opacity-50"
            >
              {{ isAr ? 'حفظ التعديلات' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
