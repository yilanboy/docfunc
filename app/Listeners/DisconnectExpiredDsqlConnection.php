<?php

declare(strict_types=1);

namespace App\Listeners;

use Bref\LaravelDsql\DsqlConnection;
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Support\Facades\Date;
use Laravel\Octane\Contracts\OperationTerminated;
use PDO;

/**
 * Proactively disconnects persistent Aurora DSQL connections before they hit
 * DSQL's 60-minute maximum connection duration so the next query reconnects
 * with a freshly signed IAM authentication token.
 */
class DisconnectExpiredDsqlConnection
{
    public const int MAX_CONNECTION_AGE_SECONDS = 3000;

    /**
     * @var array<string, int>
     */
    private static array $connectedAt = [];

    public static function recordConnection(ConnectionEstablished $event): void
    {
        if (! $event->connection instanceof DsqlConnection) {
            return;
        }

        self::$connectedAt[$event->connectionName] = Date::now()->getTimestamp();
    }

    public static function reset(): void
    {
        self::$connectedAt = [];
    }

    public function handle(OperationTerminated $event): void
    {
        $sandbox = $event->sandbox();

        if (! $sandbox->resolved('db')) {
            return;
        }

        $now = Date::now()->getTimestamp();

        foreach ($sandbox->make('db')->getConnections() as $name => $connection) {
            if (! $connection instanceof DsqlConnection) {
                continue;
            }

            if (! $connection->getRawPdo() instanceof PDO) {
                continue;
            }

            $connectedAt = self::$connectedAt[$name] ?? $now;

            if (($now - $connectedAt) >= self::MAX_CONNECTION_AGE_SECONDS) {
                $connection->disconnect();
                unset(self::$connectedAt[$name]);
            }
        }
    }
}
