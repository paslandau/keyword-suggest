<?php namespace paslandau\KeywordSuggest\ScraperRequests;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;

interface KeywordRequestInterface
{
    /**
     * @return string
     */
    public function getGroup();

    /**
     * @return string
     */
    public function getKeyword();

    /**
     * @param ClientInterface $client
     * @return RequestInterface
     */
    public function createRequest(ClientInterface $client);

    /**
     * @param ResponseInterface $resp
     * @return string[]
     */
    public function getSuggests(ResponseInterface $resp);
}