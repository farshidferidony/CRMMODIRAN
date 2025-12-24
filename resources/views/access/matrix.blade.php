@extends('layouts.master')
@section('title','ماتریس دسترسی')

@section('content')
@component('common-components.breadcrumb')
    @slot('pagetitle') تنظیمات @endslot
    @slot('title') ماتریس دسترسی @endslot
@endcomponent

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#tab-roles" role="tab">بر اساس نقش‌ها</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-users" role="tab">استثنا برای کاربران</a>
    </li>
</ul>

<div class="tab-content mt-3">
    {{-- تب نقش‌ها (مثل قبل) --}}
    <div class="tab-pane active" id="tab-roles" role="tabpanel">
        <div class="tab-pane active" id="tab-roles" role="tabpanel">
            <div class="card">
                <div class="card-body">

                    <form method="POST" action="{{ route('access.matrix.updateRoles') }}">
                        @csrf

                        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                            <table class="table table-sm table-bordered align-middle">
                                <thead class="table-light">
                                <tr>
                                    <th>پرمیشن</th>
                                    <th>گروه</th>
                                    @foreach($roles as $role)
                                        <th class="text-center">{{ $role->name }}</th>
                                    @endforeach
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($permissions as $permission)
                                    <tr>
                                        <td>
                                            <strong>{{ $permission->label ?? $permission->name }}</strong>
                                            <div class="text-muted small">{{ $permission->name }}</div>
                                        </td>
                                        <td>{{ $permission->group ?? '' }}</td>

                                        @foreach($roles as $role)
                                            @php
                                                $hasPermission = $role->permissions->contains('id', $permission->id);
                                            @endphp
                                            <td class="text-center">
                                                <input
                                                    type="checkbox"
                                                    name="role_permissions[{{ $role->id }}][]"
                                                    value="{{ $permission->id }}"
                                                    @checked($hasPermission)
                                                >
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">
                            ذخیره دسترسی نقش‌ها
                        </button>
                    </form>

                </div>
            </div>
        </div>

    </div>

    {{-- تب کاربران --}}
    <div class="tab-pane" id="tab-users" role="tabpanel">
        <div class="card">
            <div class="card-body">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">انتخاب کاربر</label>
                        <select id="am-user-select" class="form-select">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="text-muted small">
                            برای هر پرمیشن، وضعیت کاربر را تنظیم کن:
                            <span class="badge bg-secondary">ارث‌بری از نقش (none)</span>
                            <span class="badge bg-success">اجازه (allow)</span>
                            <span class="badge bg-danger">ممنوع (deny)</span>
                        </div>
                    </div>
                </div>

                <form id="am-user-form" method="POST" action="{{ route('access.matrix.updateUsers') }}">
                    @csrf
                    <input type="hidden" name="user_id" id="am-user-id">

                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-sm table-bordered align-middle">
                            <thead class="table-light">
                            <tr>
                                <th>پرمیشن</th>
                                <th>گروه</th>
                                <th>ارث‌بری (نقش)</th>
                                <th>وضعیت override</th>
                                <th>نتیجه موثر</th>
                            </tr>
                            </thead>
                            <tbody id="am-permissions-body">
                            {{-- با JS پر می‌شود --}}
                            </tbody>
                        </table>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">
                        ذخیره استثناهای کاربر
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    (function () {
        const userSelect   = document.getElementById('am-user-select');
        const userIdInput  = document.getElementById('am-user-id');
        const tbody        = document.getElementById('am-permissions-body');

        if (!userSelect) return;

        function loadUserPermissions(userId) {
            if (!userId) return;

            userIdInput.value = userId;
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">در حال بارگذاری...</td></tr>';

            fetch("{{ url('/access-matrix/user') }}/" + userId)
                .then(res => res.json())
                .then(data => {
                    renderPermissions(data.permissions || []);
                })
                .catch(err => {
                    console.error(err);
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">خطا در بارگذاری داده‌ها</td></tr>';
                });
        }

        function renderPermissions(perms) {
            if (!perms.length) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">هیچ پرمیشنی یافت نشد.</td></tr>';
                return;
            }

            let html = '';

            perms.forEach(p => {
                const inheritedBadge = p.inherited
                    ? '<span class="badge bg-success">دارد</span>'
                    : '<span class="badge bg-secondary">ندارد</span>';

                const effectiveBadge = p.effective
                    ? '<span class="badge bg-success">فعال</span>'
                    : '<span class="badge bg-secondary">غیرفعال</span>';

                html += `
<tr>
    <td>
        <strong>${p.label}</strong>
        <div class="text-muted small">${p.name}</div>
    </td>
    <td>${p.group ?? ''}</td>
    <td class="text-center">${inheritedBadge}</td>
    <td class="text-center">
        <select name="overrides[${p.id}]" class="form-select form-select-sm">
            <option value="none"  ${p.override_value === 'none'  ? 'selected' : ''}>ارث‌بری (none)</option>
            <option value="allow" ${p.override_value === 'allow' ? 'selected' : ''}>اجازه (allow)</option>
            <option value="deny"  ${p.override_value === 'deny'  ? 'selected' : ''}>ممنوع (deny)</option>
        </select>
    </td>
    <td class="text-center">${effectiveBadge}</td>
</tr>`;
            });

            tbody.innerHTML = html;
        }

        // هندل تغییر کاربر
        userSelect.addEventListener('change', function () {
            loadUserPermissions(this.value);
        });

        // بارگذاری اولیه برای اولین کاربر
        if (userSelect.value) {
            loadUserPermissions(userSelect.value);
        }
    })();
</script>
@endsection

