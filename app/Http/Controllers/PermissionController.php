<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $permissions = Permission::all(); //Get all permissions

        return view('permissions.index')->with('permissions', $permissions);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::get();
        return view('permissions.create')->with('roles', $roles);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:40',
        ]);

        $name = $request['name'];
        $permission = new Permission();
        $permission->name = $name;

        $roles = $request['roles'];

        $permission->save();

        if (!empty($request['roles'])) { //If one or more role is selected
            foreach ($roles as $role) {
                $r = Role::where('id', '=', $role)->firstOrFail(); //Match input role to db record

                $permission = Permission::where('name', '=', $name)->first(); //Match input //permission to db record
                $r->givePermissionTo($permission);
            }
        }

            return redirect()->route('permissions.index')
                ->with('success','Permission created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        return redirect('permissions');

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        $roles = Role::get();
        return view('permissions.edit', compact(['permission', 'roles']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);
        $this->validate($request, [
            'name' => 'required|max:40',
        ]);

        $name = $request['name'];
        $roles = $request['roles'];


        // remove assign
        foreach (Role::all() as $role) {
            $r = Role::findById($role->id);
            $r->revokePermissionTo($permission);
        }

        $permission->fill(['id' => $id, 'name' => $name])->save();

        if (!empty($request['roles'])) { //If one or more role is selected

            foreach ($roles as $role) {
                $r = Role::where('id', '=', $role)->firstOrFail(); //Match input role to db record
                $r->givePermissionTo($permission);
            }
        }
        return redirect()->route('permissions.index')
            ->with('success',
                'Permission updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $permission = Permission::findOrFail($id);

        //Make it impossible to delete this specific permission
        if ($permission->name == "Administer roles & permissions") {
            return redirect()->route('permissions.index')
                ->with('flash_message',
                    'Cannot delete this Permission!');
        }

        $permission->delete();

        return redirect()->route('permissions.index')
            ->with('success',
                'Permission deleted!');

    }
}
