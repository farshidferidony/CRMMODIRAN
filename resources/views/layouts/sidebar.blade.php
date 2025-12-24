<!-- ========== Left Sidebar Start ========== -->
<div class="vertical-menu">

    <!-- LOGO -->
    <div class="navbar-brand-box">
        <a href="{{ url('index') }}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ URL::asset('/assets/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('/assets/images/logo-dark.png') }}" alt="" height="20">
            </span>
        </a>

        <a href="{{ url('index') }}" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ URL::asset('/assets/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('/assets/images/logo-light.png') }}" alt="" height="20">
            </span>
        </a>
    </div>

    <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect vertical-menu-btn">
        <i class="fa fa-fw fa-bars"></i>
    </button>

    <div data-simplebar class="sidebar-menu-scroll">

        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu">
                {{-- عنوان کلی --}}
                <li class="menu-title">ناوبری</li>

                {{-- داشبورد --}}
                <li>
                    <a href="{{ url('index') }}">
                        <i class="uil-home-alt"></i>
                        <span>داشبورد</span>
                    </a>
                </li>

                {{-- مشتریان و شرکت‌ها --}}
                @canAccess('customers.view')
                    <li class="menu-title">مشتریان</li>

                    <li>
                        <a href="javascript:void(0);" class="has-arrow waves-effect">
                            <i class="uil-users-alt"></i>
                            <span>مشتریان</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><a href="{{ route('customers.index') }}">لیست مشتریان</a></li>

                            @canAccess('customers.create')
                                <li><a href="{{ route('customers.create') }}">افزودن مشتری</a></li>
                            @endcanAccess

                            @canAccess('customers.fast_search')
                                <li><a href="{{ route('ajax.customers.search') }}">جستجوی سریع</a></li>
                            @endcanAccess
                        </ul>
                    </li>
                @endcanAccess

                {{-- محصولات --}}
                <li class="menu-title">محصولات</li>

                <li>
                    <a href="javascript:void(0);" class="has-arrow waves-effect">
                        <i class="uil-store"></i>
                        <span>محصولات</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('product-categories.index') }}">دسته‌بندی‌ها</a></li>
                        <li><a href="{{ route('products.index') }}">لیست محصولات</a></li>
                        <li><a href="{{ route('products.create') }}">افزودن محصول</a></li>

                        @canAccess('sources.view')
                            <li><a href="{{ route('sources.index') }}">منابع تامین</a></li>
                        @endcanAccess
                    </ul>
                </li>

                {{-- پیش‌فاکتور و فروش --}}
                @canAccess('preinvoices.view')
                    <li class="menu-title">پیش‌فاکتور و فروش</li>

                    <li>
                        <a href="javascript:void(0);" class="has-arrow waves-effect">
                            <i class="uil-invoice"></i>
                            <span>پیش‌فاکتورها</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><a href="{{ route('pre-invoices.index') }}">لیست پیش‌فاکتورها</a></li>

                            @canAccess('preinvoices.create')
                                <li><a href="{{ route('pre-invoices.create') }}">ثبت پیش‌فاکتور جدید</a></li>
                            @endcanAccess

                            @canAccess('purchase.preinvoices.exec.index')
                                <li><a href="{{ route('purchase_pre_invoices.index') }}">پیش‌فاکتورهای خرید</a></li>
                            @endcanAccess
                        </ul>
                    </li>
                @endcanAccess

                {{-- ماژول خرید --}}
                @canAccess('purchase.buyer.assignments.view')
                    <li class="menu-title">ماژول خرید</li>

                    <li>
                        <a href="javascript:void(0);" class="has-arrow waves-effect">
                            <i class="uil-cart"></i>
                            <span>خریداران</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><a href="{{ route('buyer.assignments.index') }}">وظایف خریداران</a></li>
                        </ul>
                    </li>
                @endcanAccess

                @canAccess('purchase.manager.preinvoices.view')
                    <li>
                        <a href="javascript:void(0);" class="has-arrow waves-effect">
                            <i class="uil-briefcase"></i>
                            <span>مدیر خرید</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><a href="{{ route('purchase-manager.pre-invoices.ready-for-sales') }}">آماده ارسال به فروش</a></li>
                        </ul>
                    </li>
                @endcanAccess

                {{-- مدیر فروش --}}
                <li class="menu-title">مدیر فروش</li>

                <li>
                    <a href="javascript:void(0);" class="has-arrow waves-effect">
                        <i class="uil-bill"></i>
                        <span>پیش‌فاکتورهای فروش</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('sales-manager.pre-invoices.priced') }}">قیمت‌گذاری‌شده</a></li>
                        <li><a href="{{ route('sales-manager.pre-invoices.waiting-approval') }}">در انتظار تایید</a></li>
                    </ul>
                </li>

                {{-- مالی --}}
                @canAccess('invoices.view')
                    <li class="menu-title">مالی</li>

                    <li>
                        <a href="javascript:void(0);" class="has-arrow waves-effect">
                            <i class="uil-wallet"></i>
                            <span>فاکتورها</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><a href="{{ route('invoices.index') }}">لیست فاکتورها</a></li>

                            @canAccess('finance.preinvoices.from_approved_sales')
                                <li><a href="{{ route('finance.pre-invoices.approved-by-sales') }}">تاییدشده برای فاکتور</a></li>
                            @endcanAccess

                            @canAccess('finance.purchase_preinvoices.view')
                                <li><a href="{{ route('finance.pre-invoices.purchase-pre-invoices.index') }}">خرید در انتظار مالی</a></li>
                            @endcanAccess
                        </ul>
                    </li>

                    @canAccess('finance.payments.customer_pending')
                        <li>
                            <a href="javascript:void(0);" class="has-arrow waves-effect">
                                <i class="uil-credit-card"></i>
                                <span>پرداخت‌ها</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li><a href="{{ route('finance.payments.customer.pending') }}">پرداخت‌های در انتظار</a></li>
                            </ul>
                        </li>
                    @endcanAccess

                    @canAccess('reports.debtors.view')
                        <li>
                            <a href="javascript:void(0);" class="has-arrow waves-effect">
                                <i class="uil-chart"></i>
                                <span>گزارشات</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li><a href="{{ route('reports.invoices.debtors') }}">بدهکاران</a></li>
                                @canAccess('reports.plans_due.view')
                                    <li><a href="{{ route('reports.plans.due') }}">اقساط سررسید</a></li>
                                @endcanAccess
                            </ul>
                        </li>
                    @endcanAccess
                @endcanAccess

                {{-- کاربران و نقش‌ها + ماتریکس --}}
                @canAccess('users.view')
                    <li class="menu-title">کاربران</li>

                    <li>
                        <a href="javascript:void(0);" class="has-arrow waves-effect">
                            <i class="uil-user-circle"></i>
                            <span>کاربران</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><a href="{{ route('users.index') }}">لیست کاربران</a></li>

                            @canAccess('users.create')
                                <li><a href="{{ route('users.create') }}">افزودن کاربر</a></li>
                            @endcanAccess

                            @canAccess('users.roles.manage')
                                <li><a href="{{ route('users.roles.edit', 1) }}">مدیریت نقش‌ها (نمونه)</a></li>
                            @endcanAccess

                            @canAccess('access.matrix.manage')
                                <li><a href="{{ route('access.matrix.index') }}">ماتریکس دسترسی</a></li>
                            @endcanAccess
                        </ul>
                    </li>
                @endcanAccess

            </ul>

        </div>
        <!-- Sidebar -->
    </div>
</div>
<!-- Left Sidebar End -->
