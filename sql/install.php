<?php

use Cargus\CargusCache;
use Cargus\CargusClass;
use Cargus\CargusCTTD;
//use Configuration;

$sql = [];

function load_CTTD_into_database()
{
    $excelFilePath = _PS_MODULE_DIR_ . "/cargus/CTTD.xlsx"; // Adjust the path to your Excel file

    if (!file_exists($excelFilePath)) {
        return false; // Excel file not found
    }

    require_once _PS_MODULE_DIR_ . "cargus/vendor/autoload.php"; // Include the PhpSpreadsheet library

    // Load the Excel file
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($excelFilePath);
    $worksheet = $spreadsheet->getActiveSheet();

    // Assuming the first row contains column headers, extract them
    $headers = [];
    foreach ($worksheet->getRowIterator() as $row) {
        foreach ($row->getCellIterator() as $cell) {
            $headers[] = $cell->getValue();
        }
        break;
    }

    // Create an associative array with column headers as keys
    $formattedData = [];

    // add to database start
    // Database connection
    $db = \Db::getInstance();

    // Your custom table name
    $tableName = _DB_PREFIX_ . "cargus_CTTD";

    // Truncate the table to clear previous data if needed
    $db->execute("TRUNCATE TABLE " . $tableName);

    // add to database end
    $batchSize = 1000;
    $batchData = [];
    $rowCount = 0;
    foreach ($worksheet->getRowIterator() as $row) {
        $rowData = [];
        foreach ($row->getCellIterator() as $cell) {
            // Check if the cell contains a time format and format it as HH:mm:ss
            $value = $cell->getValue();
            if (
                $cell->getDataType() ===
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC
            ) {
                $formattedTime = gmdate("H:i:s", ($value - 25569) * 86400);

                // Check if the cell is a time column based on its column index
                $isTimeColumn = in_array($cell->getColumn(), [
                    "H",
                    "I",
                    "J",
                    "K",
                ]);

                if ($isTimeColumn) {
                    $rowData[] = $formattedTime; // Format as time
                } else {
                    $rowData[] = $value; // Keep the original value for other columns
                }
            } else {
                $rowData[] = $value; // Keep the original value for non-numeric cells
            }
        }

        // Skip the row if 'IdOras' is not set or empty
        if (empty($rowData[0])) {
            continue;
        }

        $batchData[] = $rowData;
        $rowCount++;

        // Insert the batch into the database if it reaches the batch size
        if ($rowCount % $batchSize === 0) {
            $insertQuery =
                "INSERT INTO " .
                $tableName .
                ' 
            (IdOras, IdParinte, Localitate, Agentie, Judet, Distanta, DistantaReala, OraStartPreluare, OraEndPreluare, OraMaximaInregistrareComanda, IntervalPreluareDefault, LivrareSambata, TransitTimeStandard, TransitTimePalet, Pre10FromB, Pre12FromB, Pre10FromT, Pre12FromT, After17, Ruta, CodPostal)
            VALUES ';

            $values = [];
            foreach ($batchData as $row) {
                $values[] = "('" . implode("', '", $row) . "')";
            }

            $insertQuery .= implode(",", $values);
            $db->execute($insertQuery);

            $batchData = [];
        }
    }

    // Insert the remaining data into the database
    if (!empty($batchData)) {
        $insertQuery =
            "INSERT INTO " .
            $tableName .
            ' 
        (IdOras, IdParinte, Localitate, Agentie, Judet, Distanta, DistantaReala, OraStartPreluare, OraEndPreluare, OraMaximaInregistrareComanda, IntervalPreluareDefault, LivrareSambata, TransitTimeStandard, TransitTimePalet, Pre10FromB, Pre12FromB, Pre10FromT, Pre12FromT, After17, Ruta, CodPostal)
        VALUES ';

        $values = [];
        foreach ($batchData as $row) {
            $values[] = "('" . implode("', '", $row) . "')";
        }

        $insertQuery .= implode(",", $values);
        $db->execute($insertQuery);
    }

    // Create an index on the Local//itate column
    $createLocalitateIndexQuery =
        "CREATE INDEX idx_Localitate ON " . $tableName . " (Localitate)";
    $db->execute($createLocalitateIndexQuery);

    // Create an index on the Judet column
    $createJudetIndexQuery =
        "CREATE INDEX idx_Judet ON " . $tableName . " (Judet)";
    $db->execute($createJudetIndexQuery);

    return true;
}

