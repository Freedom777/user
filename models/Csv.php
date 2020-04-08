<?php

namespace Models;

use Interfaces\EntityInterface;
use Interfaces\DatasourceInterface;

class Csv implements DatasourceInterface {
    protected $limit = 1;
    protected $entity = null;
    protected $fieldMap = [];

    const FIELD_SEPARATOR = ';';
    const ESCAPE_STRINGS = '"';

    /**
     * Csv constructor.
     * @param EntityInterface $entity
     */
    public function __construct(EntityInterface $entity)
    {
        $this->entity = $entity;
        $this->fieldMap = $entity::CSV_MAPPING;
    }

    /**
     * @param $filename
     *
     * @return bool
     */
    public function loadToDb ($filename) {
        $limit = $this->limit;
        $result = false;

        if (($handle = fopen($filename, 'r')) === false) {
            Log::error('Error open:' . $filename);
        } else {
            $insertData = [];
            $curPartRowCnt = 0;
            while (($rowAr = fgetcsv($handle, null, self::FIELD_SEPARATOR, self::ESCAPE_STRINGS)) !== false) {
                $mappedDataAr = $this->map($rowAr, $this->fieldMap);
                if (!empty($mappedDataAr)) {
                    if (!$this->entity->validate($mappedDataAr)) {
                        Log::notice('Error with data: ' . var_export($mappedDataAr, true));
                    } else {
                        $insertData[$curPartRowCnt++] = $mappedDataAr;

                        if ($curPartRowCnt >= $limit) {
                            $this->write($insertData);
                            $curPartRowCnt = 0;
                            $insertData = [];
                        }
                    }
                }
            }
            fclose($handle);
            $this->write($insertData);
            $result = true;
        }

        return $result;
    }

    /**
     * @param array $rowAr
     * @param array $fieldMappingAr
     * @return array|bool
     */
    public function map($rowAr, $fieldMappingAr) {
        if (max($fieldMappingAr) > (count($rowAr)-1)) {
            return false;
        }

        $result = [];
        foreach ($fieldMappingAr as $fieldName => $fieldCsvIndex) {
            if (!isset($rowAr[$fieldCsvIndex])) {
                return false;
            }
            $result[$fieldName] = $rowAr[$fieldCsvIndex];
        }

        return $result;
    }

    /**
     * @param array $data
     */
    public function write($data) {
        if (!empty($data)) {
            $this->entity->add($data);
        }
    }
}