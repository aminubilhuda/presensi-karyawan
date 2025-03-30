<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::all();
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:roles,name',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.roles.index')
                ->withErrors($validator)
                ->withInput();
        }

        Role::create([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Peran berhasil ditambahkan!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:roles,name,' . $id,
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.roles.index')
                ->withErrors($validator)
                ->withInput();
        }

        $role->update([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Peran berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        
        // Periksa apakah ada pengguna yang menggunakan peran ini
        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Peran tidak dapat dihapus karena sedang digunakan oleh pengguna!');
        }
        
        $role->delete();
        
        return redirect()->route('admin.roles.index')
            ->with('success', 'Peran berhasil dihapus!');
    }
} 