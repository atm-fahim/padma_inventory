<?php

namespace App\Traits;

use Image;
use Illuminate\Http\UploadedFile;

trait ImageUploadTraits
{


    public function slideImage($imgPutPath, $image, $imgReadPath)
    {
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        //------------------------------
        if (!file_exists($imgPutPath)) {
            mkdir($imgPutPath, 0777, true);
        }
        $imgFile = Image::make($image->getRealPath());
        $imgFile->resize(900, 550, function ($constraint) {
            $constraint->aspectRatio();
        })->save($imgPutPath . '/' . $imageName);
        $image->move($imgPutPath, $imageName);
        return $imgReadPath . '/' . $imageName;
    }

    public function displayImage($imgPutPath, $image, $imgReadPath)
    {
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        //------------------------------
        if (!file_exists($imgPutPath)) {
            mkdir($imgPutPath, 0777, true);
        }
        $imgFile = Image::make($image->getRealPath());
        $imgFile->resize(500, 500, function ($constraint) {
            $constraint->aspectRatio();
        })->save($imgPutPath . '/' . $imageName);
        $image->move($imgPutPath, $imageName);
        return $imgReadPath . '/' . $imageName;
    }

    public function thumbnilImage($imgPutPath, $image, $imgReadPath)
    {
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        //------------------------------
        if (!file_exists($imgPutPath)) {
            mkdir($imgPutPath, 0777, true);
        }
        $imgFile = Image::make($image->getRealPath());
        $imgFile->resize(150, 150, function ($constraint) {
            $constraint->aspectRatio();
        })->save($imgPutPath . '/' . $imageName);
        $image->move($imgPutPath, $imageName);
        return $imgReadPath . '/' . $imageName;
    }

}
