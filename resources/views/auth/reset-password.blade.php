<x-authkit::ui.auth-shell active="forgot" :title="tr('Reset password')" :show-tabs="false">
    <div class="space-y-6 sm:space-y-8">
        <!-- Icon + Header (مجموعة واحدة) -->
        <div class="space-y-4 sm:space-y-5">
            <div class="flex justify-center">
                <div class="relative">
                    <div class="h-14 w-14 sm:h-16 sm:w-16 lg:h-20 lg:w-20 rounded-xl sm:rounded-2xl bg-gradient-to-br from-[color:var(--brand-from)]/10 via-[color:var(--brand-via)]/10 to-[color:var(--brand-to)]/10 flex items-center justify-center">
                        <svg class="w-7 h-7 sm:w-8 sm:h-8 lg:w-10 lg:h-10 text-[color:var(--brand-from)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div class="absolute -inset-3 sm:-inset-4 bg-gradient-to-r from-[color:var(--brand-from)]/20 to-[color:var(--brand-to)]/20 rounded-2xl sm:rounded-3xl blur-xl opacity-50 animate-pulse"></div>
                </div>
            </div>
    
            <div class="text-center space-y-1.5 sm:space-y-2">
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900">@tr('Reset password')</h1>
                <p class="text-sm sm:text-base text-slate-500">@tr('Choose a new password')</p>
            </div>
        </div>
    
        <!-- Toast -->
        <x-ui.flash-toast />
    
        <!-- Form -->
        <form method="POST" action="{{ route('authkit.password.update') }}" class="space-y-4 sm:space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
    
            {{-- Fields --}}
            <div class="space-y-3 sm:space-y-4">
                <x-authkit::ui.auth-input
                    name="email"
                    type="email"
                    :label="tr('Email address')"
                    placeholder="your@email.com"
                    :value="request('email')"
                    readonly
                    class="bg-slate-50/70 cursor-not-allowed">
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-authkit::ui.auth-input>
    
                <x-ui.password-input
                    name="password"
                    :label="tr('New password')"
                    placeholder="••••••••"
                    autocomplete="new-password" />
    
                <x-ui.password-input
                    name="password_confirmation"
                    :label="tr('Confirm password')"
                    placeholder="••••••••"
                    autocomplete="new-password" />
            </div>
    
            {{-- Actions --}}
            <div class="space-y-2 sm:space-y-3 pt-1">
                <x-ui.primary-button>
                    @tr('Update password')
                </x-ui.primary-button>

            </div>
        </form>
    </div>
</x-authkit::ui.auth-shell>
