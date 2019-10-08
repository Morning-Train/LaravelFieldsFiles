<?php

namespace MorningTrain\Laravel\Fields\Files\Operations\Filepond;

use Illuminate\Support\Facades\File;
use MorningTrain\Laravel\Fields\Files\Support\Filepond;
use MorningTrain\Laravel\Resources\Support\Contracts\Operation;
use Illuminate\Support\Facades\Response;

class Load extends Operation
{

    const ROUTE_METHOD = 'get';

    public function handle($model = null)
    {
        $filepond = new Filepond();

        $path = storage_path('app/' . $filepond->getPathFromServerId(request()->route('filepond')));

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header('Content-Type', $type);

        return $response;
    }

}