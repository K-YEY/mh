<script setup>
import { ref, reactive } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { 
  Ship, Key, Check, Copy, Eye, EyeOff, RefreshCw, Radio, 
  CheckCircle2, XCircle, AlertCircle, ExternalLink, ShieldCheck, Globe, Cpu
} from 'lucide-vue-next'

const { isAr } = useI18n()
const page = usePage()

const props = defineProps({
  settings: {
    type: Object,
    default: () => ({})
  },
  webhookUrls: {
    type: Object,
    default: () => ({})
  }
})

// Active Tab
const activeTab = ref('shipsgo')

// Masking Toggle States
const showSecrets = reactive({
  shipsgo_api_key: false,
  shipsgo_webhook_secret: false,
  searates_api_key: false,
  searates_customer_id: false,
  vizion_api_key: false,
  vizion_webhook_secret: false,
  findteu_api_token: false,
})

// Copy Feedback
const copiedWebhook = ref(null)

const copyToClipboard = (text, key) => {
  if (!text) return
  navigator.clipboard.writeText(text)
  copiedWebhook.value = key
  setTimeout(() => {
    copiedWebhook.value = null
  }, 2000)
}

// Form state
const form = useForm({
  shipsgo: {
    is_enabled: props.settings.integration_shipsgo?.is_enabled === 'true' || props.settings.integration_shipsgo?.is_enabled === true,
    api_key: props.settings.integration_shipsgo?.api_key || '',
    webhook_secret: props.settings.integration_shipsgo?.webhook_secret || '',
    environment: props.settings.integration_shipsgo?.environment || 'sandbox',
  },
  searates: {
    is_enabled: props.settings.integration_searates?.is_enabled === 'true' || props.settings.integration_searates?.is_enabled === true,
    api_key: props.settings.integration_searates?.api_key || '',
    customer_id: props.settings.integration_searates?.customer_id || '',
    environment: props.settings.integration_searates?.environment || 'sandbox',
  },
  vizion: {
    is_enabled: props.settings.integration_vizion?.is_enabled === 'true' || props.settings.integration_vizion?.is_enabled === true,
    api_key: props.settings.integration_vizion?.api_key || '',
    webhook_secret: props.settings.integration_vizion?.webhook_secret || '',
    environment: props.settings.integration_vizion?.environment || 'sandbox',
  },
  findteu: {
    is_enabled: props.settings.integration_findteu?.is_enabled === 'true' || props.settings.integration_findteu?.is_enabled === true,
    api_token: props.settings.integration_findteu?.api_token || '',
    environment: props.settings.integration_findteu?.environment || 'sandbox',
  }
})

const saveSettings = () => {
  form.post(route('admin.settings.integrations.update'), {
    preserveScroll: true,
  })
}

// Test Connection States
const testingProvider = ref(null)
const testResults = reactive({
  shipsgo: null,
  searates: null,
  vizion: null,
  findteu: null,
})

