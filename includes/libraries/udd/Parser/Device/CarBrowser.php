<?php
/**
 * Device Detector - The Universal Device Detection library for parsing User Agents
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/lgpl.html LGPL v3 or later
 */
namespace UDD\Parser\Device;

/**
 * Class CarBrowser
 *
 * Device parser for car browser detection
 *
 * @package UDD\Parser\Device
 */
class CarBrowser extends DeviceParserAbstract
{
    protected $fixtureFile = 'regexes/device/car_browsers.yml';
    protected $parserName  = 'car browser';

    public function parse()
    {
        if (!$this->preMatchOverall()) {
            return false;
        }

        return parent::parse();
    }
}
