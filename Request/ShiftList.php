<?php

namespace pokupki63\Taxcom\Request;

/**
 * Список смен заданной ККТ.
 */
class ShiftList extends Request
{
    public $method = 'GET';
    public $path = '/API/v2/ShiftList';
    /** @var string Номер фискального накопителя (ФН) */
    public $fn = '';
    /** @var string Начало периода */
    public $start = '';
    /** @var string Окончание периода */
    public $end = '';

    /** @var array Карта транляции свойств (см. формат в классе родителя) */
    protected static $map = [
        'fn'    => ['query' => 'fn'],
        'start' => ['query' => 'begin'],
        'end'   => ['query' => 'end'],
    ];

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['fn', 'start', 'end'], 'required'],
            ['fn', 'string', 'length' => [1, 16]],
            [['start', 'end'], 'date', 'format' => 'php:Y-m-d\TH:i:s'],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'fn'    => 'Номер фискального накопителя',
            'start' => 'Начало периода',
            'end'   => 'Окончание периода',
        ]);
    }
}