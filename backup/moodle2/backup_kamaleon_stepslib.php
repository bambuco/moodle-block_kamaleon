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
 * Define the complete forum structure for backup.
 *
 * @copyright 2024 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_kamaleon_block_structure_step extends backup_block_structure_step {

    protected function define_structure() {

        // Define each element separated.
        $kamaleon = new backup_nested_element('kamaleon', ['id'], null);
        $contents = new backup_nested_element('contents');

        $item = new backup_nested_element('item', ['id'], [
            'instanceid',
            'shorttitle',
            'title',
            'subtitle',
            'url',
            'target',
            'linkname',
            'content',
            'contentvars',
            'defaultweight',
            'timecreated',
            'timemodified',
        ]);

        // Build the tree.
        $kamaleon->add_child($contents);
        $contents->add_child($item);

        // Define sources.
        $kamaleon->set_source_array([(object)['id' => $this->task->get_blockid()]]);

        $item->set_source_table('block_kamaleon_contents', ['instanceid' => backup_helper::is_sqlparam($this->task->get_blockid())]);

        // Annotations.
        $item->annotate_ids('block_kamaleon', 'instanceid');
        $item->annotate_files('block_kamaleon', 'content', null);

        // Return the root element (kamaleon_contents), wrapped into standard block structure.
        return $this->prepare_block_structure($kamaleon);
    }
}
