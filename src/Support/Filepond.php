<?php

namespace MorningTrain\Laravel\Fields\Files\Support;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use MorningTrain\Laravel\Fields\Files\Support\Exceptions\InvalidPathException;

class Filepond
{

    /**
     * Converts the given path into a filepond server id
     *
     * @param string $path
     * @return string
     */
    public function getServerIdFromPath($path)
    {
        return Crypt::encryptString($path);
    }

    /**
     * Converts the given filepond server id into a path
     *
     * @param string $serverId
     * @return string
     */
    public function getPathFromServerId($serverId)
    {
        if (!trim($serverId)) {
            throw new InvalidPathException();
        }

        $filePath = Crypt::decryptString($serverId);

        return $filePath;
    }

    public function getBasePath()
    {
        return config('filepond.temporary_files_path', sys_get_temp_dir());
    }

}