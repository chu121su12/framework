<?php

namespace Illuminate\Tests\Foundation;

use Closure;
use Exception;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\NullStore;
use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Cache\Repository;
use Illuminate\Config\Repository as Config;
use Illuminate\Container\Container;
use Illuminate\Contracts\Routing\ResponseFactory as ResponseFactoryContract;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Foundation\Testing\Concerns\InteractsWithExceptionHandling;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Lottery;
use Illuminate\Support\MessageBag;
use Illuminate\Testing\Assert;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use InvalidArgumentException;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use OutOfRangeException;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use RuntimeException;
use stdClass;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FoundationExceptionsHandlerTest_testItReturnsSpecificErrorViewIfExists extends Handler
        {
            public function getErrorView($e)
            {
                return $this->getHttpExceptionView($e);
            }
        }

class FoundationExceptionsHandlerTest_testItReturnsFallbackErrorViewIfExists extends Handler
        {
            public function getErrorView($e)
            {
                return $this->getHttpExceptionView($e);
            }
        }

class FoundationExceptionsHandlerTest_testItReturnsNullIfNoErrorViewExists extends Handler
        {
            public function getErrorView($e)
            {
                return $this->getHttpExceptionView($e);
            }
        }

class FoundationExceptionsHandlerTest_testItDoesNotThrottleExceptionsWhenNullReturned_class extends Handler
        {
            protected function throttle($e)
            {
                //
            }
        }

        class FoundationExceptionsHandlerTest_testItDoesNotThrottleExceptionsWhenUnlimitedLimit_class extends Handler
        {
            protected function throttle($e)
            {
                return Limit::none();
            }
        }

class FoundationExceptionsHandlerTest_testItCanSampleExceptionsByClass_class extends Handler
        {
            protected function throttle($e)
            {
                switch (true) {
                    case $e instanceof RuntimeException: return Lottery::odds(2, 10);
                    default: return parent::throttle($e);
                }
            }
        }

class FoundationExceptionsHandlerTest_testItRescuesExceptionsWhileThrottlingAndReports_class extends Handler
        {
            protected function throttle($e)
            {
                throw new RuntimeException('Something went wrong in the throttle method.');
            }
        }

class FoundationExceptionsHandlerTest_testItRescuesExceptionsIfThereIsAnIssueResolvingTheRateLimiter_class extends Handler
        {
            protected function throttle($e)
            {
                return Limit::perDay(1);
            }
        }

class FoundationExceptionsHandlerTest_testItRescuesExceptionsIfThereIsAnIssueWithTheRateLimiter_class_1 extends Handler
        {
            protected function throttle($e)
            {
                return Limit::perDay(1);
            }
        }

class FoundationExceptionsHandlerTest_testItRescuesExceptionsIfThereIsAnIssueWithTheRateLimiter_class_2 extends RateLimiter
        {
            public $attempted = false;

            public function attempt($key, $maxAttempts, Closure $callback, $decaySeconds = 60)
            {
                $this->attempted = true;

                throw new Exception('Unable to connect to Redis.');
            }
        }

class FoundationExceptionsHandlerTest_testItCanRateLimitExceptions_class_1 extends Handler
        {
            protected function throttle($e)
            {
                return Limit::perMinute(7);
            }
        }

class FoundationExceptionsHandlerTest_testItCanRateLimitExceptions_class_2 extends RateLimiter
        {
            public $attempted = 0;

            public function attempt($key, $maxAttempts, Closure $callback, $decaySeconds = 60)
            {
                $this->attempted++;

                return parent::attempt(...func_get_args());
            }
        }

class FoundationExceptionsHandlerTest extends TestCase
{
    use \PHPUnit\Framework\PhpUnit8Assert;
    use MockeryPHPUnitIntegration;
    use InteractsWithExceptionHandling;

    protected $config;

    protected $container;

    protected $handler;

    protected $request;

