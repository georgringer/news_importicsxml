<?php

namespace PicoFeed\Client;

use PHPUnit_Framework_TestCase;

class HttpHeadersTest extends PHPUnit_Framework_TestCase
{
    public function testHttpHeadersSet()
    {
        $headers = new HttpHeaders(['Content-Type' => 'test']);
        self::assertEquals('test', $headers['content-typE']);
        self::assertTrue(isset($headers['ConTent-Type']));

        unset($headers['Content-Type']);
        self::assertFalse(isset($headers['ConTent-Type']));
    }
}
