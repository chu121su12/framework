<?php

namespace Illuminate\Tests\Mail;

use Illuminate\Contracts\Mail\Attachable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use PHPUnit\Framework\TestCase;

class AttachableTest_testItCanHaveMacroConstructors_class implements Attachable
        {
            public function toMailAttachment()
            {
                return Attachment::fromInvoice('foo')
                    ->as_('bar')
                    ->withMime('image/jpeg');
            }
        }

class AttachableTest_testItCanUtiliseExistingApisOnNonMailBasedResourcesWithPath_class_1
        {
            public $pathArgs;

            public function withPathAttachment()
            {
                $this->pathArgs = func_get_args();
            }
        }

class AttachableTest_testItCanUtiliseExistingApisOnNonMailBasedResourcesWithPath_class_2 implements Attachable
        {
            public function toMailAttachment()
            {
                return Attachment::fromPath('foo.jpg')
                    ->as_('bar')
                    ->withMime('text/css');
            }
        }

class AttachableTest_testItCanUtiliseExistingApisOnNonMailBasedResourcesWithArgs_class_1
        {
            public $pathArgs;

            public function withDataAttachment()
            {
                $this->dataArgs = func_get_args();
            }
        }

class AttachableTest_testItCanUtiliseExistingApisOnNonMailBasedResourcesWithArgs_class_2 implements Attachable
        {
            public function toMailAttachment()
            {
                return Attachment::fromData(function () {
                    return 'expected attachment body';
                }, 'bar')
                    ->withMime('text/css');
            }
        }

class AttachableTest extends TestCase
{
    public function testItCanHaveMacroConstructors()
    {
        Attachment::macro('fromInvoice', function ($name) {
            return Attachment::fromData(function () { return 'pdf content'; }, $name);
        });
        $mailable = new Mailable;

        $mailable->attach(new AttachableTest_testItCanHaveMacroConstructors_class());

        $this->assertSame([
            'data' => 'pdf content',
            'name' => 'bar',
            'options' => [
                'mime' => 'image/jpeg',
            ],
        ], $mailable->rawAttachments[0]);
    }

    public function testItCanUtiliseExistingApisOnNonMailBasedResourcesWithPath()
    {
        Attachment::macro('size', function () {
            return 99;
        });
        $notification = new AttachableTest_testItCanUtiliseExistingApisOnNonMailBasedResourcesWithPath_class_1();
        $attachable = new AttachableTest_testItCanUtiliseExistingApisOnNonMailBasedResourcesWithPath_class_2;

        $attachable->toMailAttachment()->attachWith(
            function ($path, $attachment) use ($notification) {
                return $notification->withPathAttachment($path, $attachment->as, $attachment->mime, $attachment->size());
            },
            function () { return null; }
        );

        $this->assertSame([
            'foo.jpg',
            'bar',
            'text/css',
            99,
        ], $notification->pathArgs);
    }

    public function testItCanUtiliseExistingApisOnNonMailBasedResourcesWithArgs()
    {
        Attachment::macro('size', function () {
            return 99;
        });
        $notification = new AttachableTest_testItCanUtiliseExistingApisOnNonMailBasedResourcesWithArgs_class_1();
        $attachable = new AttachableTest_testItCanUtiliseExistingApisOnNonMailBasedResourcesWithArgs_class_2();

        $attachable->toMailAttachment()->attachWith(
            function () { return null; },
            function ($data, $attachment) use ($notification) {
                return $notification->withDataAttachment($data(), $attachment->as, $attachment->mime, $attachment->size());
            }
        );

        $this->assertSame([
            'expected attachment body',
            'bar',
            'text/css',
            99,
        ], $notification->dataArgs);
    }
}
