<?php

namespace pokupki63\Taxcom;

use ReflectionClass;
use DateTime;
use pokupki63\Taxcom\Model\CashDesk;
use pokupki63\Taxcom\Model\Document;
use pokupki63\Taxcom\Model\DocumentTag;
use pokupki63\Taxcom\Model\Outlet;
use pokupki63\Taxcom\Model\Shift;
use pokupki63\Taxcom\Request\DocumentInfo;
use pokupki63\Taxcom\Request\DocumentList;
use pokupki63\Taxcom\Request\KKTList;
use pokupki63\Taxcom\Request\OutletList;
use pokupki63\Taxcom\Request\Request;
use pokupki63\Taxcom\Request\ShiftList;
use DateTimeZone;

class Taxcom
{
    /** @var Client */
    protected $client = null;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * По имени класса вернет массив свойств.
     * @param string $class
     * @return array
     * @throws \ReflectionException
     */
    private static function getClassPropertyList(string $class)
    {
        $result = [];
        $reflect = new ReflectionClass($class);
        foreach ($reflect->getProperties() as $property) {
            $result[] = $property->getName();
        }
        return $result;
    }

    /**
     * Для запросов с постраничной навигацией загрузит все возможные элементы.
     * @param Request $endpoint
     * @return array
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getAllItemList(Request $endpoint)
    {
        $result = [];
        if (!$endpoint->validate()) {
            throw new Exception(get_class($endpoint) . ': ' . current($endpoint->firstErrors));
        }
        do {
            $response = $this->client->request($endpoint);
            foreach ($response['records'] as $record) {
                $result[] = $record;
            }

            $lastPageNumber = ceil($response['counts']['recordFilteredCount'] / $endpoint->perPage);
            ++$endpoint->pageNumber;
        } while ($endpoint->pageNumber <= $lastPageNumber);
        return $result;
    }

    /**
     * Получить список торговых точек.
     * @return Outlet[]
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \ReflectionException
     * @throws Exception
     */
    public function getOutletList()
    {
        $result = [];
        $propertyList = array_flip(self::getClassPropertyList(Outlet::class));
        $responseOutletList = $this->getAllItemList(new OutletList());
        foreach ($responseOutletList as $responseOutlet) {
            $config = array_intersect_key($responseOutlet, $propertyList);
            $outlet = new Outlet($config);
            $outlet->scenario = Outlet::SCENARIO_LOAD_LIST;
            if (!$outlet->validate()) {
                throw new Exception(Outlet::class . ': ' . current($outlet->firstErrors));
            }
            $result[$outlet->id] = $outlet;
        }
        return $result;
    }

    /**
     * Вернет список касс относящихся к торговой точке $outlet.
     * @param Outlet $outlet
     * @return CashDesk[]
     * @throws Exception
     * @throws \ReflectionException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCashDeskList(Outlet $outlet)
    {
        $result = [];
        $propertyList = array_flip(self::getClassPropertyList(CashDesk::class));
        $KKTList = $this->getAllItemList(new KKTList([
            'id' => $outlet->id,
        ]));
        foreach ($KKTList as $KKT) {
            $config = array_intersect_key($KKT, $propertyList);
            $cashDesk = new CashDesk($config);
            $cashDesk->scenario = CashDesk::SCENARIO_LOAD_LIST;
            if (!$cashDesk->validate()) {
                throw new Exception(CashDesk::class . ': ' . current($cashDesk->firstErrors));
            }
            $result[$cashDesk->kktRegNumber] = $cashDesk;
        }
        return $result;
    }

    /**
     * Получить список смен кассы $cashDesk.
     * @param CashDesk $cashDesk
     * @param DateTime $start
     * @param DateTime $end
     * @return Shift[]
     * @throws Exception
     * @throws \ReflectionException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getShiftList(CashDesk $cashDesk, DateTime $start = null, DateTime $end = null)
    {
        $result = [];

        // Задаем время
        if (!$start instanceof DateTime) {
            $start = new DateTime('today');
        }
        if (!$end instanceof DateTime) {
            $end = new DateTime('tomorrow');
        }
        // Сервис оперирует часовым поясом +0, поэтому корректируем
        $tz = new DateTimeZone('UTC');
        $start->setTimezone($tz);
        $end->setTimezone($tz);

        $propertyList = array_flip(self::getClassPropertyList(Shift::class));
        $responseShiftList = $this->getAllItemList(new ShiftList([
            'fn'    => $cashDesk->fnFactoryNumber,
            'start' => $start->format('Y-m-d\TH:i:s'),
            'end'   => $end->format('Y-m-d\TH:i:s'),
        ]));
        foreach ($responseShiftList as $responseShift) {
            $config = array_intersect_key($responseShift, $propertyList);
            $shift = new Shift($config);
            $shift->scenario = Shift::SCENARIO_LOAD_LIST;
            if (!$shift->validate()) {
                throw new Exception(Shift::class . ': ' . current($shift->firstErrors));
            }
            $result[] = $shift;
        }
        return $result;
    }

    /**
     * Вернет список документов.
     * @param Shift $shift
     * @return Document[]
     * @throws Exception
     * @throws \ReflectionException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getDocumentList(Shift $shift, $type = null)
    {
        $result = [];
        // Параметры запроса
        $requestParamList = [
            'fn'    => $shift->fnFactoryNumber,
            'shift' => $shift->shiftNumber,
        ];
        if ($type !== null) {
            $requestParamList['type'] = Document::TYPE_CHECK;
        }
        $responseDocumentList = $this->getAllItemList(new DocumentList($requestParamList));
        $propertyList = array_flip(self::getClassPropertyList(Document::class));
        foreach ($responseDocumentList as $responseDocument) {
            $config = array_intersect_key($responseDocument, $propertyList);
            $document = new Document($config);
            $document->scenario = Document::SCENARIO_LOAD_LIST;
            if (!$document->validate()) {
                throw new Exception(Document::class . ': ' . current($document->firstErrors));
            }
            $result[] = $document;
        }
        return $result;
    }

    /**
     * Получить ФФД теги домента $document.
     * @param Document $document
     * @return DocumentTag
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \ReflectionException
     */
    public function getDocumentTag(Document $document)
    {
        if (!$document->validate()) {
            throw new Exception(get_class($document) . ': ' . current($document->firstErrors));
        }
        $responseDocumentInfo = $this->client->request(new DocumentInfo([
            'fdNumber'        => $document->fdNumber,
            'fnFactoryNumber' => $document->fnFactoryNumber,
        ]));
        $propertyList = array_flip(self::getClassPropertyList(DocumentTag::class));
        $config = array_intersect_key(DocumentTag::configTransform($responseDocumentInfo['document']), $propertyList);
        $documentTag = new DocumentTag($config);
        if (!$documentTag->validate()) {
            throw new Exception(DocumentTag::class . ': ' . current($documentTag->firstErrors));
        }
        return $documentTag;
    }
}