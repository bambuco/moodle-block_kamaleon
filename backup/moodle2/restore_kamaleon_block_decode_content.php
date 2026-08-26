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
 * Specialised restore_decode_content provider that unserializes the configdata
 *
 * @package    block_kamaleon
 * @copyright  2026 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Specialised restore_decode_content provider that unserializes the configdata
 * field, to serve the configdata->htmlheader and configdata->htmlfooter content
 * to the restore_decode_processor packaging it back to its serialized form after
 * process.
 *
 * @copyright 2024 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_kamaleon_block_decode_content extends restore_decode_content {
    /**
     * @var stdClass Temp storage for unserialized configdata.
     */
    protected $configdata;

    /**
     * Defines the iterator to fetch the records to be processed by the restore_decode_processor.
     *
     * @return moodle_recordset The recordset to be processed.
     */
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

    /**
     * Field preprocessing, to unserialize the configdata before processing the htmlheader and htmlfooter content.
     *
     * @param string $field The field value to preprocess.
     * @return string The preprocessed field value.
     */
    protected function preprocess_field($field) {
        $this->configdata = unserialize_object(base64_decode($field));
        $htmlheader = isset($this->configdata->htmlheader) ? $this->configdata->htmlheader : '';
        $htmlfooter = isset($this->configdata->htmlfooter) ? $this->configdata->htmlfooter : '';

        return $htmlheader . '<!--headerxfooter-->' . $htmlfooter;
    }

    /**
     * Field postprocessing, to repackage the configdata after processing the htmlheader and htmlfooter content.
     *
     * @param string $field The field value to postprocess.
     * @return string The postprocessed field value.
     */
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
