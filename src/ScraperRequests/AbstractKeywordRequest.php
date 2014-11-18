<?php

namespace paslandau\KeywordSuggest\ScraperRequests;


use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\ResponseInterface;
use paslandau\KeywordSuggest\Exceptions\KeywordSuggestException;

abstract class AbstractKeywordRequest implements KeywordRequestInterface{

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
     * @param string $group. [optional]. Default: "".
     */
    function __construct($keyword, $group = null)
    {
        if($group === null) {
            $group = "";
        }
        $this->group = $group;
        $this->keyword = $keyword;
    }
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