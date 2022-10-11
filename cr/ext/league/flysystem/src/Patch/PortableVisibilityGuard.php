<?php

namespace League\Flysystem\Patch;

final class PortableVisibilityGuard
{
    public static function guardAgainstInvalidInput(/*string */$visibility)/*: void*/
    {
        $visibility = backport_type_check('string', $visibility);

        if ($visibility !== Visibility::PUBLIC_ && $visibility !== Visibility::PRIVATE_) {
            $className = Visibility::class;
            throw InvalidVisibilityProvided::withVisibility(
                $visibility,
                "either {$className}::PUBLIC or {$className}::PRIVATE"
            );
        }
    }
}
