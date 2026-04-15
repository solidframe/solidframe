<?php

declare(strict_types=1);

namespace App\Domain\Account\Port;

use App\Domain\Account\Account;
use App\Domain\Account\AccountId;

interface AccountRepository
{
    public function load(AccountId $id): Account;

    public function save(Account $account): void;
}
