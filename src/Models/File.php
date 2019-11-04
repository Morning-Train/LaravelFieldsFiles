<?php

namespace MorningTrain\Laravel\Fields\Files\Models;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\File as FileHTTP;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use MorningTrain\Laravel\Fields\Files\Filepond;

class File extends Model
{
    protected $appends = ['serverId', 'url', 'filename'];
    protected $visible = ['serverId', 'url', 'name', 'size', 'mime', 'extension', 'description', 'filename'];

    protected static function boot()
    {
        parent::boot();

        static::deleted(function (File $file) {
            $file->deleteFile();
        });
    }

    //////////////////////////
    /// Methods
    //////////////////////////

    protected function deleteFile()
    {
        return $this->storage->delete($this->path);
    }

    public static function createFromStorage(string $disk, string $path)
    {
        $storage = Storage::disk($disk);

        if (!$storage->exists($path)) {
            return false;
        }

        $model = new static();

        $parts     = explode('.', Arr::last(explode('/', $path)));
        $extension = array_pop($parts);

        $model->name      = join('.', $parts);
        $model->extension = $extension;
        $model->size      = $storage->size($path);
        $model->mime      = $storage->mimeType($path);
        $model->uuid      = (string)Str::uuid();
        $model->disk      = config('filepond.disk', 'local');
        $model->location  = config('filepond.location', 'filepond');

		$model->storage->put($model->path, $storage->get($path));
        $model->save();

		return $model;
    }

    public function loadFromServerId($serverId, Closure $manipulator = null)
    {

        $fileinfo = Filepond::getInfoFromServerId($serverId);

        if (!isset($fileinfo->path)) {
            return;
        }

        if ($this->fileExists) {
            $this->deleteFile();
        }

        $path = $fileinfo->path;

        // Invoke manipulator
        with($path, $manipulator);

        $file = new FileHTTP($path);

        $this->size     = $file->getSize();
        $this->mime     = $file->getMimeType();
        $this->uuid     = (string)Str::uuid();
        $this->disk     = config('filepond.disk', 'local');
        $this->location = config('filepond.location', 'filepond');

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

    public function isSameAs($serverId)
    {
        $info = Filepond::getInfoFromServerId($serverId);

        if (isset($info->uuid)) {
            return $info->uuid === $this->uuid;
        }

        return false;
    }

    //////////////////////////
    /// Scopes
    //////////////////////////

    public function scopeUuid(Builder $q, string $uuid)
    {
        return $q->where('uuid', $uuid);
    }

    //////////////////////////
    /// Accessors
    //////////////////////////

    public function getPathAttribute()
    {
        return $this->location . '/' . $this->internal_filename;
    }

    public function getUrlAttribute()
    {
        return $this->storage->url($this->path);
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

    public function getFileExistsAttribute()
    {
        return $this->storage->exists($this->path);
    }

    public function getServerIdAttribute()
    {
        return Filepond::getServerIdFromInfo((object)[
            'name'      => $this->name,
            'extension' => $this->extension,
            'path'      => $this->path,
            'disk'      => $this->disk,
            'uuid'      => $this->uuid,
        ]);
    }

}

