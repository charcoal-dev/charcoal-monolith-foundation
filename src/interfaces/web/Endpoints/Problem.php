<?php
declare(strict_types=1);

namespace App\Interfaces\Web\Endpoints;


use App\Interfaces\Web\AbstractWebEndpoint;

/**
 * Class Problem
 * @package App\Interfaces\Web\Endpoints
 */
class Problem extends AbstractWebEndpoint
{
    protected function entrypoint(): void
    {
        throw new \RuntimeException("This exception has been intentionally triggered so you can enjoy this attractive page",
            previous: new \LogicException("Nope! There doesn't seem to be anything wrong here."));
    }
}