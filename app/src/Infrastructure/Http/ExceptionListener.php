<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        if (! str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/json');

        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->setData([
                'error' => [
                    'code' => $exception->getStatusCode(),
                    'message' => $exception->getMessage(),
                ],
            ]);
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            $response->setData([
                'error' => [
                    'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => 'Internal server error',
                ],
            ]);
        }

        $event->setResponse($response);
    }
}
