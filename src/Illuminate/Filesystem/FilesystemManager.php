<?php

namespace Illuminate\Filesystem;

use Aws\S3\S3Client;
use Closure;
use Illuminate\Contracts\Filesystem\Factory as FactoryContract;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use League\Flysystem\AdapterInterface as FlysystemAdapter;
use League\Flysystem\Filesystem as Flysystem;
use League\Flysystem\Patch\AwsS3V3Adapter as S3Adapter;
use League\Flysystem\Patch\FtpAdapter;
use League\Flysystem\Patch\LocalFilesystemAdapter as LocalAdapter;
use League\Flysystem\Patch\PathPrefixedAdapter;
use League\Flysystem\Patch\PortableVisibilityConverter;
use League\Flysystem\Patch\ReadOnlyFilesystemAdapter;
use League\Flysystem\Patch\SftpAdapter;
use League\Flysystem\Patch\Visibility;

// use League\Flysystem\AwsS3V3\PortableVisibilityConverter as AwsS3PortableVisibilityConverter;
// use League\Flysystem\Ftp\FtpConnectionOptions;
// use League\Flysystem\PhpseclibV3\SftpConnectionProvider;

/**
 * @mixin \Illuminate\Contracts\Filesystem\Filesystem
 * @mixin \Illuminate\Filesystem\FilesystemAdapter
 */
