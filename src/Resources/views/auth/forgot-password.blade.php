<x-authkit::ui.auth-shell active="forgot" :title="tr('Reset your password')">
    <div class="space-y-4 sm:space-y-5 lg:space-y-6">

        <!-- Header -->
        <div class="text-center">
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900">@tr('Forgot password?')</h1>
            <p class="text-slate-500 mt-1.5 sm:mt-2 text-sm sm:text-base">@tr('Enter your email and we will send you a reset link')</p>
        </div>

        <x-ui.flash-toast />

        <!-- Form -->
        <form method="POST" action="{{ route('authkit.password.email') }}" class="space-y-4 sm:space-y-5">
            @csrf

            <x-authkit::ui.auth-input
                name="email"
                type="email"
                :label="tr('Email address')"
                placeholder="your@email.com">
                <x-slot:icon>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </x-slot:icon>
            </x-authkit::ui.auth-input>
    
            <x-ui.primary-button class="w-full">
                @tr('Send reset link')
            </x-ui.primary-button>

            <div class="text-center pt-2">
                <a href="{{ route('authkit.login') }}" class="inline-flex items-center gap-1.5 sm:gap-2 text-xs sm:text-sm font-semibold text-[color:var(--brand-from)] hover:text-[color:var(--brand-to)] transition-colors">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    @tr('Back to login')
                </a>
            </div>
        </form>
    </div>
</x-authkit::ui.auth-shell>
