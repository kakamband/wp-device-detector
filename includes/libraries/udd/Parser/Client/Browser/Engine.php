<?php
/**
 * Device Detector - The Universal Device Detection library for parsing User Agents
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/lgpl.html LGPL v3 or later
 */
namespace UDD\Parser\Client\Browser;

use UDD\Parser\Client\ClientParserAbstract;

/**
 * Class Engine
 *
 * Client parser for browser engine detection
 *
 * @package UDD\Parser\Client\Browser
 */
class Engine extends ClientParserAbstract
{
    protected $fixtureFile = 'regexes/client/browser_engine.yml';
    protected $parserName = 'browserengine';

    /**
     * Known browser engines mapped to their internal short codes
     *
     * @var array
     */
    protected static $availableEngines = array(
        'WebKit',
        'Blink',
        'Trident',
        'Text-based',
        'Dillo',
        'iCab',
        'Elektra',
        'Presto',
        'Gecko',
        'KHTML',
        'NetFront',
        'Edge',
        'NetSurf',
        'Servo',
        'Goanna'
    );

    /**
     * Returns list of all available browser engines
     * @return array
     */
    public static function getAvailableEngines()
    {
        return self::$availableEngines;
    }

    public function parse()
    {
        $matches = false;
        foreach ($this->getRegexes() as $regex) {
            $matches = $this->matchUserAgent($regex['regex']);
            if ($matches) {
                break;
            }
        }

        if (!$matches) {
            return '';
        }

        $name  = $this->buildByMatch($regex['name'], $matches);

        foreach (self::getAvailableEngines() as $engineName) {
            if (strtolower($name) == strtolower($engineName)) {
                return $engineName;
            }
        }

        // This Exception should never be thrown. If so a defined browser name is missing in $availableEngines
        throw new \Exception('Detected browser engine was not found in $availableEngines. Tried to parse user agent: '.$this->userAgent); // @codeCoverageIgnore
    }
}
