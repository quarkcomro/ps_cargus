<?php

use Cargus\CargusClass;

class CargusPreferencesController extends ModuleAdminController
{
    private $canUseAutocomplete;

    /**
     * CargusPreferencesController constructor.
     *
     * @throws PrestaShopException
     */
    public function __construct()
    {
        parent::__construct();

        $this->canUseAutocomplete = false;

        if (_PS_VERSION_ >= '1.7.7') {
            $this->canUseAutocomplete = true;
        }
    }

    public function validate()
    {
        $awbReturValiditate = Tools::getValue('CARGUS_AWB_RETUR_VALIDITATE');

        //must be integer
        if (!empty($awbReturValiditate) &&
            !filter_var(
                $awbReturValiditate,
                FILTER_VALIDATE_INT,
                array('options' => array('min_range' => 0, 'max_range' => 180))
            )
        ) {
            $_SESSION['post_status']['errors'][] = 'Validitate (zile) trebuie sa fie un numar intreg intre 0 si 180';
        }

        return empty($_SESSION['post_status']['errors']);
    }

    public function initContent()
    {
        parent::initContent();

        if (Tools::isSubmit('submit')) {
            if (!$this->validate()) {
                ob_end_clean();
                Tools::redirectAdmin($this->context->link->getAdminLink('CargusPreferences', true));
                exit;
            }

            Configuration::updateValue('CARGUS_PUNCT_RIDICARE', Tools::getValue('CARGUS_PUNCT_RIDICARE'));
            Configuration::updateValue('CARGUS_ASIGURARE', Tools::getValue('CARGUS_ASIGURARE'));
            Configuration::updateValue('CARGUS_SAMBATA', Tools::getValue('CARGUS_SAMBATA'));
            Configuration::updateValue('CARGUS_DIMINEATA', Tools::getValue('CARGUS_DIMINEATA'));
            Configuration::updateValue('CARGUS_DESCHIDERE_COLET', Tools::getValue('CARGUS_DESCHIDERE_COLET'));
            Configuration::updateValue('CARGUS_EXPEDITOR_LOCATION_ID', Tools::getValue('CARGUS_EXPEDITOR_LOCATION_ID'));
            Configuration::updateValue('CARGUS_TIP_RAMBURS', Tools::getValue('CARGUS_TIP_RAMBURS'));
            Configuration::updateValue('CARGUS_PLATITOR', Tools::getValue('CARGUS_PLATITOR'));
            Configuration::updateValue('CARGUS_TIP_EXPEDITIE', Tools::getValue('CARGUS_TIP_EXPEDITIE'));
            Configuration::updateValue('CARGUS_SM_CARRIER_ID', Tools::getValue('CARGUS_SM_CARRIER_ID'));
            Configuration::updateValue('CARGUS_TRANSPORT_GRATUIT', Tools::getValue('CARGUS_TRANSPORT_GRATUIT'));
            Configuration::updateValue('CARGUS_COST_FIX', Tools::getValue('CARGUS_COST_FIX'));
            Configuration::updateValue('CARGUS_SERVICIU', Tools::getValue('CARGUS_SERVICIU'));
            Configuration::updateValue('CARGUS_ID_TARIF', Tools::getValue('CARGUS_ID_TARIF'));
            Configuration::updateValue('CARGUS_GREUTATE', Tools::getValue('CARGUS_GREUTATE'));
            Configuration::updateValue('CARGUS_LUNGIME', Tools::getValue('CARGUS_LUNGIME'));
            Configuration::updateValue('CARGUS_LATIME', Tools::getValue('CARGUS_LATIME'));
            Configuration::updateValue('CARGUS_INALTIME', Tools::getValue('CARGUS_INALTIME'));
            if ($this->canUseAutocomplete) {
                Configuration::updateValue('CARGUS_POSTALCODE', Tools::getValue('CARGUS_POSTALCODE'));
            }
            Configuration::updateValue('CARGUS_PACKAGE_CONTENT_TEXT', Tools::getValue('CARGUS_PACKAGE_CONTENT_TEXT'));
            Configuration::updateValue('CARGUS_AWB_RETUR', Tools::getValue('CARGUS_AWB_RETUR'));
            Configuration::updateValue('CARGUS_AWB_RETUR_VALIDITATE', Tools::getValue('CARGUS_AWB_RETUR_VALIDITATE'));
            Configuration::updateValue('CARGUS_PRINT_AWB_RETUR', Tools::getValue('CARGUS_PRINT_AWB_RETUR'));

            Configuration::updateValue('CARGUS_SATURDAY_DELIVERY', Tools::getValue('CARGUS_SATURDAY_DELIVERY'));
            Configuration::updateValue('CARGUS_SATURDAY_DELIVERY_TARIF', Tools::getValue('CARGUS_SATURDAY_DELIVERY_TARIF'));
            Configuration::updateValue('CARGUS_SATURDAY_MESAJ', Tools::getValue('CARGUS_SATURDAY_MESAJ'));

            Configuration::updateValue('CARGUS_PRE10_DELIVERY', Tools::getValue('CARGUS_PRE10_DELIVERY'));
            Configuration::updateValue('CARGUS_PRE10_DELIVERY_TARIF', Tools::getValue('CARGUS_PRE10_DELIVERY_TARIF'));
            Configuration::updateValue('CARGUS_PRE10_MESAJ', Tools::getValue('CARGUS_PRE10_MESAJ'));


            Configuration::updateValue('CARGUS_PRE12_DELIVERY', Tools::getValue('CARGUS_PRE12_DELIVERY'));
            Configuration::updateValue('CARGUS_PRE12_DELIVERY_TARIF', Tools::getValue('CARGUS_PRE12_DELIVERY_TARIF'));
            Configuration::updateValue('CARGUS_PRE12_MESAJ', Tools::getValue('CARGUS_PRE12_MESAJ'));

            Configuration::updateValue('CARGUS_SHIPGO_DELIVERY', Tools::getValue('CARGUS_SHIPGO_DELIVERY'));
            Configuration::updateValue('CARGUS_SHIPGO_DELIVERY_TARIF', Tools::getValue('CARGUS_SHIPGO_DELIVERY_TARIF'));
            Configuration::updateValue('CARGUS_SHIPGO_MESAJ', Tools::getValue('CARGUS_SHIPGO_MESAJ'));

            $_SESSION['post_status'] = [
                'confirmations' => ['Preferintele au fost salvate cu succes!'],
            ];

            $this->module->alterTable();

            ob_end_clean();
            Tools::redirectAdmin($this->context->link->getAdminLink('CargusPreferences', true));
            exit;
        }

        if (isset($_SESSION['post_status'])) {
            if (isset($_SESSION['post_status']['confirmations'])) {
                $this->confirmations = $_SESSION['post_status']['confirmations'];
            }
            if (isset($_SESSION['post_status']['errors'])) {
                $this->errors = $_SESSION['post_status']['errors'];
            }
            unset($_SESSION['post_status']);
        }

        $cargus = new CargusClass(Configuration::get('CARGUS_API_URL'), Configuration::get('CARGUS_API_KEY'));

        // UC login user
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
            if (Configuration::get('CARGUS_PUNCT_RIDICARE') == '' && count($pickupLocations) > 0) {
                Configuration::updateValue('CARGUS_PUNCT_RIDICARE', $pickupLocations[0]['LocationId']);
            }

            foreach ($pickupLocations as $pickupLocation) {
                $pickups[$pickupLocation['LocationId']] = $pickupLocation['Name'];
            }

            $serviciuOptions = [
                '0' => '-',
                '1' => '1 - Standard',
                '39' => '39 - Multipiece',
                '34' => '34 - Economic standard',
                '36' => '36 - Standard plus',
            ];

            $awbReturOptions = array(
                '0' => 'Nu',
                '1' => 'Cod retur',
                '2' => 'AWB Pre-tiparit'
            );

            $printAwbReturOptions = array(
                '0' => 'Nu',
                '2' => 'Da cu instructiuni'
            );

            $this->context->smarty->assign('awbReturOptions', $awbReturOptions);
            $this->context->smarty->assign('printAwbReturOptions', $printAwbReturOptions);

            $this->context->smarty->assign('pickups', $pickups);
            $this->context->smarty->assign('pickupsSelect', Configuration::get('CARGUS_PUNCT_RIDICARE'));
            $this->context->smarty->assign('yesNo', [0 => 'Nu', 1 => 'Da']);
            $this->context->smarty->assign('serviciuOptions', $serviciuOptions);
            $this->context->smarty->assign('yesNoAsiguare', Configuration::get('CARGUS_ASIGURARE'));
            $this->context->smarty->assign('CARGUS_EXPEDITOR_LOCATION_ID', Configuration::get('CARGUS_EXPEDITOR_LOCATION_ID'));
            $this->context->smarty->assign('yesNoSambata', Configuration::get('CARGUS_SAMBATA'));
            $this->context->smarty->assign('yesNoDimineata', Configuration::get('CARGUS_DIMINEATA'));
            $this->context->smarty->assign('yesNoDeschidereColet', Configuration::get('CARGUS_DESCHIDERE_COLET'));
            $this->context->smarty->assign('ramburs', ['cash' => 'Numerar', 'cont' => 'Cont colector']);
            $this->context->smarty->assign('rambursSelect', Configuration::get('CARGUS_TIP_RAMBURS'));
            $this->context->smarty->assign('platitor', ['destinatar' => 'Destinatar', 'expeditor' => 'Expeditor']);
            $this->context->smarty->assign('platitorSelect', Configuration::get('CARGUS_PLATITOR'));
            $this->context->smarty->assign('expeditie', ['colet' => 'Colet', 'plic' => 'Plic']);
            $this->context->smarty->assign('expeditieSelect', Configuration::get('CARGUS_TIP_EXPEDITIE'));
            $this->context->smarty->assign('CARGUS_SM_CARRIER_ID', Configuration::get('CARGUS_SM_CARRIER_ID'));
            $this->context->smarty->assign('transport', Configuration::get('CARGUS_TRANSPORT_GRATUIT'));
            $this->context->smarty->assign('cost', Configuration::get('CARGUS_COST_FIX'));
            $this->context->smarty->assign('serviciuSelect', Configuration::get('CARGUS_SERVICIU'));
            $this->context->smarty->assign('id_tarif', Configuration::get('CARGUS_ID_TARIF'));
            $this->context->smarty->assign('greutate', Configuration::get('CARGUS_GREUTATE'));
            $this->context->smarty->assign('lungime', Configuration::get('CARGUS_LUNGIME'));
            $this->context->smarty->assign('latime', Configuration::get('CARGUS_LATIME'));
            $this->context->smarty->assign('inaltime', Configuration::get('CARGUS_INALTIME'));

            $this->context->smarty->assign('canUseAutocomplete', $this->canUseAutocomplete);
            if ($this->canUseAutocomplete) {
                $this->context->smarty->assign('postal_code', Configuration::get('CARGUS_POSTALCODE'));
            }

            $this->context->smarty->assign('package_content_text', Configuration::get('CARGUS_PACKAGE_CONTENT_TEXT'));
            $this->context->smarty->assign('awbReturSelect', Configuration::get('CARGUS_AWB_RETUR'));
            $this->context->smarty->assign('awb_retur_validitate', Configuration::get('CARGUS_AWB_RETUR_VALIDITATE'));
            $this->context->smarty->assign('printAwbRetur', Configuration::get('CARGUS_PRINT_AWB_RETUR'));

            $this->context->smarty->assign('CARGUS_SATURDAY_DELIVERY', Configuration::get('CARGUS_SATURDAY_DELIVERY'));
            $this->context->smarty->assign('CARGUS_SATURDAY_DELIVERY_TARIF', Configuration::get('CARGUS_SATURDAY_DELIVERY_TARIF'));
            $this->context->smarty->assign('CARGUS_SATURDAY_MESAJ', Configuration::get('CARGUS_SATURDAY_MESAJ'));

            $this->context->smarty->assign('CARGUS_PRE10_DELIVERY', Configuration::get('CARGUS_PRE10_DELIVERY'));
            $this->context->smarty->assign('CARGUS_PRE10_DELIVERY_TARIF', Configuration::get('CARGUS_PRE10_DELIVERY_TARIF'));
            $this->context->smarty->assign('CARGUS_PRE10_MESAJ', Configuration::get('CARGUS_PRE10_MESAJ'));

            $this->context->smarty->assign('CARGUS_PRE12_DELIVERY', Configuration::get('CARGUS_PRE12_DELIVERY'));
            $this->context->smarty->assign('CARGUS_PRE12_DELIVERY_TARIF', Configuration::get('CARGUS_PRE12_DELIVERY_TARIF'));
            $this->context->smarty->assign('CARGUS_PRE12_MESAJ', Configuration::get('CARGUS_PRE12_MESAJ'));


            $this->context->smarty->assign('CARGUS_SHIPGO_DELIVERY', Configuration::get('CARGUS_SHIPGO_DELIVERY'));
            $this->context->smarty->assign('CARGUS_SHIPGO_DELIVERY_TARIF', Configuration::get('CARGUS_SHIPGO_DELIVERY_TARIF'));
            $this->context->smarty->assign('CARGUS_SHIPGO_MESAJ', Configuration::get('CARGUS_SHIPGO_MESAJ'));
//            $this->setTemplate('preferences.tpl');

            $content = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'cargus/views/templates/admin/preferences.tpl');
            $this->context->smarty->assign(array(
                'content' => $this->content . $content,
            ));
        }
    }
}
