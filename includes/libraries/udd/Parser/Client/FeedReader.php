<?php
/**
 * Device Detector - The Universal Device Detection library for parsing User Agents
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/lgpl.html LGPL v3 or later
 */
namespace UDD\Parser\Client;

/**
 * Class FeedReader
 *
 * Client parser for feed reader detection
 *
 * @package UDD\Parser\Client
 */
class FeedReader extends ClientParserAbstract
{
    protected $fixtureFile = 'regexes/client/feed_readers.yml';
    protected $parserName = 'feed reader';
}
