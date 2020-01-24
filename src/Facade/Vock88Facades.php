<?php
/**
 * @package     IdnPlay\Vock88\Facade - Vock88Facades
 * @author      singkek
 * @copyright   Copyright(c) 2019
 * @version     1
 * @created     2020-01-24
 * @updated     2020-01-24
 **/

namespace IdnPlay\Vock88\Facade;

use Illuminate\Support\Facades\Facade;

class Vock88Facades extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'vock88';
    }
}
