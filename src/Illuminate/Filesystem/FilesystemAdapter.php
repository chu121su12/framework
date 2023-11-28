<?php

namespace Illuminate\Filesystem;

use Closure;
use CR\LaravelBackport\SymfonyHelper;
use Illuminate\Contracts\Filesystem\Cloud as CloudFilesystemContract;
use Illuminate\Contracts\Filesystem\Filesystem as FilesystemContract;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;
use League\Flysystem\AdapterInterface as FlysystemAdapter;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface as FilesystemOperator;
use League\Flysystem\Patch\LocalFilesystemAdapter as LocalAdapter;
use League\Flysystem\Patch\FtpAdapter;
use League\Flysystem\Patch\PathPrefixer;
use League\Flysystem\Patch\SftpAdapter;
use League\Flysystem\Patch\UnableToCopyFile;
use League\Flysystem\Patch\UnableToCreateDirectory;
use League\Flysystem\Patch\UnableToDeleteDirectory;
use League\Flysystem\Patch\UnableToDeleteFile;
use League\Flysystem\Patch\UnableToMoveFile;
use League\Flysystem\Patch\UnableToProvideChecksum;
use League\Flysystem\Patch\UnableToReadFile;
use League\Flysystem\Patch\UnableToRetrieveMetadata;
use League\Flysystem\Patch\UnableToSetVisibility;
use League\Flysystem\Patch\UnableToWriteFile;
use League\Flysystem\Patch\Visibility;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Testing\Assert as PHPUnit;

// use League\Flysystem\StorageAttributes;

/**
 * #mixin \League\Flysystem\FilesystemOperator
 *
 * @mixin \League\Flysystem\FilesystemInterface
 */
class FilesystemAdapter implements CloudFilesystemContract
{
    use Conditionable;
    use Macroable {
        __call as macroCall;
    }

    /**
     * Get the URL for the file at the given path.
     *
     * @param  \League\Flysystem\AwsS3v3\AwsS3Adapter  $adapter
     * @param  string  $path
     * @return string
     */
    protected function getAwsUrl($adapter, $path)
    {
        // If an explicit base URL has been set on the disk configuration then we will use
        // it as the base URL instead of the default path. This allows the developer to
        // have full control over the base path for this filesystem's generated URLs.
        if (! is_null($url = $this->driver->getConfig()->get('url'))) {
            return $this->concatPathToUrl($url, $adapter->getPathPrefix().$path);
        }

        return $adapter->getClient()->getObjectUrl(
            $adapter->getBucket(), $adapter->getPathPrefix().$path
        );
    }

    /**
     * Get a temporary URL for the file at the given path.
     *
     * @param  \League\Flysystem\AwsS3v3\AwsS3Adapter  $adapter
     * @param  string  $path
     * @param  \DateTimeInterface  $expiration
     * @param  array  $options
     * @return string
     */
    protected function getAwsTemporaryUrl($adapter, $path, $expiration, $options)
    {
        $client = $adapter->getClient();

        $command = $client->getCommand('GetObject', array_merge([
            'Bucket' => $adapter->getBucket(),
            'Key' => $adapter->getPathPrefix().$path,
        ], $options));

        return (string) $client->createPresignedRequest(
            $command, $expiration
        )->getUri();
    }

    /**
     * Filter directory contents by type.
     *
     * @param  array  $contents
     * @param  string  $type
     * @return array
     */
    protected function filterContentsByType($contents, $type)
    {
        return Collection::make($contents)
            ->where('type', $type)
            ->pluck('path')
            ->values()
            ->all();
    }

    /**
     * The Flysystem filesystem implementation.
     *
     * #var \League\Flysystem\FilesystemOperator
     *
     * @var \League\Flysystem\FilesystemInterface
     */
    protected $driver;

    /**
     * The Flysystem adapter implementation.
     *
     * @var \League\Flysystem\FilesystemAdapter
     */
    protected $adapter;

