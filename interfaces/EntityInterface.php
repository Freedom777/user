<?php

namespace Interfaces;

interface EntityInterface {
    /**
     * @param string|array $condition
     * @param array $params
     *
     * @return array
     */
    public function get($condition = [], $params = []);

    /**
     * @param array $data
     * @param string|array $condition
     *
     * @return array
     */
    public function edit(array $data = [], $condition = []);

    /**
     * @param array $data
     *
     * @return array
     */
    public function add(array $data = []);

    /**
     * @param string|array $condition
     *
     * @return boolean
     */
    public function delete($condition = []);

    /**
     * @param array $data
     *
     * @return boolean
     */
    public function validate(array $data = []);
}
