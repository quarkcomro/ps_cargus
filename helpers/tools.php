<?php

use Cargus\CargusCache;
use Cargus\CargusClass;
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
    $url = (string) Tools::getValue('CARGUS_API_URL');
    $key = (string) Tools::getValue('CARGUS_API_KEY');
    $user = (string) Tools::getValue('CARGUS_USERNAME');
    $pass = (string) Tools::getValue('CARGUS_PASSWORD');
    $cargus = CargusClass::getInstance(Configuration::get('CARGUS_API_URL'), Configuration::get('CARGUS_API_KEY'));
    return $cargus->getCounties();
}

function getLocalities( $county_id ) {
    $url = (string) Tools::getValue('CARGUS_API_URL');
    $key = (string) Tools::getValue('CARGUS_API_KEY');
    $user = (string) Tools::getValue('CARGUS_USERNAME');
    $pass = (string) Tools::getValue('CARGUS_PASSWORD');
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

function isSaturdayDeliveryAvailable() {
    
    if( !isSaturdayDeliveryAvailableInAdmin() ) {
        return false;
    }

    if( !isThursdayOrFriday() ) {
        return false;
    }
    // get all counties
    $all_counties = getCounties();
    // get selected county from cart
    $selected_county = getCartDeliveryCounty();
    // get associated id for the selected county
    $countyId = get_county_id($selected_county, $all_counties);
    // get all localities
    $localities = getLocalities($countyId);
    // get cart associated city
    $city = getCartDeliveryCity();
    // find information based on city and county
    $locality = get_locality_by_city($city, $localities);

    $isAvailable = $locality['SaturdayDelivery'];

    if (is_array($locality) && array_key_exists('SaturdayDelivery', $locality)) {
        return $isAvailable;
    }

}

function isThursdayOrFriday() {
    $currentDayOfWeek = date('N'); // Get the current day of the week (1 = Monday, 4 = Thursday, 5 = Friday)
    return ($currentDayOfWeek == 4 || $currentDayOfWeek == 5);
}

function isSundayToThursday() {
    $currentDayOfWeek = date('N'); // Get the current day of the week (1 = Monday, 7 = Sunday)
    return ($currentDayOfWeek >= 1 && $currentDayOfWeek <= 4);
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
