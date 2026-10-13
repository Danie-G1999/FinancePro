<?php

namespace App\Modules\Profile\Services;

use App\Models\User;
use App\Modules\Profile\Enums\EmploymentType;
use App\Modules\Profile\Interfaces\ProfileRepositoryInterface;
use App\Modules\Profile\Models\Profile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public const PHOTO_DISK = Profile::PHOTO_DISK;

    private const PHOTO_DIRECTORY = 'profile-photos';

    public function __construct(
        private ProfileRepositoryInterface $repository
    ) {}

    /** Si el usuario aún no tiene perfil, devuelve uno sin guardar prellenado con sus datos de cuenta. */
    public function getForUser(User $user): Profile
    {
        return $this->repository->findByUser($user->id)
            ?? $this->defaultProfile($user);
    }

    public function updateForUser(User $user, array $data): Profile
    {
        $data = $this->normalizeEmployment($data);

        return DB::transaction(function () use ($user, $data) {
            $profile = $this->repository->updateOrCreateForUser($user->id, $data);

            $user->update([
                'name' => trim("{$profile->first_name} {$profile->last_name}"),
            ]);

            return $profile;
        });
    }

    public function updatePhoto(User $user, UploadedFile $photo): Profile
    {
        $previous = $this->ensureProfileExists($user)->photo_path;

        $path = $photo->store(self::PHOTO_DIRECTORY, self::PHOTO_DISK);

        $profile = $this->repository->updateOrCreateForUser($user->id, [
            'photo_path' => $path,
        ]);

        $this->deletePhotoFile($previous);

        return $profile;
    }

    public function removePhoto(User $user): Profile
    {
        $previous = $this->ensureProfileExists($user)->photo_path;

        $profile = $this->repository->updateOrCreateForUser($user->id, [
            'photo_path' => null,
        ]);

        $this->deletePhotoFile($previous);

        return $profile;
    }

    /** Los campos que dependen del tipo de empleo se limpian cuando no aplican. */
    private function normalizeEmployment(array $data): array
    {
        if ($data['employment_type'] === EmploymentType::EMPLOYEE->value) {
            $data['contributes_social_security'] = null;
        } else {
            $data['contract_type'] = null;
        }

        return $data;
    }

    /** La foto se puede subir antes de llenar el formulario, así que se crea un perfil mínimo. */
    private function ensureProfileExists(User $user): Profile
    {
        $existing = $this->repository->findByUser($user->id);

        if ($existing) {
            return $existing;
        }

        $default = $this->defaultProfile($user);

        return $this->repository->updateOrCreateForUser($user->id, [
            'first_name' => $default->first_name,
            'last_name' => $default->last_name,
            'email' => $default->email,
            'employment_type' => $default->employment_type->value,
        ]);
    }

    private function defaultProfile(User $user): Profile
    {
        [$firstName, $lastName] = array_pad(
            explode(' ', trim($user->name), 2),
            2,
            ''
        );

        return new Profile([
            'user_id' => $user->id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $user->email,
            'employment_type' => EmploymentType::EMPLOYEE,
        ]);
    }

    private function deletePhotoFile(?string $path): void
    {
        if ($path) {
            Storage::disk(self::PHOTO_DISK)->delete($path);
        }
    }
}
