<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/helpers/cargus_carrier_tools.php';
require __DIR__.'/lib/classes/WebserviceSpecificManagementSmexport.php';

use Cargus\CargusClass;
use Cargus\CargusLog;
use Cargus\CargusCache;
use Cargus\CarrierPolicy;
use Cargus\CargusCTTD;

use PrestaShopBundle\Controller\Admin\Sell\Order\ActionsBarButton;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollectionInterface;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\SubmitRowAction;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\LinkRowAction;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnInterface;
use PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface;
use PrestaShop\PrestaShop\Core\Grid\Exception\ColumnNotFoundException;
use Module\Cargus\Grid\Action\Type\BlankLinkRowAction;

use Module\Cargus\Controller\Admin\CargusNewAwbController;
use Module\Cargus\Grid\Action\AccessibilityChecker\CargusPrintAwb;
use Module\Cargus\Grid\Action\AccessibilityChecker\CargusCreateAwb;


class Cargus extends PaymentModule
{
    protected $mapNotDisplayed;
    private $saturdayDeliveryValue;
    private $carriersToHide;
    public $id_carrier;
    private $CARGUS_DELIVERY_SATURDAY;
    private $CARGUS_DELIVERY_PRE10;

    private $CARGUS_DELIVERY_SHIPGO;

    private $CARGUS_DELIVERY_PRE12;
    public function __construct()
    {
        $this->name = 'cargus';
        $this->tab = 'shipping_logistics';
        $this->version = '5.1.2';
        $this->author = 'Cargus';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_,
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Cargus');
        $this->description = $this->l('Curier Rapid');

        $this->confirmUninstall = $this->l('Sunteti sigur ca doriti sa dezinstalati modulul Cargus?');

        if (!Configuration::get('CARGUS_API_URL')) {
            $this->warning = $this->l('API URL nu a fost configurat!');
        }

        if (!Configuration::get('CARGUS_API_KEY')) {
            $this->warning = $this->l('API KEY nu a fost configurata!');
        }

        if (!Configuration::get('CARGUS_USERNAME')) {
            $this->warning = $this->l('API USERNAME nu a fost configurata!');
        }

        if (!Configuration::get('CARGUS_PASSWORD')) {
            $this->warning = $this->l('API PASSWORD nu a fost configurata!');
        }
    }

    public function install()
    {
        include_once dirname(__FILE__) . '/sql/install.php';

        if (!parent::install() ||
        !$this->registerHook('displayHeader') ||
        !$this->registerHook('rightColumn') ||
        !$this->registerHook('leftColumn') ||
        !$this->registerHook('displayBackOfficeHeader') ||
        !$this->registerHook('paymentOptions') ||
        !$this->registerHook('paymentReturn') ||
        !$this->registerHook('displayCarrierExtraContent') ||
        !$this->registerHook('additionalCustomerAddressFields') ||
        !$this->registerHook('actionObjectAddressAddAfter') ||
        !$this->registerHook('actionObjectAddressAddBefore') ||
        !$this->registerHook('actionObjectAddressUpdateAfter') ||
        !$this->registerHook('actionObjectAddressUpdateBefore') ||
        !$this->registerHook('actionGetAdminOrderButtons') ||
        !$this->registerHook('actionOrderGridDefinitionModifier') ||
        !$this->registerHook('displayBeforeCarrier') ||
        !$this->registerHook('displayCarrierList') ||
        !$this->registerHook('ActionValidateStepComplete') ||
        !$this->registerHook('ActionCarrierProcess') ||
        !$this->registerHook('ActionCarrierUpdate') ||
        !$this->registerHook('OrderConfirmation') ||
        !$this->registerHook('ActionValidateOrder') ||
        !$this->registerHook('ActionOrderStatusUpdate') ||
        !$this->registerHook('addWebserviceResources') ||
        !$this->installTabs()
        ) {
            return false;
        }

        $hooks = array(
            'displayAdminOrderSide',
            'displayOrderDetail'
        );

        if (_PS_VERSION_ <= '1.7.6.0') {
            $hooks[] = 'displayAdminOrder';
            $hooks[] = 'displayBackOfficeOrderActions';
        }

        foreach ($hooks as $hook) {
            if (!$this->registerHook($hook)) {
                return false;
            }
        }

        $this->alterTable();

        $this->addWebExpressCarrier();
        $this->addCarrier();
        
        $this->addCargusSaturdayCarrier();
        $this->addCargusPre10Carrier();
        $this->addCargusPre12Carrier();

        PrestaShopLogger::addLog('Cargus module installed', 1);

        return true;
    }

    public function installTabs()
    {
        $mainTab = new Tab();

        foreach (Language::getLanguages() as $language) {
            $mainTab->name[$language['id_lang']] = $this->l('Cargus');
        }

        $mainTab->class_name = $this->name;
        $mainTab->id_parent = 0;
        $mainTab->add();

        $tab = new Tab();
        foreach (Language::getLanguages() as $language) {
            $tab->name[$language['id_lang']] = $this->l('Comanda curenta');
        }

        $tab->class_name = 'CargusOrders';
        $tab->module = $this->name;
        $tab->id_parent = $mainTab->id;
        $tab->icon = 'shopping_basket';
        $tab->add();

        $tab = new Tab();
        foreach (Language::getLanguages() as $language) {
            $tab->name[$language['id_lang']] = $this->l('Istoric livrari');
        }

        $tab->class_name = 'CargusHistory';
        $tab->module = $this->name;
        $tab->id_parent = $mainTab->id;
        $tab->icon = 'list';
        $tab->add();

        $tab = new Tab();
        foreach (Language::getLanguages() as $language) {
            $tab->name[$language['id_lang']] = $this->l('Preferinte');
        }

        $tab->class_name = 'CargusPreferences';
        $tab->module = $this->name;
        $tab->id_parent = $mainTab->id;
        $tab->icon = 'settings';
        $tab->add();

        $tab = new Tab();
        foreach (Language::getLanguages() as $language) {
            $tab->name[$language['id_lang']] = $this->l('Istoric comanda');
        }

        $tab->class_name = 'CargusOrderHistory';
        $tab->module = $this->name;
        $tab->id_parent = $mainTab->id;
        $tab->active = false;
        $tab->add();

        $tab = new Tab();
        foreach (Language::getLanguages() as $language) {
            $tab->name[$language['id_lang']] = $this->l('Istoric awb');
        }

        $tab->class_name = 'CargusAwbHistory';
        $tab->module = $this->name;
        $tab->id_parent = $mainTab->id;
        $tab->active = false;
        $tab->add();

        $tab = new Tab();
        foreach (Language::getLanguages() as $language) {
            $tab->name[$language['id_lang']] = $this->l('Editeaza awb');
        }

        $tab->class_name = 'CargusEditAwb';
        $tab->module = $this->name;
        $tab->id_parent = $mainTab->id;
        $tab->active = false;
        $tab->add();

        $tab = new Tab();
        foreach (Language::getLanguages() as $language) {
            $tab->name[$language['id_lang']] = $this->l('Admin');
        }

        $tab->class_name = 'CargusAdmin';
        $tab->module = $this->name;
        $tab->id_parent = $mainTab->id;
        $tab->active = false;
        $tab->add();

        $tab = new Tab();
        foreach (Language::getLanguages() as $language) {
            $tab->name[$language['id_lang']] = $this->l('Generare Awb');
        }
        $tab->class_name = 'CargusAddAwb';
        $tab->module = $this->name;
        $tab->id_parent = $mainTab->id;
        $tab->active = false;
        $tab->add();


        $tab = new Tab();
        foreach (Language::getLanguages() as $language) {
            $tab->name[$language['id_lang']] = $this->l('Trace awb');
        }

        $tab->class_name = 'CargusAwbTrace';
        $tab->module = $this->name;
        $tab->id_parent = $mainTab->id;
        $tab->active = false;
        $tab->add();

        return true;
    }

    public function addCarrier()
    {
        $carrier = new Carrier();
        $carrier->name = 'Cargus';
        $carrier->id_tax_rules_group = 0;
        $carrier->id_zone = 1;
        $carrier->active = true;
        $carrier->deleted = 0;
        $carrier->shipping_handling = false;
        $carrier->range_behavior = 0;
        $carrier->is_module = true;
        $carrier->shipping_external = true;
        $carrier->external_module_name = 'cargus';
        $carrier->need_range = true;

        $languages = Language::getLanguages(true);
        foreach ($languages as $language) {
            if ($language['iso_code'] == 'fr') {
                $carrier->delay[(int) $language['id_lang']] = '24 heures';
            }
            if ($language['iso_code'] == 'ro') {
                $carrier->delay[(int) $language['id_lang']] = '24 ore';
            }
            if ($language['iso_code'] == 'en') {
                $carrier->delay[(int) $language['id_lang']] = '24 hours';
            }
            if ($language['iso_code'] == Language::getIsoById(Configuration::get('PS_LANG_DEFAULT'))) {
                $carrier->delay[(int) $language['id_lang']] = '24 hours';
            }
        }

        if ($carrier->add()) {
            // Save Carrier ID
            Configuration::updateValue('CARGUS_CARRIER_ID', (int) $carrier->id);

            $groups = Group::getGroups(true);
            foreach ($groups as $group) {
                Db::getInstance()->insert(
                    'carrier_group',
                    [
                        'id_carrier' => (int) $carrier->id,
                        'id_group' => (int) $group['id_group'],
                    ]
                );
            }

            $rangePrice = new RangePrice();
            $rangePrice->id_carrier = $carrier->id;
            $rangePrice->delimiter1 = '0';
            $rangePrice->delimiter2 = '10000';
            $rangePrice->add();

            $rangeWeight = new RangeWeight();
            $rangeWeight->id_carrier = $carrier->id;
            $rangeWeight->delimiter1 = '0';
            $rangeWeight->delimiter2 = '10000';
            $rangeWeight->add();

            $zones = Zone::getZones(true);

            foreach ($zones as $zone) {
                Db::getInstance()->insert(
                    'carrier_zone',
                    [
                        'id_carrier' => (int) $carrier->id,
                        'id_zone' => (int) $zone['id_zone'],
                    ]
                );
                Db::getInstance()->insert(
                    'delivery',
                    [
                        'id_carrier' => (int) $carrier->id,
                        'id_range_price' => (int) $rangePrice->id,
                        'id_range_weight' => null,
                        'id_zone' => (int) $zone['id_zone'],
                        'price' => '0',
                    ]
                );
                Db::getInstance()->insert(
                    'delivery',
                    [
                        'id_carrier' => (int) $carrier->id,
                        'id_range_price' => null,
                        'id_range_weight' => (int) $rangeWeight->id,
                        'id_zone' => (int) $zone['id_zone'],
                        'price' => '0',
                    ]
                );
            }

            // Copy Logo
            if (!copy(dirname(__FILE__) . '/views/img/carrier.png', _PS_SHIP_IMG_DIR_ . '/' . (int) $carrier->id . '.jpg')) {
                return false;
            }

            // Return ID Carrier
            return (int) $carrier->id;
        }

        return false;
    }

