<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Class to manage a general entity.
 *
 * This solve the PHP Copy/Paste Detector rule.
 *
 * @package   block_kamaleon
 * @copyright 2024 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_kamaleon;

/**
 * Entity generalization.
 *
 * @copyright 2024 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class entity {

    /**
     * @var \stdClass General entity data.
     */
    protected $data;

    /**
     * Class constructor.
     *
     * @param object $data Current data.
     */
    public function __construct($data = null) {

        $this->data = null;

        if ($data && is_object($data)) {
            $this->data = $data;
        }
    }

    public function get_object() {
        return json_decode(json_encode($this->data));
    }

    /**
     * Magic get function.
     *
     * @param string $name Property name.
     * @return mixed Name property value.
     */
    public function __get($name) {
        if (property_exists($this, $name)) {
            return $this->$name;
        } else if (property_exists($this->data, $name)) {
            return $this->data->$name;
        } else if (method_exists($this, 'get_' . $name)) {
            return call_user_func([$this, 'get_' . $name]);
        } else {
            throw new \Exception('propertie_or_method_not_found: ' . get_class($this) . '->'. $name);
        }
    }

    /**
     * Magic ser function.
     *
     * @param string $name Property name.
     * @param mixed $value Property new value.
     */
    public function __set($name, $value) {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        } else if (property_exists($this->data, $name)) {
            $this->data->$name = $value;
        } else if (method_exists($this, 'set_' . $name)) {
            return call_user_func([$this, 'set_' . $name], $value);
        } else {
            throw new \Exception('propertie_or_method_not_found: ' . get_class($this) . '->'. $name);
        }
    }
}