    protected function setUp()/*: void*/
    {
        $this->config = m::mock(Config::class);

        $this->request = m::mock(stdClass::class);

        $this->container = Container::setInstance(new Container);

        $this->container->instance('config', $this->config);

        $this->container->instance(ResponseFactoryContract::class, new ResponseFactory(
            m::mock(ViewFactory::class),
            m::mock(Redirector::class)
        ));

        $this->handler = new Handler($this->container);
    }

    protected function tearDown()/*: void*/
    {
        Container::setInstance(null);
    }

    public function testHandlerReportsExceptionAsContext()
    {
        $logger = m::mock(LoggerInterface::class);
        $this->container->instance(LoggerInterface::class, $logger);
        $logger->shouldReceive('error')->withArgs(['Exception message', m::hasKey('exception')])->once();

        $this->handler->report(new RuntimeException('Exception message'));
    }

    public function testHandlerCallsContextMethodIfPresent()
    {
        $logger = m::mock(LoggerInterface::class);
        $this->container->instance(LoggerInterface::class, $logger);
        $logger->shouldReceive('error')->withArgs(['Exception message', m::subset(['foo' => 'bar'])])->once();

        $this->handler->report(new ContextProvidingException('Exception message'));
    }

    public function testHandlerReportsExceptionWhenUnReportable()
    {
        $logger = m::mock(LoggerInterface::class);
        $this->container->instance(LoggerInterface::class, $logger);
        $logger->shouldReceive('error')->withArgs(['Exception message', m::hasKey('exception')])->once();

        $this->handler->report(new UnReportableException('Exception message'));
    }

    public function testHandlerReportsExceptionWithCustomLogLevel()
    {
        $logger = m::mock(LoggerInterface::class);
        $this->container->instance(LoggerInterface::class, $logger);

        $logger->shouldReceive('critical')->withArgs(['Critical message', m::hasKey('exception')])->once();
        $logger->shouldReceive('error')->withArgs(['Error message', m::hasKey('exception')])->once();
        $logger->shouldReceive('log')->withArgs(['custom', 'Custom message', m::hasKey('exception')])->once();

        $this->handler->level(InvalidArgumentException::class, LogLevel::CRITICAL);
        $this->handler->level(OutOfRangeException::class, 'custom');

        $this->handler->report(new InvalidArgumentException('Critical message'));
        $this->handler->report(new RuntimeException('Error message'));
        $this->handler->report(new OutOfRangeException('Custom message'));
    }

    public function testHandlerIgnoresNotReportableExceptions()
    {
        $logger = m::mock(LoggerInterface::class);
        $this->container->instance(LoggerInterface::class, $logger);
        $logger->shouldNotReceive('log');

        $this->handler->ignore(RuntimeException::class);

        $this->handler->report(new RuntimeException('Exception message'));
    }

    public function testHandlerCallsReportMethodWithDependencies()
    {
        $reporter = m::mock(ReportingService::class);
        $this->container->instance(ReportingService::class, $reporter);
        $reporter->shouldReceive('send')->withArgs(['Exception message'])->once();

        $logger = m::mock(LoggerInterface::class);
        $this->container->instance(LoggerInterface::class, $logger);
        $logger->shouldNotReceive('log');

        $this->handler->report(new ReportableException('Exception message'));
    }

    public function testHandlerReportsExceptionUsingCallableClass()
    {
        $reporter = m::mock(ReportingService::class);
        $reporter->shouldReceive('send')->withArgs(['Exception message'])->once();

        $logger = m::mock(LoggerInterface::class);
        $this->container->instance(LoggerInterface::class, $logger);
        $logger->shouldNotReceive('log');

        $this->handler->reportable(new CustomReporter($reporter));

        $this->handler->report(new CustomException('Exception message'));
    }

