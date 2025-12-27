@props([
    'active' => 'login',
    'title' => null,
    'showTabs' => true,

])

@php
    $locale = app()->getLocale();
    $isRtl  = in_array($locale, ['ar', 'ar_YE', 'ar-SA', 'ar-EG']);
@endphp

<x-authkit::ui.auth-layout :title="$title">
    <!-- Floating background elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -left-40 w-80 h-80 bg-[color:var(--brand-from)]/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/3 -right-20 w-60 h-60 bg-[color:var(--brand-to)]/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 left-1/4 w-40 h-40 bg-[color:var(--brand-via)]/10 rounded-full blur-3xl"></div>
    </div>

    <x-ui.language-switcher class="fixed top-6 {{ $isRtl ? 'left-6' : 'right-6' }} z-50" />

    <div class="min-h-screen flex items-center justify-center p-4 relative">
        <!-- Modern card design -->
        <div class="w-full max-w-7xl overflow-hidden rounded-3xl shadow-2xl relative
        lg:h-[780px] lg:max-h-[780px]">
        <!-- Background gradient with pattern -->
            <div class="absolute inset-0 bg-gradient-to-br from-slate-50 to-white"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(99,102,241,0.15)_0%,transparent_50%),radial-gradient(circle_at_80%_80%,rgba(139,92,246,0.1)_0%,transparent_50%)]"></div>
            
            <!-- Grid layout -->
            <div class="grid lg:grid-cols-2 relative z-10 h-full">

                <div class="relative p-10 lg:p-14 h-full">
                    <!-- Animated gradient orb -->
                    <div class="absolute top-10 {{ $isRtl ? 'right-10' : 'left-10' }} w-32 h-32 rounded-full bg-gradient-to-r from-[color:var(--brand-from)] to-[color:var(--brand-to)] opacity-20 blur-2xl animate-pulse-glow"></div>
                    
                    <!-- Logo and branding -->
                    <div class="relative z-10">
                        <div class="inline-flex items-center gap-3 mb-8">
                            <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-[color:var(--brand-from)] via-[color:var(--brand-via)] to-[color:var(--brand-to)] flex items-center justify-center shadow-lg">
                                <span class="text-white font-bold text-xl">HR</span>
                            </div>
                            <span class="text-2xl font-black gradient-text">@tr('Athka HR')</span>
                        </div>
                        
        <!-- Floating HR-themed 3D elements -->
        <div class="relative h-64 mb-12">
            <!-- Main HR Dashboard Card -->
            <div class="absolute {{ $isRtl ? 'right-4' : 'left-4' }} top-2 w-52 h-52 bg-gradient-to-br from-blue-500/25 to-cyan-500/25 rounded-3xl rotate-3 border-2 border-white/40 shadow-2xl backdrop-blur-lg overflow-hidden">
                <!-- Dashboard Grid -->
                <div class="absolute inset-2 grid grid-cols-3 grid-rows-3 gap-2">
                    @foreach(range(1, 9) as $i)
                    <div class="bg-white/20 rounded-lg {{ $i % 3 === 0 ? 'animate-pulse' : '' }}"></div>
                    @endforeach
                </div>
                <!-- HR Badge -->
                <div class="absolute top-4 {{ $isRtl ? 'left-4' : 'right-4' }} w-10 h-10 bg-gradient-to-r from-emerald-400 to-blue-400 rounded-lg flex items-center justify-center shadow-md">
                    <span class="text-white font-bold text-xs">HR</span>
                </div>
            </div>
            
            <!-- Employee Profile Card -->
            <div class="absolute {{ $isRtl ? 'right-16' : 'left-16' }} top-16 w-48 h-48 bg-gradient-to-br from-purple-500/25 to-pink-500/25 rounded-3xl -rotate-6 border-2 border-white/40 shadow-2xl backdrop-blur-lg animate-float-slow overflow-hidden">
                <!-- Profile Elements -->
                <div class="absolute top-6 inset-x-6 h-10 bg-white/30 rounded-full"></div>
                <div class="absolute top-20 inset-x-8 h-4 bg-white/20 rounded-full"></div>
                <div class="absolute top-28 inset-x-10 h-4 bg-white/20 rounded-full"></div>
            </div>
            
            <!-- Calendar/Time Card -->
            <div class="absolute {{ $isRtl ? 'right-28' : 'left-28' }} top-28 w-40 h-40 bg-gradient-to-br from-amber-500/20 to-orange-500/20 rounded-2xl rotate-12 border-2 border-white/40 shadow-xl backdrop-blur-md animate-float-delayed">
                <!-- Calendar Grid -->
                <div class="absolute top-4 inset-x-4 grid grid-cols-4 gap-1">
                    @foreach(range(1, 12) as $i)
                    <div class="h-2 bg-white/25 rounded"></div>
                    @endforeach
                </div>
                <!-- Time Display -->
                <div class="absolute bottom-6 inset-x-8 h-6 bg-gradient-to-r from-white/40 to-white/20 rounded-lg"></div>
            </div>
        </div>
        
                      
                        <!-- Features list -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-full bg-gradient-to-r from-[color:var(--brand-from)] to-[color:var(--brand-to)] flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="text-slate-700 font-medium">@tr('Attendance & leave management')</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-full bg-gradient-to-r from-[color:var(--brand-from)] to-[color:var(--brand-to)] flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <span class="text-slate-700 font-medium">@tr('Payroll & allowances in one place')</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-full bg-gradient-to-r from-[color:var(--brand-from)] to-[color:var(--brand-to)] flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <span class="text-slate-700 font-medium">@tr('Employee records & approvals workflow')</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Floating particles -->
                    <div class="absolute bottom-10 {{ $isRtl ? 'right-10' : 'left-10' }} flex gap-2">
                        @foreach(range(1, 3) as $i)
                        <div class="w-2 h-2 rounded-full bg-gradient-to-r from-[color:var(--brand-from)] to-[color:var(--brand-to)] opacity-50 animate-bounce" style="animation-delay: {{ $i * 0.1 }}s"></div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Form side - Clean design -->
                <div class="p-10 lg:p-14 h-full flex flex-col">
                    <!-- Mobile header -->
                    <div class="lg:hidden mb-8">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-[color:var(--brand-from)] to-[color:var(--brand-to)] flex items-center justify-center">
                                    <span class="text-white font-bold">HR</span>
                                </div>
                                <span class="text-xl font-bold gradient-text">Athka HR</span>
                            </div>
                        </div>
                        @if($showTabs)

                        <!-- Tabs for mobile -->
                        <div class="flex gap-2 p-1 rounded-2xl bg-slate-100/80">
                            <a href="{{ route('authkit.login') }}"
                               class="flex-1 text-center px-4 py-3 text-sm font-semibold rounded-xl transition-all
                               {{ $active === 'login' ? 'bg-white text-slate-900 shadow-md' : 'text-slate-600 hover:text-slate-900' }}">
                               @tr('Login')
                            </a>
                            <a href="{{ route('authkit.password.request') }}"
                               class="flex-1 text-center px-4 py-3 text-sm font-semibold rounded-xl transition-all
                               {{ $active === 'forgot' ? 'bg-white text-slate-900 shadow-md' : 'text-slate-600 hover:text-slate-900' }}">
                               @tr('Forgot password')
                            </a>
                        </div>
                        @endif
                    </div>
                    @if($showTabs)

                    <!-- Desktop header + Tabs (Centered) -->
