<?php

namespace App\Http\Controllers;

use App\Models\Transport;
use App\Models\PreInvoice;
use Illuminate\Http\Request;

class TransportClosingController extends Controller
{
    public function close(Request $request, Transport $transport)
    {
        $this->authorize('closeTransport', $transport); // اگر نقش/سطح دسترسی داری

        $data = $request->validate([
            'process_completed' => 'accepted',
        ]);

        if (!$transport->canCloseByLogistics()) {
            abort(422, 'هنوز همه‌ی پیش‌شرط‌های بستن پرونده کامل نشده است.');
        }

        $transport->update([
            'closed_by_logistics' => true,
            'closed_at'           => now(),
        ]);

        // اگر لازم است وضعیت پیش‌فاکتور یا سفارش را هم به "کاملاً بسته" یا وضعیتی خاص منتقل کن
        if ($transport->preInvoice) {
            $transport->preInvoice->update([
                // مثلاً:
                // 'status' => PreInvoiceStatus::Closed->value,
            ]);
        }

        return back()->with('success', 'پرونده حمل با موفقیت بسته شد.');
    }
}
