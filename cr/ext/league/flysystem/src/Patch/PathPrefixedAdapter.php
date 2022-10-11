<?php

namespace League\Flysystem\Patch;

use League\Flysystem\AdapterInterface as FilesystemAdapter;
use League\Flysystem\Config;
use Throwable;

class PathPrefixedAdapter implements FilesystemAdapter
{
    protected /*FilesystemAdapter */$adapter;
    private /*PathPrefixer */$prefix;

    public function __construct(FilesystemAdapter $adapter, /*string */$prefix)
    {
        $prefix = backport_type_check('string', $prefix);

        if ($prefix === '') {
            throw new \InvalidArgumentException('The prefix must not be empty.');
        }

        $this->adapter = $adapter;
        $this->prefix = new PathPrefixer($prefix);
    }

    public function read(/*string */$location)/*: string*/
    {
        $location = backport_type_check('string', $location);

        try {
            return $this->adapter->read($this->prefix->prefixPath($location));
        } catch (\Exception $previous) {
        } catch (\ErrorException $previous) {
        } catch (Throwable $previous) {
            // throw UnableToReadFile::fromLocation($location, $previous->getMessage(), $previous);
        }

        if (isset($previous)) {
            throw new UnableToReadFile($previous->getMessage(), $previous->getCode(), $previous);
        }
    }

    public function readStream(/*string */$location)
    {
        $location = backport_type_check('string', $location);

        try {
            return $this->adapter->readStream($this->prefix->prefixPath($location));
        } catch (\Exception $previous) {
        } catch (\ErrorException $previous) {
        } catch (Throwable $previous) {
            // throw UnableToReadFile::fromLocation($location, $previous->getMessage(), $previous);
        }

        if (isset($previous)) {
            throw new UnableToReadFile($previous->getMessage(), $previous->getCode(), $previous);
        }
    }

    public function listContents(/*string */$location = '', /*bool */$deep = false)/*: Generator*/
    {
        $location = backport_type_check('string', $location);

        $deep = backport_type_check('bool', $deep);

        foreach ($this->adapter->listContents($this->prefix->prefixPath($location), $deep) as $attributes) {
            yield $attributes->withPath($this->prefix->stripPrefix($attributes->path()));
        }
    }

    public function fileExists(/*string */$location)/*: bool*/
    {
        $location = backport_type_check('string', $location);

        try {
            return $this->adapter->fileExists($this->prefix->prefixPath($location));
        } catch (\Exception $previous) {
        } catch (\ErrorException $previous) {
        } catch (Throwable $previous) {
            // throw UnableToCheckFileExistence::forLocation($location, $previous);
        }

        if (isset($previous)) {
            throw new UnableToCheckFileExistence($previous->getMessage(), $previous->getCode(), $previous);
        }
    }

    public function directoryExists(/*string */$location)/*: bool*/
    {
        $location = backport_type_check('string', $location);

        try {
            return $this->adapter->directoryExists($this->prefix->prefixPath($location));
        } catch (\Exception $previous) {
        } catch (\ErrorException $previous) {
        } catch (Throwable $previous) {
            // throw UnableToCheckDirectoryExistence::forLocation($location, $previous);
        }

        if (isset($previous)) {
            throw new UnableToCheckDirectoryExistence($previous->getMessage(), $previous->getCode(), $previous);
        }
    }

    public function lastModified(/*string */$path)/*: FileAttributes*/
    {
        $path = backport_type_check('string', $path);

        try {
            return $this->adapter->lastModified($this->prefix->prefixPath($path));
        } catch (\Exception $previous) {
        } catch (\ErrorException $previous) {
        } catch (Throwable $previous) {
            // throw UnableToRetrieveMetadata::lastModified($path, $previous->getMessage(), $previous);
        }

        if (isset($previous)) {
            throw new UnableToRetrieveMetadata($previous->getMessage(), $previous->getCode(), $previous);
        }
    }

