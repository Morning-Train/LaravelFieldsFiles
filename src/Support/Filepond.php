<?php

namespace MorningTrain\Laravel\Fields\Files\Support;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use MorningTrain\Laravel\Fields\Files\Support\Exceptions\InvalidPathException;

class Filepond
{

    public function encode($string) {
        return Crypt::encryptString($string);
    }

    public function decode($string) {
        return Crypt::decryptString($string);
    }

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

    public function getServerIdFromInfo($info)
    {
        if(!is_string($info)) {
            $info = json_encode($info);
        }

        return $this->encode($info);
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

        $info = $this->getInfoFromServerId($serverId);

        if(is_string($info)) {
            return $info;
        }

        if(isset($info->path)) {
            return $info->path;
        }
    }

    public function getInfoFromServerId($serverId)
    {
        if (!trim($serverId)) {
            throw new InvalidPathException();
        }

        return json_decode($this->decode($serverId));
    }

    public function getBasePath()
    {
        return config('filepond.temporary_files_path', sys_get_temp_dir());
    }

    public function exists($serverId)
    {
        try {
            $path = $this->getPathFromServerId($serverId);
            return file_exists($path) && !is_dir($path);
        } catch (\Exception $exception) {
            return false;
        }
    }

}