    /**
     * The filesystem configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * The Flysystem PathPrefixer instance.
     *
     * @var \League\Flysystem\PathPrefixer
     */
    protected $prefixer;

    /**
     * The temporary URL builder callback.
     *
     * @var \Closure|null
     */
    protected $temporaryUrlCallback;

    /**
     * Create a new filesystem adapter instance.
     *
     * #param  \League\Flysystem\FilesystemOperator  $driver
     * #param  \League\Flysystem\FilesystemAdapter  $adapter
     * #param  array  $config
     *
     * @param  \League\Flysystem\FilesystemInterface  $driver
     * @param  \League\Flysystem\AdapterInterface  $adapter
     * @param  array  $config
     * @return void
     */
    public function __construct(FilesystemOperator $driver, FlysystemAdapter $adapter, array $config = [])
    {
        $this->driver = $driver;
        $this->adapter = $adapter;
        $this->config = $config;
        $separator = isset($config['directory_separator']) ? $config['directory_separator'] : DIRECTORY_SEPARATOR;

        $this->prefixer = new PathPrefixer(isset($config['root']) ? $config['root'] : '', $separator);

        if (isset($config['prefix'])) {
            $this->prefixer = new PathPrefixer($this->prefixer->prefixPath($config['prefix']), $separator);
        }
    }

    /**
     * Assert that the given file or directory exists.
     *
     * @param  string|array  $path
     * @param  string|null  $content
     * @return $this
     */
    public function assertExists($path, $content = null)
    {
        clearstatcache();

        $paths = Arr::wrap($path);

        foreach ($paths as $path) {
            PHPUnit::assertTrue(
                $this->exists($path), "Unable to find a file or directory at path [{$path}]."
            );

            if (! is_null($content)) {
                $actual = $this->get($path);

                PHPUnit::assertSame(
                    $content,
                    $actual,
                    "File or directory [{$path}] was found, but content [{$actual}] does not match [{$content}]."
                );
            }
        }

        return $this;
    }

    /**
     * Assert that the given file or directory does not exist.
     *
     * @param  string|array  $path
     * @return $this
     */
    public function assertMissing($path)
    {
        clearstatcache();

        $paths = Arr::wrap($path);

        foreach ($paths as $path) {
            PHPUnit::assertFalse(
                $this->exists($path), "Found unexpected file or directory at path [{$path}]."
            );
        }

        return $this;
    }

    /**
     * Assert that the given directory is empty.
     *
     * @param  string  $path
     * @return $this
     */
    public function assertDirectoryEmpty($path)
    {
        PHPUnit::assertEmpty(
            $this->allFiles($path), "Directory [{$path}] is not empty."
        );

        return $this;
    }

    /**
     * Determine if a file or directory exists.
     *
     * @param  string  $path
     * @return bool
     */
    public function exists($path)
    {
        // return $this->driver->fileExists($path);
        return $this->driver->has($path);
    }

    /**
     * Determine if a file or directory is missing.
     *
     * @param  string  $path
     * @return bool
     */
    public function missing($path)
    {
        return ! $this->exists($path);
    }

    /**
     * Determine if a file exists.
     *
     * @param  string  $path
     * @return bool
     */
    public function fileExists($path)
    {
        // return $this->driver->fileExists($path);
        return $this->exists($path);
    }

    /**
     * Determine if a file is missing.
     *
     * @param  string  $path
     * @return bool
     */
    public function fileMissing($path)
    {
        return ! $this->fileExists($path);
    }

    /**
     * Determine if a directory exists.
     *
     * @param  string  $path
     * @return bool
     */
    public function directoryExists($path)
    {
        // return $this->driver->directoryExists($path);
        return $this->exists($path);
    }

    /**
     * Determine if a directory is missing.
     *
     * @param  string  $path
     * @return bool
     */
    public function directoryMissing($path)
    {
        return ! $this->directoryExists($path);
    }

