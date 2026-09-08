<?php

namespace Database\Seeders\Concerns;

use RuntimeException;

use function config;

/**
 * Тестовые сидеры (ReferenceDataSeeder, TestClientDataSeeder) пишут напрямую в легаси-схему
 * REVELATION по её реальным таблицам (CLIENT, CONTAINER, MONEY_DIST...) — если их случайно
 * запустить с .env, указывающим на боевую/реальную БД (victory@10.20.36.3), они испортят
 * реальные данные клиентов.
 *
 * ВАЖНО: у yajra/laravel-oci8 подключение устанавливается сразу при первом обращении к
 * DB::connection($name) (не лениво, как у обычных PDO-драйверов Laravel) — то есть сам факт
 * вызова DB::connection('oracle') уже означает попытку реального сетевого соединения.
 * Поэтому эта проверка читает конфиг напрямую через config(), НЕ принимает готовое
 * ConnectionInterface и обязана вызываться ДО DB::connection($this->connection) в run(),
 * иначе к моменту проверки соединение с боевой БД уже могло быть установлено.
 */
trait GuardsAgainstNonTestDatabase
{
    protected function guardTestDatabaseOnly(string $connectionName): void
    {
        $config = config("database.connections.{$connectionName}", []);

        $host = strtolower((string)($config['host'] ?? ''));
        $database = strtolower((string)($config['database'] ?? ''));
        $serviceName = strtolower((string)($config['service_name'] ?? ''));

        $isLocalHost = in_array($host, ['localhost', '127.0.0.1'], true);
        $isTestDatabase = $database === 'xe' || $serviceName === 'xe';

        if (!$isLocalHost || !$isTestDatabase) {
            throw new RuntimeException(
                "Отказ: сидер {$this->seederName()} пишет тестовые данные напрямую в таблицы "
                . "легаси-схемы REVELATION и разрешён только на локальной тестовой XE "
                . "(host=localhost/127.0.0.1, database/service_name=XE). "
                . "Текущий конфиг подключения '{$connectionName}': host=\"{$config['host']}\", "
                . "database=\"{$config['database']}\", service_name=\"{$config['service_name']}\". "
                . 'Если это боевая или иная реальная БД — остановитесь. '
                . 'Если это новая тестовая база — проверьте .env (DB_ORA_HOST/DB_ORA_DATABASE/DB_ORA_SERVICE_NAME) '
                . 'и при необходимости расширьте guardTestDatabaseOnly().'
            );
        }
    }

    protected function seederName(): string
    {
        return static::class;
    }
}
