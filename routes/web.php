<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BuyerDashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\GeoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoicePaymentPlanController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PreInvoiceController;
use App\Http\Controllers\PreInvoiceItemController;
use App\Http\Controllers\ProductAttributeController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseAssignmentController;
use App\Http\Controllers\PurchaseManagerController;
use App\Http\Controllers\PurchasePreInvoiceController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PreInvoicePaymentPlanController;
use App\Http\Controllers\FinancePaymentController;
use App\Models\ProductCategory;



use App\Http\Controllers\PurchaseExecutionController;
use App\Http\Controllers\PurchasePaymentPlanController;
use App\Http\Controllers\TransportController;




/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Authentication
Auth::routes();

// Public Routes
Route::get('/', [HomeController::class, 'root']);
Route::get('index/{locale}', [HomeController::class, 'lang']);
Route::post('/formsubmit', [HomeController::class, 'FormSubmit'])->name('FormSubmit');

// Geo API (Public)
Route::get('/geo/provinces', [GeoController::class, 'provinces'])->name('geo.provinces');
Route::get('/geo/cities', [GeoController::class, 'cities'])->name('geo.cities');

// Resource Routes
Route::resource('customers', CustomerController::class);
Route::resource('sources', SourceController::class);
Route::resource('product-categories', ProductCategoryController::class);
Route::resource('products', ProductController::class);
Route::resource('users', UserController::class);
Route::resource('invoices', InvoiceController::class)->only(['index', 'show']);

// Product Attributes
Route::post('product-categories/{category}/attributes', [ProductAttributeController::class, 'store'])->name('product-attributes.store');
Route::put('product-attributes/{attribute}', [ProductAttributeController::class, 'update'])->name('product-attributes.update');
Route::delete('product-attributes/{attribute}', [ProductAttributeController::class, 'destroy'])->name('product-attributes.destroy');
Route::get('categories/{category}/attributes', function (ProductCategory $category) {
    return $category->inheritedAttributes();
})->name('categories.attributes');

// PreInvoice Core Routes
Route::resource('pre-invoices', PreInvoiceController::class);
Route::post('pre-invoices/{pre_invoice}/items', [PreInvoiceItemController::class, 'store'])->name('pre-invoices.items.store');
Route::delete('pre-invoice-items/{item}', [PreInvoiceItemController::class, 'destroy'])->name('pre-invoice-items.destroy');


// Route::prefix('pre-invoices')->name('pre_invoices.')->group(function () {
//     // ...
//     Route::post('{pre_invoice}/customer-approve', [PreInvoiceController::class, 'customerApprove'])
//         ->name('customer-approve');
//     Route::post('{pre_invoice}/customer-reject', [PreInvoiceController::class, 'customerReject'])
//         ->name('customer-reject');
// });


