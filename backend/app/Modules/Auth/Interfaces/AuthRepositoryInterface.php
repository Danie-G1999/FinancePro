<?php

namespace App\Modules\Auth\Interfaces;

use App\Models\User;
use App\Shared\Repositories\BaseRepositoryInterface;

interface AuthRepositoryInterface extends BaseRepositoryInterface
{
    public function findByEmail(string $email): ?User;
}
