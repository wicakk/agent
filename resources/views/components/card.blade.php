@props(['padding' => true])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm ' . ($padding ? 'p-5' : '')]) }}>
    {{ $slot }}
</div>
