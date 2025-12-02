<?php

namespace PicoFeed\Parser;

use SimpleXMLElement;

/**
 * RSS 1.0 parser
 *
 * @author  Frederic Guillot
 */
class Rss10 extends Rss20
{
    /**
     * Get the path to the items XML tree
     *
     * @param  SimpleXMLElement   $xml   Feed xml
     * @return SimpleXMLElement
     */
    public function getItemsTree(SimpleXMLElement $xml)
    {
        return $xml->item;
    }

    /**
     * Find the feed date
     *
     * @param  SimpleXMLElement   $xml     Feed xml
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public function findFeedDate(SimpleXMLElement $xml, Feed $feed)
    {
        $feed->date = $this->date->getDateTime(XmlParser::getNamespaceValue($xml->channel, $this->namespaces, 'date'));
    }

    /**
     * Find the feed language
     *
     * @param  SimpleXMLElement   $xml     Feed xml
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public function findFeedLanguage(SimpleXMLElement $xml, Feed $feed)
    {
        $feed->language = XmlParser::getNamespaceValue($xml->channel, $this->namespaces, 'language');
    }

    /**
     * Genereate the item id
     *
     * @param  SimpleXMLElement   $entry   Feed item
     * @param  \PicoFeed\Parser\Item     $item    Item object
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public function findItemId(SimpleXMLElement $entry, Item $item, Feed $feed)
    {
        $item->id = $this->generateId(
            $item->getTitle(),
            $item->getUrl(),
            $item->getContent()
        );
    }

    /**
     * Find the item enclosure
     *
     * @param  SimpleXMLElement   $entry   Feed item
     * @param  \PicoFeed\Parser\Item     $item    Item object
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public function findItemEnclosure(SimpleXMLElement $entry, Item $item, Feed $feed) {}
}
