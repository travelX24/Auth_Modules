<x-authkit::ui.auth-shell active="login" :title="tr('Athka HR')">
    <div class="space-y-6">
        <x-ui.flash-toast />

        
        <!-- Social login -->
        {{-- <div class="text-center">
            <p class="text-sm text-slate-500 mb-4">@tr('Or continue with')</p>
            <div class="flex gap-3">
                <button type="button" class="flex-1 py-3 rounded-xl border border-slate-200 hover:border-slate-300 bg-white hover:bg-slate-50 transition-all duration-300 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    <span class="text-sm font-medium text-slate-700">Google</span>
                </button>
                <button type="button" class="flex-1 py-3 rounded-xl border border-slate-200 hover:border-slate-300 bg-white hover:bg-slate-50 transition-all duration-300 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5 text-[#1877F2]" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    <span class="text-sm font-medium text-slate-700">Facebook</span>
                </button>
            </div>
        </div> --}}

        <!-- Divider -->
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-slate-200"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-4 bg-white text-slate-500">@tr('Informations')</span>
            </div>
        </div>

        <!-- Login form -->
        <form method="POST" action="{{ route('authkit.login.store') }}" class="space-y-5">
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

            <x-ui.password-input name="password" :label="tr('Password')" placeholder="••••••••" />

            <div class="mt-4 flex items-center justify-between">
                <label class="flex items-center gap-3 cursor-pointer group">
                    <div class="relative">
                        <input type="checkbox" name="remember" class="sr-only peer">
                        <div class="h-5 w-5 rounded border border-slate-300 bg-white peer-checked:border-[color:var(--brand-from)] peer-checked:bg-[color:var(--brand-from)] transition-all duration-300 group-hover:border-[color:var(--brand-from)]"></div>
                        <svg class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <span class="text-sm text-slate-600 group-hover:text-slate-900 transition-colors">@tr('Remember me')</span>
                </label>
            </div>

            <x-ui.primary-button class="mt-4" >
                @tr('Sign in')
            </x-ui.primary-button>
        </form>
    </div>
</x-authkit::ui.auth-shell>