    public function testReturnsJsonWithStackTraceWhenAjaxRequestAndDebugTrue()
    {
        $this->config->shouldReceive('get')->with('app.debug', null)->once()->andReturn(true);
        $this->request->shouldReceive('expectsJson')->once()->andReturn(true);

        $response = $this->handler->render($this->request, new Exception('My custom error message'))->getContent();

        $this->assertStringNotContainsString('<!DOCTYPE html>', $response);
        $this->assertStringContainsString('"message": "My custom error message"', $response);
        $this->assertStringContainsString('"file":', $response);
        $this->assertStringContainsString('"line":', $response);
        $this->assertStringContainsString('"trace":', $response);
    }

    public function testReturnsCustomResponseFromRenderableCallback()
    {
        $this->handler->renderable(function (CustomException $e, $request) {
            $this->assertSame($this->request, $request);

            return response()->json(['response' => 'My custom exception response']);
        });

        $response = $this->handler->render($this->request, new CustomException)->getContent();

        $this->assertSame('{"response":"My custom exception response"}', $response);
    }

    public function testReturnsCustomResponseFromCallableClass()
    {
        $this->handler->renderable(new CustomRenderer);

        $response = $this->handler->render($this->request, new CustomException)->getContent();

        $this->assertSame('{"response":"The CustomRenderer response"}', $response);
    }

    public function testReturnsResponseFromRenderableException()
    {
        $response = $this->handler->render(Request::create('/'), new RenderableException)->getContent();

        $this->assertSame('{"response":"My renderable exception response"}', $response);
    }

    public function testReturnsResponseFromMappedRenderableException()
    {
        $this->handler->map(RuntimeException::class, RenderableException::class);

        $response = $this->handler->render(Request::create('/'), new RuntimeException)->getContent();

        $this->assertSame('{"response":"My renderable exception response"}', $response);
    }

    public function testReturnsCustomResponseWhenExceptionImplementsResponsable()
    {
        $response = $this->handler->render($this->request, new ResponsableException)->getContent();

        $this->assertSame('{"response":"My responsable exception response"}', $response);
    }

    public function testReturnsJsonWithoutStackTraceWhenAjaxRequestAndDebugFalseAndExceptionMessageIsMasked()
    {
        $this->config->shouldReceive('get')->with('app.debug', null)->once()->andReturn(false);
        $this->request->shouldReceive('expectsJson')->once()->andReturn(true);

        $response = $this->handler->render($this->request, new Exception('This error message should not be visible'))->getContent();

        $this->assertStringContainsString('"message": "Server Error"', $response);
        $this->assertStringNotContainsString('<!DOCTYPE html>', $response);
        $this->assertStringNotContainsString('This error message should not be visible', $response);
        $this->assertStringNotContainsString('"file":', $response);
        $this->assertStringNotContainsString('"line":', $response);
        $this->assertStringNotContainsString('"trace":', $response);
    }

    public function testReturnsJsonWithoutStackTraceWhenAjaxRequestAndDebugFalseAndHttpExceptionErrorIsShown()
    {
        $this->config->shouldReceive('get')->with('app.debug', null)->once()->andReturn(false);
        $this->request->shouldReceive('expectsJson')->once()->andReturn(true);

        $response = $this->handler->render($this->request, new HttpException(403, 'My custom error message'))->getContent();

        $this->assertStringContainsString('"message": "My custom error message"', $response);
        $this->assertStringNotContainsString('<!DOCTYPE html>', $response);
        $this->assertStringNotContainsString('"message": "Server Error"', $response);
        $this->assertStringNotContainsString('"file":', $response);
        $this->assertStringNotContainsString('"line":', $response);
        $this->assertStringNotContainsString('"trace":', $response);
    }

    public function testReturnsJsonWithoutStackTraceWhenAjaxRequestAndDebugFalseAndAccessDeniedHttpExceptionErrorIsShown()
    {
        $this->config->shouldReceive('get')->with('app.debug', null)->once()->andReturn(false);
        $this->request->shouldReceive('expectsJson')->once()->andReturn(true);

        $response = $this->handler->render($this->request, new AccessDeniedHttpException('My custom error message'))->getContent();

        $this->assertStringContainsString('"message": "My custom error message"', $response);
        $this->assertStringNotContainsString('<!DOCTYPE html>', $response);
        $this->assertStringNotContainsString('"message": "Server Error"', $response);
        $this->assertStringNotContainsString('"file":', $response);
        $this->assertStringNotContainsString('"line":', $response);
        $this->assertStringNotContainsString('"trace":', $response);
    }

