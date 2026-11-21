<?php

namespace App\Http\Controllers;

use App\Models\LedgerEntry;
use Illuminate\Http\Request;

class KhataController extends Controller
{
    /**
     * The shop-wide khata book: every udhaar and payment, newest first.
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $from = $request->query('from');
        $to = $request->query('to');
        $type = $request->query('type');
        $customerId = $request->query('customer');

        $base = LedgerEntry::where('user_id', $userId);

        $totals = [
            'udhaar' => round((clone $base)->where('type', LedgerEntry::TYPE_UDHAAR)->sum('amount'), 2),
            'payment' => round((clone $base)->where('type', LedgerEntry::TYPE_PAYMENT)->sum('amount'), 2),
        ];

        $entries = (clone $base)
            ->with('customer')
            ->when($from, fn ($query) => $query->whereDate('entry_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('entry_date', '<=', $to))
            ->when(in_array($type, [LedgerEntry::TYPE_UDHAAR, LedgerEntry::TYPE_PAYMENT], true),
                fn ($query) => $query->where('type', $type))
            ->when($customerId, fn ($query) => $query->where('customer_id', $customerId))
            ->latest('entry_date')
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        $customers = $request->user()->customers()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('khata.index', compact('entries', 'totals', 'customers', 'from', 'to', 'type', 'customerId'));
    }
}
