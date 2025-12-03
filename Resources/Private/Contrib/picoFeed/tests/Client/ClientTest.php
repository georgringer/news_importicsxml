<?php

namespace PicoFeed\Client;

use PHPUnit_Framework_TestCase;

class ClientTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group online
     */
    public function testDownload()
    {
        $client = Client::getInstance();
        $client->setUrl('http://php.net/robots.txt');
        $client->execute();

        self::assertTrue($client->isModified());
        self::assertNotEmpty($client->getContent());
        self::assertNotEmpty($client->getEtag());
        self::assertNotEmpty($client->getLastModified());
    }

    /**
     * @runInSeparateProcess
     * @group online
     */
    public function testPassthrough()
    {
        $client = Client::getInstance();
        $client->setUrl('http://miniflux.net/favicon.ico');
        $client->enablePassthroughMode();
        $client->execute();

        $this->expectOutputString(file_get_contents('tests/fixtures/miniflux_favicon.ico'));
    }

    /**
     * @group online
     */
    public function testCacheBothHaveToMatch()
    {
        $client = Client::getInstance();
        $client->setUrl('http://php.net/robots.txt');
        $client->execute();
        $etag = $client->getEtag();

        $client = Client::getInstance();
        $client->setUrl('http://php.net/robots.txt');
        $client->setEtag($etag);
        $client->execute();

        self::assertTrue($client->isModified());
    }

    /**
     * @group online
     */
    public function testCacheEtag()
    {
        $client = Client::getInstance();
        $client->setUrl('http://php.net/robots.txt');
        $client->execute();
        $etag = $client->getEtag();
        $lastModified = $client->getLastModified();

        $client = Client::getInstance();
        $client->setUrl('http://php.net/robots.txt');
        $client->setEtag($etag);
        $client->setLastModified($lastModified);
        $client->execute();

        self::assertFalse($client->isModified());
    }

    /**
     * @group online
     */
    public function testCacheLastModified()
    {
        $client = Client::getInstance();
        $client->setUrl('http://miniflux.net/humans.txt');
        $client->execute();
        $lastmod = $client->getLastModified();

        $client = Client::getInstance();
        $client->setUrl('http://miniflux.net/humans.txt');
        $client->setLastModified($lastmod);
        $client->execute();

        self::assertFalse($client->isModified());
    }

    /**
     * @group online
     */
    public function testCacheBoth()
    {
        $client = Client::getInstance();
        $client->setUrl('http://miniflux.net/humans.txt');
        $client->execute();
        $lastmod = $client->getLastModified();
        $etag = $client->getEtag();

        $client = Client::getInstance();
        $client->setUrl('http://miniflux.net/humans.txt');
        $client->setLastModified($lastmod);
        $client->setEtag($etag);
        $client->execute();

        self::assertFalse($client->isModified());
    }

    /**
     * @group online
     */
    public function testCharset()
    {
        $client = Client::getInstance();
        $client->setUrl('http://php.net/');
        $client->execute();
        self::assertEquals('utf-8', $client->getEncoding());

        $client = Client::getInstance();
        $client->setUrl('http://php.net/robots.txt');
        $client->execute();
        self::assertEquals('', $client->getEncoding());
    }

    /**
     * @group online
     */
    public function testContentType()
    {
        $client = Client::getInstance();
        $client->setUrl('http://miniflux.net/assets/img/favicon.png');
        $client->execute();
        self::assertEquals('image/png', $client->getContentType());

        $client = Client::getInstance();
        $client->setUrl('http://miniflux.net/');
        $client->execute();
        self::assertEquals('text/html; charset=utf-8', $client->getContentType());
    }
}
