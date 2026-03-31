<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    public function index()
    {
        // Only owner can access billing
        if (!Auth::user()->isOwner()) {
            abort(403, 'Hanya Owner yang dapat mengakses halaman Billing.');
        }

        $company      = Auth::user()->company;
        $plans        = SubscriptionPlan::where('is_active', true)->orderBy('price_monthly')->get();
        $subscription = $company->activeSubscription;
        $history      = $company->subscriptions()->with('plan')->latest()->take(10)->get();
        $userCount    = $company->salesCount();

        return view('billing.index', compact('plans', 'subscription', 'history', 'userCount'));
    }

    public function upgrade(Request $request)
    {
        if (!Auth::user()->isOwner()) abort(403);

        $request->validate([
            'plan_id'       => 'required|exists:subscription_plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $company  = Auth::user()->company;
        $plan     = SubscriptionPlan::findOrFail($request->plan_id);
        $price    = $request->billing_cycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;
        $months   = $request->billing_cycle === 'yearly' ? 12 : 1;
        $endsAt   = now()->addMonths($months);

        // Cancel existing active subscription
        $company->subscriptions()
            ->whereIn('status', ['active', 'trial'])
            ->update(['status' => 'cancelled']);

        // Create new subscription
        Subscription::create([
            'company_id'    => $company->id,
            'plan_id'       => $plan->id,
            'status'        => 'active',
            'billing_cycle' => $request->billing_cycle,
            'starts_at'     => now(),
            'ends_at'       => $endsAt,
            'amount_paid'   => $price,
        ]);

        return redirect()->route('billing.index')
            ->with('success', "Paket {$plan->name} berhasil diaktifkan hingga {$endsAt->format('d M Y')}. (Demo: pembayaran disimulasikan)");
    }
}
