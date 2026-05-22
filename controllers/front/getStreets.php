<?php

class CargusGetStreetsModuleFrontController extends ModuleFrontController
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

        $city = Tools::getValue('city');

        $json = $cache->getStreets($city);

        // data is already json
        exit($json);
    }
}
