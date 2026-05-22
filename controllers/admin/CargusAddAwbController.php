<?php

use Cargus\CargusClass;
use Cargus\CargusLog;

class CargusAddAwbController extends ModuleAdminController
{
    private function getSession()
    {
        return \PrestaShop\PrestaShop\Adapter\SymfonyContainer::getInstance()->get('session');
    }

    public function display()
    {
        $orderId = Tools::getValue('id_order');

        $orderListUrl = $this->context->link->getAdminLink('AdminOrders');
        $cargusOrdersUrl = $this->context->link->getAdminLink('CargusOrders');

        //validate order id
        $order = new Order($orderId);

        if (!Validate::isLoadedObject($order)) {
            $type = 'failure';
            $message_txt = 'Comanda #'.$orderId.' este invalida.';

            if (_PS_VERSION_ >= '1.7.8') {
                $this->get('session')->getFlashBag()->add($type, $message_txt);
            } else {
                $this->getSession()->getFlashBag()->add($type, $message_txt);
            }
            Tools::redirectAdmin($orderListUrl);
        }

        $isOrderInfo = Tools::getValue('info');


        $orderInfoUrl = $this->context->link->getAdminLink('AdminOrders', true, [], ['id_order' => $orderId, 'vieworder' => '']);

        $awb = $this->module->getAwbForOrderId($orderId, true);

        if ($awb !== false) {
            // Already generated.
            $_SESSION['post_status']['confirmations'][] = 'Comanda este in lista de livrari cargus';

            Tools::redirectAdmin($cargusOrdersUrl);
        }

        //insert new awb
        $result = $this->module->insertNewAwb($orderId);

        if ($result != 'ok') {
            //error inserting new awb

            if (_PS_VERSION_ >= '1.7.8') {
                $this->get('session')->getFlashBag()->add('failure', 'Eroare creare awb nou: ' . $result);
            } else {
                $this->getSession()->getFlashBag()->add('failure', 'Eroare creare awb nou: ' . $result);
            }
            Tools::redirectAdmin($orderListUrl);
        }

        //validate awb
        $result = $this->module->createNewAwb($orderId);

        if (!empty($result['errors'])) {
            //error validating awb
            $_SESSION['post_status'] = [
                'errors' => $result['errors'],
            ];

            Tools::redirectAdmin($cargusOrdersUrl);
        }

        //all ok
        if (_PS_VERSION_ >= '1.7.8') {
            $this->get('session')->getFlashBag()->add('success', 'AWB generat');
        } else {
            $this->getSession()->getFlashBag()->add('success', 'AWB generat');
        }

        if ($isOrderInfo) {
            Tools::redirectAdmin($orderInfoUrl);
        }

        Tools::redirectAdmin($orderListUrl);
    }
}