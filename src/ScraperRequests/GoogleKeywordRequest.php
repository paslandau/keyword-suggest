<?php

namespace paslandau\KeywordSuggest\ScraperRequests;


use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use paslandau\KeywordSuggest\Exceptions\KeywordSuggestException;

class GoogleKeywordRequest extends AbstractKeywordRequest
{

    const SUGGEST_URL = "http://clients1.google.com/complete/search";

    /**
     * @var int|null
     */
    private $cursorPosition;
    /**
     * @var null|string
     */
    private $domain;

    /**
     * @var string
     */
    private $lang;

    /**
     * @param string $keyword
     * @param string $group. [optional]. Default: "".
     * @param int|null $cursorPosition [optional]. Default: null.
     * @param string|null $domain [optional]. Default: null.
     * @param string $domain [optional]. Default: de.
     */
    function __construct($keyword, $group = null, $cursorPosition = null, $domain = null, $lang = null)
    {
        $this->cursorPosition = $cursorPosition;
        $this->domain = $domain;
        if($lang === null) {
            $lang = "de";
        }
        $this->lang = $lang;
        parent::__construct($keyword, $group);
    }

    /**
     * @param ClientInterface $client
     * @return RequestInterface
     */
    public function createRequest(ClientInterface $client){
        $query = [];
        $query["q"] = $this->keyword;
        $query["hl"] = $this->lang;
        if($this->cursorPosition !== null && $this->cursorPosition > 0 && $this->cursorPosition <= mb_strlen($this->keyword)){
            $query["cp"] = $this->cursorPosition;
        }
        if($this->domain !== null){
            $query["client"] = "navquery";
            $query["ds"] = "navquery";
            $query["gs_ri"] = "navquery";
            $query["requiredfields"] = "site:".$this->domain;
            $query["sos"] = "1";
        }else{
            $query["client"] = "hp";
        }
        $options = ["query" => $query];
        $req = $client->createRequest("GET",self::SUGGEST_URL,$options);
        return $req;
    }

    /**
     * @param ResponseInterface $resp
     * @return string[]
     * @throws KeywordSuggestException
     */
    public function getSuggests(ResponseInterface $resp)
    {
        $body = $resp->getBody()->__toString();
        $pattern = "#window\\.google\\.ac\\.h\\((?P<json>.*)\\)#i";
        if (!preg_match($pattern, $body, $json)) {
            throw new KeywordSuggestException("Invalid response, pattern $pattern not found in '{$body}'");
        }
        $obj = json_decode($json["json"]);
        if ($obj === false) {
            throw new KeywordSuggestException("Invalid json: '{$json["json"]}'");
        }
        if (!is_array($obj[1])) {
            throw new KeywordSuggestException("Expected index \$obj[1] to be an array in '{$json["json"]}'");
        }
        $suggestArray = [];
        foreach ($obj[1] as $arr) {
            $tmp = $arr[0];
            $tmp = str_replace("<b>", "", $tmp);
            $tmp = str_replace("</b>", "", $tmp);
            $suggestArray[] = $tmp;
        }
        return $suggestArray;
    }

    /**
     * @return int|null
     */
    public function getCursorPosition()
    {
        return $this->cursorPosition;
    }

    /**
     * @param int|null $cursorPosition
     */
    public function setCursorPosition($cursorPosition)
    {
        $this->cursorPosition = $cursorPosition;
    }

    /**
     * @return null|string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param null|string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }
}