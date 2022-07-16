<?php

namespace Illuminate\Tests\Auth;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\HandlesAuthorization;
use PHPUnit\Framework\TestCase;

class AuthHandlesAuthorizationTest_testDenyHasNullStatus_class
        {
            use HandlesAuthorization;

            public function __invoke()
            {
                return $this->deny('xxxx', 321);
            }
        }

class AuthHandlesAuthorizationTest_testItCanDenyWithStatus_1_class
        {
            use HandlesAuthorization;

            public function __invoke()
            {
                return $this->denyWithStatus(418);
            }
        }

class AuthHandlesAuthorizationTest_testItCanDenyWithStatus_2_class
        {
            use HandlesAuthorization;

            public function __invoke()
            {
                return $this->denyWithStatus(418, 'foo', 3);
            }
        }

class AuthHandlesAuthorizationTest_testItCanDenyAsNotFound_1_class
        {
            use HandlesAuthorization;

            public function __invoke()
            {
                return $this->denyAsNotFound();
            }
        }

class AuthHandlesAuthorizationTest_testItCanDenyAsNotFound_2_class
        {
            use HandlesAuthorization;

            public function __invoke()
            {
                return $this->denyAsNotFound('foo', 3);
            }
        }

class AuthHandlesAuthorizationTest extends TestCase
{
    use HandlesAuthorization;

    public function testAllowMethod()
    {
        $response = $this->allow('some message', 'some_code');

        $this->assertTrue($response->allowed());
        $this->assertFalse($response->denied());
        $this->assertSame('some message', $response->message());
        $this->assertSame('some_code', $response->code());
    }

    public function testDenyMethod()
    {
        $response = $this->deny('some message', 'some_code');

        $this->assertTrue($response->denied());
        $this->assertFalse($response->allowed());
        $this->assertSame('some message', $response->message());
        $this->assertSame('some_code', $response->code());
    }

    public function testDenyHasNullStatus()
    {
        $class = new AuthHandlesAuthorizationTest_testDenyHasNullStatus_class;

        try {
            $class()->authorize();
            $this->fail();
        } catch (AuthorizationException $e) {
            $this->assertFalse($e->hasStatus());
            $this->assertNull($e->status());
        }
    }

    public function testItCanDenyWithStatus()
    {
        $class = new AuthHandlesAuthorizationTest_testItCanDenyWithStatus_1_class;

        try {
            $class()->authorize();
            $this->fail();
        } catch (AuthorizationException $e) {
            $this->assertTrue($e->hasStatus());
            $this->assertSame(418, $e->status());
            $this->assertSame('This action is unauthorized.', $e->getMessage());
            $this->assertSame(0, $e->getCode());
        }

        $class = new AuthHandlesAuthorizationTest_testItCanDenyWithStatus_2_class;

        try {
            $class()->authorize();
            $this->fail();
        } catch (AuthorizationException $e) {
            $this->assertTrue($e->hasStatus());
            $this->assertSame(418, $e->status());
            $this->assertSame('foo', $e->getMessage());
            $this->assertSame(3, $e->getCode());
        }
    }

    public function testItCanDenyAsNotFound()
    {
        $class = new AuthHandlesAuthorizationTest_testItCanDenyAsNotFound_1_class;

        try {
            $class()->authorize();
            $this->fail();
        } catch (AuthorizationException $e) {
            $this->assertTrue($e->hasStatus());
            $this->assertSame(404, $e->status());
            $this->assertSame('This action is unauthorized.', $e->getMessage());
            $this->assertSame(0, $e->getCode());
        }

        $class = new AuthHandlesAuthorizationTest_testItCanDenyAsNotFound_2_class;

        try {
            $class()->authorize();
            $this->fail();
        } catch (AuthorizationException $e) {
            $this->assertTrue($e->hasStatus());
            $this->assertSame(404, $e->status());
            $this->assertSame('foo', $e->getMessage());
            $this->assertSame(3, $e->getCode());
        }
    }
}
