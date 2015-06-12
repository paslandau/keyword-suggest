<?php

namespace paslandau\KeywordSuggest\Requests;


use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use paslandau\KeywordSuggest\Results\KeywordResult;
use paslandau\QueryScraper\Requests\QueryRequestInterface;

interface KeywordRequestInterface extends QueryRequestInterface
{
    /**
     * @param ClientInterface $client
     * @return RequestInterface
     */
    public function createRequest(ClientInterface $client);

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $resp
     * @param Exception $exception
     * @return KeywordResult
     */
    public function getResult(RequestInterface $request, ResponseInterface $resp = null, \Exception $exception = null);
}