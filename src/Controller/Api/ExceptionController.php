<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class ExceptionController
 */
class ExceptionController
{
    /**
     * @param Request                   $request
     * @param \Exception                $exception
     * @param DebugLoggerInterface|null $logger
     *
     * @return JsonResponse
     * @todo: implement full implementation
     */
    public function showAction(Request $request, \Exception $exception, DebugLoggerInterface $logger = null): JsonResponse
    {
        $code = 500;
        if ($exception instanceof NotFoundHttpException) {
            $code = 404;
        }
        if ($exception instanceof AccessDeniedHttpException) {
            $code = 403;
        }

        return new JsonResponse(['error' => $exception->getMessage(), 'trace' => $exception->getTrace()], $code);
    }
}
