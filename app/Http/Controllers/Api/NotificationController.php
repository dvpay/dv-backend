<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\NotificationStoreRequest;
use App\Http\Requests\Notification\NotificationTargetStoreRequest;
use App\Http\Resources\DefaultResponseResource;
use App\Http\Resources\Notification\NotificationCategoryCollection;
use App\Http\Resources\Notification\NotificationCollection;
use App\Http\Resources\Notification\NotificationTargetCollection;
use App\Models\Notification\NotificationCategory;
use App\Models\Notification\NotificationTarget;
use Illuminate\Contracts\Auth\Authenticatable;

class NotificationController extends Controller
{

    /**
     * @param Authenticatable $user
     * @return NotificationCollection
     */
    public function index(Authenticatable $user): NotificationCollection
    {
        return NotificationCollection::make($user->notifications);
    }

    /**
     * @return NotificationCategoryCollection
     */
    public function list(): NotificationCategoryCollection
    {
        $notifications = NotificationCategory::with('notification')->get();

        return NotificationCategoryCollection::make($notifications);
    }

    /**
     * @param NotificationStoreRequest $request
     * @param Authenticatable $user
     * @return DefaultResponseResource
     */
    public function store(NotificationStoreRequest $request, Authenticatable $user): DefaultResponseResource
    {
        $user->notifications()->sync($request->input('notifications'));

        return DefaultResponseResource::make([]);
    }

    /**
     * @return NotificationTargetCollection
     */
    public function targetsList(): NotificationTargetCollection
    {
        return NotificationTargetCollection::make(NotificationTarget::take(10)->get());
    }

    /**
     * @param NotificationTargetStoreRequest $request
     * @param Authenticatable $user
     * @return DefaultResponseResource
     */
    public function storeTargets(
        NotificationTargetStoreRequest $request,
        Authenticatable                $user
    ): DefaultResponseResource
    {
        $user->notificationTarget()->sync($request->input('targets'));
        return DefaultResponseResource::make([]);
    }

    public function targets(Authenticatable $user)
    {
        return NotificationTargetCollection::make($user->notificationTarget);
    }

}
