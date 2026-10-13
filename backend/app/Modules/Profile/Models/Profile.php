<?php

namespace App\Modules\Profile\Models;

use App\Models\User;
use App\Modules\Profile\Enums\ContractType;
use App\Modules\Profile\Enums\EmploymentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Profile extends Model
{
    public const PHOTO_DISK = 'public';

    protected $table = 'profiles';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'birth_date',
        'email',
        'phone',
        'address',
        'photo_path',
        'employment_type',
        'contributes_social_security',
        'contract_type',
        'monthly_income',
        'family_reference_name',
        'family_reference_relationship',
        'family_reference_phone',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'employment_type' => EmploymentType::class,
            'contributes_social_security' => 'boolean',
            'contract_type' => ContractType::class,
            'monthly_income' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path
            ? Storage::disk(self::PHOTO_DISK)->url($this->photo_path)
            : null;
    }
}
