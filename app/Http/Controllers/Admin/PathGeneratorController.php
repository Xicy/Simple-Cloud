<?php

namespace App\Http\Controllers\Admin;

use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\PathGenerator\BasePathGenerator;

class PathGeneratorController extends BasePathGenerator
{
    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media) . '/';
    }

    public function getPath(Media $media): string
    {
        return $media->id . '/';
    }

}
