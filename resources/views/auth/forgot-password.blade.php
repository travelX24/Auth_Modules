<x-authkit::ui.auth-shell active="forgot" :title="tr('Reset your password')">
    <div class="space-y-6">
        <!-- Icon -->
        <div class="flex justify-center">
            <div class="relative">
                <div class="h-20 w-20 rounded-2xl bg-gradient-to-br from-[color:var(--brand-from)]/10 via-[color:var(--brand-via)]/10 to-[color:var(--brand-to)]/10 flex items-center justify-center">
                    <svg class="w-10 h-10 text-[color:var(--brand-from)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <div class="absolute -inset-4 bg-gradient-to-r from-[color:var(--brand-from)]/20 to-[color:var(--brand-to)]/20 rounded-3xl blur-xl opacity-50 animate-pulse"></div>
            </div>
        </div>

        <!-- Header -->
        <div class="text-center">
            <h1 class="text-2xl font-bold text-slate-900">@tr('Forgot password?')</h1>
            <p class="text-slate-500 mt-2">@tr('Enter your email and we will send you a reset link')</p>
        </div>

        <x-ui.flash-toast />



        <!-- Form -->
        <form method="POST" action="{{ route('authkit.password.email') }}" class="space-y-5">
            @csrf

            <x-authkit::ui.auth-input
                name="email"
                type="email"
                :label="tr('Email address')"
                placeholder="your@email.com">
                <x-slot:icon>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </x-slot:icon>
            </x-authkit::ui.auth-input>
    
            <x-ui.primary-button>
                @tr('Send reset link')
            </x-ui.primary-button>

            <div class="text-center">
                <a href="{{ route('authkit.login') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[color:var(--brand-from)] hover:text-[color:var(--brand-to)] transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    @tr('Back to login')
                </a>
            </div>
        </form>
    </div>
</x-authkit::ui.auth-shell>
