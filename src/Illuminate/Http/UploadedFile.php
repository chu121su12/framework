<?php

namespace Illuminate\Http;

use Illuminate\Container\Container;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Testing\FileFactory;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Macroable;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;

class UploadedFile extends SymfonyUploadedFile
{
    use FileHelpers, Macroable;

    /**
     * Accepts the information of the uploaded file as provided by the PHP global $_FILES.
     *
     * The file object is only created when the uploaded file is valid (i.e. when the
     * isValid() method returns true). Otherwise the only methods that could be called
     * on an UploadedFile instance are:
     *
     *   * getClientOriginalName,
     *   * getClientMimeType,
     *   * isValid,
     *   * getError.
     *
     * Calling any other method on an non-valid instance will cause an unpredictable result.
     *
     * @param string      $path         The full temporary path to the file
     * @param string      $originalName The original file name of the uploaded file
     * @param string|null $mimeType     The type of the file as provided by PHP; null defaults to application/octet-stream
     * @param int|null    $error        The error constant of the upload (one of PHP's UPLOAD_ERR_XXX constants); null defaults to UPLOAD_ERR_OK
     * @param bool        $test         Whether the test mode is active
     *                                  Local files are used in test mode hence the code should not enforce HTTP uploads
     *
     * @throws FileException         If file_uploads is disabled
     * @throws FileNotFoundException If the file does not exist
     */
    public function __construct($path, $originalName, $mimeType = null, $error = null, $test = false, $unused = null)
    {
        parent::__construct($path, $originalName, $mimeType, null, $error, $test);

        // updated constructor signature:
        // parent::__construct($path, $originalName, $mimeType, $error, $test);
    }

    /**
     * Begin creating a new file fake.
     *
     * @return \Illuminate\Http\Testing\FileFactory
     */
    public static function fake()
    {
        return new FileFactory;
    }

    /**
     * Store the uploaded file on a filesystem disk.
     *
     * @param  string  $path
     * @param  array|string  $options
     * @return string|false
     */
    public function store($path, $options = [])
    {
        return $this->storeAs($path, $this->hashName(), $this->parseOptions($options));
    }

    /**
     * Store the uploaded file on a filesystem disk with public visibility.
     *
     * @param  string  $path
     * @param  array|string  $options
     * @return string|false
     */
    public function storePublicly($path, $options = [])
    {
        $options = $this->parseOptions($options);

        $options['visibility'] = 'public';

        return $this->storeAs($path, $this->hashName(), $options);
    }

    /**
     * Store the uploaded file on a filesystem disk with public visibility.
     *
     * @param  string  $path
     * @param  string  $name
     * @param  array|string  $options
     * @return string|false
     */
    public function storePubliclyAs($path, $name, $options = [])
    {
        $options = $this->parseOptions($options);

        $options['visibility'] = 'public';

        return $this->storeAs($path, $name, $options);
    }

    /**
     * Store the uploaded file on a filesystem disk.
     *
     * @param  string  $path
     * @param  string  $name
     * @param  array|string  $options
     * @return string|false
     */
    public function storeAs($path, $name, $options = [])
    {
        $options = $this->parseOptions($options);

        $disk = Arr::pull($options, 'disk');

        return Container::getInstance()->make(FilesystemFactory::class)->disk($disk)->putFileAs(
            $path, $this, $name, $options
        );
    }

    /**
     * Get the contents of the uploaded file.
     *
     * @return false|string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function get()
    {
        if (! $this->isValid()) {
            throw new FileNotFoundException("File does not exist at path {$this->getPathname()}.");
        }

        return file_get_contents($this->getPathname());
    }

    /**
     * Returns the file size.
     *
     * It is extracted from the request from which the file has been uploaded.
     * Then it should not be considered as a safe value.
     *
     * @return int|null The file size
     */
    public function getClientSize()
    {
        return $this->getSize();
    }

    /**
     * Get the file's extension supplied by the client.
     *
     * @return string
     */
    public function clientExtension()
    {
        return $this->guessClientExtension();
    }

    /**
     * Create a new file instance from a base instance.
     *
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile  $file
     * @param  bool  $test
     * @return static
     */
    public static function createFromBase(SymfonyUploadedFile $file, $test = false)
    {
        return $file instanceof static ? $file : new static(
            $file->getPathname(),
            $file->getClientOriginalName(),
            $file->getClientMimeType(),
            null,
            $file->getError(),
            $test
        );

        // updated constructor signature:
        // return $file instanceof static ? $file : new static(
        //     $file->getPathname(),
        //     $file->getClientOriginalName(),
        //     $file->getClientMimeType(),
        //     $file->getError(),
        //     $test
        // );
    }

    /**
     * Parse and format the given options.
     *
     * @param  array|string  $options
     * @return array
     */
    protected function parseOptions($options)
    {
        if (is_string($options)) {
            $options = ['disk' => $options];
        }

        return $options;
    }
}
