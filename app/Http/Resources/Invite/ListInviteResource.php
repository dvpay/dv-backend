<?php

namespace App\Http\Resources\Invite;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class ListInviteResource extends BaseResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
