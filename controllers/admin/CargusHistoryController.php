<?php

use Cargus\CargusClass;

class CargusHistoryController extends ModuleAdminController
{
    /**
     * CargusHistoryController constructor.
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

    public function initContent()
    {
        parent::initContent();

        if (Tools::isSubmit('submitPickup')) {
            Configuration::updateValue('CARGUS_PUNCT_RIDICARE', Tools::getValue('CARGUS_PUNCT_RIDICARE'));

            $_SESSION['post_status'] = [
                'confirmations' => ['Punctul de ridicare a fost schimbat!'],
            ];

            ob_end_clean();
            Tools::redirectAdmin($this->context->link->getAdminLink('CargusHistory', true));
            exit;
        }

        if (isset($_SESSION['post_status'])) {
            $this->confirmations = $_SESSION['post_status']['confirmations'];
            unset($_SESSION['post_status']);
        }

        $cargus = new CargusClass(Configuration::get('CARGUS_API_URL'), Configuration::get('CARGUS_API_KEY'));

        $fields = [
            'UserName' => Configuration::get('CARGUS_USERNAME'),
            'Password' => Configuration::get('CARGUS_PASSWORD'),
        ];

        $token = $cargus->CallMethod('LoginUser', $fields, 'POST');

        $pickupLocations = $cargus->CallMethod('PickupLocations', [], 'GET', $token);

        if (is_null($pickupLocations)) {
            $this->errors[] = 'Nu exista niciun punct de ridicare asociat acestui cont!';
        } elseif (Configuration::get('CARGUS_USERNAME') == '' || Configuration::get('CARGUS_PASSWORD') == '') {
            $this->errors[] = 'Va rugam sa completati username-ul si parola in pagina de configurare a modulului!';
        } else {
            if (Configuration::get('CARGUS_PUNCT_RIDICARE') == '' && !empty($pickupLocations)) {
                Configuration::updateValue('CARGUS_PUNCT_RIDICARE', $pickupLocations[0]['LocationId']);
            }

            $orders = $cargus->CallMethod(
                'Orders?locationId=' . Configuration::get('CARGUS_PUNCT_RIDICARE') .
                '&status=1&pageNumber=1&itemsPerPage=100',
                [],
                'GET',
                $token
            );

            if (is_null($orders)) {
                $this->errors[] = 'Nu exista nicio comanda pentru punctul curent de ridicare!';
            } else {
                foreach ($pickupLocations as $pickupLocation) {
                    $pickups[$pickupLocation['LocationId']] = $pickupLocation['Name'];
                }

                $this->context->smarty->assign('pickups', $pickups);
                $this->context->smarty->assign('pickupsSelect', Configuration::get('CARGUS_PUNCT_RIDICARE'));
                $this->context->smarty->assign('orders', $orders);
                $this->context->smarty->assign('token', Tools::getAdminTokenLite('CargusOrderHistory'));

                $dateFormat['date'] = '%I:%M %p';
                $dateFormat['time'] = '%H:%M:%S';
                $this->context->smarty->assign('dateFormat', $dateFormat);
            }

//            $this->setTemplate('history.tpl');
            $content = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'cargus/views/templates/admin/history.tpl');
            $this->context->smarty->assign(array(
                'content' => $this->content . $content,
            ));
        }
    }
}
