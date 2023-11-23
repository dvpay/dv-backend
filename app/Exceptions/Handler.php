<?php

namespace App\Exceptions;

use App\Enums\HeartbeatServiceName;
use App\Enums\HeartbeatStatus;
use App\Http\Resources\ExceptionResource;
use App\Jobs\HeartbeatStatusJob;
use App\Jobs\TelegramNotificationJob;
use Http\Client\Exception\HttpException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use NotificationChannels\Telegram\Exceptions\CouldNotSendNotification;
use Sentry\Laravel\Integration;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
            WebhookException::class,
            CouldNotSendNotification::class, // disabled to avoid recursion
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
            'current_password',
            'password',
            'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register(): void
    {
        $this->renderable([$this, 'renderApiException']);
        $this->renderable([$this, 'renderUnauthorizedException']);
        $this->renderable([$this, 'renderException']);

        $this->reportable(function (Throwable $e) {
            Integration::captureUnhandledException($e);
        });
    }

    public function report(Throwable $e)
    {
        $this->reportable(function (Throwable $e) {
            $error = substr($e->getMessage(), 0, 100);
            $trace = substr($e->getTraceAsString(), 0, 200);

            $message = $error."\n".$trace;

            //TelegramNotificationJob::dispatchSync($message);

        });

        parent::report($e);
    }

    public function renderException(HttpException $e): JsonResponse
    {
        $environment = app()->environment();
        if ($environment == 'local' || $environment == 'stage') {
            $msg = $e->getMessage().' '.$e->getFile().':'.$e->getLine();
        } else {
            $msg = __('Something went wrong, please try again!');
        }
        return (new ExceptionResource([$msg]))
                ->response()
                ->setStatusCode(($e->getCode() == 0 ? Response::HTTP_INTERNAL_SERVER_ERROR : $e->getCode()));
    }

    public function renderApiException(ApiException $e): JsonResponse
    {
        return (new ExceptionResource([$e->getMessage()]))
                ->response()
                ->setStatusCode(($e->getCode() == 0 ? Response::HTTP_BAD_REQUEST : $e->getCode()));
    }

    public function renderUnauthorizedException(AuthenticationException $e): JsonResponse
    {
        return (new ExceptionResource([$e->getMessage()]))
                ->response()
                ->setStatusCode(($e->getCode() == 0 ? Response::HTTP_UNAUTHORIZED : $e->getCode()));
    }
}
