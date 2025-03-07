<?php

declare(strict_types=1);

namespace Longman\LaravelLodash\Eloquent;

use Ramsey\Uuid\Exception\InvalidArgumentException;
use Ramsey\Uuid\Uuid;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait UsesUuidAsPrimary
{
    public function isUuidBinary(string $value): bool
    {
        try {
            Uuid::fromBytes($value);
        } catch (InvalidArgumentException) {
            return false;
        }

        return true;
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }

    protected static function bootUsesUuidAsPrimary(): void
    {
        static::creating(static function (UuidAsPrimaryContract $model): void {
            $keyName = $model->getKeyName();

            if (! empty($model->{$keyName})) {
                // Do some validation
                $model->isUuidBinary($model->{$keyName})
                    ? Uuid::fromBytes($model->{$keyName})
                    : Uuid::fromString($model->{$keyName});
            } elseif (! empty($model->attributes[$keyName])) {
                // Do some validation
                $model->isUuidBinary($model->attributes[$keyName])
                    ? Uuid::fromBytes($model->attributes[$keyName])
                    : Uuid::fromString($model->attributes[$keyName]);
            } else {
                $model->{$keyName} = Uuid::uuid1()->getBytes();
            }
        });
    }
}