    public function fileSize(/*string */$path)/*: FileAttributes*/
    {
        $path = backport_type_check('string', $path);

        try {
            return $this->adapter->fileSize($this->prefix->prefixPath($path));
        } catch (\Exception $previous) {
        } catch (\ErrorException $previous) {
        } catch (Throwable $previous) {
            // throw UnableToRetrieveMetadata::fileSize($path, $previous->getMessage(), $previous);
        }

        if (isset($previous)) {
            throw new UnableToRetrieveMetadata($previous->getMessage(), $previous->getCode(), $previous);
        }
    }

    public function mimeType(/*string */$path)/*: FileAttributes*/
    {
        $path = backport_type_check('string', $path);

        try {
            return $this->adapter->mimeType($this->prefix->prefixPath($path));
        } catch (\Exception $previous) {
        } catch (\ErrorException $previous) {
        } catch (Throwable $previous) {
            // throw UnableToRetrieveMetadata::mimeType($path, $previous->getMessage(), $previous);
        }

        if (isset($previous)) {
            throw new UnableToRetrieveMetadata($previous->getMessage(), $previous->getCode(), $previous);
        }
    }

    public function visibility(/*string */$path)/*: FileAttributes*/
    {
        $path = backport_type_check('string', $path);

        try {
            return $this->adapter->visibility($this->prefix->prefixPath($path));
        } catch (\Exception $previous) {
        } catch (\ErrorException $previous) {
        } catch (Throwable $previous) {
            // throw UnableToRetrieveMetadata::visibility($path, $previous->getMessage(), $previous);
        }

        if (isset($previous)) {
            throw new UnableToRetrieveMetadata($previous->getMessage(), $previous->getCode(), $previous);
        }
    }

    public function write(/*string */$location, /*string */$contents, Config $config)/*: void*/
    {
        $location = backport_type_check('string', $location);

        $contents = backport_type_check('string', $contents);

        try {
            $this->adapter->write($this->prefix->prefixPath($location), $contents, $config);
        } catch (\Exception $previous) {
        } catch (\ErrorException $previous) {
        } catch (Throwable $previous) {
            // throw UnableToWriteFile::atLocation($location, $previous->getMessage(), $previous);
        }

        if (isset($previous)) {
            throw new UnableToWriteFile($previous->getMessage(), $previous->getCode(), $previous);
        }
    }

    public function writeStream(/*string */$location, $contents, Config $config)/*: void*/
    {
        $location = backport_type_check('string', $location);

        try {
            $this->adapter->writeStream($this->prefix->prefixPath($location), $contents, $config);
        } catch (\Exception $previous) {
        } catch (\ErrorException $previous) {
        } catch (Throwable $previous) {
            // throw UnableToWriteFile::atLocation($location, $previous->getMessage(), $previous);
        }

        if (isset($previous)) {
            throw new UnableToWriteFile($previous->getMessage(), $previous->getCode(), $previous);
        }
    }

    public function setVisibility(/*string */$path, /*string */$visibility)/*: void*/
    {
        $path = backport_type_check('string', $path);

        $visibility = backport_type_check('string', $visibility);

        try {
            $this->adapter->setVisibility($this->prefix->prefixPath($path), $visibility);
        } catch (\Exception $previous) {
        } catch (\ErrorException $previous) {
        } catch (Throwable $previous) {
            // throw UnableToSetVisibility::atLocation($path, $previous->getMessage(), $previous);
        }

        if (isset($previous)) {
            throw new UnableToSetVisibility($previous->getMessage(), $previous->getCode(), $previous);
        }
    }

    public function delete(/*string */$location)/*: void*/
    {
        $location = backport_type_check('string', $location);

        try {
            $this->adapter->delete($this->prefix->prefixPath($location));
        } catch (\Exception $previous) {
        } catch (\ErrorException $previous) {
        } catch (Throwable $previous) {
            // throw UnableToDeleteFile::atLocation($location, $previous->getMessage(), $previous);
        }

        if (isset($previous)) {
            throw new UnableToDeleteFile($previous->getMessage(), $previous->getCode(), $previous);
        }
    }

