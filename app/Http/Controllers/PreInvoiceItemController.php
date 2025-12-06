<?php
namespace App\Http\Controllers;

use App\Models\PreInvoice;
use App\Models\PreInvoiceItem;
use App\Models\Product;
use Illuminate\Http\Request;

class PreInvoiceItemController extends Controller
{
    public function store(Request $request, PreInvoice $pre_invoice)
    {
        $this->authorize('update', $pre_invoice);

        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|numeric|min:1',
            'unit_price' => 'required|numeric|min:0',
        ]);

        $total = $data['quantity'] * $data['unit_price'];

        PreInvoiceItem::create([
            'pre_invoice_id' => $pre_invoice->id,
            'product_id'     => $data['product_id'],
            'quantity'       => $data['quantity'],
            'unit_price'     => $data['unit_price'],
            'total'          => $total,
        ]);

        // آپدیت جمع کل
        $pre_invoice->total_amount = $pre_invoice->items()->sum('total');
        if ($pre_invoice->type === 'formal') {
            $pre_invoice->formal_extra = round($pre_invoice->total_amount * 0.1);
        }
        $pre_invoice->save();

        return back()->with('success','آیتم اضافه شد.');
    }

    public function destroy(PreInvoiceItem $item)
    {
        $preInvoice = $item->preInvoice;
        $this->authorize('update', $preInvoice);

        $item->delete();

        // آپدیت جمع کل
        $preInvoice->total_amount = $preInvoice->items()->sum('total');
        if ($preInvoice->type === 'formal') {
            $preInvoice->formal_extra = round($preInvoice->total_amount * 0.1);
        } else {
            $preInvoice->formal_extra = null;
        }
        $preInvoice->save();

        return back()->with('success','آیتم حذف شد.');
    }
}
