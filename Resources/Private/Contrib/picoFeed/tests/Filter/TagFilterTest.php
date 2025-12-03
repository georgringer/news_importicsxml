<?php

namespace PicoFeed\Filter;

use PHPUnit_Framework_TestCase;

class TagFilterTest extends PHPUnit_Framework_TestCase
{
    public function testAllowedTag()
    {
        $tag = new Tag();

        self::assertTrue($tag->isAllowed('p', ['class' => 'test']));
        self::assertTrue($tag->isAllowed('img', ['class' => 'test']));

        self::assertFalse($tag->isAllowed('script', ['class' => 'test']));
        self::assertFalse($tag->isAllowed('img', ['width' => '1', 'height' => '1']));
    }

    public function testHtml()
    {
        $tag = new Tag();

        self::assertEquals('<p>', $tag->openHtmlTag('p'));
        self::assertEquals('<img src="test" alt="truc"/>', $tag->openHtmlTag('img', 'src="test" alt="truc"'));
        self::assertEquals('<img/>', $tag->openHtmlTag('img'));
        self::assertEquals('<br/>', $tag->openHtmlTag('br'));

        self::assertEquals('</p>', $tag->closeHtmlTag('p'));
        self::assertEquals('', $tag->closeHtmlTag('img'));
        self::assertEquals('', $tag->closeHtmlTag('br'));
    }
}
