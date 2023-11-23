<?php

namespace App\Services\Telegram\Commands;

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Message;

abstract class BaseCommand
{
    abstract public function getCommand(): string;

    abstract public function getCallable(Message $message, $command): void;

    public static function make(...$params): static
    {
        return new static(...$params);
    }

}