    /**
     * Get the full path for the file at the given "short" path.
     *
     * @param  string  $path
     * @return string
     */
    public function path($path)
    {
        return $this->prefixer->prefixPath($path);
    }

    /**
     * Get the contents of a file.
     *
     * @param  string  $path
     * @return string|null
     */
    public function get($path)
    {
        try {
            return $this->driver->read($path);
        } catch (FileNotFoundException $e) {
            $e = new UnableToReadFile($e->getMessage(), $e->getCode(), $e);
        } catch (UnableToReadFile $e) {
        }

        if (isset($e)) {
            throw_if($this->throwsExceptions(), $e);
        }
    }

    /**
     * Get the contents of a file as decoded JSON.
     *
     * @param  string  $path
     * @param  int  $flags
     * @return array|null
     */
    public function json($path, $flags = 0)
    {
        $content = $this->get($path);

        return is_null($content) ? null : backport_json_decode($content, true, 512, $flags);
    }

    /**
     * Create a streamed response for a given file.
     *
     * @param  string  $path
     * @param  string|null  $name
     * @param  array  $headers
     * @param  string|null  $disposition
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function response($path, $name = null, array $headers = [], $disposition = 'inline')
    {
        $response = SymfonyHelper::makeStreamedResponse();

        $filename = isset($name) ? $name : basename($path);

        if (!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = $this->mimeType($path);
        }
        if (!isset($headers['Content-Length'])) {
            $headers['Content-Length'] = $this->size($path);
        }

        if (! array_key_exists('Content-Disposition', $headers)) {
            $filename = isset($name) ? $name : basename($path);

            $disposition = $response->headers->makeDisposition(
                $disposition, $filename, $this->fallbackName($filename)
            );

            $headers['Content-Disposition'] = $disposition;
        }

        $response->headers->replace($headers);

        $response->setCallback(function () use ($path) {
            $stream = $this->readStream($path);
            fpassthru($stream);
            fclose($stream);
        });

        return $response;
    }

    /**
     * Create a streamed download response for a given file.
     *
     * @param  string  $path
     * @param  string|null  $name
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download($path, $name = null, array $headers = [])
    {
        return $this->response($path, $name, $headers, 'attachment');
    }

    /**
     * Convert the string to ASCII characters that are equivalent to the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function fallbackName($name)
    {
        return str_replace('%', '', Str::ascii($name));
    }

    /**
     * Write the contents of a file.
     *
     * @param  string  $path
     * @param  \Psr\Http\Message\StreamInterface|\Illuminate\Http\File|\Illuminate\Http\UploadedFile|string|resource  $contents
     * @param  mixed  $options
     * @return string|bool
     */
    public function put($path, $contents, $options = [])
    {
        $options = is_string($options)
                     ? ['visibility' => $options]
                     : (array) $options;

        // If the given contents is actually a file or uploaded file instance than we will
        // automatically store the file using a stream. This provides a convenient path
        // for the developer to store streams without managing them manually in code.
        if ($contents instanceof File ||
            $contents instanceof UploadedFile) {
            return $this->putFile($path, $contents, $options);
        }

        $_restore = backport_convert_error_to_error_exception();

        try {
            if ($contents instanceof StreamInterface) {
                // $this->driver->writeStream($path, $contents->detach(), $options);

                // return true;

                return $this->driver->putStream($path, $contents->detach(), $options);
            }

            is_resource($contents)
                ? $this->driver->writeStream($path, $contents, $options)
                : $this->driver->write($path, $contents, $options);
        } catch (UnableToWriteFile $e) {
        } catch (UnableToSetVisibility $e) {
        } catch (\ErrorException $e) {
            $e = new UnableToWriteFile($e->getMessage(), $e->getCode(), $e);
        } finally {
            $_restore();
        }

        if (isset($e)) {
            throw_if($this->throwsExceptions(), $e);

            return false;
        }

        return true;
    }

