<?php

// declare(strict_types=1);

namespace League\Flysystem\Patch;

use League\Flysystem\FileExistsException;

final class UnableToWriteFile extends FileExistsException
{
}
