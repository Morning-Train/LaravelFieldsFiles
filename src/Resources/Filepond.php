<?php

namespace MorningTrain\Laravel\Fields\Files\Resources;

use MorningTrain\Laravel\Fields\Files\Operations\Filepond\Delete;
use MorningTrain\Laravel\Fields\Files\Operations\Filepond\Upload;
use MorningTrain\Laravel\Resources\Support\Contracts\Resource;

class Filepond extends Resource
{

    public function operations()
    {
        return [
            Upload::create(),
            Delete::create()
        ];
    }

}
