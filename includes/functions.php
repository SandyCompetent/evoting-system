<?php
/**
 * Parameterized data-access API. Every value MUST be passed via the $params
 * array (never concatenated into the SQL string). This is the only data layer
 * in the application; the legacy string-SQL helpers were removed in
 * installment 2 of the security refactor.
 */
function q(string $sql, array $params = []): PDOStatement
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function fetchAll(string $sql, array $params = []): array
{
    return q($sql, $params)->fetchAll();
}

function fetchOne(string $sql, array $params = []): ?array
{
    $row = q($sql, $params)->fetch();
    return $row === false ? null : $row;
}

function execute(string $sql, array $params = []): int
{
    return q($sql, $params)->rowCount();
}