$sql[] =
    "CREATE TABLE IF NOT EXISTS `" .
    _DB_PREFIX_ .
    'awb_urgent_cargus` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `order_id` int(11) NOT NULL,
            `pickup_id` int(11) NOT NULL,
            `name` varchar(64) NOT NULL,
            `locality_id` int(11) NOT NULL,
            `locality_name` varchar(128) NOT NULL,
            `county_id` int(11) NOT NULL,
            `county_name` varchar(128) NOT NULL,
            `street_id` int(11) NOT NULL,
            `street_name` varchar(128) NOT NULL,
            `number` varchar(32) NOT NULL,
            `address` varchar(256) NOT NULL,
            `postal_code` varchar(256) NOT NULL,
            `contact` varchar(64) NOT NULL,
            `phone` varchar(32) NOT NULL,
            `email` varchar(96) NOT NULL,
            `parcels` int(11) NOT NULL,
            `envelopes` int(11) NOT NULL,
            `weight` int(11) NOT NULL,
            `length` int(11) NOT NULL,
            `width` int(11) NOT NULL,
            `height` int(11) NOT NULL,
            `value` double NOT NULL,
            `cash_repayment` double NOT NULL,
            `bank_repayment` double NOT NULL,
            `other_repayment` varchar(256) NOT NULL,
            `payer` tinyint(1) NOT NULL,
            `morning_delivery` tinyint(1) NOT NULL,
            `saturday_delivery` tinyint(1) NOT NULL,
	        `openpackage` tinyint(1) NOT NULL,
            `observations` varchar(256) NOT NULL,
            `contents` varchar(256) NOT NULL,
            `barcode` varchar(50) NOT NULL,
            `ReturnCode` VARCHAR(50) NULL,
            `ReturnAwb` VARCHAR(50) NULL,
            `pudo_location_id` int(11) NOT NULL,
            `delivery_time` int(11) NOT NULL,
            `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
            ) ENGINE=' .
    _MYSQL_ENGINE_ .
    " DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "cart` ADD COLUMN pudo_location_id INT;";
$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "cart` ADD COLUMN delivery_time INT;";
$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "cart` ADD COLUMN saturday_delivery BOOLEAN;";
$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "orders` ADD COLUMN pudo_location_id INT;";
$sql[] =  "ALTER TABLE `" . _DB_PREFIX_ . "orders` ADD COLUMN delivery_time INT;";
$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "orders` ADD COLUMN saturday_delivery BOOLEAN NOT NULL DEFAULT false;";

$sql[] = "CREATE TABLE IF NOT EXISTS `" .
_DB_PREFIX_ .
    'cargus_street` (
            `id_address` int(11) NOT NULL,
            `street` varchar(255) NULL,
            `street_number` varchar(60) NULL,
            PRIMARY KEY  (`id_address`)
        ) ENGINE=' .
    _MYSQL_ENGINE_ .
    " DEFAULT CHARSET=utf8;";

$sql[] =
    "CREATE TABLE IF NOT EXISTS `" .
    _DB_PREFIX_ .
    'cargus_CTTD` (
    `IdOras` INT NOT NULL,
    `IdParinte` INT,
    `Localitate` VARCHAR(255),
    `Agentie` VARCHAR(255),
    `Judet` VARCHAR(255),
    `Distanta` DECIMAL(10, 2),
    `DistantaReala` DECIMAL(10, 2),
    `OraStartPreluare` TIME,
    `OraEndPreluare` TIME,
    `OraMaximaInregistrareComanda` TIME,
    `IntervalPreluareDefault` INT,
    `LivrareSambata` TINYINT(1),
    `TransitTimeStandard` INT,
    `TransitTimePalet` INT,
    `Pre10FromB` BOOLEAN,
    `Pre12FromB` BOOLEAN,
    `Pre10FromT` BOOLEAN,
    `Pre12FromT` BOOLEAN,
    `After17` TINYINT(1),
    `Ruta` VARCHAR(255),
    `CodPostal` VARCHAR(255),
    PRIMARY KEY (`IdOras`)
)';

$success = true;

foreach ($sql as $query) {
    PrestaShopLogger::addLog("Executing query: $query", 1);

    if (Db::getInstance()->execute($query) == false) {
        PrestaShopLogger::addLog("Error executing query: $query", 3);
        $success = false; // Set a flag to indicate failure, but continue with the next query
    }

    PrestaShopLogger::addLog("Query executed successfully: $query", 1);
}
function create_shipping_manager_dependency_table() {
    $db = Db::getInstance();
    $db->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'smanager_data` (
            `cart_id` varchar(255)  NOT NULL,
            `data` TEXT  NOT NULL,
            `data_carrier` TEXT  NOT NULL,
            PRIMARY KEY (`cart_id`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8');
}

create_shipping_manager_dependency_table();
load_CTTD_into_database();
