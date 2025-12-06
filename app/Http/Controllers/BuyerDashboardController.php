<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseAssignment;


class BuyerDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $query = PurchaseAssignment::with([
                'item.preInvoice.customer',
                'source',
                'buyer'
            ])
            ->whereIn('status', ['assigned','pricing','priced','approved_by_buyer']);

        if (! $user->isManagement()) {
            // کارشناس خرید فقط ارجاعات خودش را ببیند
            $query->where('buyer_id', $user->id);
        }
        // مدیر خرید، مدیر بازرگانی، مدیر عامل، IT همه‌چیز را می‌بینند

        $assignments = $query->orderByDesc('id')->paginate(20);

        return view('buyer.assignments.index', compact('assignments'));
    }

    // public function index()
    // {
    //     $user = auth()->user();

    //     $query = PurchaseAssignment::with([
    //             'item.preInvoice.customer',
    //             'item.preInvoice',
    //             'buyer',
    //             'source',
    //         ]);

    //     if (! $user->isManagement()) {
    //         $query->where('buyer_id', $user->id);
    //     }

    //     // در صورت نیاز فیلتر براساس وضعیت
    //     if (request('status')) {
    //         $query->where('status', request('status'));
    //     }

    //     $assignments = $query->orderByDesc('id')->paginate(30);

    //     return view('buyer.assignments.index', compact('assignments'));
    // }



    
    public function edit(PurchaseAssignment $assignment)
    {
        // $this->authorize('update', $assignment); // بعداً در پالیسی می‌گذاری یا موقتاً حذف کن

        // فقط خود خریدار ببیند
        // abort_unless($assignment->buyer_id === auth()->id(), 403);

        $sources = \App\Models\Source::orderBy('id')->get();
        $assignment->load(['buyer','item.product']);

        return view('buyer.assignments.edit', compact('assignment','sources'));
    }

    public function update(Request $request, PurchaseAssignment $assignment)
    {
        // abort_unless($assignment->buyer_id === auth()->id(), 403);

        $data = $request->validate([
            'source_id'   => 'nullable|exists:sources,id',
            'unit_price'  => 'required|numeric|min:0',
            'note'        => 'nullable|string',
            'confirm'     => 'nullable|boolean', // اگر تیک «قیمت مورد تأیید من است» را بزند
        ]);

        $assignment->source_id  = $data['source_id'] ?? $assignment->source_id;
        $assignment->unit_price = $data['unit_price'];
        $assignment->note       = $data['note'] ?? null;
        $assignment->status     = $request->boolean('confirm') ? 'approved_by_buyer' : 'priced';
        $assignment->save();

        return redirect()->route('buyer.assignments.index')
            ->with('success','قیمت ذخیره شد.');
    }
}
