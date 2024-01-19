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
 * Form for editing block instances.
 *
 * @package   block_kamaleon
 * @copyright 2023 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Form for editing block instances.
 *
 * @copyright 2023 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_kamaleon_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
        global $CFG;

        // Fields for editing HTML block title and contents.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_kamaleon'));
        $mform->setType('config_title', PARAM_TEXT);

        if (!empty($CFG->block_kamaleon_allowcssclasses)) {
            $mform->addElement('text', 'config_classes', get_string('configclasses', 'block_kamaleon'));
            $mform->setType('config_classes', PARAM_TEXT);
            $mform->addHelpButton('config_classes', 'configclasses', 'block_kamaleon');
        }

        $types = ['custom' => get_string('type_custom', 'block_kamaleon')];

        if (has_capability('block/kamaleon:addsources', $this->page->context)) {
            $sourcestypes = \block_kamaleon\controller::get_sourcestypes();
            $types = array_merge($types, $sourcestypes);
        }
        $mform->addElement('select', 'config_type', get_string('configtype', 'block_kamaleon'), $types);

        $availabledesigns = \block_kamaleon\design::get_availables();
        $mform->addElement('select', 'config_design', get_string('design', 'block_kamaleon'), $availabledesigns);

        $contentitemurl = new \moodle_url('/blocks/kamaleon/content.php', ['id' => $this->block->instance->id]);
        $contentbuttonlabel = get_string('customcontentgo', 'block_kamaleon');
        $link = \html_writer::link($contentitemurl, $contentbuttonlabel, ['class' => 'btn btn-primary']);
        $mform->addElement('static', 'contentbutton', $link);

    }

    function set_data($defaults) {

        if (!$this->block->user_can_edit() && !empty($this->block->config->title)) {
            // If a title has been set but the user cannot edit it format it nicely
            $title = $this->block->config->title;
            $defaults->config_title = format_string($title, true, $this->page->context);
            // Remove the title from the config so that parent::set_data doesn't set it.
            unset($this->block->config->title);
        }

        parent::set_data($defaults);

        if (!isset($this->block->config)) {
            $this->block->config = new stdClass();
        }

        if (isset($title)) {
            // Reset the preserved title.
            $this->block->config->title = $title;
        }
    }

}
