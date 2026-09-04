<script setup>
import { ref, computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { 
  Users, 
  Plus, 
  Search, 
  Phone, 
  Mail, 
  Building2, 
  ShieldCheck, 
  Truck, 
  Anchor, 
  X,
  Filter
} from 'lucide-vue-next'

const { isAr } = useI18n()

const props = defineProps({
  parties: Array,
})

const showCreateModal = ref(false)
const searchQuery = ref('')
const selectedRole = ref('ALL')

const roleOptions = [
  { value: 'ALL', labelAr: 'كافة الأطراف', labelEn: 'All Parties' },
  { value: 'CUSTOMS_BROKER', labelAr: 'مخلصون جمركيون', labelEn: 'Customs Brokers' },
  { value: 'TRUCKER', labelAr: 'شركات النقل البري', labelEn: 'Inland Truckers' },
  { value: 'SHIPPING_LINE', labelAr: 'الخطوط الملاحية', labelEn: 'Shipping Lines' },
  { value: 'CUSTOMER', labelAr: 'العملاء والشاحنون', labelEn: 'Customers & Shippers' },
  { value: 'PACKHOUSE', labelAr: 'محطات التعبئة', labelEn: 'Packhouses' },
  { value: 'OVERSEAS_AGENT', labelAr: 'وكلاء الشحن بالخارج', labelEn: 'Overseas Agents' },
]

const form = useForm({
  code: '',
  legal_name: '',
  role: 'CUSTOMS_BROKER',
  tax_number: '',
  commercial_registry: '',
  phone: '',
  email: '',
  address: '',
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
      const matchPhone = party.phone?.toLowerCase().includes(q)
      if (!matchName && !matchCode && !matchTax && !matchPhone) return false
    }

    return true
  })
})

const roleBadgeClass = (role) => {
  switch (role) {
    case 'CUSTOMS_BROKER':
      return 'bg-zinc-100 text-zinc-900 border-zinc-300'
    case 'TRUCKER':
      return 'bg-zinc-200 text-zinc-900 border-zinc-400'
    case 'SHIPPING_LINE':
      return 'bg-zinc-950 text-white border-zinc-950'
    default:
      return 'bg-zinc-100 text-zinc-800 border-zinc-300'
  }
}

const roleName = (role) => {
  const map = {
    CUSTOMS_BROKER: isAr.value ? 'مخلص جمركي' : 'Customs Broker',
    TRUCKER: isAr.value ? 'شركة نقل بري' : 'Trucker',
    SHIPPING_LINE: isAr.value ? 'خط ملاحي' : 'Shipping Line',
    CUSTOMER: isAr.value ? 'عميل / شاحن' : 'Customer',
    PACKHOUSE: isAr.value ? 'محطة تعبئة' : 'Packhouse',
    OVERSEAS_AGENT: isAr.value ? 'وكيل خارجي' : 'Overseas Agent',
    EMPLOYEE: isAr.value ? 'موظف' : 'Employee',
  }
  return map[role] || role
}

const submitCreate = () => {
  form.post(route('parties.store'), {
    onSuccess: () => {
      showCreateModal.value = false
      form.reset()
    }
  })
}
</script>

