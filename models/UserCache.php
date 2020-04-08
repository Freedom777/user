<?php

namespace Models;

class UserCache extends User {
    /**
     * @param array|string $condition
     * @param array $params
     * @return array|false
     */
    public function get($condition = [], $params = [])
    {
        App::getDb()->setDebug(true);
        parent::get($condition, $params);
        App::getDb()->setDebug(false);
        $query = App::getDb()->getLastQuery();

        $userTableCache = App::getCache()->get(parent::TABLE_NAME);
        if (empty($userTableCache)) {
            App::getCache()->set(parent::TABLE_NAME, []);
        }
        if (isset($userTableCache[$query])) {
            $result = $userTableCache[$query];
        } else {
            $result = false;
            if (App::getDb()->getStmt()->execute($params)) {
                $result = App::getDb()->getStmt()->fetchAll(\PDO::FETCH_ASSOC);
            }
            App::getCache()->set(parent::TABLE_NAME, array_merge(App::getCache()->get(parent::TABLE_NAME), [$query => $result]));
        }

        return $result;
    }

    /**
     * @param array $data
     * @param array|string $condition
     * @return boolean
     */
    public function edit(array $data = [], $condition = [])
    {
        $result = parent::edit($data, $condition);
        App::getCache()->clear(parent::class);

        return $result;
    }

    /**
     * @param array $data
     * @return boolean
     */
    public function add(array $data = [])
    {
        $result = parent::add($data);
        App::getCache()->clear(parent::class);

        return $result;
    }

    /**
     * @param array|string $condition
     * @return boolean
     */
    public function delete($condition = [])
    {
        $result = parent::delete($condition);
        App::getCache()->clear(parent::class);

        return $result;
    }
}
