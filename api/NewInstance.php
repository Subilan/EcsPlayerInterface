<?php
require "vendor/autoload.php";
require "Global.php";

$results = EcsActions::describeSpotPrice();
$prices = [];
$infos = [];
foreach ($results as $value) {
    $price = 999;
    if ($value != null) {
        if ($value->body->spotPrices->spotPriceType != null) {
            $re = $value->body->spotPrices->spotPriceType[0];
            if (property_exists($re, "spotPrice")) {
                $price = $re->spotPrice;
                array_push($prices, $price);
                array_push($infos, $value->body->spotPrices->spotPriceType[0]);
            }
        }
    }
}
$min = min($prices);
$minObject = $infos[array_search($min, $prices)];
$zoneId = $minObject->zoneId;
$id = EcsActions::createInstance($zoneId);
if ($id != null) {
    if (EcsActions::launchInstance($id)) {
        echo "Done.";
    } else {
        echo "Failed to launch the instance.";
    }
}