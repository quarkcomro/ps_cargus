<?php

use Cargus\CargusCache;
use Cargus\CargusClass;
use Cargus\CargusCTTD;
//use Configuration;
//use PrestaShopLogger;


if (!defined('_PS_VERSION_')) {
    exit;
}

// saturday delivery is based on two policies:
// * mention: saturday delivery is always return correctly by getSaturdayDeliveryCarrierID()
// 1. check if combination of city and county are available through Cargus API.
// 2. check based on week day
// functionality is describer in @isSaturdayDeliveryAvailable function.
// Admin must allow Saturday Delivery from Preferences, by setting 'CARGUS_SATURDAY_DELIVERY' to true. It should also provide a fixed price in 'CARGUS_SATURDAY_DELIVERY_TARIF'.
// If @isSaturdayDeliveryAvailable is true, @isSaturdayDeliveryAvailableInAdmin is true and 'CARGUS_SATURDAY_DELIVERY_TARIF' preference returns true, calculate shipping price in getOrderShippingCost, else, return false.

// 10 in the morning delivery is based on one policy:
// * mention: saturday delivery is always return correctly by getPre10DeliverCarrierID()
// 1. check based on week day
// functionality is describer in @isPre10DeliveryAvailable function.
// Admin must allow Pre10 Delivery from Preferences, by setting 'CARGUS_PRE10_DELIVERY' to true. It should also provide a fixed price in 'CARGUS_PRE10_DELIVERY_TARIF'.
// If @isSaturdayDeliveryAvailable is true, and 'CARGUS_SATURDAY_DELIVERY_TARIF' preference returns true, calculate shipping price in getOrderShippingCost, else, return false.


// user input county
function getCartDeliveryCounty() {
    $context = Context::getContext();
    $cart = $context->cart;
    $id_address_delivery = (int) $cart->id_address_delivery;
    if ($id_address_delivery != 0) {
        $delivery_address = new Address($id_address_delivery);
        if (!isset($delivery_address->city) || strlen($delivery_address->city) < 3) {
            return null;
        }
    } else {
        return null;
    }
    $state = State::getNameById($delivery_address->id_state);
    return $state;
}

function getCartDeliveryCity() {
    $context = Context::getContext();
    $cart = $context->cart;
    $id_address_delivery = (int) $cart->id_address_delivery;
    if ($id_address_delivery != 0) {
        $delivery_address = new Address($id_address_delivery);
        if (!isset($delivery_address->city) || strlen($delivery_address->city) < 3) {
            return null;
        }
    } else {
        return null;
    }
    $states = State::getStatesByIdCountry($delivery_address->city);
    return $delivery_address->city;
}

// all counties from Romania
function getCounties() {
    $cargus = CargusClass::getInstance(Configuration::get('CARGUS_API_URL'), Configuration::get('CARGUS_API_KEY'));
    return $cargus->getCounties();
}

function getLocalities( $county_id ) {
    $cargus = CargusClass::getInstance(Configuration::get('CARGUS_API_URL'), Configuration::get('CARGUS_API_KEY'));
    return $cargus->getLocalities( $county_id );
}

// gets associated ID for county from CARGUS API
function get_county_id($county_name, $counties_array)
{
    $county_id = 0;

    foreach ($counties_array as $county) {
        if ($county['Name'] == $county_name) {
            $county_id = $county['CountyId'];
            break;
        }
    }

    return $county_id;
}

// gets associated city based on cargus api

// ::TODO:: find a more optimized way to transverse array
function get_locality_by_city($selected_city, $localities_array)
{
    $matching_locality = null;
    $selected_city = strtolower(preg_replace('/[^a-z0-9]+/i', '', $selected_city));


    foreach ($localities_array as $locality) {
        $locality_name = strtolower(preg_replace('/[^a-z0-9]+/i', '', $locality['Name']));

        if ($locality_name == $selected_city) {
            $matching_locality = $locality;
            break;
        }
    }

    return $matching_locality;
}

