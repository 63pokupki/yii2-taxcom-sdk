<?php

namespace pokupki63\Taxcom\Model;

use yii\base\Model as YiiModel;

class Model extends YiiModel
{
    // Сценарии
    const SCENARIO_LOAD_LIST = 'list'; // загрузка из списка смен (сокращенный список свойств)
    const SCENARIO_LOAD_INFO = 'info'; // загрузка подробной информации (полный список свойств)

    /**
     * @inheritDoc
     */
    public function scenarios()
    {
        $scenarioList = parent::scenarios();
        $scenarioList[self::SCENARIO_LOAD_LIST] = $scenarioList['default'];
        $scenarioList[self::SCENARIO_LOAD_INFO] = $scenarioList['default'];
        return $scenarioList;
    }
}