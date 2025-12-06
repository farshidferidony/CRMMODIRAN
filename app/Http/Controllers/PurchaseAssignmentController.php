<?php

namespace App\Http\Controllers;

use App\Models\PurchaseAssignment;
use App\Models\PreInvoiceItem;
use Illuminate\Http\Request;

class PurchaseAssignmentController extends Controller
{
    public function store(Request $request)
    {
        // فقط مدیر خرید مجاز است
        // $this->authorize('assignPurchase', PreInvoice::class); // بعداً در پالیسی اضافه می‌شود

        $data = $request->validate([
            'pre_invoice_item_id' => 'required|exists:pre_invoice_items,id',
            'buyer_id'            => 'required|exists:users,id',
            'source_id'           => 'nullable|exists:sources,id',
        ]);

        $item = PreInvoiceItem::with('preInvoice')->findOrFail($data['pre_invoice_item_id']);

        PurchaseAssignment::create([
            'pre_invoice_item_id' => $item->id,
            'buyer_id'            => $data['buyer_id'],
            'source_id'           => $data['source_id'] ?? null,
            'status'              => 'assigned',
            'created_by'          => auth()->id(),
        ]);

        return back()->with('success','آیتم به کارشناس خرید ارجاع شد.');
    }
}
