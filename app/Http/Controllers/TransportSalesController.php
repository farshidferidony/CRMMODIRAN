<?php

namespace App\Http\Controllers;

use App\Models\Transport;
use App\Models\PreInvoice;
use Illuminate\Http\Request;

class TransportSalesController extends Controller
{
    public function approve(Request $request, Transport $transport)
    {
        $data = $request->validate([
            'approved_by_sales_manager' => 'accepted',
        ]);

        if (!$transport->canApproveBySalesManager()) {
            abort(403, 'هنوز شرایط لازم برای تأیید مدیر فروش فراهم نشده است.');
        }

        $transport->update([
            'approved_by_sales_manager' => true,
        ]);

        if ($transport->preInvoice) {
            $transport->preInvoice->updateStatusFromTransports();
        }

        return back()->with('success', 'تأیید مدیر فروش ثبت شد.');
    }

}