    public function deleteDirectory(/*string */$location)/*: void*/
    {
        $location = backport_type_check('string', $location);

        try {
            $this->adapter->deleteDirectory($this->prefix->prefixPath($location));
        } catch (\Exception $previous) {
        } catch (\ErrorException $previous) {
        } catch (Throwable $previous) {
            // throw UnableToDeleteDirectory::atLocation($location, $previous->getMessage(), $previous);
        }

        if (isset($previous)) {
            throw new UnableToDeleteDirectory($previous->getMessage(), $previous->getCode(), $previous);
        }
    }

    public function createDirectory(/*string */$location, Config $config)/*: void*/
    {
        $location = backport_type_check('string', $location);

        try {
            $this->adapter->createDirectory($this->prefix->prefixPath($location), $config);
        } catch (\Exception $previous) {
        } catch (\ErrorException $previous) {
        } catch (Throwable $previous) {
            // throw UnableToCreateDirectory::atLocation($location, $previous->getMessage(), $previous);
        }

        if (isset($previous)) {
            throw new UnableToCreateDirectory($previous->getMessage(), $previous->getCode(), $previous);
        }
    }

    public function move(/*string */$source, /*string */$destination, Config $config)/*: void*/
    {
        $source = backport_type_check('string', $source);

        $destination = backport_type_check('string', $destination);

        try {
            $this->adapter->move($this->prefix->prefixPath($source), $this->prefix->prefixPath($destination), $config);
        } catch (\Exception $previous) {
        } catch (\ErrorException $previous) {
        } catch (Throwable $previous) {
            // throw UnableToMoveFile::fromLocationTo($source, $destination, $previous);
        }

        if (isset($previous)) {
            throw new UnableToMoveFile($previous->getMessage(), $previous->getCode(), $previous);
        }
    }

    public function copy(/*string */$source, /*string */$destination/*, Config $config*/)/*: void*/
    {
        $source = backport_type_check('string', $source);

        $destination = backport_type_check('string', $destination);

        try {
            $this->adapter->copy($this->prefix->prefixPath($source), $this->prefix->prefixPath($destination), $config);
        } catch (\Exception $previous) {
        } catch (\ErrorException $previous) {
        } catch (Throwable $previous) {
            // throw UnableToCopyFile::fromLocationTo($source, $destination, $previous);
        }

        if (isset($previous)) {
            throw new UnableToCopyFile($previous->getMessage(), $previous->getCode(), $previous);
        }
    }

    public function update($path, $contents, Config $config)
    {
        return $this->adapter->update($this->prefix->prefixPath($path), $contents, $config);
    }

    public function updateStream($path, $resource, Config $config)
    {
        return $this->adapter->updateStream($this->prefix->prefixPath($path), $resource, $config);
    }

    public function rename($path, $newpath)
    {
        return $this->adapter->rename($this->prefix->prefixPath($path), $this->prefix->prefixPath($newpath));
    }

    public function deleteDir($dirname)
    {
        return $this->adapter->deleteDir($this->prefix->prefixPath($dirname));
    }

    public function createDir($dirname, Config $config)
    {
        return $this->adapter->createDir($this->prefix->prefixPath($dirname), $config);
    }

    public function has($path)
    {
        return $this->adapter->has($this->prefix->prefixPath($path));
    }

    public function getMetadata($path)
    {
        return $this->adapter->getMetadata($this->prefix->prefixPath($path));
    }

    public function getSize($path)
    {
        return $this->adapter->getSize($this->prefix->prefixPath($path));
    }

    public function getMimetype($path)
    {
        return $this->adapter->getMimetype($this->prefix->prefixPath($path));
    }

    public function getTimestamp($path)
    {
        return $this->adapter->getTimestamp($this->prefix->prefixPath($path));
    }

    public function getVisibility($path)
    {
        return $this->adapter->getVisibility($this->prefix->prefixPath($path));
    }
}