    public function addWebExpressCarrier()
    {
        $carrier = new Carrier();
        $carrier->name = 'Cargus Ship & GO';
        $carrier->id_tax_rules_group = 0;
        $carrier->id_zone = 1;
        $carrier->active = true;
        $carrier->deleted = 0;
        $carrier->shipping_handling = false;
        $carrier->range_behavior = 0;
        $carrier->is_module = true;
        $carrier->shipping_external = true;
        $carrier->external_module_name = 'cargus';
        $carrier->need_range = true;

        $languages = Language::getLanguages(true);
        foreach ($languages as $language) {
            if ($language['iso_code'] == 'fr') {
                $carrier->delay[(int) $language['id_lang']] = '24 heures';
            }
            if ($language['iso_code'] == 'ro') {
                $carrier->delay[(int) $language['id_lang']] = '24 ore';
            }
            if ($language['iso_code'] == 'en') {
                $carrier->delay[(int) $language['id_lang']] = '24 hours';
            }
            if ($language['iso_code'] == Language::getIsoById(Configuration::get('PS_LANG_DEFAULT'))) {
                $carrier->delay[(int) $language['id_lang']] = '24 hours';
            }
        }

        if ($carrier->add()) {
            // Save Carrier ID
            Configuration::updateValue('CARGUS_SHIP_GO_CARRIER_ID', (int) $carrier->id);

            $groups = Group::getGroups(true);
            foreach ($groups as $group) {
                Db::getInstance()->insert(
                    'carrier_group',
                    [
                        'id_carrier' => (int) $carrier->id,
                        'id_group' => (int) $group['id_group'],
                    ]
                );
            }

            // TODO Do we need any price ranges for this?
            $rangePrice = new RangePrice();
            $rangePrice->id_carrier = $carrier->id;
            $rangePrice->delimiter1 = '0';
            $rangePrice->delimiter2 = '10000';
            $rangePrice->add();

            $rangeWeight = new RangeWeight();
            $rangeWeight->id_carrier = $carrier->id;
            $rangeWeight->delimiter1 = '0';
            $rangeWeight->delimiter2 = '10000';
            $rangeWeight->add();

            $zones = Zone::getZones(true);

            foreach ($zones as $zone) {
                Db::getInstance()->insert(
                    'carrier_zone',
                    [
                        'id_carrier' => (int) $carrier->id,
                        'id_zone' => (int) $zone['id_zone'],
                    ]
                );

                Db::getInstance()->insert(
                    'delivery',
                    [
                        'id_carrier' => (int) $carrier->id,
                        'id_range_price' => (int) $rangePrice->id,
                        'id_range_weight' => null,
                        'id_zone' => (int) $zone['id_zone'],
                        'price' => '0',
                    ]
                );
                Db::getInstance()->insert(
                    'delivery',
                    [
                        'id_carrier' => (int) $carrier->id,
                        'id_range_price' => null,
                        'id_range_weight' => (int) $rangeWeight->id,
                        'id_zone' => (int) $zone['id_zone'],
                        'price' => '0',
                    ]
                );
            }

            // Copy Logo
            if (!copy(dirname(__FILE__) . '/views/img/ship_and_go.jpg', _PS_SHIP_IMG_DIR_ . '/' . (int) $carrier->id . '.jpg')) {
                return false;
            }

            // Return ID Carrier
            return (int) $carrier->id;
        }

        return false;
    }

    public function addCargusSaturdayCarrier()
    {
        $carrier = new Carrier();
        $carrier->name = 'Cargus Saturday Carrier';
        $carrier->id_tax_rules_group = 0;
        $carrier->id_zone = 1;
        $carrier->active = true;
        $carrier->deleted = 0;
        $carrier->shipping_handling = false;
        $carrier->range_behavior = 0;
        $carrier->is_module = true;
        $carrier->shipping_external = true;
        $carrier->external_module_name = 'cargus';
        $carrier->need_range = true;

        $languages = Language::getLanguages(true);
        foreach ($languages as $language) {
            if ($language['iso_code'] == 'fr') {
                $carrier->delay[(int) $language['id_lang']] = '24 heures';
            }
            if ($language['iso_code'] == 'ro') {
                $carrier->delay[(int) $language['id_lang']] = '24 ore';
            }
            if ($language['iso_code'] == 'en') {
                $carrier->delay[(int) $language['id_lang']] = '24 hours';
            }
            if ($language['iso_code'] == Language::getIsoById(Configuration::get('PS_LANG_DEFAULT'))) {
                $carrier->delay[(int) $language['id_lang']] = '24 hours';
            }
        }

        if ($carrier->add()) {
            // Save Carrier ID
            Configuration::updateValue('CARGUS_SATURDAY_CARRIER_ID', (int) $carrier->id);

            $groups = Group::getGroups(true);
            foreach ($groups as $group) {
                Db::getInstance()->insert(
                    'carrier_group',
                    [
                        'id_carrier' => (int) $carrier->id,
                        'id_group' => (int) $group['id_group'],
                    ]
                );
            }

            // TODO Do we need any price ranges for this?
            $rangePrice = new RangePrice();
            $rangePrice->id_carrier = $carrier->id;
            $rangePrice->delimiter1 = '0';
            $rangePrice->delimiter2 = '10000';
            $rangePrice->add();

            $rangeWeight = new RangeWeight();
            $rangeWeight->id_carrier = $carrier->id;
            $rangeWeight->delimiter1 = '0';
            $rangeWeight->delimiter2 = '10000';
            $rangeWeight->add();

            $zones = Zone::getZones(true);

            foreach ($zones as $zone) {
                Db::getInstance()->insert(
                    'carrier_zone',
                    [
                        'id_carrier' => (int) $carrier->id,
                        'id_zone' => (int) $zone['id_zone'],
                    ]
                );

                Db::getInstance()->insert(
                    'delivery',
                    [
                        'id_carrier' => (int) $carrier->id,
                        'id_range_price' => (int) $rangePrice->id,
                        'id_range_weight' => null,
                        'id_zone' => (int) $zone['id_zone'],
                        'price' => '0',
                    ]
                );
                Db::getInstance()->insert(
                    'delivery',
                    [
                        'id_carrier' => (int) $carrier->id,
                        'id_range_price' => null,
                        'id_range_weight' => (int) $rangeWeight->id,
                        'id_zone' => (int) $zone['id_zone'],
                        'price' => '0',
                    ]
                );
            }

            // Copy Logo
            if (!copy(dirname(__FILE__) . '/views/img/ship_and_go.jpg', _PS_SHIP_IMG_DIR_ . '/' . (int) $carrier->id . '.jpg')) {
                return false;
            }

            // Return ID Carrier
            return (int) $carrier->id;
        }

        return false;
    }

