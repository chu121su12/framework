<?php

namespace Illuminate\Tests\Integration\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Orchestra\Testbench\Attributes\WithConfig;
use Orchestra\Testbench\Attributes\WithMigration;
use Orchestra\Testbench\Factories\UserFactory;
use Orchestra\Testbench\TestCase;

#[WithMigration]
#[WithEnv('BCRYPT_ROUNDS', 12)]
#[WithConfig('app.key', 'base64:IUHRqAQ99pZ0A1MPjbuv1D6ff3jxv0GIvS2qIW4JNU4=')]
class RehashOnLogoutOtherDevicesTest extends TestCase
{
    protected function attributeBp()
    {
        return [
            'config' => [
                ['hashing.bcrypt.rounds', 12], // env
                ['app.key', 'base64:IUHRqAQ99pZ0A1MPjbuv1D6ff3jxv0GIvS2qIW4JNU4'],
            ],
            'migration' => true,
        ];
    }
    
    use RefreshDatabase;

    protected function defineRoutes($router)
    {
        $router->post('logout', function (Request $request) {
            auth()->logoutOtherDevices($request->input('password'));

            return response()->noContent();
        })->middleware(['web', 'auth']);
    }

    public function testItRehashThePasswordUsingLogoutOtherDevices()
    {
        $this->withoutExceptionHandling();

        $user = UserFactory::new_()->create();

        $password = $user->password;

        $this->actingAs($user);

        $this->post('logout', [
            'password' => 'password',
        ])->assertStatus(204);

        $user->refresh();

        $this->assertNotSame($password, $user->password);
    }
}
