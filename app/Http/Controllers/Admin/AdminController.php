<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function createUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,user',
        ]);
        \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
        ]);
        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function updateUserRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:admin,user',
        ]);
        $user = \App\Models\User::findOrFail($id);
        $user->role = $request->role;
        $user->save();
        return redirect()->route('admin.users.index')->with('success', 'User role updated.');
    }

    public function insights()
    {
        $insights = \App\Models\PageVisit::select('url')
            ->selectRaw('count(*) as visits')
            ->groupBy('url')
            ->orderByDesc('visits')
            ->limit(10)
            ->get();
        return view('admin.insights', compact('insights'));
    }

    public function users()
    {
        $users = \App\Models\User::all();
        return view('admin.users.index', compact('users'));
    }

    public function orders()
    {
        $orders = \App\Models\Order::latest()->get();
        return view('admin.orders.index', compact('orders'));
    }

    public function ordersCreate()
    {
        return view('admin.orders.create');
    }
}
