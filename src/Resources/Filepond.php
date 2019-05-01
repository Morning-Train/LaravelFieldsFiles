<?php

namespace MorningTrain\Laravel\Fields\Files\Resources;

use MorningTrain\Laravel\Fields\Files\Operations\Filepond\Revert;
use MorningTrain\Laravel\Fields\Files\Operations\Filepond\Process;
use MorningTrain\Laravel\Resources\Support\Contracts\Resource;

class Filepond extends Resource
{

    public function operations()
    {
        return [
            Process::create(),
            Revert::create(),
            //Restore
            //Load
            //Fetch
        ];
    }

}
