<?php

namespace Illuminate\Database\Eloquent\Factories;

use Closure;
use Faker\Generator;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;

abstract class Factory
{
    use ForwardsCalls;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model;

    /**
     * The number of models that should be generated.
     *
     * @var int|null
     */
    protected $count;

    /**
     * The state transformations that will be applied to the model.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $states;

    /**
     * The parent relationships that will be applied to the model.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $has;

    /**
     * The child relationships that will be applied to the model.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $for;

    /**
     * The "after making" callbacks that will be applied to the model.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $afterMaking;

    /**
     * The "after creating" callbacks that will be applied to the model.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $afterCreating;

    /**
     * The name of the database connection that will be used to create the models.
     *
     * @var string
     */
    protected $connection;

    /**
     * The current Faker instance.
     *
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * The default namespace where factories reside.
     *
     * @var string
     */
    protected static $namespace = 'Database\\Factories\\';

    /**
     * The default model name resolver.
     *
     * @var callable
     */
    protected static $modelNameResolver;

    /**
     * The factory name resolver.
     *
     * @var callable
     */
    protected static $factoryNameResolver;

    /**
     * Create a new factory instance.
     *
     * @param  int|null  $count
     * @param  \Illuminate\Support\Collection  $states
     * @param  \Illuminate\Support\Collection  $has
     * @param  \Illuminate\Support\Collection  $for
     * @param  \Illuminate\Support\Collection  $afterMaking
     * @param  \Illuminate\Support\Collection  $afterCreating
     * @param  string  $connection
     * @return void
     */
    public function __construct($count = null,
                                Collection $states = null,
                                Collection $has = null,
                                Collection $for = null,
                                Collection $afterMaking = null,
                                Collection $afterCreating = null,
                                $connection = null)
    {
        $this->count = $count;
        $this->states = $states ?: new Collection;
        $this->has = $has ?: new Collection;
        $this->for = $for ?: new Collection;
        $this->afterMaking = $afterMaking ?: new Collection;
        $this->afterCreating = $afterCreating ?: new Collection;
        $this->connection = $connection;
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    abstract public function definition();

    /**
     * Get a new factory instance for the given attributes.
     *
     * @param  callable|array  $attributes
     * @return static
     */
    public static function newUp($attributes = [])
    {
        return (new static)->state($attributes);
    }

    /**
     * Get a new factory instance for the given number of models.
     *
     * @param  int  $count
     * @return static
     */
    public static function times($count)
    {
        $count = cast_to_int($count);

        return new static($count);
    }

    /**
     * Create a single model and persist it to the database.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createOne($attributes = [])
    {
        return $this->count(null)->create($attributes);
    }

    /**
     * Create a collection of models and persist them to the database.
     *
     * @param  array  $attributes
     * @param  \Illuminate\Database\Eloquent\Model|null  $parent
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     */
    public function create($attributes = [], Model $parent = null)
    {
        if (! empty($attributes)) {
            return $this->state($attributes)->create([], $parent);
        }

        $results = $this->make($attributes, $parent);

        if ($results instanceof Model) {
            $this->store(collect([$results]));

            $this->callAfterCreating(collect([$results]), $parent);
        } else {
            $this->store($results);

            $this->callAfterCreating($results, $parent);
        }

        return $results;
    }

    /**
     * Set the connection name on the results and store them.
     *
     * @param  \Illuminate\Support\Collection  $results
     * @return void
     */
    protected function store(Collection $results)
    {
        $results->each(function ($model) {
            if (! isset($this->connection)) {
                $model->setConnection($model->newQueryWithoutScopes()->getConnection()->getName());
            }

            $model->save();

            $this->createChildren($model);
        });
    }

    /**
     * Create the children for the given model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    protected function createChildren(Model $model)
    {
        Model::unguarded(function () use ($model) {
            $this->has->each(function ($has) use ($model) {
                $has->createFor($model);
            });
        });
    }

    /**
     * Make a single instance of the model.
     *
     * @param  callable|array  $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function makeOne($attributes = [])
    {
        return $this->count(null)->make($attributes);
    }

    /**
     * Create a collection of models.
     *
     * @param  array  $attributes
     * @param  \Illuminate\Database\Eloquent\Model|null  $parent
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     */
    public function make($attributes = [], Model $parent = null)
    {
        if (! empty($attributes)) {
            return $this->state($attributes)->make([], $parent);
        }

        if ($this->count === null) {
            return tap($this->makeInstance($parent), function ($instance) {
                $this->callAfterMaking(collect([$instance]));
            });
        }

        if ($this->count < 1) {
            return $this->newModel()->newCollection();
        }

        $instances = $this->newModel()->newCollection(array_map(function () use ($parent) {
            return $this->makeInstance($parent);
        }, range(1, $this->count)));

        $this->callAfterMaking($instances);

        return $instances;
    }

    /**
     * Make an instance of the model with the given attributes.
     *
     * @param  \Illuminate\Database\Eloquent\Model|null  $parent
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function makeInstance(Model $parent = null)
    {
        return Model::unguarded(function () use ($parent) {
            return tap($this->newModel($this->getExpandedAttributes($parent)), function ($instance) {
                if (isset($this->connection)) {
                    $instance->setConnection($this->connection);
                }
            });
        });
    }

    /**
     * Get a raw attributes array for the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model|null  $parent
     * @return mixed
     */
    protected function getExpandedAttributes(Model $parent = null)
    {
        return $this->expandAttributes($this->getRawAttributes($parent));
    }

    /**
     * Get the raw attributes for the model as an array.
     *
     * @param  \Illuminate\Database\Eloquent\Model|null  $parent
     * @return array
     */
    protected function getRawAttributes(Model $parent = null)
    {
        $this->faker = $this->withFaker();

        return $this->states->pipe(function ($states) {
            return $this->for->isEmpty() ? $states : new Collection(array_merge([function () {
                return $this->parentResolvers();
            }], $states->all()));
        })->reduce(function ($carry, $state) use ($parent) {
            return array_merge($carry, $state($carry, $parent));
        }, $this->definition());
    }

    /**
     * Create the parent relationship resolvers (as deferred Closures).
     *
     * @return array
     */
    protected function parentResolvers()
    {
        $model = $this->newModel();

        return $this->for->map(function (BelongsToRelationship $for) use ($model) {
            return $for->attributesFor($model);
        })->collapse()->all();
    }

    /**
     * Expand all attributes to their underlying values.
     *
     * @param  array  $definition
     * @return array
     */
    protected function expandAttributes(array $definition)
    {
        return collect($definition)->map(function ($attribute) use ($definition) {
            if (is_callable($attribute) && ! is_string($attribute) && ! is_array($attribute)) {
                $attribute = $attribute($definition);
            }

            if ($attribute instanceof self) {
                return $attribute->create()->getKey();
            } elseif ($attribute instanceof Model) {
                return $attribute->getKey();
            } else {
                return $attribute;
            }
        })->all();
    }

    /**
     * Add a new state transformation to the model definition.
     *
     * @param  callable|array  $state
     * @return static
     */
    public function state($state)
    {
        return $this->newInstance([
            'states' => $this->states->concat([
                is_callable($state) ? $state : function () use ($state) {
                    return $state;
                },
            ]),
        ]);
    }

    /**
     * Add a new sequenced state transformation to the model definition.
     *
     * @param  array  $sequence
     * @return static
     */
    public function sequence(...$sequence)
    {
        return $this->state(new Sequence(...$sequence));
    }

    /**
     * Define a child relationship for the model.
     *
     * @param  \Illuminate\Database\Eloquent\Factories\Factory  $factory
     * @param  string|null  $relationship
     * @return static
     */
    public function has(self $factory, $relationship = null)
    {
        return $this->newInstance([
            'has' => $this->has->concat([new Relationship(
                $factory, $relationship ?: Str::camel(Str::plural(class_basename($factory->modelName())))
            )]),
        ]);
    }

    /**
     * Define an attached relationship for the model.
     *
     * @param  \Illuminate\Database\Eloquent\Factories\Factory  $factory
     * @param  callable|array  $pivot
     * @param  string|null  $relationship
     * @return static
     */
    public function hasAttached(self $factory, $pivot = [], $relationship = null)
    {
        return $this->newInstance([
            'has' => $this->has->concat([new BelongsToManyRelationship(
                $factory,
                $pivot,
                $relationship ?: Str::camel(Str::plural(class_basename($factory->modelName())))
            )]),
        ]);
    }

    /**
     * Define a parent relationship for the model.
     *
     * @param  \Illuminate\Database\Eloquent\Factories\Factory  $factory
     * @param  string|null  $relationship
     * @return static
     */
    public function forParent(self $factory, $relationship = null)
    {
        return $this->newInstance(['for' => $this->for->concat([new BelongsToRelationship(
            $factory,
            $relationship ?: Str::camel(class_basename($factory->modelName()))
        )])]);
    }

    /**
     * Add a new "after making" callback to the model definition.
     *
     * @param  \Closure  $callback
     * @return static
     */
    public function afterMaking(Closure $callback)
    {
        return $this->newInstance(['afterMaking' => $this->afterMaking->concat([$callback])]);
    }

    /**
     * Add a new "after creating" callback to the model definition.
     *
     * @param  \Closure  $callback
     * @return static
     */
    public function afterCreating(Closure $callback)
    {
        return $this->newInstance(['afterCreating' => $this->afterCreating->concat([$callback])]);
    }

    /**
     * Call the "after making" callbacks for the given model instances.
     *
     * @param  \Illuminate\Support\Collection  $instances
     * @return void
     */
    protected function callAfterMaking(Collection $instances)
    {
        $instances->each(function ($model) {
            $this->afterMaking->each(function ($callback) use ($model) {
                $callback($model);
            });
        });
    }

    /**
     * Call the "after creating" callbacks for the given model instances.
     *
     * @param  \Illuminate\Support\Collection  $instances
     * @param  \Illuminate\Database\Eloquent\Model|null  $parent
     * @return void
     */
    protected function callAfterCreating(Collection $instances, Model $parent = null)
    {
        $instances->each(function ($model) use ($parent) {
            $this->afterCreating->each(function ($callback) use ($model, $parent) {
                $callback($model, $parent);
            });
        });
    }

    /**
     * Specify how many models should be generated.
     *
     * @param  int|null  $count
     * @return static
     */
    public function count($count = null)
    {
        $count = cast_to_int($count, null);

        return $this->newInstance(['count' => $count]);
    }

    /**
     * Specify the database connection that should be used to generate models.
     *
     * @param  string  $connection
     * @return static
     */
    public function connection($connection)
    {
        $connection = cast_to_string($connection);

        return $this->newInstance(['connection' => $connection]);
    }

    /**
     * Create a new instance of the factory builder with the given mutated properties.
     *
     * @param  array  $arguments
     * @return static
     */
    protected function newInstance(array $arguments = [])
    {
        return new static(...array_values(array_merge([
            'count' => $this->count,
            'states' => $this->states,
            'has' => $this->has,
            'for' => $this->for,
            'afterMaking' => $this->afterMaking,
            'afterCreating' => $this->afterCreating,
            'connection' => $this->connection,
        ], $arguments)));
    }

    /**
     * Get a new model instance.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function newModel(array $attributes = [])
    {
        $model = $this->modelName();

        return new $model($attributes);
    }

    /**
     * Get the name of the model that is generated by the factory.
     *
     * @return string
     */
    public function modelName()
    {
        $resolver = static::$modelNameResolver ?: function (self $factory) {
            $factoryBasename = Str::replaceLast('Factory', '', class_basename($factory));

            return class_exists('App\\Models\\'.$factoryBasename)
                        ? 'App\\Models\\'.$factoryBasename
                        : 'App\\'.$factoryBasename;
        };

        return $this->model ?: $resolver($this);
    }

    /**
     * Specify the callback that should be invoked to guess model names based on factory names.
     *
     * @param  callable  $callback
     * @return void
     */
    public static function guessModelNamesUsing(callable $callback)
    {
        static::$modelNameResolver = $callback;
    }

    /**
     * Specify the default namespace that contains the application's model factories.
     *
     * @param  string  $namespace
     * @return void
     */
    public static function useNamespace($namespace)
    {
        $namespace = cast_to_string($namespace);

        static::$namespace = $namespace;
    }

    /**
     * Get a new factory instance for the given model name.
     *
     * @param  string  $modelName
     * @return static
     */
    public static function factoryForModel($modelName)
    {
        $modelName = cast_to_string($modelName);

        $resolver = static::$factoryNameResolver ?: function ($modelName) {
            return static::$namespace.Str::singular(class_basename($modelName)).'Factory';
        };

        $factory = $resolver($modelName);

        return $factory::newUp();
    }

    /**
     * Specify the callback that should be invoked to guess factory names based on dynamic relationship names.
     *
     * @param  callable  $callback
     * @return void
     */
    public static function guessFactoryNamesUsing(callable $callback)
    {
        static::$factoryNameResolver = $callback;
    }

    /**
     * Get a new Faker instance.
     *
     * @return \Faker\Generator
     */
    protected function withFaker()
    {
        return Container::getInstance()->make(Generator::class);
    }

    /**
     * Proxy dynamic factory methods onto their proper methods.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (! Str::startsWith($method, ['for', 'has'])) {
            static::throwBadMethodCallException($method);
        }

        $relationship = Str::camel(Str::substr($method, 3));

        $factory = static::factoryForModel(
            get_class($this->newModel()->{$relationship}()->getRelated())
        );

        if (Str::startsWith($method, 'for')) {
            return $this->for($factory->state(isset($parameters[0]) ? $parameters[0] : []), $relationship);
        } elseif (Str::startsWith($method, 'has')) {
            return $this->has(
                $factory
                    ->count(is_numeric(isset($parameters[0]) ? $parameters[0] : null) ? $parameters[0] : 1)
                    ->state(
                        (is_callable(isset($parameters[0]) ? $parameters[0] : null)
                            || is_array(isset($parameters[0]) ? $parameters[0] : null))
                        ? $parameters[0]
                        : (isset($parameters[1]) ? $parameters[1] : [])),
                $relationship
            );
        }
    }
}
