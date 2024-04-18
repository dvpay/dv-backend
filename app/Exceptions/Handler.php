<?php

namespace App\Exceptions;

use App\Http\Resources\ExceptionResource;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use NotificationChannels\Telegram\Exceptions\CouldNotSendNotification;
use Sentry\Laravel\Integration;
use Sentry\State\Scope;
use Symfony\Component\HttpFoundation\Response;
use Throwable;


class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    protected $dontReport = [
        WebhookException::class,
        CouldNotSendNotification::class, // disabled to avoid recursion
        LowConfirmationException::class,
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable([$this, 'renderApiException']);
        $this->renderable([$this, 'renderUnauthorizedException']);

        $this->reportable(function (Throwable $e) {
            Integration::configureScope(function (Scope $scope): void {
                $scope->setTag('app.domain', config('app.app_domain'));
                $scope->setTag('app.version', config('app.version'));
            });
            Integration::captureUnhandledException($e);
        });
    }

    public function render($request, Throwable $e)
    {
        if ($this->isHttpException($e)) {
            if ($e->getStatusCode() === 404) {
                return response()->json(['error' => $e->getMessage()], 404);
            }
            if ($e->getStatusCode() === 500) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }
        return parent::render($request, $e);
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