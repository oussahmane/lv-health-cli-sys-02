<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Clinic;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role === 'super_admin') {
            $payments = Payment::with(['clinic', 'plan'])->latest()->get();
        } else {
            $payments = Payment::with('plan')
                ->where('clinic_id', $request->user()->clinic_id)
                ->latest()
                ->get();
        }

        return response()->json($payments);
    }

    public function store(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'amount' => 'required|numeric',
            'method' => 'required|in:edahabia,ccp',
            'receipt' => 'required|image|max:5120', // Max 5MB
        ]);

        $clinicId = $request->user()->clinic_id;
        $path = $request->file('receipt')->store('receipts', 'public');

        $payment = Payment::create([
            'clinic_id' => $clinicId,
            'plan_id' => $request->plan_id,
            'amount' => $request->amount,
            'method' => $request->method,
            'receipt_path' => $path,
            'status' => 'pending',
        ]);

        // Also update clinic's payment status to pending if it's not already paid
        $clinic = Clinic::find($clinicId);
        if ($clinic->payment_status !== 'paid') {
            $clinic->update(['payment_status' => 'pending']);
        }

        return response()->json([
            'message' => 'Payment submitted successfully. Awaiting admin approval.',
            'payment' => $payment
        ], 201);
    }

    public function approve(Request $request, Payment $payment)
    {
        if ($request->user()->role !== 'super_admin') abort(403);

        if ($payment->status === 'approved') {
            return response()->json(['message' => 'Payment already approved.'], 400);
        }

        return \DB::transaction(function () use ($payment, $request) {
            $payment->update([
                'status' => 'approved',
                'notes' => $request->notes
            ]);

            $clinic = Clinic::findOrFail($payment->clinic_id);
            
            // Safe Extension Logic
            $currentEndsAt = $clinic->subscription_ends_at;
            $baseDate = ($currentEndsAt && $currentEndsAt->isFuture()) ? $currentEndsAt : Carbon::now();
            $newEndsAt = $baseDate->addDays(30);

            $clinic->update([
                'plan_id' => $payment->plan_id,
                'payment_status' => 'paid',
                'is_active' => true,
                'activated_at' => now(),
                'subscription_ends_at' => $newEndsAt,
            ]);

            return response()->json([
                'message' => 'Payment approved and clinic activated.',
                'clinic' => $clinic
            ]);
        });
    }

    public function reject(Request $request, Payment $payment)
    {
        if ($request->user()->role !== 'super_admin') abort(403);

        $payment->update([
            'status' => 'rejected',
            'notes' => $request->notes
        ]);

        // Update clinic status only if it's currently pending or rejected
        $clinic = Clinic::findOrFail($payment->clinic_id);
        if ($clinic->payment_status !== 'paid') {
            $clinic->update(['payment_status' => 'rejected']);
        }

        return response()->json(['message' => 'Payment rejected.']);
    }
}