function isSaturdayDeliveryAvailableInLocation() {

    $cargus = new CargusClass(Configuration::get('CARGUS_API_URL'), Configuration::get('CARGUS_API_KEY'));

    $county = getCartDeliveryCounty();

    $locality = getCartDeliveryCity();

    $data = informatii_localitate_CTTD($county, $locality);

    if (isset($data->LocalityId)) {
        $LocalityId = $data->LocalityId;
    } else {
        return false;
    }

    $localityInfo = $cargus->getLocalityInfo($LocalityId);

    if (isset($localityInfo['SaturdayDelivery']) && $localityInfo['SaturdayDelivery']) {
        $isAvailable = $localityInfo['SaturdayDelivery'];
    } else {
        return false;
    }
    
    if (is_array($localityInfo) && array_key_exists('SaturdayDelivery', $localityInfo)) {
        return $isAvailable;
    }

}

function isThursdayOrFriday() {
    $currentDayOfWeek = date('N'); // Get the current day of the week (1 = Monday, 4 = Thursday, 5 = Friday)
    return ($currentDayOfWeek == 4 || $currentDayOfWeek == 5);
}

function isSundayToThursday() {
    $currentDay = date('w');
    // Check if the current day is Sunday (0), Monday (1), Tuesday (2), Wednesday (3), or Thursday (4)
    if ($currentDay >= 0 && $currentDay <= 4) {
        return true;
    } else {
        return false;
    }
}

function isPre10DeliveryAvailable() {
    if( !isPre10DeliveryAvailableInAdmin()) {
        return false;
    }
    return isSundayToThursday();
}

function isPre12DeliveryAvailable() {
    if( !isPre12DeliveryAvailableInAdmin()) {
        return false;
    }
    return isSundayToThursday();
}

function isShipGoDeliveryAvailable() {
    return Tools::getValue('CARGUS_SHIPGO_DELIVERY') == 1;
}

function isPre10DeliveryAvailableInAdmin() {
    return Tools::getValue('CARGUS_PRE10_DELIVERY') == 1;
}
function isPre12DeliveryAvailableInAdmin() {
    return Tools::getValue('CARGUS_PRE12_DELIVERY') == 1;
}

function isSaturdayDeliveryAvailableInAdmin() {
    return Tools::getValue('CARGUS_SATURDAY_DELIVERY') == 1;
}

/**
 * Update the 'delivery_time' in the cart.
 *
 * @param int $cartId The ID of the cart to update.
 * @param int $deliveryTime The new delivery time value to set.
 * @return bool True if the update is successful, false otherwise.
 */
function addOrderDeliveryTime($deliveryTime)
{
    $context = Context::getContext();
    $cart = $context->cart;

    $cartId = (int)$cart->id;
    $deliveryTime = (int)$deliveryTime;

    $sql = 'UPDATE `' . _DB_PREFIX_ . 'cart`
            SET `delivery_time` = ' . $deliveryTime . '
            WHERE `id_cart` = ' . $cartId;

    if (Db::getInstance()->execute($sql)) {
        return true;
    }

    return false;
}

function setSaturdayDeliveryDay($arg)
{
    $context = Context::getContext();
    $cart = $context->cart;

    $cartId = (int)$cart->id;
    $saturdayDelivery = (int)$arg;

    $sql = 'UPDATE `' . _DB_PREFIX_ . 'cart`
            SET `saturday_delivery` = ' . $saturdayDelivery . '
            WHERE `id_cart` = ' . $cartId;

    if (Db::getInstance()->execute($sql)) {
        return true;
    }

    return false;

}

function informatii_localitate_CTTD($judet, $localitate) {
    $cargusCTTD = CargusCTTD::getInstance();
    return $cargusCTTD->informatii_localitate($judet, $localitate);
}

function what_time_is_now() {
    $currentTime = date("H:i:s");
    return $currentTime;
}

function detalii_punct_de_ridicare() {

    $id_punct_ridicare = Configuration::get('CARGUS_EXPEDITOR_LOCATION_ID');
    $cargus = new CargusClass(Configuration::get('CARGUS_API_URL'), Configuration::get('CARGUS_API_KEY'));

    try {
        $localityInfo = $cargus->getLocalityInfo($id_punct_ridicare);
    } catch (Exception $e) {
        PrestaShopLogger::addLog("Delivery Location ID returned no information using DetailsLocality. Maybe wrong LocalityID? Current locality id : $id_punct_ridicare " . $e->getMessage(), 3);
        return false;
    }

    if (isset($localityInfo['CountyId']) && $localityInfo['CountyId'] !== null) {
        $county = $cargus->getCountyNameById($localityInfo['CountyId']);
    } else {
        return false;
    }

    if (isset($localityInfo['Name']) && isset($county)) {
        return [
            'City' => $localityInfo['Name'],
            'County' => $county
        ];
    } else {
        return false; // Return false if the required data is not present in the API response
    }
}

