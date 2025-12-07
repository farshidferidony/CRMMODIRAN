<div class="right-bar">
    <div data-simplebar class="h-100">
        <div class="rightbar-title d-flex align-items-center p-3">
            <h5 class="m-0 me-2">میانبرها</h5>
            <a href="javascript:void(0);" class="right-bar-toggle ms-auto">
                <i class="mdi mdi-close noti-icon"></i>
            </a>
        </div>

        <hr class="m-0" />

        <div class="p-3">
            {{-- بخش مشتریان --}}
            <h6 class="mb-2">مشتریان</h6>
            <div class="list-group mb-3">
                <a href="{{ route('customers.index') }}" class="list-group-item list-group-item-action">
                    لیست مشتریان
                </a>
                <a href="{{ route('customers.create') }}" class="list-group-item list-group-item-action">
                    افزودن مشتری جدید
                </a>
            </div>

            {{-- بخش پیش‌فاکتور فروش --}}
            <h6 class="mb-2">پیش‌فاکتور فروش</h6>
            <div class="list-group mb-3">
                <a href="{{ route('pre-invoices.index') }}" class="list-group-item list-group-item-action">
                    لیست پیش‌فاکتورهای فروش
                </a>
                <a href="{{ route('pre-invoices.create') }}" class="list-group-item list-group-item-action">
                    ثبت پیش‌فاکتور جدید
                </a>
            </div>

            {{-- ماژول خرید / مدیر خرید --}}
            <h6 class="mb-2">خرید</h6>
            <div class="list-group mb-3">
                <a href="{{ route('purchase_pre_invoices.index') }}" class="list-group-item list-group-item-action">
                    پیش‌فاکتورهای خرید (ارجاع شده)
                </a>
                <a href="{{ route('buyer.assignments.index') }}" class="list-group-item list-group-item-action">
                    وظایف کارشناسان خرید
                </a>
                <a href="{{ route('purchase-manager.pre-invoices.ready-for-sales') }}" class="list-group-item list-group-item-action">
                    پیش‌فاکتورهای آماده ارسال به فروش
                </a>
            </div>

            {{-- ماژول فروش / مدیر فروش --}}
            <h6 class="mb-2">فروش</h6>
            <div class="list-group mb-3">
                <a href="{{ route('sales-manager.pre-invoices.priced') }}" class="list-group-item list-group-item-action">
                    پیش‌فاکتورهای قیمت‌گذاری شده توسط فروش
                </a>
                <a href="{{ route('sales-manager.pre-invoices.waiting-approval') }}" class="list-group-item list-group-item-action">
                    پیش‌فاکتورهای در انتظار تایید مدیر فروش
                </a>
            </div>

            {{-- ماژول مالی --}}
            <h6 class="mb-2">مالی</h6>
            <div class="list-group mb-3">
                <a href="{{ route('finance.pre-invoices.approved-by-sales') }}" class="list-group-item list-group-item-action">
                    پیش‌فاکتورهای تایید شده برای صدور فاکتور
                </a>
                <a href="{{ route('invoices.index') }}" class="list-group-item list-group-item-action">
                    لیست فاکتورها
                </a>
                <a href="{{ route('reports.invoices.debtors') }}" class="list-group-item list-group-item-action">
                    گزارش بدهکاران
                </a>
                <a href="{{ route('reports.plans.due') }}" class="list-group-item list-group-item-action">
                    اقساط سررسید شده
                </a>
            </div>

            {{-- محصولات و دسته‌بندی‌ها --}}
            <h6 class="mb-2">محصولات</h6>
            <div class="list-group mb-3">
                <a href="{{ route('product-categories.index') }}" class="list-group-item list-group-item-action">
                    دسته‌بندی محصولات
                </a>
                <a href="{{ route('products.index') }}" class="list-group-item list-group-item-action">
                    لیست محصولات
                </a>
                <a href="{{ route('products.create') }}" class="list-group-item list-group-item-action">
                    افزودن محصول جدید
                </a>
            </div>

            {{-- کاربران و نقش‌ها --}}
            <h6 class="mb-2">کاربران</h6>
            <div class="list-group mb-3">
                <a href="{{ route('users.index') }}" class="list-group-item list-group-item-action">
                    لیست کاربران
                </a>
                <a href="{{ route('users.create') }}" class="list-group-item list-group-item-action">
                    ایجاد کاربر جدید
                </a>
            </div>
        </div>
    </div> <!-- end slimscroll-menu-->
</div>

<div class="rightbar-overlay"></div>
