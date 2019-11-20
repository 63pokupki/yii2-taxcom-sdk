<?php

namespace pokupki63\Taxcom\Model;

class Document extends Model
{
    /** @var string Признак расчета */
    public $accountingType = '';
    /** @var int Сумма нал (копейки) */
    public $cash = 0;
    /** @var string Кассир */
    public $cashier = '';
    /** @var string Время создания */
    public $dateTime = '';
    /** @var integer Тип документа */
    public $documentType = 0;
    /** @var int Сумма безнал (копейки) */
    public $electronic = 0;
    /** @var integer Номер ФД */
    public $fdNumber = 0;
    /** @var string Номер ФН */
    public $fnFactoryNumber = '';
    /** @var string Фискальный признак документа */
    public $fpd = '';
    /** @var int Сумма с НДС 0% (копейки) */
    public $nds0Sum = 0;
    /** @var int НДС 10% (копейки) */
    public $nds10 = 0;
    /** @var int НДС 18% (копейки) */
    public $nds18 = 0;
    /** @var int НДС 20% (копейки) */
    public $nds20 = 0;
    /** @var int НДС 10/110 (копейки) */
    public $ndsC10 = 0;
    /** @var int НДС 18/118 (копейки) */
    public $ndsC18 = 0;
    /** @var int НДС 20/120 (копейки) */
    public $ndsC20 = 0;
    /** @var int Сумма без НДС (копейки) */
    public $nondsSum = 0;
    /** @var integer Номер за смену */
    public $numberInShift = 0;
    /** @var int Номер смены */
    public $shiftNumber = 0;
    /** @var int Сумма (копейки) */
    public $sum = 0;

    // Типы
    const TYPE_OPEN           = 2;  // отчет об открытии смены
    const TYPE_CLOSE          = 5;  // отчет о закрытии смены
    const TYPE_STATE          = 21; // отчет о текущем состоянии расчетов
    const TYPE_CHECK          = 3;  // кассовый чек
    const TYPE_CHECK_CORRECT  = 31; // кассовый чек коррекции
    const TYPE_STRICT         = 4;  // бланк строгой отчетности
    const TYPE_STRICT_CORRECT = 41; // бланк строгой отчетности коррекции

    // Признак расчета
    const ACCOUNTING_INCOME             = 'Income';            // приход
    const ACCOUNTING_INCOME_RETURN      = 'IncomeReturn';      // возврат прихода
    const ACCOUNTING_EXPENDITURE        = 'Expenditure';       // расход
    const ACCOUNTING_EXPENDITURE_RETURN = 'ExpenditureReturn'; // возврат расхода

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['fnFactoryNumber', 'documentType', 'dateTime', 'fdNumber', 'fpd'], 'required'],
            ['fnFactoryNumber', 'string', 'length' => [1, 16]],
            [['shiftNumber', 'fdNumber', 'numberInShift'], 'integer', 'min' => 0],
            [['dateTime'], 'datetime', 'format' => 'php:Y-m-d\TH:i:s'],
            ['documentType', 'in', 'range' => [
                self::TYPE_OPEN,
                self::TYPE_CLOSE,
                self::TYPE_STATE,
                self::TYPE_CHECK,
                self::TYPE_CHECK_CORRECT,
                self::TYPE_STRICT,
                self::TYPE_STRICT_CORRECT,
            ]],
            ['accountingType', 'in', 'range' => [
                self::ACCOUNTING_INCOME,
                self::ACCOUNTING_INCOME_RETURN,
                self::ACCOUNTING_EXPENDITURE,
                self::ACCOUNTING_EXPENDITURE_RETURN,
            ]],
            ['fpd', 'string', 'length' => [1, 10]],
            ['cashier', 'string', 'length' => [1, 256]],
            [['sum', 'cash', 'electronic', 'nondsSum', 'nds0Sum'], 'integer', 'min' => 0, 'max' => 9223372036854776000],
            [['nds10', 'nds18', 'nds20', 'ndsC10', 'ndsC18', 'ndsC20'], 'integer'],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'fnFactoryNumber' => 'Номер ФН',
            'shiftNumber'     => 'Номер смены',
            'dateTime'        => 'Время создания',
            'documentType'    => 'Тип документа',
            'fdNumber'        => 'Номер ФД',
            'numberInShift'   => 'Номер за смену',
            'fpd'             => 'Фискальный признак документа (ФПД)',
            'accountingType'  => 'Признак расчета',
            'sum'             => 'Сумма (копейки)',
            'cash'            => 'Сумма нал (копейки)',
            'electronic'      => 'Сумма безнал (копейки)',
            'nondsSum'        => 'Сумма без НДС (копейки)',
            'ndsC20'          => 'НДС 20/120 (копейки)',
            'ndsC18'          => 'НДС 18/118 (копейки)',
            'ndsC10'          => 'НДС 10/110 (копейки)',
            'nds20'           => 'НДС 20% (копейки)',
            'nds18'           => 'НДС 18% (копейки)',
            'nds10'           => 'НДС 10% (копейки)',
            'nds0Sum'         => 'Сумма с НДС 0% (копейки)',
        ]);
    }
}