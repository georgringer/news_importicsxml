<?php

namespace PicoFeed\Scraper;

use PHPUnit_Framework_TestCase;
use PicoFeed\Config\Config;

class RuleLoaderTest extends PHPUnit_Framework_TestCase
{
    public function testGetRulesFolders()
    {
        // No custom path
        $loader = new RuleLoader(new Config());
        $dirs = $loader->getRulesFolders();
        self::assertNotEmpty($dirs);
        self::assertCount(1, $dirs);
        self::assertTrue(strpos($dirs[0], '/../Rules') !== false);

        // Custom path
        $config = new Config();
        $config->setGrabberRulesFolder('/foobar/rules');

        $loader = new RuleLoader($config);

        $dirs = $loader->getRulesFolders();

        self::assertNotEmpty($dirs);
        self::assertCount(2, $dirs);
        self::assertTrue(strpos($dirs[0], '/../Rules') !== false);
        self::assertEquals('/foobar/rules', $dirs[1]);

        // No custom path with empty config object
        $loader = new RuleLoader(new Config());

        $dirs = $loader->getRulesFolders();

        self::assertNotEmpty($dirs);
        self::assertCount(1, $dirs);
        self::assertTrue(strpos($dirs[0], '/../Rules') !== false);
    }

    public function testLoadRuleFile()
    {
        $loader = new RuleLoader(new Config());
        $dirs = $loader->getRulesFolders();

        self::assertEmpty($loader->loadRuleFile($dirs[0], ['test']));
        self::assertNotEmpty($loader->loadRuleFile($dirs[0], ['test', 'xkcd.com']));
    }

    public function testGetRulesFileList()
    {
        $loader = new RuleLoader(new Config());
        self::assertEquals(
            ['www.google.ca', 'google.ca', '.google.ca', 'www'],
            $loader->getRulesFileList('www.google.ca')
        );

        $loader = new RuleLoader(new Config());
        self::assertEquals(
            ['google.ca', '.google.ca', 'google'],
            $loader->getRulesFileList('google.ca')
        );

        $loader = new RuleLoader(new Config());
        self::assertEquals(
            ['a.b.c.d', 'b.c.d', '.b.c.d', 'a'],
            $loader->getRulesFileList('a.b.c.d')
        );

        $loader = new RuleLoader(new Config());
        self::assertEquals(
            ['localhost'],
            $loader->getRulesFileList('localhost')
        );
    }

    public function testGetRules()
    {
        $loader = new RuleLoader(new Config());
        self::assertNotEmpty($loader->getRules('http://www.egscomics.com/index.php?id=1690'));

        $loader = new RuleLoader(new Config());
        self::assertEmpty($loader->getRules('http://localhost/foobar'));
    }
}
