<?php

namespace Interfaces;

interface DBInterface
{
    public static function getInstance();

    public function insert($table, array $data = []);
    public function update($table, array $data = [], $condition = []);
    public function select($table, $fields = '*', $condition = [], $params = []);
    public function delete($table, $condition = []);
}