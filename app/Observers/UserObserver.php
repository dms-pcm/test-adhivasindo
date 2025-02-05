<?php

namespace App\Observers;

use Illuminate\Support\Str;
use App\Models\User;

class UserObserver
{
    public function creating(User $item): void
    {
        $item->skey = Str::uuid()->toString();
    }
}
