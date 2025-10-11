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
 * Class containing the general controls.
 *
 * @package   block_kamaleon
 * @copyright 2023 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_kamaleon;
use \block_kamaleon\type;

/**
 * Component controller.
 *
 * @copyright 2023 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class controller {

    /**
     * List of available sources.
     *
     * @return array List of sources types.
     */
    public static function get_sourcestypes() {

        $types = [
            'blog' => get_string('type_blog', 'block_kamaleon'),
            /*'calendar' => get_string('type_calendar', 'block_kamaleon'),
            'coursecategories' => get_string('type_coursecategories', 'block_kamaleon'),
            'courses' => get_string('type_courses', 'block_kamaleon'),
            'glossary' => get_string('type_glossary', 'block_kamaleon'),
            'moddata' => get_string('type_moddata', 'block_kamaleon'),
            'statistics' => get_string('type_statistics', 'block_kamaleon')*/
        ];

        $plugininfo = \core_plugin_manager::instance()->get_plugin_info('tool_courserating');
        if ($plugininfo && $plugininfo->is_installed_and_upgraded()) {
            $types['tool_courserating'] = get_string('pluginname', 'tool_courserating');
        }

        return $types;
    }

    public static function get_typeinstance($type) {
        $classname = '\block_kamaleon\type\\' . $type;
        if (class_exists($classname)) {
            return new $classname();
        }

        return null;
    }

    /**
     * Include a CSS file according the current used design.
     *
     * @return void
     */
    public static function include_designcss(string $design) {
        global $CFG, $PAGE;

        if (empty($design)) {
            return;
        }

        $csspath = '/blocks/kamaleon/templates/designs/' . $design . '/styles.css';

        // If the template is not the default and a templace CSS file exist, include the CSS file.
        if (file_exists($CFG->dirroot . $csspath)) {
            $PAGE->requires->css($csspath);
        }

    }

    /**
     * Include the external files according the current used design.
     *
     * @param string $design The design name.
     * @param string $visualization The visualization name.
     * @return void
     */
    public static function include_externals(string $design, string $visualization = '') {
        global $CFG, $PAGE;

        if (empty($design)) {
            return;
        }

        // First load the generic externals.
        if (!empty($visualization)) {

            // Check if use slider in the block and include the flexslider CSS file.
            if ($visualization == 'hslider') {
                $csspath = '/blocks/kamaleon/externals/flexslider/flexslider.css';
                $PAGE->requires->css($csspath, true);
                $PAGE->requires->js_call_amd('block_kamaleon/main', 'initSlider');
            }
        }

        $externals = \block_kamaleon\design::get_externals($design);

        if (empty($externals)) {
            return;
        }

        $localpath = '/blocks/kamaleon/externals/';

        // Include externals CSS files.
        if (property_exists($externals, 'css')) {
            foreach ($externals->css as $css) {
                $csspath = realpath($CFG->dirroot . $localpath . $css);

                if ($csspath != $CFG->dirroot . $localpath . $css) {
                    // Is a relative or other wrong path.
                    continue;
                }

                if (file_exists($csspath)) {
                    $PAGE->requires->css($localpath . $css, true);
                }
            }
        }

        // Include externals JS files.
        if (property_exists($externals, 'js')) {
            foreach ($externals->js as $js) {
                $jspath = realpath($CFG->dirroot . $localpath . $js);

                if ($jspath != $CFG->dirroot . $localpath . $js) {
                    // Is a relative or other wrong path.
                    continue;
                }

                if (file_exists($jspath)) {
                    $PAGE->requires->js($localpath . $js);
                }
            }
        }
    }
}
