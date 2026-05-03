<?php

abstract class Model {

    protected function db(): mysqli {
        return db();
    }

    protected function query(string $sql, string $types = '', array $params = []): mysqli_stmt {
        return db_query($sql, $types, $params);
    }

    protected function fetchAll(string $sql, string $types = '', array $params = []): array {
        return db_fetch_all($sql, $types, $params);
    }

    protected function fetchOne(string $sql, string $types = '', array $params = []): ?array {
        return db_fetch_one($sql, $types, $params);
    }

    protected function fetchScalar(string $sql, string $types = '', array $params = []) {
        return db_fetch_scalar($sql, $types, $params);
    }

    protected function lastId(): int {
        return db_last_id();
    }
}