    /**
     * Store the uploaded file on the disk.
     *
     * @param  \Illuminate\Http\File|\Illuminate\Http\UploadedFile|string  $path
     * @param  \Illuminate\Http\File|\Illuminate\Http\UploadedFile|string|array|null  $file
     * @param  mixed  $options
     * @return string|false
     */
    public function putFile($path, $file = null, $options = [])
    {
        if (is_null($file) || is_array($file)) {
            list($path, $file, $options) = ['', $path, isset($file) ? $file : []];
        }

        $file = is_string($file) ? new File($file) : $file;

        return $this->putFileAs($path, $file, $file->hashName(), $options);
    }

    /**
     * Store the uploaded file on the disk with a given name.
     *
     * @param  \Illuminate\Http\File|\Illuminate\Http\UploadedFile|string  $path
     * @param  \Illuminate\Http\File|\Illuminate\Http\UploadedFile|string|array|null  $file
     * @param  string|array|null  $name
     * @param  mixed  $options
     * @return string|false
     */
    public function putFileAs($path, $file, $name = null, $options = [])
    {
        if (is_null($name) || is_array($name)) {
            list($path, $file, $name, $options) = ['', $path, $file, isset($name) ? $name : []];
        }

        $stream = fopen(is_string($file) ? $file : $file->getRealPath(), 'r');

        // Next, we will format the path of the file and store the file using a stream since
        // they provide better performance than alternatives. Once we write the file this
        // stream will get closed automatically by us so the developer doesn't have to.
        $result = $this->put(
            $path = trim($path.'/'.$name, '/'), $stream, $options
        );

        if (is_resource($stream)) {
            fclose($stream);
        }

        return $result ? $path : false;
    }

    /**
     * Get the visibility for the given path.
     *
     * @param  string  $path
     * @return string
     */
    public function getVisibility($path)
    {
        if ($this->driver->getVisibility($path) == Visibility::PUBLIC_) {
            return FilesystemContract::VISIBILITY_PUBLIC;
        }

        return FilesystemContract::VISIBILITY_PRIVATE;
    }

    /**
     * Set the visibility for the given path.
     *
     * @param  string  $path
     * @param  string  $visibility
     * @return bool
     */
    public function setVisibility($path, $visibility)
    {
        try {
            return $this->driver->setVisibility($path, $this->parseVisibility($visibility));
        } catch (UnableToSetVisibility $e) {
            throw_if($this->throwsExceptions(), $e);

            return false;
        }
    }

    /**
     * Prepend to a file.
     *
     * @param  string  $path
     * @param  string  $data
     * @param  string  $separator
     * @return bool
     */
    public function prepend($path, $data, $separator = PHP_EOL)
    {
        if ($this->fileExists($path)) {
            return $this->put($path, $data.$separator.$this->get($path));
        }

        return $this->put($path, $data);
    }

    /**
     * Append to a file.
     *
     * @param  string  $path
     * @param  string  $data
     * @param  string  $separator
     * @return bool
     */
    public function append($path, $data, $separator = PHP_EOL)
    {
        if ($this->fileExists($path)) {
            return $this->put($path, $this->get($path).$separator.$data);
        }

        return $this->put($path, $data);
    }

    /**
     * Delete the file at a given path.
     *
     * @param  string|array  $paths
     * @return bool
     */
    public function delete($paths)
    {
        $paths = is_array($paths) ? $paths : func_get_args();

        $success = true;

        foreach ($paths as $path) {
            try {
                if (! $this->driver->delete($path)) {
                    $success = false;
                }
            } catch (FileNotFoundException $e) {
                $success = true;
            } catch (UnableToDeleteFile $e) {
                throw_if($this->throwsExceptions(), $e);

                $success = false;
            }
        }

        return $success;
    }

    /**
     * Copy a file to a new location.
     *
     * @param  string  $from
     * @param  string  $to
     * @return bool
     */
    public function copy($from, $to)
    {
        try {
            return $this->driver->copy($from, $to);
        } catch (UnableToCopyFile $e) {
            throw_if($this->throwsExceptions(), $e);

            return false;
        }
    }

