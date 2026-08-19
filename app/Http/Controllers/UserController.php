<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index() {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }
        $users = User::orderBy('id', 'asc')->get();
        return view('user', compact('users'), [
            'judul' => 'User Management'
        ]);
    }

    public function create(Request $request) {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->back()->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,user',
            'password' => 'nullable|min:6'
        ]);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
            'role'  => $request->role,
        ];

        $passwordChanged = false;

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
            $passwordChanged = true;
        }

        $user->update($data);
        
        if (Auth::id() === $user->id && $passwordChanged) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')
                ->with('success', 'Password berhasil diubah. Silakan login ulang.');
        }

        return redirect()->back()
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }
        $user = User::findOrFail($id);
        if (Auth::id() === $user->id) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $user->delete();

            return redirect('/login')
                ->with('success', 'Akun berhasil dihapus.');
        }

        $user->delete();

        return redirect()->back()
            ->with('success', 'User berhasil dihapus.');
    }

}
