<?php

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Message\Request;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Subscriber\Mock;
use paslandau\ArrayUtility\ArrayUtil;
use paslandau\GuzzleRotatingProxySubscriber\Exceptions\NoProxiesLeftException;
use paslandau\GuzzleRotatingProxySubscriber\ProxyRotator;
use paslandau\KeywordSuggest\Exceptions\KeywordSuggestException;
use paslandau\KeywordSuggest\KeywordResult;
use paslandau\KeywordSuggest\KeywordSuggestScraper;
use paslandau\KeywordSuggest\ScraperRequests\AbstractKeywordRequest;
use paslandau\KeywordSuggest\ScraperRequests\KeywordRequestInterface;

class KeywordSuggestScraperTest extends PHPUnit_Framework_TestCase {

    public function test_scrapeKeywords(){
        $client = new Client();

        $request = $this->getMock(KeywordRequestInterface::class);
        $createRequestFn = function (ClientInterface $client){
            return $client->createRequest("GET","/");
        };
        $request->expects($this->any())->method("createRequest")->will($this->returnCallback($createRequestFn));
        $suggests = ["foo"];
        $getSuggestsFn = function (ResponseInterface $response) use ($suggests){
            if($response->getStatusCode() == 200) {
                return $suggests;
            }
            throw new KeywordSuggestException("StatusCode must be 200");
        };
        $request->expects($this->any())->method("getSuggests")->will($this->returnCallback($getSuggestsFn));

        /** @var KeywordRequestInterface $request */
        $tests = [
            "request-successful" => [
                "responses" => [new Response(200)],
                "kss" => new KeywordSuggestScraper($client,1,5),
                "expected" => [new KeywordResult($request,$suggests,null)],
                "expectedException" => null
            ],
            "request-failed" => [
                "responses" => [new Response(404)],
                "kss" => new KeywordSuggestScraper($client,1,0),
                "expected" => [new KeywordResult($request,null,new ClientException("",$request->createRequest($client),null))],
                "expectedException" => null
            ],
            "retry-successful" => [
                "responses" => [new Response(404),new Response(404),new Response(200)],
                "kss" => new KeywordSuggestScraper($client,1,2),
                "expected" => [new KeywordResult($request,$suggests,null)],
                "expectedException" => null
            ],
            "retry-fail" => [
                "responses" => [new Response(404),new Response(404),new Response(200)],
                "kss" => new KeywordSuggestScraper($client,1,1),
                "expected" => [new KeywordResult($request,null,new ClientException("",$request->createRequest($client),null))],
                "expectedException" => null
            ],
            "no-proxies-left" => [
                "responses" => [new NoProxiesLeftException(new ProxyRotator(),$request->createRequest($client),"")],
                "kss" => new KeywordSuggestScraper($client,1,0),
                "expected" => [new KeywordResult($request,null,new NoProxiesLeftException(new ProxyRotator(),$request->createRequest($client),""))],
                "expectedException" => NoProxiesLeftException::class
            ],
        ];

        foreach ($tests as $test => $data) {
            $mock = new Mock($data["responses"]);
            $client->getEmitter()->attach($mock);

            $requests = [
                $request
            ];

            $this->assertKeywordResults($data["kss"],$test,$requests,$data["expected"],$data["expectedException"]);
            $client->getEmitter()->detach($mock);
        }
    }

    public function assertKeywordResults(KeywordSuggestScraper $kss, $test, array $requests, array $expectedResults, $expectedKeywordSuggestScraperError = null){
        //check results
        $expected = $this->getKeywordResultsAsArray($expectedResults);
        $result = $kss->scrapeKeywords($requests);
        $actual = $this->getKeywordResultsAsArray($result);

        $msg = [
            "Error in test $test (checking KeywordResults):",
            "Excpected: ".json_encode($expected),
            "Actual   : ".json_encode($actual),
        ];
        $msg = implode("\n",$msg);
        $this->assertEquals($expected, $actual, $msg);

        if(is_array($expected)){
            $this->assertTrue(ArrayUtil::equals($actual, $expected, true, false, true), $msg);
        }else{
            $this->assertEquals($expected, $actual, $msg);
        }

        //check Exception
        $expected = $expectedKeywordSuggestScraperError;
        $actual = $kss->getError();
        if($actual !== null){
            $actual = get_class($actual);
        }
        $msg = [
            "Error in test $test (checking KeywordSuggestScraper error):",
            "Excpected: ".json_encode($expected),
            "Actual   : ".json_encode($actual),
        ];
        $msg = implode("\n",$msg);
        $this->assertEquals($expected, $actual, $msg);
    }

    /**
     * @param KeywordResult[] $results
     * @return array
     */
    private function getKeywordResultsAsArray(array $results){
        $arr = [];
        foreach($results as $result){
            $arr[] = [
                "request" => $result->getRequest(),
                "suggests" => $result->getSuggests(),
                "exception" => ( $result->getException() === null ? null : get_class($result->getException()) )
            ];
        }
        return $arr;
    }
}
 