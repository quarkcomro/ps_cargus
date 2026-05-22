<?php

use Cargus\CargusClass;

class CargusOrderHistoryController extends ModuleAdminController
{
    /**
     * CargusOrderHistory constructor.
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

        if (Configuration::get('CARGUS_USERNAME') == '' || Configuration::get('CARGUS_PASSWORD') == '') {
            $this->errors[] = 'Va rugam sa completati username-ul si parola in pagina de configurare a modulului!';
        } else {
            $cargus = new CargusClass(Configuration::get('CARGUS_API_URL'), Configuration::get('CARGUS_API_KEY'));

            $fields = [
                'UserName' => Configuration::get('CARGUS_USERNAME'),
                'Password' => Configuration::get('CARGUS_PASSWORD'),
            ];

            $token = $cargus->CallMethod('LoginUser', $fields, 'POST');

            $awbs = $cargus->CallMethod('Awbs?&orderId=' . Tools::getValue('orderId'), [], 'GET', $token);

            $this->context->smarty->assign('orderId', Tools::getValue('orderId'));

            if (is_null($awbs)) {
                $this->errors[] = 'Nu exista niciun AWB asociat acestei comenzi!';
            } else {
                $this->context->smarty->assign('orderId', Tools::getValue('orderId'));
                $this->context->smarty->assign('awbs', $awbs);
                $this->context->smarty->assign('token', Tools::getAdminTokenLite('CargusAwbHistory'));
                $this->context->smarty->assign('tokenTrace', Tools::getAdminTokenLite('CargusAwbTrace'));

//                $this->setTemplate('order_history.tpl');
                $content = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'cargus/views/templates/admin/order_history.tpl');
                $this->context->smarty->assign(array(
                    'content' => $this->content . $content,
                ));
            }
        }
    }
}
