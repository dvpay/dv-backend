<?php

declare(strict_types=1);

namespace App\Http\Resources\Heartbeat;

use App\Http\Resources\BaseResource;
use Exception;

/**
 * ListInvoiceAddressesResourceNew
 */
class ServiceLogHistoryResource extends BaseResource
{
    /**
     * @param $request
     * @return array
     * @throws Exception
     */
    public function toArray($request): array
    {
        $variables = is_array($this->message_variables)
            ? array_map(fn($v) => is_array($v) ? implode('', $v) : $v, $this->message_variables)
            : [];
        return [
            'createdAt' => $this->created_at,
            'message' => __($this->message, $variables)
        ];
    }
}
