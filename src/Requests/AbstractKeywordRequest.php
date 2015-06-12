<?php

namespace paslandau\KeywordSuggest\Requests;


use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use paslandau\KeywordSuggest\Exceptions\KeywordSuggestException;
use paslandau\KeywordSuggest\Results\KeywordResult;

abstract class AbstractKeywordRequest implements KeywordRequestInterface
{

    /**
     * @var string
     */
    protected $group;

    /**
     * @var string
     */
    protected $keyword;

    /**
     * @param string $keyword
     * @param string $group . [optional]. Default: "".
     */
    function __construct($keyword, $group = null)
    {
        if ($group === null) {
            $group = "";
        }
        $this->group = $group;
        $this->keyword = $keyword;
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $resp
     * @param \Exception $exception
     * @return KeywordResult
     * @throws KeywordSuggestException
     */
    public function getResult(RequestInterface $request, ResponseInterface $resp = null, \Exception $exception = null)
    {
        $suggests = null;
        if ($exception === null) {
            try {
                $body = $resp->getBody()->__toString();
                $suggests = $this->parseSuggests($body);
            } catch (\Exception $e) {
                $exception = $e;
            }
        }
        $result = new KeywordResult($this, $suggests, $exception);
        $retry = $suggests === null;
        $result->setRetry($retry);
        return $result;
    }

    /**
     * @param string $body
     * @return string[]
     */
    abstract public function parseSuggests($body);

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param string $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * @return string
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * @param string $keyword
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;
    }

}