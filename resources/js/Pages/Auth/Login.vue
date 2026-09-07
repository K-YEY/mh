<script setup>
import { ref } from 'vue'
import { useForm, Head } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { Anchor, ThermometerSnowflake, ShieldCheck, ArrowRight, ArrowLeft, Lock, Mail, Sparkles, CheckCircle2, Globe } from 'lucide-vue-next'

defineOptions({ layout: null })

const { locale, isAr, dir, toggleLocale } = useI18n()

const form = useForm({
  email: 'admin@agrierp.com',
  password: 'password',
  remember: true,
})

const isCopied = ref(false)

const fillDemo = () => {
  form.email = 'admin@agrierp.com'
  form.password = 'password'
  isCopied.value = true
  setTimeout(() => {
    isCopied.value = false
  }, 2000)
}

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <Head :title="isAr ? 'تسجيل الدخول - نظام MH For Export ERP' : 'Sign In - MH For Export ERP Portal'" />

  <div :dir="dir" class="min-h-screen bg-[#f5f7fa] text-slate-900 flex font-sans antialiased selection:bg-blue-500/20 selection:text-blue-900 relative overflow-hidden">
    
    <!-- Top Floating Language Switcher (Apple Glass Pill) -->
    <div class="absolute top-5 ltr:right-6 rtl:left-6 z-50">
      <button 
        @click="toggleLocale"
        class="flex items-center gap-2 bg-white/80 hover:bg-white backdrop-blur-xl border border-white/90 text-slate-800 px-4 py-2 rounded-full text-xs font-semibold shadow-[0_2px_12px_rgba(0,0,0,0.04)] hover:shadow-[0_4px_16px_rgba(0,0,0,0.08)] transition-all cursor-pointer active:scale-95"
      >
        <Globe class="w-3.5 h-3.5 text-blue-600" />
        <span>{{ isAr ? '🇬🇧 English' : '🇪🇬 عربي (مصر)' }}</span>
      </button>
    </div>

    <!-- Ambient Calm Background Glows -->
    <div class="pointer-events-none absolute inset-0 overflow-hidden z-0">
      <div class="absolute -top-40 -left-40 w-[650px] h-[650px] rounded-full bg-gradient-to-br from-blue-400/15 via-sky-300/10 to-transparent blur-[140px]"></div>
      <div class="absolute -bottom-40 -right-40 w-[700px] h-[700px] rounded-full bg-gradient-to-tl from-indigo-400/10 via-cyan-300/10 to-transparent blur-[150px]"></div>
    </div>

    <!-- Left Split Panel: Apple VisionOS Frosted Branding Panel -->
    <div class="hidden lg:flex lg:w-1/2 relative bg-gradient-to-br from-slate-900 via-slate-900/95 to-blue-950 text-white p-14 flex-col justify-between overflow-hidden border-r border-white/10 shadow-2xl z-10">
      <!-- Ambient Inner Glass Glows -->
      <div class="absolute -top-32 -left-32 w-96 h-96 bg-blue-500/20 rounded-full blur-3xl animate-pulse"></div>
      <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-sky-400/15 rounded-full blur-3xl animate-pulse" style="animation-delay: 2s;"></div>

      <!-- Top Brand Header -->
      <div class="relative z-10 flex items-center justify-between animate-fade-down">
        <div class="flex items-center gap-3.5">
          <div class="h-12 w-auto px-3 py-1.5 bg-white/95 rounded-2xl flex items-center justify-center shadow-[0_4px_20px_rgba(0,0,0,0.2)] hover:scale-105 transition-all duration-300">
            <img src="/logo.png" alt="MH For Export" class="h-8 w-auto object-contain" />
          </div>
          <div>
            <div class="font-extrabold text-xl text-white tracking-tight flex items-center gap-2">
              <span>MH For Export</span>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-mono uppercase bg-blue-500/20 text-cyan-300 border border-blue-400/30 font-bold tracking-wider">ERP v2.4</span>
            </div>
            <p class="text-xs text-slate-300/90 font-medium mt-0.5">
              {{ isAr ? 'إدارة الشحن البحري الزراعي والمحاسبة المالية' : 'Agri-Ocean Freight & Double-Entry Accounting' }}
            </p>
          </div>
        </div>

        <div class="flex items-center gap-2 text-xs text-cyan-200 font-mono bg-white/10 backdrop-blur-md px-3.5 py-1.5 rounded-full border border-white/15">
          <span class="w-2 h-2 rounded-full bg-cyan-400 animate-ping"></span>
          <span>{{ isAr ? 'تتبع مباشر' : 'Live Telemetry' }}</span>
        </div>
      </div>

      <!-- Center Dynamic Telemetry & Feature Card -->
      <div class="relative z-10 space-y-6 max-w-md my-auto animate-fade-up">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-mono font-bold bg-white/10 text-cyan-300 border border-white/15 backdrop-blur-md">
          <ThermometerSnowflake class="w-3.5 h-3.5 text-cyan-400 animate-spin" style="animation-duration: 10s;" />
          <span>{{ isAr ? 'تتبع حرارة الحاويات المبردة USDA فعال' : 'USDA 3-Probe Cold Treatment Active' }}</span>
        </div>

        <h2 class="text-3xl font-extrabold text-white tracking-tight leading-snug">
          {{ isAr ? 'إدارة سلاسل التبريد الشحن البحري والرقابة المالية' : 'Precision Cold-Chain Logistics & Financial Control' }}
        </h2>
        
        <p class="text-xs text-slate-300 leading-relaxed font-normal">
          {{ isAr 
            ? 'تتبع آلي لحاويات الموالح المبردة، خزنة التوثيق لشهادات الحجر الزراعي، حسابات طن النولون البحري، وتوليد قيود اليومية الموزونة تلقائياً.' 
            : 'Automated citrus reefer monitoring, phytosanitary compliance verification, W/M revenue-ton calculations, and zero-sum general ledger balancing.' }}
        </p>

        <!-- Metric Stat Cards with Micro-hover Animation -->
        <div class="grid grid-cols-2 gap-3.5 pt-2">
          <div class="p-4 rounded-2xl bg-white/8 backdrop-blur-xl border border-white/15 space-y-1 hover:border-white/30 hover:-translate-y-0.5 transition-all duration-300">
            <div class="text-xs font-bold text-white font-mono flex items-center justify-between">
              <span>{{ isAr ? '100% قيود موزونة' : '100% Zero-Sum' }}</span>
              <Sparkles class="w-3.5 h-3.5 text-cyan-400" />
            </div>
            <div class="text-[11px] text-slate-300">{{ isAr ? 'قيود يومية آلية بدون تضارب' : 'Automated GL Journals' }}</div>
          </div>

          <div class="p-4 rounded-2xl bg-white/8 backdrop-blur-xl border border-white/15 space-y-1 hover:border-white/30 hover:-translate-y-0.5 transition-all duration-300">
            <div class="text-xs font-bold text-white font-mono flex items-center justify-between">
              <span>{{ isAr ? 'تتبع 24/7' : '24/7 Telematics' }}</span>
              <ThermometerSnowflake class="w-3.5 h-3.5 text-cyan-400" />
            </div>
            <div class="text-[11px] text-slate-300">{{ isAr ? 'تسجيل حرارة المولدات والحساسات' : 'GenSet Temp Logging' }}</div>
          </div>
        </div>
      </div>

      <!-- Footer Security Compliance Badge -->
      <div class="relative z-10 flex items-center justify-between text-xs text-slate-300 border-t border-white/10 pt-6 animate-fade-up">
        <div class="flex items-center gap-2">
          <ShieldCheck class="w-4 h-4 text-cyan-400" />
          <span>{{ isAr ? 'نظام مؤمن ومتوافق مع معايير الحجر الزراعي ISO 27001' : 'ISO 27001 & Phytosanitary Vault Security Verified' }}</span>
        </div>
        <span class="font-mono text-[10px] text-slate-400">PROD-LOCK-OK</span>
      </div>
    </div>

    <!-- Right Split Panel: Apple Liquid Glass Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 z-10">
      <div class="w-full max-w-md space-y-6 animate-scale-in">
        <!-- Mobile Header Brand -->
        <div class="lg:hidden flex items-center justify-between mb-4">
          <div class="flex items-center gap-3">
            <div class="h-11 w-auto px-2.5 py-1 rounded-2xl bg-white border border-white shadow-[0_2px_10px_rgba(0,0,0,0.05)] flex items-center justify-center font-bold">
              <img src="/logo.png" alt="MH For Export" class="h-8 w-auto object-contain" />
            </div>
            <div>
              <div class="font-black text-lg text-slate-900">MH For Export</div>
              <p class="text-xs text-slate-500">{{ isAr ? 'منظومة اللوجستيات الشحن البحري' : 'Agri Logistics Suite' }}</p>
            </div>
          </div>
        </div>

        <!-- Form Title Header -->
        <div class="space-y-1 text-center sm:text-start">
          <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
            {{ isAr ? 'تسجيل الدخول للنظام' : 'Sign in to your account' }}
          </h1>
          <p class="text-xs text-slate-500 font-medium">
            {{ isAr ? 'أدخل بيانات حسابك المعتمدة للوصول لبيئة العمل' : 'Enter your corporate credentials to launch your workbench' }}
          </p>
        </div>

        <!-- Auto Demo Credentials Filler Bar (Frosted Pill) -->
        <div class="p-3 bg-white/75 backdrop-blur-xl rounded-2xl border border-white shadow-xs flex items-center justify-between text-xs">
          <div class="flex items-center gap-2 text-slate-700">
            <Sparkles class="w-4 h-4 text-blue-600 shrink-0" />
            <span>
              {{ isAr ? 'حساب التجربة:' : 'Demo Access:' }} 
              <strong class="text-slate-900 font-mono" dir="ltr">admin@agrierp.com</strong>
            </span>
          </div>
          <button 
            type="button"
            @click="fillDemo"
            class="px-3 py-1 text-[11px] font-semibold rounded-full bg-blue-500/10 text-blue-700 hover:bg-blue-500/20 active:scale-95 transition-all flex items-center gap-1 shrink-0 cursor-pointer border border-blue-500/20"
          >
            <component :is="isCopied ? CheckCircle2 : Sparkles" class="w-3 h-3" />
            <span>{{ isCopied ? (isAr ? 'تم التعبئة!' : 'Auto Filled!') : (isAr ? 'تعبئة تلقائية' : 'Auto Fill') }}</span>
          </button>
        </div>

        <!-- Validation Errors Alert -->
        <div v-if="form.errors.email" class="p-3.5 bg-rose-500/10 border border-rose-500/25 text-rose-950 backdrop-blur-xl rounded-2xl text-xs font-medium flex items-center gap-2">
          <ShieldCheck class="w-4 h-4 text-rose-600 shrink-0" />
          <span>{{ form.errors.email }}</span>
        </div>

        <!-- Apple Liquid Glass Form Card -->
        <form @submit.prevent="submit" class="shadcn-card p-7 sm:p-9 space-y-5 shadow-[0_12px_40px_rgba(0,0,0,0.04)]">
          <!-- Corporate Email Input Field -->
          <div class="space-y-1.5">
            <label for="email-input" class="block text-xs font-semibold text-slate-800 tracking-wide">
              {{ isAr ? 'البريد الإلكتروني للشركة' : 'Corporate Email Address' }}
            </label>
            <div class="relative flex items-center">
              <Mail 
                class="absolute w-4 h-4 text-slate-400 pointer-events-none z-10"
                :class="isAr ? 'right-3.5' : 'left-3.5'"
              />
              <input
                id="email-input"
                v-model="form.email"
                type="email"
                required
                placeholder="admin@agrierp.com"
                :style="isAr ? 'padding-right: 2.6rem !important; padding-left: 0.75rem !important;' : 'padding-left: 2.6rem !important; padding-right: 0.75rem !important;'"
                class="shadcn-input font-medium"
                dir="ltr"
              />
            </div>
          </div>

          <!-- Password Input Field -->
          <div class="space-y-1.5">
            <div class="flex items-center justify-between">
              <label for="password-input" class="block text-xs font-semibold text-slate-800 tracking-wide">
                {{ isAr ? 'كلمة المرور' : 'Password' }}
              </label>
              <a href="#" class="text-[11px] text-blue-600 font-semibold hover:underline">
                {{ isAr ? 'نسيت كلمة المرور؟' : 'Forgot password?' }}
              </a>
            </div>
            <div class="relative flex items-center">
              <Lock 
                class="absolute w-4 h-4 text-slate-400 pointer-events-none z-10"
                :class="isAr ? 'right-3.5' : 'left-3.5'"
              />
              <input
                id="password-input"
                v-model="form.password"
                type="password"
                required
                placeholder="••••••••"
                :style="isAr ? 'padding-right: 2.6rem !important; padding-left: 0.75rem !important;' : 'padding-left: 2.6rem !important; padding-right: 0.75rem !important;'"
                class="shadcn-input font-mono"
                dir="ltr"
              />
            </div>
          </div>

          <!-- Remember Me Checkbox -->
          <div class="flex items-center justify-between pt-1">
            <label for="remember-checkbox" class="flex items-center gap-2.5 cursor-pointer group select-none">
              <input
                id="remember-checkbox"
                v-model="form.remember"
                type="checkbox"
                class="rounded-md border-slate-300 text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer"
              />
              <span class="text-xs text-slate-600 group-hover:text-slate-900 transition-colors">
                {{ isAr ? 'تذكر جلسة الدخول لمدة 30 يوم' : 'Remember session for 30 days' }}
              </span>
            </label>
          </div>

          <!-- Submit Button (Apple Liquid Blue) -->
          <button
            type="submit"
            :disabled="form.processing"
            class="shadcn-btn-primary w-full gap-2 mt-2 h-11 text-xs font-semibold rounded-2xl shadow-[0_4px_16px_rgba(0,113,227,0.3)] cursor-pointer"
          >
            <span>{{ isAr ? 'تسجيل الدخول للنظام' : 'Sign In to MH For Export Portal' }}</span>
            <component 
              :is="isAr ? ArrowLeft : ArrowRight" 
              class="w-4 h-4 transition-transform"
              :class="isAr ? 'group-hover:-translate-x-1' : 'group-hover:translate-x-1'"
            />
          </button>
        </form>

        <div class="text-center text-[11px] text-slate-400 font-mono">
          {{ isAr ? 'بوابة الحماية وتأمين الدخول • 2026 نظام MH For Export ERP' : 'Strict Enterprise Authentication Gateway • 2026 MH For Export ERP' }}
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes fadeDown {
  from { opacity: 0; transform: translateY(-12px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes scaleIn {
  from { opacity: 0; transform: scale(0.97); }
  to { opacity: 1; transform: scale(1); }
}

.animate-fade-down {
  animation: fadeDown 0.5s ease-out forwards;
}

.animate-fade-up {
  animation: fadeUp 0.6s ease-out forwards;
}

.animate-scale-in {
  animation: scaleIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