function can_order_based_on_time() {

    $timenow = what_time_is_now();

    $detalii_expeditor = detalii_punct_de_ridicare();

    if(!$detalii_expeditor) {
        return false;
    }
    $countyInfo = informatii_localitate_CTTD($detalii_expeditor['County'], $detalii_expeditor['City']);

    if (isset($countyInfo->OraMaximaInregistrareComanda)) {
        $maximumRegisterOrderTime = $countyInfo->OraMaximaInregistrareComanda;
    } else {
        return false;
    }
    if ($timenow <= $maximumRegisterOrderTime) {
        return true;
    } else {
        return false;
    }
}

// pre10 and pre12 can be ordered from sunday to thursday.
// saturday delivery can be ordered on thursday or friday.
// this functions checks for last moment when an order can be placed

function check_last_call_order_time_thursday() {

    $currentDay = date('l');

    if($currentDay === 'Thursday' ) {

        $timenow = what_time_is_now();

        $detalii_expeditor = detalii_punct_de_ridicare();

        if (!$detalii_expeditor) {
            return false;
        }
        $countyInfo = informatii_localitate_CTTD($detalii_expeditor['County'], $detalii_expeditor['City']);

        if (isset($countyInfo->OraMaximaInregistrareComanda)) {
            $maximumRegisterOrderTime = $countyInfo->OraMaximaInregistrareComanda;
        } else {
            return false;
        }
        if ($timenow <= $maximumRegisterOrderTime) {
            return true;
        } else {
            return false;
        }
    } else {
        return true;
    }
}
function check_last_call_order_time_friday() {

    $currentDay = date('l');

    if($currentDay === 'Friday') {

        $timenow = what_time_is_now();

        $detalii_expeditor = detalii_punct_de_ridicare();

        if (!$detalii_expeditor) {
            return false;
        }
        $countyInfo = informatii_localitate_CTTD($detalii_expeditor['County'], $detalii_expeditor['City']);

        if (isset($countyInfo->OraMaximaInregistrareComanda)) {
            $maximumRegisterOrderTime = $countyInfo->OraMaximaInregistrareComanda;
        } else {
            return false;
        }
        if ($timenow <= $maximumRegisterOrderTime) {
            return true;
        } else {
            return false;
        }
    } else {
        return true;
    }
}

function checkMorningDeliveryServiceBasedOnLocations( $requested_service ) {

    $detalii_expeditor = detalii_punct_de_ridicare();

    if( $detalii_expeditor && $detalii_expeditor['City'] && $detalii_expeditor['County'] ) {
        $localitate_expeditor = $detalii_expeditor['City'];
        $judet_expeditor = $detalii_expeditor['County'];
    } else {
        return false;
    }

    $judet_destinatar = getCartDeliveryCounty();
    $localitate_destinatar = getCartDeliveryCity();

    $locatie_destinatar = informatii_localitate_CTTD($judet_destinatar, $localitate_destinatar);

    if( !$locatie_destinatar ) {
        return false;
    }

    $pre10fromT = $locatie_destinatar->Pre10FromT;
    $pre12fromT = $locatie_destinatar->Pre12FromT;
    $pre10fromB = $locatie_destinatar->Pre10FromB;
    $pre12fromB = $locatie_destinatar->Pre12FromB;

    $localitate_expeditor = preg_replace('/[^a-zA-Z0-9\s]/', '', $localitate_expeditor);
    $judet_expeditor = preg_replace('/[^a-zA-Z0-9\s]/', '', $judet_expeditor);
    $localitate_expeditor = strtolower($localitate_expeditor);
    $judet_expeditor = strtolower($judet_expeditor);

    $is_expeditor_from_bucuresti = ($localitate_expeditor === 'bucuresti' && $judet_expeditor === 'bucuresti');

    if ($is_expeditor_from_bucuresti) {
        $is_pre10_available = $pre10fromB;
        $is_pre12_available = $pre12fromB;
    } else {
        $is_pre10_available = $pre10fromT;
        $is_pre12_available = $pre12fromT;
    }

    if ($requested_service == 10) {
        return $is_pre10_available !== "0";
    } elseif ($requested_service == 12) {
        return $is_pre12_available !== "0";
    }

    return false;

}
