<?php

namespace Models;

use Interfaces\DBInterface;
use Interfaces\EntityInterface;

class User implements EntityInterface
{
    const TABLE_NAME = 'user';
    const OUTPUT_FIELDS = '*';
    const CSV_MAPPING = [
        'name' => 0,
        'email' => 1,
    ];

    /**
     * @param array|string $condition
     * @param array $params
     * @return array|false
     */
    public function get($condition = [], $params = [])
    {
        if (App::getDb()->select(self::TABLE_NAME, self::OUTPUT_FIELDS, $condition, $params)) {
            return App::getDb()->getStmt()->fetchAll(\PDO::FETCH_ASSOC);
        }

        return false;
    }

    /**
     * @param array $data
     * @param array|string $condition
     * @return boolean
     */
    public function edit(array $data = [], $condition = [])
    {
        return App::getDb()->update(self::TABLE_NAME, $data, $condition);
    }

    /**
     * @param array $data
     * @return boolean
     */
    public function add(array $data = [])
    {
        return App::getDb()->insert(self::TABLE_NAME, $data);
    }

    /**
     * @param array|string $condition
     * @return boolean
     */
    public function delete($condition = [])
    {
        return App::getDb()->delete(self::TABLE_NAME, $condition);
    }

    /**
     * @param array $data
     * @return boolean
     */
    public function validate(array $data = []) {
        if (empty($data['name']) || !is_string($data['name']) || strlen($data['name']) > 255) {
            return false;
        }
        if (
            empty($data['email']) || !is_string($data['email']) ||
            strlen($data['email']) > 255 || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)
        ) {
            return false;
        }

        return true;
    }

}