<?php

namespace Mips\Http;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Mips HttpClient
 * @author Mips
 */
class HttpClient {

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $host;


    /**
     * @var string
     */
    private $userPwd;

    /**
     * @var HttpHeader
     */
    private $httpHeaders;

    /**
     * @param string $host
     * @return HttpClient
     */
    public function __construct(string $_host, LoggerInterface $logger = null) {
        $this->host = $_host;
        $this->httpHeaders = new HttpHeader();
        $this->httpHeaders->setHeader('Content-Type', 'application/json; charset=utf-8');
        $this->httpHeaders->setHeader('Accept', 'application/json');
        $this->logger = $logger ?: new NullLogger();

        if ($this->host[strlen($this->host) - 1] === '/') {
            $this->host = substr($this->host, 0, strlen($this->host) - 1);
        }
    }

    public function setBasicAuth(string $username, string $password) {
        if (!empty($username) && !empty($password)) {
            $this->userPwd = "{$username}:{$password}";
        } else {
            $this->userPwd = null;
        }
    }

    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    public function getLogger() {
        return $this->logger;
    }

    public function getHttpHeaders() {
        return $this->httpHeaders;
    }

    /**
     * @param string $_method must be 'GET', 'POST', 'PUT' or 'DELETE'
     * @param string $_path
     * @param array  $_data
     * @param HttpHeader $_headers
     * @throws \InvalidArgumentException
     * @return HttpResponse
     */
    public function doRequest(string $_method, string $_path, array $_data = [], HttpHeader $_headers = null) {
        $method = strtoupper($_method);

        if (!in_array($method, array('GET', 'POST', 'PUT', 'DELETE'))) throw new InvalidArgumentException("Method not supported:{$method}");

        $_headers ?: $_headers = new HttpHeader();

        //remove null data
        $requestData = array_filter($_data, function ($value) {
            return $value !== null;
        });

        $url = "{$this->host}/$_path";

        $countdata = count($requestData);
        $this->logger->debug("preparing request {$method} {$url} nb_data:{$countdata} nb_headers:{$_headers->count()}");

        $_headers->mergeHeaders($this->httpHeaders);
        $this->logger->debug("headers count after merge:{$_headers->count()}");

        $ch = curl_init();
        if ($method === 'GET' && count($requestData)) {
            $url .= '?' . http_build_query($requestData);
        } elseif ($method != 'GET') {
            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
            } else {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            }
            $content = json_encode($requestData);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
            $_headers->setHeader('Content-Length', strlen($content));
            unset($content);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $_headers->getHeadersForHttp());
        if (!empty($this->userPwd)) {
            curl_setopt($ch, CURLOPT_USERPWD, $this->userPwd);
        }

        $this->logger->debug("sending request...");
        $curlResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        unset($ch);
        $this->logger->debug("result {$httpCode}");

        return new HttpResponse($httpCode, $curlResponse, $error);
    }

    public function doGet(string $_path, array $_data = [], HttpHeader $_headers = null) {
        return $this->doRequest('GET', $_path, $_data, $_headers);
    }

    public function doPost(string $_path, array $_data = [], HttpHeader $_headers = null) {
        return $this->doRequest('POST', $_path, $_data, $_headers);
    }

    public function doPut(string $_path, array $_data = [], HttpHeader $_headers = null) {
        return $this->doRequest('PUT', $_path, $_data, $_headers);
    }

    public function doDelete(string $_path, array $_data = [], HttpHeader $_headers = null) {
        return $this->doRequest('DELETE', $_path, $_data, $_headers);
    }
}
