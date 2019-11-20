<?php

namespace pokupki63\Taxcom\Model;

/**
 * Информация по смене.
 */
class Shift extends Model
{
    /** @var string Кассир */
    public $cashier = '';
    /** @var integer Номер ФД отчета об открытии смены  */
    public $openFdNumber = 0;
    /** @var integer Номер ФД отчета о закрытии смены */
    public $closeFdNumber = 0;
    /** @var string Номер ФН */
    public $fnFactoryNumber = '';
    /** @var string Дата открытия */
    public $openDateTime = '';
    /** @var string Дата закрытия */
    public $closeDateTime = '';
    /** @var int Номер смены */
    public $shiftNumber = 0;
    /** @var int Кол-во чеков за смену */
    public $receiptCount = 0;
    /** @var string  */
    public $state = self::STATUS_NODATA;

    // Статус
    const STATUS_OPEN   = 'Open';
    const STATUS_Close  = 'Close';
    const STATUS_NODATA = 'NoData';

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['fnFactoryNumber', 'shiftNumber', 'openDateTime'], 'required'],
            [['receiptCount'], 'required', 'on' => self::SCENARIO_LOAD_LIST],
            [['openFdNumber', 'cashier', 'state'], 'required', 'on' => self::SCENARIO_LOAD_INFO],

            ['fnFactoryNumber', 'string', 'length' => [1, 16]],
            ['state', 'in', 'range' => [self::STATUS_OPEN, self::STATUS_Close], 'on' => self::SCENARIO_LOAD_INFO],
            [['shiftNumber', 'receiptCount'], 'integer', 'min' => 0],
            [['openDateTime', 'closeDateTime'], 'datetime', 'format' => 'php:Y-m-d\TH:i:s', 'skipOnEmpty' => true],
            [['openFdNumber'], 'integer', 'on' => self::SCENARIO_LOAD_INFO],
            [['closeFdNumber'], 'integer'],
            ['cashier', 'string', 'length' => [1, 256], 'on' => self::SCENARIO_LOAD_INFO],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'fnFactoryNumber' => 'Номер ФН',
            'state'           => 'Статус',
            'shiftNumber'     => 'Номер смены',
            'openDateTime'    => 'Дата открытия',
            'closeDateTime'   => 'Дата закрытия',
            'openFdNumber'    => 'Номер ФД отчета об открытии смены',
            'closeFdNumber'   => 'Номер ФД отчета о закрытии смены',
            'cashier'         => 'Кассир',
        ]);
    }
}