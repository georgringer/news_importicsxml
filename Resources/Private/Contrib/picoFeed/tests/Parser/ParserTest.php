<?php

namespace PicoFeed\Parser;

use PHPUnit_Framework_TestCase;

class ParserTest extends PHPUnit_Framework_TestCase
{
    public function testChangeHashAlgo()
    {
        $parser = new Rss20('');
        self::assertEquals('fb8e20fc2e4c3f248c60c39bd652f3c1347298bb977b8b4d5903b85055620603', $parser->generateId('a', 'b'));

        $parser->setHashAlgo('sha1');
        self::assertEquals('da23614e02469a0d7c7bd1bdab5c9c474b1904dc', $parser->generateId('a', 'b'));
    }

    public function testLangRTL()
    {
        self::assertFalse(Parser::isLanguageRTL('fr-FR'));
        self::assertTrue(Parser::isLanguageRTL('ur'));
        self::assertTrue(Parser::isLanguageRTL('syr-**'));
        self::assertFalse(Parser::isLanguageRTL('ru'));
    }

    public function testNamespaceValue()
    {
        $xml = XmlParser::getSimpleXml(file_get_contents('tests/fixtures/rue89.xml'));
        self::assertNotFalse($xml);
        $namespaces = $xml->getNamespaces(true);

        $parser = new Rss20('');
        self::assertEquals('Blandine Grosjean', XmlParser::getNamespaceValue($xml->channel->item[0], $namespaces, 'creator'));
        self::assertEquals('Pierre-Carl Langlais', XmlParser::getNamespaceValue($xml->channel->item[1], $namespaces, 'creator'));
    }

    public function testFeedsWithInvalidCharacters()
    {
        $parser = new Rss20(file_get_contents('tests/fixtures/lincoln_loop.xml'));
        $feed = $parser->execute();
        self::assertNotEmpty($feed->items);

        $parser = new Rss20(file_get_contents('tests/fixtures/next_inpact_full.xml'));
        $feed = $parser->execute();
        self::assertNotEmpty($feed->items);
    }
}
