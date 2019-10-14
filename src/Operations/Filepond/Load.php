<?php

namespace MorningTrain\Laravel\Fields\Files\Operations\Filepond;

use Illuminate\Support\Facades\Storage;
use MorningTrain\Laravel\Fields\Files\Filepond;
use MorningTrain\Laravel\Resources\Support\Contracts\Operation;

class Load extends Operation
{

    const ROUTE_METHOD = 'get';

    public function handle($model = null)
    {
        $info = Filepond::getInfoFromServerId(request()->route('filepond'));
        $name = "{$info->name}.{$info->extension}";

        return Storage::disk($info->disk)->response($info->path, $name);
    }

}
