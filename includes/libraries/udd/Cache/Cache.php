<?php
/**
 * Device Detector - The Universal Device Detection library for parsing User Agents
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/lgpl.html LGPL v3 or later
 */

namespace UDD\Cache;

interface Cache
{
    public function fetch($id);

    public function contains($id);

    public function save($id, $data, $lifeTime = 0);

    public function delete($id);

    public function flushAll();
}
