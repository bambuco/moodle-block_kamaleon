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
 * @package    block_kamaleon
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define the complete structure for restore
 *
 * @copyright 2024 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_kamaleon_block_structure_step extends restore_structure_step {

    protected function define_structure() {

        $paths = [];

        $paths[] = new restore_path_element('block', '/block', true);
        $paths[] = new restore_path_element('kamaleon', '/block/kamaleon');
        $paths[] = new restore_path_element('item', '/block/kamaleon/contents/item');

        return $paths;
    }

    /**
     * Process block data.
     */
    public function process_block($data) {
        global $DB;

        $data = (object)$data;

        // For any reason (non multiple, dupe detected...) block not restored, return.
        if (!$this->task->get_blockid()) {
            return;
        }

        // Iterate over all the item elements, creating them if needed.
        if (isset($data->kamaleon['contents']['item'])) {
            foreach ($data->kamaleon['contents']['item'] as $item) {
                $item = (object)$item;

                $item->instanceid = $this->task->get_blockid();
                $DB->insert_record('block_kamaleon_contents', $item);
            }
        }

    }
}
