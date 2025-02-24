<?php
declare(strict_types=1);

namespace App\Shared\Core\Orm;

use App\Shared\CharcoalApp;
use Charcoal\App\Kernel\Orm\AbstractOrmModule;
use Charcoal\Semaphore\FilesystemSemaphore;

/**
 * Class AppOrmModule
 * @package App\Shared\Core\Orm
 * @property CharcoalApp $app
 */
abstract class AppOrmModule extends AbstractOrmModule
{
    /**
     * @return FilesystemSemaphore
     */
    public function getSemaphore(): FilesystemSemaphore
    {
        return $this->app->semaphore;
    }
}