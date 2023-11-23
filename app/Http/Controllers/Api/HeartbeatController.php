<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Dto\ServiceLogHistoryDto;
use App\Http\Resources\DefaultResponseResource;
use App\Http\Resources\Heartbeat\HeartbeatServiceCollection;
use App\Http\Resources\Heartbeat\ServiceLogHistoryCollection;
use App\Http\Resources\Heartbeat\ServiceLogLaunchCollection;
use App\Models\Service;
use App\Models\ServiceLogLaunch;
use App\Services\Heartbeat\HeartbeatService;
use Illuminate\Http\Request;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * HeartbeatController
 * todo write policy for this controller
 */
class HeartbeatController extends ApiController
{
    /**
     * @param HeartbeatService $heartbeatService
     */
    public function __construct(private readonly HeartbeatService $heartbeatService)
    {
    }

    /**
     * @param Request $request
     * @return DefaultResponseResource
     * @throws InvalidArgumentException
     */
    public function getStatus(Request $request): DefaultResponseResource
    {
        $status = $this->heartbeatService->getSystemStatus();

        return new DefaultResponseResource($status);
    }


    /**
     * @param Request $request
     * @return DefaultResponseResource
     */
    public function getStatusForDashboard(Request $request)
    {
        $statuses = $this->heartbeatService->getStatusForDashboard();

        return new DefaultResponseResource($statuses);
    }

    /**
     * @param Request $request
     * @return ServiceLogHistoryCollection
     */
    public function getServiceLogHistory(Request $request): ServiceLogHistoryCollection
    {
        $input = $request->input();

        $dto = new ServiceLogHistoryDto([
            'page'          => $input['page'] ?? 1,
            'perPage'       => $input['perPage'] ?? 10,
            'sortField'     => $input['sortField'] ?? 'created_at',
            'sortDirection' => $input['sortDirection'] ?? 'desc',
            'serviceId'     => $input['serviceId'],
            'status'        => !empty($input['status']) ? $input['status'] : null,
        ]);

        $history = $this->heartbeatService->getServiceLogHistory($dto);

        return new ServiceLogHistoryCollection($history);
    }

    /**
     * @return DefaultResponseResource
     */
    public function getResources()
    {
        return DefaultResponseResource::make([
            'queues' => $this->heartbeatService->getQueues(),
            'disk'   => $this->heartbeatService->getDiskSpace(),
        ]);
    }

    /**
     * @return DefaultResponseResource
     */
    public function getAllService()
    {
        return HeartbeatServiceCollection::make($this->heartbeatService->getAllService());
    }

    public function getServiceLaunch(Service $service)
    {
        $launch = ServiceLogLaunch::where('service_id', $service->id)
            ->with(['serviceLogs', 'service'])
            ->orderBy('start_at', 'DESC')
            ->paginate();

        return ServiceLogLaunchCollection::make($launch);
    }
}