    public function addCargusPre10Carrier()
    {
        $carrier = new Carrier();
        $carrier->name = 'Cargus Pre10 Carrier';
        $carrier->id_tax_rules_group = 0;
        $carrier->id_zone = 1;
        $carrier->active = true;
        $carrier->deleted = 0;
        $carrier->shipping_handling = false;
        $carrier->range_behavior = 0;
        $carrier->is_module = true;
        $carrier->shipping_external = true;
        $carrier->external_module_name = 'cargus';
        $carrier->need_range = true;

        $languages = Language::getLanguages(true);
        foreach ($languages as $language) {
            if ($language['iso_code'] == 'fr') {
                $carrier->delay[(int) $language['id_lang']] = '24 heures';
            }
            if ($language['iso_code'] == 'ro') {
                $carrier->delay[(int) $language['id_lang']] = '24 ore';
            }
            if ($language['iso_code'] == 'en') {
                $carrier->delay[(int) $language['id_lang']] = '24 hours';
            }
            if ($language['iso_code'] == Language::getIsoById(Configuration::get('PS_LANG_DEFAULT'))) {
                $carrier->delay[(int) $language['id_lang']] = '24 hours';
            }
        }

        if ($carrier->add()) {
            // Save Carrier ID
            Configuration::updateValue('CARGUS_PRE10_CARRIER_ID', (int) $carrier->id);
            $groups = Group::getGroups(true);
            foreach ($groups as $group) {
                Db::getInstance()->insert(
                    'carrier_group',
                    [
                        'id_carrier' => (int) $carrier->id,
                        'id_group' => (int) $group['id_group'],
                    ]
                );
            }

            // TODO Do we need any price ranges for this?
            $rangePrice = new RangePrice();
            $rangePrice->id_carrier = $carrier->id;
            $rangePrice->delimiter1 = '0';
            $rangePrice->delimiter2 = '10000';
            $rangePrice->add();

            $rangeWeight = new RangeWeight();
            $rangeWeight->id_carrier = $carrier->id;
            $rangeWeight->delimiter1 = '0';
            $rangeWeight->delimiter2 = '10000';
            $rangeWeight->add();

            $zones = Zone::getZones(true);

            foreach ($zones as $zone) {
                Db::getInstance()->insert(
                    'carrier_zone',
                    [
                        'id_carrier' => (int) $carrier->id,
                        'id_zone' => (int) $zone['id_zone'],
                    ]
                );

                Db::getInstance()->insert(
                    'delivery',
                    [
                        'id_carrier' => (int) $carrier->id,
                        'id_range_price' => (int) $rangePrice->id,
                        'id_range_weight' => null,
                        'id_zone' => (int) $zone['id_zone'],
                        'price' => '0',
                    ]
                );
                Db::getInstance()->insert(
                    'delivery',
                    [
                        'id_carrier' => (int) $carrier->id,
                        'id_range_price' => null,
                        'id_range_weight' => (int) $rangeWeight->id,
                        'id_zone' => (int) $zone['id_zone'],
                        'price' => '0',
                    ]
                );
            }

            // Copy Logo
            if (!copy(dirname(__FILE__) . '/views/img/ship_and_go.jpg', _PS_SHIP_IMG_DIR_ . '/' . (int) $carrier->id . '.jpg')) {
                return false;
            }

            // Return ID Carrier
            return (int) $carrier->id;
        }

        return false;
    }

    public function addCargusPre12Carrier()
    {
        $carrier = new Carrier();
        $carrier->name = 'Cargus Pre12 Carrier';
        $carrier->id_tax_rules_group = 0;
        $carrier->id_zone = 1;
        $carrier->active = true;
        $carrier->deleted = 0;
        $carrier->shipping_handling = false;
        $carrier->range_behavior = 0;
        $carrier->is_module = true;
        $carrier->shipping_external = true;
        $carrier->external_module_name = 'cargus';
        $carrier->need_range = true;

        $languages = Language::getLanguages(true);
        foreach ($languages as $language) {
            if ($language['iso_code'] == 'fr') {
                $carrier->delay[(int) $language['id_lang']] = '24 heures';
            }
            if ($language['iso_code'] == 'ro') {
                $carrier->delay[(int) $language['id_lang']] = '24 ore';
            }
            if ($language['iso_code'] == 'en') {
                $carrier->delay[(int) $language['id_lang']] = '24 hours';
            }
            if ($language['iso_code'] == Language::getIsoById(Configuration::get('PS_LANG_DEFAULT'))) {
                $carrier->delay[(int) $language['id_lang']] = '24 hours';
            }
        }

        if ($carrier->add()) {
            // Save Carrier ID
            Configuration::updateValue('CARGUS_PRE12_CARRIER_ID', (int) $carrier->id);

            $groups = Group::getGroups(true);
            foreach ($groups as $group) {
                Db::getInstance()->insert(
                    'carrier_group',
                    [
                        'id_carrier' => (int) $carrier->id,
                        'id_group' => (int) $group['id_group'],
                    ]
                );
            }

            // TODO Do we need any price ranges for this?
            $rangePrice = new RangePrice();
            $rangePrice->id_carrier = $carrier->id;
            $rangePrice->delimiter1 = '0';
            $rangePrice->delimiter2 = '10000';
            $rangePrice->add();

            $rangeWeight = new RangeWeight();
            $rangeWeight->id_carrier = $carrier->id;
            $rangeWeight->delimiter1 = '0';
            $rangeWeight->delimiter2 = '10000';
            $rangeWeight->add();

            $zones = Zone::getZones(true);

            foreach ($zones as $zone) {
                Db::getInstance()->insert(
                    'carrier_zone',
                    [
                        'id_carrier' => (int) $carrier->id,
                        'id_zone' => (int) $zone['id_zone'],
                    ]
                );

                Db::getInstance()->insert(
                    'delivery',
                    [
                        'id_carrier' => (int) $carrier->id,
                        'id_range_price' => (int) $rangePrice->id,
                        'id_range_weight' => null,
                        'id_zone' => (int) $zone['id_zone'],
                        'price' => '0',
                    ]
                );
                Db::getInstance()->insert(
                    'delivery',
                    [
                        'id_carrier' => (int) $carrier->id,
                        'id_range_price' => null,
                        'id_range_weight' => (int) $rangeWeight->id,
                        'id_zone' => (int) $zone['id_zone'],
                        'price' => '0',
                    ]
                );
            }

            // Copy Logo
            if (!copy(dirname(__FILE__) . '/views/img/ship_and_go.jpg', _PS_SHIP_IMG_DIR_ . '/' . (int) $carrier->id . '.jpg')) {
                return false;
            }

            // Return ID Carrier
            return (int) $carrier->id;
        }

        return false;
    }

    public function uninstall()
    {
        include_once dirname(__FILE__) . '/sql/uninstall.php';

//        Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "configuration WHERE `name` LIKE '%CARGUS%'");

        $this->removeCarrier();

        PrestaShopLogger::addLog('Cargus module uninstalled', 1);

        return $this->uninstallTabs() &&
                parent::uninstall();
    }

    public function removeCarrier()
    {
        $carrier = new Carrier(Configuration::get('CARGUS_CARRIER_ID'));
        $carrier->delete();

        $carrier = new Carrier(Configuration::get('CARGUS_SHIP_GO_CARRIER_ID'));
        $carrier->delete();

        $carrier = new Carrier(Configuration::get('CARGUS_PRE12_CARRIER_ID'));
        $carrier->delete();

        $carrier = new Carrier(Configuration::get('CARGUS_PRE10_CARRIER_ID'));
        $carrier->delete();

        $carrier = new Carrier(Configuration::get('CARGUS_SATURDAY_CARRIER_ID'));
        $carrier->delete();
        
    }

    public function uninstallTabs()
    {
        $moduleTabs = Tab::getCollectionFromModule($this->name);

        if (!empty($moduleTabs)) {
            foreach ($moduleTabs as $moduleTab) {
                $moduleTab->delete();
            }
        }

        // delete main tab
        $id_tab = (int) Tab::getIdFromClassName($this->name);
        $tab = new Tab($id_tab);

        return $tab->delete();
    }

    public function getContent()
    {
        $output = null;

        if (Tools::isSubmit('submit' . $this->name)) {
            $url = (string) Tools::getValue('CARGUS_API_URL');
            $key = (string) Tools::getValue('CARGUS_API_KEY');
            $user = (string) Tools::getValue('CARGUS_USERNAME');
            $pass = (string) Tools::getValue('CARGUS_PASSWORD');

            $valid = true;

            if (!$url || empty($url) || !Validate::isGenericName($url)) {
                $output .= $this->displayError($this->l('API Url invalid!'));
                $valid = false;
            }

            if (!$key || empty($key) || !Validate::isGenericName($key)) {
                $output .= $this->displayError($this->l('Subscription Key invalid!'));
                $valid = false;
            }
            if (!$user || empty($user) || !Validate::isGenericName($user)) {
                $output .= $this->displayError($this->l('Username invalid!'));
                $valid = false;
            }

            if (!$pass || empty($pass) || !Validate::isGenericName($pass)) {
                $output .= $this->displayError($this->l('Password invalid!'));
                $valid = false;
            }

            // test login
            if ($valid) {
                $cargus = new CargusClass(
                    $url,
                    $key
                );

                $fields = [
                    'UserName' => $user,
                    'Password' => $pass,
                ];

                try {
                    $cargus->CallMethod('LoginUser', $fields, 'POST', 'useException');
                } catch (\Exception $e) {
                    $output .= $this->displayError(
                        $this->l('Verificare credentiale esuata: cod ' . $e->getCode() . ', mesaj: ' . $e->getMessage())
                    );
                    $valid = false;
                }
            }

            if ($valid) {
                Configuration::updateValue('CARGUS_API_URL', $url);
                Configuration::updateValue('CARGUS_API_KEY', $key);
                Configuration::updateValue('CARGUS_USERNAME', $user);
                Configuration::updateValue('CARGUS_PASSWORD', $pass);
                $output .= $this->displayConfirmation($this->l('Configurare salvata!'));

                $this->alterTable();
            }
        }

        return $output . $this->displayForm();
    }

    public function alterTable()
    {
        try {
            $sql = 'ALTER TABLE `' . _DB_PREFIX_ .
                   'awb_urgent_cargus` ADD COLUMN ReturnCode VARCHAR(50) AFTER `barcode`';
            if (Db::getInstance()->execute($sql) == false) {
                \Cargus\CargusLog::logError("Error executing query: $sql");
            }

            $sql = 'ALTER TABLE `' . _DB_PREFIX_ .
                   'awb_urgent_cargus` ADD COLUMN ReturnAwb VARCHAR(50) AFTER `barcode`';
            if (Db::getInstance()->execute($sql) == false) {
                \Cargus\CargusLog::logError("Error executing query: $sql");
            }
        } catch (Exception $e) {
            \Cargus\CargusLog::logError("Error executing query: " . $e->getMessage());
        }
    }