    public function testValidateFileMethod()
    {
        $argumentExpected = ['input' => 'My input value'];
        $argumentActual = null;

        $this->container->singleton('redirect', function () use (&$argumentActual) {
            $redirector = m::mock(Redirector::class);

            $redirector->shouldReceive('to')->once()
                ->andReturn($responder = m::mock(RedirectResponse::class));

            $responder->shouldReceive('withInput')->once()->with(m::on(
                function ($argument) use (&$argumentActual) {
                    $argumentActual = $argument;

                    return true;
                }))->andReturn($responder);

            $responder->shouldReceive('withErrors')->once()
                ->andReturn($responder);

            return $redirector;
        });

        $file = m::mock(UploadedFile::class);
        $file->shouldReceive('getPathname')->andReturn('photo.jpg');
        $file->shouldReceive('getClientOriginalName')->andReturn('photo.jpg');
        $file->shouldReceive('getClientMimeType')->andReturn('application/octet-stream');
        $file->shouldReceive('getError')->andReturn(\UPLOAD_ERR_NO_FILE);

        $request = Request::create('/', 'POST', $argumentExpected, [], ['photo' => $file]);

        $validator = m::mock(Validator::class);
        $validator->shouldReceive('errors')->andReturn(new MessageBag(['error' => 'My custom validation exception']));

        $validationException = new ValidationException($validator);
        $validationException->redirectTo = '/';

        $this->handler->render($request, $validationException);

        $this->assertEquals($argumentExpected, $argumentActual);
    }

    public function testSuspiciousOperationReturns404WithoutReporting()
    {
        $this->config->shouldReceive('get')->with('app.debug', null)->once()->andReturn(true);
        $this->request->shouldReceive('expectsJson')->once()->andReturn(true);

        $response = $this->handler->render($this->request, new SuspiciousOperationException('Invalid method override "__CONSTRUCT"'));

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertStringContainsString('"message": "Bad hostname provided."', $response->getContent());

        $logger = m::mock(LoggerInterface::class);
        $this->container->instance(LoggerInterface::class, $logger);
        $logger->shouldNotReceive('log');

        $this->handler->report(new SuspiciousOperationException('Invalid method override "__CONSTRUCT"'));
    }

    public function testRecordsNotFoundReturns404WithoutReporting()
    {
        $this->config->shouldReceive('get')->with('app.debug', null)->once()->andReturn(true);
        $this->request->shouldReceive('expectsJson')->once()->andReturn(true);

        $response = $this->handler->render($this->request, new RecordsNotFoundException);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertStringContainsString('"message": "Not found."', $response->getContent());

        $logger = m::mock(LoggerInterface::class);
        $this->container->instance(LoggerInterface::class, $logger);
        $logger->shouldNotReceive('log');

        $this->handler->report(new RecordsNotFoundException);
    }

    public function testItReturnsSpecificErrorViewIfExists()
    {
        $viewFactory = m::mock(stdClass::class);
        $viewFactory->shouldReceive('exists')->with('errors::502')->andReturn(true);

        $this->container->instance(ViewFactory::class, $viewFactory);

        $handler = new FoundationExceptionsHandlerTest_testItReturnsSpecificErrorViewIfExists($this->container);

        $this->assertSame('errors::502', $handler->getErrorView(new HttpException(502)));
    }

    public function testItReturnsFallbackErrorViewIfExists()
    {
        $viewFactory = m::mock(stdClass::class);
        $viewFactory->shouldReceive('exists')->once()->with('errors::502')->andReturn(false);
        $viewFactory->shouldReceive('exists')->once()->with('errors::5xx')->andReturn(true);

        $this->container->instance(ViewFactory::class, $viewFactory);

        $handler = new FoundationExceptionsHandlerTest_testItReturnsFallbackErrorViewIfExists($this->container);

        $this->assertSame('errors::5xx', $handler->getErrorView(new HttpException(502)));
    }

