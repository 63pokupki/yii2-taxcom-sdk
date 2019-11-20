<?php

namespace pokupki63\Taxcom\Model;

/**
 * Торговая точка.
 */
class Outlet extends Model
{
    /** @var string Адрес торговой точки */
    public $address = '';
    /** @var integer Количество ККТ */
    public $cashdeskCount = 0;
    /** @var string Код торговой точки */
    public $code = '';
    /** @var string Идентификатор торговой точки */
    public $id = '';
    /** @var string Название торговой точки */
    public $name = '';

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['id', 'name', 'cashdeskCount'], 'required'],
            ['id', 'string', 'max' => 36],
            ['name', 'string', 'max' => 255],
            ['code', 'string', 'max' => 10],
            ['address', 'string'],
            ['cashdeskCount', 'integer', 'min' => 0],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'id'            => 'Идентификатор торговой точки',
            'name'          => 'Название торговой точки',
            'code'          => 'Код торговой точки',
            'address'       => 'Адрес торговой точки',
            'cashdeskCount' => 'Количество ККТ',
        ]);
    }
}