    public function displayForm()
    {
        // Get default language
        $defaultLang = (int) Configuration::get('PS_LANG_DEFAULT');

        // Init Fields form array
        $fieldsForm[0]['form'] = [
            'legend' => [
                'title' => $this->l('Configurare'),
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('API Url'),
                    'name' => 'CARGUS_API_URL',
                    'size' => 20,
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Subscription Key'),
                    'name' => 'CARGUS_API_KEY',
                    'size' => 20,
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Username'),
                    'name' => 'CARGUS_USERNAME',
                    'size' => 20,
                    'required' => true,
                ],
                [
                    'type' => 'password',
                    'label' => $this->l('Password'),
                    'name' => 'CARGUS_PASSWORD',
                    'size' => 20,
                    'required' => true,
                ],
            ],
            'submit' => [
                'title' => $this->l('Salveaza'),
                'class' => 'btn btn-default pull-right',
            ],
        ];

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        // Language
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = $defaultLang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit' . $this->name;
        $helper->toolbar_btn = [
            'save' => [
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                    '&token=' . Tools::getAdminTokenLite('AdminModules'),
            ],
            'back' => [
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list'),
            ],
        ];

        // Load current value
        $helper->fields_value['CARGUS_API_URL'] = Tools::getValue(
            'CARGUS_API_URL',
            Configuration::get('CARGUS_API_URL')
        );
        $helper->fields_value['CARGUS_API_KEY'] = Tools::getValue(
            'CARGUS_API_KEY',
            Configuration::get('CARGUS_API_KEY')
        );
        $helper->fields_value['CARGUS_USERNAME'] = Tools::getValue(
            'CARGUS_USERNAME',
            Configuration::get('CARGUS_USERNAME')
        );
        $helper->fields_value['CARGUS_PASSWORD'] = Tools::getValue(
            'CARGUS_PASSWORD',
            Configuration::get('CARGUS_PASSWORD')
        );

