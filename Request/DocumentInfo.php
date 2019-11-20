<?php

namespace pokupki63\Taxcom\Request;

/**
 * Информация по фискальному документу (ФД) в тегах.
 */
class DocumentInfo extends Request
{
    public $method = 'GET';
    public $path = '/API/v2/DocumentInfo';
    /** @var string Номер фискального накопителя (ФН) */
    public $fnFactoryNumber = '';
    /** @var integer Номер ФД */
    public $fdNumber = 0;

    /** @var array Карта транляции свойств (см. формат в классе родителя) */
    protected static $map = [
        'fnFactoryNumber' => ['query' => 'fn'],
        'fdNumber'        => ['query' => 'fd'],
    ];

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['fnFactoryNumber', 'fdNumber'], 'required'],
            ['fdNumber', 'integer', 'min' => 0],
            ['fnFactoryNumber', 'string', 'length' => [1, 16]],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'fnFactoryNumber' => 'Номер фискального накопителя',
            'fdNumber'        => 'Номер фискального документа',
        ]);
    }
}