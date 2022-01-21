<?php

namespace Illuminate\Session;

use SessionHandlerInterface;

class NullSessionHandler implements SessionHandlerInterface
{
    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function open($savePath, $sessionName)/*: bool*/
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function close()/*: bool*/
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    #[\ReturnTypeWillChange]
    public function read($sessionId)/*: string*/
    {
        return '';
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function write($sessionId, $data)/*: bool*/
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function destroy($sessionId)/*: bool*/
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function gc($lifetime)/*: int*/
    {
        return 0;
    }
}
