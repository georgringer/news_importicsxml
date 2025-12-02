<?php

namespace PicoFeed\Filter;

use PHPUnit_Framework_TestCase;

use PicoFeed\Client\Url;
use PicoFeed\Config\Config;

class AttributeFilterTest extends PHPUnit_Framework_TestCase
{
    public function testFilterEmptyAttribute()
    {
        $filter = new Attribute(new Url('http://google.com'));

        self::assertTrue($filter->filterEmptyAttribute('abbr', 'title', 'test'));
        self::assertFalse($filter->filterEmptyAttribute('abbr', 'title', ''));
        self::assertEquals(['title' => 'test'], $filter->filter('abbr', ['title' => 'test']));
        self::assertEquals([], $filter->filter('abbr', ['title' => '']));
    }

    public function testFilterAllowedAttribute()
    {
        $filter = new Attribute(new Url('http://google.com'));

        self::assertTrue($filter->filterAllowedAttribute('abbr', 'title', 'test'));
        self::assertFalse($filter->filterAllowedAttribute('script', 'type', 'text/javascript'));

        self::assertEquals([], $filter->filter('script', ['type' => 'text/javascript']));
        self::assertEquals([], $filter->filter('a', ['onclick' => 'javascript']));
        self::assertEquals(['href' => 'http://google.com/'], $filter->filter('a', ['href' => 'http://google.com']));
    }

    public function testFilterIntegerAttribute()
    {
        $filter = new Attribute(new Url('http://google.com'));

        self::assertTrue($filter->filterIntegerAttribute('abbr', 'title', 'test'));
        self::assertTrue($filter->filterIntegerAttribute('iframe', 'width', '0'));
        self::assertTrue($filter->filterIntegerAttribute('iframe', 'width', '450'));
        self::assertFalse($filter->filterIntegerAttribute('iframe', 'width', 'test'));

        self::assertEquals(['width' => '10', 'src' => 'https://www.youtube.com/test'], $filter->filter('iframe', ['width' => '10', 'src' => 'http://www.youtube.com/test']));
        self::assertEquals(['src' => 'https://www.youtube.com/test'], $filter->filter('iframe', ['width' => 'test', 'src' => 'http://www.youtube.com/test']));
    }

    public function testRewriteProxyImageUrl()
    {
        $filter = new Attribute(new Url('http://www.la-grange.net'));
        $url = '/2014/08/03/4668-noisettes';
        self::assertTrue($filter->rewriteImageProxyUrl('a', 'href', $url));
        self::assertEquals('/2014/08/03/4668-noisettes', $url);

        $filter = new Attribute(new Url('http://www.la-grange.net'));
        $url = '/2014/08/03/4668-noisettes';
        self::assertTrue($filter->rewriteImageProxyUrl('img', 'alt', $url));
        self::assertEquals('/2014/08/03/4668-noisettes', $url);

        $filter = new Attribute(new Url('http://www.la-grange.net'));
        $url = '/2014/08/03/4668-noisettes';
        self::assertTrue($filter->rewriteImageProxyUrl('img', 'src', $url));
        self::assertEquals('/2014/08/03/4668-noisettes', $url);

        $filter = new Attribute(new Url('http://www.la-grange.net'));
        $filter->setImageProxyUrl('https://myproxy/?u=%s');
        $url = 'http://example.net/image.png';
        self::assertTrue($filter->rewriteImageProxyUrl('img', 'src', $url));
        self::assertEquals('https://myproxy/?u=' . rawurlencode('http://example.net/image.png'), $url);

        $filter = new Attribute(new Url('http://www.la-grange.net'));

        $filter->setImageProxyCallback(function ($image_url) {
            $key = hash_hmac('sha1', $image_url, 'secret');
            return 'https://mypublicproxy/' . $key . '/' . rawurlencode($image_url);
        });

        $url = 'http://example.net/image.png';
        self::assertTrue($filter->rewriteImageProxyUrl('img', 'src', $url));
        self::assertEquals('https://mypublicproxy/d9701029b054f6e178ef88fcd3c789365e52a26d/' . rawurlencode('http://example.net/image.png'), $url);
    }