class FilesystemManager implements FactoryContract
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The array of resolved filesystem drivers.
     *
     * @var array
     */
    protected $disks = [];

    /**
     * The registered custom driver creators.
     *
     * @var array
     */
    protected $customCreators = [];

    /**
     * Create a new filesystem manager instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get a filesystem instance.
     *
     * @param  string|null  $name
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function drive($name = null)
    {
        return $this->disk($name);
    }

    /**
     * Get a filesystem instance.
     *
     * @param  string|null  $name
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function disk($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->disks[$name] = $this->get($name);
    }

    /**
     * Get a default cloud filesystem instance.
     *
     * @return \Illuminate\Contracts\Filesystem\Cloud
     */
    public function cloud()
    {
        $name = $this->getDefaultCloudDriver();

        return $this->disks[$name] = $this->get($name);
    }

    /**
     * Build an on-demand disk.
     *
     * @param  string|array  $config
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function build($config)
    {
        return $this->resolve('ondemand', is_array($config) ? $config : [
            'driver' => 'local',
            'root' => $config,
        ]);
    }

    /**
     * Attempt to get the disk from the local cache.
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected function get($name)
    {
        return isset($this->disks[$name]) ? $this->disks[$name] : $this->resolve($name);
    }

    /**
     * Resolve the given disk.
     *
     * @param  string  $name
     * @param  array|null  $config
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve($name, $config = null)
    {
        $config = isset($config) ? $config : $this->getConfig($name);

        if (empty($config['driver'])) {
            throw new InvalidArgumentException("Disk [{$name}] does not have a configured driver.");
        }

        $driver = $config['driver'];

        if (isset($this->customCreators[$driver])) {
            return $this->callCustomCreator($config);
        }

        $driverMethod = 'create'.ucfirst($driver).'Driver';

        if (! method_exists($this, $driverMethod)) {
            throw new InvalidArgumentException("Driver [{$driver}] is not supported.");
        }

        return $this->{$driverMethod}($config, $name);
    }

    /**
     * Call a custom driver creator.
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected function callCustomCreator(array $config)
    {
        return $this->customCreators[$config['driver']]($this->app, $config);
    }

    /**
     * Create an instance of the local driver.
     *
     * @param  array  $config
     * @param  string  $name
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function createLocalDriver(array $config, string $name = 'local')
    {
        // $visibility = PortableVisibilityConverter::fromArray(
        //     $config['permissions'] ?? [],
        //     $config['directory_visibility'] ?? $config['visibility'] ?? Visibility::PRIVATE
        // );
        $permissions = isset($config['permissions']) ? $config['permissions'] : [];

        $links = (isset($config['links']) ? $config['links'] : null) === 'skip'
            ? LocalAdapter::SKIP_LINKS
            : LocalAdapter::DISALLOW_LINKS;

        $adapter = new LocalAdapter(
            // $config['root'], $visibility, $config['lock'] ?? LOCK_EX, $links
            $config['root'], isset($config['lock']) ? $config['lock'] : LOCK_EX, $links, $permissions
        );

        return (new LocalFilesystemAdapter(
            $this->createFlysystem($adapter, $config), $adapter, $config
        ))->diskName(
            $name
        )->shouldServeSignedUrls(
            $config['serve'] ?? false,
            fn () => $this->app['url'],
        );
    }

    /**
     * Create an instance of the ftp driver.
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function createFtpDriver(array $config)
    {
        if (! isset($config['root'])) {
            $config['root'] = '';
        }

        // $adapter = new FtpAdapter(FtpConnectionOptions::fromArray($config));
        $adapter = new FtpAdapter($config);

        return new FilesystemAdapter($this->createFlysystem($adapter, $config), $adapter, $config);
    }

    /**
     * Create an instance of the sftp driver.
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function createSftpDriver(array $config)
    {
        // $provider = SftpConnectionProvider::fromArray($config);

        // $root = $config['root'] ?? '';

        // $visibility = PortableVisibilityConverter::fromArray(
        //     $config['permissions'] ?? []
        // );

        // $adapter = new SftpAdapter($provider, $root, $visibility);

        $adapter = new SftpAdapter($config);

        return new FilesystemAdapter($this->createFlysystem($adapter, $config), $adapter, $config);
    }

    /**
     * Create an instance of the Amazon S3 driver.
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Filesystem\Cloud
     */
    public function createS3Driver(array $config)
    {
        $s3Config = $this->formatS3Config($config);

        $root = (string) (isset($s3Config['root']) ? $s3Config['root'] : '');

        $options = isset($config['options']) ? $config['options'] : [];

        $streamReads = isset($config['stream_reads']) ? $config['stream_reads'] : false;

        $adapter = new S3Adapter(new S3Client($s3Config), $s3Config['bucket'], $root, $options, $streamReads);

        return new FilesystemAdapter($this->createFlysystem($adapter, $config), $adapter, $config);

        // $s3Config = $this->formatS3Config($config);

        // $root = $s3Config['root'] ?? null;

        // $visibility = new AwsS3PortableVisibilityConverter(
        //     $config['visibility'] ?? Visibility::PUBLIC
        // );

        // $streamReads = $s3Config['stream_reads'] ?? false;

        // $client = new S3Client($s3Config);

        // $adapter = new S3Adapter($client, $s3Config['bucket'], $root, $visibility, null, [], $streamReads);

        // return new AwsS3V3Adapter(
        //     $this->createFlysystem($adapter, $config), $adapter, $s3Config, $client
        // );
    }

    /**
     * Format the given S3 configuration with the default options.
     *
     * @param  array  $config
     * @return array
     */
    protected function formatS3Config(array $config)
    {
        $config += ['version' => 'latest'];

        if (! empty($config['key']) && ! empty($config['secret'])) {
            $config['credentials'] = Arr::only($config, ['key', 'secret']);
        }

        if (! empty($config['token'])) {
            $config['credentials']['token'] = $config['token'];
        }

        return Arr::except($config, ['token']);
    }

    /**
     * Create a scoped driver.
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function createScopedDriver(array $config)
    {
        if (empty($config['disk'])) {
            throw new InvalidArgumentException('Scoped disk is missing "disk" configuration option.');
        } elseif (empty($config['prefix'])) {
            throw new InvalidArgumentException('Scoped disk is missing "prefix" configuration option.');
        }

        return $this->build(tap(
            is_string($config['disk']) ? $this->getConfig($config['disk']) : $config['disk'],
            function (&$parent) use ($config) {
                $parent['prefix'] = $config['prefix'];

                if (isset($config['visibility'])) {
                    $parent['visibility'] = $config['visibility'];
                }
            }
        ));
    }

    /**
     * Create a Flysystem instance with the given adapter.
     *
     * #param  \League\Flysystem\FilesystemAdapter  $adapter
     * #param  array  $config
     * #return \League\Flysystem\FilesystemOperator
     *
     * @param  \League\Flysystem\AdapterInterface  $adapter
     * @param  array  $config
     * @return \League\Flysystem\FilesystemInterface
     */
    protected function createFlysystem(FlysystemAdapter $adapter, array $config)
    {
        if (isset($config['read-only']) ? $config['read-only'] : false === true) {
            $adapter = new ReadOnlyFilesystemAdapter($adapter);
        }

        if (! empty($config['prefix'])) {
            $adapter = new PathPrefixedAdapter($adapter, $config['prefix']);
        }

        $config = Arr::only($config, [
            'directory_visibility',
            'disable_asserts',
            'retain_visibility',
            'temporary_url',
            'url',
            'visibility',
        ]);

        return new Flysystem($adapter, count($config) > 0 ? $config : null);
    }

    /**
     * Set the given disk instance.
     *
     * @param  string  $name
     * @param  mixed  $disk
     * @return $this
     */
    public function set($name, $disk)
    {
        $this->disks[$name] = $disk;

        return $this;
    }

    /**
     * Get the filesystem connection configuration.
     *
     * @param  string  $name
     * @return array
     */
    protected function getConfig($name)
    {
        return $this->app['config']["filesystems.disks.{$name}"] ?: [];
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['filesystems.default'];
    }

    /**
     * Get the default cloud driver name.
     *
     * @return string
     */
    public function getDefaultCloudDriver()
    {
        return isset($this->app['config']) && isset($this->app['config']['filesystems.cloud']) ? $this->app['config']['filesystems.cloud'] : 's3';
    }

    /**
     * Unset the given disk instances.
     *
     * @param  array|string  $disk
     * @return $this
     */
    public function forgetDisk($disk)
    {
        foreach ((array) $disk as $diskName) {
            unset($this->disks[$diskName]);
        }

        return $this;
    }

    /**
     * Disconnect the given disk and remove from local cache.
     *
     * @param  string|null  $name
     * @return void
     */
    public function purge($name = null)
    {
        $name = isset($name) ? $name : $this->getDefaultDriver();

        unset($this->disks[$name]);
    }

    /**
     * Register a custom driver creator Closure.
     *
     * @param  string  $driver
     * @param  \Closure  $callback
     * @return $this
     */
    public function extend($driver, Closure $callback)
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * Set the application instance used by the manager.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return $this
     */
    public function setApplication($app)
    {
        $this->app = $app;

        return $this;
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->disk()->$method(...$parameters);
    }
}
