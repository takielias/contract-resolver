<?php

namespace TakiElias\ContractResolver\Facades;

use Illuminate\Support\Facades\Facade;

class ContractResolver extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'contract-resolver';
    }
}
