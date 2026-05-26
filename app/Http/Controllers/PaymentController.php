<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Contract;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends BaseController
{
    private function userCanAccessContract(Request $request, Contract $contract): bool
    {
        $user = $request->user();

        if (! $user) {
            return false;
        }

        if ($user->role === 'manager') {
            return true;
        }

        if ($user->role === 'owner') {
            $owner = $user->owner;

            return $owner
                ? $owner->properties()->whereKey($contract->property_id)->exists()
                : false;
        }

        return false;
    }

    private function userCanAccessPayment(Request $request, Payment $payment): bool
    {
        return $payment->contract
            ? $this->userCanAccessContract($request, $payment->contract)
            : false;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'manager') {
            return response()->json(PaymentResource::collection(Payment::with('contract.property')->get()));
        }

        $owner = $user->owner;

        if (! $owner) {
            return response()->json([]);
        }

        $payments = Payment::with('contract.property')
            ->whereHas('contract.property.owners', function ($query) use ($owner) {
                $query->where('owners.id', $owner->id);
            })
            ->get();

        return response()->json(PaymentResource::collection($payments));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaymentRequest $request)
    {
        $validated = $request->validated();

        $contract = Contract::findOrFail($validated['contract_id']);

        if (! $this->userCanAccessContract($request, $contract)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $payment = Payment::create($validated);

        return response()->json(new PaymentResource($payment->load('contract.property')), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $payment = Payment::with('contract.property')->findOrFail($id);

        if (! $this->userCanAccessPayment($request, $payment)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json(new PaymentResource($payment));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PaymentRequest $request, $id)
    {
        $payment = Payment::with('contract.property')->findOrFail($id);

        if (! $this->userCanAccessPayment($request, $payment)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validated();

        if (array_key_exists('contract_id', $validated)) {
            $contract = Contract::findOrFail($validated['contract_id']);

            if (! $this->userCanAccessContract($request, $contract)) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
        }

        $payment->update($validated);

        return response()->json(new PaymentResource($payment->load('contract.property')));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $payment = Payment::with('contract.property')->findOrFail($id);

        if (! $this->userCanAccessPayment($request, $payment)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $payment->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
