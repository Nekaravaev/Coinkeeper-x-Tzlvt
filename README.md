# Coinkeeper x Тяжеловато

Мне понравилась идея [приложения Tzlvt](https://www.fuckgrechka.ru/tzlvt/), которое рассчитывает траты на день, исходя из общего бюджета на месяц. 

Но так как порой хочется вести учёт финансов подробнее, легче интегрировать идею в текущий стек.

Устанавливаем бюджет -> берём транзакции из коинкипера с суммой всех трат за текущий период (месяц) -> рассчитываем сколько осталось на сегодня.


Пример использования:

```php
<?php
require 'vendor/autoload.php';

use Nekaravaev\Coinkeeper\Coinkeeper;

echo (new Coinkeeper(
    ['user_id' => '',
     'budget' => 1200,
     'cookies' => '_SCREEN_RESOLUTION=1680x1027;']
))->calculate();

//Total: -55.35 Available: -85.3
```

Данные о user_id и cookies легче всего взять из веб-версии коинкипера, отследив запросы удобным способом.


Благодарность:
* За вдохновение [Вадиму Юмадилову](https://www.fuckgrechka.ru/tzlvt/)
* За подсмотренный алгоритм [Igor Ramazanov](https://github.com/igor-ramazanov/coinkeeper-helper)
