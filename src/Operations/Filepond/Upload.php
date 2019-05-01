<?php

namespace MorningTrain\Laravel\Fields\Files\Operations\Filepond;

use MorningTrain\Laravel\Fields\Files\Support\Filepond;
use MorningTrain\Laravel\Resources\Support\Contracts\Operation;
use Illuminate\Support\Facades\Response;

class Upload extends Operation
{

    const ROUTE_METHOD = 'post';

    public function handle($model = null)
    {
        $filepond = new Filepond();

        $file = request()->file('file')[0];
        $filePath = tempnam(config('filepond.temporary_files_path'), "laravel-filepond");
        $filePathParts = pathinfo($filePath);

        if (!$file->move($filePathParts['dirname'], $filePathParts['basename'])) {
            return Response::make('Could not save file', 500);
        }

        return Response::make($filepond->getServerIdFromPath($filePath), 200);
    }

}
