<?php

// declare(strict_types=1);

namespace League\Flysystem\Patch;

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
        $filePublic = backport_type_check('int', $filePublic);
        $filePrivate = backport_type_check('int', $filePrivate);
        $directoryPublic = backport_type_check('int', $directoryPublic);
        $directoryPrivate = backport_type_check('int', $directoryPrivate);
        $defaultForDirectories = backport_type_check('string', $defaultForDirectories);

        $this->filePublic = $filePublic;
        $this->filePrivate = $filePrivate;
        $this->directoryPublic = $directoryPublic;
        $this->directoryPrivate = $directoryPrivate;
        $this->defaultForDirectories = $defaultForDirectories;
    }

    public function forFile(/*string */$visibility)/*: int*/
    {
        $visibility = backport_type_check('string', $visibility);

        PortableVisibilityGuard::guardAgainstInvalidInput($visibility);

        return $visibility === Visibility::PUBLIC_
            ? $this->filePublic
            : $this->filePrivate;
    }

    public function forDirectory(/*string */$visibility)/*: int*/
    {
        $visibility = backport_type_check('string', $visibility);

        PortableVisibilityGuard::guardAgainstInvalidInput($visibility);

        return $visibility === Visibility::PUBLIC_
            ? $this->directoryPublic
            : $this->directoryPrivate;
    }

    public function inverseForFile(/*int */$visibility)/*: string*/
    {
        $visibility = backport_type_check('int', $visibility);

        if ($visibility === $this->filePublic) {
            return Visibility::PUBLIC_;
        } elseif ($visibility === $this->filePrivate) {
            return Visibility::PRIVATE_;
        }

        return Visibility::PUBLIC_; // default
    }

    public function inverseForDirectory(/*int */$visibility)/*: string*/
    {
        $visibility = backport_type_check('int', $visibility);

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
        $defaultForDirectories = backport_type_check('string', $defaultForDirectories);

        return new PortableVisibilityConverter(
            isset($permissionMap['file']) && isset($permissionMap['file']['public']) ? $permissionMap['file']['public'] : 0644,
            isset($permissionMap['file']) && isset($permissionMap['file']['private']) ? $permissionMap['file']['private'] : 0600,
            isset($permissionMap['dir']) && isset($permissionMap['dir']['public']) ? $permissionMap['dir']['public'] : 0755,
            isset($permissionMap['dir']) && isset($permissionMap['dir']['private']) ? $permissionMap['dir']['private'] : 0700,
            $defaultForDirectories
        );
    }
}