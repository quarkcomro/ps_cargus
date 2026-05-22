<?php

class CargusGetCitiesModuleFrontController extends ModuleFrontController
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

        $state = Tools::getValue('state');

        if (is_numeric(addslashes($state))) {
            $states = State::getStatesByIdCountry(36);
            $state_ISO = null;
            foreach ($states as $s) {
                if ($s['id_state'] == addslashes($state)) {
                    $state_ISO = trim(strtolower($s['iso_code']));
                }
            }
        } else {
            $state_ISO = addslashes(trim(strtolower($state)));
        }

        // get list of counties
        $counties = $cache->getCounties();

        foreach ($counties as $c) {
            if (trim(strtolower($c['Abbreviation'])) == $state_ISO) {
                $countyId = trim($c['CountyId']);
            }
        }

        // get list of localities using county code
        $json = $cache->getLocalities($countyId);

        // data is already json
        exit($json);
    }
}
