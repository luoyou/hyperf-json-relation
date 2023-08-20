<?php

declare(strict_types=1);

namespace Luo\HyperfJsonRelation;

use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\Relations\HasManyThrough;
use Hyperf\Database\Model\Relations\HasOne;
use Hyperf\Database\Model\Relations\HasOneThrough;
use Hyperf\Database\Model\Relations\MorphMany;
use Hyperf\Database\Model\Relations\MorphOne;
use Luo\HyperfJsonRelation\Relations\BelongsToJson;
use Luo\HyperfJsonRelation\Relations\HasManyJson;
use Staudenmeir\EloquentJsonRelations\Relations\Postgres\BelongsTo as BelongsToPostgres;
use Staudenmeir\EloquentJsonRelations\Relations\Postgres\HasMany as HasManyPostgres;
use Staudenmeir\EloquentJsonRelations\Relations\Postgres\HasManyThrough as HasManyThroughPostgres;
use Staudenmeir\EloquentJsonRelations\Relations\Postgres\HasOne as HasOnePostgres;
use Staudenmeir\EloquentJsonRelations\Relations\Postgres\HasOneThrough as HasOneThroughPostgres;
use Staudenmeir\EloquentJsonRelations\Relations\Postgres\MorphMany as MorphManyPostgres;
use Staudenmeir\EloquentJsonRelations\Relations\Postgres\MorphOne as MorphOnePostgres;

trait HasJsonRelationships {

    use \Staudenmeir\EloquentJsonRelations\HasJsonRelationships;

    /**
     * Get an attribute from the $attributes array.
     *
     * @param string $key
     * @return mixed
     */
    public function getAttributeFromArray(string $key): mixed {
        if (str_contains($key, '->')) {
            return $this->getAttributeValue($key);
        }

        return parent::getAttributeFromArray($key);
    }

    public function newHasOne(Builder $query, Model $parent, $foreignKey, $localKey) {
        if ($query->getConnection()->getDriverName() === 'pgsql') {
            return new HasOnePostgres($query, $parent, $foreignKey, $localKey);
        }

        return new HasOne($query, $parent, $foreignKey, $localKey);
    }

    public function newHasOneThrough(Builder $query, Model $farParent, Model $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey) {
        if ($query->getConnection()->getDriverName() === 'pgsql') {
            return new HasOneThroughPostgres($query, $farParent, $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey);
        }

        return new HasOneThrough($query, $farParent, $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey);
    }

    public function newMorphOne(Builder $query, Model $parent, $type, $id, $localKey) {
        if ($query->getConnection()->getDriverName() === 'pgsql') {
            return new MorphOnePostgres($query, $parent, $type, $id, $localKey);
        }

        return new MorphOne($query, $parent, $type, $id, $localKey);
    }

    public function newBelongsTo(Builder $query, Model $child, $foreignKey, $ownerKey, $relation) {
        if ($query->getConnection()->getDriverName() === 'pgsql') {
            return new BelongsToPostgres($query, $child, $foreignKey, $ownerKey, $relation);
        }

        return new BelongsTo($query, $child, $foreignKey, $ownerKey, $relation);
    }

    public function newHasMany(Builder $query, Model $parent, $foreignKey, $localKey) {
        if ($query->getConnection()->getDriverName() === 'pgsql') {
            return new HasManyPostgres($query, $parent, $foreignKey, $localKey);
        }

        return new HasMany($query, $parent, $foreignKey, $localKey);
    }

    public function newHasManyThrough(Builder $query, Model $farParent, Model $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey) {
        if ($query->getConnection()->getDriverName() === 'pgsql') {
            return new HasManyThroughPostgres($query, $farParent, $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey);
        }

        return new HasManyThrough($query, $farParent, $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey);
    }

    public function newMorphMany(Builder $query, Model $parent, $type, $id, $localKey) {
        if ($query->getConnection()->getDriverName() === 'pgsql') {
            return new MorphManyPostgres($query, $parent, $type, $id, $localKey);
        }

        return new MorphMany($query, $parent, $type, $id, $localKey);
    }

    protected function newHasManyJson(Builder $query, Model $parent, $foreignKey, $localKey) {
        return new HasManyJson($query, $parent, $foreignKey, $localKey);
    }

    protected function newBelongsToJson(Builder $query, Model $child, $foreignKey, $ownerKey, $relation) {
        return new BelongsToJson($query, $child, $foreignKey, $ownerKey, $relation);
    }
}
