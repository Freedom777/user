<?php

namespace Models;

use Interfaces\DBInterface;

class Mysql implements DBInterface
{
    /** @var \PDOStatement|null $stmt */
    static protected $stmt = null;

    static private $instance = null;
    static private $db = null;
    static private $debug = false;
    static private $lastQuery = null;

    /**
     * Mysql constructor.
     * @param array $options
     */
    private function __construct($options = [])
    {
        if (empty($options['dsn']) || empty($options['username']) || !isset($options['password'])) {
            Log::critical('Connection parameters missing.');
        }
        try {
            self::$db = new \PDO($options['dsn'], $options['username'], $options['password'], !empty($options['driverOptions']) ? $options['driverOptions'] : []);
        } catch (\PDOException $e) {
            Log::critical($e->getMessage());
        }
    }

    /**
     * @param array $options
     *
     * @return Mysql
     */
    static public function getInstance($options = []) {
        if (null === self::$instance) {
            self::$instance = new self($options);
        }
        return self::$instance;
    }

    /**
     * @return \PDOStatement|null
     */
    public static function getStmt() {
        return self::$stmt;
    }

    /**
     * @return string|null
     */
    public static function getLastQuery() {
        return self::$lastQuery;
    }

    /**
     * @param string $method
     * @param array $args
     *
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        return call_user_func_array([self::$db, $method], $args);
    }

    /**
     * @return bool
     */
    public static function isDebug()
    {
        return self::$debug;
    }

    /**
     * @param bool $debug
     */
    public static function setDebug($debug)
    {
        self::$debug = $debug;
    }

    /**
     * @param string $table
     * @param array $data
     *
     * @return bool
     */
    public function insert($table, array $data = [])
    {
        if (empty($table) || empty($data) || !is_array($data)) {
            return false;
        }

        $simpleCnt = count($data);
        $recursiveCnt = count($data, COUNT_RECURSIVE);
        if ($simpleCnt == $recursiveCnt) {
            $data = [$data];
        }

        $firstRow = current($data);
        $columns = array_keys($firstRow);
        $sql = 'INSERT INTO `'.$table.'` (`'.implode('`,`', $columns).'`) VALUES ';
        $rowPlaceholdersBlock = '('.substr(str_repeat('?,', count($columns)), 0, -1).'),';
        $sql .= substr(str_repeat($rowPlaceholdersBlock, count($data)), 0, -1);

        $allValues = [];
        foreach ($data as $row) {
            $allValues = array_merge($allValues, array_values($row));
        }

        return $this->executeSql($sql, $allValues);
    }

    /**
     * @param string $table
     * @param string|array $fields
     * @param array $condition
     * @param array $params
     *
     * @return bool
     */
    public function select($table, $fields = '*', $condition = [], $params = []) {
        if (is_array($fields)) {
            $fields = implode(',', $fields);
        }

        $sql = 'SELECT ' . $fields . ' FROM `' . $table . '`';

        $where = $this->parseFields($condition, ' AND ');
        if (!empty($where)) {
            $sql .= ' WHERE ' . $where;
        }

        $formattedParams = $this->parseParams($params);
        if (!empty($formattedParams)) {
            $sql .= $formattedParams;
        }

        return $this->executeSql($sql);
    }

    /**
     * @param $table
     * @param array $data
     * @param string|array $condition
     * @return bool
     */
    public function update($table, array $data = [], $condition = [])
    {
        $sql = 'UPDATE `' . $table . '` SET ' . $this->parseFields($data, ', ');

        $where = $this->parseFields($condition, ' AND ');
        if (!empty($where)) {
            $sql .= ' WHERE ' . $where;
        }

        return $this->executeSql($sql);
    }

    /**
     * @param string $sql
     * @param array $params
     * @return bool
     */
    public function executeSql($sql, array $params = []) {
        /** @var \PDOStatement $stmt */
        $stmt = self::$db->prepare($sql);
        self::$lastQuery = $this->parseSqlParams($sql, $params);
        $result = false;
        try {
            if (!self::isDebug()) {
                $result = $stmt->execute($params);
            }
            self::$stmt = $stmt;
        } catch (\PDOException $e){
            Log::error($e->getMessage());
        }
        return $result;
    }

    /**
     * @param string $table
     * @param string|array $condition
     * @return bool
     */
    public function delete($table, $condition = [])
    {
        $sql = 'DELETE FROM `' . $table . '`';

        $where = $this->parseFields($condition, ' AND ');
        if (!empty($where)) {
            $sql .= ' WHERE ' . $where;
        }

        return $this->executeSql($sql);
    }

    /**
     * @param string|array $params
     * @return string
     */
    protected function parseParams($params = []) {
        $strParams = '';
        if (!empty($params)) {
            if (is_string($params)) {
                $strParams = $params;
            } elseif (is_array($params)) {
                $sortedParams = [
                    'GROUP' => '',
                    'HAVING' => '',
                    'ORDER' => '',
                    'LIMIT' => '',
                ];
                foreach ($params as $operator => $values) {
                    if (!empty($values)) {
                        switch (strtoupper($operator)) {
                            case 'GROUP':
                                $sortedParams['GROUP'] = 'GROUP BY ' . '`' . implode('`,`', $values) . '`';
                                break;
                            case 'HAVING':
                                $sortedParams['HAVING'] = 'HAVING ' . $this->parseFields($values, ' AND ');
                                break;
                            case 'ORDER':
                                $strParams .= 'ORDER BY ';
                                $orderAr = [];
                                foreach ($values as $fieldName => $ascDesc) {
                                    $orderAr[] = '`' . $fieldName . '` ' . $ascDesc;
                                }
                                $sortedParams['ORDER'] .= implode(',', $orderAr);
                                break;
                            case 'LIMIT':
                                $sortedParams['LIMIT'] = 'LIMIT ' . $values;
                                break;
                        }
                    }
                }
                $strParams .= implode(PHP_EOL, $sortedParams);
            }
        }
        return $strParams;
    }

    /**
     * @param string|array $condition
     * @param string $separator
     * @return string
     */
    protected function parseFields($condition, $separator = ', ')
    {
        if (empty($condition)) {
            return '';
        }
        if (is_string($condition)) {
            return $condition;
        } elseif (is_array($condition)) {
            $condAr = [];
            foreach ($condition as $fieldName => $fieldValue) {
                $condAr[] = '`' . $fieldName . '` = ' . self::$db->quote($fieldValue);
            }
            return implode($separator, $condAr);
        }
        return '';
    }

    /**
     * @param string $string
     * @param array $data
     *
     * @return string
     */
    protected function parseSqlParams($string, $data = []) {

        $indexed = $data == array_values($data);
        foreach ($data as $k => $v) {
            if ($indexed) {
                $string = preg_replace('/\?/', self::$db->quote($v), $string,1);
            } else {
                $string = str_replace(':' . $k, self::$db->quote($v), $string);
            }
        }

        return $string;
    }

    private function __clone(){}
    private function __wakeup(){}
}