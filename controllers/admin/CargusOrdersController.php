<?php

use Cargus\CargusClass;

class CargusOrdersController extends ModuleAdminController
{
    public function initContent()
    {
        parent::initContent();

        if (Tools::isSubmit('submitPickup')) {
            Configuration::updateValue('CARGUS_PUNCT_RIDICARE', Tools::getValue('CARGUS_PUNCT_RIDICARE'));

            $_SESSION['post_status'] = [
                'confirmations' => ['Punctul de ridicare a fost schimbat!'],
            ];

            ob_end_clean();
            Tools::redirectAdmin($this->context->link->getAdminLink('CargusOrders', true));
            exit;
        }

        if (isset($_SESSION['post_status'])) {
            if (isset($_SESSION['post_status']['confirmations'])) {
                $this->confirmations = $_SESSION['post_status']['confirmations'];
            }

            if (isset($_SESSION['post_status']['errors'])) {
                $this->errors = $_SESSION['post_status']['errors'];
            }

            if (isset($_SESSION['post_status']['warnings'])) {
                $this->errors = $_SESSION['post_status']['warnings'];
            }

            unset($_SESSION['post_status']);
        }

        // VALIDEAZA AWB-urile AFLATE IN ASTEPTARE
        if (Tools::isSubmit('submit_valideaza')) {
            $errors = [];
            $success = [];

            /*
            $cargus = new CargusClass(Configuration::get('CARGUS_API_URL'), Configuration::get('CARGUS_API_KEY'));

            $fields = [
                'UserName' => Configuration::get('CARGUS_USERNAME'),
                'Password' => Configuration::get('CARGUS_PASSWORD'),
            ];

            $token = $cargus->CallMethod('LoginUser', $fields, 'POST');
            */

            // we receive array of order_id
            foreach (Tools::getValue('selected') as $order_id) {
                /*
                // UC create awb

                $rows = Db::getInstance()->ExecuteS(
                    'SELECT * FROM `' . _DB_PREFIX_ . "awb_urgent_cargus` WHERE `barcode` = '0' AND `order_id` = '" .
                    addslashes($order_id) . "' ORDER BY `id`"
                );

                $go = true;

                $missing_info = false;

                $count_parcels = 0;
                $count_envelopes = 0;
                $total_weight = 0;

                foreach ($rows as $pdata) {
                    if (!$pdata['height'] || !$pdata['length'] || !$pdata['width']) {
                        $missing_info = true;
                    }

                    if ($pdata['parcels'] > 0) {
                        ++$count_parcels;
                    }

                    if ($pdata['envelopes'] > 0) {
                        ++$count_envelopes;
                    }

                    $total_weight += $pdata['weight'];
                }

                if ((!Configuration::get('CARGUS_GREUTATE') || !Configuration::get('CARGUS_LUNGIME')
                || !Configuration::get('CARGUS_LATIME')) && $missing_info) {
                    $errors[] = 'Va rugam sa introduceti dimensiunile coletelor!';
                    $go = false;
                }

                if (($count_parcels + $count_envelopes) > 15) {
                    $errors[] = 'Sunt permise maxim 15 colete+plic in expeditie';
                    $go = false;
                }

                if ($count_envelopes > 1) {
                    $errors[] = 'Maxim 1 plic in expeditie';
                    $go = false;
                }

//                if(!$row[0]['postal_code']){
//                    $errors[] = 'Va rugam sa introduceti codul postal al destinatarului!';
//                    $go = false;
//                }

                if ($go) {
                    $package_content_text = Configuration::get('CARGUS_PACKAGE_CONTENT_TEXT');

                    //privacy is needed so override packagecontent sent
                    if (!empty($package_content_text)) {
                        $rows[0]['contents'] = $package_content_text;
                    }

                    $fields = [
                        'Sender' => [
                            'LocationId' => $rows[0]['pickup_id'],
                        ],
                        'Parcels' => $count_parcels,
                        'Envelopes' => $count_envelopes,
                        'TotalWeight' => $total_weight,
                        'DeclaredValue' => $rows[0]['value'],
                        'CashRepayment' => $rows[0]['cash_repayment'],
                        'BankRepayment' => $rows[0]['bank_repayment'],
                        'OtherRepayment' => $rows[0]['other_repayment'],
                        'OpenPackage' => $rows[0]['openpackage'] == 1 ? true : false,
                        'ShipmentPayer' => $rows[0]['payer'],
                        'MorningDelivery' => $rows[0]['morning_delivery'] == 1 ? true : false,
                        'SaturdayDelivery' => $rows[0]['saturday_delivery'] == 1 ? true : false,
                        'Observations' => $rows[0]['observations'],
                        'PackageContent' => $rows[0]['contents'],
                        'CustomString' => $rows[0]['order_id'],
                    ];

                    $fields['ConsumerReturnType'] = Configuration::get('CARGUS_AWB_RETUR');
                    $fields['ReturnCodeExpirationDays'] = Configuration::get('CARGUS_AWB_RETUR_VALIDITATE');

                    if ($rows[0]['pudo_location_id']) {
                        $fields['DeliveryPudoPoint'] = $rows[0]['pudo_location_id'];

                        $fields['Recipient'] = [
                            'Name' => $rows[0]['name'],
                            'ContactPerson' => $rows[0]['contact'],
                            'PhoneNumber' => $rows[0]['phone'],
                            'Email' => $rows[0]['email'],
                        ];
                    } else {
                        $fields['Recipient'] = [
                            'LocationId' => null,
                            'Name' => $rows[0]['name'],
                            'CountyId' => null,
                            'CountyName' => $rows[0]['county_name'],
                            'LocalityId' => null,
                            'LocalityName' => $rows[0]['locality_name'],
                            'StreetId' => null,
                            'StreetName' => '-',
                            'AddressText' => $rows[0]['address'],
                            'ContactPerson' => $rows[0]['contact'],
                            'PhoneNumber' => $rows[0]['phone'],
                            'Email' => $rows[0]['email'],
                            'CodPostal' => $rows[0]['postal_code'],
                        ];
                    }

                    foreach ($rows as $pdata) {
                        //privacy is needed so override ParcelContent sent
                        if (!empty($package_content_text)) {
                            $pdata['contents'] = $package_content_text;
                        }

                        $fields['ParcelCodes'][] = [
                            // always 0
                            'Code' => 0,

                            // Type - package =1 or envelope =0
                            'Type' => $pdata['parcels'] > 0 ? 1 : 0,
                            'Weight' => $pdata['weight'] > 0 ? $pdata['weight'] : Configuration::get('CARGUS_GREUTATE'),
                            'Length' => $pdata['length'] > 0 ? $pdata['length'] : Configuration::get('CARGUS_LUNGIME'),
                            'Width' => $pdata['width'] > 0 ? $pdata['width'] : Configuration::get('CARGUS_LATIME'),
                            'Height' => $pdata['height'] > 0 ? $pdata['height'] : Configuration::get('CARGUS_INALTIME'),
                            'ParcelContent' => $pdata['contents'],
                        ];
                    }

                    if (!empty(Configuration::get('CARGUS_ID_TARIF'))) {
                        $fields['PriceTableId'] = Configuration::get('CARGUS_ID_TARIF');
                    }

                    if ($rows[0]['pudo_location_id']) {
                        $fields['ServiceId'] = 38;
                    } else {
                        $config_serviciu = Configuration::get('CARGUS_SERVICIU');
                        if ($config_serviciu > 0) {
                            $fields['ServiceId'] = $config_serviciu;
                        }
                    }

                    //\Cargus\CargusLog::logDebug('AWB data: '. print_r($fields, true));

                    $barcode = $cargus->CallMethod('Awbs', $fields, 'POST', $token);

                    if (is_array($barcode)) {
                        if (isset($barcode['error'])) {
                            \Cargus\CargusLog::logError(
                                'Create AWB error, data: ' . print_r($fields, true) . ', message: ' .
                                print_r($barcode['error'], true)
                            );

                            $errors[] = 'AWB-ul pentru comanda ' . addslashes($order_id) . ' nu a putut fi validat';
                            $errorIds[] = addslashes($order_id);
                        }
                    } else {
                        if (is_null($barcode)) {
                            $errors[] = 'AWB-ul pentru comanda ' . addslashes($order_id) . ' nu a putut fi validat';
                            $errorIds[] = addslashes($order_id);
                        } else {
                            $update = Db::getInstance()->execute(
                                'UPDATE `' . _DB_PREFIX_ . "awb_urgent_cargus` SET `barcode` = '" . $barcode .
                                "' WHERE `order_id` = '" . addslashes($order_id) . "'"
                            );
                            if ($update == 1) {
                                $success[] = addslashes($order_id);
                            } else {
                                $errors[] = 'AWB-ul pentru comanda ' . addslashes($order_id) .
                                            ' nu a putut fi actualizat in baza de date';
                                $errorIds[] = addslashes($order_id);
                            }
                        }
                    }

                    // end $go
                }

                */

                $result = $this->module->createNewAwb($order_id);

                $errors = $result['errors'];
                $success = $result['success'];
                $errorIds = $result['errorIds'];

                // end foreach order
            }

            if (empty($errors)) {
                $_SESSION['post_status'] = [
                    'confirmations' => ['Toate AWB-urile selectate au fost validate cu succes!'],
                ];
            } elseif (empty($success)) {
                $_SESSION['post_status'] = [
                    'errors' => $errors,
                ];
            } else {
                $_SESSION['post_status'] = [
                    'errors' => ['Nu a fost posibila validarea urmatoarelor comenzi: ' . implode(', ', $errorIds)],
                    'warnings' => ['ATENTIE: Doar o parte din AWB-urile selectate au fost validate cu succes!'],
                ];
            }

            ob_end_clean();
            Tools::redirectAdmin($this->context->link->getAdminLink('CargusOrders', true));
            exit;
        }

        // STERGE AWB-urile AFLATE IN ASTEPTARE
        if (Tools::isSubmit('submit_sterge')) {
            $errors = [];
            $success = [];

            foreach (Tools::getValue('selected') as $order_id) {
                $delete = Db::getInstance()->execute(
                    'DELETE FROM `' . _DB_PREFIX_ . "awb_urgent_cargus` WHERE `order_id` = '" . addslashes($order_id) . "'"
                );
                if ($delete == 1) {
                    $success[] = addslashes($order_id);
                } else {
                    $errors[] = addslashes($order_id);
                }
            }

            if (empty($errors)) {
                $_SESSION['post_status'] = [
                    'confirmations' => ['Toate AWB-urile selectate au fost sterse cu succes!'],
                ];
            } elseif (empty($success)) {
                $_SESSION['post_status'] = [
                    'errors' => ['Niciun AWB selectat nu a putut fi sters!'],
                ];
            } else {
                $_SESSION['post_status'] = [
                    'errors' => ['Nu a fost posibila stergerea urmatoarelor comenzi: ' . implode(', ', $errors)],
                    'warnings' => ['ATENTIE: Doar o parte din AWB-urile selectate au fost sterse cu succes!'],
                ];
            }

            ob_end_clean();
            Tools::redirectAdmin($this->context->link->getAdminLink('CargusOrders', true));
            exit;
        }

        // DEZACTIVEAZA AWB-urile DEJA VALIDATE
        if (Tools::isSubmit('submit_dezactiveaza')) {
            $errors = [];
            $success = [];
            foreach (Tools::getValue('awbs') as $barcode) {
                $cargus = new CargusClass(Configuration::get('CARGUS_API_URL'), Configuration::get('CARGUS_API_KEY'));

                $fields = [
                    'UserName' => Configuration::get('CARGUS_USERNAME'),
                    'Password' => Configuration::get('CARGUS_PASSWORD'),
                ];

                $token = $cargus->CallMethod('LoginUser', $fields, 'POST');

                // UC delete awb
                $result = $cargus->CallMethod('Awbs?barCode=' . addslashes($barcode), [], 'DELETE', $token);
                if ($result == 1) {
                    $update = Db::getInstance()->execute(
                        'UPDATE `' . _DB_PREFIX_ . "awb_urgent_cargus` SET `barcode` = '0',`ReturnAwb`=NULL,`ReturnCode`=NULL WHERE `barcode` = '" .
                        addslashes($barcode) . "'"
                    );
                    if ($update == 1) {
                        $success[] = addslashes($barcode);
                    } else {
                        $errors[] = addslashes($barcode);
                    }
                } else {
                    $errors[] = addslashes($barcode);
                }
            }

            if (empty($errors)) {
                $_SESSION['post_status'] = [
                    'confirmations' => ['Toate AWB-urile selectate au fost dezactivate cu succes!'],
                ];
            } elseif (empty($success)) {
                $_SESSION['post_status'] = [
                    'errors' => ['Niciun AWB selectat nu a putut fi dezactivat!'],
                ];
            } else {
                $_SESSION['post_status'] = [
                    'errors' => ['Nu a fost posibila dezactivarea urmatoarelor AWB-uri: ' . implode(', ', $errors)],
                    'warnings' => ['ATENTIE: Doar o parte din AWB-urile selectate au fost dezactivate cu succes!'],
                ];
            }

            ob_end_clean();
            Tools::redirectAdmin($this->context->link->getAdminLink('CargusOrders', true));
            exit;
        }

        $pickups = null;
        $awbs = null;
        $lines = null;

        if (Configuration::get('CARGUS_USERNAME') == '' || Configuration::get('CARGUS_PASSWORD') == '') {
            $this->errors[] = 'Va rugam sa completati username-ul si parola in pagina de configurare a modulului!';
        } else {
            $cargus = new CargusClass(Configuration::get('CARGUS_API_URL'), Configuration::get('CARGUS_API_KEY'));

            $fields = [
                'UserName' => Configuration::get('CARGUS_USERNAME'),
                'Password' => Configuration::get('CARGUS_PASSWORD'),
            ];

            $token = $cargus->CallMethod('LoginUser', $fields, 'POST');

            $pickupLocations = $cargus->CallMethod('PickupLocations', [], 'GET', $token);

            if (is_null($pickupLocations)) {
                $this->errors[] = 'Nu exista niciun punct de ridicare asociat acestui cont!';
            } else {
                foreach ($pickupLocations as $pickupLocation) {
                    $pickups[$pickupLocation['LocationId']] = $pickupLocation['Name'];
                }

                if (Configuration::get('CARGUS_PUNCT_RIDICARE') == '' && !empty($pickupLocations)) {
                    Configuration::updateValue('CARGUS_PUNCT_RIDICARE', $pickupLocations[0]['LocationId']);
                }

                $orders = $cargus->CallMethod(
                    'Orders?locationId=' . Configuration::get('CARGUS_PUNCT_RIDICARE') .
                    '&status=0&pageNumber=1&itemsPerPage=1000',
                    [],
                    'GET',
                    $token
                );

                $awbs = [];
                if (!is_null($orders)) {
                    $allAwbs = $cargus->CallMethod(
                        'Awbs?&orderId=' . (isset($orders['OrderId']) == 1 ? $orders['OrderId'] : $orders[0]['OrderId']),
                        [],
                        'GET',
                        $token
                    );
                    if (!is_null($allAwbs)) {
                        foreach ($allAwbs as $rawAwb) {
                            if ($rawAwb['Status'] != 'Deleted') {
                                $awbs[] = $rawAwb;
                            }
                        }
                    }
                }

                if (empty($awbs)) {
                    $this->warnings[] = 'Nu exista niciun AWB validat pentru punctul curent de ridicare!';
                }

                $lines = Db::getInstance()->ExecuteS(
                    'SELECT *,SUM(`parcels`) as `parcels`,SUM(`envelopes`) as `envelopes`,SUM(`weight`) as `weight` ' .
                    'FROM `' . _DB_PREFIX_ .
                    "awb_urgent_cargus` WHERE `barcode`='0' GROUP BY `order_id` ORDER BY `order_id`"
                );
                if (empty($lines)) {
                    $this->warnings[] = 'Nu exista niciun AWB in asteptare!';
                }
            }
        }

        $this->context->smarty->assign('pickups', $pickups);
        $this->context->smarty->assign('awbs', $awbs);
        $this->context->smarty->assign('lines', $lines);

        $this->context->smarty->assign('printTypes', ['A4', '10x14']);
        $this->context->smarty->assign('printType', Tools::getValue('PRINT_TYPE') ? Tools::getValue('PRINT_TYPE') : 0);

        $this->context->smarty->assign('token', Tools::getAdminTokenLite('CargusEditAwb'));
        $this->context->smarty->assign('tokenAdmin', Tools::getAdminTokenLite('CargusAdmin'));
        $this->context->smarty->assign('cookie', _COOKIE_KEY_);
        $this->context->smarty->assign('pickupsSelect', Configuration::get('CARGUS_PUNCT_RIDICARE'));

//        $this->setTemplate('orders.tpl');
        $content = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'cargus/views/templates/admin/orders.tpl');
        $this->context->smarty->assign(array(
            'content' => $this->content . $content,
        ));
    }
}
