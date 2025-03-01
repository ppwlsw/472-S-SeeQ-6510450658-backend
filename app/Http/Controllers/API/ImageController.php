<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function show(Request $request, string $image) {
        $appUrl = env('APP_URL');
        $image_path = str_replace($appUrl, '', $image);
        $image_path = str_replace( '+', '/', $image_path);
        return response(Storage::disk('s3')->get($image_path))
            ->header('Content-Type', 'image/png');
    }
}
