<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\DefaultResponseResource;
use App\Services\Telegram\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use Throwable;

/**
 * TelegramController
 */
class TelegramController extends ApiController
{
    /**
     * @param TelegramService $telegramService
     */
    public function __construct(private readonly TelegramService $telegramService)
    {
    }

    /**
     * @param Request $request
     * @return DefaultResponseResource
     */
    public function start(Request $request): DefaultResponseResource
    {
        $url = $this->telegramService->start($request->user());

        return new DefaultResponseResource([$url]);
    }

    /**
     * @param Request $request
     * @return DefaultResponseResource
     * @throws Throwable
     */
    public function notification(Request $request)
    {
        $user = $request->user();
        $status = $request->input('status');

        $this->telegramService->notification($user, $status);

        return new DefaultResponseResource([]);
    }

    /**
     * @param Request $request
     * @return DefaultResponseResource
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws Throwable
     */
    public function command(Request $request)
    {
        $input = $request->input();

        Log::channel('tgLog')->error('TelegramController->command', $input);

        $this->telegramService->command($input);

        return new DefaultResponseResource([]);
    }
}
