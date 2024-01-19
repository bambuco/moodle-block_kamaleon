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
 * Class containing form definition to manage the designs.
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
class designs extends moodleform {

    /**
     * Form definition.
     */
    public function definition() {

        $mform = $this->_form;

        $mform->addElement('select', 'design', get_string('design', 'block_kamaleon'), $this->_customdata['designs']);
        $mform->setDefault('design', $this->_customdata['current']);

        $mform->addElement('hidden', 'id', null);
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $this->_customdata['id']);

        $this->add_action_buttons(false);

    }

}
