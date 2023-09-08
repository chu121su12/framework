<?php

namespace Illuminate\Tests\Integration\Queue;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Queue\Attributes\WithoutRelations;
use Illuminate\Queue\SerializesModels;
use LogicException;
use Orchestra\Testbench\TestCase;
use Schema;

class ModelSerializationTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.connections.custom', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUp()/*: void*/
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
        });

        Schema::connection('custom')->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
        });

        Schema::create('lines', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('order_id');
            $table->unsignedInteger('product_id');
        });

        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('role_id');
        });
    }

    public function testItSerializeUserOnDefaultConnection()
    {
        $user = ModelSerializationTestUser::create([
            'email' => 'mohamed@laravel.com',
        ]);

        ModelSerializationTestUser::create([
            'email' => 'taylor@laravel.com',
        ]);

        $serialized = backport_serialize(new ModelSerializationTestClass($user));

        $unSerialized = backport_unserialize($serialized);

        $this->assertSame('testing', $unSerialized->user->getConnectionName());
        $this->assertSame('mohamed@laravel.com', $unSerialized->user->email);

        $serialized = backport_serialize(new CollectionSerializationTestClass(ModelSerializationTestUser::on('testing')->get()));

        $unSerialized = backport_unserialize($serialized);

        $this->assertSame('testing', $unSerialized->users[0]->getConnectionName());
        $this->assertSame('mohamed@laravel.com', $unSerialized->users[0]->email);
        $this->assertSame('testing', $unSerialized->users[1]->getConnectionName());
        $this->assertSame('taylor@laravel.com', $unSerialized->users[1]->email);
    }

    public function testItSerializeUserOnDifferentConnection()
    {
        $user = ModelSerializationTestUser::on('custom')->create([
            'email' => 'mohamed@laravel.com',
        ]);

        ModelSerializationTestUser::on('custom')->create([
            'email' => 'taylor@laravel.com',
        ]);

        $serialized = backport_serialize(new ModelSerializationTestClass($user));

        $unSerialized = backport_unserialize($serialized);

        $this->assertSame('custom', $unSerialized->user->getConnectionName());
        $this->assertSame('mohamed@laravel.com', $unSerialized->user->email);

        $serialized = backport_serialize(new CollectionSerializationTestClass(ModelSerializationTestUser::on('custom')->get()));

        $unSerialized = backport_unserialize($serialized);

        $this->assertSame('custom', $unSerialized->users[0]->getConnectionName());
        $this->assertSame('mohamed@laravel.com', $unSerialized->users[0]->email);
        $this->assertSame('custom', $unSerialized->users[1]->getConnectionName());
        $this->assertSame('taylor@laravel.com', $unSerialized->users[1]->email);
    }

    public function testItFailsIfModelsOnMultiConnections()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Queueing collections with multiple model connections is not supported.');

        $user = ModelSerializationTestUser::on('custom')->create([
            'email' => 'mohamed@laravel.com',
        ]);

        $user2 = ModelSerializationTestUser::create([
            'email' => 'taylor@laravel.com',
        ]);

        $serialized = backport_serialize(new CollectionSerializationTestClass(
            new Collection([$user, $user2])
        ));

        // backport_unserialize($serialized);
    }

    public function testItReloadsRelationships()
    {
        $order = tap(Order::create(), function (Order $order) {
            $order->wasRecentlyCreated = false;
        });

        $product1 = Product::create();
        $product2 = Product::create();

        Line::create(['order_id' => $order->id, 'product_id' => $product1->id]);
        Line::create(['order_id' => $order->id, 'product_id' => $product2->id]);

        $order->load('line', 'lines', 'products');

        $serialized = backport_serialize(new ModelRelationSerializationTestClass($order));
        $unSerialized = backport_unserialize($serialized);

        $this->assertEquals($unSerialized->order->getRelations(), $order->getRelations());
    }

    public function testItReloadsNestedRelationships()
    {
        $order = tap(Order::create(), function (Order $order) {
            $order->wasRecentlyCreated = false;
        });

        $product1 = Product::create();
        $product2 = Product::create();

        Line::create(['order_id' => $order->id, 'product_id' => $product1->id]);
        Line::create(['order_id' => $order->id, 'product_id' => $product2->id]);

        $order->load('line.product', 'lines', 'lines.product', 'products');

        $nestedSerialized = backport_serialize(new ModelRelationSerializationTestClass($order));
        $nestedUnSerialized = backport_unserialize($nestedSerialized);

        $this->assertEquals($nestedUnSerialized->order->getRelations(), $order->getRelations());
    }

    public function testItCanRunModelBootsAndTraitInitializations()
    {
        $model = new ModelBootTestWithTraitInitialization();

        $this->assertTrue($model->fooBar);
        $this->assertTrue($model::hasGlobalScope('foo_bar'));

        $model::clearBootedModels();

        $this->assertFalse($model::hasGlobalScope('foo_bar'));

        $unSerializedModel = backport_unserialize(backport_serialize($model));

        $this->assertFalse($unSerializedModel->fooBar);
        $this->assertTrue($model::hasGlobalScope('foo_bar'));
    }

    /**
     * Regression test for https://github.com/laravel/framework/issues/23068.
     */
    public function testItCanUnserializeNestedRelationshipsWithoutPivot()
    {
        $user = tap(User::create([
            'email' => 'taylor@laravel.com',
        ]), function (User $user) {
            $user->wasRecentlyCreated = false;
        });

        $role1 = Role::create();
        $role2 = Role::create();

        RoleUser::create(['user_id' => $user->id, 'role_id' => $role1->id]);
        RoleUser::create(['user_id' => $user->id, 'role_id' => $role2->id]);

        $user->roles->each(function ($role) {
            $role->pivot->load('user', 'role');
        });

        $serialized = backport_serialize(new ModelSerializationTestClass($user));
        backport_unserialize($serialized);
    }

    public function testItSerializesAnEmptyCollection()
    {
        $serialized = backport_serialize(new CollectionSerializationTestClass(
            new Collection([])
        ));

        backport_unserialize($serialized);
    }

    public function testItSerializesACollectionInCorrectOrder()
    {
        ModelSerializationTestUser::create(['email' => 'mohamed@laravel.com']);
        ModelSerializationTestUser::create(['email' => 'taylor@laravel.com']);

        $serialized = backport_serialize(new CollectionSerializationTestClass(
            ModelSerializationTestUser::orderByDesc('email')->get()
        ));

        $unserialized = backport_unserialize($serialized);

        $this->assertSame('taylor@laravel.com', $unserialized->users->first()->email);
        $this->assertSame('mohamed@laravel.com', $unserialized->users->last()->email);
    }

    public function testItCanUnserializeACollectionInCorrectOrderAndHandleDeletedModels()
    {
        ModelSerializationTestUser::create(['email' => '2@laravel.com']);
        ModelSerializationTestUser::create(['email' => '3@laravel.com']);
        ModelSerializationTestUser::create(['email' => '1@laravel.com']);

        $serialized = backport_serialize(new CollectionSerializationTestClass(
            ModelSerializationTestUser::orderByDesc('email')->get()
        ));

        ModelSerializationTestUser::where(['email' => '2@laravel.com'])->delete();

        $unserialized = backport_unserialize($serialized);

        $this->assertCount(2, $unserialized->users);

        $this->assertSame('3@laravel.com', $unserialized->users->first()->email);
        $this->assertSame('1@laravel.com', $unserialized->users->last()->email);
    }

    public function testItCanUnserializeCustomCollection()
    {
        ModelSerializationTestCustomUser::create(['email' => 'mohamed@laravel.com']);
        ModelSerializationTestCustomUser::create(['email' => 'taylor@laravel.com']);

        $serialized = backport_serialize(new CollectionSerializationTestClass(
            ModelSerializationTestCustomUser::all()
        ));

        $unserialized = backport_unserialize($serialized);

        $this->assertInstanceOf(ModelSerializationTestCustomUserCollection::class, $unserialized->users);
    }

    /**
     * @requires PHP 7.4
     */
    public function testItSerializesTypedProperties()
    {
        require_once __DIR__.'/typed-properties.php';

        $user = ModelSerializationTestUser::create([
            'email' => 'mohamed@laravel.com',
        ]);

        ModelSerializationTestUser::create([
            'email' => 'taylor@laravel.com',
        ]);

        $serialized = backport_serialize(new TypedPropertyTestClass($user, 5, ['James', 'Taylor', 'Mohamed']));

        $unSerialized = backport_unserialize($serialized);

        $this->assertSame('testing', $unSerialized->user->getConnectionName());
        $this->assertSame('mohamed@laravel.com', $unSerialized->user->email);
        $this->assertSame(5, $unSerialized->getId());
        $this->assertSame(['James', 'Taylor', 'Mohamed'], $unSerialized->getNames());

        $serialized = backport_serialize(new TypedPropertyCollectionTestClass(ModelSerializationTestUser::on('testing')->get()));

        $unSerialized = backport_unserialize($serialized);

        $this->assertSame('testing', $unSerialized->users[0]->getConnectionName());
        $this->assertSame('mohamed@laravel.com', $unSerialized->users[0]->email);
        $this->assertSame('testing', $unSerialized->users[1]->getConnectionName());
        $this->assertSame('taylor@laravel.com', $unSerialized->users[1]->email);
    }

    public function test_model_serialization_structure()
    {
        $user = ModelSerializationTestUser::create([
            'email' => 'taylor@laravel.com',
        ]);

        $serialized = backport_serialize(new ModelSerializationParentAccessibleTestClass($user, $user, $user));

        if (\version_compare(\PHP_VERSION, '7.4', '>=')) {

        $this->assertSame(
            'O:78:"Illuminate\\Tests\\Integration\\Queue\\ModelSerializationParentAccessibleTestClass":2:{s:4:"user";O:45:"Illuminate\\Contracts\\Database\\ModelIdentifier":5:{s:5:"class";s:61:"Illuminate\\Tests\\Integration\\Queue\\ModelSerializationTestUser";s:2:"id";i:1;s:9:"relations";a:0:{}s:10:"connection";s:7:"testing";s:15:"collectionClass";N;}s:8:"'."\0".'*'."\0".'user2";O:45:"Illuminate\\Contracts\\Database\\ModelIdentifier":5:{s:5:"class";s:61:"Illuminate\\Tests\\Integration\\Queue\\ModelSerializationTestUser";s:2:"id";i:1;s:9:"relations";a:0:{}s:10:"connection";s:7:"testing";s:15:"collectionClass";N;}}', $serialized
        );

        } else {

        $this->assertSame(
            'a:1:{s:17:"[\'¯\_(ツ)_/¯\']";a:2:{i:0;s:78:"Illuminate\Tests\Integration\Queue\ModelSerializationParentAccessibleTestClass";i:1;a:2:{s:4:"user";O:45:"Illuminate\Contracts\Database\ModelIdentifier":5:{s:5:"class";s:61:"Illuminate\Tests\Integration\Queue\ModelSerializationTestUser";s:2:"id";i:1;s:9:"relations";a:0:{}s:10:"connection";s:7:"testing";s:15:"collectionClass";N;}s:8:"'."\0*\0".'user2";O:45:"Illuminate\Contracts\Database\ModelIdentifier":5:{s:5:"class";s:61:"Illuminate\Tests\Integration\Queue\ModelSerializationTestUser";s:2:"id";i:1;s:9:"relations";a:0:{}s:10:"connection";s:7:"testing";s:15:"collectionClass";N;}}}}', $serialized
        );

        }
    }

    /**
     * @requires PHP 8.0
     */
    public function test_it_respects_without_relations_attribute()
    {
        $user = User::create([
            'email' => 'taylor@laravel.com',
        ])->load(['roles']);

        $serialized = serialize(new ModelSerializationWithoutRelations($user));

        $this->assertSame(
            'O:69:"Illuminate\Tests\Integration\Queue\ModelSerializationWithoutRelations":1:{s:4:"user";O:45:"Illuminate\Contracts\Database\ModelIdentifier":5:{s:5:"class";s:39:"Illuminate\Tests\Integration\Queue\User";s:2:"id";i:1;s:9:"relations";a:0:{}s:10:"connection";s:7:"testing";s:15:"collectionClass";N;}}', $serialized
        );
    }

    /**
     * @requires PHP 8.0
     */
    public function test_it_respects_without_relations_attribute_applied_to_class()
    {
        $user = User::create([
            'email' => 'taylor@laravel.com',
        ])->load(['roles']);

        $serialized = serialize(new ModelSerializationAttributeTargetsClassTestClass($user, new DataValueObject('hello')));

        $this->assertSame(
            'O:83:"Illuminate\Tests\Integration\Queue\ModelSerializationAttributeTargetsClassTestClass":2:{s:4:"user";O:45:"Illuminate\Contracts\Database\ModelIdentifier":5:{s:5:"class";s:39:"Illuminate\Tests\Integration\Queue\User";s:2:"id";i:1;s:9:"relations";a:0:{}s:10:"connection";s:7:"testing";s:15:"collectionClass";N;}s:5:"value";O:50:"Illuminate\Tests\Integration\Queue\DataValueObject":1:{s:5:"value";s:5:"hello";}}',
            $serialized
        );

        /** @var ModelSerializationAttributeTargetsClassTestClass $unserialized */
        $unserialized = unserialize($serialized);

        $this->assertFalse($unserialized->user->relationLoaded('roles'));
        $this->assertEquals('hello', $unserialized->value->value);
    }

    public function test_serialization_types_empty_custom_eloquent_collection()
    {
        $class = new ModelSerializationTypedCustomCollectionTestClass(
            new ModelSerializationTestCustomUserCollection());

        $serialized = backport_serialize($class);

        backport_unserialize($serialized);

        $this->assertTrue(true);
    }
}

