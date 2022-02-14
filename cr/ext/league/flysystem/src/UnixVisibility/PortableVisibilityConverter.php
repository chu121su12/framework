<?php

// declare(strict_types=1);

namespace League\Flysystem\UnixVisibility;

use InvalidArgumentException;
use League\Flysystem\FilesystemException;

class InvalidVisibilityProvided extends InvalidArgumentException implements FilesystemException
{
    public static function withVisibility(/*string */$visibility, /*string */$expectedMessage)/*: InvalidVisibilityProvided*/
    {
        $visibility = cast_to_string($visibility);
        $expectedMessage = cast_to_string($expectedMessage);

        $provided = var_export($visibility, true);
        $message = "Invalid visibility provided. Expected {$expectedMessage}, received {$provided}";

        throw new InvalidVisibilityProvided($message);
    }
}

final class Visibility
{
    const PUBLIC_ = 'public';
    const PRIVATE_ = 'private';
}

final class PortableVisibilityGuard
{
    public static function guardAgainstInvalidInput(/*string */$visibility)/*: void*/
    {
        $visibility = cast_to_string($visibility);

        if ($visibility !== Visibility::PUBLIC_ && $visibility !== Visibility::PRIVATE_) {
            $className = Visibility::class;
            throw InvalidVisibilityProvided::withVisibility(
                $visibility,
                "either {$className}::PUBLIC or {$className}::PRIVATE"
            );
        }
    }
}

class PortableVisibilityConverter implements VisibilityConverter
{
    /**
     * @var int
     */
    private $filePublic;

    /**
     * @var int
     */
    private $filePrivate;

    /**
     * @var int
     */
    private $directoryPublic;

    /**
     * @var int
     */
    private $directoryPrivate;

    /**
     * @var string
     */
    private $defaultForDirectories;

    public function __construct(
        /*int */$filePublic = 0644,
        /*int */$filePrivate = 0600,
        /*int */$directoryPublic = 0755,
        /*int */$directoryPrivate = 0700,
        /*string */$defaultForDirectories = Visibility::PRIVATE_
    ) {
        $filePublic = cast_to_int($filePublic);
        $filePrivate = cast_to_int($filePrivate);
        $directoryPublic = cast_to_int($directoryPublic);
        $directoryPrivate = cast_to_int($directoryPrivate);
        $defaultForDirectories = cast_to_string($defaultForDirectories);

        $this->filePublic = $filePublic;
        $this->filePrivate = $filePrivate;
        $this->directoryPublic = $directoryPublic;
        $this->directoryPrivate = $directoryPrivate;
        $this->defaultForDirectories = $defaultForDirectories;
    }

    public function forFile(/*string */$visibility)/*: int*/
    {
        $visibility = cast_to_string($visibility);

        PortableVisibilityGuard::guardAgainstInvalidInput($visibility);

        return $visibility === Visibility::PUBLIC_
            ? $this->filePublic
            : $this->filePrivate;
    }

    public function forDirectory(/*string */$visibility)/*: int*/
    {
        $visibility = cast_to_string($visibility);

        PortableVisibilityGuard::guardAgainstInvalidInput($visibility);

        return $visibility === Visibility::PUBLIC_
            ? $this->directoryPublic
            : $this->directoryPrivate;
    }

    public function inverseForFile(/*int */$visibility)/*: string*/
    {
        $visibility = cast_to_int($visibility);

        if ($visibility === $this->filePublic) {
            return Visibility::PUBLIC_;
        } elseif ($visibility === $this->filePrivate) {
            return Visibility::PRIVATE_;
        }

        return Visibility::PUBLIC_; // default
    }

    public function inverseForDirectory(/*int */$visibility)/*: string*/
    {
        $visibility = cast_to_int($visibility);

        if ($visibility === $this->directoryPublic) {
            return Visibility::PUBLIC_;
        } elseif ($visibility === $this->directoryPrivate) {
            return Visibility::PRIVATE_;
        }

        return Visibility::PUBLIC_; // default
    }

    public function defaultForDirectories()/*: int*/
    {
        return $this->defaultForDirectories === Visibility::PUBLIC_ ? $this->directoryPublic : $this->directoryPrivate;
    }

    /**
     * @param array<mixed>  $permissionMap
     */
    public static function fromArray(array $permissionMap, /*string */$defaultForDirectories = Visibility::PRIVATE_)/*: PortableVisibilityConverter*/
    {
        $defaultForDirectories = cast_to_string($defaultForDirectories);

        return new PortableVisibilityConverter(
            isset($permissionMap['file']) && isset($permissionMap['file']['public']) ? $permissionMap['file']['public'] : 0644,
            isset($permissionMap['file']) && isset($permissionMap['file']['private']) ? $permissionMap['file']['private'] : 0600,
            isset($permissionMap['dir']) && isset($permissionMap['dir']['public']) ? $permissionMap['dir']['public'] : 0755,
            isset($permissionMap['dir']) && isset($permissionMap['dir']['private']) ? $permissionMap['dir']['private'] : 0700,
            $defaultForDirectories
        );
    }
}