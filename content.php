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
 * List of contents.
 *
 * @package    block_kamaleon
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$delete = optional_param('delete', 0, PARAM_INT);
$confirm = optional_param('confirm', '', PARAM_ALPHANUM);
$preview = optional_param('preview', 0, PARAM_INT);

if ($delete) {
    $content = $DB->get_record('block_kamaleon_contents', ['id' => $delete], '*', MUST_EXIST);
    $id = $content->instanceid;
} else {
    $id = required_param('id', PARAM_INT);
}

// Validate the block instance.
$instance = $DB->get_record('block_instances', ['id' => $id, 'blockname' => 'kamaleon'], '*', MUST_EXIST);

$msg = optional_param('msg', '', PARAM_TEXT);

require_login(null, true);

$context = context_block::instance($id);
require_capability('block/kamaleon:addinstance', $context);

$PAGE->set_context($context);
$PAGE->set_url('/blocks/kamaleon/index.php');
$PAGE->set_pagelayout('incourse');
$PAGE->set_heading(get_string('content', 'block_kamaleon'));
$PAGE->set_title(get_string('content', 'block_kamaleon'));

$configdata = empty($instance->configdata) ? (new stdClass()) : unserialize(base64_decode($instance->configdata));
$availabledesigns = \block_kamaleon\design::get_availables();
$currentdesign = !empty($configdata->design) ? $configdata->design : null;

$designform = new \block_kamaleon\forms\designs(null, ['id' => $id, 'designs' => $availabledesigns, 'current' => $currentdesign]);

if ($designformdata = $designform->get_data()) {
    $configdata->design = $designformdata->design;
    $currentdesign = $configdata->design;

    if (!$preview) {
        $configdata = base64_encode(serialize($configdata));
        $DB->set_field('block_instances', 'configdata', $configdata, ['id' => $id]);
    }
}

if (!empty($currentdesign)) {
    \block_kamaleon\controller::include_designcss($currentdesign);
}

$PAGE->requires->js_call_amd('block_kamaleon/designchooser', 'init');

echo $OUTPUT->header();

// Delete a content, after confirmation.
if ($delete && confirm_sesskey()) {

    if ($confirm != md5($delete)) {
        $returnurl = new moodle_url('/blocks/kamaleon/content.php', ['id' => $id]);
        echo $OUTPUT->heading(get_string('contentdelete', 'block_kamaleon'), 3);
        $optionsyes = ['delete' => $delete, 'confirm' => md5($delete), 'sesskey' => sesskey()];
        echo $OUTPUT->confirm(get_string('deletecheck', 'block_kamaleon'),
                                new moodle_url($returnurl, $optionsyes), $returnurl);
        echo $OUTPUT->footer();
        die;
    } else if (data_submitted()) {

        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'block_kamaleon', 'banner', $content->id);
        foreach ($files as $file) {
            $file->delete();
        }

        $files = $fs->get_area_files($context->id, 'block_kamaleon', 'icon', $content->id);
        foreach ($files as $file) {
            $file->delete();
        }

        $DB->delete_records('block_kamaleon_contents', ['id' => $content->id]);

        $event = \block_kamaleon\event\content_deleted::create([
            'objectid' => $content->id,
            'context' => $context
        ]);
        $event->add_record_snapshot('block_kamaleon_contents', $content);
        $event->trigger();

        $msg = 'contentdeleted';
    }
}

if (!empty($msg)) {
    $msg = get_string($msg, 'block_kamaleon');
    echo $OUTPUT->notification($msg, 'notifysuccess');
}

echo html_writer::start_tag('div', ['class' => 'block_kamaleon-designs card']);
echo html_writer::start_tag('div', ['class' => 'card-body']);
$designform->display();
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');

$list = $DB->get_records('block_kamaleon_contents', ['instanceid' => $id], 'defaultweight ASC');

$renderable = new \block_kamaleon\output\contents($id, $list, $currentdesign, true);
$renderer = $PAGE->get_renderer('block_kamaleon');

echo html_writer::start_tag('div', ['class' => 'block_kamaleon']);
echo $renderer->render($renderable);
echo html_writer::end_tag('div');

echo html_writer::start_tag('div', ['class' => 'row buttons']);
echo html_writer::link('contentedit.php?instanceid=' . $id,
                        $OUTPUT->image_icon('t/add', 'core') . get_string('newcontent', 'block_kamaleon'),
                        ['class' => 'btn btn-primary']);
echo html_writer::end_tag('div');


echo $OUTPUT->footer();
