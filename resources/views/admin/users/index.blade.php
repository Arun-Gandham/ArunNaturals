@extends('layouts.app')

@section('content')
<div class="container">
    <h1>All Users</h1>

    <!-- Create User Form -->
    <div class="mb-4">
        <form method="POST" action="{{ route('admin.users.create') }}">
            @csrf
            <div class="row g-2 align-items-end">
                <div class="col-auto">
                    <input type="text" name="name" class="form-control" placeholder="Name" required>
                </div>
                <div class="col-auto">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                </div>
                <div class="col-auto">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <div class="col-auto">
                    <select name="role" class="form-select">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </div>
        </form>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Change Role</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->role }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.users.updateRole', $user->id) }}" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <select name="role" class="form-select form-select-sm d-inline w-auto">
                            <option value="user" @if($user->role=='user') selected @endif>User</option>
                            <option value="admin" @if($user->role=='admin') selected @endif>Admin</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-secondary">Update</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
