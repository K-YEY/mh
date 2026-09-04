<?php

namespace App\Http\Controllers\Accounting;

use App\Actions\Accounting\ProcessContainerDepositAction;
use App\Http\Controllers\Controller;
use App\Models\ContainerDeposit;
use App\Models\MasterContainer;
use App\Models\Party;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContainerDepositController extends Controller
{
    public function index(): Response
    {
        $deposits = ContainerDeposit::with(['container.masterShipment', 'party'])
            ->latest('id')
            ->paginate(15);

        $containers = MasterContainer::with('masterShipment')->get();
        $parties = Party::all();

        return Inertia::render('Accounting/Deposits/Index', [
            'deposits' => $deposits,
            'containers' => $containers,
            'parties' => $parties,
        ]);
    }

    public function store(Request $request, ProcessContainerDepositAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'master_container_id' => 'required|exists:master_containers,id',
            'party_id' => 'required|exists:parties,id',
            'deposit_type' => 'required|in:PAID_TO_LINE,COLLECTED_FROM_CUSTOMER',
            'amount' => 'required|numeric|min:0.01',
            'currency_code' => 'required|string|size:3',
            'notes' => 'nullable|string',
        ]);

        $action->execute($validated);

        return redirect()->back()->with('success', 'Container Guarantee Deposit logged.');
    }

    public function refund(ContainerDeposit $deposit): RedirectResponse
    {
        $deposit->update([
            'status' => 'REFUNDED_FULL',
            'refunded_date' => now()->toDateString(),
        ]);

        return redirect()->back()->with('success', 'Container deposit marked as REFUNDED.');
    }
}
