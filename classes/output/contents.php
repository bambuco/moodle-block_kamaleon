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
 * Class containing renderers for the component.
 *
 * @package    block_kamaleon
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_kamaleon\output;

use core_tag\reportbuilder\local\entities\instance;
use renderable;
use renderer_base;
use templatable;

/**
 * Class containing data for the component.
 *
 * @copyright 2023 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class contents implements renderable, templatable {

    /**
     * @var int The block instance id.
     */
    private $instanceid;

    /**
     * @var array List of contents to print.
     */
    private $contents;

    /**
     * @var string The current block design.
     */
    public $currentdesign;

    /**
     * @var bool Show edition buttons.
     */
    public $showedition;

    /**
     * Constructor.
     *
     * @param int $instanceid The block instance id.
     * @param array $contents The contents list.
     */
    public function __construct(int $instanceid, array $contents, string $currentdesign = null, bool $showedition = false) {
        $this->instanceid = $instanceid;
        $this->contents = $contents;
        $this->currentdesign = $currentdesign;
        $this->showedition = $showedition;
    }

    /**
     * Get the current template according the selected design.
     *
     * @return string
     */
    public function get_template() {

        if (empty($this->currentdesign)) {
            return 'block_kamaleon/contents';
        }

        return 'block_kamaleon/designs/' . $this->currentdesign . '/layout';
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return array Context variables for the template
     */
    public function export_for_template(renderer_base $output) {

        $context = \context_block::instance($this->instanceid);

        $contents = [];
        $index = 0;
        foreach ($this->contents as $content) {
            // Check if the content is an object of \block_kamaleon\content type.
            if (!($content instanceof \block_kamaleon\content)) {
                $content = new \block_kamaleon\content($content);
            }

            $content->content = file_rewrite_pluginfile_urls($content->content, 'pluginfile.php', $context->id, 'block_kamaleon',
                                                                'content', $content->id);

            // Convert $content into stdClass.
            $contentdata = $content->get_object();
            $contentdata->banner = $content->get_banner();
            $contentdata->icon = $content->get_icon();
            $contentdata->timecreatedformated = userdate($contentdata->timecreated);
            $contentdata->timemodifiedformated = userdate($contentdata->timemodified);

            $contentdata->hasbanner = !empty($contentdata->banner);
            $contentdata->hasicon = !empty($contentdata->icon);
            $contentdata->index = $index;
            $index++;
            $contents[] = $contentdata;
        }

        $defaultvariables = [
            'instanceid' => $this->instanceid,
            'contents' => $contents,
            'sesskey' => sesskey(),
            'showedition' => $this->showedition,
        ];

        return $defaultvariables;
    }
}
