<?php

namespace App\Services\Telegram;

use App\Enums\TelegramNotificationStatus;
use App\Models\TgUser;
use App\Models\User;
use App\Services\Report\ReportService;
use App\Services\Telegram\Commands\BalanceCommand;
use App\Services\Telegram\Commands\ReportCommand;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\HttpException;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\InvalidJsonException;
use TelegramBot\Api\Types\BotCommand;
use TelegramBot\Api\Types\Update;
use TelegramBot\Api\Types\WebhookInfo;
use Throwable;

/**
 * TelegramService
 */
class TelegramService
{
    /**
     * @param  BotApi  $botApi
     * @param  ReportService  $reportService
     * @param  string  $telegramBot
     * @param  string  $telegramToken
     * @param  string  $appUrl
     */
    public function __construct(
            private readonly BotApi $botApi,
            private readonly ReportService $reportService,
            private readonly string $telegramBot,
            private readonly string $telegramToken,
            private readonly string $appUrl
    ) {
    }

    /**
     * @throws Exception
     */
    public function setWebhookUrl(string $url): void
    {
        $this->botApi->setWebhook($url);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function getWebhookInfo(): WebhookInfo
    {
        return $this->botApi->getWebhookInfo();
    }

    /**
     * @param  int  $chatId
     * @param  string  $message
     * @return void
     * @throws Throwable
     */
    public function send(int $chatId, string $message): void
    {
        try {
            $this->botApi->sendMessage($chatId, $message, 'HTML');
        } catch (Throwable $e) {
            if ($e->getCode() == Response::HTTP_FORBIDDEN) {
                $this->disabledTgUser($chatId);
            } else {
                $msg = "Exception in TelegramService->sendAll: {$e->getMessage()}: {$e->getTraceAsString()}";
                Log::channel('tgLog')->error('TelegramService->sendAll', [$msg]);
            }
        }
    }

    /**
     * @param  string  $message
     * @return void
     */
    public function sendAll(string $message): void
    {
        $tgUsers = TgUser::where('deleted_at', null)->get();
        if (!$tgUsers) {
            return;
        }

        foreach ($tgUsers as $tgUser) {
            try {
                $this->send((int) $tgUser->chat_id, $message);
            } catch (Throwable $e) {
                $msg = "Exception in TelegramService->sendAll: {$e->getMessage()}: {$e->getTraceAsString()}";
                Log::channel('tgLog')->error('TelegramService->sendAll', [$msg]);

                continue;
            }
        }
    }

    /**
     * @param  User  $user
     * @return string
     */
    public function start(User $user): string
    {
        $token = md5($user->id.'~'.$this->telegramToken);

        $url = 'https://t.me/'.$this->telegramBot.'?start='.$token;

        return $url;
    }

    /**
     * @param  User  $user
     * @param  string  $status
     * @return void
     * @throws Throwable
     */
    public function notification(User $user, string $status): void
    {
        $tgUser = TgUser::withTrashed()->where('user_id', $user->id)->first();
        if (!$tgUser) {
            throw new NotFoundHttpException(__('User not found.'));
        }

        if ($status == TelegramNotificationStatus::Disabled->value) {
            $tgUser->delete();
        } else {
            $tgUser->restore();
        }
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException|Throwable
     */
    public function command(array $message): void
    {
        $tgMessage = Update::fromResponse($message);


        if ($tgMessage->getCallbackQuery()) {
            ReportCommand::make($this->reportService)
                    ->getCallable(
                            $tgMessage->getCallbackQuery()->getMessage(),
                            $tgMessage->getCallbackQuery()->getData()
                    );
            return;
        }

        if (empty($tgMessage->getMessage())) {
            return;
        }


        try {
            $command = explode(' ', $tgMessage->getMessage()->getText());
            switch ($command[0]) {
                case '/start':
                    $this->getStarted($message);
                    break;
                case '/report':
                    ReportCommand::make($this->reportService)
                            ->getCallable($tgMessage->getMessage());
                    break;
                case '/balance':
                    BalanceCommand::make($this->reportService)
                            ->getCallable($tgMessage->getMessage());
            }
            return;
        } catch (Throwable $e) {
            Log::channel('tgLog')->error('TelegramService->command', [$e->getMessage()]);

            throw $e;
        }
    }

    /**
     * todo refactoring and change using with types
     * @throws Exception
     * @throws InvalidArgumentException|Throwable
     */
    private function getStarted($message): void
    {
        $chatId = $message['message']['from']['id'];
        $username = $message['message']['from']['username'] ?? $message['message']['from']['first_name'];
        $text = explode(' ', $message['message']['text']);
        $commandParam = $text[1] ?? null;

        if (!$commandParam) {
            $this->send($chatId, __('Please log in to your personal account on ').$this->appUrl);

            return;
        }

        $user = User::whereRaw("md5(CONCAT_WS('~', id, '{$this->telegramToken}')) = '$commandParam'")->first();
        if (!$user) {
            $this->send($chatId, __('Please log in to your personal account on ').$this->appUrl);

            return;
        }

        $tgUser = TgUser::where('chat_id', $chatId)->first();
        if ($tgUser) {
            if (md5($tgUser->user_id.'~'.$this->telegramToken) == $commandParam) {
                $this->send($chatId, __('Oops, this telegram is already linked to you.'));
            } else {
                $this->send($chatId, __('Oops, this telegram is already linked to another user.'));
            }

            return;
        }

        $tgUser = new TgUser([
                'username' => $username,
                'chat_id'  => $chatId,
                'user_id'  => $user->id,
        ]);
        $tgUser->saveOrFail();

        $this->send($chatId, __('Welcome!'));
    }

    /**
     * @throws Throwable
     */
    private function disabledTgUser(int $chatId): void
    {
        $tgUser = TgUser::where('chat_id', $chatId)->first();

        if (!$tgUser) {
            return;
        }

        $tgUser->deleteOrFail();
    }

    /**
     * @throws InvalidJsonException
     * @throws Exception
     * @throws HttpException
     */
    public function setCommands(): void
    {
        $commands = [
                BotCommand::fromResponse([
                        'command'     => 'report',
                        'description' => 'This is command send report!',
                ]),
                BotCommand::fromResponse([
                        'command'     => 'balance',
                        'description' => 'This is command send balance!',
                ]),
        ];
        $this->botApi->setMyCommands($commands);
    }
}