    public function testRewriteAbsoluteUrl()
    {
        $filter = new Attribute(new Url('http://www.la-grange.net'));
        $url = '/2014/08/03/4668-noisettes';
        self::assertTrue($filter->rewriteAbsoluteUrl('a', 'href', $url));
        self::assertEquals('http://www.la-grange.net/2014/08/03/4668-noisettes', $url);

        $filter = new Attribute(new Url('http://google.com'));

        $url = 'test';
        self::assertTrue($filter->rewriteAbsoluteUrl('a', 'href', $url));
        self::assertEquals('http://google.com/test', $url);

        $url = 'http://127.0.0.1:8000/test';
        self::assertTrue($filter->rewriteAbsoluteUrl('img', 'src', $url));
        self::assertEquals('http://127.0.0.1:8000/test', $url);

        $url = '//example.com';
        self::assertTrue($filter->rewriteAbsoluteUrl('a', 'href', $url));
        self::assertEquals('http://example.com/', $url);

        $filter = new Attribute(new Url('https://google.com'));
        $url = '//example.com/?youpi';
        self::assertTrue($filter->rewriteAbsoluteUrl('a', 'href', $url));
        self::assertEquals('https://example.com/?youpi', $url);

        $filter = new Attribute(new Url('https://127.0.0.1:8000/here/'));
        $url = 'image.png?v=2';
        self::assertTrue($filter->rewriteAbsoluteUrl('a', 'href', $url));
        self::assertEquals('https://127.0.0.1:8000/here/image.png?v=2', $url);

        $filter = new Attribute(new Url('https://truc/'));
        self::assertEquals(['src' => 'https://www.youtube.com/test'], $filter->filter('iframe', ['width' => 'test', 'src' => '//www.youtube.com/test']));

        $filter = new Attribute(new Url('http://truc/'));
        self::assertEquals(['href' => 'http://google.fr/'], $filter->filter('a', ['href' => '//google.fr']));
    }

    public function testFilterIframeAttribute()
    {
        $filter = new Attribute(new Url('http://google.com'));

        self::assertTrue($filter->filterIframeAttribute('iframe', 'src', 'http://www.youtube.com/test'));
        self::assertTrue($filter->filterIframeAttribute('iframe', 'src', 'https://www.youtube.com/test'));
        self::assertFalse($filter->filterIframeAttribute('iframe', 'src', '//www.youtube.com/test'));
        self::assertFalse($filter->filterIframeAttribute('iframe', 'src', '//www.bidule.com/test'));

        self::assertEquals(['src' => 'https://www.youtube.com/test'], $filter->filter('iframe', ['src' => '//www.youtube.com/test']));
    }

    public function testRemoveYouTubeAutoplay()
    {
        $filter = new Attribute(new Url('http://google.com'));
        $urls = [
            'https://www.youtube.com/something/?autoplay=1' => 'https://www.youtube.com/something/?autoplay=0',
            'https://www.youtube.com/something/?test=s&autoplay=1&a=2' => 'https://www.youtube.com/something/?test=s&autoplay=0&a=2',
            'https://www.youtube.com/something/?test=s' => 'https://www.youtube.com/something/?test=s',
            'https://youtube.com/something/?autoplay=1' => 'https://youtube.com/something/?autoplay=0',
            'https://youtube.com/something/?test=s&autoplay=1&a=2' => 'https://youtube.com/something/?test=s&autoplay=0&a=2',
            'https://youtube.com/something/?test=s' => 'https://youtube.com/something/?test=s',
        ];

        foreach ($urls as $before => $after) {
            $filter->removeYouTubeAutoplay('iframe', 'src', $before);
            self::assertEquals($after, $before);
        }
    }

    public function testFilterBlacklistAttribute()
    {
        $filter = new Attribute(new Url('http://google.com'));

        self::assertTrue($filter->filterBlacklistResourceAttribute('a', 'href', 'http://google.fr/'));
        self::assertFalse($filter->filterBlacklistResourceAttribute('a', 'href', 'http://res3.feedsportal.com/truc'));

        self::assertEquals(['href' => 'http://google.fr/'], $filter->filter('a', ['href' => 'http://google.fr/']));
        self::assertEquals([], $filter->filter('a', ['href' => 'http://res3.feedsportal.com/']));
    }

    public function testFilterProtocolAttribute()
    {
        $filter = new Attribute(new Url('http://google.com'));

        self::assertTrue($filter->filterProtocolUrlAttribute('a', 'href', 'http://google.fr/'));
        self::assertFalse($filter->filterProtocolUrlAttribute('a', 'href', 'bla://google.fr/'));
        self::assertFalse($filter->filterProtocolUrlAttribute('a', 'href', 'javascript:alert("test")'));

        self::assertEquals(['href' => 'http://google.fr/'], $filter->filter('a', ['href' => 'http://google.fr/']));
        self::assertEquals([], $filter->filter('a', ['href' => 'bla://google.fr/']));
    }

    public function testRequiredAttribute()
    {
        $filter = new Attribute(new Url('http://google.com'));

        self::assertTrue($filter->hasRequiredAttributes('a', ['href' => 'bla']));
        self::assertTrue($filter->hasRequiredAttributes('img', ['src' => 'bla']));
        self::assertTrue($filter->hasRequiredAttributes('source', ['src' => 'bla']));
        self::assertTrue($filter->hasRequiredAttributes('audio', ['src' => 'bla']));
        self::assertTrue($filter->hasRequiredAttributes('iframe', ['src' => 'bla']));
        self::assertTrue($filter->hasRequiredAttributes('p', ['class' => 'bla']));
        self::assertFalse($filter->hasRequiredAttributes('a', ['title' => 'bla']));
    }

