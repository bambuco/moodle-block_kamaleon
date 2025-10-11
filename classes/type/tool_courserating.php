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

namespace block_kamaleon\type;

require_once($CFG->dirroot . '/admin/tool/courserating/lib.php');

/**
 * Type tool_courserating
 *
 * @package    block_kamaleon
 * @copyright  2025 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_courserating extends base {

    /**
     * Get the contents for the custom type.
     *
     * @param int $instanceid The block instance id.
     * @param object $configdata The block configuration data.
     * @return array List of contents.
     */
    public function get_contents($instanceid, $configdata = null) : array {
        global $DB, $OUTPUT, $PAGE;

        $size = $configdata->maxrecords ?? 5;
        $summarylength = 100;

        $show = get_config('block_kamaleon', 'tool_courserating_show') ?? 'recent';

        if ($show === 'top') {
            $orderby = 'r.rating DESC, r.timemodified DESC';
        } else {
            $orderby = 'r.timemodified DESC';
        }

        $sql = 'SELECT r.id, r.rating, r.review, r.timemodified, r.timecreated,
                       c.id AS courseid, c.fullname AS coursename, c.shortname AS courseshortname,
                       u.id AS userid
                    FROM {tool_courserating_rating} r
                    INNER JOIN {course} c ON c.id = r.courseid
                    INNER JOIN {user} u ON u.id = r.userid
                    WHERE c.visible = 1 AND r.hasreview = 1 AND u.deleted = 0
              ORDER BY ' . $orderby;

        $ratings = $DB->get_records_sql($sql, [], 0, $size);

        $list = [];
        $dateformat = get_string('strftimedatefullshort', 'langconfig');
        $linkname = get_string('viewcourse', 'block_kamaleon');
        foreach ($ratings as $rating) {

            $user = $DB->get_record('user', ['id' => $rating->userid]);

            $summary = format_string($rating->review);

            if (strlen($summary) > $summarylength) {
                $summary = substr($summary, 0, $summarylength) . '...';
            }

            $image = '';

            // The user profile image.
            $userimagelink = $OUTPUT->user_picture($user, ['class' => 'userpicture', 'size' => 1]);
            $userpicture = new \core\output\user_picture($user);
            $userpicture->size = 512;
            $userpictureicon = new \core\output\user_picture($user);
            $userpictureicon->size = 1;

            $days = round((time() - $rating->timemodified) / 86400);
            if ($days < 1) {
                $shorttitle = get_string('today', 'calendar');
            } else if ($days < 2) {
                $shorttitle = get_string('yesterday', 'calendar');
            } else if ($days < 62) { // Approx 2 months.
                $shorttitle = get_string('daysago', 'block_kamaleon', (int)$days);
            } else if ($days < 731) { // Approx 2 years.
                $months = round($days / 30);
                $shorttitle = get_string('monthsago', 'block_kamaleon', (int)$months);
            } else {
                $shorttitle = userdate($rating->timemodified, $dateformat);
            }

            $userfullname = fullname($user);

            $stars = [];
            for ($i = 0; $i < 5; $i++) {
                $stars[] = $i < $rating->rating;
            }

            $content = new \block_kamaleon\content((object)[
                'id' => -1,
                'instanceid' => $instanceid,
                'shorttitle' => $shorttitle,
                'title' => format_string($rating->coursename),
                'subtitle' => $rating->rating,
                'url' => (string)new \moodle_url('/course/view.php', ['id' => $rating->courseid]),
                'target' => '_self',
                'linkname' => $linkname,
                'content' => $summary,
                'contentvars' => ['author' => $userfullname, 'rating' => $stars],
                'banner' => $userpicture->get_url($PAGE)->out(false),
                'icon' => $userpictureicon->get_url($PAGE)->out(false),
                'timecreated' => $rating->timecreated,
                'timemodified' => $rating->timemodified,
            ]);

            $list[] = $content;
        }


        return $list;
    }

    /**
     * Get the configuration form elements.
     *
     * @param object $configdata The block configuration data.
     * @return array List of setting elements.
     */
    public function get_config_form_elements($configdata = null): array {
        $elements = [];
        $options = [
            'top' => get_string('tool_courserating_show_top', 'block_kamaleon'),
            'recent' => get_string('tool_courserating_show_recent', 'block_kamaleon'),
        ];
        $elements[] = new \admin_setting_configselect('block_kamaleon/tool_courserating_show',
                                                    get_string('tool_courserating_show', 'block_kamaleon'),
                                                    get_string('tool_courserating_show_help', 'block_kamaleon'),
                                                    'top', $options
        );
        return $elements;
    }
}
