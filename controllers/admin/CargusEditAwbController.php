<?php

use Cargus\CargusClass;

class CargusEditAwbController extends ModuleAdminController
{
    /**
     * AdminPreferencesController constructor.
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

        if (Tools::isSubmit('delete')) {
            $delete_id = Tools::getValue('delete');

            $result = Db::getInstance()->delete('awb_urgent_cargus', '`id` = ' . (int) $delete_id, 1, false);

            if ($result) {
                $_SESSION['post_status'] = [
                    'confirmations' => ['Coletul a fost sters!'],
                ];
            } else {
                $_SESSION['post_status'] = [
                    'errors' => ['Eroare la stergerea coletului!'],
                ];
            }

            $id = Tools::getValue('id');

            // reload page
            Tools::redirectAdmin(
                $this->context->link->getAdminLink('CargusEditAwb', true) .
                '&id=' . $id
            );

            exit;
        }

        if (Tools::isSubmit('submit') || Tools::isSubmit('add')) {
            // must save current data and add new parcel for this expedition

            $config_serviciu = Configuration::get('CARGUS_SERVICIU');

            $expedition_type = Tools::getValue('expedition_type');

            $count_parcels = 0;
            $count_envelopes = 0;
            foreach (Tools::getValue('cid') as $index => $value) {
                $parcels = $expedition_type[$index] == 'parcel' ? 1 : 0;
                $envelopes = $expedition_type[$index] == 'envelop' ? 1 : 0;

                $count_parcels += $parcels;
                $count_envelopes += $envelopes;

                $sql = 'UPDATE ' . _DB_PREFIX_ . "awb_urgent_cargus SET 
                                pickup_id = '" . addslashes(Tools::getValue('pickup_id')) . "',
                                name = '" . addslashes(Tools::getValue('name')) . "',
                                locality_name = '" . addslashes(Tools::getValue('city')) . "',
                                county_name = '" . addslashes(Tools::getValue('id_state')) . "',
                                address = '" . addslashes(Tools::getValue('address')) . "',
                                postal_code = '" . addslashes(Tools::getValue('postal_code')) . "',
                                contact = '" . addslashes(Tools::getValue('contact')) . "',
                                phone = '" . addslashes(Tools::getValue('phone')) . "',
                                email = '" . addslashes(Tools::getValue('email')) . "',
                                parcels = '" . addslashes($parcels) . "',
                                envelopes = '" . addslashes($envelopes) . "',
                                weight = '" . addslashes(Tools::getValue('weight')[$index]) . "',
                                length = '" . addslashes(Tools::getValue('length')[$index]) . "',
                                width = '" . addslashes(Tools::getValue('width')[$index]) . "',
                                height = '" . addslashes(Tools::getValue('height')[$index]) . "',
                                value = '" . addslashes(Tools::getValue('value')) . "',
                                cash_repayment = '" . addslashes(Tools::getValue('cash_repayment')) . "',
                                bank_repayment = '" . addslashes(Tools::getValue('bank_repayment')) . "',
                                other_repayment = '" . addslashes(Tools::getValue('other_repayment')) . "',
                                payer = '" . addslashes(Tools::getValue('payer')) . "',
                                delivery_time = '" . addslashes(Tools::getValue('delivery_time')) . "',
                                saturday_delivery = '" . addslashes(Tools::getValue('saturday_delivery')) . "',
                                openpackage = '" . addslashes(Tools::getValue('openpackage')) . "',
                                observations = '" . addslashes(Tools::getValue('observations')) . "',
                                contents = '" . addslashes(Tools::getValue('contents')[$index]) . "'
                            WHERE id = '" . Tools::getValue('cid')[$index] . "'
                            ";

                $result = Db::getInstance()->execute($sql);

                if ($config_serviciu != '36' && (float) Tools::getValue('weight')[$index] > 31) {
                    $_SESSION['post_status']['errors'][] = 'Un colet nu poate cantari mai mult de 31kg';
                }
                if ($config_serviciu == '36' && (float) Tools::getValue('weight')[$index] > 50) {
                    $_SESSION['post_status']['errors'][] = 'Un colet nu poate cantari mai mult de 50kg';
                }
                if ($config_serviciu == '36' && (float) Tools::getValue('weight')[$index] < 32) {
                    $_SESSION['post_status']['errors'][] = 'Un colet nu poate cantari mai putin de 32kg';
                }
                if ((Tools::getValue('pudo_location_id')[$index] != 0) &&
                    (
                        Tools::getValue('length')[$index] > 70 ||
                        Tools::getValue('width')[$index] > 70 ||
                        Tools::getValue('height')[$index] > 70
                    )
                ) {
                    $_SESSION['post_status']['errors'][] = 'Latura unui colet nu poate fi mai mare de 70cm';
                }

                if ($result != 1) {
                    // if error exit loop
                    \Cargus\CargusLog::logError(__CLASS__ . '::' . __METHOD__ . ' update sql error: ' . Db::getInstance()->getMsgError());

                    $_SESSION['post_status']['errors'][] = 'Eroare la inserarea datelor in baza de date!';
                    break;
                }

                // end foreach
            }

            if ($result == 1) {
                $_SESSION['post_status']['confirmations'][] = 'Modificarile au fost salvate cu succes!';
            } else {
                $_SESSION['post_status']['errors'][] = 'Eroare la inserarea datelor in baza de date!';
            }

            if (($count_parcels + $count_envelopes) > 15) {
                $_SESSION['post_status']['errors'][] = 'Sunt permise maxim 15 colete+plic in expeditie';
                $result = 0;
            }

            if ($count_envelopes > 1) {
                $_SESSION['post_status']['errors'][] = 'Maxim 1 plic in expeditie';
                $result = 0;
            }

            if (Tools::isSubmit('add') && ($count_parcels + $count_envelopes) >= 15) {
                $_SESSION['post_status']['errors'][] = 'Sunt permise maxim 15 colete+plic in expeditie';
                $result = 0;
            }

            if ($result == 1 && Tools::isSubmit('add')) {
                // we must add new parcel and reload data on page

                $newparcel = [
                    'order_id' => addslashes(Tools::getValue('id')),
                    'pickup_id' => addslashes(Tools::getValue('pickup_id')),
                    'name' => addslashes(Tools::getValue('name')),
                    'locality_name' => addslashes(Tools::getValue('city')),
                    'county_name' => addslashes(Tools::getValue('id_state')),
                    'address' => addslashes(Tools::getValue('address')),
                    'postal_code' => addslashes(Tools::getValue('postal_code')),
                    'contact' => addslashes(Tools::getValue('contact')),
                    'phone' => addslashes(Tools::getValue('phone')),
                    'email' => addslashes(Tools::getValue('email')),
                    'parcels' => 1,
                    'envelopes' => 0,
                    'weight' => '',
                    'length' => '',
                    'width' => '',
                    'height' => '',
                    'value' => addslashes(Tools::getValue('value')),
                    'cash_repayment' => addslashes(Tools::getValue('cash_repayment')),
                    'bank_repayment' => addslashes(Tools::getValue('bank_repayment')),
                    'other_repayment' => addslashes(Tools::getValue('other_repayment')),
                    'payer' => addslashes(Tools::getValue('payer')),
                    'saturday_delivery' => addslashes(Tools::getValue('saturday_delivery')),
                    'delivery_time' => addslashes(Tools::getValue('delivery_time')),
                    'openpackage' => addslashes(Tools::getValue('openpackage')),
                    'observations' => addslashes(Tools::getValue('observations')),
                    'contents' => '',
                    'barcode' => '0',
                ];

                $result = Db::getInstance()->insert('awb_urgent_cargus', $newparcel, false, false, Db::INSERT);

                if ($result == 1) {
                    $_SESSION['post_status']['confirmations'][] = 'Colet nou adaugat cu succes!';
                } else {
                    \Cargus\CargusLog::logError(__CLASS__ . '::' . __METHOD__ . ' insert sql error: ' . Db::getInstance()->getMsgError());
                    $_SESSION['post_status']['errors'][] = 'Eroare la inserarea datelor in baza de date!';
                }
            } else {
                // just a save, go back to orders list
                // if no errors

                if (empty($_SESSION['post_status']['errors'])) {
                    Tools::redirectAdmin(
                        $this->context->link->getAdminLink('CargusOrders', true) .
                        '&module_name=[cargus]&configure=[cargus]'
                    );
                }
            }
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
            $this->errors[] = 'Nu exista niciun punct de ridicare disponibil pentru acest utilizator!';
        } elseif (Configuration::get('CARGUS_USERNAME') == '' || Configuration::get('CARGUS_PASSWORD') == '') {
            $this->errors[] = 'Va rugam sa completati username-ul si parola in pagina de configurare a modulului!';
        } else {
            foreach ($pickupLocations as $pickupLocation) {
                $pickups[$pickupLocation['LocationId']] = $pickupLocation['Name'];
            }

            $data = Db::getInstance()->ExecuteS('SELECT * FROM `' . _DB_PREFIX_ . "awb_urgent_cargus` WHERE `order_id` = '" . Tools::getValue('id') . "' ORDER BY `id`");
            $fullStates = State::getStatesByIdCountry(36);

            $states = [];

            foreach ($fullStates as $state) {
                $states[$state['iso_code']] = $state['name'];
            }

            $count_parcels = 0;
            $count_envelopes = 0;
            foreach ($data as $index => $value) {
                $data[$index]['expedition_type'] = null;
                if ($value['parcels'] > 0) {
                    $data[$index]['expedition_type'] = 'parcel';
                    ++$count_parcels;
                }
                if ($value['envelopes'] > 0) {
                    $data[$index]['expedition_type'] = 'envelop';
                    ++$count_envelopes;
                }
            }

            $allowMultiPiece = false;

            $config_serviciu = Configuration::get('CARGUS_SERVICIU');

            switch ($config_serviciu) {
                case '0':
                case '1':
                case '39':
                    $allowMultiPiece = true;
                    break;

                case '34':
                case '36':
                    $allowMultiPiece = false;
                    break;
            }

            if ($data[0]['pudo_location_id'] != 0) {
                $allowMultiPiece = false;
            }

            if (($count_parcels + $count_envelopes) >= 15) {
                $allowMultiPiece = false;
            }

            $this->context->smarty->assign('states', $states);
            $this->context->smarty->assign('statesSelect', $data[0]['county_name']);
            $this->context->smarty->assign('payers', [1 => 'Expeditor', 2 => 'Destinatar']);
            $this->context->smarty->assign('payerSelect', $data[0]['payer']);
            $this->context->smarty->assign('yesNo', [0 => 'Nu', 1 => 'Da']);
            $this->context->smarty->assign('expeditie', ['parcel' => 'Colet', 'envelop' => 'Plic']);
            $this->context->smarty->assign('yesNoSambata', $data[0]['saturday_delivery']);
            $this->context->smarty->assign('delivery_time', $data[0]['delivery_time']);
            $this->context->smarty->assign('yesNoDeschidereColet', $data[0]['openpackage']);
            $this->context->smarty->assign('data', $data[0]);
            $this->context->smarty->assign('dataparcels', $data);
            $this->context->smarty->assign('pickups', $pickups);
            $this->context->smarty->assign('pickupsSelect', $data[0]['pickup_id']);
            $this->context->smarty->assign('allowMultiPiece', $allowMultiPiece);

//            $this->setTemplate('edit_awb.tpl');

            $this->addJqueryUI('ui.autocomplete');

            $this->context->smarty->assign(
                'CARGUS_API_STREETS',
                $this->context->link->getModuleLink('cargus', 'getStreets', [], true)
            );

            $this->context->smarty->assign(
                'CARGUS_API_CITIES',
                $this->context->link->getModuleLink('cargus', 'getCities', [], true)
            );

            $content = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'cargus/views/templates/admin/edit_awb.tpl');
            $this->context->smarty->assign(array(
                'content' => $this->content . $content,
            ));
        }
    }
}