// PreInvoice Workflow (Grouped)
Route::prefix('pre-invoices')->name('pre_invoices.')->group(function () {
    // Route::resource('/', PreInvoiceController::class)->parameters(['' => 'pre_invoice']);
    
    Route::post('{pre_invoice}/send-to-purchase', [PreInvoiceController::class, 'sendToPurchase'])->name('send_to_purchase');
    Route::post('{pre_invoice}/price-by-purchase', [PreInvoiceController::class, 'priceByPurchase'])->name('price_by_purchase');
    Route::post('{pre_invoice}/approve-purchase', [PreInvoiceController::class, 'approvePurchase'])->name('approve_purchase');
    Route::post('{pre_invoice}/price-by-sales', [PreInvoiceController::class, 'priceBySales'])->name('price_by_sales');
    Route::post('{pre_invoice}/approve-sales', [PreInvoiceController::class, 'approveSales'])->name('approve_sales');
    Route::post('{pre_invoice}/send-to-sales-approval', [PreInvoiceController::class, 'sendToSalesApproval'])->name('send_to_sales_approval');
    Route::post('{pre_invoice}/sales-approve', [PreInvoiceController::class, 'salesApprove'])->name('sales_approve');
    Route::post('{pre_invoice}/sales-reject', [PreInvoiceController::class, 'salesReject'])->name('sales_reject');
    Route::post('{pre_invoice}/send-to-customer', [PreInvoiceController::class, 'sendToCustomer'])->name('send_to_customer');
    Route::post('{pre_invoice}/accept-by-customer', [PreInvoiceController::class, 'acceptByCustomer'])->name('accept_by_customer');
    Route::post('{pre_invoice}/reject-by-customer', [PreInvoiceController::class, 'rejectByCustomer'])->name('reject_by_customer');
    
    Route::post('{pre_invoice}/sale-prices', [PreInvoiceController::class, 'saveSalePrices'])->name('save_sale_prices');
    Route::get('{pre_invoice}/sales-prices', [PreInvoiceController::class,'editSalePrices'])->name('edit_sale_prices');

    
    // فروش (پیش‌فاکتور اصلی)
    Route::get('{preInvoice}', [PreInvoiceController::class, 'show'])->name('pre-invoices.show');

    Route::get('{pre_invoice}/print', [PreInvoiceController::class,'print'])->name('print');

    Route::post('{pre_invoice}/customer-approve', [PreInvoiceController::class, 'customerApprove'])->name('customer_approve');
    Route::post('{pre_invoice}/customer-reject', [PreInvoiceController::class, 'customerReject'])->name('customer_reject');

    Route::post('{pre_invoice}/payments', [PaymentController::class, 'storeCustomer'])->name('payments.store');

    Route::post('{pre_invoice}/payment-plan', [PreInvoicePaymentPlanController::class, 'store'])->name('payment_plan.store');

    
    Route::post('{pre_invoice}/send-to-finance', [PreInvoiceController::class, 'sendToFinance'])->name('send_to_finance');

    Route::post('{pre_invoice}/advance-confirm', [PreInvoiceController::class, 'advanceConfirm'])->name('advance_confirm');

    
    Route::post('{pre_invoice}/go-to-buying', [PreInvoiceController::class, 'goToBuying'])->name('go_to_buying');

    Route::post('{preInvoice}/approve-full-purchase', [PreInvoiceController::class, 'approveFullPurchase'])->name('approve_full_purchase');

    Route::post('{preInvoice}/post-purchase-sales-approve', [PreInvoiceController::class, 'postPurchaseSalesApprove'])->name('post_purchase_sales_approve');

    Route::post('{preInvoice}/request-shipping', [PreInvoiceController::class, 'requestShipping'])->name('request_shipping');


    // صفحه لیست فرم‌های حمل + دکمه ایجاد
    Route::get('{preInvoice}/transports', [TransportController::class, 'index'])->name('transports.index');

        // ساخت فرم حمل جدید از روی پیش‌فاکتور
    Route::post('{preInvoice}/transports', [TransportController::class, 'store'])->name('transports.store');

});





// Purchase Workflow
Route::post('sale-pre-invoices/{sale_pre_invoice}/create-purchase', [PurchasePreInvoiceController::class, 'store'])->name('sale-pre-invoices.create-purchase');


Route::prefix('purchase-assignments')->name('purchase_assignments.')->group(function () {
    Route::post('/', [PurchaseAssignmentController::class, 'store'])->name('store');
    Route::post('{assignment}/change-buyer',  [PurchaseAssignmentController::class, 'changeBuyer'])->name('change_buyer');
    Route::post('{assignment}/change-source', [PurchaseAssignmentController::class, 'changeSource'])->name('change_source');
});

// Buyer Dashboard
Route::prefix('buyer/assignments')->name('buyer.assignments.')->group(function () {
    Route::get('/', [BuyerDashboardController::class, 'index'])->name('index');
    Route::get('{assignment}/edit', [BuyerDashboardController::class, 'edit'])->name('edit');
    Route::put('{assignment}', [BuyerDashboardController::class, 'update'])->name('update');
});

// Purchase Manager
Route::prefix('purchase-manager')->name('purchase-manager.')->group(function () {
    Route::get('pre-invoices/ready-for-sales', [PurchaseManagerController::class, 'readyForSales'])->name('pre-invoices.ready-for-sales');
    Route::get('pre-invoices/{pre_invoice}', [PurchaseManagerController::class, 'review'])->name('pre-invoices.review');
    Route::post('pre-invoices/{pre_invoice}/choose-prices', [PurchaseManagerController::class, 'choosePrices'])->name('pre-invoices.choose-prices');
    // Route::post('items/{item}/reassign-next', [PurchaseManagerController::class, 'reassignToNextSupplier'])->name('purchase-manager.items.reassign-next');
    Route::post('assignments/{assignment}/choose', [PurchaseManagerController::class, 'chooseSupplierForItem'])->name('purchase-manager.assignments.choose');

    Route::post('purchase-pre-invoices/{preInvoice}/approve', [PurchaseManagerController::class, 'approvePurchasePreInvoice'])->name('purchase-pre-invoices.approve');
    Route::post('purchase-pre-invoices/{preInvoice}/reject', [PurchaseManagerController::class, 'rejectPurchasePreInvoice'])->name('purchase-pre-invoices.reject');

});



// Sales Manager
Route::prefix('sales-manager/pre-invoices')->name('sales-manager.pre-invoices.')->group(function () {
    Route::get('priced', [PreInvoiceController::class, 'pricedBySalesIndex'])->name('priced');
    Route::get('waiting-approval', [PreInvoiceController::class, 'salesWaitingApproval'])->name('waiting-approval');
});


