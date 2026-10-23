<?php
/**
 * Parameterized data-access API. Every value MUST be passed via the $params
 * array (never concatenated into the SQL string).
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