    public function testHtml()
    {
        $filter = new Attribute(new Url('http://google.com'));

        self::assertEquals('title="A &amp; B"', $filter->toHtml(['title' => 'A & B']));
        self::assertEquals('title="&quot;a&quot;"', $filter->toHtml(['title' => '"a"']));
        self::assertEquals('title="ç" alt="b"', $filter->toHtml(['title' => 'ç', 'alt' => 'b']));
    }

    public function testNoImageProxySet()
    {
        $f = Filter::html('<p>Image <img src="/image.png" alt="My Image"/></p>', 'http://foo');

        self::assertEquals(
            '<p>Image <img src="http://foo/image.png" alt="My Image"/></p>',
            $f->execute()
        );
    }

    public function testImageProxyWithHTTPLink()
    {
        $config = new Config();
        $config->setFilterImageProxyUrl('http://myproxy/?url=%s');

        $f = Filter::html('<p>Image <img src="http://localhost/image.png" alt="My Image"/></p>', 'http://foo');
        $f->setConfig($config);

        self::assertEquals(
            '<p>Image <img src="http://myproxy/?url=' . rawurlencode('http://localhost/image.png') . '" alt="My Image"/></p>',
            $f->execute()
        );
    }

    public function testImageProxyWithHTTPSLink()
    {
        $config = new Config();
        $config->setFilterImageProxyUrl('http://myproxy/?url=%s');

        $f = Filter::html('<p>Image <img src="https://localhost/image.png" alt="My Image"/></p>', 'http://foo');
        $f->setConfig($config);

        self::assertEquals(
            '<p>Image <img src="http://myproxy/?url=' . rawurlencode('https://localhost/image.png') . '" alt="My Image"/></p>',
            $f->execute()
        );
    }

    public function testImageProxyLimitedToUnknownProtocol()
    {
        $config = new Config();
        $config->setFilterImageProxyUrl('http://myproxy/?url=%s');
        $config->setFilterImageProxyProtocol('tripleX');

        $f = Filter::html('<p>Image <img src="http://localhost/image.png" alt="My Image"/></p>', 'http://foo');
        $f->setConfig($config);

        self::assertEquals(
            '<p>Image <img src="http://localhost/image.png" alt="My Image"/></p>',
            $f->execute()
        );
    }

    public function testImageProxyLimitedToHTTPwithHTTPLink()
    {
        $config = new Config();
        $config->setFilterImageProxyUrl('http://myproxy/?url=%s');
        $config->setFilterImageProxyProtocol('http');

        $f = Filter::html('<p>Image <img src="http://localhost/image.png" alt="My Image"/></p>', 'http://foo');
        $f->setConfig($config);

        self::assertEquals(
            '<p>Image <img src="http://myproxy/?url=' . rawurlencode('http://localhost/image.png') . '" alt="My Image"/></p>',
            $f->execute()
        );
    }

    public function testImageProxyLimitedToHTTPwithHTTPSLink()
    {
        $config = new Config();
        $config->setFilterImageProxyUrl('http://myproxy/?url=%s');
        $config->setFilterImageProxyProtocol('http');

        $f = Filter::html('<p>Image <img src="https://localhost/image.png" alt="My Image"/></p>', 'http://foo');
        $f->setConfig($config);

        self::assertEquals(
            '<p>Image <img src="https://localhost/image.png" alt="My Image"/></p>',
            $f->execute()
        );
    }

    public function testImageProxyLimitedToHTTPSwithHTTPLink()
    {
        $config = new Config();
        $config->setFilterImageProxyUrl('http://myproxy/?url=%s');
        $config->setFilterImageProxyProtocol('https');

        $f = Filter::html('<p>Image <img src="http://localhost/image.png" alt="My Image"/></p>', 'http://foo');
        $f->setConfig($config);

        self::assertEquals(
            '<p>Image <img src="http://localhost/image.png" alt="My Image"/></p>',
            $f->execute()
        );
    }

    public function testImageProxyLimitedToHTTPSwithHTTPSLink()
    {
        $config = new Config();
        $config->setFilterImageProxyUrl('http://myproxy/?url=%s');
        $config->setFilterImageProxyProtocol('https');

        $f = Filter::html('<p>Image <img src="https://localhost/image.png" alt="My Image"/></p>', 'http://foo');
        $f->setConfig($config);

        self::assertEquals(
            '<p>Image <img src="http://myproxy/?url=' . rawurlencode('https://localhost/image.png') . '" alt="My Image"/></p>',
            $f->execute()
        );
    }

    public function testsetFilterImageProxyCallback()
    {
        $config = new Config();
        $config->setFilterImageProxyCallback(function ($image_url) {
            $key = hash_hmac('sha1', $image_url, 'secret');
            return 'https://mypublicproxy/' . $key . '/' . rawurlencode($image_url);
        });

        $f = Filter::html('<p>Image <img src="/image.png" alt="My Image"/></p>', 'http://foo');
        $f->setConfig($config);

        self::assertEquals(
            '<p>Image <img src="https://mypublicproxy/4924964043f3119b3cf2b07b1922d491bcc20092/' . rawurlencode('http://foo/image.png') . '" alt="My Image"/></p>',
            $f->execute()
        );
    }
}
