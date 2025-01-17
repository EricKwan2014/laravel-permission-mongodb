<?php

namespace Maklad\Permission;

use Illuminate\Support\Collection;

/**
 * Class Guard
 * @package Maklad\Permission
 */
class Guard
{
    /**
     * return collection of (guardName) property if exist on class or object
     * otherwise will return collection of guards names that exists in config/auth.php.
     *
     * @param $model
     *
     * @return Collection
     * @throws \ReflectionException
     */
    public function getNames($model) : Collection
    {
        $guardName = null;
        $class = null;

        if (\is_object($model)) {
            $guardName = $model->guardName ?? null;
        }

        if ($guardName === null) {
            $class = \is_object($model) ? \get_class($model) : $model;
            $guardName = (new \ReflectionClass($class))->getDefaultProperties()['guardName'] ?? null;
        }

        if ($guardName) {
            return collect($guardName);
        }

        return collect(config('auth.guards'))
            ->map(function ($guard) {
                if (! isset($guard['provider'])) {
                    return;
                }
                return config("auth.providers.{$guard['provider']}.model");
            })
            ->filter(function ($model) use ($class) {
                return $class === $model;
            })
            ->keys();
    }

    /**
     * Return Default Guard name
     *
     * @param $class
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getDefaultName($class): string
    {
        $default = config('auth.defaults.guard');
        return $this->getNames($class)->first() ?: $default;
    }
}
