<?php

namespace pokupki63\Taxcom;

use pokupki63\Taxcom\Request\Login;
use pokupki63\Taxcom\Request\Request;
use yii\helpers\Json;

class Client
{
    /** @var \GuzzleHttp\Client */
    protected $httpClient = null;
    /** @var string  */
    protected $sessionToken = '';
    /** @var Login */
    protected $login = null;
    /** @var string  */
    protected $integratorId = '';
    private $version = '0.1.0';

    /**
     * @param string $domain       Имя домена на которы уходят запросы.
     * @param Login $login         Аккаунт для входа.
     * @param string $integratorId Токен доступа интегратора для работы с API.
     * @param string $sessionToken Сессионный токен доступа.
     * @throws \Exception
     */
    public function __construct(string $domain, Login $login, string $integratorId, string $sessionToken = '')
    {
        if (!$login->validate()) {
            $firstErrorList = $login->getFirstErrors();
            throw new \Exception(current($firstErrorList));
        }
        $this->httpClient = new \GuzzleHttp\Client([
            'base_uri' => 'https://' . $domain,
            'defaults' => [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ],
        ]);
        $this->integratorId = $integratorId;
        $this->login = $login;
        $this->sessionToken = $sessionToken;
    }

    /**
     * Залогиниться получив сессионный токен.
     * @return $this
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function login()
    {
        $response = $this->sendRequest($this->login);
        $body = Json::decode($response->getBody()->getContents(), true);
        if ($response->getStatusCode() != 200) {
            throw new Exception(
                $body['commonDescription'] ?? 'Неизвестная ошибка',
                $body['apiErrorCode'] ?? 0
            );
        }
        if (empty($body['sessionToken'])) {
            throw new Exception('Не удалось получить сессионный токен');
        }
        $this->sessionToken = $body['sessionToken'];
        return $this;
    }

    /**
     * Отправить запрос.
     * @param Request $endpoint
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     */
    protected function sendRequest($endpoint)
    {
        return $this->httpClient->request(
            $endpoint->method,
            $endpoint->path,
            [
                'debug' => $endpoint->debug,
                'exceptions' => false,
                'headers' => [
                    'Session-Token' => $this->sessionToken,
                    'Integrator-ID' => $this->integratorId,
                    'Accept'        => 'application/json',
                    'User-Agent'    => '63pokupki-PHP-SDK/' . $this->version,
                ],
                'query' => $endpoint->getQuery(),
                'json' => $endpoint->getBody(),
            ]
        );
    }

    /**
     * @param Request $endpoint
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     * @see https://lk-ofd.taxcom.ru/ApiHelp/index.html?3___.htm Обработка ошибок
     */
    public function request($endpoint)
    {
        $response = $this->sendRequest($endpoint);
        $body = Json::decode($response->getBody()->getContents(), true);
        $errorCode = $body['apiErrorCode'] ?? 0;
        // Истек срок действия маркера доступа
        if ($errorCode == 2109) {
            // Обновляем токен
            $this->login();
            $response = $this->sendRequest($endpoint);
            $body = Json::decode($response->getBody()->getContents(), true);
            $errorCode = $body['apiErrorCode'] ?? 0;
        }
        if (!empty($errorCode)) {
            throw new Exception(get_class($endpoint) . ' ошибка: ' . ($body['details'] ?? $body['commonDescription']), $errorCode);
        }
        return $body;
    }
}