trait TraitBootsAndInitializersTest
{
    public $fooBar = false;

    public function initializeTraitBootsAndInitializersTest()
    {
        $this->fooBar = ! $this->fooBar;
    }

    public static function bootTraitBootsAndInitializersTest()
    {
        static::addGlobalScope('foo_bar', function () {
        });
    }
}

class ModelBootTestWithTraitInitialization extends Model
{
    use TraitBootsAndInitializersTest;
}

class ModelSerializationTestUser extends Model
{
    public $table = 'users';
    public $guarded = [];
    public $timestamps = false;
}

class ModelSerializationTestCustomUserCollection extends Collection
{
    //
}

class ModelSerializationTypedCustomCollectionTestClass
{
    use SerializesModels;

    public /*ModelSerializationTestCustomUserCollection */$collection;

    public function __construct(ModelSerializationTestCustomUserCollection $collection)
    {
        $this->collection = $collection;
    }
}

class ModelSerializationTestCustomUser extends Model
{
    public $table = 'users';
    public $guarded = [];
    public $timestamps = false;

    public function newCollection(array $models = [])
    {
        return new ModelSerializationTestCustomUserCollection($models);
    }
}

class Order extends Model
{
    public $guarded = [];
    public $timestamps = false;

    public function line()
    {
        return $this->hasOne(Line::class);
    }

