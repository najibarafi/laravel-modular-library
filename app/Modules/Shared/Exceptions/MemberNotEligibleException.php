<?php

namespace Modules\Shared\Exceptions;

use Exception;

class MemberNotEligibleException extends Exception
{
    public function __construct(private string $reason)
    {
        parent::__construct($reason);
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
                'error' => 'member_not_eligible',
            ], 422);
        }

        return back()->withInput()->withErrors([
            'member' => $this->getMessage(),
        ]);
    }
}
