<?php

// Uninstallation script
$queries = [
    'ALTER TABLE `' . _DB_PREFIX_ . 'cart` DROP COLUMN saturday_delivery;',
    'ALTER TABLE `' . _DB_PREFIX_ . 'orders` DROP COLUMN saturday_delivery;',
    'ALTER TABLE `' . _DB_PREFIX_ . 'cart` DROP COLUMN pudo_location_id;',
    'ALTER TABLE `' . _DB_PREFIX_ . 'orders` DROP COLUMN pudo_location_id;',
    'ALTER TABLE `' . _DB_PREFIX_ . 'cart` DROP COLUMN delivery_time;',
    'ALTER TABLE `' . _DB_PREFIX_ . 'orders` DROP COLUMN delivery_time;',
    'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'awb_urgent_cargus`',
    'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'cargus_street`',
    'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'cargus_CTTD`',
    'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'smanager_data`',
];

$db = Db::getInstance();
$errorMessages = [];

foreach ($queries as $query) {
    try {
        $db->execute($query);
    } catch (Exception $e) {
        // Log the error message
        PrestaShopLogger::addLog("Error executing query: $query. Error: " . $e->getMessage(), 3);
        $errorMessages[] = "Error executing query: $query";
    }
}

if (!empty($errorMessages)) {
    // Return false if any errors occurred
    return false;
}

// Return true if all queries executed successfully
return true;
