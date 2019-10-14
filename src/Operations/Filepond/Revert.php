<?php

namespace MorningTrain\Laravel\Fields\Files\Operations\Filepond;

use MorningTrain\Laravel\Fields\Files\Filepond;
use MorningTrain\Laravel\Resources\Support\Contracts\Operation;
use Illuminate\Support\Facades\Response;

class Revert extends Operation
{

    const ROUTE_METHOD = 'delete';

    public function handle($model = null)
    {
        $filePath = Filepond::getPathFromServerId(request()->getContent());

        if(unlink($filePath)) {
            return Response::make('', 200);
        } else {
            return Response::make('', 500);
        }
    }

}
