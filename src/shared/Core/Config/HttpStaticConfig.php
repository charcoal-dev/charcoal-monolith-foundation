<?php
declare(strict_types=1);

namespace App\Shared\Core\Config;

use App\Shared\Core\Directories;
use App\Shared\Foundation\Http\Config\HttpClientConfig;
use App\Shared\Foundation\Http\Config\HttpInterfaceConfig;
use App\Shared\Foundation\Http\HttpInterface;
use App\Shared\Foundation\Http\HttpLogLevel;
use App\Shared\Utility\StringHelper;
use Charcoal\Filesystem\Directory;
use Charcoal\Filesystem\Exception\FilesystemException;

/**
 * Class HttpStaticConfig
 * @package App\Shared\Core\Config
 */
readonly class HttpStaticConfig
{
    public ?HttpClientConfig $clientConfig;
    public array $interfaces;

    /**
     * @param Directories $dir
     * @param mixed $configData
     */
    public function __construct(Directories $dir, mixed $configData)
    {
        if (!is_array($configData)) {
            throw new \UnexpectedValueException("HTTP configuration is required");
        }

        $this->clientConfig = $this->getClientConfig($dir->storage, $configData["client"] ?? null);

        $interfaces = [];
        $interfaceData = $configData["interfaces"] ?? null;
        if (is_array($interfaceData)) {
            foreach ($interfaceData as $interfaceId => $ifData) {
                $ifId = HttpInterface::tryFrom(strval($interfaceId));
                if (!$ifId instanceof HttpInterface) {
                    throw new \DomainException("No such HTTP interface declared in " . HttpInterface::class);
                }

                if (!is_array($ifData)) {
                    throw new \UnexpectedValueException("Bad HTTP interface configuration for: " . $ifId->name);
                }

                $status = $ifData["status"] ?? null;
                if (!is_bool($status)) {
                    throw new \UnexpectedValueException('Invalid HTTP interface "status" config for: ' . $ifId->name);
                }

                $logData = $ifData["logData"] ?? null;
                if (!is_string($logData) || empty($logData)) {
                    throw new \UnexpectedValueException('Invalid HTTP interface "logData" config for: ' . $ifId->name);
                }

                $logData = HttpLogLevel::fromString(trim($logData));

                $logHttpMethodOptions = $ifData["logHttpMethodOptions"] ?? null;
                if (!is_bool($logHttpMethodOptions)) {
                    throw new \UnexpectedValueException('Invalid HTTP interface "logHttpMethodOptions" config for: ' . $ifId->name);
                }

                $traceHeader = $this->validateHeaderKey("traceHeader",
                    $ifData["traceHeader"] ?? null, $ifId->name);

                $cachedResponseHeader = $this->validateHeaderKey("cachedResponseHeader",
                    $ifData["cachedResponseHeader"] ?? null, $ifId->name);

                $ifConfig = new HttpInterfaceConfig();
                $ifConfig->status = $status;
                $ifConfig->logData = $logData;
                $ifConfig->logHttpMethodOptions = $logHttpMethodOptions;
                $ifConfig->traceHeader = $traceHeader;
                $ifConfig->cachedResponseHeader = $cachedResponseHeader;
                $interfaces[$ifId->value] = $ifConfig;
            }
        }

        $this->interfaces = $interfaces;
    }

    private function validateHeaderKey(string $key, ?string $value, string $interface): string
    {
        $value = StringHelper::getTrimmedOrNull($value);
        if (!is_null($value) && !is_string($value)) {
            throw new \UnexpectedValueException(
                'Invalid HTTP interface "' . $key . '" config for: ' . $interface
            );
        }

        if (is_string($value) && !preg_match('/^[\w\-]+$/i', $value)) {
            throw new \UnexpectedValueException(
                'Bad value for HTTP interface "' . $key . '" config for: ' . $interface
            );
        }

        return $value;
    }

    /**
     * @param Directory $storageDir
     * @param mixed $clientConfigData
     * @return HttpClientConfig
     */
    private function getClientConfig(Directory $storageDir, mixed $clientConfigData): HttpClientConfig
    {
        if (!is_array($clientConfigData)) {
            throw new \InvalidArgumentException("HTTP client config must be an array");
        }

        $clientConfig = new HttpClientConfig();

        $userAgent = $clientConfigData["userAgent"] ?? null;
        if (!is_string($userAgent) || empty($userAgent)) {
            throw new \InvalidArgumentException("User-agent must be a string for HTTP client config");
        }

        $sslCertificateFilePath = $clientConfigData["sslCertificateFilePath"] ?? null;
        if (!is_string($sslCertificateFilePath) || empty($sslCertificateFilePath)) {
            throw new \InvalidArgumentException("SSL certificate file path must be a string for HTTP client config");
        }

        try {
            $sslCaFile = $storageDir->getFile($sslCertificateFilePath, createIfNotExists: false);
            if (!$sslCaFile->isReadable()) {
                throw new \InvalidArgumentException("SSL certificate file path is not readable");
            }
        } catch (FilesystemException $e) {
            throw new \InvalidArgumentException("SSL certificate file error: " . $e->error->name);
        }

        $timeout = $clientConfigData["timeout"] ?? null;
        if (!is_int($timeout) || $timeout <= 0 || $timeout > 30) {
            throw new \OutOfRangeException("HTTP client timeout must be between 0-30 seconds");
        }

        $connectTimeout = $clientConfigData["connectTimeout"] ?? null;
        if (!is_int($connectTimeout) || $connectTimeout <= 0 || $connectTimeout > 30) {
            throw new \OutOfRangeException("HTTP client connectTimeout must be between 0-30 seconds");
        }

        $clientConfig->userAgent = $userAgent;
        $clientConfig->sslCertificateFilePath = $sslCaFile->path;
        $clientConfig->timeout = $timeout;
        $clientConfig->connectTimeout = $connectTimeout;
        return $clientConfig;
    }
}