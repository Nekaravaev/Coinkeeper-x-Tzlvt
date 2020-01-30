<?php
require 'vendor/autoload.php';

use Nekaravaev\Telegram\Commands\CoinkeeperCommand;
use Telegram\Bot\Api;

$telegram = new Api('bot_token');

$coinkeeper = new CoinkeeperCommand();
$telegram->addCommand($coinkeeper);
$update = $telegram->commandsHandler(true);
