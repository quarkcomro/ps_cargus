<?php

declare(strict_types=1);

namespace Module\Cargus\Controller\Admin;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Response;
use Context;
use Tools;
use Module;


class CargusNewAwbController extends FrameworkBundleAdminController
{
    /**
    * @param int $orderId
    *
    * @return Response
    */
    public function addAwbAction(int $id_order)
    {
        $link = Context::getContext()->link;

        $addAwbUrl = $link->getAdminLink(
            'CargusAddAwb',
            true,
            [],
            ['id_order' => (int)$id_order]
        );
        //, 'info' => true

        Tools::redirectAdmin($addAwbUrl);
    }

    public function printAwbAction(int $id_order)
    {
        $myModule = Module::getInstanceByName('cargus');

        $awb = $myModule->getAwbForOrderId($id_order);

        $printAwbUrl = '#';

        if ($awb > 0) {
            //print awb
            $link = Context::getContext()->link;

            $printAwbUrl = $link->getAdminLink(
                'CargusAdmin',
                true,
                [],
                ['type' => 'PRINTAWB', 'format' => 0, 'secret' => _COOKIE_KEY_, 'codes' => (int)$awb]
            );
        }

        Tools::redirectAdmin($printAwbUrl);
    }
}
