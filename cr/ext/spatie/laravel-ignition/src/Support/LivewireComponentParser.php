<?php

namespace Spatie\LaravelIgnition\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\LivewireManager;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class LivewireComponentParser
{
    protected /*string */$componentClass;

    protected /*ReflectionClass */$reflectionClass;

    public static function create(/*string */$componentAlias)/*: self*/
    {
        $componentAlias = backport_type_check('string', $componentAlias);

        return new self($componentAlias);
    }

    protected /*string */$componentAlias;

    public function __construct(/*protected *//*string */$componentAlias)
    {
        $this->componentAlias = backport_type_check('string', $componentAlias);

        $this->componentClass = app(LivewireManager::class)->getClass($this->componentAlias);
        $this->reflectionClass = new ReflectionClass($this->componentClass);
    }

    public function getComponentClass()/*: string*/
    {
        return $this->componentClass;
    }

    public function getPropertyNamesLike(/*string */$similar)/*: Collection*/
    {
        $similar = backport_type_check('string', $similar);

        $properties = collect($this->reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC))
            ->reject(function (ReflectionProperty $reflectionProperty) {
                return $reflectionProperty->class !== $this->reflectionClass->name;
            })
            ->map(function (ReflectionProperty $reflectionProperty) {
                return $reflectionProperty->name;
            });

        $computedProperties = collect($this->reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC))
            ->reject(function (ReflectionMethod $reflectionMethod) {
                return $reflectionMethod->class !== $this->reflectionClass->name;
            })
            ->filter(function (ReflectionMethod $reflectionMethod) {
                return str_starts_with($reflectionMethod->name, 'get') && str_ends_with($reflectionMethod->name, 'Property');
            })
            ->map(function (ReflectionMethod $reflectionMethod) {
                return lcfirst(Str::of($reflectionMethod->name)->after('get')->before('Property'));
            });

        return $this->filterItemsBySimilarity(
            $properties->merge($computedProperties),
            $similar
        );
    }

    public function getMethodNamesLike(/*string */$similar)/*: Collection*/
    {
        $similar = backport_type_check('string', $similar);

        $methods = collect($this->reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC))
            ->reject(function (ReflectionMethod $reflectionMethod) {
                return $reflectionMethod->class !== $this->reflectionClass->name;
            })
            ->map(function (ReflectionMethod $reflectionMethod) {
                return $reflectionMethod->name;
            });

        return $this->filterItemsBySimilarity($methods, $similar);
    }

    protected function filterItemsBySimilarity(Collection $items, /*string */$similar)/*: Collection*/
    {
        $similar = backport_type_check('string', $similar);

        return $items
            ->map(function (/*string */$name) use ($similar) {
                $name = backport_type_check('string', $name);

                similar_text($similar, $name, $percentage);

                return ['match' => $percentage, 'value' => $name];
            })
            ->sortByDesc('match')
            ->filter(function (array $item) {
                return $item['match'] > 40;
            })
            ->map(function (array $item) {
                return $item['value'];
            })
            ->values();
    }
}
