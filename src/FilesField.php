<?php

namespace MorningTrain\Laravel\Fields\Files;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Http\File as FileHTTP;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use MorningTrain\Laravel\Fields\Fields\Field;
use MorningTrain\Laravel\Fields\Files\Models\File;

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

            if (!is_array($fileServerIds)) {
                $fileServerIds = [$fileServerIds];
            }

            if ($this->isSingleRelation($model)) {
                $this->updateSingle($model, $fileServerIds[0]);
            }
            else {
                $this->updateMany($model, collect($fileServerIds));
            }


        };
    }

    protected function updateSingle(Model $model, string $fileServerId = null)
    {
        if ($fileServerId === null) {
            return optional(
                $model->{$this->relation}()->first()
            )->delete();
        }

        if (Filepond::exists($fileServerId)) {
            /** @var File $item */
            $item = $model->{$this->relation}()->first() ?? new File();

            if ($item->isSameAs($fileServerId)) return;

            $item->loadFromServerId($fileServerId);
            $item->save();

            $this->attachToRelation($model, $item);
        }

    }

    protected function updateMany(Model $model, Collection $fileServerIds)
    {
        $ids = $fileServerIds
            ->filter(function ($serverId) {
                return Filepond::exists($serverId);
            })
            ->map(function ($serverId) {
                $item = new File();
                $item->loadFromServerId($serverId);
                $item->save();

                return $item;
            })
            ->pluck('id');

        $model->{$this->relation}()->sync($ids);
    }

    //////////////////////////
    /// Overrides
    //////////////////////////

    protected function checkRequest(Request $request)
    {
        return true;
    }

    protected function getValidator()
    {
        $name = $this->validatorName ?? $this->getRequestName();

        return [
            $name => function (string $attribute, $value, Closure $fails) {
                $files    = collect((array)$value);
                $valid    = $files->every(function ($serverId) {
                    return Filepond::exists($serverId);
                });

                if (!$valid) {
                    $key  = "validation.attributes.{$attribute}";
                    $name = __("validation.attributes.{$attribute}");
                    $name = $name === $key ? $attribute : $name;

                    return $fails(__(
                        'validation.file',
                        ['attribute' => $name]
                    ));
                }

                $files = $files
                    ->filter(function ($serverId) {
                        return !Filepond::existsPermanently($serverId);
                    })
                    ->map(function ($serverId) {
                        return new FileHTTP(
                            Filepond::getPathFromServerId($serverId)
                        );
                    });

                $validator = Validator::make([$attribute => $files->toArray()], [
                    "{$attribute}.*" => parent::getValidator() ?? 'file',
                ]);

                if ($validator->fails()) {
                    return $fails(
                        Arr::collapse($validator->errors()->messages())
                    );
                }
            },
        ];
    }
}

