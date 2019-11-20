<?php


namespace pokupki63\Taxcom\Request;


use pokupki63\Taxcom\Exception;
use yii\base\Model;

class Request extends Model
{
    /** @var string HTTP метод */
    public $method = '';
    /** @var string Путь до документа */
    public $path = '';
    /** @var int Номер страницы (нумерация от 1) */
    public $pageNumber = 1;
    /** @var int Элементов на странице */
    public $perPage = 100;
    public $debug = false;
    /**
     * Карта соответствия имени свойства объекта и имени перенной в API. Все свойства которые должны передаваться при
     * запросе в API должны быть тут перечислены. При этом значение это массив:
     *   ключ - куда уходит переменная: query (в URL), body (тело запроса);
     *   значение - имя переменной в API
     * @var array
     */
    protected static $map = [
        'pageNumber' => ['query' => 'pn'],
        'perPage'    => ['query' => 'ps'],
    ];

    const STATUS_OK      = 'OK';
    const STATUS_WARNING = 'Warning';
    const STATUS_PROBLEM = 'Problem';

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['path', 'method'], 'required'],
            ['method', 'in', 'range' => ['GET', 'POST']],
            ['pageNumber', 'integer'],
            ['perPage', 'integer', 'min' => 1, 'max' => 100],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'method'     => 'HTTP метод',
            'path'       => 'Путь до документа',
            'pageNumber' => 'Номер страницы',
            'perPage'    => 'Элементов на странице',
        ]);
    }

    /**
     * Метод собирает результатирующую карту объединив текущую карту и карту потомка.
     * @return array
     * @@throws Exception
     */
    protected function getMap()
    {
        $mapList = array_merge(self::$map, $this::$map);
        foreach ($mapList as $propertyName => $map) {
            if (!isset($this->$propertyName)) {
                throw new Exception(get_class($this) . " ошибка: отсутствует свойство {$this->$propertyName} описанное в self::\$map");
            }
        }
        return array_merge(self::$map, $this::$map);
    }

    /**
     * Сериализация для отправки в API.
     * @return mixed
     * @throws Exception
     */
    public function getBody()
    {
        $result = [];
        foreach ($this->getMap() as $propertyName => $filter) {
            if (!isset($filter['body'])) {
                continue;
            }
            if (!empty($this->$propertyName)) {
                $result[$filter['body']] = $this->$propertyName;
            }
        }
        return $result;
    }

    /**
     * Получить строку запроса.
     * @return array
     * @throws Exception
     */
    public function getQuery()
    {
        $result = [];
        foreach ($this->getMap() as $propertyName => $filter) {
            if (!isset($filter['query'])) {
                continue;
            }
            $result[$filter['query']] = $this->$propertyName;
        }
        return $result;
    }

    /**
     * Задать фильтр по полю $name в значении $value.
     * @param string $name
     * @param integer|float|string|boolean $value
     * @throws Exception
     * @return self
     * @deprecated
     */
    public function filterBy(string $name, $value)
    {
        if (!isset($this->map[$name]['value'])) {
            throw new Exception('В классе ' . get_class($this) . ' не определен фильтр ' . $name);
        }
        if (!is_scalar($value)) {
            throw new Exception('Значение фильтра должно быть скалярным');
        }
        if (is_array($this->map[$name]['value'])) {
            $this->map[$name]['value'][] = $value;
        } else {
            $this->map[$name]['value'] = $value;
        }
        return $this;
    }
}