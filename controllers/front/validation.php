<?php
/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/**
 * @since 1.5.0
 */
class CargusValidationModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $cart = $this->context->cart;
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'cargus') {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            exit($this->module->l('This payment method is not available.', 'validation'));
        }

        $this->context->smarty->assign([
            'params' => $_REQUEST,
        ]);

        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $total = (float) $cart->getOrderTotal(true, Cart::BOTH);
        $mailVars = [];

        $this->module->validateOrder(
            (int) $this->context->cart->id,
            Configuration::get('PS_OS_PREPARATION'),
            $total,
            $this->module->displayName,
            null,
            [],
            null,
            false,
            $customer->secure_key
        );

        $pudoLocationId = Db::getInstance()->getValue('
            SELECT pudo_location_id FROM `' . _DB_PREFIX_ . 'cart`
            WHERE `id_cart` = ' . $cart->id
        );

        Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ . 'orders`
            SET `pudo_location_id` = ' . (int) $pudoLocationId . '
            WHERE `id_order` = ' . (int) $this->module->currentOrder
        );

        Tools::redirect(
            'index.php?controller=order-confirmation&id_cart=' . $cart->id .
            '&id_module=' . $this->module->id . '&id_order=' . $this->module->currentOrder .
            '&key=' . $customer->secure_key
        );
    }
}
