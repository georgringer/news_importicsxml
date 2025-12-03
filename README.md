# TYPO3 CMS Extension `news_importicsxml`

[![core 13](https://github.com/georgringer/news_importicsxml/actions/workflows/core13.yml/badge.svg)](https://github.com/georgringer/news_importicsxml/actions/workflows/core13.yml)
[![core 14](https://github.com/georgringer/news_importicsxml/actions/workflows/core14.yml/badge.svg)](https://github.com/georgringer/news_importicsxml/actions/workflows/core14.yml)

This extension provides an import interface for `xml` and `ics` files which can either be located on the same server or reached via URL.
The import is done by the scheduler.

**Requirements**

- TYPO3 CMS 13.4 LTS or 14 LTS
- EXT:news 13 / 14

**Sponsors**

- Webconsulting https://webconsulting.at/
- TUM (Technical University of Munich) https://www.tum.de/
- Hochschule Darmstadt - University of Applied Sciences https://www.h-da.de/

## Screenshots


**Screenshot #1:** Creation of an import task

![screenshot-task.png](Resources/Public/Documentation/screenshot-task.png)

**Screenshot #2:** Metadata of an imported ICS item

![screenshot-import-ics.png](Resources/Public/Documentation/screenshot-import-ics.png)

## Installation

**Installation using Composer**

The recommended way to install the extension is by using composer. In your composer based TYPO3 project root, just do `composer require georgringer/news-importicsxml`.

**Installation as extension from TYPO3 Extension Repository (TER)**

Download and install the extension with the extension manager module.

## Configuration

After installing the extension, switch to the module **scheduler** and create a new task **Import news**.
These additional fields are available:

- Format: Select either *XML* or *ICS* to import an ICS file or an XML file.
- Path: Define a local path like `fileadmin/data.xml` or any URL like `https://typo3.org/xml-feeds/rss.xml`.
- Page ID: Define a page id where the new records will be saved.

### Category mapping

A category mapping can be used to add categories to the news records based on categories found in the feed.

A typical feed item can look like this:

```xml
	<item>
		<title>A possible title</title>
		<link>http://www.examle.com/feed.xml</link>
		<description>Lorem ipsum</description>
		<content:encoded><![CDATA[A lot of text]]></content:encoded>
		<category>Sports</category>
		<category>Tech</category>
		<category>Information</category>
		<pubDate>Tue, 02 Jun 2015 16:54:00 +0200</pubDate>
	</item>
```

An ICS entry with a category can look like this:

```
	BEGIN:VCALENDAR
	PRODID:QIS-LSF HIS eG
	VERSION:2.0
	BEGIN:VTIMEZONE
	TZID:Europe/Berlin
	X-LIC-LOCATION:Europe/Berlin
	BEGIN:DAYLIGHT
	TZOFFSETFROM:+0100
	TZOFFSETTO:+0200
	TZNAME:CEST
	DTSTART:19700329T020000
	RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=3
	END:DAYLIGHT
	BEGIN:STANDARD
	TZOFFSETFROM:+0200
	TZOFFSETTO:+0100
	TZNAME:CET
	DTSTART:19701025T030000
	RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10
	END:STANDARD
	END:VTIMEZONE
	METHOD:PUBLISH
	BEGIN:VEVENT
	DTSTART;TZID=Europe/Berlin:20171016T141500
	DTEND;TZID=Europe/Berlin:20171016T154500
	RRULE:FREQ=WEEKLY;UNTIL=20171211T235900Z;INTERVAL=2;BYDAY=MO
	EXDATE;TZID=Europe/Berlin:
	LOCATION:C12 - C12 / 024
	DTSTAMP:20180216T113640Z
	UID:52965155936
	DESCRIPTION:
	SUMMARY:MK.1890a - Finite Berechnungsverfahren  Praktikum (A-Zug) (Eufinger)
	CATEGORIES:Praktikum
	END:VEVENT
	END:VCALENDAR
```

A possible category mapping would look like this:

```
	5:Sports
	17:Tech
	17:Information
```
As a result, the imported news record will belong to the categories of the IDs *5 & 17*.

### Email notification
Add an email address which will get notified after each run.

**Important**: This feature is not yet implemented!

### Persist article with type external page
If set, the news article is saved with the type "External Url".

## Further information

### Debugging
This extension uses the logging API of TYPO3 CMS. You can find some basic information in the log files (default `typo3temp/var/logs/typo3_****.log`).

### Extending the import

If you need to import additional data from a feed or ics item or you need to modify the data, use the Event Dispatching of the extension *news*.
Most likely, you will need the NewsImportPostHydrateEvent EventListener.


- Check the documentation of the news extension for a full list of available events: `https://docs.typo3.org/p/georgringer/news/main/en-us/Reference/Events/Index.html`
- An example on how to use the NewsImportPostHydrateEvent EventListener is available in `ext:eventnews`



