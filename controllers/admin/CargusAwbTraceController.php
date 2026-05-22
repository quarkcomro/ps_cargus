<?php

use Cargus\CargusClass;

class CargusAwbTraceController extends ModuleAdminController
{
    /**
     * CargusAwbTrace constructor.
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

            $awbTrace = $cargus->CallMethod('AwbTrace?&barCode=' . Tools::getValue('BarCode'), [], 'GET', $token);

            if (!is_null($awbTrace) && empty($awbTrace)) {
                $awbTrace['Event'] = [];
            }

            if (is_null($awbTrace)) {
                $this->errors[] = 'Nu s-au putut prelua detaliile acestui AWB!';
            } else {
                $this->context->smarty->assign('BarCode', Tools::getValue('BarCode'));
                $this->context->smarty->assign('awbTrace', $awbTrace);
//                $this->setTemplate('awb_trace_history.tpl');
                $content = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'cargus/views/templates/admin/awb_trace_history.tpl');
                $this->context->smarty->assign(array(
                    'content' => $this->content . $content,
                ));
            }
        }
    }
}
