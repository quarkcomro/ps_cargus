<?php

namespace Cargus;

class CargusLog
{
    public static function logError($message)
    {
        $logger = new \FileLogger();
        $logger->setFilename(_PS_ROOT_DIR_ . '/var/logs/' . date('Ymd') . '_cargus.log');
        $logger->logError($message);
    }

    public static function logDebug($message)
    {
        $logger = new \FileLogger(0);
        $logger->setFilename(_PS_ROOT_DIR_ . '/var/logs/' . date('Ymd') . '_cargus.log');
        $logger->logDebug($message);
    }

    public static function logInfo($message)
    {
        $logger = new \FileLogger();
        $logger->setFilename(_PS_ROOT_DIR_ . '/var/logs/' . date('Ymd') . '_cargus.log');
        $logger->logInfo($message);
    }

    public static function logWarning($message)
    {
        $logger = new \FileLogger();
        $logger->setFilename(_PS_ROOT_DIR_ . '/var/logs/' . date('Ymd') . '_cargus.log');
        $logger->logWarning($message);
    }
}
