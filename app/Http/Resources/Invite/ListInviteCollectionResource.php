<?php

namespace App\Http\Resources\Invite;

use App\Http\Resources\BaseCollection;

class ListInviteCollectionResource extends BaseCollection
{

    /**
     * @var string
     */
    public $collects = ListInviteResource::class;

}
