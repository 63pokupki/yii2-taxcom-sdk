<?php

namespace pokupki63\Taxcom\Request;

/**
 * Список подразделений.
 */
final class DepartmentList extends Request
{
    public $method = 'GET';
    public $path = '/API/v2/DepartmentList';
    /** @var string Статус */
    public $status = '';

    /** @var array Карта транляции свойств (см. формат в классе родителя) */
    protected static $map = [
        'status' => ['query' => 'np'],
    ];

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
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
            'status' => 'Статус',
        ]);
    }
}