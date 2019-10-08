<?php

namespace MorningTrain\Laravel\Fields\Files\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use MorningTrain\Laravel\Fields\Files\Support\Filepond;
use Illuminate\Http\File as FileHTTP;
use Ramsey\Uuid\Uuid;

class File extends Model
{

    public function loadFromServerId($serverId)
    {

        $filepond = new Filepond();

        $fileinfo = $filepond->getInfoFromServerId($serverId);

        if (!isset($fileinfo->path)) {
            return;
        }

        $path = $fileinfo->path;

        $file = new FileHTTP($path);

        $this->size = $file->getSize();
        $this->mime = $file->getMimeType();
        $this->uuid = Uuid::uuid4()->toString();
        $this->disk = 'local'; //TODO: Make disk dynamic
        $this->location = 'filepond'; // TODO: Make location dynamic

        if (isset($fileinfo->name)) {
            $this->name = $fileinfo->name;
        }

        if (isset($fileinfo->extension)) {
            $this->extension = $fileinfo->extension;
        }

        $this->storage->putFileAs($this->location, $file, $this->internal_filename);

        if (file_exists($path)) {
            unlink($path);
        }

    }

    public function getPathAttribute()
    {
        return $this->location . '/' . $this->internal_filename;
    }

    public function getInternalFilenameAttribute()
    {
        return $this->uuid . '.' . $this->extension;
    }

    public function getFilenameAttribute()
    {
        return $this->name . '.' . $this->extension;
    }

    public function getStorageAttribute()
    {
        return Storage::disk($this->disk);
    }

    public function getContentAttribute()
    {
        return $this->storage->get($this->path);
    }

}
