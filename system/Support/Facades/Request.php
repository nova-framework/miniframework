<?php

namespace System\Support\Facades;


/**
 * @see \System\Http\Request
 */
class Request extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'request'; }
}
