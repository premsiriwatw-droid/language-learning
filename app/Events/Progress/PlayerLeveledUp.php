<?php

namespace App\Events\Progress;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class PlayerLeveledUp implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public readonly int $userId, public readonly int $level) {}
}
