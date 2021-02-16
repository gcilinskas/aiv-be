<?php declare(strict_types=1);

namespace App\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiController
 */
class ApiResponseController extends AbstractFOSRestController
{
    const SERVER_ERROR = 'server_error';
    const NOT_AUTH_ERROR = 'not_authorized';
    const NOT_FOUND_ERROR = 'not_found';
    const WRONG_DATA = 'wrong_data';
    const SEND_ERROR = 'not_sent';
    const NO_ACTIVE_PLAN = 'no_active_plan';
    const ALREADY_PAID = 'already_paid';
    const NO_OFFER = 'no_offer';
    const NO_POSITIONS_LEFT = 'no_positions_left';
    const VALIDATION_ERROR = 'not_valid';
    const EXISTS_ERROR = 'already_exists';
    const OLD_PASS_ERROR = 'old_pass_error';
    const SAME_PASS_ERROR = 'same_pass_error';
    const NOT_DRAFT = 'not_draft';
    const FB_API_ERROR = 'fb_api_error';
    const GOOGLE_API_ERROR = 'g_api_error';
    const APPLE_API_ERROR = 'apple_api_error';
    const RESET_LIMIT_ERROR = 'reset_limit';
    const RESET_EXPIRED_ERROR = 'reset_expired';
    const RESET_CODE_ERROR = 'reset_wrong';
    const EMAIL_VALIDATED = 'already_validated';
    const EMAIL_WRONG_VALIDATION = 'wrong_token';
    const NOT_PUBLISHABLE = 'not_publishable';

    /**
     * @var int
     */
    protected $statusCode = Response::HTTP_OK;

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /***
     * @param int $statusCode
     *
     * @return self
     */
    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * 200
     *
     * @param string|array $data
     * @param array|null   $groups
     * @param array        $headers
     *
     * @return Response
     */
    public function apiSuccessResponse($data = ['success' => true], array $groups = null, array $headers = []): Response
    {
        $view = $this->view($data, $this->getStatusCode(), $headers);
        if (false === is_null($groups)) {
            $context = (new Context())->setSerializeNull(true);
            $context->addGroups($groups);
            $view->setContext($context);
        }

        return $this->handleView($view->setFormat('json'));
    }

    /**
     * 400
     *
     * @param string       $code
     * @param string|array $message
     * @param array        $headers
     *
     * @return Response
     */
    public function apiErrorResponse(string $code, $message, array $headers = []): Response
    {
        if ($this->getStatusCode() === Response::HTTP_OK) {
            $this->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        return $this->apiSuccessResponse(["code" => $code, "message" => $message], null, $headers);
    }

    /**
     * 201
     *
     * @param array $data
     * @param array $groups
     * @param array $headers
     *
     * @return Response
     */
    public function apiCreatedResponse($data = [], array $groups = null, array $headers = []): Response
    {
        return $this->setStatusCode(Response::HTTP_CREATED)
                    ->apiSuccessResponse($data, $groups, $headers);
    }

    /**
     * 204
     *
     * @return Response
     */
    public function apiDeletedResponse(): Response
    {
        return $this->setStatusCode(Response::HTTP_NO_CONTENT)
                    ->apiSuccessResponse(null);
    }

    /**
     * 401
     *
     * @param string       $code
     * @param string|array $message
     *
     * @return Response
     */
    public function apiUnauthorizedResponse(string $code = self::NOT_AUTH_ERROR, $message = 'Not authorized'): Response
    {
        return $this->setStatusCode(Response::HTTP_UNAUTHORIZED)
                    ->apiErrorResponse($code, $message);
    }

    /**
     * 404
     *
     * @param string       $code
     * @param string|array $message
     *
     * @return Response
     */
    public function apiNotFoundResponse(string $code = self::NOT_FOUND_ERROR, $message = 'Entity not found'): Response
    {
        return $this->setStatusCode(Response::HTTP_NOT_FOUND)
                    ->apiErrorResponse($code, $message);
    }

    /**
     * 409
     *
     * @param string       $code
     * @param string|array $message
     *
     * @return Response
     */
    public function apiExistsResponse(string $code = self::EXISTS_ERROR, $message = 'Entity already exists'): Response
    {
        return $this->setStatusCode(Response::HTTP_CONFLICT)
                    ->apiErrorResponse($code, $message);
    }

    /**
     * 422
     *
     * @param string       $code
     * @param string|array $message
     *
     * @return Response
     */
    public function apiValidationResponse(string $code = self::VALIDATION_ERROR, $message = 'Validation error'): Response
    {
        return $this->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY)
                    ->apiErrorResponse($code, $message);
    }

    /**
     * 500
     *
     * @param string       $code
     * @param string|array $message
     *
     * @return Response
     */
    public function apiFailedResponse(string $code = self::SERVER_ERROR, $message = 'Server error'): Response
    {
        return $this->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR)
                    ->apiErrorResponse($code, $message);
    }

    /**
     * 503
     *
     * @param string       $code
     * @param string|array $message
     *
     * @return Response
     */
    public function apiServiceUnavailableResponse(string $code = self::SERVER_ERROR, $message = 'Service unavailable'): Response
    {
        return $this->setStatusCode(Response::HTTP_SERVICE_UNAVAILABLE)
                    ->apiErrorResponse($code, $message);
    }
}
