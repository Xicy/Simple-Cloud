<?php

namespace App\Http\Controllers\Admin;

use DateTimeInterface;
use Spatie\MediaLibrary\UrlGenerator\BaseUrlGenerator;
use Illuminate\Support\Facades\URL;

class UrlGeneratorController extends BaseUrlGenerator
{
    public function getUrl(): string
    {
        return URL::signedRoute("download", ["filename" => $this->getPath() ]);
    }

    public function getPath(): string
    {
        return $this->pathGenerator->getPath($this->media) . $this->media->file_name;
    }


    public function getTemporaryUrl(DateTimeInterface $expiration, array $options = []): string
    {
    }

    public function getResponsiveImagesDirectoryUrl(): string
    {
    }
}
