<?php

namespace pokupki63\Taxcom\Request;

class Login extends Request
{
    public $method = 'POST';
    public $path = '/API/v2/Login';

    /** @var string */
    public $login = '';
    /** @var string */
    public $password = '';
    /** @var string */
    public $agreementNumber = '';

    /** @var array Карта транляции свойств (см. формат в классе родителя) */
    protected static $map = [
        'login'            => ['body' => 'login'],
        'password'         => ['body' => 'password'],
        'agreementNumber'  => ['body' => 'agreementNumber'],
    ];

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['login', 'password'], 'required'],
            ['agreementNumber', 'string', 'length' => [1, 50]],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'login'    => 'Логин',
            'password' => 'Пароль',
            'agreementNumber' => 'Номер договора',
        ]);
    }
}