<?php
require 'src/SendGrid/Config.php';
require 'src/SendGrid/Api.php';
require 'src/SendGrid/Block.php';
require 'src/SendGrid/Bounce.php';
use SendGrid\Config,
    SendGrid\Api,
    SendGrid\Block,
    SendGrid\Bounce;

$params = json_decode(
    file_get_contents(
        'example_params.json'
    ),true
);
$config = new Config($params);
$api = Api::GetApiSection(
    Api::API_BOUNCE,
    $config
);
echo 'Bounce count: ',$api->getCount(array('type' => Bounce::BOUNCE_TYPE_SOFT), true), PHP_EOL;
var_dump(
    $api->getBounces(
        array(
            'type'  => Bounce::BOUNCE_TYPE_SOFT,
            'limit' => 1
        )
    )
);

$blockApi = new Block($config);

echo 'Block count: ', $blockApi->getCount(array(),true), PHP_EOL;
var_dump(
    $blockApi->getBlocks(
        array(
            'limit' => 1
        )
    )
);