    public function testItReturnsNullIfNoErrorViewExists()
    {
        $viewFactory = m::mock(stdClass::class);
        $viewFactory->shouldReceive('exists')->once()->with('errors::404')->andReturn(false);
        $viewFactory->shouldReceive('exists')->once()->with('errors::4xx')->andReturn(false);

        $this->container->instance(ViewFactory::class, $viewFactory);

        $handler = new FoundationExceptionsHandlerTest_testItReturnsNullIfNoErrorViewExists($this->container);

        $this->assertNull($handler->getErrorView(new HttpException(404)));
    }

    public function testAssertExceptionIsThrown()
    {
        $this->assertThrows(function () {
            throw new Exception;
        });
        $this->assertThrows(function () {
            throw new CustomException;
        });
        $this->assertThrows(function () {
            throw new CustomException;
        }, CustomException::class);
        $this->assertThrows(function () {
            throw new Exception('Some message.');
        }, null, /*expectedMessage: */'Some message.');
        $this->assertThrows(function () {
            throw new CustomException('Some message.');
        }, null, /*expectedMessage: */'Some message.');
        $this->assertThrows(function () {
            throw new CustomException('Some message.');
        }, /*expectedClass: */CustomException::class, /*expectedMessage: */'Some message.');

        try {
            $this->assertThrows(function () {
                throw new Exception;
            }, CustomException::class);
            $testFailed = true;
        } catch (AssertionFailedError $_e) {
            $testFailed = false;
        }

        if ($testFailed) {
            Assert::fail('assertThrows failed: non matching exceptions are thrown.');
        }

        try {
            $this->assertThrows(function () {
                throw new Exception('Some message.');
            }, /*expectedClass: */Exception::class, /*expectedMessage: */'Other message.');
            $testFailed = true;
        } catch (AssertionFailedError $_e) {
            $testFailed = false;
        }

        if ($testFailed) {
            Assert::fail('assertThrows failed: non matching message are thrown.');
        }
    }

    public function testItReportsDuplicateExceptions()
    {
        $reported = [];
        if (version_compare(PHP_VERSION, '7.0', '<')) {
            $this->handler->reportable(function (\Exception $e) use (&$reported) {
                backport_type_throwable($e);
    
                $reported[] = $e;
    
                return false;
            });
        }
        else {
            $this->handler->reportable(function (\Throwable $e) use (&$reported) {
                backport_type_throwable($e);
    
                $reported[] = $e;
    
                return false;
            });
        }

        $this->handler->report($one = new RuntimeException('foo'));
        $this->handler->report($one);
        $this->handler->report($two = new RuntimeException('foo'));

        $this->assertSame($reported, [$one, $one, $two]);
    }

    public function testItCanDedupeExceptions()
    {
        $reported = [];
        $e = new RuntimeException('foo');
        if (version_compare(PHP_VERSION, '7.0', '<')) {
            $this->handler->reportable(function (\Exception $e) use (&$reported) {
                backport_type_throwable($e);
    
                $reported[] = $e;
    
                return false;
            });
        }
        else {
            $this->handler->reportable(function (\Throwable $e) use (&$reported) {
                backport_type_throwable($e);
    
                $reported[] = $e;
    
                return false;
            });
        }

        $this->handler->dontReportDuplicates();
        $this->handler->report($one = new RuntimeException('foo'));
        $this->handler->report($one);
        $this->handler->report($two = new RuntimeException('foo'));

        $this->assertSame($reported, [$one, $two]);
    }

    public function testItDoesNotThrottleExceptionsByDefault()
    {
        $reported = [];
        $this->handler->reportable(function (\Throwable $e) use (&$reported) {
            $reported[] = $e;

            return false;
        });

        for ($i = 0; $i < 100; $i++) {
            $this->handler->report(new RuntimeException("Exception {$i}"));
        }

        $this->assertCount(100, $reported);
    }

