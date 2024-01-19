<?php
//
// It is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// It is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Edit content page
 *
 * @package    block_kamaleon
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/formslib.php');

$id = optional_param('id', 0, PARAM_INT);

require_login(null, true);

$content = null;
if ($id) {
    $params = ['id' => $id];
    $content = $DB->get_record('block_kamaleon_contents', $params, '*', MUST_EXIST);

    $bid = $content->instanceid;
} else {
    // We need to know the block instance id to create a new content.
    $bid = required_param('instanceid', PARAM_INT);
}

$context = context_block::instance($bid);
require_capability('block/kamaleon:addinstance', $context);

$PAGE->set_context($context);
$PAGE->set_url('/blocks/kamaleon/contentedit.php');
$PAGE->set_pagelayout('incourse');
$PAGE->set_heading(get_string('content', 'block_kamaleon'));
$PAGE->set_title(get_string('content', 'block_kamaleon'));

$acceptedtypes = (new \core_form\filetypes_util)->normalize_file_types($CFG->courseoverviewfilesext);
$filemanageroptions = [
                        'maxbytes' => $CFG->maxbytes,
                        'subdirs' => 0,
                        'maxfiles' => 1,
                        'accepted_types' => $acceptedtypes,
                    ];

$editoroptions = ['maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' => $context];

$contentdraftideditor = file_get_submitted_draft_itemid('content');

if ($content) {

    $bannerdraftitemid = file_get_submitted_draft_itemid('banner');
    file_prepare_draft_area($bannerdraftitemid, $context->id, 'block_kamaleon', 'banner', $id, $filemanageroptions);
    $content->banner = $bannerdraftitemid;

    $icondraftitemid = file_get_submitted_draft_itemid('icon');
    file_prepare_draft_area($icondraftitemid, $context->id, 'block_kamaleon', 'icon', $id, $filemanageroptions);
    $content->icon = $icondraftitemid;

    if (!empty($content->content)) {
        $text = $content->content;
        $content->content = [];
        $content->content['text'] = file_prepare_draft_area($contentdraftideditor, $context->id, 'block_kamaleon', 'content',
                                            $id, $editoroptions, $text);
        $content->content['itemid'] = $contentdraftideditor;
        $content->content['format'] = editors_get_preferred_format();
    }

}

$data = [];
$data['data'] = $content ? $content : ['instanceid' => $bid];
$data['filemanageroptions'] = $filemanageroptions;
$data['editoroptions'] = $editoroptions;

$form = new \block_kamaleon\forms\content(null, $data);
if ($form->is_cancelled()) {
    $url = new moodle_url($CFG->wwwroot . '/blocks/kamaleon/content.php', ['id' => $bid]);
    redirect($url);
} else if ($data = $form->get_data()) {

    if (!$content) {
        $content = new stdClass();
        $content->timecreated = time();
    }

    $content->instanceid = $bid;
    $content->shorttitle = $data->shorttitle;
    $content->title = $data->title;
    $content->subtitle = $data->subtitle;
    $content->url = $data->url;
    $content->target = $data->target;
    $content->linkname = $data->linkname;
    $content->defaultweight = $data->defaultweight;
    $content->contentvars = $data->contentvars;
    $content->timemodified = time();

    if (is_array($data->content)) {
        $content->content = $data->content['text'];
    } else {
        $content->content = $data->content;
    }

    if (!empty($content->id)) {
        $DB->update_record('block_kamaleon_contents', $content);

        $event = \block_kamaleon\event\content_updated::create([
            'objectid' => $content->id,
            'context' => $context,
        ]);
        $event->trigger();
    } else {
        $id = $DB->insert_record('block_kamaleon_contents', $content, true);

        $event = \block_kamaleon\event\content_created::create([
            'objectid' => $id,
            'context' => $context,
        ]);
        $event->trigger();
    }

    // Adding images.
    file_save_draft_area_files($data->banner, $context->id, 'block_kamaleon', 'banner', $id, $filemanageroptions);

    file_save_draft_area_files($data->icon, $context->id, 'block_kamaleon', 'icon', $id, $filemanageroptions);

    // Save editor files.
    if (!empty($content->content)) {
        $text = file_save_draft_area_files($contentdraftideditor, $context->id, 'block_kamaleon', 'content',
                                            $id, $editoroptions, $content->content);
        $DB->set_field('block_kamaleon_contents', 'content', $text, ['id' => $id]);
    }

    $url = new moodle_url($CFG->wwwroot . '/blocks/kamaleon/content.php', ['id' => $bid, 'msg' => 'changessaved']);
    redirect($url);
    exit;

}


echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('newcontent', 'block_kamaleon'));

$form->display();

echo $OUTPUT->footer();