// Finance
Route::prefix('finance/pre-invoices')->name('finance.pre-invoices.')->group(function () {
    Route::get('approved-by-sales', [PreInvoiceController::class, 'financeFromApprovedSales'])->name('approved-by-sales');
    Route::post('{pre_invoice}/create-invoice', [PreInvoiceController::class, 'financeCreateInvoice'])->name('create-invoice');

    Route::get('purchase-pre-invoices', [PreInvoiceController::class, 'purchaseWaitingFinance'])->name('purchase-pre-invoices.index');
    Route::post('purchase-pre-invoices/{preInvoice}/approve', [PreInvoiceController::class, 'financeApprovePurchase'])->name('purchase-pre-invoices.approve');
    Route::post('purchase-pre-invoices/{preInvoice}/reject', [PreInvoiceController::class, 'financeRejectPurchase'])->name('purchase-pre-invoices.reject');

});

Route::prefix('finance/payments')->name('finance.payments.')->group(function () {
    Route::get('customer/pending', [FinancePaymentController::class, 'customerPending'])->name('customer.pending');

    Route::post('{payment}/confirm', [FinancePaymentController::class, 'confirm'])->name('confirm');

    Route::post('{payment}/reject', [FinancePaymentController::class, 'reject'])->name('reject');
});

// Route::prefix('purchase-pre-invoices')->name('purchase-pre-invoices.')->group(function () {

//     Route::get('/', [PreInvoiceController::class, 'purchaseIndex'])->name('index');
//     // خرید (پیش‌فاکتورهای خرید)
//     Route::get('{preInvoice}', [PreInvoiceController::class, 'showPurchase'])->name('show');
// });

// Route::prefix('purchase-pre-invoices')->name('purchase_pre_invoices.')->group(function () {
//     Route::get('{preInvoice}', [PreInvoiceController::class, 'showPurchase'])->name('purchase_show');
// });


Route::prefix('purchase-pre-invoices')->name('purchase_pre_invoices.')->group(function () {

    Route::get('/', [PreInvoiceController::class, 'purchaseIndex'])->name('index');

    // Route::get('{preInvoice}', [PreInvoiceController::class, 'showPurchase'])->name('show');

    Route::get('{preInvoice}', [PreInvoiceController::class, 'showPurchase'])->name('purchase_show');

    // Route::post('{preInvoice}/change-source', [PurchaseExecutionController::class, 'changeSource'])->name('change_source');

    // Route::post('{preInvoice}/change-buyer', [PurchaseExecutionController::class, 'changeBuyer'])->name('change_buyer');

    Route::post('items/{item}/finalize', [PurchaseExecutionController::class, 'finalizeItem'])->name('items.finalize');

    Route::post('{preInvoice}/approve-purchase', [PurchaseExecutionController::class, 'approvePurchase'])->name('approve_purchase');


    // Route::post('{preInvoice}/payment-plans', [PurchasePaymentPlanController::class, 'store'])->name('payment_plans.store');


    Route::post('items/{item}/finalize-purchase', [PurchaseExecutionController::class, 'finalizeItemPurchase'])->name('items.finalize_purchase');

    Route::post('{preInvoice}/approve-supplier-payment', [PurchaseExecutionController::class, 'approveSupplierPayment'])->name('approve_supplier_payment');
});


// صفحه ویرایش خود فرم حمل (عمومی‌تر)
Route::get('transports/{transport}/edit', [TransportController::class, 'edit'])
    ->name('transports.edit');

Route::put('transports/{transport}', [TransportController::class, 'update'])
    ->name('transports.update');


// User Roles
Route::get('users/{user}/roles', [UserController::class,'editRoles'])->name('users.roles.edit');
Route::put('users/{user}/roles', [UserController::class,'updateRoles'])->name('users.roles.update');


// Payments & Invoices
Route::post('invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('invoices.payments.store');
Route::post('payments/{payment}/mark-paid', [PaymentController::class, 'markPaid'])->name('payments.mark-paid');
Route::post('invoices/{invoice}/payment-plan', [InvoicePaymentPlanController::class, 'store'])->name('invoices.payment-plan.store');
Route::post('plans/{plan}/pay', [PaymentController::class, 'payPlan'])->name('plans.pay');
Route::post('plans/{plan}/pre-pay', [PaymentController::class, 'prePayPlan'])->name('plans.pre-pay');

// Reports
Route::get('reports/invoices/debtors', [InvoiceController::class, 'debtors'])->name('reports.invoices.debtors');
Route::get('reports/plans/due', [InvoicePaymentPlanController::class, 'due'])->name('reports.plans.due');
Route::get('invoices/{invoice}/history', [InvoiceController::class, 'history'])->name('invoices.history');

// Catch-all route (must be last)
Route::get('{any}', [HomeController::class, 'index'])->where('any', '.*');
