<?php
declare(strict_types=1);

namespace App\Shared\Foundation\Http\InterfaceLog;

use App\Shared\CharcoalApp;
use App\Shared\Foundation\Http\HttpLogLevel;
use App\Shared\Utility\ArrayHelper;
use Charcoal\App\Kernel\Errors\ErrorEntry;
use Charcoal\Database\Queries\DbExecutedQuery;
use Charcoal\Database\Queries\DbFailedQuery;
use Charcoal\Http\Router\Controllers\Request;
use Charcoal\Http\Router\Controllers\Response\AbstractControllerResponse;
use Charcoal\Http\Router\Controllers\Response\FileDownloadResponse;
use Charcoal\Http\Router\Controllers\Response\PayloadResponse;

/**
 * Class InterfaceLogSnapshot
 * @package App\Shared\Foundation\Http\InterfaceLog
 */
class InterfaceLogSnapshot
{
    public array $requestUrl;
    public array $requestHeaders = [];
    public array $requestParams = [];
    public array $responseHeaders = [];
    public array $responseParams = [];
    public ?string $responseFileDownload = null;
    public array $dbQueries = [];
    public array $errors = [];
    public array $lifecycle = [];

    public function __construct(
        HttpLogLevel $logLevel,
        Request      $request,
        array        $ignoreHeaders = [],
        array        $ignoreParams = []
    )
    {
        // Request URL
        $this->requestUrl = [
            "queryStr" => $request->url->query,
            "fragment" => $request->url->fragment
        ];

        // Headers
        $this->requestHeaders = ArrayHelper::excludeKeys($request->headers->toArray(), $ignoreHeaders);

        // Request Params
        $this->requestParams = $logLevel === HttpLogLevel::COMPLETE ?
            ArrayHelper::excludeKeys($request->payload->toArray(), $ignoreParams) : [];
    }

    /**
     * @param CharcoalApp $app
     * @param HttpLogLevel $logLevel
     * @param AbstractControllerResponse $response
     * @param array $ignoreHeaders
     * @param array $ignoreParams
     * @return void
     * @throws \JsonException
     */
    public function finalise(
        CharcoalApp                $app,
        HttpLogLevel               $logLevel,
        AbstractControllerResponse $response,
        array                      $ignoreHeaders = [],
        array                      $ignoreParams = []
    ): void
    {
        $this->responseHeaders = ArrayHelper::excludeKeys($response->headers->toArray(), $ignoreHeaders);
        if ($response instanceof FileDownloadResponse) {
            $this->responseFileDownload = $response->filepath;
        }

        if ($logLevel === HttpLogLevel::COMPLETE) {
            if ($response instanceof PayloadResponse) {
                $this->responseParams = ArrayHelper::excludeKeys($response->payload->toArray(), $ignoreParams);
            }

            // Errors
            $errors = $app->errors->getAll();
            /** @var ErrorEntry $error */
            foreach ($errors as $error) {
                $this->errors[] = ArrayHelper::jsonFilter($error);
            }

            // Lifecycle Entries
            $this->lifecycle = $app->lifecycle->toArray();

            // Database Queries
            $appDbQueries = $app->databases->getAllQueries();
            foreach ($appDbQueries as $dbQuery) {
                /** @var DbExecutedQuery|DbFailedQuery $executed */
                $executed = $dbQuery["query"];
                $thisQuery = [
                    "db" => $dbQuery["db"],
                    "query" => [
                        "sql" => $executed->queryStr,
                        "data" => json_encode($executed->boundData),
                    ]
                ];

                if ($executed instanceof DbExecutedQuery) {
                    $thisQuery["rowsCount"] = $executed->rowsCount;
                }

                if ($executed instanceof DbFailedQuery) {
                    $thisQuery["error"] = [
                        "code" => $executed->error->code,
                        "info" => $executed->error->info,
                        "sqlState" => $executed->error->sqlState
                    ];
                }

                $this->dbQueries[] = $thisQuery;
                unset($thisQuery);
            }
        }
    }
}