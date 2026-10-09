<?php

declare(strict_types=1);

namespace GeorgRinger\NewsImporticsxml\Tests\Unit\Contrib;

/**
 * This file is part of the "news_importicsxml" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use ICal\ICal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class IcalParserTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../../../Resources/Private/Contrib/Event.php';
        require_once __DIR__ . '/../../../Resources/Private/Contrib/ICal.php';
    }

    #[Test]
    public function colonInValuesIsKept(): void
    {
        $event = $this->getFirstEvent(
            'SUMMARY:Vortrag: Neues aus TYPO3',
            'URL:https://example.org/a?b=c:d',
            'DESCRIPTION:Erste Zeile: mit Doppelpunkt'
        );

        self::assertSame('Vortrag: Neues aus TYPO3', $event->summary);
        self::assertSame('https://example.org/a?b=c:d', $event->url);
        self::assertSame('Erste Zeile: mit Doppelpunkt', $event->description);
    }

    #[Test]
    public function foldedDescriptionIsNotCutOff(): void
    {
        $event = $this->getFirstEvent(
            'DESCRIPTION:Das ist ein sehr langer Text, der ueber mehrere',
            '  gefaltete Zeilen geht und nicht abgeschnitten werden da',
            ' rf. Ende.'
        );

        self::assertSame(
            'Das ist ein sehr langer Text, der ueber mehrere gefaltete Zeilen geht und nicht abgeschnitten werden darf. Ende.',
            $event->description
        );
    }

    private function getFirstEvent(string ...$lines): object
    {
        $ics = implode("\r\n", array_merge([
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//news_importicsxml//EN',
            'BEGIN:VEVENT',
            'UID:1@example.org',
            'DTSTAMP:20260101T100000Z',
            'DTSTART:20260601T100000Z',
            'DTEND:20260601T110000Z',
        ], $lines, [
            'END:VEVENT',
            'END:VCALENDAR',
        ])) . "\r\n";

        $ical = new ICal();
        $ical->initString($ics);
        $events = $ical->events();
        self::assertCount(1, $events);

        return $events[0];
    }
}
