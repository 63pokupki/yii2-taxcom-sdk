<?php

namespace pokupki63\Taxcom\Model;

use pokupki63\Taxcom\Request\Request;

/**
 * Информация по кассе (ККТ).
 */
class CashDesk extends Model
{
    /** @var string Оплачена по */
    public $cashdeskEndDateTime;
    /** @var string Состояние  */
    public $cashdeskState;
    /** @var string Срок действия ФН */
    public $fnDuration;
    /** @var string Дата окончания действия ФН */
    public $fnEndDateTime;
    /** @var string Номер ФН */
    public $fnFactoryNumber;
    /** @var string Дата регистрации ФН */
    public $fnRegDateTime;
    /** @var string Состояние ФН */
    public $fnState;
    /** @var string Заводской номер */
    public $kktFactoryNumber;
    /** @var string Модель */
    public $kktModelName;
    /** @var string Регистрационный номер */
    public $kktRegNumber;
    /** @var string Дата последнего документа */
    public $lastDocumentDateTime;
    /** @var string Статус последнего документа */
    public $lastDocumentState;
    /** @var string Название */
    public $name;
    /** @var Outlet Торговая точка */
    public $outlet;
    /** @var string Статус смены */
    public $shiftStatus;

    // Состояние
    const STATUS_ACTIVE = 'Active'; // Подключена
    const STATUS_EXPIRES = 'Expires'; // Заканчивается оплата
    const STATUS_EXPIRED = 'Expired'; // Не оплачена
    const STATUS_INACTIVE = 'Inactive'; // Отключена пользователем
    const STATUS_ACTIVATION = 'Activation'; // Подключение
    const STATUS_DEACTIVATION = 'Deactivation'; // Отключение
    const STATUS_FN_CHANGE = 'FNChange'; // Замена ФН
    const STATUS_FN_REGISTRATION = 'FNSRegistration'; // Регистрация в ФНС
    const STATUS_FN_REGISTRATION_ERROR = 'FNSRegistrationError'; // Ошибка регистрации в ФНС

    // Состояние ФН
    const FN_STATUS_ACTIVE = 'Active'; // Активен
    const FN_STATUS_EXPIRES = 'Expires'; // Срок истекат
    const FN_STATUS_EXPIRED = 'Expired'; // Срок истек

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['name', 'kktRegNumber', 'kktFactoryNumber', 'fnFactoryNumber'], 'required'],
            ['name', 'string', 'max' => 255],
            ['kktRegNumber', 'string', 'length' => [1, 16]],
            ['kktFactoryNumber', 'string', 'length' => [1, 20]],
            ['kktModelName', 'string', 'length' => [1, 255]],
            ['fnFactoryNumber', 'string', 'length' => [1, 16]],
            ['fnRegDateTime', 'datetime', 'format' => 'php:H-m-dTH:i:s'],
            ['fnDuration', 'string', 'length' => [1, 20]],
            ['shiftStatus', 'in', 'range' => [
                Shift::STATUS_OPEN,
                Shift::STATUS_Close,
                Shift::STATUS_NODATA,
            ]],
            ['cashdeskState', 'in', 'range' => [
                self::STATUS_ACTIVE,
                self::STATUS_EXPIRES,
                self::STATUS_EXPIRED,
                self::STATUS_INACTIVE,
                self::STATUS_ACTIVATION,
                self::STATUS_DEACTIVATION,
                self::STATUS_FN_CHANGE,
                self::STATUS_FN_REGISTRATION,
                self::STATUS_FN_REGISTRATION_ERROR,
            ]],
            ['fnState', 'in', 'range' => [
                self::FN_STATUS_ACTIVE,
                self::FN_STATUS_EXPIRES,
                self::FN_STATUS_EXPIRED,
            ]],
            ['cashdeskEndDateTime', 'datetime', 'format' => 'php:H-m-dTH:i:s'],
            ['fnEndDateTime', 'datetime', 'format' => 'php:H-m-dTH:i:s'],
            ['lastDocumentState', 'in', 'range' => [Request::STATUS_OK, Request::STATUS_WARNING, Request::STATUS_PROBLEM]],
            ['outlet', 'exist', 'targetClass' => Outlet::class],
            ['lastDocumentDateTime', 'datetime', 'format' => 'php:H-m-dTH:i:s'],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'name'                 => 'Название',
            'kktRegNumber'         => 'Регистрационный номер',
            'kktFactoryNumber'     => 'Заводской номер',
            'kktModelName'         => 'Модель',
            'fnFactoryNumber'      => 'Номер ФН',
            'fnRegDateTime'        => 'Дата регистрации ФН',
            'fnDuration'           => 'Срок действия ФН',
            'shiftStatus'          => 'Статус смены',
            'cashdeskState'        => 'Статус',
            'cashdeskEndDateTime'  => 'Оплачена по',
            'fnState'              => 'Состояние ФН',
            'fnEndDateTime'        => 'Дата окончания действия ФН',
            'lastDocumentState'    => 'Статус последнего документа',
            'outlet'               => 'Торговая точка',
            'lastDocumentDateTime' => 'Дата последнего документа',
        ]);
    }
}