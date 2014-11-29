<?php

use GuzzleHttp\Client;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use paslandau\ArrayUtility\ArrayUtil;
use paslandau\KeywordSuggest\Exceptions\KeywordSuggestException;
use paslandau\KeywordSuggest\ScraperRequests\GoogleKeywordRequest;
use paslandau\WebUtility\WebUtil;

class GoogleKeywordRequestTest extends PHPUnit_Framework_TestCase {

    public function test_createRequest(){

        $client = new Client();

        $tests = [
            "keyword-default" => [
                "input" => new GoogleKeywordRequest("test"),
                "expected" => $client->createRequest("GET","http://clients1.google.com/complete/search?q=test&hl=de&client=hp")
            ],
            "keyword-group" => [
                "input" => new GoogleKeywordRequest("test","test-group"),
                "expected" => $client->createRequest("GET","http://clients1.google.com/complete/search?q=test&hl=de&client=hp")
            ],
            "keyword-group-cursor" => [
                "input" => new GoogleKeywordRequest("test","test-group",2),
                "expected" => $client->createRequest("GET","http://clients1.google.com/complete/search?q=test&hl=de&cp=2&client=hp")
            ],
            "keyword-group-cursor-domain" => [
                "input" => new GoogleKeywordRequest("test","test-group",2,"example.com"),
                "expected" => $client->createRequest("GET","http://clients1.google.com/complete/search?q=test&hl=de&cp=2&client=navquery&ds=navquery&gs_ri=navquery&requiredfields=site%3Aexample.com&sos=1")
            ],
            "keyword-group-cursor-domain-lang" => [
                "input" => new GoogleKeywordRequest("test","test-group",2,"example.com","en"),
                "expected" => $client->createRequest("GET","http://clients1.google.com/complete/search?q=test&hl=en&cp=2&client=navquery&ds=navquery&gs_ri=navquery&requiredfields=site%3Aexample.com&sos=1")
            ],
        ];

        foreach($tests as $test => $data){
            /** @var GoogleKeywordRequest $kwRequest */
            $kwRequest = $data["input"];
            $res = $kwRequest->createRequest($client);

            $expected = $this->requestToArray($data["expected"]);
            $actual = $this->requestToArray($res);
//            echo $res->getUrl()."\n";
            $msg = [
                "Error in test $test:",
                "Input    : ".json_encode($kwRequest),
                "Excpected: ".json_encode($expected),
                "Actual   : ".json_encode($actual),
            ];
            $msg = implode("\n",$msg);
            $this->assertTrue(ArrayUtil::equals($actual,$expected,true,false,false),$msg);
        }
    }

    public function requestToArray(RequestInterface $req)
    {
        return [
            "method" => $req->getMethod(),
            "url" => WebUtil::normalizeUrl($req->getUrl()),
            "options" => $req->getConfig()->toArray()
        ];
    }

    public function test_getSuggests(){

        $request = new GoogleKeywordRequest("test");
        $tests = [
            "default" => [
                "input" => 'window.google.ac.h(["was osterglocke",[["was\u003cb\u003e ist eine \u003c\/b\u003eosterglocke",0,[7,30]],["was\u003cb\u003e ist der unterschied zwischen \u003c\/b\u003eosterglocke\u003cb\u003e und narzisse\u003c\/b\u003e",0,[7,30]],["was\u003cb\u003e heißt \u003c\/b\u003eosterglocke\u003cb\u003e auf englisch\u003c\/b\u003e",0,[7,30]],["was\u003cb\u003e hat die \u003c\/b\u003eosterglocke\u003cb\u003e mit ostern zu tun\u003c\/b\u003e",0,[7,30]],["osterglocke\u003cb\u003en verblüht \u003c\/b\u003ewas\u003cb\u003e nun\u003c\/b\u003e",0,[8,30]],["was\u003cb\u003e tun wenn \u003c\/b\u003eosterglocke\u003cb\u003en verblüht sind\u003c\/b\u003e",0,[8,30]],["was\u003cb\u003e macht man mit verblühten \u003c\/b\u003eosterglocke\u003cb\u003en\u003c\/b\u003e",0,[8,30]],["was\u003cb\u003e machen wenn \u003c\/b\u003eosterglocke\u003cb\u003en verblüht sind\u003c\/b\u003e",0,[8,30]],["was\u003cb\u003e tun mit verwelkten \u003c\/b\u003eosterglocke\u003cb\u003en\u003c\/b\u003e",0,[8,30]]],{"q":"O0vYloU4svMTIIhXyeyZlKYqCQ4"}])',
                "expected" => ["was ist eine osterglocke", "was ist der unterschied zwischen osterglocke und narzisse", "was heißt osterglocke auf englisch", "was hat die osterglocke mit ostern zu tun", "osterglocken verblüht was nun", "was tun wenn osterglocken verblüht sind", "was macht man mit verblühten osterglocken", "was machen wenn osterglocken verblüht sind", "was tun mit verwelkten osterglocken"]
            ],
            "default-for-domain"  => [
                "input" => 'window.google.ac.h(["s",[["s\u003cb\u003echuhe\u003c\/b\u003e",0],["s\u003cb\u003eale\u003c\/b\u003e",0],["s\u003cb\u003echuhe damen\u003c\/b\u003e",0],["s\u003cb\u003etiefeletten\u003c\/b\u003e",0],["s\u003cb\u003eandalen\u003c\/b\u003e",0],["s",88,[],{"b":"Ergebnisse für \"\u0007s\f\""}]],{"q":"LX5WCDdsHz5YP96RARyLtfXzWRQ"}])"',
                "expected" => ["schuhe", "sale", "schuhe damen", "stiefeletten", "sandalen"]
            ],
            "0-suggests" => [
                "input" => 'window.google.ac.h(["was tchibo",[],{"q":"O0vYloU4svMTIIhXyeyZlKYqCQ4"}])',
                "expected" => []
            ],
            "empty" => [
                "input" => '',
                "expected" => KeywordSuggestException::class
            ],
            "invalid-json" => [
                "input" => 'window.google.ac.h(["was {invalid json])',
                "expected" => KeywordSuggestException::class
            ],
            // @todo: how does the content look for blocked?
//            "blocked" => [
//                "input" => "",
//                "expected" => KeywordSuggestException::class
//            ],
        ];

        foreach($tests as $test => $data){
            $body = new Stream(fopen('php://temp', 'r+'));// see Guzzle 4.1.7 > GuzzleHttp\Adapter\Curl\RequestMediator::writeResponseBody
            $body->write($data["input"]);
            $resp = new Response("200",[],$body);

            try {
                $res = $request->getSuggests($resp);
//                echo "[\"".implode('", "',$res)."\"]"."\n";
            }catch(Exception $e){
                $res = get_class($e);
            }

            $expected = $data["expected"];
            $actual = $res;
//            echo $res->getUrl()."\n";
            $msg = [
                "Error in test $test:",
                "Input    : ".json_encode($data["input"]),
                "Excpected: ".json_encode($expected),
                "Actual   : ".json_encode($actual),
            ];
            $msg = implode("\n",$msg);
            if(is_array($expected)){
                $this->assertTrue(ArrayUtil::equals($actual, $expected, true, false, false), $msg);
            }else{
                $this->assertEquals($expected, $actual, $msg);
            }
        }
    }
}
 