    /**
     * Move a file to a new location.
     *
     * @param  string  $from
     * @param  string  $to
     * @return bool
     */
    public function move($from, $to)
    {
        try {
            // $this->driver->move($from, $to);

            return $this->driver->rename($from, $to);
        } catch (UnableToMoveFile $e) {
            throw_if($this->throwsExceptions(), $e);

            return false;
        }
    }

    /**
     * Get the file size of a given file.
     *
     * @param  string  $path
     * @return int
     */
    public function size($path)
    {
        // return $this->driver->fileSize($path);
        return $this->driver->getSize($path);
    }

    /**
     * Get the checksum for a file.
     *
     * @return string|false
     *
     * @throws UnableToProvideChecksum
     */
    public function checksum(/*string */$path, array $options = [])
    {
        $path = backport_type_check('string', $path);

        try {
            return $this->driver->checksum($path, $options);
        } catch (UnableToProvideChecksum $e) {
            throw_if($this->throwsExceptions(), $e);

            return false;
        }
    }

    /**
     * Get the mime-type of a given file.
     *
     * @param  string  $path
     * @return string|false
     */
    public function mimeType($path)
    {
        try {
            // return $this->driver->mimeType($path);
            $result = $this->driver->getMimetype($path);

            if ($result === null) {
                throw new UnableToRetrieveMetadata('Unable to retrieve metadata');
            }

            return $result;
        } catch (UnableToRetrieveMetadata $e) {
            throw_if($this->throwsExceptions(), $e);
        }

        return false;
    }

    /**
     * Get the file's last modification time.
     *
     * @param  string  $path
     * @return int
     */
    public function lastModified($path)
    {
        // return $this->driver->lastModified($path);
        return $this->driver->getTimestamp($path);
    }

    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
        try {
            return $this->driver->readStream($path);
        } catch (FileNotFoundException $e) {
            $e = new UnableToReadFile($e->getMessage(), $e->getCode(), $e);
        } catch (UnableToReadFile $e) {
        }

