@extends('layouts.app')
@section('title', 'Billing & Paket')
@section('page-title', 'Billing & Paket')
@section('breadcrumb', 'Kelola langganan dan paket')

@section('content')

{{-- Current Subscription Status --}}
@if($subscription)
<div class="mb-5 p-5 rounded-2xl border {{ $subscription->isActive() ? 'bg-indigo-50 dark:bg-indigo-950/50 border-indigo-200 dark:border-indigo-900' : 'bg-red-50 dark:bg-red-950/50 border-red-200 dark:border-red-900' }}">
    <div class="flex items-start justify-between flex-wrap gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl {{ $subscription->isActive() ? 'bg-indigo-100 dark:bg-indigo-900' : 'bg-red-100 dark:bg-red-900' }} flex items-center justify-center">
                <svg class="w-7 h-7 {{ $subscription->isActive() ? 'text-indigo-600 dark:text-indigo-400' : 'text-red-600 dark:text-red-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <h2 class="text-lg font-extrabold {{ $subscription->isActive() ? 'text-indigo-900 dark:text-indigo-100' : 'text-red-900 dark:text-red-100' }}">
                        Paket {{ $subscription->plan->name }}
                    </h2>
                    <x-badge :color="$subscription->status_color">
                        {{ ucfirst($subscription->status === 'trial' ? 'Trial' : $subscription->status) }}
                    </x-badge>
                </div>
                <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm {{ $subscription->isActive() ? 'text-indigo-700 dark:text-indigo-300' : 'text-red-700 dark:text-red-300' }}">
                    <span>Max {{ $subscription->plan->max_users }} sales aktif</span>
                    <span>·</span>
                    <span>Aktif: {{ $subscription->starts_at->format('d M Y') }}</span>
                    <span>·</span>
                    <span>Berakhir: <strong>{{ $subscription->ends_at->format('d M Y') }}</strong></span>
                    @if($subscription->isActive())
                    <span>·</span>
                    <span class="font-semibold">{{ $subscription->days_remaining }} hari tersisa</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="text-right">
            <p class="text-sm {{ $subscription->isActive() ? 'text-indigo-600 dark:text-indigo-400' : 'text-red-500' }}">
                Penggunaan Sales
            </p>
            <p class="text-2xl font-extrabold {{ $subscription->isActive() ? 'text-indigo-900 dark:text-indigo-100' : 'text-red-700 dark:text-red-300' }}">
                {{ $userCount }} / {{ $subscription->plan->max_users }}
            </p>
            <div class="w-32 h-2 bg-indigo-200 dark:bg-indigo-900 rounded-full mt-1 overflow-hidden">
                @php $pct = $subscription->plan->max_users > 0 ? min(100, ($userCount / $subscription->plan->max_users) * 100) : 0; @endphp
                <div class="h-full rounded-full transition-all {{ $pct >= 90 ? 'bg-red-500' : 'bg-indigo-500' }}" style="width: {{ $pct }}%"></div>
            </div>
        </div>
    </div>
</div>
@else
<div class="mb-5 p-5 bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-900 rounded-2xl">
    <p class="text-red-700 dark:text-red-300 font-semibold">Tidak ada langganan aktif. Pilih paket di bawah untuk mulai.</p>
</div>
@endif

{{-- Plans --}}
<h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Pilih Paket</h2>

