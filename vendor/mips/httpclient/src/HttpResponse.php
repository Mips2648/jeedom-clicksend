<?php

namespace Mips\Http;

class HttpResponse {

    /**
     * @var int
     */
    private $httpStatusCode;

    /**
     * @var string
     */
    private $body;

    /**
     * @var string
     */
    private $error;

    /**
     * @param int $httpCode
     * @param string $body
     * @param string  $error
     */
    public function __construct($httpCode, $body, $error = '') {
        $this->httpStatusCode = (int)$httpCode;
        $this->body = $body;
        $this->error = $error;
    }

    /**
     * @return string
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * Contains the values of status codes defined for HTTP.
     * @return int
     */
    public function getHttpStatusCode() {
        return $this->httpStatusCode;
    }

    /**
     * Get Error
     * @return string
     */
    public function getError() {
        return $this->error;
    }

    /**
     * Gets a value that indicates if the HTTP response was successful.
     * @return bool
     */
    public function isSuccess() {
        return $this->httpStatusCode >= 200 && $this->httpStatusCode < 300;
    }
}