        if (isset($e)) {
            throw_if($this->throwsExceptions(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream($path, $resource, array $options = [])
    {
        if (! is_resource($resource)) {
            throw new \InvalidArgumentException;
        }

        try {
            $this->driver->writeStream($path, $resource, $options);
        } catch (FileExistsException $e1) {
        } catch (UnableToWriteFile $e1) {
        } catch (UnableToSetVisibility $e1) {
        }

        if (isset($e1)) {
            try {
                $this->delete($path);
                $this->driver->writeStream($path, $resource, $options);
            } catch (FileExistsException $e2) {
            } catch (UnableToWriteFile $e2) {
            } catch (UnableToSetVisibility $e2) {
            }

            if (isset($e2)) {
                throw_if($this->throwsExceptions(), $e2);
            }
            if (isset($e1)) {
                throw_if($this->throwsExceptions(), $e1);
            }

            return false;
        }

        return true;
    }

    /**
     * Get the URL for the file at the given path.
     *
     * @param  string  $path
     * @return string
     *
     * @throws \RuntimeException
     */
    public function url($path)
    {
        if (isset($this->config['prefix'])) {
            $path = $this->concatPathToUrl($this->config['prefix'], $path);
        }

        $adapter = $this->adapter;

        if (method_exists($adapter, 'getUrl')) {
            return $adapter->getUrl($path);
        } elseif (method_exists($this->driver, 'getUrl')) {
            return $this->driver->getUrl($path);
        } elseif ($adapter instanceof AwsS3Adapter) {
            return $this->getAwsUrl($adapter, $path);
        } elseif ($adapter instanceof FtpAdapter || $adapter instanceof SftpAdapter) {
            return $this->getFtpUrl($path);
        } elseif ($adapter instanceof LocalAdapter) {
            return $this->getLocalUrl($path);
        } else {
            throw new RuntimeException('This driver does not support retrieving URLs.');
        }
    }

    /**
     * Get the URL for the file at the given path.
     *
     * @param  string  $path
     * @return string
     */
    protected function getFtpUrl($path)
    {
        return isset($this->config['url'])
                ? $this->concatPathToUrl($this->config['url'], $path)
                : $path;
    }

    /**
     * Get the URL for the file at the given path.
     *
     * @param  string  $path
     * @return string
     */
    protected function getLocalUrl($path)
    {
        // If an explicit base URL has been set on the disk configuration then we will use
        // it as the base URL instead of the default path. This allows the developer to
        // have full control over the base path for this filesystem's generated URLs.
        if (isset($this->config['url'])) {
            return $this->concatPathToUrl($this->config['url'], $path);
        }

        $path = '/storage/'.$path;

        // If the path contains "storage/public", it probably means the developer is using
        // the default disk to generate the path instead of the "public" disk like they
        // are really supposed to use. We will remove the public from this path here.
        if (str_contains($path, '/storage/public/')) {
            return Str::replaceFirst('/public/', '/', $path);
        }

        return $path;
    }

    /**
     * Determine if temporary URLs can be generated.
     *
     * @return bool
     */
    public function providesTemporaryUrls()
    {
        return method_exists($this->adapter, 'getTemporaryUrl') || isset($this->temporaryUrlCallback);
    }

    /**
     * Get a temporary URL for the file at the given path.
     *
     * @param  string  $path
     * @param  \DateTimeInterface  $expiration
     * @param  array  $options
     * @return string
     *
     * @throws \RuntimeException
     */
    public function temporaryUrl($path, $expiration, array $options = [])
    {
        if ($this->adapter instanceof AwsS3Adapter) {
            return $this->getAwsTemporaryUrl($this->adapter, $path, $expiration, $options);
        }

        if (method_exists($this->adapter, 'getTemporaryUrl')) {
            return $this->adapter->getTemporaryUrl($path, $expiration, $options);
        }

        if ($this->temporaryUrlCallback) {
            $callback = $this->temporaryUrlCallback->bindTo($this, static::class);
            return $callback(
                $path, $expiration, $options
            );
        }

        throw new RuntimeException('This driver does not support creating temporary URLs.');
    }

    /**
     * Get a temporary upload URL for the file at the given path.
     *
     * @param  string  $path
     * @param  \DateTimeInterface  $expiration
     * @param  array  $options
     * @return array
     *
     * @throws \RuntimeException
     */
    public function temporaryUploadUrl($path, $expiration, array $options = [])
    {
        if (method_exists($this->adapter, 'temporaryUploadUrl')) {
            return $this->adapter->temporaryUploadUrl($path, $expiration, $options);
        }

        throw new RuntimeException('This driver does not support creating temporary upload URLs.');
    }

    /**
     * Concatenate a path to a URL.
     *
     * @param  string  $url
     * @param  string  $path
     * @return string
     */
    protected function concatPathToUrl($url, $path)
    {
        return rtrim($url, '/').'/'.ltrim($path, '/');
    }

    /**
     * Replace the scheme, host and port of the given UriInterface with values from the given URL.
     *
     * @param  \Psr\Http\Message\UriInterface  $uri
     * @param  string  $url
     * @return \Psr\Http\Message\UriInterface
     */
    protected function replaceBaseUrl($uri, $url)
    {
        $parsed = parse_url($url);

        return $uri
            ->withScheme($parsed['scheme'])
            ->withHost($parsed['host'])
            ->withPort(isset($parsed['port']) ? $parsed['port'] : null);
    }

    /**
     * Get an array of all files in a directory.
     *
     * @param  string|null  $directory
     * @param  bool  $recursive
     * @return array
     */
    public function files($directory = null, $recursive = false)
    {
        $contents = $this->driver->listContents(isset($directory) ? $directory : '', $recursive);

        usort($contents, function ($a, $b) {
            return strcmp($a['path'], $b['path']);
        });

        return $this->filterContentsByType($contents, 'file');

        // return $this->driver->listContents(isset($directory) ? $directory : '', $recursive)
        //     ->filter(function (StorageAttributes $attributes) {
        //         return $attributes->isFile();
        //     })
        //     ->sortByPath()
        //     ->map(function (StorageAttributes $attributes) {
        //         return $attributes->path();
        //     })
        //     ->toArray();
    }

    /**
     * Get all of the files from the given directory (recursive).
     *
     * @param  string|null  $directory
     * @return array
     */
    public function allFiles($directory = null)
    {
        return $this->files($directory, true);
    }

    /**
     * Get all of the directories within a given directory.
     *
     * @param  string|null  $directory
     * @param  bool  $recursive
     * @return array
     */
    public function directories($directory = null, $recursive = false)
    {
        $contents = $this->driver->listContents(isset($directory) ? $directory : '', $recursive);

        return $this->filterContentsByType($contents, 'dir');

        // return $this->driver->listContents(isset($directory) ? $directory : '', $recursive)
        //     ->filter(function (StorageAttributes $attributes) {
        //         return $attributes->isDir();
        //     })
        //     ->map(function (StorageAttributes $attributes) {
        //         return $attributes->path();
        //     })
        //     ->toArray();
    }

    /**
     * Get all the directories within a given directory (recursive).
     *
     * @param  string|null  $directory
     * @return array
     */
    public function allDirectories($directory = null)
    {
        return $this->directories($directory, true);
    }

    /**
     * Create a directory.
     *
     * @param  string  $path
     * @return bool
     */
    public function makeDirectory($path)
    {
        try {
            // $this->driver->createDirectory($path);

            return $this->driver->createDir($path);
        } catch (UnableToCreateDirectory $e) {
        } catch (UnableToSetVisibility $e) {
        }

        if (isset($e)) {
            throw_if($this->throwsExceptions(), $e);

            return false;
        }

        return true;
    }

    /**
     * Recursively delete a directory.
     *
     * @param  string  $directory
     * @return bool
     */
    public function deleteDirectory($directory)
    {
        try {
            // $this->driver->deleteDirectory($directory);

            return $this->driver->deleteDir($directory);
        } catch (UnableToDeleteDirectory $e) {
            throw_if($this->throwsExceptions(), $e);

            return false;
        }
    }

    /**
     * Get the Flysystem driver.
     *
     * #return \League\Flysystem\FilesystemOperator
     *
     * @return \League\Flysystem\FilesystemInterface
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Get the Flysystem adapter.
     *
     * #return \League\Flysystem\FilesystemAdapter
     *
     * @return \League\Flysystem\AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Get the configuration values.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Parse the given visibility value.
     *
     * @param  string|null  $visibility
     * @return string|null
     *
     * @throws \InvalidArgumentException
     */
    protected function parseVisibility($visibility)
    {
        if (is_null($visibility)) {
            return;
        }

        switch ($visibility) {
            case FilesystemContract::VISIBILITY_PUBLIC: return Visibility::PUBLIC_;

            case FilesystemContract::VISIBILITY_PRIVATE: return Visibility::PRIVATE_;
        }

        throw new InvalidArgumentException("Unknown visibility: {$visibility}.");
    }

    /**
     * Define a custom temporary URL builder callback.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public function buildTemporaryUrlsUsing(Closure $callback)
    {
        $this->temporaryUrlCallback = $callback;
    }

    /**
     * Determine if Flysystem exceptions should be thrown.
     *
     * @return bool
     */
    protected function throwsExceptions()/*: bool*/
    {
        return (bool) (isset($this->config['throw']) ? $this->config['throw'] : false);
    }

    /**
     * Pass dynamic methods call onto Flysystem.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        return $this->driver->{$method}(...$parameters);
    }
}
