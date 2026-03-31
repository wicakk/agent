@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
$variants = [
    'primary'   => 'bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40',
    'secondary' => 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700',
    'danger'    => 'bg-red-500 hover:bg-red-600 text-white shadow-lg shadow-red-500/25',
    'success'   => 'bg-emerald-500 hover:bg-emerald-600 text-white shadow-lg shadow-emerald-500/25',
    'ghost'     => 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800',
];
$sizes = [
    'xs' => 'text-xs px-2.5 py-1.5 gap-1',
    'sm' => 'text-sm px-3 py-2 gap-1.5',
    'md' => 'text-sm px-4 py-2.5 gap-2',
    'lg' => 'text-base px-5 py-3 gap-2.5',
];
$baseClasses = 'inline-flex items-center justify-center font-semibold rounded-xl transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
$classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
