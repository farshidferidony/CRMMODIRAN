<?php

namespace App\Http\Controllers;

use App\Models\Transport;
use App\Models\TransportVehicle;
use App\Models\TransportVehicleFile;
use App\Enums\FreightAccountingStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TransportAccountingController extends Controller
{
    public function updateVehicle(Transport $transport, TransportVehicle $vehicle, Request $request)
    {
        // اطمینان از اینکه این vehicle متعلق به همین transport است
        if ($vehicle->transport_id !== $transport->id) {
            abort(404);
        }

        $data = $request->validate([
            'action'               => 'required|in:approve,reject,settle',
            'freight_paid_amount'  => 'nullable|integer|min:0',
            'freight_reject_reason'=> 'nullable|string|max:500',
            'freight_paid_at'      => 'nullable|date',
            'freight_settled'      => 'nullable|boolean',
            'files.*'              => 'nullable|file|max:10240',
        ]);

        DB::transaction(function () use ($transport, $vehicle, $data, $request) {

            // تأیید هزینه
            if ($data['action'] === 'approve') {
                $vehicle->update([
                    'freight_accounting_status' => FreightAccountingStatus::Approved->value,
                    'freight_reject_reason'     => null,
                    'freight_approved_at'       => now(),
                    'freight_approved_by'       => Auth::id(),
                ]);
            }

            // رد هزینه
            if ($data['action'] === 'reject') {
                $vehicle->update([
                    'freight_accounting_status' => FreightAccountingStatus::Rejected->value,
                    'freight_reject_reason'     => $data['freight_reject_reason'] ?? null,
                    'freight_approved_at'       => null,
                    'freight_approved_by'       => null,
                    'freight_paid_at'           => null,
                    'freight_paid_by'           => null,
                    'freight_settled'           => false,
                ]);
            }

            // ثبت پرداخت و تسویه
            if ($data['action'] === 'settle') {
                $vehicle->update([
                    'freight_accounting_status' => FreightAccountingStatus::Approved->value,
                    'freight_paid_at'           => $data['freight_paid_at'] ?? now(),
                    'freight_paid_by'           => Auth::id(),
                    'freight_settled'           => !empty($data['freight_settled']),
                ]);
            }

            // اگر مبلغ پرداخت شده را جدا ذخیره می‌کنی، اینجا set کن (مثلاً در فیلد کمکی)
            if (!empty($data['freight_paid_amount'])) {
                // اگر فیلدی مثل freight_paid_amount نداری، می‌توانیم اضافه کنیم
                $vehicle->update([
                    'total_freight_amount' => $data['freight_paid_amount'],
                ]);
            }

            // ذخیره فایل‌های واریزی (اگر بخواهی همان TransportVehicleFile استفاده شود)
            if ($request->hasFile('files')) {
                $disk    = Storage::disk('public');
                $baseDir = 'logistics/transport-files';

                if (!$disk->exists($baseDir)) {
                    $disk->makeDirectory($baseDir);
                }

                $vehicleDir = $baseDir . '/' . $transport->id . '/' . $vehicle->id;
                if (!$disk->exists($vehicleDir)) {
                    $disk->makeDirectory($vehicleDir);
                }

                foreach ($request->file('files') as $file) {
                    $storedPath = $file->store($vehicleDir, 'public');

                    $vehicle->files()->create([
                        'title'         => 'رسید پرداخت',
                        'path'          => $storedPath,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type'     => $file->getClientMimeType(),
                        'size'          => $file->getSize(),
                        'kind'          => 'accounting_receipt', // اگر فیلد kind را داری
                    ]);
                }
            }

            // در صورت نیاز، وضعیت کلی Transport را هم بعد از حسابداری آپدیت کن
            // $this->maybeUpdateTransportStatusAfterAccounting($transport);
        });

        return back()->with('success', 'وضعیت حسابداری این وسیله به‌روزرسانی شد.');
    }

    public function settleAll(Transport $transport, Request $request)
    {
        $data = $request->validate([
            'settle_all' => 'accepted',
        ]);

        DB::transaction(function () use ($transport) {

            // چک کن که همه وسایل Approved و paid باشند
            $allVehicles = $transport->vehicles()->get();

            $allPaid = $allVehicles->every(function (TransportVehicle $v) {
                return $v->freight_accounting_status === FreightAccountingStatus::Approved
                    && $v->freight_paid_at !== null;
            });

            if (!$allPaid) {
                // اگر همه پرداخت نشده باشند، می‌توانی Exception بندازی یا فقط هیچ کاری نکنی
                // این‌جا فرض می‌کنیم Exception:
                abort(422, 'همه‌ی وسایل هنوز پرداخت نشده‌اند.');
            }

            // اگر همه پرداخت شده‌اند، می‌توانی یک فلگ روی خود Transport بگذاری (در صورت داشتن فیلد)
            $transport->update([
                'approved_by_accounting' => true,
            ]);
        });

        return back()->with('success', 'تسویه کامل این فرم حمل ثبت شد.');
    }
}