    public function testItDoesNotThrottleExceptionsWhenNullReturned()
    {
        $handler = new FoundationExceptionsHandlerTest_testItDoesNotThrottleExceptionsWhenNullReturned_class($this->container);
        $reported = [];
        if (version_compare(PHP_VERSION, '7.0', '<')) {
            $handler->reportable(function (\Exception $e) use (&$reported) {
                $reported[] = $e;
    
                return false;
            });
        }
        else {
            $handler->reportable(function (\Throwable $e) use (&$reported) {
                $reported[] = $e;
    
                return false;
            });
        }

        for ($i = 0; $i < 100; $i++) {
            $handler->report(new RuntimeException("Exception {$i}"));
        }

        $this->assertCount(100, $reported);
    }

    public function testItDoesNotThrottleExceptionsWhenUnlimitedLimit()
    {
        $handler = new FoundationExceptionsHandlerTest_testItDoesNotThrottleExceptionsWhenUnlimitedLimit_class($this->container);
        $reported = [];
        if (version_compare(PHP_VERSION, '7.0', '<')) {
            $handler->reportable(function (\Exception $e) use (&$reported) {
                $reported[] = $e;
    
                return false;
            });
        }
        else {
            $handler->reportable(function (\Throwable $e) use (&$reported) {
                $reported[] = $e;
    
                return false;
            });
        }

        for ($i = 0; $i < 100; $i++) {
            $handler->report(new RuntimeException("Exception {$i}"));
        }

        $this->assertCount(100, $reported);
    }

    public function testItCanSampleExceptionsByClass()
    {
        $handler = new FoundationExceptionsHandlerTest_testItCanSampleExceptionsByClass_class($this->container);
        Lottery::forceResultWithSequence([
            true, false, false, false, false,
            true, false, false, false, false,
        ]);
        $reported = [];
        if (version_compare(PHP_VERSION, '7.0', '<')) {
            $handler->reportable(function (\Exception $e) use (&$reported) {
                $reported[] = $e;
    
                return false;
            });
        }
        else {
            $handler->reportable(function (\Throwable $e) use (&$reported) {
                $reported[] = $e;
    
                return false;
            });
        }

        for ($i = 0; $i < 10; $i++) {
            $handler->report(new Exception("Exception {$i}"));
            $handler->report(new RuntimeException("RuntimeException {$i}"));
        }

        list($runtimeExceptions, $baseExceptions) = collect($reported)->partition(function ($e) { return $e instanceof RuntimeException; });
        $this->assertCount(10, $baseExceptions);
        $this->assertCount(2, $runtimeExceptions);
    }

    public function testItRescuesExceptionsWhileThrottlingAndReports()
    {
        $handler = new FoundationExceptionsHandlerTest_testItRescuesExceptionsWhileThrottlingAndReports_class($this->container);
        $reported = [];
        $handler->reportable(function (\Throwable $e) use (&$reported) {
            $reported[] = $e;

            return false;
        });

        $handler->report(new Exception('Something in the app went wrong.'));

        $this->assertCount(1, $reported);
        $this->assertSame('Something in the app went wrong.', $reported[0]->getMessage());
    }

    public function testItRescuesExceptionsIfThereIsAnIssueResolvingTheRateLimiter()
    {
        $handler = new FoundationExceptionsHandlerTest_testItRescuesExceptionsIfThereIsAnIssueResolvingTheRateLimiter_class($this->container);
        $reported = [];
        if (version_compare(PHP_VERSION, '7.0', '<')) {
            $handler->reportable(function (\Exception $e) use (&$reported) {
                $reported[] = $e;
    
                return false;
            });
        }
        else {
            $handler->reportable(function (\Throwable $e) use (&$reported) {
                $reported[] = $e;
    
                return false;
            });
        }

        $resolved = false;
        $this->container->bind(RateLimiter::class, function () use (&$resolved) {
            $resolved = true;

            throw new Exception('Error resolving rate limiter.');
        });

        $handler->report(new Exception('Something in the app went wrong.'));

        $this->assertTrue($resolved);
        $this->assertCount(1, $reported);
        $this->assertSame('Something in the app went wrong.', $reported[0]->getMessage());
    }

