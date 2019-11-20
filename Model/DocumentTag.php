<?php

namespace pokupki63\Taxcom\Model;

use \pokupki63\Taxcom\Exception;

/**
 * Информация по документу в ФФД тегах.
 */
class DocumentTag extends Model
{
    /** @var integer Номер ФД */
    public $fdNumber = 0;
    /** @var integer Итого (копеек), включая размер НДС (тег 1020) */
    public $total = 0;
    /** @var string Ставка НДС */
    public $vatType = '';
    /**
     * Время создания (UTC часовой пояс) (тег 1012).
     * Время записывается ККТ без указания временной зоны. Сервис приводит его к UTC исходя из настроек заданных в личном
     * кабинете для конкретной кассы. Поэтому нужно следить за корректностью этих данных как в самой ККТ, так и в настройках
     * сервиса.
     * @var string
     */
    public $createAt = '';
    /** @var array Дополнительный реквизит (тег 1084) */
    public $extend = [];
    /** @var string Версия ФФД (тег 1209) */
    public $version = '';
    /** @var string Цифровая подпись документа (тег 1077) */
    public $digitalSignature = '';
    /** @var string Регистрационный номер */
    public $kktRegNumber;
    /** @var array Сумма по чеку */
    public $totalType = [
        'cash'       => 0, // сумма по чеку (БСО) наличными (тег 1031)
        'cashLess'   => 0, // сумма по чеку (БСО) безналичными (тег 1081)
        'prepayment' => 0, // сумма по чеку (БСО) предоплатой (зачетом аванса)) (тег 1215)
        'credit'     => 0, // сумма по чеку (БСО) постоплатой (в кредит) (тег 1216)
        'other'      => 0, // сумма по чеку (БСО) встречным предоставлением (тег 1217)
    ];
    /**
     * Список товаров (услуг) перечисленных в чеке. Двумерный массив:
     *   array(
     *     [
     *       'name'      => '',  // наименование предмета расчета (тег 1030)
     *       'price'     => 0.0, // цена за единицу (тег 1079)
     *       'quantity'  => 0.0, // количество (тег 1023)
     *       'sum'       => 0.0, // общая стоимость позиции с учетом скидок и наценок (тег 1043)
     *       'method'    => 0,   // признак способа расчета (тег 1214)
     *       'subject'   => 0,   // признак предмета расчета (тег 1212)
     *     ],
     *     [...],
     *   )
     * @var array
     */
    public $itemList = [];

