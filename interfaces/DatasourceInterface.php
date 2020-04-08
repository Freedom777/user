<?php

namespace Interfaces;

interface DatasourceInterface {
    public function __construct(EntityInterface $entity);
}