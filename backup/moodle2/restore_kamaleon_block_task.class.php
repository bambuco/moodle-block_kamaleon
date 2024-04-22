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

require_once($CFG->dirroot . '/blocks/kamaleon/backup/moodle2/restore_kamaleon_stepslib.php');

/**
 * Specialised restore task for the html block
 * (requires encode_content_links in some configdata attrs)
 *
 *
 * @copyright 2024 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_kamaleon_block_task extends restore_block_task {

    /**
     * If the block declares own backup settings defined in the file backup_foobar_settingslib.php, add them here.
     * Most blocks just leave the method body empty.
     *
     */
    protected function define_my_settings() {
    }

    /**
     * Blocks that do not have their own database tables usually leave this method empty.
     * Otherwise this method consists of one or more $this->add_step() calls where you
     * define the task as a sequence of steps to execute.
     *
     */
    protected function define_my_steps() {
        // Block has one structure step.
        $this->add_step(new restore_kamaleon_block_structure_step('kamaleon_structure', 'kamaleon.xml'));
    }

    /**
     * Returns the array of file area names within the block context.
     *
     * @return array
     */
    public function get_fileareas() {
        return ['banner', 'icon', 'content', 'content_header', 'content_footer'];
    }

    /**
     * Instead of using their own tables, blocks usually use the configuration tables to hold their data.
     * This method returns the array of all config elements that must be processed before they are stored
     * in the backup.
     * This is typically used when the stored config elements holds links to embedded media.
     * Most blocks just return empty array here.
     *
     * @return array
     */
    public function get_configdata_encoded_attributes() {
        return ['htmlheader', 'htmlfooter']; // We need to encode some attrs in configdata.
    }

    static public function define_decode_contents() {

        $contents = [];

        $contents[] = new restore_kamaleon_block_decode_content('block_instances', 'configdata', 'block_instance');

        return $contents;
    }

    static public function define_decode_rules() {
        return [];
    }
}

/**
 * Specialised restore_decode_content provider that unserializes the configdata
 * field, to serve the configdata->htmlheader and configdata->htmlfooter content
 * to the restore_decode_processor packaging it back to its serialized form after
 * process.
 */
class restore_kamaleon_block_decode_content extends restore_decode_content {

    protected $configdata; // Temp storage for unserialized configdata.

    protected function get_iterator() {
        global $DB;

        // Build the SQL dynamically here.
        $fieldslist = 't.' . implode(', t.', $this->fields);
        $sql = "SELECT t.id, $fieldslist
                  FROM {" . $this->tablename . "} t
                  JOIN {backup_ids_temp} b ON b.newitemid = t.id
                 WHERE b.backupid = ?
                   AND b.itemname = ?
                   AND t.blockname = 'kamaleon'";
        $params = [$this->restoreid, $this->mapping];
        return ($DB->get_recordset_sql($sql, $params));
    }

    protected function preprocess_field($field) {
        $this->configdata = unserialize_object(base64_decode($field));
        $htmlheader = isset($this->configdata->htmlheader) ? $this->configdata->htmlheader : '';
        $htmlfooter = isset($this->configdata->htmlfooter) ? $this->configdata->htmlfooter : '';

        return $htmlheader . '<!--headerxfooter-->' . $htmlfooter;
    }

    protected function postprocess_field($field) {
        $field = explode('<!--headerxfooter-->', $field);
        $this->configdata->htmlheader = $field[0];
        $this->configdata->htmlfooter = $field[1];

        if (isset($this->configdata->originalinstanceid)) {
            unset($this->configdata->originalinstanceid);
        }

        return base64_encode(serialize($this->configdata));
    }
}
