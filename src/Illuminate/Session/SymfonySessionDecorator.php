<?php

namespace Illuminate\Session;

use BadMethodCallException;
use Illuminate\Contracts\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;

class SymfonySessionDecorator implements SessionInterface
{
    /**
     * The underlying Laravel session store.
     *
     * @var \Illuminate\Contracts\Session\Session
     */
    public /*readonly Session */$store;

    /**
     * Create a new session decorator.
     *
     * @param  \Illuminate\Contracts\Session\Session  $store
     * @return void
     */
    public function __construct(Session $store)
    {
        $this->store = $store;
    }

    /**
     * {@inheritdoc}
     */
    public function start()/*: bool*/
    {
        return $this->store->start();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()/*: string*/
    {
        return $this->store->getId();
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function setId(/*string */$id)/*: void*/
    {
        $id = backport_type_check('string', $id);

        $this->store->setId($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()/*: string*/
    {
        return $this->store->getName();
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function setName(/*string */$name)/*: void*/
    {
        $name = backport_type_check('string', $name);

        $this->store->setName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function invalidate(/*int */$lifetime = null)/*: bool*/
    {
        $lifetime = backport_type_check('?int', $lifetime);

        $this->store->invalidate();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function migrate(/*bool */$destroy = false, /*int */$lifetime = null)/*: bool*/
    {
        $destroy = backport_type_check('bool', $destroy);

        $lifetime = backport_type_check('?int', $lifetime);

        $this->store->migrate($destroy);

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function save()/*: void*/
    {
        $this->store->save();
    }

    /**
     * {@inheritdoc}
     */
    public function has(/*string */$name)/*: bool*/
    {
        $name = backport_type_check('string', $name);

        return $this->store->has($name);
    }

    /**
     * {@inheritdoc}
     */
    public function get(/*string */$name, /*mixed */$default = null)/*: mixed*/
    {
        $name = backport_type_check('string', $name);

        $default = backport_type_check('?mixed', $default);

        return $this->store->get($name, $default);
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function set(/*string */$name, /*mixed */$value)/*: void*/
    {
        $value = backport_type_check('mixed', $value);

        $name = backport_type_check('string', $name);

        $this->store->put($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function all()/*: array*/
    {
        return $this->store->all();
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function replace(array $attributes)/*: void*/
    {
        $this->store->replace($attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(/*string */$name)/*: mixed*/
    {
        $name = backport_type_check('string', $name);

        return $this->store->remove($name);
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function clear()/*: void*/
    {
        $this->store->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function isStarted()/*: bool*/
    {
        return $this->store->isStarted();
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function registerBag(SessionBagInterface $bag)/*: void*/
    {
        throw new BadMethodCallException('Method not implemented by Laravel.');
    }

    /**
     * {@inheritdoc}
     */
    public function getBag(/*string */$name)/*: SessionBagInterface*/
    {
        // $name = backport_type_check('string', $name);

        throw new BadMethodCallException('Method not implemented by Laravel.');
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataBag()/*: MetadataBag*/
    {
        throw new BadMethodCallException('Method not implemented by Laravel.');
    }
}
