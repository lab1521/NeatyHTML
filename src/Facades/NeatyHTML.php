<?php

namespace Lab1521\NeatyHTML\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Facade Implementation of NeatyHTML class
 */
class NeatyHTML extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Lab1521\NeatyHTML\NeatyHTML::class;
    }
}