const testConnection = async (provider) => {
  testingProvider.value = provider
  testResults[provider] = null

  try {
    const response = await fetch(route('admin.settings.integrations.test', { provider }), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': page.props.csrf_token || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        container_number: 'MSKU9012345',
        shipping_line_code: 'MAERSK',
      }),
    })

    const data = await response.json()
    testResults[provider] = {
      success: response.ok && data.success,
      message: data.message || (response.ok ? 'Connection established successfully.' : 'Connection test failed.'),
      details: data.details || null,
    }
  } catch (err) {
    testResults[provider] = {
      success: false,
      message: isAr.value ? 'فشل الاتصال الخارجي بالخادم.' : 'Failed to reach external endpoint.',
      details: null,
    }
  } finally {
    testingProvider.value = null
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-extrabold text-zinc-950 tracking-tight flex items-center gap-2.5">
          <Cpu class="w-6 h-6 text-zinc-950" />
          <span>{{ isAr ? 'محرك الربط والتتبع الآلي للحاويات (Logistics API Engine)' : 'Third-Party Ocean Logistics API Engine' }}</span>
        </h1>
        <p class="text-xs text-zinc-500 mt-1">
          {{ isAr ? 'إدارة مفاتيح API المشفرة، وإشعارات الويب (Webhooks)، واختبار جودة الاتصال بمزودي خدمات الشحن البحري العالمي' : 'Configure encrypted credentials, live tracking webhooks, and test carrier API connectivity' }}
        </p>
      </div>

      <UiButton
        @click="saveSettings"
        :disabled="form.processing"
         variant="primary"
      >
        <ShieldCheck class="w-4 h-4" />
        <span>{{ form.processing ? (isAr ? 'جاري الحفظ...' : 'Saving...') : (isAr ? 'حفظ إعدادات الربط' : 'Save Integration Settings') }}</span>
      </UiButton>
    </div>

    <!-- Main Container Card with Tabs -->
    <UiCard >
      <!-- Tabs Header -->
      <div class="border-b border-zinc-200 bg-zinc-50/80 px-4 pt-3 flex items-center gap-2 overflow-x-auto">
        <button
          @click="activeTab = 'shipsgo'"
          :class="[
            activeTab === 'shipsgo'
              ? 'border-zinc-950 text-zinc-950 bg-white font-extrabold shadow-2xs'
              : 'border-transparent text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100/70 font-semibold',
            'flex items-center gap-2 px-4 py-2.5 text-xs rounded-t-lg border-b-2 transition-all cursor-pointer whitespace-nowrap'
          ]"
        >
          <Ship class="w-4 h-4" />
          <span>ShipsGo API</span>
          <span 
            :class="form.shipsgo.is_enabled ? 'bg-zinc-950 text-white' : 'bg-zinc-200 text-zinc-600'"
            class="px-1.5 py-0.2 rounded text-[9px] font-mono font-bold"
          >
            {{ form.shipsgo.is_enabled ? (isAr ? 'مفعل' : 'ENABLED') : (isAr ? 'معطل' : 'OFF') }}
          </span>
        </button>

        <button
          @click="activeTab = 'searates'"
          :class="[
            activeTab === 'searates'
              ? 'border-zinc-950 text-zinc-950 bg-white font-extrabold shadow-2xs'
              : 'border-transparent text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100/70 font-semibold',
            'flex items-center gap-2 px-4 py-2.5 text-xs rounded-t-lg border-b-2 transition-all cursor-pointer whitespace-nowrap'
          ]"
        >
          <Globe class="w-4 h-4" />
          <span>SeaRates API</span>
          <span 
            :class="form.searates.is_enabled ? 'bg-zinc-950 text-white' : 'bg-zinc-200 text-zinc-600'"
            class="px-1.5 py-0.2 rounded text-[9px] font-mono font-bold"
          >
            {{ form.searates.is_enabled ? (isAr ? 'مفعل' : 'ENABLED') : (isAr ? 'معطل' : 'OFF') }}
          </span>
        </button>

        <button
          @click="activeTab = 'vizion'"
          :class="[
            activeTab === 'vizion'
              ? 'border-zinc-950 text-zinc-950 bg-white font-extrabold shadow-2xs'
              : 'border-transparent text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100/70 font-semibold',
            'flex items-center gap-2 px-4 py-2.5 text-xs rounded-t-lg border-b-2 transition-all cursor-pointer whitespace-nowrap'
          ]"
        >
          <Radio class="w-4 h-4" />
          <span>Vizion 4.0</span>
          <span 
            :class="form.vizion.is_enabled ? 'bg-zinc-950 text-white' : 'bg-zinc-200 text-zinc-600'"
            class="px-1.5 py-0.2 rounded text-[9px] font-mono font-bold"
          >
            {{ form.vizion.is_enabled ? (isAr ? 'مفعل' : 'ENABLED') : (isAr ? 'معطل' : 'OFF') }}
          </span>
        </button>

        <button
          @click="activeTab = 'findteu'"
          :class="[
            activeTab === 'findteu'
              ? 'border-zinc-950 text-zinc-950 bg-white font-extrabold shadow-2xs'
              : 'border-transparent text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100/70 font-semibold',
            'flex items-center gap-2 px-4 py-2.5 text-xs rounded-t-lg border-b-2 transition-all cursor-pointer whitespace-nowrap'
          ]"
        >
          <Cpu class="w-4 h-4" />
          <span>FindTEU / Track-Trace</span>
          <span 
            :class="form.findteu.is_enabled ? 'bg-zinc-950 text-white' : 'bg-zinc-200 text-zinc-600'"
            class="px-1.5 py-0.2 rounded text-[9px] font-mono font-bold"
          >
            {{ form.findteu.is_enabled ? (isAr ? 'مفعل' : 'ENABLED') : (isAr ? 'معطل' : 'OFF') }}
          </span>
        </button>
      </div>

      <!-- Tab Content Area -->
      <div class="p-6 space-y-6">
        
        <!-- Tab 1: ShipsGo -->
        <div v-if="activeTab === 'shipsgo'" class="space-y-6">
          <div class="flex items-center justify-between p-4 bg-zinc-50 rounded-lg border border-zinc-200">
            <div class="space-y-0.5">
              <h3 class="text-sm font-extrabold text-zinc-950 flex items-center gap-2">
                <span>ShipsGo Container Tracking & Delay Alerts</span>
              </h3>
              <p class="text-xs text-zinc-500">
                {{ isAr ? 'تتبع صعود وتفريغ الحاويات وتنبيهات تأخير الشحنات' : 'Real-time container tracking, delay risk scoring and port milestones' }}
              </p>
            </div>
            
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="form.shipsgo.is_enabled" class="sr-only peer">
              <div class="w-11 h-6 bg-zinc-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-zinc-950"></div>
              <span class="ms-2 text-xs font-bold text-zinc-950">{{ form.shipsgo.is_enabled ? (isAr ? 'مفعّل' : 'Active') : (isAr ? 'معطّل' : 'Disabled') }}</span>
            </label>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- API Key -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-zinc-800">{{ isAr ? 'مفتاح API الخاص بـ ShipsGo' : 'ShipsGo API Key' }}</label>
              <div class="relative">
                <UiInput
                  :type="showSecrets.shipsgo_api_key ? 'text' : 'password'"
                  v-model="form.shipsgo.api_key"
                  placeholder="e.g. sg_live_79a8f..."
                  
                  dir="ltr"
                / />
                <button
                  type="button"
                  @click="showSecrets.shipsgo_api_key = !showSecrets.shipsgo_api_key"
                  class="absolute right-2.5 top-2.5 text-zinc-400 hover:text-zinc-950 cursor-pointer"
                >
                  <component :is="showSecrets.shipsgo_api_key ? EyeOff : Eye" class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- Webhook Secret -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-zinc-800">{{ isAr ? 'مفتاح التحقق من الويب هوك (Webhook Secret)' : 'Webhook Verification Secret' }}</label>
              <div class="relative">
                <UiInput
                  :type="showSecrets.shipsgo_webhook_secret ? 'text' : 'password'"
                  v-model="form.shipsgo.webhook_secret"
                  placeholder="e.g. whsec_908f2..."
                  
                  dir="ltr"
                / />
                <button
                  type="button"
                  @click="showSecrets.shipsgo_webhook_secret = !showSecrets.shipsgo_webhook_secret"
                  class="absolute right-2.5 top-2.5 text-zinc-400 hover:text-zinc-950 cursor-pointer"
                >
                  <component :is="showSecrets.shipsgo_webhook_secret ? EyeOff : Eye" class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- Environment Mode -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-zinc-800">{{ isAr ? 'بيئة التشغيل' : 'Environment Mode' }}</label>
              <UiSelect v-model="form.shipsgo.environment" >
                <option value="sandbox">Sandbox (تجريبي)</option>
                <option value="live">Live (إنتاج لايف)</option>
              </UiSelect>
            </div>

            <!-- Webhook URL Display -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-zinc-800">{{ isAr ? 'رابط الويب هوك الخاص بنظامك' : 'Webhook Ingestion Endpoint' }}</label>
              <div class="flex items-center gap-1.5">
                <UiInput
                  type="text"
                  readonly
                  :value="webhookUrls.shipsgo"
                  
                  dir="ltr"
                / />
                <UiButton
                  type="button"
                  @click="copyToClipboard(webhookUrls.shipsgo, 'shipsgo')"
                   variant="secondary"
                >
                  <component :is="copiedWebhook === 'shipsgo' ? Check : Copy" class="w-3.5 h-3.5" />
                  <span>{{ copiedWebhook === 'shipsgo' ? (isAr ? 'تم النسخ' : 'Copied') : (isAr ? 'نسخ' : 'Copy') }}</span>
                </UiButton>
              </div>
            </div>
          </div>

          <!-- Connection Tester & Status -->
          <div class="p-4 bg-zinc-50 rounded-lg border border-zinc-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
              <span class="text-xs font-bold text-zinc-800">{{ isAr ? 'حالة الاتصال:' : 'Connection Status:' }}</span>
              <span 
                v-if="testResults.shipsgo?.success" 
                class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-zinc-950 text-white flex items-center gap-1"
              >
                <CheckCircle2 class="w-3.5 h-3.5" />
                <span>VERIFIED CONNECTED</span>
              </span>
              <span 
                v-else-if="testResults.shipsgo && !testResults.shipsgo.success" 
                class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-red-100 text-red-700 border border-red-200 flex items-center gap-1"
              >
                <XCircle class="w-3.5 h-3.5" />
                <span>INVALID CREDENTIALS</span>
              </span>
              <span 
                v-else 
                class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-zinc-200 text-zinc-700"
              >
                {{ form.shipsgo.is_enabled ? 'CONFIGURED' : 'DISABLED' }}
              </span>
            </div>

            <UiButton
              type="button"
              @click="testConnection('shipsgo')"
              :disabled="testingProvider === 'shipsgo'"
               variant="secondary"
            >
              <RefreshCw :class="{ 'animate-spin': testingProvider === 'shipsgo' }" class="w-3.5 h-3.5" />
              <span>{{ testingProvider === 'shipsgo' ? (isAr ? 'جاري الفحص...' : 'Testing...') : (isAr ? 'اختبار مفتاح الاتصال' : 'Test Connection') }}</span>
            </UiButton>
          </div>

          <div v-if="testResults.shipsgo?.message" :class="testResults.shipsgo.success ? 'bg-zinc-950 text-white' : 'bg-red-50 text-red-900 border-red-200'" class="p-3 rounded-lg text-xs font-mono border">
            {{ testResults.shipsgo.message }}
          </div>
        </div>

        <!-- Tab 2: SeaRates -->
        <div v-if="activeTab === 'searates'" class="space-y-6">
          <div class="flex items-center justify-between p-4 bg-zinc-50 rounded-lg border border-zinc-200">
            <div class="space-y-0.5">
              <h3 class="text-sm font-extrabold text-zinc-950 flex items-center gap-2">
                <span>SeaRates Logistics API (Tracking & Sailing Schedules)</span>
              </h3>
              <p class="text-xs text-zinc-500">
                {{ isAr ? 'تتبع جدول الخطوط الملاحية والمسارات البحرية ومواعيد الوصول ETA المتوقعة' : 'Carrier sailing schedules, vessel routes, POL/POD ETA forecasts' }}
              </p>
            </div>
            
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="form.searates.is_enabled" class="sr-only peer">
              <div class="w-11 h-6 bg-zinc-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-zinc-950"></div>
              <span class="ms-2 text-xs font-bold text-zinc-950">{{ form.searates.is_enabled ? (isAr ? 'مفعّل' : 'Active') : (isAr ? 'معطّل' : 'Disabled') }}</span>
            </label>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- API Key -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-zinc-800">{{ isAr ? 'مفتاح SeaRates API Key' : 'SeaRates API Key' }}</label>
              <div class="relative">
                <UiInput
                  :type="showSecrets.searates_api_key ? 'text' : 'password'"
                  v-model="form.searates.api_key"
                  placeholder="e.g. sr_key_8819x..."
                  
                  dir="ltr"
                / />
                <button
                  type="button"
                  @click="showSecrets.searates_api_key = !showSecrets.searates_api_key"
                  class="absolute right-2.5 top-2.5 text-zinc-400 hover:text-zinc-950 cursor-pointer"
                >
                  <component :is="showSecrets.searates_api_key ? EyeOff : Eye" class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- Customer ID -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-zinc-800">{{ isAr ? 'معرف العميل (Customer ID)' : 'Customer ID' }}</label>
              <div class="relative">
                <UiInput
                  :type="showSecrets.searates_customer_id ? 'text' : 'password'"
                  v-model="form.searates.customer_id"
                  placeholder="e.g. CUST-90123"
                  
                  dir="ltr"
                / />
                <button
                  type="button"
                  @click="showSecrets.searates_customer_id = !showSecrets.searates_customer_id"
                  class="absolute right-2.5 top-2.5 text-zinc-400 hover:text-zinc-950 cursor-pointer"
                >
                  <component :is="showSecrets.searates_customer_id ? EyeOff : Eye" class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- Environment Mode -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-zinc-800">{{ isAr ? 'بيئة التشغيل' : 'Environment Mode' }}</label>
              <UiSelect v-model="form.searates.environment" >
                <option value="sandbox">Sandbox (تجريبي)</option>
                <option value="live">Live (إنتاج لايف)</option>
              </UiSelect>
            </div>

            <!-- Webhook URL Display -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-zinc-800">{{ isAr ? 'رابط الويب هوك' : 'Webhook Ingestion Endpoint' }}</label>
              <div class="flex items-center gap-1.5">
                <UiInput
                  type="text"
                  readonly
                  :value="webhookUrls.searates"
                  
                  dir="ltr"
                / />
                <UiButton
                  type="button"
                  @click="copyToClipboard(webhookUrls.searates, 'searates')"
                   variant="secondary"
                >
                  <component :is="copiedWebhook === 'searates' ? Check : Copy" class="w-3.5 h-3.5" />
                  <span>{{ copiedWebhook === 'searates' ? (isAr ? 'تم النسخ' : 'Copied') : (isAr ? 'نسخ' : 'Copy') }}</span>
                </UiButton>
              </div>
            </div>
          </div>

          <!-- Connection Tester & Status -->
          <div class="p-4 bg-zinc-50 rounded-lg border border-zinc-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
              <span class="text-xs font-bold text-zinc-800">{{ isAr ? 'حالة الاتصال:' : 'Connection Status:' }}</span>
              <span 
                v-if="testResults.searates?.success" 
                class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-zinc-950 text-white flex items-center gap-1"
              >
                <CheckCircle2 class="w-3.5 h-3.5" />
                <span>VERIFIED CONNECTED</span>
              </span>
              <span 
                v-else-if="testResults.searates && !testResults.searates.success" 
                class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-red-100 text-red-700 border border-red-200 flex items-center gap-1"
              >
                <XCircle class="w-3.5 h-3.5" />
                <span>INVALID CREDENTIALS</span>
              </span>
              <span 
                v-else 
                class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-zinc-200 text-zinc-700"
              >
                {{ form.searates.is_enabled ? 'CONFIGURED' : 'DISABLED' }}
              </span>
            </div>

            <UiButton
              type="button"
              @click="testConnection('searates')"
              :disabled="testingProvider === 'searates'"
               variant="secondary"
            >
              <RefreshCw :class="{ 'animate-spin': testingProvider === 'searates' }" class="w-3.5 h-3.5" />
              <span>{{ testingProvider === 'searates' ? (isAr ? 'جاري الفحص...' : 'Testing...') : (isAr ? 'اختبار مفتاح الاتصال' : 'Test Connection') }}</span>
            </UiButton>
          </div>

          <div v-if="testResults.searates?.message" :class="testResults.searates.success ? 'bg-zinc-950 text-white' : 'bg-red-50 text-red-900 border-red-200'" class="p-3 rounded-lg text-xs font-mono border">
            {{ testResults.searates.message }}
          </div>
        </div>

        <!-- Tab 3: Vizion -->
        <div v-if="activeTab === 'vizion'" class="space-y-6">
          <div class="flex items-center justify-between p-4 bg-zinc-50 rounded-lg border border-zinc-200">
            <div class="space-y-0.5">
              <h3 class="text-sm font-extrabold text-zinc-950 flex items-center gap-2">
                <span>Vizion 4.0 Standardized Ocean Tracking</span>
              </h3>
              <p class="text-xs text-zinc-500">
                {{ isAr ? 'استقبال إشعارات وتدفق الأحداث القياسية المباشرة للحاويات عالمياً عبر Push Webhooks' : 'Automated push event webhooks & normalized ocean carrier data API 4.0' }}
              </p>
            </div>
            
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="form.vizion.is_enabled" class="sr-only peer">
              <div class="w-11 h-6 bg-zinc-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-zinc-950"></div>
              <span class="ms-2 text-xs font-bold text-zinc-950">{{ form.vizion.is_enabled ? (isAr ? 'مفعّل' : 'Active') : (isAr ? 'معطّل' : 'Disabled') }}</span>
            </label>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- API Key -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-zinc-800">{{ isAr ? 'مفتاح Vizion API Key (X-API-Key)' : 'Vizion X-API-Key' }}</label>
              <div class="relative">
                <UiInput
                  :type="showSecrets.vizion_api_key ? 'text' : 'password'"
                  v-model="form.vizion.api_key"
                  placeholder="e.g. vz_key_9012..."
                  
                  dir="ltr"
                / />
                <button
                  type="button"
                  @click="showSecrets.vizion_api_key = !showSecrets.vizion_api_key"
                  class="absolute right-2.5 top-2.5 text-zinc-400 hover:text-zinc-950 cursor-pointer"
                >
                  <component :is="showSecrets.vizion_api_key ? EyeOff : Eye" class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- Webhook Secret -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-zinc-800">{{ isAr ? 'رمز تشفير الويب هوك (Webhook Secret)' : 'Webhook Signing Secret' }}</label>
              <div class="relative">
                <UiInput
                  :type="showSecrets.vizion_webhook_secret ? 'text' : 'password'"
                  v-model="form.vizion.webhook_secret"
                  placeholder="e.g. vz_sec_8819..."
                  
                  dir="ltr"
                / />
                <button
                  type="button"
                  @click="showSecrets.vizion_webhook_secret = !showSecrets.vizion_webhook_secret"
                  class="absolute right-2.5 top-2.5 text-zinc-400 hover:text-zinc-950 cursor-pointer"
                >
                  <component :is="showSecrets.vizion_webhook_secret ? EyeOff : Eye" class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- Environment Mode -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-zinc-800">{{ isAr ? 'بيئة التشغيل' : 'Environment Mode' }}</label>
              <UiSelect v-model="form.vizion.environment" >
                <option value="sandbox">Sandbox (تجريبي)</option>
                <option value="live">Live (إنتاج لايف)</option>
              </UiSelect>
            </div>

            <!-- Webhook URL Display -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-zinc-800">{{ isAr ? 'رابط الويب هوك للربط التلقائي' : 'Webhook Ingestion Endpoint' }}</label>
              <div class="flex items-center gap-1.5">
                <UiInput
                  type="text"
                  readonly
                  :value="webhookUrls.vizion"
                  
                  dir="ltr"
                / />
                <UiButton
                  type="button"
                  @click="copyToClipboard(webhookUrls.vizion, 'vizion')"
                   variant="secondary"
                >
                  <component :is="copiedWebhook === 'vizion' ? Check : Copy" class="w-3.5 h-3.5" />
                  <span>{{ copiedWebhook === 'vizion' ? (isAr ? 'تم النسخ' : 'Copied') : (isAr ? 'نسخ' : 'Copy') }}</span>
                </UiButton>
              </div>
            </div>
          </div>

          <!-- Connection Tester & Status -->
          <div class="p-4 bg-zinc-50 rounded-lg border border-zinc-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
              <span class="text-xs font-bold text-zinc-800">{{ isAr ? 'حالة الاتصال:' : 'Connection Status:' }}</span>
              <span 
                v-if="testResults.vizion?.success" 
                class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-zinc-950 text-white flex items-center gap-1"
              >
                <CheckCircle2 class="w-3.5 h-3.5" />
                <span>VERIFIED CONNECTED</span>
              </span>
              <span 
                v-else-if="testResults.vizion && !testResults.vizion.success" 
                class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-red-100 text-red-700 border border-red-200 flex items-center gap-1"
              >
                <XCircle class="w-3.5 h-3.5" />
                <span>INVALID CREDENTIALS</span>
              </span>
              <span 
                v-else 
                class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-zinc-200 text-zinc-700"
              >
                {{ form.vizion.is_enabled ? 'CONFIGURED' : 'DISABLED' }}
              </span>
            </div>

            <UiButton
              type="button"
              @click="testConnection('vizion')"
              :disabled="testingProvider === 'vizion'"
               variant="secondary"
            >
              <RefreshCw :class="{ 'animate-spin': testingProvider === 'vizion' }" class="w-3.5 h-3.5" />
              <span>{{ testingProvider === 'vizion' ? (isAr ? 'جاري الفحص...' : 'Testing...') : (isAr ? 'اختبار مفتاح الاتصال' : 'Test Connection') }}</span>
            </UiButton>
          </div>

          <div v-if="testResults.vizion?.message" :class="testResults.vizion.success ? 'bg-zinc-950 text-white' : 'bg-red-50 text-red-900 border-red-200'" class="p-3 rounded-lg text-xs font-mono border">
            {{ testResults.vizion.message }}
          </div>
        </div>

        <!-- Tab 4: FindTEU -->
        <div v-if="activeTab === 'findteu'" class="space-y-6">
          <div class="flex items-center justify-between p-4 bg-zinc-50 rounded-lg border border-zinc-200">
            <div class="space-y-0.5">
              <h3 class="text-sm font-extrabold text-zinc-950 flex items-center gap-2">
                <span>FindTEU / Track-Trace Polling API</span>
              </h3>
              <p class="text-xs text-zinc-500">
                {{ isAr ? 'استعلام مباشر عن حركة ومحطات الحاويات عبر شبكة Track-Trace العالمية' : 'Global container movement lookup & milestone status polling' }}
              </p>
            </div>
            
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="form.findteu.is_enabled" class="sr-only peer">
              <div class="w-11 h-6 bg-zinc-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-zinc-950"></div>
              <span class="ms-2 text-xs font-bold text-zinc-950">{{ form.findteu.is_enabled ? (isAr ? 'مفعّل' : 'Active') : (isAr ? 'معطّل' : 'Disabled') }}</span>
            </label>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- API Token -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-zinc-800">{{ isAr ? 'رمز FindTEU API Token' : 'FindTEU API Token' }}</label>
              <div class="relative">
                <UiInput
                  :type="showSecrets.findteu_api_token ? 'text' : 'password'"
                  v-model="form.findteu.api_token"
                  placeholder="e.g. ft_tok_1092..."
                  
                  dir="ltr"
                / />
                <button
                  type="button"
                  @click="showSecrets.findteu_api_token = !showSecrets.findteu_api_token"
                  class="absolute right-2.5 top-2.5 text-zinc-400 hover:text-zinc-950 cursor-pointer"
                >
                  <component :is="showSecrets.findteu_api_token ? EyeOff : Eye" class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- Environment Mode -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-zinc-800">{{ isAr ? 'بيئة التشغيل' : 'Environment Mode' }}</label>
              <UiSelect v-model="form.findteu.environment" >
                <option value="sandbox">Sandbox (تجريبي)</option>
                <option value="live">Live (إنتاج لايف)</option>
              </UiSelect>
            </div>

            <!-- Webhook URL Display -->
            <div class="space-y-1.5 md:col-span-2">
              <label class="block text-xs font-bold text-zinc-800">{{ isAr ? 'رابط الويب هوك الخاص بنظامك' : 'Webhook Ingestion Endpoint' }}</label>
              <div class="flex items-center gap-1.5">
                <UiInput
                  type="text"
                  readonly
                  :value="webhookUrls.findteu"
                  
                  dir="ltr"
                / />
                <UiButton
                  type="button"
                  @click="copyToClipboard(webhookUrls.findteu, 'findteu')"
                   variant="secondary"
                >
                  <component :is="copiedWebhook === 'findteu' ? Check : Copy" class="w-3.5 h-3.5" />
                  <span>{{ copiedWebhook === 'findteu' ? (isAr ? 'تم النسخ' : 'Copied') : (isAr ? 'نسخ' : 'Copy') }}</span>
                </UiButton>
              </div>
            </div>
          </div>

          <!-- Connection Tester & Status -->
          <div class="p-4 bg-zinc-50 rounded-lg border border-zinc-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
              <span class="text-xs font-bold text-zinc-800">{{ isAr ? 'حالة الاتصال:' : 'Connection Status:' }}</span>
              <span 
                v-if="testResults.findteu?.success" 
                class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-zinc-950 text-white flex items-center gap-1"
              >
                <CheckCircle2 class="w-3.5 h-3.5" />
                <span>VERIFIED CONNECTED</span>
              </span>
              <span 
                v-else-if="testResults.findteu && !testResults.findteu.success" 
                class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-red-100 text-red-700 border border-red-200 flex items-center gap-1"
              >
                <XCircle class="w-3.5 h-3.5" />
                <span>INVALID CREDENTIALS</span>
              </span>
              <span 
                v-else 
                class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-zinc-200 text-zinc-700"
              >
                {{ form.findteu.is_enabled ? 'CONFIGURED' : 'DISABLED' }}
              </span>
            </div>

            <UiButton
              type="button"
              @click="testConnection('findteu')"
              :disabled="testingProvider === 'findteu'"
               variant="secondary"
            >
              <RefreshCw :class="{ 'animate-spin': testingProvider === 'findteu' }" class="w-3.5 h-3.5" />
              <span>{{ testingProvider === 'findteu' ? (isAr ? 'جاري الفحص...' : 'Testing...') : (isAr ? 'اختبار مفتاح الاتصال' : 'Test Connection') }}</span>
            </UiButton>
          </div>

          <div v-if="testResults.findteu?.message" :class="testResults.findteu.success ? 'bg-zinc-950 text-white' : 'bg-red-50 text-red-900 border-red-200'" class="p-3 rounded-lg text-xs font-mono border">
            {{ testResults.findteu.message }}
          </div>
        </div>

      </div>

      <!-- Footer Action Bar -->
      <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 flex items-center justify-between">
        <div class="flex items-center gap-2 text-xs text-zinc-500">
          <AlertCircle class="w-4 h-4 text-zinc-400 shrink-0" />
          <span>{{ isAr ? 'يتم تشفير جميع بيانات الاعتماد ومفاتيح API في قاعدة البيانات تلقائياً.' : 'All credentials & API tokens are automatically encrypted at rest.' }}</span>
        </div>

        <UiButton
          type="button"
          @click="saveSettings"
          :disabled="form.processing"
           variant="primary"
        >
          <ShieldCheck class="w-4 h-4" />
          <span>{{ form.processing ? (isAr ? 'جاري الحفظ...' : 'Saving...') : (isAr ? 'حفظ إعدادات الربط' : 'Save Integration Settings') }}</span>
        </UiButton>
      </div>
    </div>
  </div>
</template>
