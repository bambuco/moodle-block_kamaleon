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
 * Class to manage the content information.
 *
 * @package   block_kamaleon
 * @copyright 2024 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_kamaleon;

/**
 * Content info.
 *
 * @copyright 2024 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content extends entity {

    /**
     * Class constructor.
     *
     * @param int|object $content Current content data or id.
     */
    public function __construct($content = null) {
        global $DB;

        $this->data = null;

        if ($content) {

            if (is_object($content) && property_exists($content, 'id')) {
                $this->data = $content;
            } else {
                $this->data = $DB->get_record('block_kamaleon_contents', ['id' => (int)$content]);
            }
        }

        if (!$this->data) {
            throw new \moodle_exception('errornotcontentdata', 'block_kamaleon');
        }
    }

    /**
     * Get the banner image.
     *
     * @return string Image URI.
     */
    public function get_banner() {

        $uri = '';
        $context = \context_block::instance($this->data->instanceid);
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'block_kamaleon', 'banner', $this->data->id);
        foreach ($files as $file) {
            $filename = $file->get_filename();

            if (!empty($filename) && $filename != '.') {
                $path = '/' . implode('/', [$file->get_contextid(),
                                                    'block_kamaleon',
                                                    'banner',
                                                    $file->get_itemid() . $file->get_filepath() . $filename]);

                return \moodle_url::make_file_url('/pluginfile.php', $path);

                // Only one image by content.
                break;
            }
        }

        return $uri;
    }

    /**
     * Get the icon image.
     *
     * @return string Image URI.
     */
    public function get_icon() {

        $uri = '';
        $context = \context_block::instance($this->data->instanceid);
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'block_kamaleon', 'icon', $this->data->id);
        foreach ($files as $file) {
            $filename = $file->get_filename();

            if (!empty($filename) && $filename != '.') {
                $path = '/' . implode('/', [$file->get_contextid(),
                                                    'block_kamaleon',
                                                    'icon',
                                                    $file->get_itemid() . $file->get_filepath() . $filename]);

                return \moodle_url::make_file_url('/pluginfile.php', $path);

                // Only one image by content.
                break;
            }
        }

        return $uri;
    }

}