        return $helper->generateForm($fieldsForm);
    }

    public function hookDisplayHeader($params)
    {
        if ($params['cart']->id_carrier == Configuration::get('CARGUS_SHIP_GO_CARRIER_ID')) {
            $this->context->smarty->assign(
                'CARGUS_SHIP_GO_CARRIER_ID',
                Configuration::get('CARGUS_SHIP_GO_CARRIER_ID')
            );
        }

        $this->context->smarty->assign(
            'CARGUS_API_STREETS',
            $this->context->link->getModuleLink($this->name, 'getStreets', [], true)
        );

        $this->context->smarty->assign(
            'CARGUS_API_CITIES',
            $this->context->link->getModuleLink($this->name, 'getCities', [], true)
        );

        return $this->display(__FILE__, 'views/templates/hook/frontend_header.tpl');
    }

    public function hookDisplayBackOfficeHeader($params)
    {
        if (strtolower(Tools::getValue('controller')) == 'adminorders' && !Tools::getIsset('id_order')) {
            $this->context->smarty->assign('CargusAdminToken', Tools::getAdminTokenLite('CargusAdmin'));

            return $this->display(__FILE__, 'views/templates/hook/admin_mods.tpl');
        } elseif (strtolower(Tools::getValue('controller')) == 'order') {
            return $this->display(__FILE__, 'views/templates/hook/cargus_autocomplete.tpl');
        }
    }

    /**
     * @param $params
     *
     * @return void
     */
    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }

        if ($params['cart']->id_carrier != Configuration::get('CARGUS_SHIP_GO_CARRIER_ID')) {
            return;
        }

        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        $payment_options = [
            $this->getOfflinePaymentOption(),
        ];

        return $payment_options;
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getOfflinePaymentOption()
    {
        $offlineOption = new \PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $offlineOption->setCallToActionText('Plata ramburs la Ship & Go')
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', [], true))
            ->setAdditionalInformation(
                $this->context->smarty->fetch('module:cargus/views/templates/front/payment_infos.tpl')
            )
            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/logosg-30x30.jpg'));

        return $offlineOption;
    }

    public function getOrderShippingCost($params, $shipping_cost)
    {

        $this->CARGUS_DELIVERY_SATURDAY = new CarrierPolicy(
            'CARGUS_SATURDAY_DELIVERY_TARIF',
            'CARGUS_SATURDAY_DELIVERY',
            'CARGUS_SATURDAY_CARRIER_ID',
            'CARGUS_SATURDAY_MESAJ',
            '0',
            [
                'rule1' => 'isThursdayOrFriday',
                'rule2' => 'isSaturdayDeliveryAvailableInLocation',
                'rule3' => 'check_last_call_order_time_friday',
            ]
        );
        
        $this->CARGUS_DELIVERY_PRE10 = new CarrierPolicy(
            'CARGUS_PRE10_DELIVERY_TARIF',
            'CARGUS_PRE10_DELIVERY',
            'CARGUS_PRE10_CARRIER_ID',
            'CARGUS_PRE10_MESAJ',
            '10',
            [
                'rule1' => 'isSundayToThursday',
                'rule2' => 'check_last_call_order_time_thursday',
                'rule3' => function () {
                    return checkMorningDeliveryServiceBasedOnLocations(10);
                }
            ]
        );

        //can_order_based_on_time rule

        $this->CARGUS_DELIVERY_PRE12 = new CarrierPolicy(
            'CARGUS_PRE12_DELIVERY_TARIF',
            'CARGUS_PRE12_DELIVERY',
            'CARGUS_PRE12_CARRIER_ID',
            'CARGUS_PRE12_MESAJ',
            '12',
            [
                'rule1' => 'isSundayToThursday',
                'rule2' => 'check_last_call_order_time_thursday',
                'rule3' => function () {
                    return checkMorningDeliveryServiceBasedOnLocations(12);
                }
            ]
        );

        $this->CARGUS_DELIVERY_SHIPGO = new CarrierPolicy(
            'CARGUS_SHIPGO_DELIVERY_TARIF',
            'CARGUS_SHIPGO_DELIVERY',
            'CARGUS_SHIP_GO_CARRIER_ID',
            'CARGUS_SHIPGO_MESAJ',
            '0',
            []
        );

        $CARGUS_DELIVERY_STANDARD = new CarrierPolicy(
            'CARGUS_COST_FIX',
            'CARGUS_SHIPGO_DELIVERY',
            'CARGUS_SHIP_GO_CARRIER_ID',
            'CARGUS_STANDARD_MESAJ',
            '0',
            []
        );
        
        $tarif_field = null;

        switch ($this->id_carrier) {
            case $this->getSaturdayDeliveryCarrierID():
                // Check if option is turned ON in preferences and also if requirements for such delivery are met ()
                if( !$this->CARGUS_DELIVERY_SATURDAY->isAvailable() ) {
                    return false;
                }
                $tarif_field = $this->CARGUS_DELIVERY_SATURDAY->get_price_field();
                $CargusCarrier = $this->CARGUS_DELIVERY_SATURDAY;
                break;
            
            case $this->getPre10DeliverCarrierID():
                if( !$this->CARGUS_DELIVERY_PRE10->isAvailable() ) {
                    return false;
                }
                // Check if option is turned ON in preferences and also if requirements for such delivery are met.
                $CargusCarrier = $this->CARGUS_DELIVERY_PRE10;
                $tarif_field = $this->CARGUS_DELIVERY_PRE10->get_price_field();
                break;
            case $this->getPre12DeliverCarrierID():
                if( !$this->CARGUS_DELIVERY_PRE12->isAvailable() ) {
                    return false;
                }
                $tarif_field = $this->CARGUS_DELIVERY_PRE12->get_price_field();
                $CargusCarrier = $this->CARGUS_DELIVERY_PRE12;
                break;
            case $this->getShipGoDeliveryCarrierID():
                if ( !$this->CARGUS_DELIVERY_SHIPGO->isAvailable() ) {
                    return false;
                }
                $tarif_field = $this->CARGUS_DELIVERY_SHIPGO->get_price_field();
                $CargusCarrier = $this->CARGUS_DELIVERY_SHIPGO;
                // Check only if option is available in preferences
                break;
            case $this->getStandardCargusCarrierID():
                $tarif_field = 'CARGUS_COST_FIX';
                $CargusCarrier = $CARGUS_DELIVERY_STANDARD;
                break;
        }
    
        if ($tarif_field !== null) {
            return $this->calculeazaTransport($params, $shipping_cost, $tarif_field, $CargusCarrier);
        }     
        
    }

    public function getOrderShippingCostExternal($params)
    {
        return $this->calculeazaTransport($params);
    }

    public function calculeazaTransport($cart, $shipping_cost = null, $tarif_field, $CargusCarrier)
    {
        try {

            if ($shipping_cost && $shipping_cost > 0) {
                return $shipping_cost;
            }

            // if fixed price is set there is no need for API call
            $cost_fix_expeditie = Configuration::get($tarif_field);

            // get currency id from cart
            $id_currency = (int) $cart->id_currency;

            // convert prices
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
            $currency_DEFAULT = new Currency($id_currency);

            $orderTotal = Tools::convertPriceFull(
                $cart->getOrderTotal(true, Cart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING),
                $currency_DEFAULT,
                $currency_RON
            );

            $plafon_plata_dest = Configuration::get('CARGUS_TRANSPORT_GRATUIT');

            // if order total is greater than set value then shipment is free
            if (round($orderTotal, 2) > $plafon_plata_dest && $plafon_plata_dest > 0) {
                return 0;
            }

            if ($cost_fix_expeditie != '') {
                return round($cost_fix_expeditie, 2);
            }

            // get weight
            $total_weight = ceil($cart->getTotalWeight());
            if ($total_weight == 0) {
                $total_weight = 1;
            }

            // get expedition value
            if (Configuration::get('CARGUS_ASIGURARE') == '1') {
                $valoare_declarata = round($orderTotal, 2);
            } else {
                $valoare_declarata = 0;
            }

            // reimbursement value
            $suma_ramburs = round($orderTotal, 2);

            // if payment method is not reimbursement then value is 0
            $url_path = $_SERVER['REQUEST_URI'];
            if (
                !strstr($url_path, 'cashondelivery')
                && !strstr($url_path, 'cod')
                && !strstr($url_path, 'ramburs')
                && !strstr($url_path, 'cash')
                && !strstr($url_path, 'numerar')

                && strstr($url_path, 'module')
            ) {
                $suma_ramburs = 0;
            }

            // get reimbursement values
            if (Configuration::get('CARGUS_TIP_RAMBURS') == 'cont') {
                $ramburs_cont_colector = $suma_ramburs;
                $ramburs_cash = 0;
            } else {
                $ramburs_cont_colector = 0;
                $ramburs_cash = $suma_ramburs;
            }

            $id_address_delivery = (int) $cart->id_address_delivery;
            // get delivery address
            if ($id_address_delivery != 0) {
                $delivery_address = new Address($id_address_delivery);
                if (!isset($delivery_address->city) || strlen($delivery_address->city) < 3) {
                    return null;
                }
            } else {
                return null;
            }

            // get state from delivery address
            $states = State::getStatesByIdCountry($delivery_address->id_country);
            $state_ISO = null;
            foreach ($states as $s) {
                if ($s['id_state'] == $delivery_address->id_state) {
                    $state_ISO = $s['iso_code'];
                }
            }
            if (is_null($state_ISO)) {
                throw new Exception('Nu am putut obtine indicativul judetului destinatarului');
            }

            $cargus = new CargusClass(
                Configuration::get('CARGUS_API_URL'),
                Configuration::get('CARGUS_API_KEY')
            );

            // UC login user
            $fields = [
                'UserName' => Configuration::get('CARGUS_USERNAME'),
                'Password' => Configuration::get('CARGUS_PASSWORD'),
            ];

            $token = $cargus->CallMethod('LoginUser', $fields, 'POST');

            // UC default pickup location
            $location = [];
            $pickups = $cargus->CallMethod('PickupLocations', [], 'GET', $token);

            if (is_null($pickups)) {
                throw new Exception('Nu exista niciun punct de ridicare asociat acestui cont!');
            }
            foreach ($pickups as $pick) {
                if (Configuration::get('CARGUS_PUNCT_RIDICARE') == $pick['LocationId']) {
                    $location = $pick;
                    break;
                }
            }


            // UC shipping calculation
            $fields = [
                'FromLocalityId' => $location['LocalityId'],
                'ToLocalityId' => 0,
                'FromCountyName' => '',
                'FromLocalityName' => '',
                'ToCountyName' => $state_ISO,
                'ToLocalityName' => $delivery_address->city,
                'Parcels' => Configuration::get('CARGUS_TIP_EXPEDITIE') != 'plic' ? 1 : 0,
                'Envelopes' => Configuration::get('CARGUS_TIP_EXPEDITIE') == 'plic' ? 1 : 0,
                'TotalWeight' => $total_weight,
                'DeclaredValue' => $valoare_declarata,
                'CashRepayment' => $ramburs_cash,
                'BankRepayment' => $ramburs_cont_colector,
                'OtherRepayment' => '',
                'PaymentInstrumentId' => 0,
                'PaymentInstrumentValue' => 0,
                'OpenPackage' => Configuration::get('CARGUS_DESCHIDERE_COLET') != 1 ? false : true,
                'SaturdayDelivery' => Configuration::get('CARGUS_SAMBATA') != 1 ? false : true,
                'MorningDelivery' => Configuration::get('CARGUS_DIMINEATA') != 1 ? false : true,
                'ShipmentPayer' => Configuration::get('CARGUS_PLATITOR') != 'expeditor' ? 2 : 1,
                'DeliveryTime' => $CargusCarrier->getDeliveryTime(),
            ];
            
            if (!empty(Configuration::get('CARGUS_ID_TARIF'))) {
                $fields['PriceTableId'] = Configuration::get('CARGUS_ID_TARIF');
            }

            /*if (Configuration::get('CARGUS_SERVICIU') == 1) {
                if ($total_weight <= 31) {
                    $fields['ServiceId'] = 34;
                } elseif ($total_weight <= 50) {
                    $fields['ServiceId'] = 35;
                } else {
                    $fields['ServiceId'] = 36;
                }
            }*/

            $result = $cargus->CallMethod('ShippingCalculation', $fields, 'POST', $token);
            if (!isset($result['Subtotal'])) {
                if (isset($result['Error'])) {
                    throw new \Exception($result['Error']);
                }

                return null;
            }

            if (!is_null($result)) {
                return Tools::convertPriceFull($result['Subtotal'], $currency_RON, $currency_DEFAULT);
            } else {
                throw new \Exception('fields sent ' . print_r($fields, true));
//                echo '<pre>';
//                print_r($fields);
//                exit;
            }
        } catch (\Exception $ex) {
            $errMsg = $ex->getMessage();

            \Cargus\CargusLog::logError('Error ' . __FUNCTION__ . ': ' . $errMsg . ', data: ' . print_r($fields, true));// . print_r($ex, true));

//            exit($ex->getMessage());
//            return $ex->getMessage();
//            return null;

/*            switch ($errMsg) {
                case "The recipient's locality could not be matched!":
                    $errMsg = 'Cargus: Va rugam verificati localitatea de livrare!';
                    break;
            }*/


            return 0;
        }
    }

    /**
     * Reposition an array element by its key.
     *
     * @param array $array The array being reordered
     * @param string|int $key They key of the element you want to reposition
     * @param int $order The position in the array you want to move the element to. (0 is first)
     *
     * @throws \Exception
     */
    public function repositionArrayElement(array &$array, $key, int $order)
    {
        if (($a = array_search($key, array_keys($array))) === false) {
            CargusLog::logError("The {$key} cannot be found in the given array=" . print_r($array, true));
            throw new \Exception("The {$key} cannot be found in the given array.");
        }
        $p1 = array_splice($array, $a, 1);
        $p2 = array_splice($array, 0, $order);
        $array = array_merge($p2, $p1, $array);
    }

    /**
     * Add fields in Customer Form
     *
     * @param array $params parameters (@see CustomerFormatter->getFormat())
     *
     * @return bool|array of extra FormField
     */
    public function hookAdditionalCustomerAddressFields($params)
    {
        //this hook is available only in >=1.7.7

        // if autocomplete is wanted
        $isAutocompleteEnabled = Configuration::get('CARGUS_POSTALCODE');

        if (!$isAutocompleteEnabled) {
            return true;
        }

        if (!isset($params['fields']['id_state'])) {
            return true;
        }

        $extra_fields = [];

        $id_address = Tools::getValue('id_address');
        if ($id_address) {
            $values = Db::getInstance()->getRow(
                'select `street`,`street_number` from `' . _DB_PREFIX_ . 'cargus_street` where id_address = '
                . (int) $id_address
            );
        }

        $extra_fields['street_cargus'] = (new FormField())
            ->setName('street_cargus')
            ->setType('text')
            ->setLabel($this->l('Street'));

        if (!empty($values['street'])) {
            $extra_fields['street_cargus']->setValue($values['street']);
        }

        $extra_fields['street_number_cargus'] = (new FormField())
            ->setName('street_number_cargus')
            ->setType('text')
            ->setLabel($this->l('Street number'));

        if (!empty($values['street_number'])) {
            $extra_fields['street_number_cargus']->setValue($values['street_number']);
        }

        // reorder existing fields
        // Where is VAT?
        $position = (int) array_search('vat_number', array_keys($params['fields']), null) + 1;

        $this->repositionArrayElement($params['fields'], 'city', $position);
        $this->repositionArrayElement($params['fields'], 'id_state', $position);

        // hide address1
        $params['fields']['address1']->setType('hidden');
        $params['fields']['address1']->setRequired(false);

        // We want to insert it before Address 2

        // Where is Address 2?
        $position = (int) array_search('address2', array_keys($params['fields']), null);

        // How many fields do we have?
        $fieldcount = count($params['fields']);

        // Lets insert it...
        $result = array_merge(
            array_slice($params['fields'], 0, $position),
            $extra_fields,
            array_slice($params['fields'], $position - $fieldcount)
        );

        // And set $params with the new updated array
        $params['fields'] = $result;
    }

    /**
     * When adding addresss, we have to save neighborhood anywhere...
     *
     * @param $params
     *
     * @throws PrestaShopDatabaseException
     */
    public function hookActionObjectAddressAddAfter($params)
    {
        $this->saveExtraAddressFields($params['object']->id);
    }

    /**
     *  When updating addresss too...
     *
     * @param $params
     *
     * @throws PrestaShopDatabaseException
     */
    public function hookActionObjectAddressUpdateAfter($params)
    {
        $this->saveExtraAddressFields($params['object']->id);
    }

    public function hookActionObjectAddressAddBefore($params)
    {
        // if autocomplete is used
        $isAutocompleteEnabled = Configuration::get('CARGUS_POSTALCODE');

        if (!$isAutocompleteEnabled) {
            return true;
        }

        $address = $params['object'];

        $address->address1 = $address->street_cargus . ', nr. ' . $address->street_number_cargus;
    }

    public function hookActionObjectAddressUpdateBefore($params)
    {
        // if autocomplete is used
        $isAutocompleteEnabled = Configuration::get('CARGUS_POSTALCODE');

        if (!$isAutocompleteEnabled) {
            return true;
        }

        $address = $params['object'];

        if ($address->deleted != 1) {
            $address->address1 = $address->street_cargus . ', nr. ' . $address->street_number_cargus;
        }
    }

    /**
     * And here we save the extra address fields!
     *
     * @param $id_address
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     */
    private function saveExtraAddressFields($id_address)
    {
        $isAutocompleteEnabled = Configuration::get('CARGUS_POSTALCODE');

        if (!$isAutocompleteEnabled) {
            return true;
        }

        $street_cargus = Tools::getValue('street_cargus', null);
        $street_number = Tools::getValue('street_number_cargus', null);

        if ($id_address) {
            return Db::getInstance()->insert(
                'cargus_street',
                [
                    'id_address' => (int) $id_address,
                    'street' => pSQL($street_cargus),
                    'street_number' => pSQL($street_number),
                ],
                true,
                true,
                Db::REPLACE
            );
        }

        return true;
    }

    public function getAwbForOrderId($orderId, $checkAll = false)
    {
        $barcode = Db::getInstance()->getValue('
                    SELECT `barcode` FROM `' . _DB_PREFIX_ . 'awb_urgent_cargus`                    
                    WHERE `order_id` = ' . (int)$orderId .' '.($checkAll ? '' : 'AND `barcode`<>0').' GROUP BY `order_id` ORDER BY `order_id`'
        );

        return $barcode;
    }

    public function getAwbInfoForOrderId($orderId)
    {
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_);

        $sql = 'SELECT `barcode`,`ReturnCode`,`ReturnAwb` FROM `' . _DB_PREFIX_ . 'awb_urgent_cargus`                    
                    WHERE `order_id` = ' . (int)$orderId . ' AND `barcode`<>0 GROUP BY `order_id` ORDER BY `order_id`';

        return $db->getRow($sql);
    }

    public function hookDisplayAdminOrder(array $params)
    {
        if (Configuration::get('CARGUS_AWB_RETUR') <= 0) {
            return '';
        }

        $id_order = $params['id_order'];

        //get order awb info
        $info = $this->getAwbInfoForOrderId($id_order);

        $returnCode = $info['ReturnCode'];
        $returnAwb = $info['ReturnAwb'];

        if (empty($returnAwb) && empty($returnCode)) {
            return '';
        }

        switch (Configuration::get('CARGUS_AWB_RETUR')) {
            case 1:
                //voucher
                $showQr = $returnCode;
                $showText = 'Voucher retur comanda';
                break;

            default:
                //awb return
                $showQr = $returnAwb;
                $showText = 'AWB retur comanda';
                break;
        }

        if (empty($showQr)) {
            return '';
        }

        $showImg = Media::getMediaPath(
            _PS_MODULE_DIR_ . $this->name . '/views/img/logosg-30x30.jpg'
        );

        $this->context->smarty->assign(array(
            'showText' => $showText,
            'showQr' => $showQr,
            'showImg' => $showImg
        ));

        return $this->display(__FILE__, 'displayAdminOrder.tpl');
    }

    public function hookDisplayBackOfficeOrderActions(array $params)
    {
        $order_id = $params['id_order'];

        $awb = $this->getAwbForOrderId($order_id);

        $cargus_img = Media::getMediaPath(
            _PS_MODULE_DIR_ . $this->name . '/views/img/logosg-30x30.jpg'
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
                ['id_order' => (int)$order_id, 'info' => true]
            );

            $url = $addAwbUrl;
            $target = null;
            $text = 'Generare AWB';
        }

        $this->context->smarty->assign(array(
            'url' => $url,
            'target' => $target,
            'text' => $text,
            'orderId' => $order_id,
            'img' => $cargus_img,
            'img_dir' => _PS_IMG_DIR_,
        ));

        return $this->display(__FILE__, 'displayBackOfficeOrderActions.tpl');
    }

    /**
     * Add buttons to main buttons bar
     */
    public function hookActionGetAdminOrderButtons(array $params)
    {
        $order = new Order($params['id_order']);

        /** @var \PrestaShopBundle\Controller\Admin\Sell\Order\ActionsBarButtonsCollection $bar */
        $bar = $params['actions_bar_buttons_collection'];

        $awb = $this->getAwbForOrderId($order->id);

        if ($awb > 0) {
            //print awb
            $printAwbUrl = $this->context->link->getAdminLink(
                'CargusAdmin',
                true,
                [],
                ['type' => 'PRINTAWB', 'format' => 0, 'secret' => _COOKIE_KEY_, 'codes' => (int)$awb]
            );

            $bar->add(
                new ActionsBarButton(
                    'btn-action',
                    ['href' => $printAwbUrl, 'target' => '_blank'],
                    '<img style="width: 20px; height: 20px;" src="' . Media::getMediaPath(
                        _PS_MODULE_DIR_ . $this->name . '/views/img/logosg-30x30.jpg'
                    ) . '"> Print AWB'
                )
            );
        } else {
            //create awb url
            $addAwbUrl = $this->context->link->getAdminLink(
                'CargusAddAwb',
                true,
                [],
                ['id_order' => (int)$order->id, 'info' => true]
            );

            $bar->add(
                new ActionsBarButton(
                    'btn-secondary',
                    ['href' => $addAwbUrl],
                    '<img style="width: 20px; height: 20px;" src="' . Media::getMediaPath(
                        _PS_MODULE_DIR_ . $this->name . '/views/img/logosg-30x30.jpg'
                    ) . '"> Generare AWB'
                )
            );
        }
    }

    public function insertNewAwb($id)
    {
        try {
            // obtin detaliile comenzii
            $order = new Order($id);

            // obtin adresa
            $address = new Address($order->id_address_delivery);

            // obtin detaliile clientului
            $customer = new Customer($order->id_customer);

            // obiectele pentru cursul de schimb
            $key_ron  = 0;
            $currency = Currency::getCurrencies();
            foreach ($currency as $cc) {
                if (strtolower($cc['iso_code']) == 'ron') {
                    $key_ron = $cc['id_currency'];
                }
            }
            if ($key_ron == 0) {
                return 'Trebuie adaugata moneda RON';
//            exit('Trebuie adaugata moneda RON');
            }
            $currency_RON     = new Currency($key_ron);
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
            $weight   = 0;

            if (isset($shipping[0]['weight'])) {
                $weight = ceil($shipping[0]['weight']);
            }

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
                $payer          = 1;
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
                return 'Nu am putut obtine indicativul judetului destinatarului ' . var_export($address, true);
//            exit('Nu am putut obtine indicativul judetului destinatarului ' . var_export($address, true));
            }

            // get products from order/cart
            $contents = [];
            $products = $order->getCartProducts();


            $total_width = 0;
            $total_height = 0;
            $total_length = 0;

            foreach ($products as $p) {
                $contents[] = $p['product_quantity'] . ' buc. ' . $p['product_name'];

                $total_width += $p['width'];
                $total_height += $p['height'];
                $total_length += $p['depth'];
            }

            $pudoLocationId = Db::getInstance()->getValue(
                '
                    SELECT `c`.`pudo_location_id` FROM `' . _DB_PREFIX_ . 'orders` AS `o`
                    LEFT JOIN `' . _DB_PREFIX_ . 'cart` AS `c` ON (`o`.`id_cart` = `c`.`id_cart`)
                    WHERE `id_order` = ' . $id
            );

            $deliveryTime = Db::getInstance()->getValue(
                '
                    SELECT `c`.`delivery_time` FROM `' . _DB_PREFIX_ . 'orders` AS `o`
                    LEFT JOIN `' . _DB_PREFIX_ . 'cart` AS `c` ON (`o`.`id_cart` = `c`.`id_cart`)
                    WHERE `id_order` = ' . $id
            );

            $saturdayDelivery = Db::getInstance()->getValue(
                '
                    SELECT `c`.`saturday_delivery` FROM `' . _DB_PREFIX_ . 'orders` AS `o`
                    LEFT JOIN `' . _DB_PREFIX_ . 'cart` AS `c` ON (`o`.`id_cart` = `c`.`id_cart`)
                    WHERE `id_order` = ' . $id
            );

            // insert awb
            $sql = 'INSERT INTO ' . _DB_PREFIX_ . "awb_urgent_cargus SET
                                order_id = '" . $id . "',
                                pickup_id = '" . addslashes(Configuration::get('CARGUS_PUNCT_RIDICARE')) . "',
                                name = '" . addslashes(
                                    preg_match('/^[A-Za-z0-9]+$/', $address->company)
                                        ? $address->company
                                        : trim(implode(' ', [$address->lastname, $address->firstname]))
                                ) . "',
                                locality_id = '0',
                                locality_name = '" . addslashes($address->city) . "',
                                county_id = '0',
                                county_name = '" . addslashes($state_ISO) . "',
                                street_id = '0',
                                street_name = '',
                                number = '',
                                address = '" . addslashes(
                       htmlentities(
                           trim(
                               implode('; ', [$address->address1, $address->address2]),
                               '; '
                           )
                       )
                   ) . "',
                                postal_code = '" . addslashes(htmlentities($address->postcode)) . "',
                                contact = '" . addslashes(
                       trim(
                           implode(' ', [$address->lastname, $address->firstname])
                       )
                   ) . "',
                                phone = '" . addslashes(
                       trim(
                           implode('; ', [$address->phone, $address->phone_mobile]),
                           '; '
                       )
                   ) . "',
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
                                saturday_delivery = '" . $saturdayDelivery . "',
                                openpackage = '" . addslashes($openpackage) . "',
                                observations = '',
                                contents = '" . addslashes(htmlentities(trim(implode('; ', $contents), '; '))) . "',
                                barcode = '0',
                                
                                width = '" . ((int)Configuration::get('CARGUS_LATIME') > 0 ? Configuration::get('CARGUS_LATIME') : $total_width) . "',
                                length = '" . ((int)Configuration::get('CARGUS_LUNGIME') > 0 ? Configuration::get('CARGUS_LUNGIME') : $total_length) . "',
                                height = '" . ((int)Configuration::get('CARGUS_INALTIME') > 0 ? Configuration::get('CARGUS_INALTIME') : $total_height) . "',                                
                             
                                pudo_location_id = '" . $pudoLocationId . "',
                                delivery_time = '" . $deliveryTime . "'
                            ";

            $result = Db::getInstance()->execute($sql);

            if ($result == 1) {
                return 'ok';
//            echo 'ok';
            } else {
                $errMsg = Db::getInstance()->getMsgError();
                \Cargus\CargusLog::logError('Error adding order in cargus awb: ' . $errMsg);

//            echo 'Eroare la inserarea datelor in baza!';
                return 'Eroare la inserarea datelor in baza!';
            }
        } catch (\Exception $ex) {
            $errMsg = $ex->getMessage();

            \Cargus\CargusLog::logError('Error adding order in cargus awb: ' . $errMsg . print_r($ex, true));

            return $errMsg;
        }
    }

    public function createNewAwb($order_id)
    {
        $cargus = new CargusClass(Configuration::get('CARGUS_API_URL'), Configuration::get('CARGUS_API_KEY'));

        $fields = [
            'UserName' => Configuration::get('CARGUS_USERNAME'),
            'Password' => Configuration::get('CARGUS_PASSWORD'),
        ];

        $token = $cargus->CallMethod('LoginUser', $fields, 'POST');

        $errors = [];
        $success = [];
        $errorIds = [];

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
                'Observations' => $rows[0]['observations'],
                'PackageContent' => $rows[0]['contents'],
                'CustomString' => $rows[0]['order_id'],
                'DeliveryTime' => $rows[0]['delivery_time'],
                'SaturdayDelivery' => $rows[0]['saturday_delivery'] == 1 ? true : false,
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

            $barcode = $cargus->CallMethod('Awbs/WithGetAwb', $fields, 'POST', $token);

            //\Cargus\CargusLog::logDebug('Return AWB data: ' . print_r($barcode, true));

            if (is_array($barcode) && isset($barcode['error'])) {
                if (isset($barcode['error'])) {
                    \Cargus\CargusLog::logError(
                        'Create AWB error, data: ' . print_r($fields, true) . ', message: ' .
                        print_r($barcode['error'], true)
                    );

                    switch ($barcode['error']) {
                        case "The recipient's locality could not be matched!":
                            $barcode['error'] = 'Cargus: Va rugam verificati localitatea de livrare!';
                            break;
                    }

                    $errors[] = 'AWB-ul pentru comanda ' . addslashes($order_id) . ' nu a putut fi validat: ' . print_r($barcode['error'], true);
                    $errorIds[] = addslashes($order_id);
                }
            } else {
                if (is_null($barcode)) {
                    \Cargus\CargusLog::logError(
                        'Create AWB error, data: ' . print_r($fields, true) . ', return data: ' .
                        print_r($barcode, true)
                    );
                    $errors[] = 'AWB-ul pentru comanda ' . addslashes($order_id) . ' nu a putut fi validat';
                    $errorIds[] = addslashes($order_id);
                } else {
                    $barcodeDb = $barcode[0]['BarCode'];
                    $returnCode = $barcode[0]['ReturnCode'];
                    $returnAwb = $barcode[0]['ReturnAwb'];

                    $update = Db::getInstance()->execute(
                        'UPDATE `' . _DB_PREFIX_ . "awb_urgent_cargus` SET `barcode` = '" . $barcodeDb .
                        "', ReturnAwb='" . $returnAwb .
                        "', ReturnCode='" . $returnCode . "' WHERE `order_id` = '" . addslashes($order_id) . "'"
                    );
                    if ($update == 1) {
                        $success[] = addslashes($order_id);
                    } else {
                        $dberrMsg = Db::getInstance()->getMsgError();
                        \Cargus\CargusLog::logError('Error update db: ' . $dberrMsg);
                        $errors[] = 'AWB-ul pentru comanda ' . addslashes($order_id) .
                                    ' nu a putut fi actualizat in baza de date';
                        $errorIds[] = addslashes($order_id);
                    }
                }
            }

            // end $go
        }

        return array(
            'errors' => $errors,
            'errorIds' => $errorIds,
            'success' => $success
        );
    }

    /**
     * @param array $params
     */
    public function hookActionOrderGridDefinitionModifier(array $params): void
    {
        /** @var GridDefinitionInterface $orderGridDefinition */
        $orderGridDefinition = $params['definition'];

        /** @var RowActionCollectionInterface $actionsCollection */
//        $actionsCollection = $this->getActionsColumn($orderGridDefinition)->getOption('actions');
        $actionsCollection = $this->getActionsColumn($orderGridDefinition)->getOptions()['actions'];

        $checkCreate = new CargusCreateAwb();

        $actionsCollection->add(
            (new LinkRowAction('cargus_add_awb'))
                ->setName($this->trans('Generare AWB Cargus', [], 'Admin.Actions'))
                ->setIcon('confirmation_number')
                ->setOptions([
                    'accessibility_checker' => $checkCreate,
                    'route' => 'cargus_add_awb_orders',
                    'route_param_name' => 'id_order',
                    'route_param_field' => 'id_order',
                    // use this if you want to show the action inline instead of adding it to dropdown
                    'use_inline_display' => true,
                ])
        );

        $checkPrint = new CargusPrintAwb();

        $actionsCollection->add(
            (new BlankLinkRowAction('cargus_print_awb'))
                ->setName($this->trans('Print AWB Cargus', [], 'Admin.Actions'))
                ->setIcon('picture_as_pdf')
                ->setOptions([
                    'accessibility_checker' => $checkPrint,
                    'route' => 'cargus_print_awb_orders',
                    'route_param_name' => 'id_order',
                    'route_param_field' => 'id_order',
                    'use_inline_display' => true,
                ])
        );
    }

    private function getActionsColumn(GridDefinitionInterface $gridDefinition): ColumnInterface
    {
        try {
            if (_PS_VERSION_ >= '1.7.8') {
                return $gridDefinition->getColumnById('actions');
            } else {
                foreach ($gridDefinition->getColumns() as $column) {
                    if ('actions' === $column->getId()) {
                        return $column;
                    }
                }
            }
        } catch (ColumnNotFoundException $e) {
            $errMsg = $e->getMessage();

            \Cargus\CargusLog::logError('Error ' . __FUNCTION__ . ': ' . $errMsg);

            // It is possible that not every grid will have actions column.
            // In this case you can create a new column or throw exception depending on your needs
            throw $e;
        }
    }

    /**
     * Render a twig template.
     */
    private function render(string $template, array $params = []): string
    {
        /** @var Twig_Environment $twig */
        $twig = $this->get('twig');

        return $twig->render($template, $params);
    }

    /**
     * Get path to this module's template directory
     */
    private function getModuleTemplatePath(): string
    {
        return sprintf('@Modules/%s/views/templates/admin/', $this->name);
    }

    public function hookDisplayAdminOrderSide(array $params)
    {
        if (Configuration::get('CARGUS_AWB_RETUR') <= 0) {
            return '';
        }

        $id_order = $params['id_order'];

        //get order awb info
        $info = $this->getAwbInfoForOrderId($id_order);

        $returnCode = $info['ReturnCode'];
        $returnAwb = $info['ReturnAwb'];

        if (empty($returnAwb) && empty($returnCode)) {
            return '';
        }

        switch (Configuration::get('CARGUS_AWB_RETUR')) {
            case 1:
                //voucher
                $showQr = $returnCode;
                $showText = 'Voucher retur comanda';
                break;

            default:
                //awb return
                $showQr = $returnAwb;
                $showText = 'AWB retur comanda';
                break;
        }

        if (empty($showQr)) {
            return '';
        }

        $showImg = Media::getMediaPath(
            _PS_MODULE_DIR_ . $this->name . '/views/img/logosg-30x30.jpg'
        );

        return $this->render($this->getModuleTemplatePath() . 'hookDisplayAdminOrderSide.twig', array(
            'showText' => $showText,
            'showQr' => $showQr,
            'showImg' => $showImg
        ));
    }

    public function hookDisplayOrderDetail(array $params)
    {
        $showRetur = true;
        $trackAwb = false;

        if (Configuration::get('CARGUS_AWB_RETUR') <= 0) {
            $showRetur = false;
        }

        $id_order = $params['order']->id;

        //get order awb info
        $info = $this->getAwbInfoForOrderId($id_order);

        $returnCode = $info['ReturnCode'];
        $returnAwb = $info['ReturnAwb'];

        if (empty($returnAwb) && empty($returnCode)) {
            $showRetur = false;
        }

        if (Configuration::get('CARGUS_AWB_RETUR') == 1 && empty($returnCode)) {
            $showRetur = false;
        }

        if (Configuration::get('CARGUS_AWB_RETUR') == 2 && empty($returnAwb)) {
            $showRetur = false;
        }

        if (isset($info['barcode']) && !empty($info['barcode'])) {
            $trackAwb = $info['barcode'];
        }

        switch (Configuration::get('CARGUS_AWB_RETUR')) {
            case 1:
                //voucher
                $showQr = $returnCode;
                $showText = 'Voucher retur comanda';
                break;

            default:
                //awb return
                $showQr = $returnAwb;
                $showText = 'AWB retur comanda';
                break;
        }

        $showImg = Media::getMediaPath(
            _PS_MODULE_DIR_ . $this->name . '/views/img/logosg-30x30.jpg'
        );

        $this->context->smarty->assign(array(
            'showImg' => $showImg,
            'showText' => $showText,
            'showQr' => $showQr,
            'showRetur' => $showRetur,
            'trackAwb' => $trackAwb
        ));

        return $this->context->smarty->fetch('module:cargus/views/templates/hook/displayOrderDetail.tpl');
    }

    // Saturday, pre10, pre12 functionality::

    // before rendering carrier, call cargus class method getLocalities for user-entered postcode.
    // using method getLocalities() check if TRUE or false.
    // Continue rendereing carriers based on response for pre10, pre12 & saturday.


    public function get_address_postcode($params) {
        $address = new Address(intval($params['cart']->id_address_delivery));
        $postcode = $address->postcode;
        return $postcode;
    }
    
    // GET Localities should return true and.
    // Available only on thursday and friday.
    public function check_if_saturday_delivery_available() {
        if ($this->saturdayDeliveryValue && isThursdayOrFriday()) {
            return true; // Saturday delivery is available.
        } else {
            return false; // Saturday delivery is not available.
        }
    }

    public function getShipGoDeliveryCarrierID() {
        return (int) Configuration::get('CARGUS_SHIP_GO_CARRIER_ID');
    }

    public function getStandardCargusCarrierID() {
        return (int) Configuration::get('CARGUS_CARRIER_ID');
    }

    public function getSaturdayDeliveryCarrierID() {
        return (int) Configuration::get('CARGUS_SATURDAY_CARRIER_ID');
    }

    public function getPre10DeliverCarrierID() {
        return (int) Configuration::get('CARGUS_PRE10_CARRIER_ID');
    }

    public function getPre12DeliverCarrierID() {
        return (int) Configuration::get('CARGUS_PRE12_CARRIER_ID');
    }

    public function hideCarriersById($ids) {
        if (!is_array($ids)) {
            return ''; // Return an empty string if $ids is not an array
        }
        print_r($ids);
        $script = '<script>';
        foreach ($ids as $id) {
            $script .= '
                document.addEventListener("DOMContentLoaded", function() {
                    var inputElement = document.getElementById("delivery_option_' . $id . '");
                    if (inputElement) {
                        var parentDiv = inputElement.closest(".delivery-option");
                        if (parentDiv) {
                            parentDiv.style.display = "none";
                        }
                    }
                });
            ';
        }
        $script .= '</script>';
    
        return $script;
    }
        
    public function hookActionCarrierUpdate($params)
    {
        $id_carrier_old = (int) $params['id_carrier'];
        $id_carrier_new = (int) $params['carrier']->id;
        if ($id_carrier_old === (int) Configuration::get('CARGUS_CARRIER_ID')) {
            Configuration::updateValue('CARGUS_CARRIER_ID', $id_carrier_new);
        }
        if ($id_carrier_old === (int) Configuration::get('CARGUS_PRE12_CARRIER_ID')) {
            Configuration::updateValue('CARGUS_PRE12_CARRIER_ID', $id_carrier_new);
        }
        if ($id_carrier_old === (int) Configuration::get('CARGUS_PRE10_CARRIER_ID')) {
            Configuration::updateValue('CARGUS_PRE10_CARRIER_ID', $id_carrier_new);
        }
        if ($id_carrier_old === (int) Configuration::get('CARGUS_SATURDAY_CARRIER_ID')) {
            Configuration::updateValue('CARGUS_SATURDAY_CARRIER_ID', $id_carrier_new);
        }
        if ($id_carrier_old === (int) Configuration::get('CARGUS_SHIP_GO_CARRIER_ID')) {
            Configuration::updateValue('CARGUS_SHIP_GO_CARRIER_ID', $id_carrier_new);
        }
    }


    public function hookDisplayBeforeCarrier($params)
    {
        $url = (string) Tools::getValue('CARGUS_API_URL');
        $key = (string) Tools::getValue('CARGUS_API_KEY');
        $user = (string) Tools::getValue('CARGUS_USERNAME');
        $pass = (string) Tools::getValue('CARGUS_PASSWORD');

        $cargus = new CargusClass(
            Configuration::get('CARGUS_API_URL'),
            Configuration::get('CARGUS_API_KEY')
        );
    }

    public function hookDisplayCarrierExtraContent($carrier)
    {


        //            var_dump(ora_maxima_inregistrare_comanda('Bucuresti'));
//        $this->context->smarty->assign([
//            'greetingsFront' => 'Hello Front from MyExtraCarrierBranch !',
//        ]);
//
//
//        return $this->display(__FILE__, 'views/templates/hook/CARRIER_MESSAGE.tpl'); // Use 'CARRIER2_MESSAGE.tpl'

//        if ($carrier['carrier']['id_reference'] == Configuration::get('CARGUS_PRE12_CARRIER_ID')) {
//            $informative_message = $this->CARGUS_DELIVERY_PRE12->get_informative_message();
//            $this->context->smarty->assign('informative_message', $informative_message);
//            return $this->display(__FILE__, 'views/templates/hook/CARRIER2_MESSAGE.tpl'); // Use 'CARRIER2_MESSAGE.tpl'
//        }
//
//
//        if ($carrier['carrier']['id_reference'] == Configuration::get('CARGUS_PRE10_CARRIER_ID')) {
//            $informative_message = $this->CARGUS_DELIVERY_PRE10->get_informative_message();
//            $this->context->smarty->assign('informative_message', $informative_message);
//            return $this->display(__FILE__, 'views/templates/hook/CARRIER_MESSAGE.tpl');
//        }



//        if ($carrier['carrier']['id_reference'] == Configuration::get('CARGUS_PRE10_CARRIER_ID')) {
//            $informative_message = $this->CARGUS_DELIVERY_PRE10->get_informative_message();
//            $this->context->smarty->assign('informative_message', $informative_message);
//            return $this->display(__FILE, 'views/templates/hook/CARRIER_MESSAGE.tpl');
//        }
//
//        if ($carrier['carrier']['id_reference'] == Configuration::get('CARGUS_PRE12_CARRIER_ID')) {
//            $informative_message = $this->CARGUS_DELIVERY_PRE12->get_informative_message();
//            $this->context->smarty->assign('informative_message', $informative_message);
//            return $this->display(__FILE, 'views/templates/hook/CARRIER_MESSAGE.tpl');
//        }

        if ($carrier['carrier']['id_reference'] == Configuration::get('CARGUS_SHIP_GO_CARRIER_ID')) {
            $this->context->smarty->assign(
                'CARGUS_SHIP_GO_CARRIER_ID',
                Configuration::get('CARGUS_SHIP_GO_CARRIER_ID')
            );
            $this->context->smarty->assign(
                'UPDATE_SHIP_GO_LOCATION_LINK',
                $this->context->link->getModuleLink($this->name, 'location', [], true)
            );
            $this->context->smarty->assign(
                'CARGUS_API_PUDOPOINTS',
                $this->context->link->getModuleLink($this->name, 'getPudoPoints', [], true)
            );

            return $this->display(__FILE__, 'views/templates/hook/ship_and_go_map.tpl');
        }
    }

    // alter ps_orders in order to keep track of delivering time associated with pre10 and pre12
    public function hookActionCarrierProcess($params) {

        if (isset($params['cart'])) {
            $cart = $params['cart'];
            $selectedCarrierId = $cart->id_carrier;

            if( $this->getPre10DeliverCarrierID() == $selectedCarrierId ) {
                addOrderDeliveryTime(10);
                setSaturdayDeliveryDay(0);
            } else if( $this->getPre12DeliverCarrierID() == $selectedCarrierId ) {
                addOrderDeliveryTime(12);
                setSaturdayDeliveryDay(0);
            } else if ( $this->getSaturdayDeliveryCarrierID() == $selectedCarrierId ) {
                addOrderDeliveryTime(0);
                setSaturdayDeliveryDay(1);
            }
            else {
                addOrderDeliveryTime(0);
                setSaturdayDeliveryDay(0);
            }
        }
    }

    // After order is complete, update in ps_order the delivery time associated with the delivery method ( pre 10 and pre 12 )
    public function hookActionValidateOrder($params)
    {
        // Get the cart and current order objects.
        $cart = $params['cart'];
        $order = $params['order'];

        // Get the delivery_time and saturday_delivery values from the cart.
        $deliveryTime = Db::getInstance()->getValue('
            SELECT `delivery_time` FROM `' . _DB_PREFIX_ . 'cart`
            WHERE `id_cart` = ' . (int)$cart->id
        );

        $saturdayDelivery = Db::getInstance()->getValue('
            SELECT `saturday_delivery` FROM `' . _DB_PREFIX_ . 'cart`
            WHERE `id_cart` = ' . (int)$cart->id
        );

        // Check and update the ps_orders table with the delivery_time.
        if ($deliveryTime !== false) {
            Db::getInstance()->execute('
        UPDATE `' . _DB_PREFIX_ . 'orders`
        SET `delivery_time` = ' . (int)$deliveryTime . '
        WHERE `id_order` = ' . (int)$order->id
            );
        }

        if ($saturdayDelivery !== false) {
            $saturdayDeliveryValue = $saturdayDelivery ? 1 : 0; // Convert boolean to 0 or 1
            Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ . 'orders`
            SET `saturday_delivery` = ' . $saturdayDeliveryValue . '
            WHERE `id_order` = ' . (int)$order->id
            );
        }

        return true;
    }

    public function hookAddWebserviceResources($resources)
    {
        return array(
            'smexport' => array(
                'description' => 'Export orderrs to ShippingManager',
                'specific_management' => true,
            )

        );
    }


}