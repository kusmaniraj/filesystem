<?php

namespace LogisticMall\FileSystem;

use Illuminate\Support\Facades\Facade;

class LMFileSystemFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'filesystem';
    }
}
