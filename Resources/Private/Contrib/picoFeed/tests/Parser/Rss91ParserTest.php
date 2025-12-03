<?php

namespace PicoFeed\Parser;

use PHPUnit_Framework_TestCase;

class Rss91ParserTest extends PHPUnit_Framework_TestCase
{
    public function testFormatOk()
    {
        $parser = new Rss91(file_get_contents('tests/fixtures/rss_0.91.xml'));
        $feed = $parser->execute();

        self::assertNotFalse($feed);
        self::assertNotEmpty($feed->items);

        self::assertEquals('WriteTheWeb', $feed->getTitle());
        self::assertEquals('', $feed->getFeedUrl());
        self::assertEquals('http://writetheweb.com/', $feed->getSiteUrl());
        self::assertEquals('http://writetheweb.com/', $feed->getId());
        self::assertEquals(time(), $feed->getDate()->getTimestamp(), '', 1);
        self::assertEquals(6, count($feed->items));

        self::assertEquals('Giving the world a pluggable Gnutella', $feed->items[0]->getTitle());
        self::assertEquals('http://writetheweb.com/read.php?item=24', $feed->items[0]->getUrl());
        self::assertEquals('085a9133a75542f878fa73ee2afbb6a2350b6c4fb125e6d8ca09478c47702111', $feed->items[0]->getId());
        self::assertEquals(time(), $feed->items[0]->getDate()->getTimestamp(), '', 1);
        self::assertEquals('webmaster@writetheweb.com', $feed->items[0]->getAuthor());
        self::assertTrue(strpos($feed->items[1]->getContent(), '<p>After a period of dormancy') === 0);
    }
}
