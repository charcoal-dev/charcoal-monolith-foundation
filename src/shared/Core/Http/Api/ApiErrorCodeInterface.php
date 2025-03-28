<?php
declare(strict_types=1);

namespace App\Shared\Core\Http\Api;

use App\Shared\Core\Http\AbstractApiEndpoint;

/**
 * Interface ApiErrorCodeInterface
 * @package App\Shared\Core\Http\Api
 */
interface ApiErrorCodeInterface extends \BackedEnum
{
    /**
     * @param \Throwable|null $context
     * @param AbstractApiEndpoint|null $route
     * @return string|null
     */
    public function getErrorMessage(\Throwable $context = null, AbstractApiEndpoint $route = null): ?string;

    /**
     * @param \Throwable|null $context
     * @param AbstractApiEndpoint|null $route
     * @return int|string|null
     */
    public function getErrorCode(\Throwable $context = null, AbstractApiEndpoint $route = null): null|int|string;

    /**
     * @return int|null
     */
    public function getHttpCode(): ?int;
}