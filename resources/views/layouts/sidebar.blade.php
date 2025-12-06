<!-- ========== Left Sidebar Start ========== -->
<div class="vertical-menu">

    <!-- LOGO -->
    <div class="navbar-brand-box">
        <a href="{{url('index')}}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ URL::asset('/assets/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('/assets/images/logo-dark.png') }}" alt="" height="20">
            </span>
        </a>

        <a href="{{url('index')}}" class="logo logo-light">
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
                <li class="menu-title">مشتریان</li>

                <li>
                    <a href="javascript:void(0);" class="has-arrow waves-effect">
                        <i class="uil-users-alt"></i>
                        <span>مشتریان</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('customers.index') }}">لیست مشتریان</a></li>
                        <li><a href="{{ route('customers.create') }}">افزودن مشتری</a></li>
                    </ul>
                </li>

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
                        <li><a href="{{ route('sources.index') }}">منابع تامین</a></li>
                    </ul>
                </li>

                {{-- پیش‌فاکتور و فروش --}}
                <li class="menu-title">پیش‌فاکتور و فروش</li>

                <li>
                    <a href="javascript:void(0);" class="has-arrow waves-effect">
                        <i class="uil-invoice"></i>
                        <span>پیش‌فاکتورها</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('pre-invoices.index') }}">لیست پیش‌فاکتورها</a></li>
                        <li><a href="{{ route('pre-invoices.create') }}">ثبت پیش‌فاکتور جدید</a></li>
                        {{-- اگر فیلتر داشتی، می‌توانی لینک‌های ویژه هم بگذاری --}}
                        <li><a href="{{ route('purchase-pre-invoices.index') }}">پیش‌فاکتورهای خرید</a></li>
                    </ul>
                </li>

                {{-- ماژول خرید --}}
                <li>
                    <a href="javascript:void(0);" class="has-arrow waves-effect">
                        <i class="uil-cart"></i>
                        <span>ماژول خرید</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('buyer.assignments.index') }}">وظایف خریداران</a></li>
                        <li><a href="{{ route('purchase-manager.pre-invoices.ready-for-sales') }}">پیش‌فاکتورهای آماده ارسال به فروش</a></li>
                    </ul>
                </li>

                {{-- ماژول فروش / مدیر فروش --}}
                <li>
                    <a href="javascript:void(0);" class="has-arrow waves-effect">
                        <i class="uil-bill"></i>
                        <span>مدیر فروش</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('sales-manager.pre-invoices.priced') }}">پیش‌فاکتورهای قیمت‌گذاری‌شده</a></li>
                        <li><a href="{{ route('sales-manager.pre-invoices.waiting-approval') }}">در انتظار تایید مدیر فروش</a></li>
                    </ul>
                </li>

                {{-- مالی / فاکتورها --}}
                <li class="menu-title">مالی</li>

                <li>
                    <a href="javascript:void(0);" class="has-arrow waves-effect">
                        <i class="uil-wallet"></i>
                        <span>فاکتورها و مالی</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('invoices.index') }}">لیست فاکتورها</a></li>
                        <li><a href="{{ route('finance.pre-invoices.approved-by-sales') }}">پیش‌فاکتورهای تاییدشده برای صدور فاکتور</a></li>
                        <li>
                            <a href="{{ route('finance.pre-invoices.purchase-pre-invoices.index') }}">
                                <i class="uil uil-receipt"></i>
                                <span>پیش‌فاکتورهای خرید در انتظار مالی</span>
                            </a>
                        </li>
                        <li><a href="{{ route('reports.invoices.debtors') }}">گزارش بدهکاران</a></li>
                        <li><a href="{{ route('reports.plans.due') }}">اقساط سررسید</a></li>
                    </ul>
                </li>

                {{-- کاربران و نقش‌ها --}}
                <li class="menu-title">کاربران</li>

                <li>
                    <a href="javascript:void(0);" class="has-arrow waves-effect">
                        <i class="uil-user-circle"></i>
                        <span>کاربران و نقش‌ها</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('users.index') }}">لیست کاربران</a></li>
                        <li><a href="{{ route('users.create') }}">افزودن کاربر</a></li>
                    </ul>
                </li>
            </ul>

        </div>
        <!-- Sidebar -->
    </div>
</div>
<!-- Left Sidebar End -->
