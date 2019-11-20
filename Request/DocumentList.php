<?php

namespace pokupki63\Taxcom\Request;

use pokupki63\Taxcom\Model\Document;

/**
 * Список документов по смене.
 */
class DocumentList extends Request
{
    public $method = 'GET';
    public $path = '/API/v2/DocumentList';
    /** @var string Номер фискального накопителя */
    public $fn = '';
    /** @var integer Номер смены */
    public $shift = 0;
    /** @var array Тип документа */
    public $type = [];

    /** @var array Карта транляции свойств (см. формат в классе родителя) */
    protected static $map = [
        'fn'    => ['query' => 'fn'],
        'shift' => ['query' => 'shift'],
        'type'  => ['query' => 'type'],
    ];

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['fn', 'shift'], 'required'],
            ['fn', 'string', 'length' => [1, 16]],
            ['shift', 'integer', 'min' => 0],
            ['type', 'in', 'allowArray' => true, 'range' => [
                Document::TYPE_OPEN,
                Document::TYPE_CLOSE,
                Document::TYPE_STATE,
                Document::TYPE_CHECK,
                Document::TYPE_CHECK_CORRECT,
                Document::TYPE_STRICT,
                Document::TYPE_STRICT_CORRECT,
            ]]
        ]);
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'fn'    => 'Номер фискального накопителя',
            'shift' => 'Номер смены',
            'type'  => 'Тип',
        ]);
    }
}