<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request, $id = null): JsonResponse
    {
        try {
            // ── Detail by ID ──
            if ($id !== null) {
                $user = User::find($id);
                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User tidak ditemukan'
                    ], 404);
                }
                return response()->json([
                    'success' => true,
                    'message' => 'Berhasil mengambil detail user',
                    'data'    => $user
                ], 200);
            }

            // ── List dengan filter, sort, pagination ──
            $query = User::query();

            // Search
            if ($request->filled('cari')) {
                $query->where(function ($q) use ($request) {
                    $q->where('name',  'LIKE', '%' . $request->cari . '%')
                    ->orWhere('email', 'LIKE', '%' . $request->cari . '%');
                });
            }

            // Sort
            $sortBy  = $request->get('sort_by', 'id');
            $sortDir = $request->get('sort_dir', 'desc');

            $allowedSort = ['id', 'name', 'email', 'recent_login'];
            if (!in_array($sortBy, $allowedSort)) $sortBy = 'id';
            if (!in_array($sortDir, ['asc', 'desc'])) $sortDir = 'desc';

            // Pagination
            $perPage = (int) $request->get('per_page', 15);
            $page    = (int) $request->get('page', 1);
            $total   = $query->count();

            $data = $query->orderBy($sortBy, $sortDir)
                        ->skip(($page - 1) * $perPage)
                        ->take($perPage)
                        ->get();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengambil data user',
                'data'    => $data,
                'meta'    => [
                    'current_page' => $page,
                    'per_page'     => $perPage,
                    'total'        => $total,
                    'has_more'     => ($page * $perPage) < $total,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'nullable|in:admin,user',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }
        try {
            $user = User::create([
                'name'         => $request->name,
                'email'        => $request->email,
                'password'     => Hash::make($request->password),
                'role'         => $request->role ?? 'user',
                'recent_login' => null,
            ]);
            return response()->json([
                'success' => true,
                'message' => 'User berhasil didaftarkan',
                'data'    => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendaftarkan user',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'role'     => 'required|in:admin,user',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }
            $updateData = [
                'name'  => $request->name,
                'email' => $request->email,
                'role'  => $request->role,
            ];
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }
            $user->update($updateData);
            return response()->json([
                'success' => true,
                'message' => 'Data user berhasil diperbarui',
                'data'    => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data user',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }
            $user->delete();
            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus user',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}