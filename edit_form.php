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

        $canmanage = has_capability('block/kamaleon:addinstance', $this->block->context);

        // Fields for editing HTML block title and contents.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_kamaleon'));
        $mform->setType('config_title', PARAM_TEXT);

        $allowcssclasses = get_config('block_kamaleon', 'allowcssclasses');
        if (!empty($allowcssclasses)) {
            $mform->addElement('text', 'config_classes', get_string('configclasses', 'block_kamaleon'));
            $mform->setType('config_classes', PARAM_TEXT);
            $mform->addHelpButton('config_classes', 'configclasses', 'block_kamaleon');
        }

        $types = ['custom' => get_string('type_custom', 'block_kamaleon')];

        if (has_capability('block/kamaleon:addsources', $this->block->context)) {
            $sourcestypes = \block_kamaleon\controller::get_sourcestypes();
            $types = array_merge($types, $sourcestypes);
        }
        $mform->addElement('select', 'config_type', get_string('configtype', 'block_kamaleon'), $types);

        $mform->addElement('text', 'config_maxrecords', get_string('configmaxrecords', 'block_kamaleon'));
        $mform->setType('config_maxrecords', PARAM_INT);

        $mform->addElement('textarea', 'config_instanceparams', get_string('configinstanceparams', 'block_kamaleon'));
        $mform->addHelpButton('config_instanceparams', 'configinstanceparams', 'block_kamaleon');
        $mform->setType('config_instanceparams', PARAM_TEXT);

        $availabledesigns = \block_kamaleon\design::get_availables();
        $mform->addElement('select', 'config_design', get_string('design', 'block_kamaleon'), $availabledesigns);

        $visualizations = \block_kamaleon\design::get_visualizations();
        $mform->addElement('select', 'config_visualization', get_string('visualization', 'block_kamaleon'), $visualizations);

        if ($canmanage) {
            $contentitemurl = new \moodle_url('/blocks/kamaleon/listcontents.php', ['id' => $this->block->instance->id]);
            $contentbuttonlabel = get_string('customcontentgo', 'block_kamaleon');
            $link = \html_writer::link($contentitemurl, $contentbuttonlabel, ['class' => 'btn btn-primary']);
            $mform->addElement('static', 'contentbutton', $link);
        }

        $editoroptions = ['maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' => $this->block->context];

        // Header HTML editor.
        $mform->addElement('editor', 'config_htmlheader', get_string('htmlheader', 'block_kamaleon'), null, $editoroptions);
        $mform->setType('config_htmlheader', PARAM_RAW); // XSS is prevented when printing the block contents and serving files.

        // Footer HTML editor.
        $mform->addElement('editor', 'config_htmlfooter', get_string('htmlfooter', 'block_kamaleon'), null, $editoroptions);
        $mform->setType('config_htmlfooter', PARAM_RAW); // XSS is prevented when printing the block contents and serving files.

    }

    /**
     * Set the default values for the form.
     *
     * @param stdClass $defaults The default values.
     * @return void
     */
    function set_data($defaults) {

        if (!empty($this->block->config) && !empty($this->block->config->htmlheader)) {
            $htmlheader = $this->block->config->htmlheader;
            $draftideditor = file_get_submitted_draft_itemid('config_htmlheader');
            if (empty($htmlheader)) {
                $currenthtmlheader = '';
            } else {
                $currenthtmlheader = $htmlheader;
            }
            $defaults->config_htmlheader['text'] = file_prepare_draft_area($draftideditor, $this->block->context->id,
                                                    'block_kamaleon', 'content_header', 0, ['subdirs' => true], $currenthtmlheader);
            $defaults->config_htmlheader['itemid'] = $draftideditor;
            $defaults->config_htmlheader['format'] = $this->block->config->htmlheaderformat ?? FORMAT_MOODLE;
        } else {
            $htmlheader = '';
        }

        if (!empty($this->block->config) && !empty($this->block->config->htmlfooter)) {
            $htmlfooter = $this->block->config->htmlfooter;
            $draftideditor = file_get_submitted_draft_itemid('config_htmlfooter');
            if (empty($htmlfooter)) {
                $currenthtmlfooter = '';
            } else {
                $currenthtmlfooter = $htmlfooter;
            }
            $defaults->config_htmlfooter['text'] = file_prepare_draft_area($draftideditor, $this->block->context->id,
                                                    'block_kamaleon', 'content_footer', 0, ['subdirs' => true], $currenthtmlfooter);
            $defaults->config_htmlfooter['itemid'] = $draftideditor;
            $defaults->config_htmlfooter['format'] = $this->block->config->htmlfooterformat ?? FORMAT_MOODLE;
        } else {
            $htmlfooter = '';
        }

        if (!$this->block->user_can_edit() && !empty($this->block->config->title)) {
            // If a title has been set but the user cannot edit it format it nicely
            $title = $this->block->config->title;
            $defaults->config_title = format_string($title, true, $this->page->context);
            // Remove the title from the config so that parent::set_data doesn't set it.
            unset($this->block->config->title);
        }

        // Have to delete html here, otherwise parent::set_data will empty content of editors.
        unset($this->block->config->htmlheader);
        unset($this->block->config->htmlfooter);
        parent::set_data($defaults);

        if (!isset($this->block->config)) {
            $this->block->config = new stdClass();
        }

        $this->block->config->htmlheader = $htmlheader;
        $this->block->config->htmlfooter = $htmlfooter;

        if (isset($title)) {
            // Reset the preserved title.
            $this->block->config->title = $title;
        }

        if (empty($this->block->config->originalinstanceid)) {
            $this->block->config->originalinstanceid = $this->block->instance->id;
        }
    }

}
