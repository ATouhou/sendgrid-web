<?php
require 'src/SendGrid/Config.php';
require 'src/SendGrid/Api.php';
require 'src/SendGrid/Block.php';
require 'src/SendGrid/Bounce.php';
require 'src/SendGrid/Spam.php';
require 'src/SendGrid/Invalid.php';
use SendGrid\Config,
    SendGrid\Api,
    SendGrid\Block,
    SendGrid\Invalid,
    SendGrid\Bounce;

$params = json_decode(
    file_get_contents(
        'example_params.json'
    ),true
);
$config = new Config($params);
$api = Api::GetApiSection(
    Api::API_INVALID,
    $config
);
echo 'Invalid count: ', $api->getCount(array(), true), PHP_EOL;
$invalid = $api->getInvalids(
    array(
        'limit' => 1
    )
);
$emails = array();
foreach ($invalid as $obj)
{
    $emails[] = $obj->email;
    echo 'Will delete: ', $obj->email, PHP_EOL;
}
var_dump(
    $api->deleteEmails(
        $emails
    )
);
var_dump(
    $api->getInvalids(
        array(
            'limit' => 1
        )
    )
);
$api = Api::GetApiSection(
    Api::API_SPAM,
    $config
);
echo 'Spam count: ', $api->getCount(array(), true), PHP_EOL;
var_dump(
    $api->getSpamReports(
        array(
            'limit' => 1
        )
    )
);
/** @var Bounce $api */
$api = Api::GetApiSection(
    Api::API_BOUNCE,
    $config
);

/** @noinspection PhpToStringImplementationInspection */
echo 'Bounce count: ',$api->getCount(array('type' => Bounce::BOUNCE_TYPE_SOFT), true), PHP_EOL;
var_dump(
    $api->getBounces(
        array(
            'type'  => Bounce::BOUNCE_TYPE_HARD,
            'limit' => 10
        )
    )
);

$blockApi = new Block($config);

/** @noinspection PhpToStringImplementationInspection */
echo 'Block count: ', $blockApi->getCount(array(),true), PHP_EOL;
var_dump(
    $blockApi->getBlocks(
        array(
            'limit' => 1
        )
    )
);
