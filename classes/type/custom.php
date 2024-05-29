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
 * Class containing the custom type information.
 *
 * @package   block_kamaleon
 * @copyright 2023 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_kamaleon\type;

/**
 * Type custom.
 *
 * @copyright 2023 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class custom {

    /**
     * Get the contents for the custom type.
     *
     * @param int $instanceid The block instance id.
     * @param object $configdata The block configuration data.
     * @return array List of contents.
     */
    public function get_contents($instanceid, $configdata = null) : array {
        global $DB;

        $contents = $DB->get_records('block_kamaleon_contents', ['instanceid' => $instanceid], 'defaultweight ASC');

        return $contents;
    }
}