    /**
     * Трансформирует ответ $config API (свойства документа) в свойства класса.
     * @param array $config
     * @return array
     * @throws Exception
     */
    public static function configTransform(array $config)
    {
        $result = [];
        foreach ($config as $tagName => $tagValue) {
            if (!empty(self::$tagList[$tagName]['property'])) {
                $property = self::$tagList[$tagName]['property'];
                $subProperty = '';
                // Если это составное свойство
                if (strpos($property, '.') !== false) {
                    list($property, $subProperty) = explode('.', $property);
                }
                if (!property_exists(self::class, $property)) {
                    throw new Exception(self::class . ' ошибка: отсутствует свойство ' . $property);
                }
                switch ($tagName) {
                    // Игнорируемые теги (как правило это вложенные в другие)
                    case '1085':
                    case '1086':
                    case '1030':
                    case '1079':
                    case '1023':
                    case '1102':
                    case '1043':
                        break;
                    case '1084':
                        // В текущий момент в теге может быть только одно свойство ключ-значение, поэтому индекс массива 0
                        $result[$property] = [$tagValue[0]['1085'] => $tagValue[0]['1086']];
                        break;
                    case '1059':
                        if (is_array($tagValue)) {
                            $result[$property] = [];
                            foreach ($tagValue as $tagItem) {
                                $item = [];
                                foreach ($tagItem as $itemTagName => $itemTagValue) {
                                    if ($itemTagName == '1199') {
                                        continue;
                                    }
                                    if (!isset(self::$tagList[$itemTagName])) {
                                        throw new Exception("Для тега {$itemTagName} нет карты соответствия в " . self::class . '::$tagList');
                                    }
                                    list($itemProperty, $itemSubProperty) = explode('.', self::$tagList[$itemTagName]['property']);
                                    $item[$itemSubProperty] = isset(self::$tagList[$itemTagName]['round'])
                                        ? round($itemTagValue, self::$tagList[$itemTagName]['round'][1])
                                        : $itemTagValue;
                                }
                                $result[$property][] = $item;
                            }
                        }
                        break;
                    case '1209':
                        if ($tagValue == '-') {
                            $result[$property] = '1.0';
                        } elseif ($tagValue == '2') {
                            $result[$property] = '1.05';
                        } elseif ($tagValue == '3') {
                            $result[$property] = '1.1';
                        } else {
                            throw new Exception('Неизвестное значение тега ' . $tagValue . '=' . $tagValue);
                        }
                        break;
                    default:
                        if (empty($subProperty)) {
                            $result[$property] = $tagValue;
                        } else {
                            $result[$property][$subProperty] = $tagValue;
                        }
                }
            }
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'fdNumber'     => 'Номер ФД',
            'createAt'     => 'Время',
            'extend'       => 'Дополнительные реквизиты',
            'version'      => 'Версия ФФД',
            'totalType'    => 'Сумма по чеку',
            'total'        => 'Итог',
            'kktRegNumber' => 'Регистрационный номер',
            'itemList'     => 'Список товаров',
            'vatType'      => 'Ставка НДС',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['createAt'], 'datetime', 'format' => 'php:Y-m-d\TH:i:s'],
            ['extend', function($attr, $params) {
                if (!is_array($this->$attr)) {
                    $this->addError($attr, 'Должен быть массивом');
                }
            }],
            [['fdNumber'], 'integer', 'min' => 0],
            ['version', 'string'],
            ['totalType', function ($attr, $params) {
                if (!is_array($this->$attr)) {
                    $this->addError($attr, 'Должен быть массивом');
                }
                if (!isset($this->$attr['cash'])
                    || !isset($this->$attr['cashLess'])
                    || !isset($this->$attr['prepayment'])
                    || !isset($this->$attr['credit'])
                    || !isset($this->$attr['other'])
                ) {
                    $this->addError($attr, 'Отсутствует обязательный ключ');
                }
            }],
            ['itemList', function ($attr, $params) {
                if (!is_array($this->$attr)) {
                    $this->addError($attr, 'Должен быть массивом');
                }
                foreach ($this->$attr as $key => $item) {
                    if (!isset($item['name'])
                        || !isset($item['price'])
                        || !isset($item['quantity'])
                        || !isset($item['sum'])
                        || !isset($item['method'])
                        || !isset($item['subject'])
                    ) {
                        $this->addError($attr, "Для позиции с индексом {$key} отсутствует один из обязательных ключей");
                    }
                }
            }],
            ['vatType', 'string'],
        ]);
    }

    /**
     * Словарь. Ключ - ФФД тег, значение - массив:
     *   name - имя;
     *   property - свойство объекта;
     *   round - количество знаков: 0 - в целой части, 1 - в дробной.
     * @var array
     */
    public static $tagList = [
        '0003' => ['name' => 'кассовый чек', 'property' => ''],
        '1041' => ['name' => 'заводской номер фискального накопителя', 'property' => ''],
        '1037' => ['name' => 'регистрационный номер ККТ', 'property' => 'kktRegNumber'],
        '1018' => ['name' => 'ИНН пользователя', 'property' => ''],
        '1040' => ['name' => 'номер ФД', 'property' => 'fdNumber'],
        '1012' => ['name' => 'дата, время', 'property' => 'createAt'],
        '1077' => ['name' => 'фискальный признак документа', 'property' => 'digitalSignature'],
        '1038' => ['name' => 'номер смены', 'property' => ''],
        '1042' => ['name' => 'номер чека за смену', 'property' => ''],
        '1054' => ['name' => 'признак расчета', 'property' => ''],
        '1020' => ['name' => 'ИТОГ, включая размер НДС', 'property' => 'total', 'round' => [8,2]],
        '1084' => ['name' => 'дополнительный реквизит', 'property' => 'extend'],
        '1085' => ['name' => 'наименование дополнительного реквизита', 'property' => ''],
        '1086' => ['name' => 'значение дополнительного реквизита', 'property' => ''],
        '1059' => ['name' => 'наименование товара (реквизиты)', 'property' => 'itemList'],
        '1030' => ['name' => 'наименование предмета расчета', 'property' => 'itemList.name'],
        '1079' => ['name' => 'цена за единицу', 'property' => 'itemList.price', 'round' => [8,2]],
        '1023' => ['name' => 'количество', 'property' => 'itemList.quantity', 'round' => [5,3]],
        '1043' => ['name' => 'общая стоимость позиции с учетом скидок и наценок', 'property' => 'itemList.sum', 'round' => [8,2]],
        '1214' => ['name' => 'признак способа расчета', 'property' => 'itemList.method'],
        '1212' => ['name' => 'признак предмета расчета', 'property' => 'itemList.subject'],
        '1021' => ['name' => 'кассир', 'property' => ''],
        '1031' => ['name' => 'сумма по чеку (БСО) наличными', 'property' => 'totalType.cash'],
        '1081' => ['name' => 'сумма по чеку (БСО) безналичными', 'property' => 'totalType.cashLess'],
        '1215' => ['name' => 'сумма по чеку (БСО) предоплатой (зачетом аванса))', 'property' => 'totalType.prepayment'],
        '1216' => ['name' => 'сумма по чеку (БСО) постоплатой (в кредит)', 'property' => 'totalType.credit'],
        '1217' => ['name' => 'сумма по чеку (БСО) встречным предоставлением', 'property' => 'totalType.other'],
        '1060' => ['name' => 'адрес сайта ФНС', 'property' => ''],
        '1187' => ['name' => 'место расчетов', 'property' => ''],
        '1209' => ['name' => 'версия ФФД', 'property' => 'version'],
        '1105' => ['name' => 'НДС не облагается', 'property' => ''],
        '1048' => ['name' => 'наименование пользователя', 'property' => ''],
        '1009' => ['name' => 'адрес (место) расчетов', 'property' => ''],
        '1055' => ['name' => 'применяемая система налогообложения', 'property' => ''],
        '1199' => ['name' => 'Ставка НДС', 'property' => 'vatType'],
    ];
}