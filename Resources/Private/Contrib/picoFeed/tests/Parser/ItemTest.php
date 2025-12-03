<?php

namespace PicoFeed\Parser;

use PHPUnit_Framework_TestCase;

class ItemTest extends PHPUnit_Framework_TestCase
{
    public function testLangRTL()
    {
        $item = new Item();
        $item->language = 'fr_FR';
        self::assertFalse($item->isRTL());

        $item->language = 'ur';
        self::assertTrue($item->isRTL());

        $item->language = 'syr-**';
        self::assertTrue($item->isRTL());

        $item->language = 'ru';
        self::assertFalse($item->isRTL());
    }
}
