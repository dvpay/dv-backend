<?php

namespace App\Http\Controllers\Api;

use App\Dto\Models\UserDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Invite\AcceptInviteRequest;
use App\Http\Requests\Invite\SendInvateRequest;
use App\Http\Requests\Invite\UpdateUserInviteRequest;
use App\Http\Requests\Root\User\UpdateUserRequest;
use App\Http\Resources\DefaultResponseResource;
use App\Http\Resources\Invite\ListInviteCollectionResource;
use App\Models\Invite;
use App\Models\User;
use App\Notifications\InviteCreateNotification;
use App\Services\Store\StoreService;
use App\Services\User\UserService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

class InviteController extends Controller
{
    public function __construct(
        private readonly UserService  $userService,
        private readonly StoreService $storeService,
    )
    {
        $this->authorizeResource(Invite::class, 'invite');
    }

    public function index(Request $request): ListInviteCollectionResource
    {
        $data = $this->userService->getInvitedByUser($request->user(), $request);

        return new ListInviteCollectionResource($data);

    }

    public function invite(SendInvateRequest $request): DefaultResponseResource
    {
        $token = hash('sha256', $request->input('email') . now());
        $user = $request->user();

        $invite = Invite::firstOrCreate([
            'email' => $request->input('email'),

        ], [
            'user_id' => $user->id,
            'token'   => $token,
            'role'    => $request->input('role')
        ]);

        $invite->notify((new InviteCreateNotification($invite->token))->locale($user->language));

        return new DefaultResponseResource([]);
    }

    /**
     * @throws \Throwable
     */
    public function accept(AcceptInviteRequest $request): DefaultResponseResource
    {
        $invite = Invite::where('token', $request->input('token'))
            ->whereNull('invited_id')
            ->firstOrFail();

        $dto = new UserDto([
            'email'    => $invite->email,
            'name'     => $dto->name ?? '',
            'password' => $request->input('password'),
        ]);

        $user = $this->userService->create($dto, $invite->role);
        $invite->update(['invited_id' => $user->id]);

        $this->storeService->attachUserToStoresByHolder($invite->user_id, $user);

        return new DefaultResponseResource(['email' => $invite->email]);

    }

    /**
     * @param User $user
     * @param UpdateUserRequest $request
     * @param Authenticatable $auth
     * @return DefaultResponseResource
     */
    public function update(Invite $invite, UpdateUserInviteRequest $request): DefaultResponseResource
    {
        $this->userService->updateUserRoles($invite->user, $request->input("roles"));
        $this->storeService->attachUserToStores($request->input("stores"), $invite->user);

        return new DefaultResponseResource([]);

    }

    public function show(Invite $invite)
    {
        $data = $invite->load('user.roles', 'user.stores');

        return new DefaultResponseResource($data);

    }
}