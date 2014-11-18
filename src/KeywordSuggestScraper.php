<?php

namespace paslandau\KeywordSuggest;


use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Event\AbstractTransferEvent;
use GuzzleHttp\Event\EndEvent;
use GuzzleHttp\Event\ErrorEvent;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Pool;
use paslandau\GuzzleRotatingProxySubscriber\Exceptions\NoProxiesLeftException;
use paslandau\KeywordSuggest\Exceptions\KeywordSuggestException;
use paslandau\KeywordSuggest\ScraperRequests\KeywordRequestInterface;

class KeywordSuggestScraper
{
    const GUZZLE_REQUEST_ID_KEY = "keyword_suggest_request_id";
    const GUZZLE_REQUEST_RETRIES = "keyword_suggest_request_retries";

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var int
     */
    private $parallel;

    /**
     * @var int
     */
    private $maxRetries;

    /**
     * @var \Exception
     */
    private $error;

    /**
     * @param Client $client
     * @param int $parallel . [optional]. Default: 5.
     * @param int $maxRetries . [optional]. Default: 3.
     */
    function __construct(ClientInterface $client, $parallel = null, $maxRetries = null)
    {
        $this->client = $client;
        if ($parallel === null) {
            $parallel = 5;
        }
        $this->parallel = $parallel;
        if ($maxRetries === null) {
            $maxRetries = 3;
        }
        $this->maxRetries = $maxRetries;
        $this->error = null;
    }

    /**
     * @param KeywordRequestInterface[] $keywordRequests
     * @return KeywordResult[]
     */
    public function scrapeKeywords(array $keywordRequests)
    {
        $this->error = null;
        $result = [];

        // prepare requests
        $requests = [];
        foreach ($keywordRequests as $key => $keywordRequest) {
            $req = $keywordRequest->createRequest($this->client);
            $req->getConfig()->set(self::GUZZLE_REQUEST_ID_KEY, $key);
            $req->getConfig()->set(self::GUZZLE_REQUEST_RETRIES, 0);
            $requests[$key] = $req;
            $result[$key] = new KeywordResult($keywordRequest, [], new KeywordSuggestException("Request has not been executed!"));
        }

        $complete = function (KeywordRequestInterface $request, ResponseInterface $response) {
            $suggests = [];
            $exception = null;
            try {
                $suggests = $request->getSuggests($response);
            } catch (\Exception $e) {
                $exception = $e;
            }
            $keywordResult = new KeywordResult($request, $suggests, $exception);
            return $keywordResult;
        };

        $error = function (KeywordRequestInterface $request, \Exception $exception) {
            $keywordResult = new KeywordResult($request, null, $exception);
            return $keywordResult;
        };

        $end = function (AbstractTransferEvent $event) use ($complete, $error, &$result, &$keywordRequests) {
//            echo "In keywordSuggestEnd filter, " . $event->getRequest()->getConfig()->get(KeywordSuggestScraper::GUZZLE_REQUEST_ID_KEY) . "\n";
            $request = $event->getRequest();
            $response = $event->getResponse();
            $exception = null;
            if ($event instanceof ErrorEvent) {
                $exception = $event->getException();
            }
            $requestId = $request->getConfig()->get(self::GUZZLE_REQUEST_ID_KEY);
            $keywordRequest = $keywordRequests[$requestId];
            if ($exception === null) {
                $keywordResult = $complete($keywordRequest, $response);
            } else {
                $keywordResult = $error($keywordRequest, $exception);
            }
            /** @var KeywordResult $keywordResult */
            if ($keywordResult->getException() !== null) {
//                echo $keywordResult->getException()->getMessage() . "\n";
                $curRetries = $request->getConfig()->get(self::GUZZLE_REQUEST_RETRIES);
                if ($curRetries < $this->maxRetries) {
                    $curRetries++;
//                    echo "Retring $requestId ($curRetries)\n";
                    $request->getConfig()->set(self::GUZZLE_REQUEST_RETRIES, $curRetries);
                    $event->retry();
                    return;
                }
//                echo "Exceeded retries for $requestId ($curRetries)\n";
            }
            $result[$requestId] = $keywordResult;
        };


        $pool = new Pool($this->client, $requests,
            [
                "pool_size" => $this->parallel,
                "complete" => $end,
                "error" => $end,
                "end" => function (EndEvent $event) use (&$pool) {
                    $exception = $event->getException();
//                    echo "In terminateFn filter, ".$event->getRequest()->getConfig()->get(KeywordSuggestScraper::GUZZLE_REQUEST_ID_KEY)."\n";
                    if ($exception instanceof NoProxiesLeftException) {
//                        echo $exception->getMessage();
                        $this->error = $exception;
                        $pool->cancel();
                    }
                }
            ]);
        $pool->wait();

        return $result;
    }

    /**
     * @return \Exception
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }
}