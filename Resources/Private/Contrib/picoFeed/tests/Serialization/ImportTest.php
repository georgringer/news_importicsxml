<?php

namespace PicoFeed\Serialization;

use PHPUnit_Framework_TestCase;

class ImportTest extends PHPUnit_Framework_TestCase
{
    public function testMalFormedFormat()
    {
        $import = new Import('boo');
        self::assertFalse($import->execute());
    }

    public function testFormat()
    {
        $import = new Import(file_get_contents('tests/fixtures/subscriptionList.opml'));
        $entries = $import->execute();

        self::assertEquals(14, count($entries));
        self::assertEquals('CNET News.com', $entries[0]->title);
        self::assertEquals('http://news.com.com/2547-1_3-0-5.xml', $entries[0]->feed_url);
        self::assertEquals('http://news.com.com/', $entries[0]->site_url);
    }

    public function testGoogleReader()
    {
        $import = new Import(file_get_contents('tests/fixtures/google-reader.opml'));
        $entries = $import->execute();

        self::assertEquals(22, count($entries));
        self::assertEquals('Code', $entries[21]->category);
        self::assertEquals('Vimeo / CocoaheadsRNS', $entries[21]->title);
        self::assertEquals('http://vimeo.com/cocoaheadsrns/videos/rss', $entries[21]->feed_url);
        self::assertEquals('http://vimeo.com/cocoaheadsrns/videos', $entries[21]->site_url);
    }

    public function testTinyTinyRss()
    {
        $import = new Import(file_get_contents('tests/fixtures/tinytinyrss.opml'));
        $entries = $import->execute();

        self::assertEquals(2, count($entries));
        self::assertEquals('coding', $entries[1]->category);
        self::assertEquals('Planète jQuery', $entries[1]->title);
        self::assertEquals('http://feeds.feedburner.com/PlaneteJqueryFr', $entries[1]->feed_url);
        self::assertEquals('http://planete-jquery.fr', $entries[1]->site_url);
    }

    public function testNewsBeuter()
    {
        $import = new Import(file_get_contents('tests/fixtures/newsbeuter.opml'));
        $entries = $import->execute();

        self::assertEquals(35, count($entries));
        self::assertEquals('', $entries[1]->category);
        self::assertEquals('code.flickr.com', $entries[1]->title);
        self::assertEquals('http://code.flickr.net/feed/', $entries[1]->feed_url);
        self::assertEquals('http://code.flickr.net', $entries[1]->site_url);
    }
}
