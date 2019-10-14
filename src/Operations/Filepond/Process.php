<?php

namespace MorningTrain\Laravel\Fields\Files\Operations\Filepond;

use Illuminate\Http\UploadedFile;
use MorningTrain\Laravel\Fields\Files\Filepond;
use MorningTrain\Laravel\Resources\Support\Contracts\Operation;
use Illuminate\Support\Facades\Response;

class Process extends Operation
{

    const ROUTE_METHOD = 'post';

    public function handle($model = null)
    {
        $file = request()->file('filepond');

        if (!file_exists(Filepond::getBasePath())) {
            mkdir(Filepond::getBasePath());
        }

        $filePath = tempnam(Filepond::getBasePath(), "laravel-filepond-");

        $filePathParts = pathinfo($filePath);

        $originalName = pathinfo($file->getClientOriginalName())['filename'];
        $originalExtension = $file->getClientOriginalExtension();

        $info = (object) [
            'name' => $originalName,
            'extension' => $originalExtension,
            'path' => $filePath
        ];

        if (!($file instanceof UploadedFile) || !$file->move($filePathParts['dirname'], $filePathParts['basename'])) {
            return Response::make('Could not save file', 500);
        }

        return Response::make(Filepond::getServerIdFromInfo($info), 200);
    }

}
