<?php
/**
 * Device Detector - The Universal Device Detection library for parsing User Agents
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/lgpl.html LGPL v3 or later
 */
namespace UDD\Parser\Client;

/**
 * Class MediaPlayer
 *
 * Client parser for mediaplayer detection
 *
 * @package UDD\Parser\Client
 */
class MediaPlayer extends ClientParserAbstract
{
    protected $fixtureFile = 'regexes/client/mediaplayers.yml';
    protected $parserName = 'mediaplayer';
}
