<?php
/**
 * Device Detector - The Universal Device Detection library for parsing User Agents
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/lgpl.html LGPL v3 or later
 */
namespace UDD\Parser\Device;

/**
 * Class Console
 *
 * Device parser for console detection
 *
 * @package UDD\Parser\Device
 */
class Console extends DeviceParserAbstract
{
    protected $fixtureFile = 'regexes/device/consoles.yml';
    protected $parserName  = 'console';

    public function parse()
    {
        if (!$this->preMatchOverall()) {
            return false;
        }

        return parent::parse();
    }
}
