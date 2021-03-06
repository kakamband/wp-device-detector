<?php
/**
 * Device Detector - The Universal Device Detection library for parsing User Agents
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/lgpl.html LGPL v3 or later
 */

namespace UDD\Yaml;

use \Spyc AS SpycParser;

class Spyc implements Parser
{
    public function parseFile($file)
    {
        return SpycParser::YAMLLoad($file);
    }
}
