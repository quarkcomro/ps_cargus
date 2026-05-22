<?php

namespace Module\Cargus\Grid\Action\AccessibilityChecker;

use PrestaShop\PrestaShop\Core\Grid\Action\Row\AccessibilityChecker\AccessibilityCheckerInterface;
use Module;

/**
 * Checks if print delivery slip option can be visible in order list.
 */
final class CargusPrintAwb implements AccessibilityCheckerInterface
{
    /**
     * {@inheritdoc}
     */
    public function isGranted(array $record)
    {
        $orderId = $record['id_order'];

        $myModule = Module::getInstanceByName('cargus');

        $awb = $myModule->getAwbForOrderId($orderId);

        return $awb ?? null;
    }
}