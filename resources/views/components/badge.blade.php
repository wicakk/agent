@props([
    'color' => 'gray',
    'size' => 'sm',
])

@php
$colorClasses = [
    'green'  => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300',
    'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300',
    'red'    => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300',
    'blue'   => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300',
    'indigo' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-300',
    'purple' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300',
    'orange' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300',
    'gray'   => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
];
$sizeClasses = [
    'xs' => 'text-[10px] px-1.5 py-0.5',
    'sm' => 'text-xs px-2 py-1',
    'md' => 'text-sm px-2.5 py-1',
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 font-semibold rounded-full ' . ($colorClasses[$color] ?? $colorClasses['gray']) . ' ' . ($sizeClasses[$size] ?? $sizeClasses['sm'])]) }}>
    {{ $slot }}
</span>
