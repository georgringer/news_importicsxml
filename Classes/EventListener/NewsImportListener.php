<?php

declare(strict_types=1);

namespace GeorgRinger\NewsImporticsxml\EventListener;

/**
 * This file is part of the "news_importicsxml" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use GeorgRinger\News\Event\NewsImportPostHydrateEvent;

/**
 * Persist dynamic data of import
 */
class NewsImportListener
{
    public function __invoke(NewsImportPostHydrateEvent $event)
    {
        $importData = $event->getImportItem();
        if (is_array($importData['_dynamicData']['news_importicsxml'] ?? null)) {
            $event->getNews()->setNewsImportData(json_encode($importData['_dynamicData']['news_importicsxml']));
        }
    }
}
