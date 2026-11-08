<?php

namespace Modules\Membership\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Membership\Models\Member;
use Modules\Membership\Models\Role;
use Modules\Membership\Requests\StoreMemberRequest;
use Modules\Membership\Requests\UpdateMemberRequest;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $members = ($request->boolean('trashed') ? Member::onlyTrashed() : Member::query())
            ->with(['memberships', 'roles'])
            ->withCount('memberships')
            ->when($search = trim((string) $request->input('search')), fn ($q) => $q->search($search))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('membership.members.index', compact('members'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();

        return view('membership.members.create', compact('roles'));
    }

    public function store(StoreMemberRequest $request)
    {
        $data = $request->validated();
        $roleIds = $data['role_ids'] ?? [];

        unset(
            $data['role_ids'],
            $data['membership_type'],
            $data['membership_start_date'],
            $data['membership_end_date'],
            $data['membership_fee'],
        );

        $member = DB::transaction(function () use ($request, $data, $roleIds) {
            $member = Member::create($data);

            $member->memberships()->create([
                'type' => $request->input('membership_type'),
                'start_date' => $request->input('membership_start_date'),
                'end_date' => $request->input('membership_end_date'),
                'fee' => $request->input('membership_fee'),
                'status' => 'active',
            ]);

            $this->syncRoles($member, $roleIds);

            return $member;
        });

        return redirect()
            ->route('members.show', $member)
            ->with('success', "Anggota '{$member->name}' berhasil ditambahkan.");
    }

    public function show(Member $member)
    {
        $member->load(['memberships', 'roles']);
        $roles = Role::orderBy('name')->get();
        $selectedRoles = $member->roles->pluck('id')->all();

        return view('membership.members.show', compact('member', 'roles', 'selectedRoles'));
    }

    public function edit(Member $member)
    {
        $roles = Role::orderBy('name')->get();
        $selectedRoles = $member->roles()->pluck('roles.id')->all();

        return view('membership.members.edit', compact('member', 'roles', 'selectedRoles'));
    }

    public function update(UpdateMemberRequest $request, Member $member)
    {
        $data = $request->validated();
        $hasRoles = array_key_exists('role_ids', $data);
        $roleIds = $data['role_ids'] ?? [];
        unset($data['role_ids']);

        DB::transaction(function () use ($member, $data, $hasRoles, $roleIds) {
            $member->update($data);

            if ($hasRoles) {
                $this->syncRoles($member, $roleIds);
            }
        });

        return redirect()
            ->route('members.show', $member)
            ->with('success', "Anggota '{$member->name}' berhasil diperbarui.");
    }

    public function destroy(Member $member)
    {
        $member->delete();

        return redirect()
            ->route('members.index')
            ->with('success', "Anggota '{$member->name}' berhasil dihapus (soft delete).");
    }

    public function restore(int $id)
    {
        $member = Member::withTrashed()->findOrFail($id);
        $member->restore();

        return redirect()
            ->route('members.index')
            ->with('success', "Anggota '{$member->name}' berhasil dipulihkan.");
    }

    public function updateRoles(Request $request, Member $member)
    {
        $data = $request->validate([
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ]);

        $this->syncRoles($member, $data['role_ids'] ?? []);

        return redirect()
            ->route('members.show', $member)
            ->with('success', 'Role anggota berhasil diperbarui.');
    }

    private function syncRoles(Member $member, array $roleIds): void
    {
        $roleIds = array_map('intval', $roleIds);

        $existing = $member->roles()
            ->get()
            ->mapWithKeys(fn (Role $role) => [$role->id => $role->pivot->assigned_at]);

        $sync = [];

        foreach ($roleIds as $roleId) {
            $sync[$roleId] = [
                'assigned_at' => $existing->get($roleId) ?? now(),
            ];
        }

        $member->roles()->sync($sync);
    }
}
