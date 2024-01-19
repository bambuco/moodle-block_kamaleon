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

class block_kamaleon extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_kamaleon');
    }

    function has_config() {
        return true;
    }

    function applicable_formats() {
        return ['all' => true];
    }

    function specialization() {
        if (isset($this->config->title)) {
            $this->title = $this->title = format_string($this->config->title, true, ['context' => $this->context]);
        } else {
            $this->title = get_string('newblock', 'block_kamaleon');
        }
    }

    function instance_allow_multiple() {
        return true;
    }

    function get_content() {
        global $DB;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->footer = '';

        $design = '';
        if (!empty($this->config) && !empty($this->config->design)) {
            \block_kamaleon\controller::include_designcss($this->config->design);
            $design = $this->config->design;
        }

        $list = $DB->get_records('block_kamaleon_contents', ['instanceid' => $this->instance->id], 'defaultweight ASC');

        $renderable = new \block_kamaleon\output\contents($this->instance->id, $list, $design);
        $renderer = $this->page->get_renderer('block_kamaleon');

        $this->content->text = $renderer->render($renderable);

        $isediting = $this->page->user_is_editing() && has_capability('block/kamaleon:addinstance', $this->context);
        if ($isediting) {
            $contentitemurl = new \moodle_url('/blocks/kamaleon/content.php', ['id' => $this->instance->id]);
            $contentbuttonlabel = get_string('customcontentgo', 'block_kamaleon');
            $link = \html_writer::link($contentitemurl, $contentbuttonlabel, ['class' => 'btn btn-primary']);
            $this->content->footer = $link;
        }

        return $this->content;
    }

    public function get_content_for_external($output) {
        global $CFG;
        require_once($CFG->libdir . '/externallib.php');

        $bc = new stdClass;
        $bc->title = null;
        $bc->content = '';
        $bc->contenformat = FORMAT_MOODLE;
        $bc->footer = '';
        $bc->files = [];

        if (!$this->hide_header()) {
            $bc->title = $this->title;
        }

        if (isset($this->config->source)) {
            \block_kamaleon\controller::include_designcss($this->config->design);

            $list = $DB->get_records('block_kamaleon_contents', ['instanceid' => $this->instance->id], 'defaultweight ASC');

            $renderable = new \block_kamaleon\output\contents($this->instance->id, $list, $this->config->design);
            $renderer = $this->page->get_renderer('block_kamaleon');

            $bc->content = $renderer->render($renderable);

            $filteropt = new stdClass;
            if ($this->content_is_trusted()) {
                // Fancy html allowed only on course, category and system blocks.
                $filteropt->noclean = true;
            }

            $format = FORMAT_HTML;
            // Check to see if the format has been properly set on the config.
            if (isset($this->config->format)) {
                $format = $this->config->format;
            }
            list($bc->content, $bc->contentformat) =
                external_format_text($content, $format, $this->context, 'block_kamaleon', 'content', null, $filteropt);
            $bc->files = external_util::get_area_files($this->context->id, 'block_kamaleon', 'content', false, false);

        }
        return $bc;
    }

    function content_is_trusted() {
        global $SCRIPT;

        if (!$context = context::instance_by_id($this->instance->parentcontextid, IGNORE_MISSING)) {
            return false;
        }
        //find out if this block is on the profile page
        if ($context->contextlevel == CONTEXT_USER) {
            if ($SCRIPT === '/my/index.php') {
                // this is exception - page is completely private, nobody else may see content there
                // that is why we allow JS here
                return true;
            } else {
                // no JS on public personal pages, it would be a big security issue
                return false;
            }
        }

        return true;
    }

    /**
     * The block should only be dockable when the title of the block is not empty
     * and when parent allows docking.
     *
     * @return bool
     */
    public function instance_can_be_docked() {
        return (!empty($this->config->title) && parent::instance_can_be_docked());
    }

    /*
     * Add custom html attributes to aid with theming and styling
     *
     * @return array
     */
    function html_attributes() {
        global $CFG;

        $attributes = parent::html_attributes();

        if (!empty($CFG->block_kamaleon_allowcssclasses)) {
            if (!empty($this->config->classes)) {
                $attributes['class'] .= ' ' . $this->config->classes;
            }
        }

        return $attributes;
    }

    /**
     * Return the plugin config settings for external functions.
     *
     * @return stdClass the configs for both the block instance and plugin
     * @since Moodle 3.8
     */
    public function get_config_for_external() {
        global $CFG;

        // Return all settings for all users since it is safe (no private keys, etc..).
        $instanceconfigs = !empty($this->config) ? $this->config : new stdClass();
        $pluginconfigs = (object) ['allowcssclasses' => $CFG->block_kamaleon_allowcssclasses];

        return (object) [
            'instance' => $instanceconfigs,
            'plugin' => $pluginconfigs,
        ];
    }

    /**
     * Delete dependencies when the block instance is deleted.
     *
     * @return bool
     */
    function instance_delete() {
        $fs = get_file_storage();
        $fs->delete_area_files($this->context->id, 'block_kamaleon');
        return true;
    }

    /**
     * Copy any block-specific data when copying to a new block instance.
     * @param int $fromid the id number of the block instance to copy from
     * @return boolean
     */
    public function instance_copy($fromid) {
        $fromcontext = context_block::instance($fromid);
        $fs = get_file_storage();

        // Do not use draft files hacks outside of forms.
        $files = $fs->get_area_files($fromcontext->id, 'block_kamaleon', 'content', 0, 'id ASC', false);
        foreach ($files as $file) {
            $filerecord = ['contextid' => $this->context->id];
            $fs->create_file_from_storedfile($filerecord, $file);
        }

        $files = $fs->get_area_files($fromcontext->id, 'block_kamaleon', 'banner', 0, 'id ASC', false);
        foreach ($files as $file) {
            $filerecord = ['contextid' => $this->context->id];
            $fs->create_file_from_storedfile($filerecord, $file);
        }

        $files = $fs->get_area_files($fromcontext->id, 'block_kamaleon', 'icon', 0, 'id ASC', false);
        foreach ($files as $file) {
            $filerecord = ['contextid' => $this->context->id];
            $fs->create_file_from_storedfile($filerecord, $file);
        }

        return true;
    }

}
