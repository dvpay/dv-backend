<?php

namespace App\Http\Resources\User;

use App\Http\Resources\BaseCollection;

class RootUserCollection extends BaseCollection
{
    public $collects = RootUserResource::class;

}
