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
 * Class containing form definition to manage a custom content.
 *
 * @package    block_kamaleon
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_kamaleon\forms;
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');

use moodleform;

/**
 * The form for handling editing an improve criteria.
 *
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content extends moodleform {

    /**
     * @var object List of local data.
     */
    protected $_data;

    /**
     * @var array List of editor options.
     */
    protected $_editoroptions;

    /**
     * Form definition.
     */
    public function definition() {

        $mform = $this->_form;

        // This contains the data of this form.
        $this->_data = $this->_customdata['data'];
        $this->_editoroptions = $this->_customdata['editoroptions'];
        $imagesoptions = $this->_customdata['filemanageroptions'];

        $mform->addElement('text', 'shorttitle', get_string('shorttitle', 'block_kamaleon'));
        $mform->setType('shorttitle', PARAM_TEXT);

        $mform->addElement('text', 'title', get_string('title', 'block_kamaleon'));
        $mform->setType('title', PARAM_TEXT);

        $mform->addElement('text', 'subtitle', get_string('subtitle', 'block_kamaleon'));
        $mform->setType('subtitle', PARAM_TEXT);

        $mform->addElement('text', 'url', get_string('url', 'block_kamaleon'));
        $mform->setType('url', PARAM_URL);

        $values = ['_blank' => get_string('resourcedisplaynew'), '_self' => get_string('resourcedisplayopen')];
        $mform->addElement('select', 'target', get_string('target', 'block_kamaleon'), $values);

        $mform->addElement('text', 'linkname', get_string('linkname', 'block_kamaleon'));
        $mform->setType('linkname', PARAM_TEXT);

        $mform->addElement('editor', 'content', get_string('content', 'block_kamaleon'), null, $this->_editoroptions);
        $mform->setType('content', PARAM_RAW);

        $mform->addElement('textarea', 'contentvars', get_string('contentvars', 'block_kamaleon'));
        $mform->setType('contentvars', PARAM_RAW);
        $mform->addHelpButton('contentvars', 'contentvars', 'block_kamaleon');

        $mform->addElement('filemanager', 'banner', get_string('banner', 'block_kamaleon'), null, $imagesoptions);
        $mform->addHelpButton('banner', 'banner', 'block_kamaleon');

        $mform->addElement('filemanager', 'icon', get_string('icon', 'block_kamaleon'), null, $imagesoptions);
        $mform->addHelpButton('icon', 'icon', 'block_kamaleon');

        $values = range(-10, 10);
        $values = array_combine($values, $values);
        $mform->addElement('select', 'defaultweight', get_string('defaultweight', 'block_kamaleon'), $values);
        $mform->setDefault('defaultweight', '0');

        $mform->addElement('hidden', 'id', null);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'instanceid', null);
        $mform->setType('instanceid', PARAM_INT);

        $this->add_action_buttons();

        // Finally set the current form data.
        $this->set_data($this->_data);
    }

}
