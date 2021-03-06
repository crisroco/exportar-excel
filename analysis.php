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
 * shows an analysed view of feedback
 *
 * @copyright Andreas Grabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package mod_feedback
 */

require_once("../../config.php");
require_once("lib.php");
global $DB;

$current_tab = 'analysis';

$id = required_param('id', PARAM_INT);  // Course module id.

$url = new moodle_url('/mod/feedback/analysis.php', array('id'=>$id));
$PAGE->set_url($url);

list($course, $cm) = get_course_and_cm_from_cmid($id, 'feedback');
require_course_login($course, true, $cm);

$feedback = $PAGE->activityrecord;
$feedbackstructure = new mod_feedback_structure($feedback, $cm);

$context = context_module::instance($cm->id);

if (!$feedbackstructure->can_view_analysis()) {
    print_error('error');
}

/// Print the page header

$PAGE->set_heading($course->fullname);
$PAGE->set_title($feedback->name);
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($feedback->name));

/// print the tabs
require('tabs.php');


//get the groupid
$mygroupid = groups_get_activity_group($cm, true);
groups_print_activity_menu($cm, $url);

// Show the summary.
$summary = new mod_feedback\output\summary($feedbackstructure, $mygroupid);
echo $OUTPUT->render_from_template('mod_feedback/summary', $summary->export_for_template($OUTPUT));

// Get the items of the feedback.
$items = $feedbackstructure->get_items(true);

$check_anonymously = true;
if ($mygroupid > 0 AND $feedback->anonymous == FEEDBACK_ANONYMOUS_YES) {
    $completedcount = $feedbackstructure->count_completed_responses($mygroupid);
    if ($completedcount < FEEDBACK_MIN_ANONYMOUS_COUNT_IN_GROUP) {
        $check_anonymously = false;
    }
}






echo '<div>';
if ($check_anonymously) {
  /*  echo"<pre>";
    print_r($items);
    //(condition) ? si : no,
    echo"<pre/>";*/
    // Print the items in an analysed form.
    foreach ($items as $item) {
        echo "<table class=\"analysis itemtype_{$item->typ}\">";
        $itemobj = feedback_get_item_class($item->typ);
        $printnr = ($feedback->autonumbering && $item->itemnr) ? ($item->itemnr . '.') : '';
        $itemobj->print_analysed($item, $printnr, $mygroupid);
        echo '</table>';
    }
} else {
    echo $OUTPUT->heading_with_help(get_string('insufficient_responses_for_this_group', 'feedback'),
                                    'insufficient_responses',
                                    'feedback', '', '', 3);
}
echo '</div>';
echo '<br /><div class="buttons">';
echo '<a href="' . new moodle_url('/mod/feedback/exportarXSLS.php?id=' . $id) . '">tmrrrrrrrrrrr</a>';
//echo $OUTPUT->single_button(new moodle_url("/mod/feedback/exportarXSLS.php?id=$id"), "''");
//echo $OUTPUT->single_button(new moodle_url('/mod/feedback/exportarXSLS.php'), "Exportar a excel");
echo '</div>';

//####################################
$questionid = $DB->get_records('feedback_item',array(),null,'id');


foreach ($questionid as $key=>$value) {
    //echo $value->id . "</br>";
    // etiquetas de pregunta
    //$espacio = 0;

    $questions = $DB->get_records('feedback_item', array('id' => $value->id), null, 'id, name');
    $data = array();
    foreach ($questions as $key => $value) {

       array_push($data, $value->name);
    }
    echo "</br>";
    print_r($data);

}

$encuesta=$DB->get_record('course_modules',array('id'=>$_GET['id']),'id,instance');
echo "</br>";
print_r($encuesta);
echo "</br>";
$lala= $encuesta->instance;
echo $lala;



echo  html_writer::start_tag('div');
echo  html_writer::link(new moodle_url('/mod/feedback/exportarXSLS.php?id=' . $id),'Exportar a excel',array('class'=>'btn btn-default' , 'target' => '_blank'));
echo html_writer::end_tag('div');



//echo $OUTPUT->footer();
