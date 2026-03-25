<x-authkit::ui.auth-shell active="login" :title="tr('Athka HR')">
    <div class="space-y-4 sm:space-y-5 lg:space-y-6">
        <x-ui.flash-toast />

        <!-- Divider -->
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-slate-200"></div>
            </div>
            <div class="relative flex justify-center text-xs sm:text-sm">
                <span class="px-3 sm:px-4 bg-white text-slate-500">@tr('Informations')</span>
            </div>
        </div>

        <!-- Login form -->
        <form
            method="POST"
            action="{{ route('authkit.login.store') }}"
            class="space-y-4 sm:space-y-5"
            x-data="{ isSubmitting: false }"
            @submit="if (isSubmitting) { $event.preventDefault(); return; } isSubmitting = true"
        >
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

            <x-ui.password-input
                name="password"
                :label="tr('Password')"
                placeholder="••••••••"
            />

            <div class="mt-3 sm:mt-4">
                <label class="flex items-center gap-2 sm:gap-3 cursor-pointer group">
                    <div class="relative">
                        <input type="checkbox" name="remember" class="sr-only peer">
                        <div class="h-4 w-4 sm:h-5 sm:w-5 rounded border border-slate-300 bg-white peer-checked:border-[color:var(--brand-from)] peer-checked:bg-[color:var(--brand-from)] transition-all duration-300 group-hover:border-[color:var(--brand-from)]"></div>
                        <svg class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-2.5 h-2.5 sm:w-3 sm:h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <span class="text-xs sm:text-sm text-slate-600 group-hover:text-slate-900 transition-colors">
                        @tr('Remember me')
                    </span>
                </label>
            </div>

            <x-ui.primary-button
                class="w-full"
                alpine-loading="isSubmitting"
            >
                @tr('Sign in')
            </x-ui.primary-button>
        </form>
    </div>
</x-authkit::ui.auth-shell>