<?php

namespace Cargus;

// login, getStreets, getCounties, getLocalities, getPudoPoints

class CargusClass
{
    private static $instance;
    private $key;
    private $curl;
    public $url;
    private $cargusapitoken;

    public function __construct($url, $key)
    {
        $this->url = $url;
        $this->key = $key;

        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 300);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 300);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
    }

//getInstance method is used to create or retrieve the 
//single instance of the CargusClass. This ensures that only one instance 
//is created and reused across multiple method calls.

    public static function getInstance($url, $key)
    {
        if (self::$instance === null) {
            self::$instance = new self($url, $key);
        }

        return self::$instance;
    }
    
    public function CallMethod($function, $parameters = '', $verb, $token = null)
    {
        error_reporting(E_ALL);
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');

        $json = json_encode($parameters);

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $json);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $verb);
        curl_setopt($this->curl, CURLOPT_URL, $this->url . '/' . $function);

        //\Cargus\CargusLog::logDebug('API function: '. print_r($function, true));

        if ($function == 'LoginUser') {
            $headers = [
                'Ocp-Apim-Subscription-Key: ' . $this->key,
                'Ocp-Apim-Trace: true',
                'Content-Type: application/json',
                'ContentLength: ' . strlen($json),
            ];
        } else {
            $headers = [
                'Ocp-Apim-Subscription-Key: ' . $this->key,
                'Ocp-Apim-Trace: true',
                'Authorization: Bearer ' . ($token == 'useException' ? $this->cargusapitoken : $token),
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json),
            ];

            if ($function == 'Awbs' && $verb == 'POST') {
                $headers[] = 'Path: PS' . substr(str_replace('.', '', _PS_VERSION_), 0, 3);
            }
        }

        curl_setopt(
            $this->curl,
            CURLOPT_HTTPHEADER,
            $headers
        );

        $result = curl_exec($this->curl);
        $header = curl_getinfo($this->curl);

        $data = json_decode($result, true);
        $status = $header['http_code'];

        if ($status == '200') {
            if (is_array($data) && isset($data['message'])) {
                return $data['message'];
            } else {
                return $data;
            }
        } elseif ($status == '204') {
            return null;
        } else {
            $message = print_r([
                'url' => $this->url,
                'key' => $this->key,
                'status' => $status,
                'method' => $function,
                'verb' => $verb,
                'token' => $token,
                'params' => $parameters,
                'data' => $data,
            ], true) . "\n\n";
            $message .= 'CURL Error=' . print_r(curl_error($this->curl), true) . "\n\n";
            $message .= 'CURL Result=' . print_r($result, true) . "\n\n";
            $message .= 'trace=' . print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), true) . "\n\n";

            CargusLog::logError($message);

            if ($function == 'LoginUser' && $token == 'test') {
                throw new \Exception(is_array($data) ? print_r($data, true) : $data, $status);
            }
            if ($token == 'useException') {
                throw new \Exception(is_array($data) ? print_r($data, true) : $data, $status);
            }

            if (isset($data) && is_array($data) && !empty($data)) {
                return ['error' => implode(', ', $data)];
            }
            if (isset($data)) {
                return ['error' => $data];
            }

            return false;
        }
    }

    public function login()
    {
        if (!is_null($this->cargusapitoken)) {
            return $this->cargusapitoken;
        }

        // login user
        $fields = [
            'UserName' => \Configuration::get('CARGUS_USERNAME'),
            'Password' => \Configuration::get('CARGUS_PASSWORD'),
        ];

        try {
            $token = $this->CallMethod('LoginUser', $fields, 'POST', 'useException');
            $this->cargusapitoken = $token;

            return $token;
        } catch (\Exception $e) {
            // login error
            $message = __CLASS__ . '::' . __FUNCTION__ . ' error: ' . $e->getMessage();

            CargusLog::logError($message);
        }

        return null;
    }

    public function getLocalityInfo($localityId)
    {
        // get streets
        if (is_null($localityId)) {
            return [];
        }

        // login
        $this->login();

        $json = [];

        // get api json
        // Streets?localityId=97243
        try {
            $json = $this->CallMethod('Localities/DetailsLocality?LocalityId=' . $localityId, [], 'GET', 'useException');
        } catch (\Exception $e) {
            $message = __CLASS__ . '::' . __FUNCTION__ . ' localityId=' . $localityId . ', error: ' . $e->getMessage();

            CargusLog::logError($message);

            // get old cache if exists
            $cache = new CargusCache();

            $temp = $cache->getCacheFile('deliveryInfo' . $localityId, true);

            if ($temp !== false) {
                // return the old data
                $json = json_decode($temp, true);
            }
        }

        return $json;
    }

    public function getStreets($localityId)
    {
        // get streets
        if (is_null($localityId)) {
            return [];
        }

        // login
        $this->login();

        $json = [];

        // get api json
        // Streets?localityId=97243
        try {
            $json = $this->CallMethod('Streets?localityId=' . $localityId, [], 'GET', 'useException');
        } catch (\Exception $e) {
            $message = __CLASS__ . '::' . __FUNCTION__ . ' localityId=' . $localityId . ', error: ' . $e->getMessage();

            CargusLog::logError($message);

            // get old cache if exists
            $cache = new CargusCache();

            $temp = $cache->getCacheFile('str' . $localityId, true);

            if ($temp !== false) {
                // return the old data
                $json = json_decode($temp, true);
            }
        }

        return $json;
    }

    public function getCTTD() {

        $excelFilePath = _PS_MODULE_DIR_ . '/cargus/CTTD.xlsx'; // Adjust the path to your Excel file

        if (!file_exists($excelFilePath)) {
            return false; // Excel file not found
        }

        require_once _PS_MODULE_DIR_ . 'cargus/vendor/autoload.php'; // Include the PhpSpreadsheet library

        // Load the Excel file
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($excelFilePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = [];

        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $rowData = [];

            foreach ($cellIterator as $cell) {
                $rowData[] = $cell->getValue();
            }

            $data[] = $rowData;
        }

        // At this point, $data will contain your Excel data as a multidimensional array.
        // You can now process and return the data as needed.
        return $data;
    }

    public function getCountyNameById($countyid) {
    $counties = $this->getCounties();
    foreach ($counties as $locality) {
        if ($locality['CountyId'] == $countyid) {
            return $locality['Name'];
            }
    }
    // Return a default value or handle not found case
    return false;
    }

    public function getCounties()
    {
        // login
        $this->login();

        $json = [];

        // get api json
        // Counties?countryId=1
        try {
            $json = $this->CallMethod('Counties?countryId=1', [], 'GET', 'useException');
        } catch (\Exception $e) {
            $message = __CLASS__ . '::' . __FUNCTION__ . ' error: ' . $e->getMessage();

            CargusLog::logError($message);

            // get old cache if exists
            $cache = new CargusCache();

            $temp = $cache->getCacheFile('counties', true);

            if ($temp !== false) {
                // return the old data
                $json = json_decode($temp, true);
            }
        }

        return $json;
    }

    public function getLocalities($countyId)
    {
        if (is_null($countyId)) {
            return [];
        }

        // login
        $this->login();

        $json = [];

        // get api json
        // Localities?countryId=1&countyId=97243
        try {
            $json = $this->CallMethod('Localities?countryId=1&countyId=' . $countyId, [], 'GET', 'useException');
        } catch (\Exception $e) {
            $message = __CLASS__ . '::' . __FUNCTION__ . ' countyId=' . $countyId . ', error: ' . $e->getMessage();

            CargusLog::logError($message);

            // get old cache if exists
            $cache = new CargusCache();

            $temp = $cache->getCacheFile('localities' . $countyId, true);

            if ($temp !== false) {
                // return the old data
                $json = json_decode($temp, true);
            }
        }

        return $json;
    }
    

    public function getPudoPoints()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');

        // login
        $this->login();

        $json = [];

        // get api json
        // PudoPoints
        try {
            $json = $this->CallMethod('PudoPoints', [], 'GET', 'useException');
        } catch (\Exception $e) {
            $message = __CLASS__ . '::' . __FUNCTION__ . ' error: ' . $e->getMessage();

            CargusLog::logError($message);

            // get old cache if exists
            $cache = new CargusCache();

            $temp = $cache->getCacheFile('pudopoints', true);

            if ($temp !== false) {
                // return the old data
                $json = json_decode($temp, true);
            }
        }

        return $json;
    }
}
