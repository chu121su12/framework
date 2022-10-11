<?php

// declare(strict_types=1);

namespace League\Flysystem\Patch;

use League\Flysystem\AdapterInterface as FilesystemAdapter;
use League\Flysystem\Config;

class ReadOnlyFilesystemAdapter implements FilesystemAdapter
{
    private $inner;

    public function __construct(/*private */FilesystemAdapter $inner)
    {
        $this->inner = $inner;
    }

    public function fileExists(/*string */$path)/*: bool*/
    {
        $path = backport_type_check('string', $path);

        return $this->inner->fileExists($path);
    }

    public function directoryExists(/*string */$path)/*: bool*/
    {
        $path = backport_type_check('string', $path);

        return $this->inner->directoryExists($path);
    }

    public function write(/*string */$path, /*string */$contents, Config $config)/*: void*/
    {
        $contents = backport_type_check('string', $contents);

        $path = backport_type_check('string', $path);

        // throw UnableToWriteFile::atLocation($path, 'This is a readonly adapter.');
        throw new UnableToWriteFile;
    }

    public function writeStream(/*string */$path, $contents, Config $config)/*: void*/
    {
        $path = backport_type_check('string', $path);

        // throw UnableToWriteFile::atLocation($path, 'This is a readonly adapter.');
        throw new UnableToWriteFile;
    }

    public function read(/*string */$path)/*: string*/
    {
        $path = backport_type_check('string', $path);

        return $this->inner->read($path);
    }

    public function readStream(/*string */$path)
    {
        $path = backport_type_check('string', $path);

        return $this->inner->readStream($path);
    }

    public function delete(/*string */$path)/*: void*/
    {
        $path = backport_type_check('string', $path);

        // throw UnableToDeleteFile::atLocation($path, 'This is a readonly adapter.');
        throw new UnableToDeleteFile;
    }

    public function deleteDirectory(/*string */$path)/*: void*/
    {
        $path = backport_type_check('string', $path);

        // throw UnableToDeleteDirectory::atLocation($path, 'This is a readonly adapter.');
        throw new UnableToDeleteDirectory;
    }

    public function createDirectory(/*string */$path, Config $config)/*: void*/
    {
        $path = backport_type_check('string', $path);

        // throw UnableToCreateDirectory::atLocation($path, 'This is a readonly adapter.');
        throw new  UnableToCreateDirectory;
    }

    public function setVisibility(/*string */$path, /*string */$visibility)/*: void*/
    {
        $visibility = backport_type_check('string', $visibility);

        $path = backport_type_check('string', $path);

        // throw UnableToSetVisibility::atLocation($path, 'This is a readonly adapter.');
        throw new  UnableToSetVisibility;
    }

    public function visibility(/*string */$path)/*: FileAttributes*/
    {
        $path = backport_type_check('string', $path);

        return $this->inner->visibility($path);
    }

    public function mimeType(/*string */$path)/*: FileAttributes*/
    {
        $path = backport_type_check('string', $path);

        return $this->inner->mimeType($path);
    }

    public function lastModified(/*string */$path)/*: FileAttributes*/
    {
        $path = backport_type_check('string', $path);

        return $this->inner->lastModified($path);
    }

    public function fileSize(/*string */$path)/*: FileAttributes*/
    {
        $path = backport_type_check('string', $path);

        return $this->inner->fileSize($path);
    }

    public function listContents(/*string */$path = '', /*bool */$deep = false)/*: iterable*/
    {
        $deep = backport_type_check('bool', $deep);

        $path = backport_type_check('string', $path);

        return $this->inner->listContents($path, $deep);
    }

    public function move(/*string */$source, /*string */$destination, Config $config)/*: void*/
    {
        $destination = backport_type_check('string', $destination);

        $source = backport_type_check('string', $source);

        throw new UnableToMoveFile("Unable to move file from $source to $destination as this is a readonly adapter.");
    }

    public function copy(/*string */$source, /*string */$destination/*, Config $config*/)/*: void*/
    {
        $destination = backport_type_check('string', $destination);

        $source = backport_type_check('string', $source);

        throw new UnableToCopyFile("Unable to copy file from $source to $destination as this is a readonly adapter.");
    }

    public function update($path, $contents, Config $config)
    {
        throw new UnableToSetVisibility;
    }

    public function updateStream($path, $resource, Config $config)
    {
        throw new UnableToSetVisibility;
    }

    public function rename($path, $newpath)
    {
        throw new UnableToMoveFile;
    }

    public function deleteDir($dirname)
    {
        throw new UnableToDeleteDirectory;
    }

    public function createDir($dirname, Config $config)
    {
        throw new UnableToCreateDirectory;
    }

    public function has($path)
    {
        return $this->inner->has($path);
    }

    public function getMetadata($path)
    {
        return $this->inner->getMetadata($path);
    }

    public function getSize($path)
    {
        return $this->inner->getSize($path);
    }

    public function getMimetype($path)
    {
        return $this->inner->getMimetype($path);
    }

    public function getTimestamp($path)
    {
        return $this->inner->getTimestamp($path);
    }

    public function getVisibility($path)
    {
        return $this->inner->getVisibility($path);
    }
}
