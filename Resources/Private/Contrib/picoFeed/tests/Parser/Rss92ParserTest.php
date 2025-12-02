<?php

namespace PicoFeed\Parser;

use PHPUnit_Framework_TestCase;

class Rss92ParserTest extends PHPUnit_Framework_TestCase
{
    public function testFormatOk()
    {
        $parser = new Rss92(file_get_contents('tests/fixtures/univers_freebox.xml'));
        $feed = $parser->execute();

        self::assertNotFalse($feed);
        self::assertNotEmpty($feed->items);

        self::assertEquals('Univers Freebox', $feed->getTitle());
        self::assertEquals('', $feed->getFeedUrl());
        self::assertEquals('http://www.universfreebox.com/', $feed->getSiteUrl());
        self::assertEquals('http://www.universfreebox.com/', $feed->getId());
        self::assertEquals(time(), $feed->getDate()->getTimestamp(), '', 1);
        self::assertEquals(30, count($feed->items));

        self::assertEquals('Retour de Xavier Niel sur Twitter, « sans initiative privée, pas de révolution #Born2code »', $feed->items[0]->title);
        self::assertEquals('http://www.universfreebox.com/article20302.html', $feed->items[0]->getUrl());
        self::assertEquals('ad23a45af194cc46d5151a9a062c5841b03405e456595c30b742d827e08af2e0', $feed->items[0]->getId());
        self::assertEquals('', $feed->items[0]->getAuthor());
    }
}
