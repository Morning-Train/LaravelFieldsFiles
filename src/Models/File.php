<?php

namespace MorningTrain\Laravel\Fields\Files\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\File as FileHTTP;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use MorningTrain\Laravel\Fields\Files\Filepond;

class File extends Model
{
    protected $appends = ['serverId'];
    protected $visible = ['serverId'];

    protected static function boot()
    {
        parent::boot();

        static::deleted(function (File $file) {
            $file->storage->delete($file->path);
        });
    }

    public function loadFromServerId($serverId)
    {

        $fileinfo = Filepond::getInfoFromServerId($serverId);

        if (!isset($fileinfo->path)) {
            return;
        }

        $path = $fileinfo->path;

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
