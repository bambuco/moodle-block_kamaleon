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
 * Class to manage the design information.
 *
 * @package   block_kamaleon
 * @copyright 2024 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_kamaleon;

/**
 * Design info.
 *
 * @copyright 2024 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class design extends entity {

    public static function get_availables() {
        global $CFG;

        $path = $CFG->dirroot . '/blocks/kamaleon/templates/designs/';
        $files = array_diff(scandir($path), ['..', '.']);

        $list = [];
        foreach ($files as $file) {
            if (is_dir($path . $file)
                    && file_exists($path . $file . '/properties.json')
                    && file_exists($path . $file . '/layout.mustache')) {

                $propertiescontent = file_get_contents($path . $file . '/properties.json');
                if (!empty($propertiescontent)) {
                    $settings = json_decode($propertiescontent, true);

                    if (is_array($settings)) {
                        $settings = (object)$settings;
                    }

                    if (!empty($settings->name)) {
                        $list[$file] = $settings->name;
                    }
                }

            }
        }

        return $list;
    }

}
