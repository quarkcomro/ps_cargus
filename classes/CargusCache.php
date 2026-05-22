<?php

namespace Cargus;

class CargusCache
{
    public const FILE_THRESHOLD_SECONDS = 24 * 3600; // If file wasn't modified for > 24h (24*3600s)

    public const CACHE_DIR = _PS_CACHE_DIR_ . 'cargus/';

    public function __construct()
    {
        clearstatcache();

        // check if cache dir is usable
        // create dir
        if (!is_dir(self::CACHE_DIR) &&
            !mkdir(self::CACHE_DIR, 0775)
        ) {
            $msg = __CLASS__ . '::' . __FUNCTION__ . ' Unable to create cache dir: ' . self::CACHE_DIR;
            CargusLog::logError($msg);
        }

        if (!is_writable(self::CACHE_DIR)) {
            $msg = __CLASS__ . '::' . __FUNCTION__ . ' Unable to write in cache dir: ' . self::CACHE_DIR;
            CargusLog::logError($msg);
        }
    }

    public function getCacheFile($filename, $alsoExpired = false)
    {
        clearstatcache();

        $file = self::CACHE_DIR . $filename;

        // check if file is present in cache dir
        if (!file_exists($file)) {
            return false;
        }

        // check if expired or updating
        $elapsed = time() - filemtime($file);
        if ($elapsed >= self::FILE_THRESHOLD_SECONDS && !$alsoExpired) {
            return false;
        }

        // get data
        $data = \Tools::file_get_contents($file);

        if ($data === false) {
            $msg = __CLASS__ . '::' . __FUNCTION__ . " Error reading file: $file";
            CargusLog::logError($msg);

            return false;
        }

        if (strlen($data) <= 2) {
            $msg = __CLASS__ . '::' . __FUNCTION__ . " File was empty: $file, data=$data";
            CargusLog::logError($msg);

            // try to remove file
            if (!unlink($file)) {
                $msg = __CLASS__ . '::' . __FUNCTION__ . " Unable to delete file: $file";
                CargusLog::logError($msg);
            }

            return false;
        }

        return $data;
    }

    public function writeCacheFile($filename, $json)
    {
        clearstatcache();

        $file = self::CACHE_DIR . $filename;

        // Write file
        $fp = @fopen($file, 'w');

        if ($fp === false) {
            $msg = __CLASS__ . '::' . __FUNCTION__ . " Unable to write file: $file";
            CargusLog::logError($msg);

            return false;
        }

        $status = true;

        if (fwrite($fp, $json) === false) {
            $msg = __CLASS__ . '::' . __FUNCTION__ . " Writing to file $file failed, data=$json";
            CargusLog::logError($msg);

            $status = false;
        }

        fclose($fp);

        return $status;
    }

    public function writeRawCacheFile($filename, $data)
    {
        clearstatcache();

        $file = self::CACHE_DIR . $filename;

        // Ensure $data is an array
        if (!is_array($data)) {
            $msg = __CLASS__ . '::' . __FUNCTION__ . " Invalid data type, expected array for file: $file";
            CargusLog::logError($msg);
            return false;
        }

        // Write file
        $fp = @fopen($file, 'w');

        if ($fp === false) {
            $msg = __CLASS__ . '::' . __FUNCTION__ . " Unable to write file: $file";
            CargusLog::logError($msg);
            return false;
        }

        $status = true;

        // Write the array data to the file without any serialization
        foreach ($data as $line) {
            if (fwrite($fp, implode("\t", $line) . PHP_EOL) === false) {
                $msg = __CLASS__ . '::' . __FUNCTION__ . " Writing to file $file failed, data=" . print_r($data, true);
                CargusLog::logError($msg);
                $status = false;
                break;
            }
        }

        fclose($fp);

        return $status;
    }


    public function getStreets($city = null)
    {
        if (is_null($city)) {
            return '[]';
        }

        // get file from cache
        $json = $this->getCacheFile('str' . $city);

        if ($json === false) {
            // file not found in cache

            // get fresh data
            $uc = new CargusClass(
                \Configuration::get('CARGUS_API_URL'),
                \Configuration::get('CARGUS_API_KEY')
            );

            $data = $uc->getStreets($city);

            $json = json_encode($data);

            // save data to cache
            $this->writeCacheFile('str' . $city, $json);
        }

        // return json data
        return $json;
    }

    public function getCounties()
    {
        // get file from cache
        $json = $this->getCacheFile('counties');

        if ($json === false) {
            // file not found in cache

            // get fresh data
            $uc = new CargusClass(
                \Configuration::get('CARGUS_API_URL'),
                \Configuration::get('CARGUS_API_KEY')
            );

            $data = $uc->getCounties();

            $json = json_encode($data);

            // save data to cache
            $this->writeCacheFile('counties', $json);
        }

        // return data
        return json_decode($json, true);
    }

    public function getCTTD() {


//        $excelCacheKey = 'cttd_data';
//        $cachedData = $this->getCacheFile($excelCacheKey);
        // TODO: WRITE RAW CACHE, NOT ARRAY
//        if ($cachedData !== false) {
//            return $cachedData;
//        } else {

            $cargus = new CargusClass(
                \Configuration::get('CARGUS_API_URL'),
                \Configuration::get('CARGUS_API_KEY')
            );

            $data = $cargus->getCTTD();
            $this->writeRawCacheFile('cttd_data', $data);


        return $data;

    }

    public function getLocalities($countyId = null)
    {
        if (is_null($countyId)) {
            return [];
        }

        // get file from cache
        $json = $this->getCacheFile('localities' . $countyId);

        if ($json === false) {
            // file not found in cache

            // get fresh data
            $uc = new CargusClass(
                \Configuration::get('CARGUS_API_URL'),
                \Configuration::get('CARGUS_API_KEY')
            );

            $data = $uc->getLocalities($countyId);

            $json = json_encode($data);

            // save data to cache
            $this->writeCacheFile('localities' . $countyId, $json);
        }

        // return json data
        return $json;
    }

    public function getPudoPoints()
    {
        // get file from cache
        $json = $this->getCacheFile('pudopoints');

        if ($json === false) {
            // file not found in cache

            // get fresh data
            $uc = new CargusClass(
                \Configuration::get('CARGUS_API_URL'),
                \Configuration::get('CARGUS_API_KEY')
            );

            $data = $uc->getPudoPoints();

            $json = json_encode($data);

            // save data to cache
            $this->writeCacheFile('pudopoints', $json);
        }

        // return json data
        return $json;
    }
}
