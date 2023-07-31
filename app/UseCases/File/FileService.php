<?php

namespace App\UseCases\File;

use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileService
{
    /**
     * Method getFileHash
     *
     * @param $file 
     *
     * @return string
     */
    public function getFileHash(UploadedFile $file): string
    {
        $hash = sha1_file($file->getRealPath());
        return $hash ?? '';
    }


    /**
     * Method saveFile
     *
     * @param UploadedFile $file 
     * @param string $dir 
     *
     * @return string
     */
    public function saveFile(UploadedFile $file, string $dir): string
    {
        $path = $file->store('public/' . $dir);
        return $path;
    }

    /**
     * Method deleteFile
     *
     * @param $filePath 
     *
     * @return void
     */
    public function deleteFile($filePath)
    {
        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
        }
    }

    /**
     * Method downloadFile
     *
     * @param $filePath 
     * @param $fileName 
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadFile($filePath, $fileName = null)
    {
        if (Storage::exists($filePath)) {
            return Storage::download($filePath, $fileName);
        }
        throw new DomainException('File is not exists.');
    }
}