<div class="hidden lg:block mb-12">
    <div class="text-center">
        <h1 class="text-3xl font-extrabold text-slate-900 leading-tight">
            @tr('Welcome back')
        </h1>
        <p class="text-slate-500 mt-2 text-base">
            @tr('Sign in to your account')
        </p>
    </div>

    <div class="mt-7 flex justify-center">
        <div class="inline-flex items-center gap-2 p-1.5 rounded-2xl bg-white/70 backdrop-blur-md shadow-sm ring-1 ring-slate-200/70">
            <a href="{{ route('authkit.login') }}"
               class="px-6 py-2.5 text-sm font-bold rounded-xl transition-all duration-200
               {{ $active === 'login'
                    ? 'bg-gradient-to-r from-[color:var(--brand-from)]/15 to-[color:var(--brand-to)]/15 text-slate-900 shadow-md ring-1 ring-slate-200'
                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
               @tr('Login')
            </a>

            <a href="{{ route('authkit.password.request') }}"
               class="px-6 py-2.5 text-sm font-bold rounded-xl transition-all duration-200
               {{ $active === 'forgot'
                    ? 'bg-gradient-to-r from-[color:var(--brand-from)]/15 to-[color:var(--brand-to)]/15 text-slate-900 shadow-md ring-1 ring-slate-200'
                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
               @tr('Forgot password')
            </a>
        </div>
    </div>
</div>
@endif
                    
<div class="flex-1 flex items-start lg:items-center">
    <div class="w-full max-w-md mx-auto">
        <div class="relative">
            {{ $slot }}
        </div>
    </div>
</div>

                    <!-- Footer -->
                    <div class="mt-8 pt-6 border-t border-slate-200">
                        <p class="text-center text-sm text-slate-500">
                            @tr('By continuing, you agree to our')
                            <a href="#" class="font-semibold text-[color:var(--brand-from)] hover:text-[color:var(--brand-to)]">@tr('Terms')</a>
                            @tr('and')
                            <a href="#" class="font-semibold text-[color:var(--brand-from)] hover:text-[color:var(--brand-to)]">@tr('Privacy Policy')</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-authkit::ui.auth-layout>
