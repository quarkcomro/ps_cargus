<?php

use Cargus\CargusLog;


class AdminOrdersController extends AdminOrdersControllerCore
{
    private function getSession()
    {
        return \PrestaShop\PrestaShop\Adapter\SymfonyContainer::getInstance()->get('session');
    }

    public function __construct()
    {
        parent::__construct();

        $errors = $this->getSession()->getFlashBag()->get('failure');

        if (is_array($errors) && !empty($errors)) {
            foreach ($errors as $error) {
                $this->errors[] = $error;
            }
        }


        $successes = $this->getSession()->getFlashBag()->get('success');

        if (is_array($successes) && !empty($successes)) {
            foreach ($successes as $success) {
                $this->confirmations[] = $success;
            }
        }
    }

    public function renderList()
    {
        $this->addRowAction('cargus');

        return parent::renderList();
    }

    public function displayCargusLink($token = null, $id, $name = null)
    {
        $awb = Module::getInstanceByName('cargus')->getAwbForOrderId($id);

        $cargus_img = Media::getMediaPath(
            _PS_MODULE_DIR_ . 'cargus' . '/views/img/logosg-30x30.jpg'
        );

        if ($awb > 0) {
            //print awb
            $printAwbUrl = $this->context->link->getAdminLink(
                'CargusAdmin',
                true,
                [],
                ['type' => 'PRINTAWB', 'format' => 0, 'secret' => _COOKIE_KEY_, 'codes' => (int)$awb]
            );

            $url = $printAwbUrl;
            $target = '_blank';
            $text = 'Print AWB';
        } else {
            //create awb url
            $addAwbUrl = $this->context->link->getAdminLink(
                'CargusAddAwb',
                true,
                [],
                ['id_order' => (int)$id, 'info' => true]
            );

            $url = $addAwbUrl;
            $target = null;
            $text = 'Generare AWB';
        }

        return '<a href="'.$url.'" target="'.$target.'" title="'.$text.'" class="default">
        <img style="width: 20px; height: 20px;" src="'.$cargus_img.'"> '.$text.'
        </a>';

        return $tpl->fetch();
    }
}
