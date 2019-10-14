<?php

namespace MorningTrain\Laravel\Fields\Files;


use MorningTrain\Laravel\Fields\Files\Support\Filepond as FilepondService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed getInfoFromServerId($serverId)
 * @method static string getPathFromServerId($serverId)
 * @method static string getBasePath()
 * @method static string getServerIdFromInfo($info)
 * @method static boolean exists($serverId)
 *
 * @see \MorningTrain\Laravel\Fields\Files\Support\Filepond
 */
class Filepond extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return FilepondService::class;
    }
}
