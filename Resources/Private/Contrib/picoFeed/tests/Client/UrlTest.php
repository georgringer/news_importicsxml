<?php

namespace PicoFeed\Client;

use PHPUnit_Framework_TestCase;

class UrlTest extends PHPUnit_Framework_TestCase
{
    public function testHasScheme()
    {
        $url = new Url('http://www.google.fr/');
        self::assertTrue($url->hasScheme());

        $url = new Url('//www.google.fr/');
        self::assertFalse($url->hasScheme());

        $url = new Url('/path');
        self::assertFalse($url->hasScheme());

        $url = new Url('anything');
        self::assertFalse($url->hasScheme());
    }

    public function testHasPort()
    {
        $url = new Url('http://127.0.0.1:8000/');
        self::assertTrue($url->hasPort());

        $url = new Url('http://127.0.0.1/');
        self::assertFalse($url->hasPort());
    }

    public function testIsProtocolRelative()
    {
        $url = new Url('http://www.google.fr/');
        self::assertFalse($url->isProtocolRelative());

        $url = new Url('//www.google.fr/');
        self::assertTrue($url->isProtocolRelative());

        $url = new Url('/path');
        self::assertFalse($url->isProtocolRelative());

        $url = new Url('anything');
        self::assertFalse($url->isProtocolRelative());
    }

    public function testBaseUrl()
    {
        $url = new Url('../bla');
        self::assertEquals('', $url->getBaseUrl());

        $url = new Url('github.com');
        self::assertEquals('', $url->getBaseUrl());

        $url = new Url('http://127.0.0.1:8000');
        self::assertEquals('http://127.0.0.1:8000', $url->getBaseUrl());

        $url = new Url('http://127.0.0.1:8000/test?123');
        self::assertEquals('http://127.0.0.1:8000', $url->getBaseUrl());

        $url = new Url('http://localhost/test');
        self::assertEquals('http://localhost', $url->getBaseUrl());

        $url = new Url('https://localhost/test');
        self::assertEquals('https://localhost', $url->getBaseUrl());

        $url = new Url('//localhost/test?truc');
        self::assertEquals('http://localhost', $url->getBaseUrl());

        $url = new Url('//localhost/test?truc');
        self::assertEquals('http://localhost', $url->getBaseUrl());
    }

    public function testIsRelativeUrl()
    {
        $url = new Url('http://www.google.fr/');
        self::assertFalse($url->isRelativeUrl());

        $url = new Url('//www.google.fr/');
        self::assertFalse($url->isRelativeUrl());

        $url = new Url('/path');
        self::assertTrue($url->isRelativeUrl());

        $url = new Url('../../path');
        self::assertTrue($url->isRelativeUrl());

        $url = new Url('anything');
        self::assertTrue($url->isRelativeUrl());

        $url = new Url('/2014/08/03/4668-noisettes');
        self::assertTrue($url->isRelativeUrl());

        $url = new Url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUA
AAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO
9TXL0Y4OHwAAAABJRU5ErkJggg==');
        self::assertFalse($url->isRelativeUrl());
    }

    public function testGetFullPath()
    {
        $url = new Url('http://www.google.fr/');
        self::assertEquals('/', $url->getFullPath());

        $url = new Url('//www.google.fr/search');
        self::assertEquals('/search', $url->getFullPath());

        $url = new Url('/path');
        self::assertEquals('/path', $url->getFullPath());

        $url = new Url('/path#test');
        self::assertEquals('/path#test', $url->getFullPath());

        $url = new Url('anything');
        self::assertEquals('/anything', $url->getFullPath());

        $url = new Url('foo/bar');
        self::assertEquals('/foo/bar', $url->getFullPath());

        $url = new Url('index.php?foo=bar&test=1');
        self::assertEquals('/index.php?foo=bar&test=1', $url->getFullPath());
    }

