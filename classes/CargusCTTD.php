<?php

namespace Cargus;
use DbQuery;
require_once(_PS_ROOT_DIR_ . '/config/config.inc.php');
require_once(_PS_ROOT_DIR_ . '/init.php');
class CargusCTTD {
    private $data = null;
    private static $instance = null; // Store the singleton instance
    public function __construct() {
        $this->resetData(); // Reset data before loading new data
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function resetData() {
        $this->data = null;
    }


    public function getData() {
        return $this->data;
    }

    public function SearchByJudetAndLocalitate($judet, $localitate) {

        if( $judet == null || $localitate == null ) {
            return null;
        }
        // Sanitize the input to prevent SQL injection
        $sanitizedJudet = pSQL($judet);
        $sanitizedLocalitate = pSQL($localitate);

        // Create a new DBQuery object
        $dbQuery = new DbQuery();

        // Define the table name
        $tableName = 'cargus_CTTD';

        // Define the columns you want to retrieve
        $columns = '*'; // All columns

        // Build the query with a WHERE condition for filtering by Judet and Localitate
        $dbQuery->select($columns)
            ->from($tableName)
            ->where("Judet = '{$sanitizedJudet}' AND Localitate = '{$sanitizedLocalitate}'");

        $sql = $dbQuery->build();

        // Execute the query and fetch the data
        $data = \Db::getInstance()->executeS($sql);

        if ($data && count($data) > 0) {
            // Since you expect at most one result, return the first (and only) result
            return $data[0];
        } else {
            // No data found
            return false;
        }
    }

    public function informatii_localitate($judet, $localitate) {
        // Convert the input strings to lowercase (or uppercase)
        $judet = strtolower($judet);
        $localitate = strtolower($localitate);


        $data = $this->SearchByJudetAndLocalitate($judet, $localitate);

        if (is_array($data)) {
            return (object) [
                'OraMaximaInregistrareComanda' => $data['OraMaximaInregistrareComanda'],
                'Pre10FromB' => $data['Pre10FromB'],
                'Pre12FromB' => $data['Pre12FromB'],
                'Pre10FromT' => $data['Pre10FromT'],
                'Pre12FromT' => $data['Pre12FromT'],
                'LocalityId' => $data['IdOras'],
            ];
        } else {
            return null;
        }

    }


}
