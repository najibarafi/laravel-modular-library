<?php

namespace Modules\Shared\Contracts;

/**
 * Contract lintas bounded context: Membership -> Circulation.
 *
 * Circulation tidak boleh mengubah model Member secara langsung.
 * Perubahan status anggota dilakukan melalui contract ini.
 */
interface MemberServiceInterface
{
    /**
     * Ambil data anggota yang dibutuhkan untuk membuat peminjaman.
     *
     * @return array{id:int,name:string,status:string,active_loans:int,max_loans:int}|null
     */
    public function getMemberForLoan(int $memberId): ?array;

    /**
     * Apakah anggota boleh meminjam buku?
     */
    public function isEligibleToBorrow(int $memberId): bool;

    /**
     * Tandai anggota sedang memiliki peminjaman aktif.
     */
    public function markAsBorrowing(int $memberId): bool;

    /**
     * Tandai satu peminjaman anggota sudah dikembalikan.
     */
    public function markAsReturned(int $memberId): bool;

    /**
     * Daftar anggota yang eligible untuk form peminjaman.
     *
     * @return array<int,array{id:int,name:string,membership_number:string,active_loans:int,max_loans:int}>
     */
    public function getEligibleMembers(): array;
}
