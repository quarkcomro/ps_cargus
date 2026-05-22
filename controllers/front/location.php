<?php

use Cargus\CargusLog;

class CargusLocationModuleFrontController extends ModuleFrontController
{
    /**
     * CargusAdminController constructor.
     *
     * @throws PrestaShopException
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        parent::init();
    }

    public function update_smdata($locationId) {

        $db = Db::getInstance();
        $cart = $this->context->cart;
        $cartId = $cart->id;
        $carrier_id = Configuration::get('CARGUS_SM_CARRIER_ID');
        $sm_data = $db->getValue('
            SELECT `cart_id`
            FROM `' . _DB_PREFIX_ . 'smanager_data`
            WHERE `cart_id` = "' .$cartId. '"'
        );

        if ($sm_data) {
            $db->execute('
        UPDATE `' . _DB_PREFIX_ . 'smanager_data`
        SET
            `data_carrier` = ' . (int) $carrier_id . '
            `data` = ' . (int) $locationId . '
        WHERE `cart_id` = "' . pSQL($cartId) . '"'
            );
        } else {
            $db->execute('
            INSERT INTO `' . _DB_PREFIX_ . 'smanager_data` (
                `cart_id`, `data`, `data_carrier`
            ) VALUES (
                "' . pSQL($cartId) . '",
                ' . (int) $locationId . ',
                ' . (int) $carrier_id . '
            )'
            );
        }
    }
    public function initContent()
    {
        parent::initContent();

        try {
            $json = Tools::file_get_contents('php://input');

            $data = json_decode($json, true);

            if (isset($data['location_id'])) {
                $locationId = $data['location_id'];

                $cart = $this->context->cart;

                Db::getInstance()->execute('
                    UPDATE `' . _DB_PREFIX_ . 'cart`
                    SET `pudo_location_id` = ' . (int) $locationId . '
                    WHERE `id_cart` = ' . $cart->id
                );

                $this->update_smdata($locationId);

            } else {
                echo 'ERROR: There was a problem saving the Ship & Go Location';
            }

            echo 'OK!';
        } catch (\Exception $e) {
            $message = __CLASS__ . '::' . __FUNCTION__ . ' error: ' . $e->getMessage();

            CargusLog::logError($message);
            echo 'ERROR: There was a problem saving the Ship & Go Location';
        }
    }
}
