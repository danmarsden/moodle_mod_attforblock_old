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
 * Prints attendance info for particular user
 *
 * @package    mod
 * @subpackage attforblock
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/locallib.php');

$pageparams = new att_view_page_params();

$id                     = required_param('id', PARAM_INT);
$pageparams->studentid	= optional_param('studentid', NULL, PARAM_INT);
$pageparams->mode 		= optional_param('mode', att_view_page_params::MODE_THIS_COURSE, PARAM_INT);
$pageparams->view       = optional_param('view', NULL, PARAM_INT);
$pageparams->curdate	= optional_param('curdate', NULL, PARAM_INT);

$cm             = get_coursemodule_from_id('attforblock', $id, 0, false, MUST_EXIST);
$course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$attforblock    = $DB->get_record('attforblock', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

$pageparams->init($cm);
$att = new attforblock($attforblock, $cm, $course, $PAGE->context, $pageparams);

// Not specified studentid for displaying attendance?
// Redirect to appropriate page if can
if (!$pageparams->studentid) {
    if ($att->perm->can_manage() || $att->perm->can_take() || $att->perm->can_change()) {
        redirect($att->url_manage());
    }
    elseif ($att->perm->can_view_reports()) {
        redirect($att->url_report());
    }
}

$att->perm->require_view_capability();

$PAGE->set_url($att->url_view());
$PAGE->set_title($course->shortname. ": ".$att->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);
$PAGE->navbar->add(get_string('attendancereport', 'attforblock'));

$output = $PAGE->get_renderer('mod_attforblock');

$userid = isset($pageparams->studentid) ? $pageparams->studentid : $USER->id;
$userdata = new attforblock_user_data($att, $userid);

echo $output->header();

echo $output->render($userdata);

echo $output->footer();