    public function testItRescuesExceptionsIfThereIsAnIssueWithTheRateLimiter()
    {
        $handler = new FoundationExceptionsHandlerTest_testItRescuesExceptionsIfThereIsAnIssueWithTheRateLimiter_class_1($this->container);
        $reported = [];
        if (version_compare(PHP_VERSION, '7.0', '<')) {
            $handler->reportable(function (\Exception $e) use (&$reported) {
                $reported[] = $e;
    
                return false;
            });
        }
        else {
            $handler->reportable(function (\Throwable $e) use (&$reported) {
                $reported[] = $e;
    
                return false;
            });
        }

        $this->container->instance(RateLimiter::class,
            $limiter = new FoundationExceptionsHandlerTest_testItRescuesExceptionsIfThereIsAnIssueWithTheRateLimiter_class_2(new Repository(new NullStore))
        );

        $handler->report(new Exception('Something in the app went wrong.'));

        $this->assertTrue($limiter->attempted);
        $this->assertCount(1, $reported);
        $this->assertSame('Something in the app went wrong.', $reported[0]->getMessage());
    }

    public function testItCanRateLimitExceptions()
    {
        $handler = new FoundationExceptionsHandlerTest_testItCanRateLimitExceptions_class_1($this->container);
        $reported = [];
        if (version_compare(PHP_VERSION, '7.0', '<')) {
            $handler->reportable(function (\Exception $e) use (&$reported) {
                $reported[] = $e;
    
                return false;
            });
        }
        else {
            $handler->reportable(function (\Throwable $e) use (&$reported) {
                $reported[] = $e;
    
                return false;
            });
        }

        $this->container->instance(RateLimiter::class,
            $limiter = new FoundationExceptionsHandlerTest_testItCanRateLimitExceptions_class_2(new Repository(new ArrayStore))
        );
        Carbon::setTestNow(Carbon::now()->startOfDay());

        for ($i = 0; $i < 100; $i++) {
            $handler->report(new Exception('Something in the app went wrong.'));
        }

        $this->assertSame(100, $limiter->attempted);
        $this->assertCount(7, $reported);
        $this->assertSame('Something in the app went wrong.', $reported[0]->getMessage());

        Carbon::setTestNow(Carbon::now()->addMinute());

        for ($i = 0; $i < 100; $i++) {
            $handler->report(new Exception('Something in the app went wrong.'));
        }

        $this->assertSame(200, $limiter->attempted);
        $this->assertCount(14, $reported);
        $this->assertSame('Something in the app went wrong.', $reported[0]->getMessage());
    }
}

class CustomException extends Exception
{
}

class ResponsableException extends Exception implements Responsable
{
    public function toResponse($request)
    {
        return response()->json(['response' => 'My responsable exception response']);
    }
}

class ReportableException extends Exception
{
    public function report(ReportingService $reportingService)
    {
        $reportingService->send($this->getMessage());
    }
}

class UnReportableException extends Exception
{
    public function report()
    {
        return false;
    }
}

class RenderableException extends Exception
{
    public function render($request)
    {
        return response()->json(['response' => 'My renderable exception response']);
    }
}

class ContextProvidingException extends Exception
{
    public function context()
    {
        return [
            'foo' => 'bar',
        ];
    }
}

class CustomReporter
{
    private $service;

    public function __construct(ReportingService $service)
    {
        $this->service = $service;
    }

    public function __invoke(CustomException $e)
    {
        $this->service->send($e->getMessage());

        return false;
    }
}

class CustomRenderer
{
    public function __invoke(CustomException $e, $request)
    {
        return response()->json(['response' => 'The CustomRenderer response']);
    }
}

interface ReportingService
{
    public function send($message);
}
