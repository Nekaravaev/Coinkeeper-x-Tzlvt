<?php

namespace Nekaravaev\Telegram\Commands;

use Nekaravaev\Coinkeeper\Exceptions\CoinkeeperException;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Nekaravaev\Coinkeeper\Coinkeeper;

class CoinkeeperCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "coinkeeper";

    /**
     * @var string Command Description
     */
    protected $description = "Показывает бюджет на день";

    /**
     * @inheritdoc
     */
    public function handle($arguments)
    {
        $this->replyWithMessage(['text' => 'Считаю...']);

        $this->replyWithChatAction(['action' => Actions::TYPING]);

        try {
            //['total_today' => '..', 'available_now' => '..'];
            $bugdet = (new Coinkeeper(
                ['user_id' => '',
                 'budget' => 1200,
                 'cookies' => '']
            ))->calculate(true);

            $response = "✅ Резульаты получены" . PHP_EOL . "*Выделено на день*: " .PHP_EOL . $bugdet['total_today'] . ";" . PHP_EOL . "*Доступно прямо сейчас*: " . PHP_EOL . $bugdet['available_now'];
        } catch (CoinkeeperException $e) {
            $response = "⚠ Не удалось связаться с коинкипером. Сообщение: {$e->getMessage()}";
        }

        $this->replyWithMessage(['text' => $response, 'parse_mode' => 'markdown']);
    }
}