    public function lines()
    {
        return $this->hasMany(Line::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'lines');
    }
}

class Line extends Model
{
    public $guarded = [];
    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

class Product extends Model
{
    public $guarded = [];
    public $timestamps = false;
}

class User extends Model
{
    public $guarded = [];
    public $timestamps = false;

    public function roles()
    {
        return $this->belongsToMany(Role::class)
            ->using(RoleUser::class);
    }
}

class Role extends Model
{
    public $guarded = [];
    public $timestamps = false;

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->using(RoleUser::class);
    }
}

class RoleUser extends Pivot
{
    public $guarded = [];
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}

class ModelSerializationTestClass
{
    use SerializesModels;

    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }
}

class ModelSerializationAccessibleTestClass
{
    use SerializesModels;

    public $user;
    protected $user2;
    private $user3;

    public function __construct($user, $user2, $user3)
    {
        $this->user = $user;
        $this->user2 = $user2;
        $this->user3 = $user3;
    }
}

class ModelSerializationParentAccessibleTestClass extends ModelSerializationAccessibleTestClass
{
    //
}

class ModelSerializationWithoutRelations
{
    use SerializesModels;

    #[WithoutRelations]
    public /*User */$user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}

#[WithoutRelations]
class ModelSerializationAttributeTargetsClassTestClass
{
    use SerializesModels;

    public $user;
    public $value;

    public function __construct(/*public */User $user, /*public */DataValueObject $value)
    {
        $this->user = $user;
        $this->value = $value;
    }
}

class ModelRelationSerializationTestClass
{
    use SerializesModels;

    public $order;

    public function __construct($order)
    {
        $this->order = $order;
    }
}

class CollectionSerializationTestClass
{
    use SerializesModels;

    public $users;

    public function __construct($users)
    {
        $this->users = $users;
    }
}

class DataValueObject
{
    public $value;

    public function __construct(/*public */$value = 1)
    {
        $this->value = $value;
    }
}
