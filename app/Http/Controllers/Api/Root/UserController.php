<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\Root;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Root\User\UpdateUserRequest;
use App\Http\Resources\DefaultResponseResource;
use App\Http\Resources\User\RootUserCollection;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Http\Request;

/**
 *
 */
class UserController extends ApiController
{
    /**
     * @param UserService $userService
     */
    public function __construct(
        private readonly UserService $userService,

    )
    {
    }

    /**
     * @return RootUserCollection
     */
    public function index(Request $request): RootUserCollection
    {
        $user = $this->userService->getAllUser($request);

        return new RootUserCollection($user);
    }

    /**
     * @param User $user
     * @return DefaultResponseResource
     */
    public function show(User $user): DefaultResponseResource
    {
        $data = $this->userService->getDetailInfo($user);

        $data['stores'] = $user->stores;

        return new DefaultResponseResource([$data]);
    }

    /**
     * @param User $user
     * @param UpdateUserRequest $request
     * @return DefaultResponseResource
     */
    public function update(User $user, UpdateUserRequest $request): DefaultResponseResource
    {
        $this->userService->updateUserRoles($user, $request->input("roles"));

        return new DefaultResponseResource([]);

    }

    /**
     * @param User $user
     * @return DefaultResponseResource
     * @throws \Throwable
     */
    public function destroy(User $user)
    {
        $this->userService->deleteUser($user);

        return new DefaultResponseResource([]);

    }

}