<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8" x-data="{ cycle: 'monthly' }">

    {{-- Billing Toggle --}}
    <div class="md:col-span-3 flex justify-center mb-2">
        <div class="inline-flex bg-slate-100 dark:bg-slate-800 rounded-xl p-1 gap-1">
            <button @click="cycle = 'monthly'" :class="cycle === 'monthly' ? 'bg-white dark:bg-slate-700 shadow text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400'" class="px-5 py-2 rounded-lg text-sm font-semibold transition-all">
                Bulanan
            </button>
            <button @click="cycle = 'yearly'" :class="cycle === 'yearly' ? 'bg-white dark:bg-slate-700 shadow text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400'" class="px-5 py-2 rounded-lg text-sm font-semibold transition-all">
                Tahunan
                <span class="ml-1 text-xs bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-400 px-1.5 py-0.5 rounded-full font-bold">-17%</span>
            </button>
        </div>
    </div>

    @foreach($plans as $plan)
    @php
        $isCurrent = $subscription && $subscription->plan_id === $plan->id && $subscription->isActive();
        $isPopular  = $plan->slug === 'growth';
    @endphp
    <div class="relative bg-white dark:bg-slate-900 rounded-2xl border {{ $isPopular ? 'border-indigo-400 dark:border-indigo-600 ring-2 ring-indigo-200 dark:ring-indigo-900 shadow-lg' : 'border-slate-200 dark:border-slate-800 shadow-sm' }} p-6 flex flex-col">

        @if($isPopular)
        <div class="absolute -top-3 left-1/2 -translate-x-1/2">
            <span class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-xs font-bold px-4 py-1 rounded-full shadow">
                POPULER
            </span>
        </div>
        @endif

        @if($isCurrent)
        <div class="absolute top-4 right-4">
            <x-badge color="green" size="xs">Paket Aktif</x-badge>
        </div>
        @endif

        <div class="mb-5">
            <h3 class="text-lg font-extrabold text-slate-900 dark:text-white mb-1">{{ $plan->name }}</h3>

            <div x-show="cycle === 'monthly'" class="flex items-baseline gap-1">
                <span class="text-3xl font-extrabold text-slate-900 dark:text-white">Rp {{ number_format($plan->price_monthly, 0, ',', '.') }}</span>
                <span class="text-slate-400 text-sm">/bulan</span>
            </div>
            <div x-show="cycle === 'yearly'" x-cloak class="flex items-baseline gap-1">
                <span class="text-3xl font-extrabold text-slate-900 dark:text-white">Rp {{ number_format($plan->price_yearly / 12, 0, ',', '.') }}</span>
                <span class="text-slate-400 text-sm">/bulan</span>
            </div>
            <div x-show="cycle === 'yearly'" x-cloak class="text-xs text-green-600 dark:text-green-400 font-semibold mt-1">
                Rp {{ number_format($plan->price_yearly, 0, ',', '.') }}/tahun (hemat Rp {{ number_format(($plan->price_monthly * 12) - $plan->price_yearly, 0, ',', '.') }})
            </div>
        </div>

        {{-- Key specs --}}
        <div class="space-y-2 mb-5 flex-1">
            <div class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                <span><strong>{{ $plan->max_users === 9999 ? 'Unlimited' : $plan->max_users }}</strong> Sales Aktif</span>
            </div>
            <div class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                <span>Max <strong>{{ $plan->max_stores === 9999 ? 'Unlimited' : $plan->max_stores }}</strong> Toko</span>
            </div>
            <div class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                <span><strong>{{ $plan->max_warehouses === 9999 ? 'Unlimited' : $plan->max_warehouses }}</strong> Gudang</span>
            </div>
            @if(is_array($plan->features))
            @foreach($plan->features as $feature)
            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <svg class="w-4 h-4 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                {{ $feature }}
            </div>
            @endforeach
            @endif
        </div>

        {{-- CTA --}}
        @if($isCurrent)
        <button disabled class="w-full py-3 px-4 bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-400 font-bold rounded-xl text-sm cursor-not-allowed">
            ✓ Paket Saat Ini
        </button>
        @else
        <form method="POST" action="{{ route('billing.upgrade') }}">
            @csrf
            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
            <input type="hidden" name="billing_cycle" x-bind:value="cycle">
            <button type="submit"
                onclick="return confirm('Upgrade ke paket {{ $plan->name }}? Langganan sebelumnya akan diganti.')"
                class="w-full py-3 px-4 font-bold rounded-xl text-sm transition-all {{ $isPopular ? 'bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white shadow-lg shadow-indigo-500/25' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200' }}">
                {{ $subscription && $subscription->isActive() ? 'Ganti ke Paket Ini' : 'Pilih Paket Ini' }}
            </button>
        </form>
        @endif
    </div>
    @endforeach
</div>

{{-- Billing History --}}
@if($history->count() > 0)
<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800">
        <h3 class="font-bold text-slate-900 dark:text-white">Riwayat Langganan</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/50">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Paket</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Periode</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Jumlah</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($history as $sub)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-5 py-3.5">
                        <p class="font-semibold text-slate-900 dark:text-white">{{ $sub->plan->name }}</p>
                        <p class="text-xs text-slate-400 capitalize">{{ $sub->billing_cycle }}</p>
                    </td>
                    <td class="px-4 py-3.5 text-slate-600 dark:text-slate-400 text-xs">
                        {{ $sub->starts_at->format('d M Y') }} — {{ $sub->ends_at->format('d M Y') }}
                    </td>
                    <td class="px-4 py-3.5 text-right font-semibold text-slate-900 dark:text-white tabular-nums">
                        Rp {{ number_format($sub->amount_paid ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        <x-badge :color="$sub->status_color" size="xs">{{ ucfirst($sub->status) }}</x-badge>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
