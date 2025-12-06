@extends('layouts.master')
@section('title','کاربران')

@section('content')
@component('common-components.breadcrumb')
    @slot('pagetitle') مدیریت کاربران @endslot
    @slot('title') لیست کاربران @endslot
@endcomponent

<div class="card">
  <div class="card-header d-flex justify-content-between">
    <span>لیست کاربران</span>
    <a href="{{ route('users.create') }}" class="btn btn-success btn-sm">کاربر جدید</a>
  </div>

  <div class="card-body">
    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered align-middle">
      <thead>
        <tr>
          <th>#</th>
          <th>نام</th>
          <th>ایمیل</th>
          <th>نقش‌ها</th>
          <th>عملیات</th>
        </tr>
      </thead>
      <tbody>
        @foreach($users as $u)
          <tr>
            <td>{{ $u->id }}</td>
            <td>{{ $u->name }}</td>
            <td>{{ $u->email }}</td>
            <td>
              @foreach($u->roles as $r)
                <span class="badge bg-info">{{ $r->title }}</span>
              @endforeach
            </td>
            <td>
              <a href="{{ route('users.roles.edit',$u->id) }}"
                 class="btn btn-sm btn-primary">نقش‌ها</a>
              <a href="{{ route('users.edit',$u->id) }}"
                 class="btn btn-sm btn-warning">ویرایش</a>
              <form method="POST"
                    action="{{ route('users.destroy',$u->id) }}"
                    style="display:inline;">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-danger"
                        onclick="return confirm('حذف کاربر؟')">
                  حذف
                </button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>

    {{ $users->links() }}
  </div>
</div>
@endsection
