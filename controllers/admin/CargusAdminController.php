<?php

use Cargus\CargusClass;

class CargusAdminController extends ModuleAdminController
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

    public function initContent()
    {
        parent::initContent();

        try {
            $secret = '';
            if (Tools::getIsset('secret')) {
                $secret = addslashes(Tools::getValue('secret'));
            }
            if ($secret != _COOKIE_KEY_) {
                exit('Acces nepermis!');
            }

            if (Tools::getIsset('type') && Tools::getValue('type') == 'PRINTAWB') {
                $format = Tools::getValue('format');

                $cargus = new CargusClass(
                    Configuration::get('CARGUS_API_URL'),
                    Configuration::get('CARGUS_API_KEY')
                );

                $fields = [
                    'UserName' => Configuration::get('CARGUS_USERNAME'),
                    'Password' => Configuration::get('CARGUS_PASSWORD'),
                ];

                $token = $cargus->CallMethod('LoginUser', $fields, 'POST');

                $printConsumerReturn = is_null(Configuration::get('CARGUS_PRINT_AWB_RETUR')) ?
                    0:
                    Configuration::get('CARGUS_PRINT_AWB_RETUR');

                // UC print
                $print = $cargus->CallMethod(
                    'AwbDocuments?type=PDF&format=' . $format .
                    '&barCodes=' . addslashes(Tools::getValue('codes')).
                    '&printReturn=' . $printConsumerReturn,
                    [],
                    'GET',
                    $token
                );

                header('Content-type:application/pdf');
                echo base64_decode($print);
                exit;
            }

            if (Tools::getIsset('type') && Tools::getValue('type') == 'SENDORDER') {
                $data = [];
                $date = new DateTime();
                $date->setTimezone(new DateTimeZone('Europe/Bucharest'));
                $today = $date->format('Y-m-d H:i:s');

                if (Tools::getIsset('date')) {
                    $d = explode('.', Tools::getValue('date'));
                    $date->setDate($d[2], $d[1], $d[0]);
                }

                $cd = $date->format('Y-m-d H:i:s');

                if (date('w', strtotime($cd)) == 0) { // duminica
                    $date = date('d.m.Y', strtotime($cd . ' +1 day'));
                    $h_start = 13;
                    $h_end = 18;
                    $h2_start = 14;
                    $h2_end = 19;
                } else {
                    if (date('w', strtotime($cd)) == 1 || date('w', strtotime($cd)) == 2 || date(
                        'w',
                        strtotime($cd)
                    ) == 3 || date('w', strtotime($cd)) == 4) { // luni, marti, miercuri si joi
                        if ($cd == $today) {
                            if (date('H', strtotime($cd)) > 18) {
                                $date = date('d.m.Y', strtotime($cd . ' +1 day'));
                                $h_start = 13;
                                $h_end = 18;
                                $h2_start = 14;
                                $h2_end = 19;
                            } else {
                                if (date('H', strtotime($cd)) == 18) {
                                    $date = date('d.m.Y', strtotime($cd));
                                    $h_start = 18;
                                    $h_end = 18;
                                    $h2_start = 19;
                                    $h2_end = 19;
                                } else {
                                    $date = date('d.m.Y', strtotime($cd));
                                    $h_start = date('H', strtotime($cd)) + 1;
                                    $h_end = 18;
                                    $h2_start = date('H', strtotime($cd)) + 2;
                                    $h2_end = 19;
                                }
                            }
                        } else {
                            $date = date('d.m.Y', strtotime($cd));
                            $h_start = 13;
                            $h_end = 18;
                            $h2_start = 14;
                            $h2_end = 19;
                        }
                    } else {
                        if (date('w', strtotime($cd)) == 5) { // vineri
                            if ($cd == $today) {
                                if (date('H', strtotime($cd)) > 18) {
                                    $date = date('d.m.Y', strtotime($cd . ' +1 day'));
                                    $h_start = 13;
                                    $h_end = 14;
                                    $h2_start = 14;
                                    $h2_end = 15;
                                } else {
                                    if (date('H', strtotime($cd)) == 18) {
                                        $date = date('d.m.Y', strtotime($cd));
                                        $h_start = 18;
                                        $h_end = 18;
                                        $h2_start = 19;
                                        $h2_end = 19;
                                    } else {
                                        $date = date('d.m.Y', strtotime($cd));
                                        $h_start = date('H', strtotime($cd)) + 1;
                                        $h_end = 18;
                                        $h2_start = date('H', strtotime($cd)) + 2;
                                        $h2_end = 19;
                                    }
                                }
                            } else {
                                $date = date('d.m.Y', strtotime($cd));
                                $h_start = 13;
                                $h_end = 18;
                                $h2_start = 14;
                                $h2_end = 19;
                            }
                        } else {
                            if (date('w', strtotime($cd)) == 6) { // sambata
                                if ($cd == $today) {
                                    if (date('H', strtotime($cd)) > 14) {
                                        $date = date('d.m.Y', strtotime($cd . ' +2 day'));
                                        $h_start = 13;
                                        $h_end = 18;
                                        $h2_start = 14;
                                        $h2_end = 19;
                                    } else {
                                        if (date('H', strtotime($cd)) == 14) {
                                            $date = date('d.m.Y', strtotime($cd));
                                            $h_start = 14;
                                            $h_end = 14;
                                            $h2_start = 15;
                                            $h2_end = 15;
                                        } else {
                                            $date = date('d.m.Y', strtotime($cd));
                                            $h_start = date('H', strtotime($cd)) + 1;
                                            $h_end = 14;
                                            $h2_start = date('H', strtotime($cd)) + 2;
                                            $h2_end = 15;
                                        }
                                    }
                                } else {
                                    $date = date('d.m.Y', strtotime($cd));
                                    $h_start = 13;
                                    $h_end = 14;
                                    $h2_start = 14;
                                    $h2_end = 15;
                                }
                            }
                        }
                    }
                }

                $data['date'] = $date;

                if (Tools::getIsset('hour')) {
                    $h = explode(':', Tools::getValue('hour'));
                    $h2_start = $h[0] + 1;
                    $hour = Tools::getValue('hour');
                } else {
                    $hour = false;
                }

                $html = '';
                for ($i = $h_start; $i <= $h_end; ++$i) {
                    $html .= '<option' .
                             ($hour == $i . ':00' ? ' selected="selected"' : '') . '>' . $i . ':00</option>';
                }
                $data['h_dela'] = $html;

                $html = '';
                for ($i = $h2_start; $i <= $h2_end; ++$i) {
                    $html .= '<option>' . $i . ':00</option>';
                }
                $data['h_panala'] = $html;

                $this->context->smarty->assign('date', $data['date']);
                $this->context->smarty->assign('h_dela', $data['h_dela']);
                $this->context->smarty->assign('h_panala', $data['h_panala']);
                $this->context->smarty->assign('token', Tools::getAdminTokenLite('CargusAdmin'));
                $this->context->smarty->assign('cookie', _COOKIE_KEY_);

//                $this->setTemplate('send_order.tpl');
                $content = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'cargus/views/templates/admin/send_order.tpl');
                $this->context->smarty->assign(array(
                    'content' => $this->content . $content,
                ));
            }

            if (Tools::getIsset('type') && Tools::getValue('type') == 'COMPLETEORDER') {
                $d = explode('.', Tools::getValue('date'));
                $from = $d[2] . '-' . $d[1] . '-' . $d[0] . ' ' . Tools::getValue('hour_from') . ':00';
                $to = $d[2] . '-' . $d[1] . '-' . $d[0] . ' ' . Tools::getValue('hour_to') . ':00';

                $cargus = new CargusClass(
                    Configuration::get('CARGUS_API_URL'),
                    Configuration::get('CARGUS_API_KEY')
                );

                $fields = [
                    'UserName' => Configuration::get('CARGUS_USERNAME'),
                    'Password' => Configuration::get('CARGUS_PASSWORD'),
                ];

                $token = $cargus->CallMethod('LoginUser', $fields, 'POST');

                // UC send order
                $order_id = $cargus->CallMethod(
                    'Orders?locationId=' . Configuration::get('CARGUS_PUNCT_RIDICARE') .
                    '&PickupStartDate=' . date('Y-m-d%20H:i:s', strtotime($from)) .
                    '&PickupEndDate=' . date('Y-m-d%20H:i:s', strtotime($to)) . '&action=1',
                    [],
                    'PUT',
                    $token
                );

                // trimite email cu link-ul pentru tracking
                $awbs = $cargus->CallMethod('Awbs?orderId=' . $order_id, [], 'GET', $token);

                foreach ($awbs as $a) {
                    if ($a['Status'] != 'Deleted') {
                        $data = Db::getInstance()->ExecuteS('SELECT
                                                                    c.firstname,
                                                                    c.lastname,
                                                                    c.email,
                                                                    o.id_order,
                                                                    o.date_add
                                                                FROM
                                                                    ' . _DB_PREFIX_ . 'customer c,
                                                                    ' . _DB_PREFIX_ . 'orders o,
                                                                    ' . _DB_PREFIX_ . "awb_urgent_cargus u
                                                                WHERE
                                                                    u.barcode = '" . $a['BarCode'] . "'
                                                                    AND u.order_id = o.id_order
                                                                    AND o.id_customer = c.id_customer");

                        $templateVars['{firstname}'] = $data[0]['firstname'];
                        $templateVars['{lastname}'] = $data[0]['lastname'];
                        $templateVars['{id_order}'] = $data[0]['id_order'];
                        $templateVars['{order_date}'] = date('d.m.Y H:i', strtotime($data[0]['date_add']));
                        $templateVars['{awb}'] = $a['BarCode'];

                        $id_lang = $this->context->cookie->id_lang;
                        $template_name = 'cargus_awb';
                        $title = Mail::l('Comanda ridicata de Cargus');
                        $from = Configuration::get('PS_SHOP_EMAIL');
                        $fromName = Configuration::get('PS_SHOP_NAME');
                        $mailDir = __DIR__ . '/../../mails/';
                        $toName = $data[0]['firstname'] . ' ' . $data[0]['lastname'];
                        $send = Mail::Send(
                            $id_lang,
                            $template_name,
                            $title,
                            $templateVars,
                            $data[0]['email'],
                            $toName,
                            $from,
                            $fromName,
                            null,
                            null,
                            $mailDir
                        );
                    }
                }

                // UC print borderou
                echo '<script>
                    window.opener.location.reload();
                    window.resizeTo(916, 669);
                    window.location = "index.php?controller=CargusAdmin&type=PRINTBORDEROU&token=' .
                     Tools::getAdminTokenLite('CargusAdmin') . '&secret=' . _COOKIE_KEY_ . '&orderId=' . $order_id . '";
                </script>';
                exit;
            }

            if (Tools::getIsset('type') && Tools::getValue('type') == 'PRINTBORDEROU') {
                $cargus = new CargusClass(
                    Configuration::get('CARGUS_API_URL'),
                    Configuration::get('CARGUS_API_KEY')
                );

                $fields = [
                    'UserName' => Configuration::get('CARGUS_USERNAME'),
                    'Password' => Configuration::get('CARGUS_PASSWORD'),
                ];

                $token = $cargus->CallMethod('LoginUser', $fields, 'POST');

                // UC print borderou
                $borderou = $cargus->CallMethod(
                    'OrderDocuments?orderId=' . Tools::getValue('orderId') . '&docType=0',
                    [],
                    'GET',
                    $token
                );

                header('Content-type:application/pdf');
                echo base64_decode($borderou);
                exit;
            }

            // TRANSFORMA COMANDA IN AWB TEMPORAR
            if (Tools::getIsset('type') && Tools::getValue('type') == 'ADDORDER') {
                // verific id-ul comenzii
                $id = 0;
                if (Tools::getIsset('id')) {
                    $id = addslashes(Tools::getValue('id'));
                }
                if ($id == 0) {
                    exit('Nicio comanda trimisa spre procesare!');
                }

                $result = $this->module->insertNewAwb($id);

                echo $result;

                /*
                // obtin detaliile comenzii
                $order = new Order($id);

                // obtin adresa
                $address = new Address($order->id_address_delivery);

                // obtin detaliile clientului
                $customer = new Customer($order->id_customer);

                // obiectele pentru cursul de schimb
                $key_ron = 0;
                $currency = Currency::getCurrencies();
                foreach ($currency as $cc) {
                    if (strtolower($cc['iso_code']) == 'ron') {
                        $key_ron = $cc['id_currency'];
                    }
                }
                if ($key_ron == 0) {
                    exit('Trebuie adaugata moneda RON');
                }
                $currency_RON = new Currency($key_ron);
                $currency_DEFAULT = new Currency($order->id_currency);

                // calculez totalul transportului inclusiv taxele
                $shipping_total = $order->total_shipping;

                // transform totalul transportului in lei
                $shipping_total = Tools::convertPriceFull($shipping_total, $currency_DEFAULT, $currency_RON);

                // calculez totalul comenzii inclusiv taxele
                $cart_total = $order->total_paid;

                // transform totalul comenzii in lei
                $cart_total = Tools::convertPriceFull($cart_total, $currency_DEFAULT, $currency_RON);

                // calculez greutatea totala a comenzii in kilograme
                $shipping = $order->getShipping();
                $weight = ceil($shipping[0]['weight']);
                if ($weight == 0) {
                    $weight = Configuration::get('CARGUS_GREUTATE') > 0 ? Configuration::get('CARGUS_GREUTATE') : 1;
                }

                // determin valoarea declarata
                if (Configuration::get('CARGUS_ASIGURARE') != 1) {
                    $value = 0;
                } else {
                    $value = round($cart_total - $shipping_total, 2);
                }

                // determin livrarea sambata
                if (Configuration::get('CARGUS_SAMBATA') != 1) {
                    $saturday = 0;
                } else {
                    $saturday = 1;
                }

                // determin livrarea dimineata
                if (Configuration::get('CARGUS_DIMINEATA') != 1) {
                    $morning = 0;
                } else {
                    $morning = 1;
                }

                // determin deschidere colet
                if (Configuration::get('CARGUS_DESCHIDERE_COLET') != 1) {
                    $openpackage = 0;
                } else {
                    $openpackage = 1;
                }

                // afla daca aceasta comanda a fost platita si daca nu determin rambursul si platitorul expeditiei
                if (
                    stristr($order->payment, 'cashondelivery')
                    || stristr($order->payment, 'cod')
                    || stristr($order->payment, 'ramburs')
                    || stristr($order->payment, 'cash')
                    || stristr($order->payment, 'numerar')
                    || stristr($order->payment, 'livrare')
                    || stristr($order->payment, 'cargus')
                ) {
                    if (Configuration::get('CARGUS_PLATITOR') != 'expeditor') {
                        $payer = 2;
                    } else {
                        $payer = 1;
                    }
                    if (Configuration::get('CARGUS_TIP_RAMBURS') != 'cont') {
                        if ($payer == 1) {
                            $cash_repayment = round($cart_total, 2);
                        } else {
                            $cash_repayment = round($cart_total - $shipping_total, 2);
                        }
                        $bank_repayment = 0;
                    } else {
                        $cash_repayment = 0;
                        if ($payer == 1) {
                            $bank_repayment = round($cart_total, 2);
                        } else {
                            $bank_repayment = round($cart_total - $shipping_total, 2);
                        }
                    }
                } else {
                    $bank_repayment = 0;
                    $cash_repayment = 0;
                    $payer = 1;
                }

                // daca transportul este gratuit, serviciul este platit de expeditor
                if ($shipping_total == 0) {
                    $payer = 1;
                }

                // obtin indicativul judetului destinatarului
                $states = State::getStatesByIdCountry($address->id_country);

                $state_ISO = null;
                foreach ($states as $s) {
                    if ($s['id_state'] == $address->id_state) {
                        $state_ISO = $s['iso_code'];
                    }
                }

                if (is_null($state_ISO)) {
                    exit('Nu am putut obtine indicativul judetului destinatarului ' . var_export($address, true));
                }

                // get products from order/cart
                $contents = [];
                $products = $order->getCartProducts();
                foreach ($products as $p) {
                    $contents[] = $p['product_quantity'] . ' buc. ' . $p['product_name'];
                }

                $pudoLocationId = Db::getInstance()->getValue('
                    SELECT `c`.`pudo_location_id` FROM `' . _DB_PREFIX_ . 'orders` AS `o`
                    LEFT JOIN `' . _DB_PREFIX_ . 'cart` AS `c` ON (`o`.`id_cart` = `c`.`id_cart`)
                    WHERE `id_order` = ' . $id
                );

                // insert awb
                $sql = 'INSERT INTO ' . _DB_PREFIX_ . "awb_urgent_cargus SET
                                order_id = '" . $id . "',
                                pickup_id = '" . addslashes(Configuration::get('CARGUS_PUNCT_RIDICARE')) . "',
                                name = '" . addslashes(
                    $address->company ? $address->company : trim(
                        implode(' ', [$address->lastname, $address->firstname])
                    )) . "',
                                locality_id = '0',
                                locality_name = '" . addslashes($address->city) . "',
                                county_id = '0',
                                county_name = '" . addslashes($state_ISO) . "',
                                street_id = '0',
                                street_name = '',
                                number = '',
                                address = '" . addslashes(htmlentities(trim(
                        implode('; ', [$address->address1, $address->address2]),
                        '; '
                    ))) . "',
                                postal_code = '" . addslashes(htmlentities($address->postcode)) . "',
                                contact = '" . addslashes(trim(
                        implode(' ', [$address->lastname, $address->firstname])
                    )) . "',
                                phone = '" . addslashes(trim(
                        implode('; ', [$address->phone, $address->phone_mobile]),
                        '; '
                    )) . "',
                                email = '" . addslashes($customer->email) . "',
                                parcels = '" . (Configuration::get('CARGUS_TIP_EXPEDITIE') != 'plic' ? 1 : 0) . "',
                                envelopes = '" . (Configuration::get('CARGUS_TIP_EXPEDITIE') == 'plic' ? 1 : 0) . "',
                                weight = '" . addslashes($weight) . "',
                                value = '" . addslashes($value) . "',
                                cash_repayment = '" . addslashes($cash_repayment) . "',
                                bank_repayment = '" . addslashes($bank_repayment) . "',
                                other_repayment = '',
                                payer = '" . addslashes($payer) . "',
                                morning_delivery = '" . addslashes($morning) . "',
                                saturday_delivery = '" . addslashes($saturday) . "',
                                openpackage = '" . addslashes($openpackage) . "',
                                observations = '',
                                contents = '" . addslashes(htmlentities(trim(implode('; ', $contents), '; '))) . "',
                                barcode = '0',

                                width = '".Configuration::get('CARGUS_LATIME')."',
                                length = '".Configuration::get('CARGUS_LUNGIME')."',
                                height = '".Configuration::get('CARGUS_INALTIME')."',

                                pudo_location_id = '" . $pudoLocationId . "'
                            ";

                $result = Db::getInstance()->execute($sql);

                if ($result == 1) {
                    echo 'ok';
                } else {
                    $errMsg = Db::getInstance()->getMsgError();
                    \Cargus\CargusLog::logError('Error adding order in cargus awb: ' . $errMsg);
                    echo 'Eroare la inserarea datelor in baza!';
                }*/
                exit;
            }
        } catch (Exception $ex) {
            print_r($ex);
        }
    }
}
