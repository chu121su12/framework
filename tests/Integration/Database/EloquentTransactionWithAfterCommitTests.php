<?php

namespace Illuminate\Tests\Integration\Database;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\Concerns\WithLaravelMigrationsForTestCase;
use Orchestra\Testbench\Factories\UserFactory;

trait EloquentTransactionWithAfterCommitTests
{
    use WithLaravelMigrationsForTestCase;

    protected function attributeBpWithMigration()
    {
        return ['laravel'];
    }

    protected function setUpEloquentTransactionWithAfterCommitTests()/*: void*/
    {
        AuthUser::unguard();
    }

    protected function tearDownEloquentTransactionWithAfterCommitTests()/*: void*/
    {
        AuthUser::reguard();
    }

    public function testObserverIsCalledOnTestsWithAfterCommit()
    {
        AuthUser::observe($observer = EloquentTransactionWithAfterCommitTestsUserObserver::resetting());

        $user1 = AuthUser::create(UserFactory::new_()->raw());

        $this->assertTrue($user1->exists);
        $this->assertEquals(1, $observer::$calledTimes, 'Failed to assert the observer was called once.');
    }

    public function testObserverCalledWithAfterCommitWhenInsideTransaction()
    {
        AuthUser::observe($observer = EloquentTransactionWithAfterCommitTestsUserObserver::resetting());

        $user1 = DB::transaction(function () { return AuthUser::create(UserFactory::new_()->raw()); });

        $this->assertTrue($user1->exists);
        $this->assertEquals(1, $observer::$calledTimes, 'Failed to assert the observer was called once.');
    }

    public function testObserverCalledWithAfterCommitWhenInsideTransactionWithDispatchSync()
    {
        User::observe($observer = EloquentTransactionWithAfterCommitTestsUserObserverUsingDispatchSync::resetting());

        $user1 = DB::transaction(function () {
            return User::create(UserFactory::new_()->raw());
        });

        $this->assertTrue($user1->exists);
        $this->assertEquals(1, $observer::$calledTimes, 'Failed to assert the observer was called once.');

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user1->email,
            'token' => sha1($user1->email),
        ]);
    }

    public function testObserverIsCalledOnTestsWithAfterCommitWhenUsingSavepoint()
    {
        AuthUser::observe($observer = EloquentTransactionWithAfterCommitTestsUserObserver::resetting());

        $user1 = AuthUser::createOrFirst(UserFactory::new_()->raw());

        $this->assertTrue($user1->exists);
        $this->assertEquals(1, $observer::$calledTimes, 'Failed to assert the observer was called once.');
    }

    public function testObserverIsCalledOnTestsWithAfterCommitWhenUsingSavepointAndInsideTransaction()
    {
        AuthUser::observe($observer = EloquentTransactionWithAfterCommitTestsUserObserver::resetting());

        $user1 = DB::transaction(function () { return AuthUser::createOrFirst(UserFactory::new_()->raw()); });

        $this->assertTrue($user1->exists);
        $this->assertEquals(1, $observer::$calledTimes, 'Failed to assert the observer was called once.');
    }

    public function testObserverIsCalledEvenWhenDeeplyNestingTransactions()
    {
        AuthUser::observe($observer = EloquentTransactionWithAfterCommitTestsUserObserver::resetting());

        $user1 = DB::transaction(function () use ($observer) {
            return tap(DB::transaction(function () use ($observer) {
                return tap(DB::transaction(function () use ($observer) {
                    return tap(AuthUser::createOrFirst(UserFactory::new_()->raw()), function () use ($observer) {
                        $this->assertEquals(0, $observer::$calledTimes, 'Should not have been called');
                    });
                }), function () use ($observer) {
                    $this->assertEquals(0, $observer::$calledTimes, 'Should not have been called');
                });
            }), function () use ($observer) {
                $this->assertEquals(0, $observer::$calledTimes, 'Should not have been called');
            });
        });

        $this->assertTrue($user1->exists);
        $this->assertEquals(1, $observer::$calledTimes, 'Failed to assert the observer was called once.');
    }
}

class EloquentTransactionWithAfterCommitTestsUserObserver
{
    public static $calledTimes = 0;

    public $afterCommit = true;

    public static function resetting()
    {
        static::$calledTimes = 0;

        return new static();
    }

    public function created($user)
    {
        static::$calledTimes++;
    }
}

class EloquentTransactionWithAfterCommitTestsUserObserverUsingDispatchSync extends EloquentTransactionWithAfterCommitTestsUserObserver
{
    public function created($user)
    {
        dispatch_sync(new EloquentTransactionWithAfterCommitTestsJob($user->email));

        parent::created($user);
    }
}

class EloquentTransactionWithAfterCommitTestsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $email;

    public function __construct(/*public *//*string */$email)
    {
        $this->email = backport_type_check('string', $email);

        // ...
    }

    public function handle()/*: void*/
    {
        DB::transaction(function () {
            DB::table('password_reset_tokens')->insert([
                ['email' => $this->email, 'token' => sha1($this->email), 'created_at' => now()],
            ]);
        });
    }
}