<template>
  <div class="space-y-6 pb-12">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-extrabold text-zinc-950 tracking-tight flex items-center gap-2">
          <Users class="w-6 h-6 text-zinc-950" />
          <span>{{ isAr ? 'سجل الجهات والأطراف الموحد (Parties Ledger)' : 'Unified Stakeholder Parties' }}</span>
        </h1>
        <p class="text-xs text-zinc-500">
          {{ isAr ? 'الدليل الموحد للمخلصين الجمركيين، شركات النقل، الخطوط الملاحية، والعملاء المربوطين بالحسابات' : 'Unified directory for customs brokers, truckers, shipping lines, and subledger clients' }}
        </p>
      </div>

      <div class="flex items-center gap-2">
        <Link 
          :href="route('customs-brokers.index')" 
          class="shadcn-btn-secondary gap-1.5 cursor-pointer text-xs"
        >
          <ShieldCheck class="w-4 h-4 text-zinc-950" />
          <span>{{ isAr ? 'سجل المخلصين' : 'Customs Brokers' }}</span>
        </Link>
        <Link 
          :href="route('truckers.index')" 
          class="shadcn-btn-secondary gap-1.5 cursor-pointer text-xs"
        >
          <Truck class="w-4 h-4 text-zinc-950" />
          <span>{{ isAr ? 'شركات النقل' : 'Truckers' }}</span>
        </Link>
        <button 
          @click="showCreateModal = true"
          class="shadcn-btn-primary gap-1.5 shadow-xs cursor-pointer text-xs"
        >
          <Plus class="w-4 h-4 font-bold" />
          <span>{{ isAr ? 'إضافة جهة جديدة' : 'Add Party' }}</span>
        </button>
      </div>
    </div>

    <!-- Search & Filters Card -->
    <div class="shadcn-card p-4 space-y-3">
      <!-- Role Tabs -->
      <div class="flex items-center gap-1.5 overflow-x-auto pb-1">
        <button
          v-for="tab in roleOptions"
          :key="tab.value"
          @click="selectedRole = tab.value"
          :class="[
            'px-3 py-1 text-xs rounded font-medium whitespace-nowrap transition cursor-pointer',
            selectedRole === tab.value
              ? 'bg-zinc-950 text-white font-semibold shadow-xs'
              : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 border border-zinc-300'
          ]"
        >
          {{ isAr ? tab.labelAr : tab.labelEn }}
        </button>
      </div>

      <!-- Search Input -->
      <div class="relative">
        <Search class="w-4 h-4 text-zinc-400 absolute start-3 top-1/2 -translate-y-1/2" />
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="isAr ? 'البحث بالاسم، الكود، الرقم الضريبي، أو الهاتف...' : 'Search by name, code, tax ID, phone...'"
          class="shadcn-input ps-9 pe-3"
        />
      </div>
    </div>

    <!-- Parties Grid -->
    <div v-if="filteredParties.length === 0" class="shadcn-card p-12 text-center text-zinc-500">
      <Users class="w-8 h-8 text-zinc-300 mx-auto mb-2" />
      <div class="text-xs font-semibold text-zinc-700">
        {{ isAr ? 'لا توجد جهات مطابقة للبحث' : 'No parties found matching criteria' }}
      </div>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div 
        v-for="party in filteredParties" 
        :key="party.id"
        class="shadcn-card p-4 flex flex-col justify-between space-y-3 hover:border-zinc-400 transition-colors"
      >
        <div>
          <!-- Code & Roles -->
          <div class="flex items-start justify-between gap-2 mb-2 border-b border-zinc-100 pb-2">
            <span class="font-mono font-bold text-xs bg-zinc-100 text-zinc-900 border border-zinc-300 px-2 py-0.5 rounded">
              {{ party.code }}
            </span>
            <div class="flex flex-wrap gap-1 justify-end">
              <span 
                v-for="role in party.roles" 
                :key="role.id"
                :class="['px-1.5 py-0.2 rounded text-[9px] font-mono font-bold uppercase tracking-wider border', roleBadgeClass(role.role)]"
              >
                {{ roleName(role.role) }}
              </span>
            </div>
          </div>

          <!-- Name -->
          <h3 class="text-sm font-bold text-zinc-950">
            {{ party.legal_name }}
          </h3>

          <!-- Details -->
          <div class="mt-2 space-y-1 text-xs text-zinc-600 font-mono">
            <div v-if="party.tax_number" class="flex items-center gap-1.5">
              <span class="text-zinc-400">{{ isAr ? 'ضريبي:' : 'Tax:' }}</span>
              <span>{{ party.tax_number }}</span>
            </div>
            <div v-if="party.phone" class="flex items-center gap-1.5">
              <span class="text-zinc-400">{{ isAr ? 'هاتف:' : 'Tel:' }}</span>
              <span dir="ltr">{{ party.phone }}</span>
            </div>
            <div v-if="party.email" class="flex items-center gap-1.5 truncate">
              <span class="text-zinc-400">{{ isAr ? 'بريد:' : 'Email:' }}</span>
              <span class="truncate">{{ party.email }}</span>
            </div>
          </div>
        </div>

        <div class="pt-2 border-t border-zinc-100 flex items-center justify-between text-[11px] font-mono">
          <span :class="party.is_active ? 'text-zinc-950 font-bold' : 'text-zinc-400'">
            {{ party.is_active ? (isAr ? '● نشط' : '● Active') : (isAr ? 'غير نشط' : 'Inactive') }}
          </span>
          <span class="text-zinc-400">ID: {{ party.id }}</span>
        </div>
      </div>
    </div>

    <!-- Create Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs">
      <div class="shadcn-card max-w-lg w-full p-6 space-y-4 shadow-2xl relative">
        <button 
          @click="showCreateModal = false"
          class="absolute top-4 end-4 text-zinc-400 hover:text-zinc-950 cursor-pointer"
        >
          <X class="w-4 h-4" />
        </button>

        <h3 class="text-base font-bold text-zinc-950 flex items-center gap-2 border-b border-zinc-100 pb-3">
          <Plus class="w-4 h-4 text-zinc-950" />
          <span>{{ isAr ? 'إضافة جهة جديدة بالنظام' : 'Add New Stakeholder Party' }}</span>
        </h3>

        <form @submit.prevent="submitCreate" class="space-y-3">
          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">
              {{ isAr ? 'كود الجهة' : 'Party Code' }}
              <span class="text-red-500">*</span>
            </label>
            <input 
              v-model="form.code"
              type="text" 
              required 
              placeholder="e.g. CB-009, TRK-009" 
              class="shadcn-input font-mono uppercase"
              dir="ltr"
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">
              {{ isAr ? 'الاسم التجاري / القانوني' : 'Legal Name' }}
              <span class="text-red-500">*</span>
            </label>
            <input 
              v-model="form.legal_name"
              type="text" 
              required 
              placeholder="اسم المؤسسة أو الشركة" 
              class="shadcn-input"
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">
              {{ isAr ? 'نوع الدور الرئيسي' : 'Primary Role' }}
              <span class="text-red-500">*</span>
            </label>
            <select 
              v-model="form.role"
              required 
              class="shadcn-input"
            >
              <option value="CUSTOMS_BROKER">{{ isAr ? 'مخلص جمركي' : 'Customs Broker' }}</option>
              <option value="TRUCKER">{{ isAr ? 'شركة نقل بري' : 'Inland Trucker' }}</option>
              <option value="SHIPPING_LINE">{{ isAr ? 'توكيل / خط ملاحي' : 'Shipping Line' }}</option>
              <option value="CUSTOMER">{{ isAr ? 'عميل / شاحن / مستورد' : 'Customer / Shipper' }}</option>
              <option value="PACKHOUSE">{{ isAr ? 'محطة تعبئة زراعية' : 'Packhouse' }}</option>
              <option value="OVERSEAS_AGENT">{{ isAr ? 'وكيل خارجي' : 'Overseas Agent' }}</option>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">
                {{ isAr ? 'الرقم الضريبي' : 'Tax ID' }}
              </label>
              <input 
                v-model="form.tax_number"
                type="text" 
                placeholder="123-456-789" 
                class="shadcn-input font-mono"
                dir="ltr"
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">
                {{ isAr ? 'السجل التجاري' : 'CR Number' }}
              </label>
              <input 
                v-model="form.commercial_registry"
                type="text" 
                placeholder="رقم السجل" 
                class="shadcn-input font-mono"
                dir="ltr"
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">
                {{ isAr ? 'رقم الهاتف' : 'Phone' }}
              </label>
              <input 
                v-model="form.phone"
                type="text" 
                placeholder="010XXXXXXXX" 
                class="shadcn-input font-mono"
                dir="ltr"
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-zinc-800 mb-1">
                {{ isAr ? 'البريد الإلكتروني' : 'Email' }}
              </label>
              <input 
                v-model="form.email"
                type="email" 
                placeholder="info@company.com" 
                class="shadcn-input font-mono"
                dir="ltr"
              />
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-zinc-100">
            <button 
              type="button"
              @click="showCreateModal = false"
              class="shadcn-btn-ghost text-xs cursor-pointer"
            >
              {{ isAr ? 'إلغاء' : 'Cancel' }}
            </button>
            <button 
              type="submit"
              :disabled="form.processing"
              class="shadcn-btn-primary text-xs cursor-pointer disabled:opacity-50"
            >
              {{ form.processing ? (isAr ? 'جاري الحفظ...' : 'Saving...') : (isAr ? 'حفظ الجهة' : 'Save Party') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
