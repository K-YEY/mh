<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { History, Search, ShieldCheck, User, Calendar, FileText, Code2, Eye } from 'lucide-vue-next'

const { isAr } = useI18n()

const props = defineProps({
  logs: Object,
  filters: Object,
})

const search = ref(props.filters?.search || '')
const selectedProperties = ref(null)
const isModalOpen = ref(false)

const doSearch = () => {
  router.get(route('admin.audit-logs.index'), { search: search.value }, { preserveState: true })
}

const openDiffModal = (propertiesJson) => {
  try {
    selectedProperties.value = typeof propertiesJson === 'string' ? JSON.parse(propertiesJson) : propertiesJson
  } catch (e) {
    selectedProperties.value = propertiesJson
  }
  isModalOpen.value = true
}

const getEventBadgeClass = (event) => {
  switch (event) {
    case 'create':
      return 'bg-zinc-950 text-white border-zinc-950'
    case 'post':
      return 'bg-zinc-800 text-white border-zinc-800'
    case 'update':
      return 'bg-zinc-200 text-zinc-950 border-zinc-300'
    case 'delete':
    case 'void':
      return 'bg-zinc-100 text-zinc-900 border-zinc-400 font-bold'
    default:
      return 'bg-zinc-100 text-zinc-800 border-zinc-200'
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-extrabold text-zinc-950 tracking-tight flex items-center gap-2">
          <History class="w-6 h-6 text-zinc-950" />
          <span>{{ isAr ? 'سجل تتبع الحركات والرقابة على النظام' : 'Audit Trail & Mutation Logs' }}</span>
        </h1>
        <p class="text-xs text-zinc-500">
          {{ isAr ? 'سجل غير قابل للتعديل للحركات وفحص فروقات البيانات' : 'Immutable System Event Logs & State Diff Inspections' }}
        </p>
      </div>

      <div class="flex items-center gap-2">
        <span class="px-3 py-1 rounded text-xs font-mono font-bold uppercase bg-zinc-950 text-white border border-zinc-950">
          {{ isAr ? 'معتمد ISO 27001' : 'ISO 27001 Audit Ready' }}
        </span>
      </div>
    </div>

    <!-- Filters Bar -->
    <UiCard >
      <div class="relative w-full sm:w-80">
        <Search class="w-4 h-4 text-zinc-400 absolute top-2.5" :class="isAr ? 'right-3' : 'left-3'" />
        <UiInput 
          v-model="search"
          @keyup.enter="doSearch"
          type="text" 
          :placeholder="isAr ? 'بحث بالحركة، العنصر، أو الوصف...' : 'Search by event, subject, or description...'"
          :style="isAr ? 'padding-right: 2.5rem !important; padding-left: 0.75rem !important;' : 'padding-left: 2.5rem !important; padding-right: 0.75rem !important;'"
          
        / />
      </div>

      <UiButton @click="doSearch"  variant="secondary">
        {{ isAr ? 'تصفية سجل الحركات' : 'Filter Audit Logs' }}
      </UiButton>
    </div>

    <!-- Data Table -->
    <UiCard >
      <table class="w-full text-xs" :class="isAr ? 'text-right' : 'text-left'">
        <thead class="bg-zinc-100 text-zinc-700 uppercase text-[10px] tracking-wider border-b border-zinc-200 font-mono">
          <tr>
            <th class="p-4">{{ isAr ? 'تاريخ ووقت الحركة' : 'Timestamp' }}</th>
            <th class="p-4">{{ isAr ? 'المستخدم / الفاعل' : 'Actor / User' }}</th>
            <th class="p-4">{{ isAr ? 'نوع الحركة' : 'Action Event' }}</th>
            <th class="p-4">{{ isAr ? 'الكيان المتأثر' : 'Entity Subject' }}</th>
            <th class="p-4">{{ isAr ? 'الوصف التفصيلي' : 'Description' }}</th>
            <th class="p-4" :class="isAr ? 'text-left' : 'text-right'">{{ isAr ? 'فحص التغيير' : 'State Diffs' }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-zinc-200 font-sans">
          <tr v-for="log in logs.data" :key="log.id" class="hover:bg-zinc-50 transition-colors">
            <td class="p-4 font-mono text-zinc-600 text-[11px]">
              <div class="flex items-center gap-1.5">
                <Calendar class="w-3.5 h-3.5 text-zinc-400" />
                <span>{{ new Date(log.created_at).toLocaleString() }}</span>
              </div>
            </td>
            <td class="p-4 font-bold text-zinc-950">
              <div class="flex items-center gap-1.5">
                <User class="w-3.5 h-3.5 text-zinc-400" />
                <span>{{ log.causer_name || (isAr ? 'النظام تلقائياً' : 'System Auto') }}</span>
              </div>
              <div class="text-[10px] text-zinc-500 font-mono" :class="isAr ? 'pr-5' : 'pl-5'">{{ log.ip_address || '127.0.0.1' }}</div>
            </td>
            <td class="p-4">
              <span :class="['px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase border', getEventBadgeClass(log.event)]">
                {{ log.event }}
              </span>
            </td>
            <td class="p-4 font-mono text-zinc-900 font-bold">
              <div class="flex items-center gap-1">
                <FileText class="w-3.5 h-3.5 text-zinc-400" />
                <span>{{ log.subject_type ? log.subject_type.split('\\').pop() : 'N/A' }} #{{ log.subject_id }}</span>
              </div>
            </td>
            <td class="p-4 text-zinc-700 max-w-xs truncate">
              {{ log.description }}
            </td>
            <td class="p-4" :class="isAr ? 'text-left' : 'text-right'">
              <UiButton 
                @click="openDiffModal(log.properties)" 
                 variant="outline"
              >
                <Eye class="w-3.5 h-3.5 inline" :class="isAr ? 'ml-1' : 'mr-1'" />
                <span>{{ isAr ? 'فحص الفرق' : 'Inspect Diff' }}</span>
              </UiButton>
            </td>
          </tr>

          <tr v-if="!logs.data?.length">
            <td colspan="6" class="p-8 text-center text-zinc-500 font-mono">
              {{ isAr ? 'لا توجد حركات مسجلة حالياً' : 'No audit log entries recorded yet.' }}
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination Footer -->
      <div v-if="logs.links?.length > 3" class="p-4 bg-zinc-50 border-t border-zinc-200 flex items-center justify-between text-xs">
        <span class="text-zinc-500 font-mono">
          {{ isAr ? `عرض ${logs.from} إلى ${logs.to} من إجمالي ${logs.total}` : `Showing ${logs.from} to ${logs.to} of ${logs.total} logs` }}
        </span>
        <div class="flex items-center gap-1">
          <button 
            v-for="(link, i) in logs.links" 
            :key="i"
            @click="link.url && $inertia.get(link.url)"
            :disabled="!link.url"
            :class="[
              link.active ? 'bg-zinc-950 text-white font-bold' : 'bg-white text-zinc-700 hover:bg-zinc-100 border border-zinc-200',
              'px-3 py-1 rounded text-xs transition-colors cursor-pointer'
            ]"
            v-html="link.label"
          />
        </div>
      </div>
    </div>

    <!-- JSON Properties Diff Inspector Modal -->
    <div v-if="isModalOpen" class="fixed inset-0 z-50 bg-zinc-950/70 backdrop-blur-xs flex items-center justify-center p-4">
      <UiCard >
        <div class="flex items-center justify-between border-b border-zinc-200 pb-3">
          <h3 class="text-sm font-bold text-zinc-950 flex items-center gap-2">
            <Code2 class="w-4 h-4 text-zinc-950" />
            <span>{{ isAr ? 'تفاصيل وفروقات بيانات الحركة (JSON Payload Diff)' : 'Audit Payload State Diff Inspection' }}</span>
          </h3>
          <button @click="isModalOpen = false" class="text-zinc-400 hover:text-zinc-950 font-bold">✕</button>
        </div>

        <div class="flex-1 overflow-y-auto space-y-3 font-mono text-xs">
          <div class="bg-zinc-950 text-zinc-100 p-4 rounded-lg overflow-x-auto">
            <pre>{{ JSON.stringify(selectedProperties, null, 2) }}</pre>
          </div>
        </div>

        <div class="flex justify-end pt-2 border-t border-zinc-200">
          <UiButton @click="isModalOpen = false"  variant="primary">
            {{ isAr ? 'إغلاق الفاحص' : 'Close Inspector' }}
          </UiButton>
        </div>
      </div>
    </div>
  </div>
</template>
