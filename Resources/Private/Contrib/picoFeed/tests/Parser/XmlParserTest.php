<?php

namespace PicoFeed\Parser;

use DOMDocument;

use PHPUnit_Framework_TestCase;

class XmlParserTest extends PHPUnit_Framework_TestCase
{
    public function testEmpty()
    {
        self::assertFalse(XmlParser::getDomDocument(''));
        self::assertFalse(XmlParser::getSimpleXml(''));
        self::assertNotFalse(XmlParser::getHtmlDocument(''));
    }

    public function testGetEncodingFromMetaTag()
    {
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1"/>'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1" />'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta http-equiv=\'Content-Type\' content=\'text/html;charset=iso-8859-1\'/>'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta http-equiv=\'Content-Type\' content=\'text/html;charset=iso-8859-1\' />'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta http-equiv=Content-Type content=text/html;charset=iso-8859-1/>'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta http-equiv=Content-Type content=text/html;charset=iso-8859-1 />'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta http-equiv="Content-Type" content="text/html;charset=ISO-8859-1">'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1" >'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta http-equiv=\'Content-Type\' content=\'text/html;charset=iso-8859-1\'>'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta http-equiv=\'Content-Type\' content=\'text/html;charset=iso-8859-1\' >'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta http-equiv=Content-Type content=text/html;charset=iso-8859-1>'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta http-equiv=Content-Type content=text/html;charset=iso-8859-1 >'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta http-equiv="Content-Type" content="text/html;charset=\'iso-8859-1\'">'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta http-equiv="Content-Type" content="\'text/html;charset=iso-8859-1\'">'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta http-equiv="Content-Type" content="\'text/html\';charset=\'iso-8859-1\'">'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta http-equiv=\'Content-Type\' content=\'text/html;charset="iso-8859-1"\'>'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta http-equiv=\'Content-Type\' content=\'"text/html;charset=iso-8859-1"\'>'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta http-equiv=\'Content-Type\' content=\'"text/html";charset="iso-8859-1"\'>'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta http-equiv="Content-Type" content="text/html;;;charset=iso-8859-1">'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta http-equiv="Content-Type" content="text/html;;;charset=\'iso-8859-1\'">'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta http-equiv="Content-Type" content="\'text/html;;;charset=iso-8859-1\'">'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta http-equiv="Content-Type" content="\'text/html\';;;charset=\'iso-8859-1\'">'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta http-equiv=\'Content-Type\' content=\'text/html;;;charset=iso-8859-1\'>'));
        self::assertEquals('windows-1251', XmlParser::getEncodingFromMetaTag('<meta http-equiv=\'Content-Type\' content=\'text/html;;;charset="windows-1251"\'>'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta http-equiv=\'Content-Type\' content=\'"text/html;;;charset=iso-8859-1"\'>'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta http-equiv=\'Content-Type\' content=\'"text/html";;;charset="iso-8859-1"\'>'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta  http-equiv  =  Content-Type  content  =  text/html;charset=iso-8859-1  >'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta  content  =  text/html;charset=iso-8859-1  http-equiv  =  Content-Type  >'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta  http-equiv  =  Content-Type  content  =  text/html  ;  charset  =  iso-8859-1  >'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta  content  =  text/html  ;  charset  =  iso-8859-1  http-equiv  =  Content-Type  >'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta  http-equiv  =  Content-Type  content  =  text/html  ;;;  charset  =  iso-8859-1  >'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta  content  =  text/html  ;;;  charset  =  iso-8859-1  http-equiv  =  Content-Type  >'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta  http-equiv  =  Content-Type  content  =  text/html  ;  ;  ;  charset  =  iso-8859-1  >'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta  content  =  text/html  ;  ;  ;  charset  =  iso-8859-1  http-equiv  =  Content-Type  >'));
        self::assertEquals('utf-8', XmlParser::getEncodingFromMetaTag('<meta charset="uTf-8"/>'));
        self::assertEquals('utf-8', XmlParser::getEncodingFromMetaTag('<meta charset="utf-8" />'));
        self::assertEquals('utf-8', XmlParser::getEncodingFromMetaTag('<meta charset=\'Utf-8\'/>'));
        self::assertEquals('utf-8', XmlParser::getEncodingFromMetaTag('<meta charset=\'utf-8\' />'));
        self::assertEquals('utf-8', XmlParser::getEncodingFromMetaTag('<meta charset=utf-8/>'));
        self::assertEquals('utf-8', XmlParser::getEncodingFromMetaTag('<meta charset=utf-8 />'));
        self::assertEquals('utf-8', XmlParser::getEncodingFromMetaTag('<meta charset="utf-8">'));
        self::assertEquals('utf-8', XmlParser::getEncodingFromMetaTag('<meta charset="utf-8" >'));
        self::assertEquals('utf-8', XmlParser::getEncodingFromMetaTag('<meta charset=\'utf-8\'>'));
        self::assertEquals('utf-8', XmlParser::getEncodingFromMetaTag('<meta charset=\'utf-8\' >'));
        self::assertEquals('utf-8', XmlParser::getEncodingFromMetaTag('<meta charset=utf-8>'));
        self::assertEquals('utf-8', XmlParser::getEncodingFromMetaTag('<meta charset=utf-8 >'));
        self::assertEquals('utf-8', XmlParser::getEncodingFromMetaTag('<meta  charset  =  "  utf-8  "  >'));
        self::assertEquals('utf-8', XmlParser::getEncodingFromMetaTag('<meta  charset  =  \'  utf-8  \'  >'));
        self::assertEquals('utf-8', XmlParser::getEncodingFromMetaTag('<meta  charset  =  "  utf-8  \'  >'));
        self::assertEquals('utf-8', XmlParser::getEncodingFromMetaTag('<meta  charset  =  \'  utf-8  "  >'));
        self::assertEquals('utf-8', XmlParser::getEncodingFromMetaTag('<meta  charset  =  "  utf-8     >'));
        self::assertEquals('utf-8', XmlParser::getEncodingFromMetaTag('<meta  charset  =  \'  utf-8     >'));
        self::assertEquals('utf-8', XmlParser::getEncodingFromMetaTag('<meta  charset  =     utf-8  \'  >'));
        self::assertEquals('utf-8', XmlParser::getEncodingFromMetaTag('<meta  charset  =     utf-8  "  >'));
        self::assertEquals('utf-8', XmlParser::getEncodingFromMetaTag('<meta  charset  =     utf-8     >'));
        self::assertEquals('utf-8', XmlParser::getEncodingFromMetaTag('<meta  charset  =     utf-8    />'));
        self::assertEquals('utf-8', XmlParser::getEncodingFromMetaTag('<meta name="title" value="charset=utf-8 — is it really useful (yep)?">'));
        self::assertEquals('utf-8', XmlParser::getEncodingFromMetaTag('<meta value="charset=utf-8 — is it really useful (yep)?" name="title">'));
        self::assertEquals('utf-8', XmlParser::getEncodingFromMetaTag('<meta name="title" content="charset=utf-8 — is it really useful (yep)?">'));
        self::assertEquals('utf-8', XmlParser::getEncodingFromMetaTag('<meta name="charset=utf-8" content="charset=utf-8 — is it really useful (yep)?">'));
        self::assertEquals('utf-8', XmlParser::getEncodingFromMetaTag('<meta content="charset=utf-8 — is it really useful (nope, not here, but gotta admit pretty robust otherwise)?" name="title">'));
        self::assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<meta http-equiv="Content-Type" content="text/html;charset=iSo-8859-1"/><meta charset="invalid" />'));
    }

    public function testGetEncodingFromXmlTag()
    {
        self::assertEquals('utf-8', XmlParser::getEncodingFromXmlTag("<?xml version='1.0' encoding='UTF-8'?><?xml-stylesheet"));
        self::assertEquals('utf-8', XmlParser::getEncodingFromXmlTag('<?xml version="1.0" encoding="UTF-8"?><feed xml:'));
        self::assertEquals('windows-1251', XmlParser::getEncodingFromXmlTag('<?xml version="1.0" encoding="Windows-1251"?><rss version="2.0">'));
        self::assertEquals('', XmlParser::getEncodingFromXmlTag("<?xml version='1.0'?><?xml-stylesheet"));
    }

    public function testScanForXEE()
    {
        $xml = <<<XML
<?xml version="1.0"?>
<!DOCTYPE results [<!ENTITY harmless "completely harmless">]>
<results>
    <result>This result is &harmless;</result>
</results>
XML;

        self::assertFalse(XmlParser::getDomDocument($xml));
    }

    public function testScanForXXE()
    {
        $file = tempnam(sys_get_temp_dir(), 'PicoFeed_XmlParser');
        file_put_contents($file, 'Content Injection');

        $xml = <<<XML
<?xml version="1.0"?>
<!DOCTYPE root
[
<!ENTITY foo SYSTEM "file://$file">
]>
<results>
    <result>&foo;</result>
</results>
XML;

        self::assertFalse(XmlParser::getDomDocument($xml));
        unlink($file);
    }

    public function testScanSimpleXML()
    {
        return <<<XML
<?xml version="1.0"?>
<results>
    <result>test</result>
</results>
XML;
        $result = XmlParser::getSimpleXml($xml);
        self::assertTrue($result instanceof SimpleXMLElement);
        self::assertEquals($result->result, 'test');
    }

    public function testScanDomDocument()
    {
        return <<<XML
<?xml version="1.0"?>
<results>
    <result>test</result>
</results>
XML;
        $result = XmlParser::getDomDocument($xml);
        self::assertTrue($result instanceof DOMDocument);
        $node = $result->getElementsByTagName('result')->item(0);
        self::assertEquals($node->nodeValue, 'test');
    }

    public function testScanInvalidXml()
    {
        $xml = <<<XML
<foo>test</bar>
XML;

        self::assertFalse(XmlParser::getDomDocument($xml));
        self::assertFalse(XmlParser::getSimpleXml($xml));
    }

    public function testScanXmlWithDTD()
    {
        $xml = <<<XML
<?xml version="1.0"?>
<!DOCTYPE results [
<!ELEMENT results (result+)>
<!ELEMENT result (#PCDATA)>
]>
<results>
    <result>test</result>
</results>
XML;

        $result = XmlParser::getDomDocument($xml);
        self::assertTrue($result instanceof DOMDocument);
        self::assertTrue($result->validate());
    }
}
