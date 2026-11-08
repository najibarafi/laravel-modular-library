<?php

namespace Modules\Membership\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Membership\Models\Member;
use Modules\Membership\Models\Membership;
use Modules\Membership\Requests\StoreMembershipRequest;

class MembershipController extends Controller
{
    public function store(StoreMembershipRequest $request, Member $member)
    {
        DB::transaction(function () use ($request, $member) {
            // Hanya boleh ada satu keanggotaan aktif pada satu waktu.
            if ($request->input('status') === 'active') {
                $member->memberships()
                    ->where('status', 'active')
                    ->update(['status' => 'expired']);
            }

            $member->memberships()->create($request->validated());
        });

        return redirect()
            ->route('members.show', $member)
            ->with('success', 'Keanggotaan baru berhasil ditambahkan.');
    }

    public function destroy(Membership $membership)
    {
        $member = $membership->member;
        $membership->delete();

        return redirect()
            ->route('members.show', $member)
            ->with('success', 'Data keanggotaan berhasil dihapus.');
    }
}
