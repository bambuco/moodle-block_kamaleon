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
 * Class containing the blog type source.
 *
 * @package   block_kamaleon
 * @copyright 2023 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_kamaleon\type;

require_once($CFG->dirroot . '/blog/locallib.php');

/**
 * Type blog.
 *
 * @copyright 2023 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class blog extends base {

    /**
     * Get the contents for the custom type.
     *
     * @param int $instanceid The block instance id.
     * @param object $configdata The block configuration data.
     * @return array List of contents.
     */
    public function get_contents($instanceid, $configdata = null) : array {
        global $DB, $OUTPUT;

        $size = $configdata->maxrecords ?? 5;
        $summarylength = $configdata->customparams['contentlength'] ?? 100;
        $params = ['module' => 'blog', 'courseid' => 0, 'publishstate' => 'public'];
        $posts = $DB->get_records('post', $params, 'created DESC', '*', 0, $size);

        $postlist = [];
        foreach ($posts as $post) {
            $blogentry = new \blog_entry(null, $post);
            $blogentry->prepare_render();

            $summary = format_text($post->summary, $post->summaryformat, ['context' => \context_system::instance()]);

            if (!empty($summarylength)) {
                if (strlen($summary) > $summarylength) {
                    $summary = substr($summary, 0, $summarylength) . '...';
                }
            }

            $image = '';
            // Find the first attached image.
            if ($blogentry->renderable->attachments) {
                $imgexts = ["gif", "jpg", "jpeg", "png", "svg"];
                foreach ($blogentry->renderable->attachments as $attachment) {
                    $m = explode('.', $attachment->url);
                    $ext = strtolower(trim(end($m)));
                    if (in_array($ext, $imgexts)) {
                        $image = $attachment->url;
                        break;
                    }
                }
            }

            $subtitle = '';
            // Get post tags used as subtitle.
            $tags = \core_tag_tag::get_item_tags('core', 'post', $post->id);
            if ($tags) {
                $taglist = [];
                foreach ($tags as $tag) {
                    $taglist[] = $tag->rawname;
                }
                $subtitle = implode(', ', $taglist);
            }

            $shorttitle = userdate($post->lastmodified, get_string('strftimedateshortmonthabbr', 'langconfig'));
            $userpicture = $OUTPUT->user_picture($blogentry->renderable->user, ['class' => 'userpicture']);

            $content = new \block_kamaleon\content((object)[
                'id' => -1,
                'instanceid' => $instanceid,
                'shorttitle' => $shorttitle,
                'title' => $post->subject,
                'subtitle' => $subtitle,
                'url' => (string)(new \moodle_url('/blog/index.php', ['entryid' => $post->id])),
                'target' => '_self',
                'content' => $summary,
                'contentvars' => [
                    'author' => $userpicture . fullname($blogentry->renderable->user),
                    'date' => userdate($post->created, get_string('strftimedate', 'langconfig')),
                ],
                'banner' => $image,
                'timecreated' => $post->created,
                'timemodified' => $post->lastmodified,
            ]);

            $postlist[] = $content;
        }


        return $postlist;
    }
}
