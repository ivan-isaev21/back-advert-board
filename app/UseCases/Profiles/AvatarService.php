<?php

namespace App\UseCases\Profiles;

use App\Models\User;
use App\UseCases\File\FileService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AvatarService
{
    private $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Method create
     *
     * @param User $user 
     * @param UploadedFile $file 
     *
     * @return void
     */
    public function create(User $user, UploadedFile $file)
    {
        DB::transaction(function () use ($user, $file) {
            $hash = $this->fileService->getFileHash($file);
            $path = $this->getAvatarPath($file, $hash);

            $user->update([
                'avatar_hash' => $hash,
                'avatar_path' => $path
            ]);
        });
    }


    /**
     * Method delete
     *
     * @param User $user
     *
     * @return void
     */
    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            $hash = $user->avatar_hash;
            $path = $user->avatar_path;

            $user->update([
                'avatar_hash' => null,
                'avatar_path' => null
            ]);

            if ($hash and !$this->findUserByAvatarHash($hash)) {
                $this->fileService->deleteFile($path);
            }
        });
    }


    /**
     * Method getAvatarPath
     *
     * @param UploadedFile $file 
     * @param string $hash 
     *
     * @return string
     */
    public function getAvatarPath(UploadedFile $file, string $hash): string
    {
        $user = $this->findUserByAvatarHash($hash);
        $dir = 'avatars';

        if ($user and Storage::exists($user->avatar_path)) {
            return $user->avatar_path;
        }

        return $this->fileService->saveFile($file, $dir);
    }

    /**
     * Method findUserByAvatarHash
     *
     * @param string $hash 
     *
     * @return User|null
     */
    public function findUserByAvatarHash(string $hash): User|null
    {
        return User::forHash($hash)->first();
    }
}
