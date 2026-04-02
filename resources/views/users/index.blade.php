@extends('layouts.app')
@section('title', 'Tim Sales')
@section('page-title', 'Manajemen User')
@section('breadcrumb', auth()->user()->isOwner() ? 'Semua cabang' : (auth()->user()->branch?->name ?? 'Cabang Anda'))

@section('content')

{{-- User Limit Banner --}}
<div class="mb-5 p-4 bg-indigo-50 dark:bg-indigo-950 border border-indigo-200 dark:border-indigo-900 rounded-2xl flex items-center gap-3">
    <div class="flex-1">
        <p class="text-sm font-semibold text-indigo-800 dark:text-indigo-200">
            Penggunaan User: <strong>{{ $userCount }}</strong> / {{ $userLimit }} user aktif
            @if(!auth()->user()->isOwner() && auth()->user()->branch)
            <span class="text-indigo-500 dark:text-indigo-400 font-normal">({{ auth()->user()->branch->name }})</span>
            @endif
        </p>
        <div class="mt-2 h-2 bg-indigo-200 dark:bg-indigo-900 rounded-full overflow-hidden">
            @php $pct = $userLimit > 0 ? min(100, ($userCount/$userLimit)*100) : 0 @endphp
            <div class="h-full rounded-full transition-all {{ $pct >= 90 ? 'bg-red-500' : 'bg-indigo-500' }}" style="width: {{ $pct }}%"></div>
        </div>
    </div>
    @if($userCount < $userLimit)
    <x-button href="{{ route('users.create') }}" size="sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Tambah User
    </x-button>
    @else
    <x-button variant="secondary" size="sm" href="{{ route('billing.index') }}">Upgrade Paket</x-button>
    @endif
</div>

<x-card :padding="false">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">User</th>
                    <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider hidden sm:table-cell">Role</th>
                    @if(auth()->user()->isOwner())
                    <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider hidden md:table-cell">Cabang</th>
                    @endif
                    <th class="text-center px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider hidden md:table-cell">Transaksi</th>
                    <th class="text-center px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($users as $user)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <img src="{{ $user->avatar_url }}" class="w-9 h-9 rounded-full ring-2 ring-slate-100 dark:ring-slate-800">
                            <div>
                                <p class="font-semibold text-slate-900 dark:text-white">{{ $user->name }}</p>
                                <p class="text-xs text-slate-400">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-4 hidden sm:table-cell">
                        <x-badge :color="$user->role_color" size="xs">{{ ucfirst($user->role) }}</x-badge>
                    </td>
                    @if(auth()->user()->isOwner())
                    <td class="px-4 py-4 hidden md:table-cell">
                        @if($user->branch)
                            <span class="text-sm text-slate-700 dark:text-slate-300">{{ $user->branch->name }}</span>
                        @elseif(!$user->isOwner())
                            {{-- Assign branch dropdown --}}
                            <form method="POST" action="{{ route('branches.assign-user') }}" class="flex items-center gap-2">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <select name="branch_id" class="text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                    <option value="">Pilih cabang...</option>
                                    @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="text-xs px-2 py-1 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-semibold">Assign</button>
                            </form>
                        @else
                            <span class="text-xs text-slate-400 italic">Owner (semua)</span>
                        @endif
                    </td>
                    @endif
                    <td class="px-4 py-4 text-center hidden md:table-cell text-slate-600 dark:text-slate-400">
                        {{ $user->transactions_count }}
                    </td>
                    <td class="px-4 py-4 text-center">
                        @if($user->is_active)
                            <x-badge color="green" size="xs">Aktif</x-badge>
                        @else
                            <x-badge color="red" size="xs">Nonaktif</x-badge>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-1">
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('users.toggle', $user) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="p-1.5 rounded-lg transition-colors {{ $user->is_active ? 'text-slate-400 hover:text-yellow-600 hover:bg-yellow-50 dark:hover:bg-yellow-950' : 'text-slate-400 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-950' }}" title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        @if($user->is_active)
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                        @else
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        @endif
                                    </svg>
                                </button>
                            </form>
                            <a href="{{ route('users.edit', $user) }}" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                            </a>
                            @if(!$user->isOwner())
                            <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Hapus user ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79"/></svg>
                                </button>
                            </form>
                            @endif
                            @else
                            <span class="text-xs text-slate-400 italic px-2">(Anda)</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800">{{ $users->links() }}</div>
    @endif
</x-card>
@endsection
