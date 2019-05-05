<?php

namespace MorningTrain\Laravel\Fields\Files;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Str;
use MorningTrain\Laravel\Fields\Fields\Field;
use MorningTrain\Laravel\Fields\Files\Models\File;
use MorningTrain\Laravel\Fields\Files\Support\Filepond;

class FilesField extends Field
{

    protected $relation;

    public function __construct(string $name = null)
    {
        parent::__construct($name);

        $this->relation = Str::camel($name);

        $this->updatesAt(Field::BEFORE_SAVE);

    }

    public function getRelation($model)
    {
        return $model->{$this->relation}();
    }

    protected function isSingleRelation(Model $model)
    {
        $relation = $this->getRelation($model);

        return $relation instanceof BelongsTo || $relation instanceof HasOne || $relation instanceof HasOneThrough;
    }

    public function clearRelated($model)
    {
        $this->getRelation($model)->delete();
    }

    public function attachToRelation(Model $model, Model $item)
    {
        $relation = $this->getRelation($model);

        if ($relation instanceof BelongsTo) {
            $relation->associate($item);
        }

        // TODO: Test all relations

    }

    public function getUpdateMethod()
    {
        return $this->update ?: function (Model $model, string $property, $fileServerIds = []) {

            $filepond = new Filepond();

            if (!is_array($fileServerIds)) {
                $fileServerIds = [$fileServerIds];
            }

            if (empty($fileServerIds)) {
                $this->clearRelated($model);
            }

            foreach ($fileServerIds as $fileServerId) {

                $fileServerId = $fileServerIds[0];

                if ($filepond->exists($fileServerId)) {
                    if ($this->isSingleRelation($model)) {

                        $item = $model->{$this->relation}()->first();
                        if ($item === null) {
                            $item = new File();
                        }

                        if ($item instanceof File) {
                            $item->loadFromServerId($fileServerId);
                        }

                        if ($item->isDirty()) {
                            $item->save();
                        }

                        $this->attachToRelation($model, $item);

                    }

                }

            }

        };
    }

}