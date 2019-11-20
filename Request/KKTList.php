<?php

namespace pokupki63\Taxcom\Request;

/**
 * Список касс (ККТ).
 */
class KKTList extends Request
{
    public $method = 'GET';
    public $path = '/API/v2/KKTList';
    /** @var string Идентификатор торговой точки */
    public $id = '';
    /** @var string Статус */
    public $status = '';

    /** @var array Карта транляции свойств (см. формат в классе родителя) */
    protected static $map = [
        'id'     => ['query' => 'id'],
        'status' => ['query' => 'np'],
    ];

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['id'], 'required'],
            ['id', 'string'],
            ['status', 'in', 'range' => [
                self::STATUS_OK,
                self::STATUS_WARNING,
                self::STATUS_PROBLEM],
            ]
        ]);
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'id'     => 'Идентификатор торговой точки',
            'status' => 'Статус',
        ]);
    }
}