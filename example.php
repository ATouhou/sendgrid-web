<?php
require 'src/SendGrid/Config.php';
require 'src/SendGrid/Api.php';
require 'src/SendGrid/Block.php';
use SendGrid\Config,
    SendGrid\Api,
    SendGrid\Block;

$params = json_decode(
    file_get_contents(
        'example_params.json'
    ),true
);
$config = new Config($params);
$blockApi = new Block($config);
echo 'Block count: ', $blockApi->getCount(array(), true), PHP_EOL;
var_dump($blockApi->getBlocks(array('limit' => 1)));
