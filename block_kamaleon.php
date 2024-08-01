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
 * Block kamaleon class definition.
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

    /* Allows the block class to have a say in the user's ability to edit (i.e., configure) blocks of this type.
     * The framework has first say in whether this will be allowed (e.g., no editing allowed unless in edit mode)
     * but if the framework does allow it, the block can still decide to refuse.
     * @return boolean
     */
    function user_can_edit() {
        $canmanage = has_capability('block/kamaleon:addinstance', $this->context);

        if ($canmanage) {
            return true;
        }

        return false;
    }

    /**
     * If overridden and set to false by the block it will not be editable.
     *
     * @return bool
     */
    public function instance_can_be_edited() {
        return $this->user_can_edit();
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

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->footer = '';

        $filteropt = new stdClass;
        $filteropt->overflowdiv = true;

        // If the content is trusted, do not clean it.
        if ($this->content_is_trusted()) {
            $filteropt->noclean = true;
        }

        $design = '';
        if (!empty($this->config) && !empty($this->config->design)) {

            $visualization = property_exists($this->config, 'visualization') ? $this->config->visualization : '';
            \block_kamaleon\controller::include_externals($this->config->design, $visualization);

            \block_kamaleon\controller::include_designcss($this->config->design);
            $design = $this->config->design;
        }

        $contentsource = null;
        if (is_object($this->config) && isset($this->config->type)) {
            $contentsource = \block_kamaleon\controller::get_typeinstance($this->config->type);
        }

        $list = [];
        if ($contentsource) {
            $list = $contentsource->get_contents($this->instance->id, $this->config);
        }

        if (empty($list) && !empty($this->config->originalinstanceid) && $contentsource) {
            $instanceid = $this->config->originalinstanceid;
            $list = $contentsource->get_contents($instanceid, $this->config);
        } else {
            $instanceid = $this->instance->id;
        }

        $renderable = new \block_kamaleon\output\contents($instanceid, $list, $design);
        $renderer = $this->page->get_renderer('block_kamaleon');

        $this->content->text = $renderer->render($renderable);

        if (isset($this->config->htmlheader)) {
            // Rewrite url.
            $htmlheader = file_rewrite_pluginfile_urls($this->config->htmlheader,
                                                                     'pluginfile.php',
                                                                     $this->context->id,
                                                                     'block_kamaleon',
                                                                     'content_header',
                                                                     0);
            // Default to FORMAT_HTML.
            $htmlheaderformat = FORMAT_HTML;
            // Check to see if the format has been properly set on the config.
            if (isset($this->config->htmlheaderformat)) {
                $htmlheaderformat = $this->config->htmlheaderformat;
            }

            if (is_array($htmlheader)) {
                $htmlheader = $htmlheader['text'];
            }

            $this->content->text = format_text($htmlheader, $htmlheaderformat, $filteropt) . $this->content->text;
        }

        if (isset($this->config->htmlfooter)) {
            // Rewrite url.
            $htmlfooter = file_rewrite_pluginfile_urls($this->config->htmlfooter,
                                                                     'pluginfile.php',
                                                                     $this->context->id,
                                                                     'block_kamaleon',
                                                                     'content_footer',
                                                                     0);
            // Default to FORMAT_HTML.
            $htmlfooterformat = FORMAT_HTML;
            // Check to see if the format has been properly set on the config.
            if (isset($this->config->htmlfooterformat)) {
                $htmlfooterformat = $this->config->htmlfooterformat;
            }

            if (is_array($htmlfooter)) {
                $htmlfooter = $htmlfooter['text'];
            }

            $this->content->footer .= format_text($htmlfooter, $htmlfooterformat, $filteropt);
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

        if ($this->config && !empty($this->config->design)) {
            $attributes['class'] .= ' design-' . $this->config->design;
        } else {
            $attributes['class'] .= ' design-default';
        }

        if ($this->config && property_exists($this->config, 'visualization') && $this->config->visualization == 'hslider') {
            $attributes['class'] .= ' kam-hslider';
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
     * Serialize and store config data.
     *
     * @param object $data
     * @param boolean $nolongerused
     * @return void
     */
    public function instance_config_save($data, $nolongerused = false) {

        $config = clone($data);
        // Move embedded files into a proper filearea and adjust HTML links to match.
        $config->htmlheader = file_save_draft_area_files($data->htmlheader['itemid'],
                              $this->context->id,
                              'block_kamaleon',
                              'content_header',
                              0,
                              ['subdirs' => true],
                              $data->htmlheader['text']);
        $config->htmlfooter = file_save_draft_area_files($data->htmlfooter['itemid'],
                              $this->context->id,
                              'block_kamaleon',
                              'content_footer',
                              0,
                              ['subdirs' => true],
                              $data->htmlfooter['text']);
        $config->htmlheaderformat = $data->htmlheader['format'];
        $config->htmlfooterformat = $data->htmlfooter['format'];
        parent::instance_config_save($config, $nolongerused);
    }

    /**
     * Delete dependencies when the block instance is deleted.
     *
     * @return bool
     */
    function instance_delete() {
        global $DB;

        $fs = get_file_storage();
        $fs->delete_area_files($this->context->id, 'block_kamaleon');

        if ($contents = $DB->get_records('block_kamaleon_contents', ['instanceid' => $this->instance->id])) {
            foreach ($contents as $content) {

                // Delete files.
                $files = $fs->get_area_files($this->context->id, 'block_kamaleon', 'banner', $content->id);
                foreach ($files as $file) {
                    $file->delete();
                }

                $files = $fs->get_area_files($this->context->id, 'block_kamaleon', 'icon', $content->id);
                foreach ($files as $file) {
                    $file->delete();
                }

                // Delete content information.
                $DB->delete_records('block_kamaleon_contents', ['id' => $content->id]);
            }
        }

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

    /**
     * Return a block_contents object representing the full contents of this block.
     *
     * This internally calls ->get_content(), and then adds the editing controls etc.
     *
     * @param object $output The output renderer from the parent context (e.g. page renderer)
     * @return block_contents a representation of the block, for rendering.
     */
    public function get_content_for_output($output) {
        $bc = parent::get_content_for_output($output);

        if (empty($bc->controls) ||
                !$this->page->user_is_editing() ||
                !has_capability('block/kamaleon:addinstance', $this->context)) {
            return $bc;
        }

        $str = get_string('customcontentgo', 'block_kamaleon');

        $newcontrols = [];
        foreach ($bc->controls as $control) {
            $newcontrols[] = $control;
            // Append our new item onto the controls if we're on the correct item.
            if (strpos($control->attributes['class'], 'editing_edit') !== false) {
                $icon = new pix_icon('t/preferences', $str, 'moodle', ['class' => 'iconsmall']);
                $newcontrols[] = new action_menu_link_secondary(
                    new moodle_url('/blocks/kamaleon/listcontents.php', ['id' => $this->instance->id, 'sesskey' => sesskey()]),
                    $icon,
                    $str,
                    ['class' => 'editing_manage']
                );
            }
        }

        $bc->controls = $newcontrols;
        return $bc;
    }

}
