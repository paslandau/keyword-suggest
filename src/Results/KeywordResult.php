<?php

namespace paslandau\KeywordSuggest\Results;


use paslandau\KeywordSuggest\Requests\KeywordRequestInterface;
use paslandau\QueryScraper\Results\QueryResultInterface;

class KeywordResult implements QueryResultInterface {
    /**
     * @var KeywordRequestInterface
     */
    private $request;
    /**
     * @var null|\string[]
     */
    private $result;
    /**
     * @var \Exception|null
     */
    private $exception;

    /**
     * @param KeywordRequestInterface $request
     * @param string[]|null $result [optional]. Default: null.
     * @param \Exception|null $exception [optional]. Default: null.
     */
    function __construct($request, $result = null, $exception = null)
    {
        $this->exception = $exception;
        $this->request = $request;
        $this->result = $result;
    }

    /**
     * @return \Exception|null
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @param \Exception|null $exception
     */
    public function setException($exception)
    {
        $this->exception = $exception;
    }

    /**
     * @return KeywordRequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param KeywordRequestInterface $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return null|\string[]
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param null|\string[] $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }
}