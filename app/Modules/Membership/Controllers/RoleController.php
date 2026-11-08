<?php

namespace Modules\Membership\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\Membership\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('members')->orderBy('name')->paginate(15);

        return view('membership.roles.index', compact('roles'));
    }

    public function create()
    {
        return view('membership.roles.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:roles,slug'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['name']);
        $role = Role::create($data);

        return redirect()
            ->route('roles.index')
            ->with('success', "Role '{$role->name}' berhasil ditambahkan.");
    }

    public function edit(Role $role)
    {
        return view('membership.roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('roles', 'slug')->ignore($role->id)],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $data['slug'] = $this->uniqueSlug(
            $data['slug'] ?? null,
            $data['name'] ?? $role->name,
            $role->id
        );

        $role->update($data);

        return redirect()
            ->route('roles.index')
            ->with('success', "Role '{$role->name}' berhasil diperbarui.");
    }

    public function destroy(Role $role)
    {
        if ($role->members()->exists()) {
            return back()->withErrors([
                'role' => "Role '{$role->name}' masih digunakan anggota dan tidak dapat dihapus.",
            ]);
        }

        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', "Role '{$role->name}' berhasil dihapus.");
    }

    private function uniqueSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $name) ?: Str::random(8);
        $candidate = $base;
        $counter = 2;

        while (
            Role::where('slug', $candidate)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $candidate = $base . '-' . $counter++;
        }

        return $candidate;
    }
}
