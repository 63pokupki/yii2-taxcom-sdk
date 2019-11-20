## Описание
Библиотека облегчает работу при интеграции ОФД Такском (оператор фискальных данных) при получении данных и работе с
ККТ (контрольно-кассовой техники).

## Требования
После регистрации в сервисе [Такском-Касса](https://lk-ofd.taxcom.ru/) и перед началом работы необходимо получить
ID интегратора. Для этого необходимо обратиться с соответствующим запросом в
[техническую поддержку компании](https://taxcom.ru/tekhpodderzhka/kontakty/) "Такском".

## Особенности сервиса
Работа с часовыми поясами. Время во всех API вызовах находится во временной зоне UTC. ККТ не передают TZ и сервис
приводит время к UTC исходя из настроект заданных в личном кабинете. Поэтому нужно следить за корректностью указания
TZ как в самой ККТ, так и в настройках сервиса для этой ККТ.

## Пример
```php
<?php

use pokupki63\Taxcom\Client;
use pokupki63\Taxcom\Model\Document;
use pokupki63\Taxcom\Request\Login;
use pokupki63\Taxcom\Taxcom;

// Инициализируем такском API клиент
$client = new Client(
    // api-lk-ofd.taxcom.ru - боевой, api-tlk-ofd.taxcom.ru - тестовый
    'api-tlk-ofd.taxcom.ru',
    new Login([
        'login' => 'логин',
        'password' => 'пароль',
        'agreementNumber' => 'номер договора',
    ]),
    'ID итегратора'
);
$taxcom = new Taxcom($client);
// Список офисов
$outletList = $taxcom->getOutletList();
foreach ($outletList as $outlet) {
    echo 'Офис: ' . $outlet->name . PHP_EOL;
    // Список касс
    $cashDeskList = $taxcom->getCashDeskList($outlet);
    foreach ($cashDeskList as $cashDesk) {
        echo "\tкасса #{$cashDesk->fnFactoryNumber} (рег. номер {$cashDesk->kktRegNumber})" . PHP_EOL;
        // Список смен на кассе
        $shiftList = $taxcom->getShiftList($cashDesk);
        foreach ($shiftList as $shift) {
            echo "\t\tсмена №" . $shift->shiftNumber . PHP_EOL;
            // Список документов (чеки) смены
            $docList = $taxcom->getDocumentList($shift, Document::TYPE_CHECK);
            foreach ($docList as $doc) {
                // ФФД теги документа
                $docTag = $taxcom->getDocumentTag($doc);
                $sum = round($doc->sum / 100, 2);
                echo $this->getLogMessage("\t\t\tФД №{$doc->fdNumber} {$sum} рублей");
            }
        }
    }
}
```