<?php

namespace Cargus;
use Context;
use Configuration;
use Currency;
use Tools;

class CarrierPolicy
{
    // Properties
    private $carrierID;
    private $policyType;
    private $coverageAmount;
    private $rules = [];

    // Constructor
    public function __construct(?string $FixedPriceField, ?string $PreferenceField, ?string $IDField, ?string $InformativeMessageField, ?int $DeliveryTime = 0, ?array $rules = null)
    {
        $this->FixedPriceField = $FixedPriceField;
        $this->PreferenceField = $PreferenceField;
        $this->IDField = $IDField;
        $this->DeliveryTime = $DeliveryTime;
        $this->InformativeMessageField = $InformativeMessageField;
        if ($rules !== null) {
            foreach ($rules as $ruleName => $ruleFunction) {
                $this->addRule($ruleName, $ruleFunction);
            }
        }
    }

    public function getPrice()
    {
        $price = Configuration::get($this->FixedPriceField);
        return $price;
    }

    public function getDeliveryTime() {
        return $this->DeliveryTime;
    }

    public function getID() {
        return Configuration::get($this->IDField);
    }

    public function get_price_field() {
        return (string) $this->FixedPriceField;
    }

    public function get_informative_message(){
        $message = Configuration::get($this->InformativeMessageField);
        return $message;
    }

    public function isActive()
    {
        return Configuration::get($this->PreferenceField) == 1;
    }

    public function freeRangeStart() {
        return Configuration::get('CARGUS_TRANSPORT_GRATUIT');
    }

    public function addRule(string $ruleName, callable $ruleFunction, array $parameters = [])
    {
        $this->rules[$ruleName] = function () use ($ruleFunction, $parameters) {
            return call_user_func($ruleFunction, ...$parameters);
        };
    }

    // Checks if available based on rules passed in $rules.

    public function isAvailable()
    {
        foreach ($this->rules as $ruleFunction) {
            if (!$ruleFunction()) {
                return false;
            }
        }

        // Check the isActive rule
        if (!$this->isActive()) {
            return false;
        }

        return true;
    }

}

