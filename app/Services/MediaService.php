<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class MediaService {

    public static function uploadImage($image) {
        try {
            $filename = $image->hashName();
            $filePath = "images/" . $filename;

            Storage::disk("public")->put("images/", $image);
        } catch (FileException $e) {
            throw $e;
        }
    }

    public static function removeImage($filePath) {
        try {
            Storage::delete($filePath);
        } catch (FileException $e) {
            throw $e;
        }
    }

}