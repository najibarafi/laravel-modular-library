<?php

namespace Modules\Membership\Services;

use Modules\Membership\Models\Member;
use Modules\Shared\Contracts\MemberServiceInterface;

/**
 * Implementasi contract Membership Context.
 *
 * Circulation tidak pernah menyentuh model Member secara langsung;
 * semua perubahan status anggota lewat service ini.
 */
class MemberService implements MemberServiceInterface
{
    public function getMemberForLoan(int $memberId): ?array
    {
        $member = Member::find($memberId);

        if (! $member) {
            return null;
        }

        return [
            'id' => $member->id,
            'name' => $member->name,
            'status' => $member->status,
            'active_loans' => $member->active_loans,
            'max_loans' => $member->max_loans,
        ];
    }

    public function isEligibleToBorrow(int $memberId): bool
    {
        $member = Member::find($memberId);

        return $member !== null && $member->isEligible();
    }

    public function markAsBorrowing(int $memberId): bool
    {
        $member = Member::lockForUpdate()->find($memberId);

        if (! $member) {
            return false;
        }

        $member->increment('active_loans');
        $member->update(['status' => Member::STATUS_BORROWING]);

        return true;
    }

    public function markAsReturned(int $memberId): bool
    {
        $member = Member::lockForUpdate()->find($memberId);

        if (! $member) {
            return false;
        }

        if ($member->active_loans > 0) {
            $member->decrement('active_loans');
        }

        // Hanya kembalikan ke status active jika sebelumnya memang sedang borrowing.
        // Status suspended/inactive tidak boleh otomatis berubah.
        if ($member->active_loans <= 0 && $member->status === Member::STATUS_BORROWING) {
            $member->update(['status' => Member::STATUS_ACTIVE]);
        }

        return true;
    }

    public function getEligibleMembers(): array
    {
        return Member::eligible()
            ->orderBy('name')
            ->get()
            ->map(fn (Member $member) => [
                'id' => $member->id,
                'name' => $member->name,
                'membership_number' => $member->membership_number,
                'active_loans' => $member->active_loans,
                'max_loans' => $member->max_loans,
            ])
            ->all();
    }
}