    public function testAbsoluteUrl()
    {
        $url = new Url('http://google.fr/');
        self::assertEquals('http://google.fr/', $url->getAbsoluteUrl());

        $url = new Url('http://google.ca');
        self::assertEquals('http://google.ca/', $url->getAbsoluteUrl());

        $url = new Url('../bla');
        self::assertEquals('', $url->getAbsoluteUrl(''));

        $url = new Url('/2014/08/03/4668-noisettes');
        self::assertEquals('http://www.la-grange.net/2014/08/03/4668-noisettes', $url->getAbsoluteUrl('http://www.la-grange.net/'));

        $url = new Url('http://www.google.fr/../bla');
        self::assertEquals('http://www.google.fr/../bla', $url->getAbsoluteUrl('http://www.google.fr/'));

        $url = new Url('http://www.google.fr/');
        self::assertEquals('http://www.google.fr/', $url->getAbsoluteUrl('http://www.google.fr/'));

        $url = new Url('//www.google.fr/search');
        self::assertEquals('http://www.google.fr/search', $url->getAbsoluteUrl('//www.google.fr/'));

        $url = new Url('//www.google.fr/search');
        self::assertEquals('http://www.google.fr/search', $url->getAbsoluteUrl());

        $url = new Url('/path');
        self::assertEquals('http://www.google.fr/path', $url->getAbsoluteUrl('http://www.google.fr/'));

        $url = new Url('/path#test');
        self::assertEquals('http://www.google.fr/path#test', $url->getAbsoluteUrl('http://www.google.fr/'));

        $url = new Url('anything');
        self::assertEquals('http://www.google.fr/anything', $url->getAbsoluteUrl('http://www.google.fr/'));

        $url = new Url('index.php?foo=bar&test=1');
        self::assertEquals('http://www.google.fr/index.php?foo=bar&test=1', $url->getAbsoluteUrl('http://www.google.fr/'));

        $url = new Url('index.php?foo=bar&test=1');
        self::assertEquals('', $url->getAbsoluteUrl());

        $url = new Url('https://127.0.0.1:8000/here/test?v=3');
        self::assertEquals('https://127.0.0.1:8000/here/test?v=3', $url->getAbsoluteUrl());

        $url = new Url('http://www.lofibucket.com/articles/oscilloscope_quake.html');
        self::assertEquals('http://www.lofibucket.com/articles/oscilloscope_quake.html', $url->getAbsoluteUrl());

        $url = new Url('test?v=3');
        self::assertEquals('https://127.0.0.1:8000/here/test?v=3', $url->getAbsoluteUrl('https://127.0.0.1:8000/here/'));
    }

    public function testIsRelativePath()
    {
        $url = new Url('');
        self::assertTrue($url->isRelativePath());

        $url = new Url('http://google.fr');
        self::assertTrue($url->isRelativePath());

        $url = new Url('filename.json');
        self::assertTrue($url->isRelativePath());

        $url = new Url('folder/filename.json');
        self::assertTrue($url->isRelativePath());

        $url = new Url('/filename.json');
        self::assertFalse($url->isRelativePath());

        $url = new Url('/folder/filename.json');
        self::assertFalse($url->isRelativePath());
    }

    public function testGetBasePath()
    {
        $url = new Url('img/quakescope.jpg');
        self::assertEquals('/img/', $url->getBasePath());

        $url = new Url('http://foo/img/quakescope.jpg');
        self::assertEquals('/img/', $url->getBasePath());

        $url = new Url('http://foo/bar.html');
        self::assertEquals('/', $url->getBasePath());

        $url = new Url('http://foo/bar');
        self::assertEquals('/', $url->getBasePath());

        $url = new Url('http://foo/bar/');
        self::assertEquals('/bar/', $url->getBasePath());

        $url = new Url('http://website/subfolder/img/foo.png');
        self::assertEquals('/subfolder/img/', $url->getBasePath());
    }

    public function testResolve()
    {
        // relative link
        self::assertEquals(
            'http://miniflux.net/assets/img/favicon.png',
            Url::resolve('assets/img/favicon.png', 'http://miniflux.net')
        );

        // relative link + HTTPS
        self::assertEquals(
            'https://miniflux.net/assets/img/favicon.png',
            Url::resolve('assets/img/favicon.png', 'https://miniflux.net')
        );

        // absolute link
        self::assertEquals(
            'http://miniflux.net/assets/img/favicon.png',
            Url::resolve('/assets/img/favicon.png', 'http://miniflux.net')
        );

        // absolute link + HTTPS
        self::assertEquals(
            'https://miniflux.net/assets/img/favicon.png',
            Url::resolve('/assets/img/favicon.png', 'https://miniflux.net')
        );

        // Protocol relative link
        self::assertEquals(
            'http://google.com/assets/img/favicon.png',
            Url::resolve('//google.com/assets/img/favicon.png', 'http://miniflux.net')
        );

        // Protocol relative link + HTTPS
        self::assertEquals(
            'https://google.com/assets/img/favicon.png',
            Url::resolve('//google.com/assets/img/favicon.png', 'https://miniflux.net')
        );

        // URL same fqdn
        self::assertEquals(
            'http://miniflux.net/assets/img/favicon.png',
            Url::resolve('http://miniflux.net/assets/img/favicon.png', 'https://miniflux.net')
        );

        // URL different fqdn
        self::assertEquals(
            'https://www.google.com/assets/img/favicon.png',
            Url::resolve('https://www.google.com/assets/img/favicon.png', 'https://miniflux.net')
        );

        // HTTPS URL
        self::assertEquals(
            'https://miniflux.net/assets/img/favicon.png',
            Url::resolve('https://miniflux.net/assets/img/favicon.png', 'https://miniflux.net')
        );

        // empty string on missing website parameter
        self::assertEquals(
            '',
            Url::resolve('favicon.png', '')
        );

        // website only on missing icon parameter
        self::assertEquals(
            'https://miniflux.net/',
            Url::resolve('', 'https://miniflux.net')
        );

        // empty string on missing website and icon parameter
        self::assertEquals(
            '',
            Url::resolve('', '')
        );
    }
}
