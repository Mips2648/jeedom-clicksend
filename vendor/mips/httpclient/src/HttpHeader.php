<?php

namespace Mips\Http;

use ArrayObject;

class HttpHeader {
    /**
     * @var ArrayObject
     */
    protected $headers;

    public function __construct() {
        $this->headers = new ArrayObject();
    }

    public function headerExists($header) {
        return $this->headers->offsetExists($header);
    }

    public function setHeader($header, $value) {
        $this->headers->offsetSet($header, $value);
    }

    public function getHeader($header) {
        $this->headers->offsetGet($header);
    }

    public function removeHeader($header) {
        $this->headers->offsetUnset($header);
    }

    public function mergeHeaders(HttpHeader $_headers) {
        foreach ($_headers->getIterator() as $key => $value) {
            $this->setHeader($key, $value);
        }
    }

    public function getHeadersForHttp() {
        $return = array();
        foreach ($this->headers as $key => $value) {
            $return[] = "{$key}: {$value}";
        }

        return $return;
    }

    public function count() {
        return $this->headers->count();
    }

    public function getIterator() {
        return $this->headers->getIterator();
    }
}
