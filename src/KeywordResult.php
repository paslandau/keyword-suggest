<?php

namespace paslandau\KeywordSuggest;


use paslandau\KeywordSuggest\ScraperRequests\KeywordRequestInterface;

class KeywordResult {
    /**
     * @var KeywordRequestInterface
     */
    private $request;
    /**
     * @var null|\string[]
     */
    private $suggests;
    /**
     * @var \Exception|null
     */
    private $exception;

    /**
     * @param KeywordRequestInterface $request
     * @param string[]|null $suggests [optional]. Default: null.
     * @param \Exception|null $exception [optional]. Default: null.
     */
    function __construct($request, $suggests = null, $exception = null)
    {
        $this->exception = $exception;
        $this->request = $request;
        $this->suggests = $suggests;
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
    public function getSuggests()
    {
        return $this->suggests;
    }

    /**
     * @param null|\string[] $suggests
     */
    public function setSuggests($suggests)
    {
        $this->suggests = $suggests;
    }


} 