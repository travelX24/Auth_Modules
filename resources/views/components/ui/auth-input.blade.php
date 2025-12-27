@props([
    'name',
    'type' => 'text',
    'label' => null,
    'placeholder' => null,
    'value' => null,
])


@php
    use App\Support\UiMsg;

    $hasError = $errors->has($name);
    $id = $attributes->get('id') ?? $name;
    $hasIcon = isset($icon);

    $isRtl = in_array(app()->getLocale(), ['ar', 'fa', 'ur']) || (config('app.rtl') === true);

    $rawErr = $errors->first($name);
    $hideInline = UiMsg::hideInline($rawErr);
    $errText = UiMsg::toText($rawErr);
@endphp


<div class="relative">
    @if($label)
    <div class="mb-2 flex items-center justify-between gap-3" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <label for="{{ $id }}" class="text-sm font-semibold text-slate-700">
            {{ $label }}
        </label>

        @if($errText && !$hideInline)
            <span class="inline-flex items-center gap-1 text-xs font-semibold text-red-700
                         bg-red-50 border border-red-200 rounded-full px-2 py-0.5
                         max-w-[55%] truncate">
                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 8 8 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span class="truncate">{{ $errText }}</span>
            </span>
        @endif
    </div>
@endif


    <div class="group relative">
        {{-- Icon (logical start) --}}
        @if($hasIcon)
            <div class="absolute inset-y-0 start-4 flex items-center text-slate-400 group-focus-within:text-[color:var(--brand-from)] transition-colors duration-300 z-10">
                {{ $icon }}
            </div>
        @endif

        {{-- Glow border --}}
        <div class="absolute -inset-0.5 rounded-2xl bg-gradient-to-r from-[color:var(--brand-from)] via-[color:var(--brand-via)] to-[color:var(--brand-to)]
                    opacity-0 group-focus-within:opacity-20 blur transition-opacity duration-300
                    {{ $hasError ? '!opacity-20 !from-red-500 !to-red-500' : '' }}">
        </div>

        {{-- Input --}}
        <input
            id="{{ $id }}"
            name="{{ $name }}"
            type="{{ $type }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            autocomplete="{{ $name }}"
            {{ $attributes->except(['class','id'])->merge([
                'class' => 'relative w-full rounded-2xl border bg-white/90 shadow-sm
                            '.($hasIcon ? 'ps-12 pe-4' : 'px-4').' py-3.5
                            text-slate-900 placeholder:text-slate-400/70
                            focus:outline-none focus:ring-0 focus:bg-white
                            border-slate-200/80 transition-all duration-300
                            '.($hasError ? 'border-red-300 focus:border-red-400' : 'group-focus-within:border-transparent')
            ]) }}
        />

        {{-- Focus outline --}}
        <div class="absolute inset-0 rounded-2xl border-2 border-transparent group-focus-within:border-[color:var(--brand-from)]/30 pointer-events-none transition-all duration-300"></div>
    </div>

</div>
