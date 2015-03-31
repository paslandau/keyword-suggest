<?php

namespace paslandau\KeywordSuggest\Requests;


use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use paslandau\QueryScraper\Requests\QueryRequestInterface;

interface KeywordRequestInterface extends QueryRequestInterface{
    /**
     * @param ClientInterface $client
     * @return RequestInterface
     */
    public function createRequest(ClientInterface $client);

    /**
     * @param ResponseInterface $resp
     * @return string[]
     */
    public function getResult(ResponseInterface $resp);
}