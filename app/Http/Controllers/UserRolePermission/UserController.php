<?php
namespace App\Http\Controllers\UserRolePermission;

use App\Helpers\DataTable;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role as SpatieRole;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'active');
        $users  = match ($filter) {
            'trashed' => User::onlyTrashed()->with(['roles', 'warehouses'])->get(),
            'all' => User::withTrashed()->with(['roles', 'warehouses'])->get(),
            default => User::with(['roles', 'warehouses'])->get(),
        };

        return Inertia::render('UserRolePermission/User/Index', [
            'users'  => $users,
            'filter' => $filter,
            'roles'  => Role::all(),
        ]);
    }

    public function json(Request $request)
    {
        $search = $request->input('search.value', '');
        $filter = $request->input('filter', 'active');

        $query = match ($filter) {
            'trashed' => User::onlyTrashed()->with(['roles', 'warehouses']),
            'all' => User::withTrashed()->with(['roles', 'warehouses']),
            default => User::with(['roles', 'warehouses']),
        };

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $columns = ['id', 'name', 'email', 'created_at', 'updated_at'];
        if ($request->filled('order')) {
            $orderColumn = $columns[$request->order[0]['column']] ?? 'id';
            $query->orderBy($orderColumn, $request->order[0]['dir']);
        }

        $data = DataTable::paginate($query, $request);

        $data['data'] = collect($data['data'])->map(function ($user) {
            return [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'roles'   => $user->roles->pluck('name')->toArray(),
                'warehouses' => $user->warehouses->pluck('name')->toArray(),
                'trashed' => $user->trashed(),
                'actions' => '',
            ];
        });

        return response()->json($data);
    }

    public function create()
    {
        $roles = Role::all();
        $warehouses = Warehouse::all();
        return Inertia::render('UserRolePermission/User/Form', [
            'roles' => $roles,
            'warehouses' => $warehouses,
        ]);
    }

    public function store(Request $request)
    {
        $validationRules = [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id'  => 'required|exists:roles,id',
            'warehouse_ids' => 'nullable|array',
            'warehouse_ids.*' => 'exists:warehouses,id',
        ];

        $validatedData = $request->validate($validationRules);

        // Check if role is "user" to determine warehouse requirement
        $role = Role::find($validatedData['role_id']);
        $isUserRole = $role && strtolower($role->name) === 'user';

        // Validate warehouse selection for user role
        if ($isUserRole && (!isset($validatedData['warehouse_ids']) || empty($validatedData['warehouse_ids']))) {
            return back()->withErrors(['warehouse_ids' => 'Warehouse selection is required for user role.'])->withInput();
        }

        $user = User::create([
            'name'     => $validatedData['name'],
            'email'    => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
        ]);

        $spatieRole = SpatieRole::findById($validatedData['role_id']);
        $user->assignRole($spatieRole);

        // Attach warehouses if role is "user"
        if ($isUserRole && isset($validatedData['warehouse_ids'])) {
            $user->warehouses()->attach($validatedData['warehouse_ids']);
        }

        return redirect()->route('users.index')->with('success', 'User berhasil dibuat.');
    }

    public function edit($id)
    {
        $user  = User::withTrashed()->with(['roles', 'warehouses'])->findOrFail($id);
        $roles = Role::all();
        $warehouses = Warehouse::all();
        $user->role_id = $user->roles->first()->id ?? null;
        $user->warehouse_ids = $user->warehouses->pluck('id')->toArray();
        
        return Inertia::render('UserRolePermission/User/Form', [
            'user'  => $user,
            'roles' => $roles,
            'warehouses' => $warehouses,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validationRules = [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'role_id'  => 'required|exists:roles,id',
            'warehouse_ids' => 'nullable|array',
            'warehouse_ids.*' => 'exists:warehouses,id',
        ];

        $validatedData = $request->validate($validationRules);

        // Check if role is "user" to determine warehouse requirement
        $role = Role::find($validatedData['role_id']);
        $isUserRole = $role && strtolower($role->name) === 'user';

        // Validate warehouse selection for user role
        if ($isUserRole && (!isset($validatedData['warehouse_ids']) || empty($validatedData['warehouse_ids']))) {
            return back()->withErrors(['warehouse_ids' => 'Warehouse selection is required for user role.'])->withInput();
        }

        $user        = User::withTrashed()->findOrFail($id);
        $user->name  = $validatedData['name'];
        $user->email = $validatedData['email'];
        if ($request->filled('password')) {
            $user->password = bcrypt($validatedData['password']);
        }
        $user->save();

        $spatieRole = SpatieRole::findById($validatedData['role_id']);
        $user->syncRoles([$spatieRole]);

        // Sync warehouses based on role
        if ($isUserRole && isset($validatedData['warehouse_ids'])) {
            $user->warehouses()->sync($validatedData['warehouse_ids']);
        } else {
            // Remove all warehouse associations if not user role
            $user->warehouses()->detach();
        }

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }

    public function trashed()
    {
        $users = User::onlyTrashed()->with(['roles', 'warehouses'])->get();
        return Inertia::render('UserRolePermission/User/Trashed', [
            'users' => $users,
        ]);
    }

    public function restore($id)
    {
        User::onlyTrashed()->where('id', $id)->restore();
        return redirect()->route('users.index')->with('success', 'User berhasil dipulihkan.');
    }

    public function forceDelete($id)
    {
        User::onlyTrashed()->where('id', $id)->forceDelete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus secara permanen.');
    }
}
