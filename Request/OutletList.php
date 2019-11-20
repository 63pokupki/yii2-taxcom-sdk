<?php

namespace pokupki63\Taxcom\Request;

/**
 * Список торговых точек.
 */
final class OutletList extends Request
{
    public $method = 'GET';
    public $path = '/API/v2/OutletList';
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
            ['id', 'integer'],
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
            'id'     => 'Идентификатор',
            'status' => 'Статус',
        ]);
    }
}