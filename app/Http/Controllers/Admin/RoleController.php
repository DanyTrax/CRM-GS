<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });

        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'array',
        ]);

        $role = Role::create(['name' => $request->name]);
        
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rol creado exitosamente');
    }

    public function update(Request $request, Role $role)
    {
        // Proteger Super Administrador
        if ($role->name === 'Super Administrador') {
            return back()->with('error', 'No se puede modificar el rol Super Administrador');
        }

        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'array',
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rol actualizado exitosamente');
    }

    public function destroy(Role $role)
    {
        // Proteger Super Administrador
        if ($role->name === 'Super Administrador') {
            return back()->with('error', 'No se puede eliminar el rol Super Administrador');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rol eliminado exitosamente');
    }
}
