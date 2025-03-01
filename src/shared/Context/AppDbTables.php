<?php
declare(strict_types=1);

namespace App\Shared\Context;

use Charcoal\App\Kernel\Orm\Db\DatabaseEnum;
use Charcoal\App\Kernel\Orm\Db\DbAwareTableEnum;

/**
 * Class AppDbTables
 * @package App\Shared\Context
 */
enum AppDbTables: string implements DbAwareTableEnum
{
    # CoreData Module
    case BFC = "bfc_table";
    case COUNTRIES = "countries";
    case DB_BACKUPS = "db_backups";
    case OBJECT_STORE = "object_store";
    case SYSTEM_ALERTS = "sys_alerts";

    # Mailer Module
    case MAILER_BACKLOG = "mails_queue";

    # HTTP Module
    case HTTP_INTERFACE_LOG = "http_if_log";
    case HTTP_CALL_LOG = "http_call_log";
    case HTTP_PROXIES = "http_proxies";

    # Engine Module
    case ENGINE_EXEC_LOG = "cli_exec_log";
    case ENGINE_EXEC_STATS = "cli_exec_stats";
    case ENGINE_CMD_QUEUE = "cli_cmd_queue";

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->value;
    }

    /**
     * @return DatabaseEnum
     */
    public function getDatabase(): DatabaseEnum
    {
        return AppDatabase::PRIMARY;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return 1;
    }
}