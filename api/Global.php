<?php

require "vendor/autoload.php";

use AlibabaCloud\SDK\Ecs\V20140526\Ecs;
use AlibabaCloud\SDK\Ecs\V20140526\Models\CreateInstanceRequest;
use AlibabaCloud\SDK\Ecs\V20140526\Models\DescribeSpotPriceHistoryRequest;
use AlibabaCloud\SDK\Ecs\V20140526\Models\StartInstanceRequest;
use Darabonba\OpenApi\Models\Config;

$globalConfig = json_decode(file_get_contents("config.json"), true);

class EcsActions
{
    public static function getClient()
    {
        global $globalConfig;
        extract($globalConfig);
        $config = new Config([
            "accessKeyId" => $accessKeyId,
            "accessKeySecret" => $accessKeySecret,
        ]);
        $config->endpoint = $endpoint;
        return new Ecs($config);
    }

    public static function describeSpotPrice()
    {
        global $globalConfig;
        extract($globalConfig["instanceConfig"]);
        $results = [];
        foreach ($zone as $z) {
            try {
                $res = self::getClient()->describeSpotPriceHistory(new DescribeSpotPriceHistoryRequest([
                    "regionId" => $region_id,
                    "zoneId" => $region_id . "-" . $z,
                    "networkType" => $network_type,
                    "spotDuration" => $duration,
                    "instanceType" => $type,
                    "ioOptimized" => $io_optimized == true ? "optimized" : "none"
                ]));
                array_push($results, $res);
            } catch (Exception $e) {
                continue;
            }
        }
        return $results;
    }

    public static function createInstance(string $zoneId)
    {
        global $globalConfig;
        extract($globalConfig["instanceConfig"]);
        try {
            $res = self::getClient()->createInstance(new CreateInstanceRequest([
                "regionId" => $region_id,
                "zoneId" => $zoneId,
                "instanceType" => $type,
                "instanceName" => $instance_name,
                "instanceChargeType" => "PostPaid",
                "internetChargeType" => $internet_charge_type,
                "systemDisk.Category" => $disk_type,
                "systemDisk.Size" => $disk_size,
                "ioOptimized" => $io_optimized == true ? "optimized" : "none",
                "spotStrategy" => $strategy == "auto" ? "SpotAsPriceGo" : "SpotWithPriceLimit",
                "spotPriceLimit" => $strategy == "auto" ? null : $price_limit,
                "spotDuration" => $duration,
                "imageId" => $image,
                "securityGroupId" => $network_sg,
                "vSwitchId" => $network_vsw,
                "internetMaxBandwidthOut" => $network_max_bandwidth
            ]));
            return $res->body->instanceId;
        } catch (Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    public static function launchInstance(string $id) {
        try {
            self::getClient()->startInstance(new StartInstanceRequest([
                "instanceId" => $id
            ]));
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
