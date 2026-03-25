<x-authkit::ui.auth-shell active="forgot" :title="tr('Reset password')" :show-tabs="false">
    <div class="space-y-6 sm:space-y-8">
        <!-- Header -->
        <div class="text-center space-y-1.5 sm:space-y-2">
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900">@tr('Reset password')</h1>
            <p class="text-sm sm:text-base text-slate-500">@tr('Choose a new password')</p>
        </div>
    
        <!-- Toast -->
        <x-ui.flash-toast />
    
        <!-- Form -->
        <form method="POST" action="{{ route('authkit.password.update') }}" class="space-y-5 sm:space-y-6">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
    
            {{-- Fields --}}
            <div class="space-y-4">
                <x-authkit::ui.auth-input
                    name="email"
                    type="email"
                    :label="tr('Email address')"
                    placeholder="your@email.com"
                    :value="$email"
                    required>
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
                    autocomplete="new-password"
                    required />
    
                <x-ui.password-input
                    name="password_confirmation"
                    :label="tr('Confirm password')"
                    placeholder="••••••••"
                    autocomplete="new-password"
                    required />
            </div>
    
            {{-- Actions --}}
            <div class="space-y-3">
                <x-ui.primary-button class="w-full">
                    @tr('Update password')
                </x-ui.primary-button>

            </div>
        </form>
    </div>
</x-authkit::ui.auth-shell>
