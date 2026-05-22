<?php

/**
 * Return pudo points json
 */
class cargusgetPudoPointsModuleFrontController extends ModuleFrontController
{
    public function initHeader()
    {
        header('Content-Type: application/json');

        // call original method, to maintain default behaviour:
        return parent::initHeader();
    }

    public function display()
    {
        $cache = new \Cargus\CargusCache();

        // PudoPoints
        $json = $cache->getPudoPoints();

        // data is already json
        exit($json);
    }
}
