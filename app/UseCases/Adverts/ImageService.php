<?php

namespace App\UseCases\Adverts;

use App\Models\Adverts\Advert;
use App\Models\Adverts\Image;
use App\UseCases\File\FileService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    private $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function create(Advert $advert, UploadedFile $file, int $index): Image
    {
        $hash = $this->fileService->getFileHash($file);
        $path = $this->getImagePath($file, $hash);
        $name = $file->getClientOriginalName();

        return Image::create([
            'advert_id' => $advert->id,
            'file_hash' => $hash,
            'file_path' => $path,
            'file_original_name' => $name,
            'index' => $index
        ]);
    }

    /**
     * Method delete
     *
     * @param Image $image 
     *
     * @return void
     */
    public function delete(Image $image): void
    {
        $hash = $image->file_hash;
        $path = $image->file_path;
        $image->delete();
        if (!$this->findImageByHash($hash)) {
            $this->fileService->deleteFile($path);
        }
    }

    /**
     * Method getImagePath
     *
     * @param UploadedFile $file 
     * @param string $hash 
     *
     * @return string
     */
    public function getImagePath(UploadedFile $file, string $hash): string
    {
        $image = $this->findImageByHash($hash);
        $dir = 'adverts';

        if ($image and Storage::exists($image->file_path)) {
            return $image->file_path;
        }

        return $this->fileService->saveFile($file, $dir);
    }

    /**
     * Method findImageByHash
     *
     * @param string $hash 
     *
     * @return Image
     */
    public function findImageByHash(string $hash): Image|null
    {
        return Image::forHash($hash)->first();
    }
}
