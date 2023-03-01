<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * course_module_list block.
 *
 * @package    block_course_module_list
 * @copyright  2023 Vaibhav <vaibhavsh.28@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_course_module_list extends block_base {


     /**
      * @var bool Flag to indicate whether the header should be hidden or not.
      */
    private $headerhidden = true;

    /**
     * Initializes class member variables.
     */
    public function init() {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_course_module_list');
    }


    function specialization() {
        // Page type starts with 'course-view' and the page's course ID is not equal to the site ID.
        if (strpos($this->page->pagetype, PAGE_COURSE_VIEW) === 0 && $this->page->course->id != SITEID) {
            $this->title = get_string('coursemodules', 'block_course_module_list');
            $this->headerhidden = false;
        }
    }
    /**
     * Sets the applicable formats for the block.
     *
     * @return string[] Array of pages and permissions.
     */
    function applicable_formats() {
        return array('all' => false, 'mod' => false, 'tag' => false, 'my' => false, 'course-view' => true);
    }

    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {
        global $DB, $CFG, $COURSE, $USER;
        if ($this->content !== null){
            return $this->content;
        }
        $this->content  = new stdClass;

        $coursemodules = $DB->get_records_sql("SELECT cm.id as coursemoduleid, m.name as modulename ,cm.instance as moduleinstance,date_format(from_unixtime(cm.added),'%d-%M-%Y') as timecreated, cmc.completionstate FROM mdl_course_modules cm  LEFT JOIN mdl_modules m on (m.id=cm.module) LEFT join mdl_course_modules_completion cmc ON (cm.id=cmc.coursemoduleid and cmc.userid=".$USER->id.") where cm.course=".$COURSE->id." and m.name!='forum'");
        $a = '<ul>';
        foreach($coursemodules as $coursemodule){
            $modname = $coursemodule->modulename;
            $activityname = $DB->get_record($modname, array('id' => $coursemodule->moduleinstance));
            $activitylink = $CFG->wwwroot.'/mod/'.$modname.'/view.php?id='.$coursemodule->coursemoduleid;
            $linkname = $coursemodule->coursemoduleid.'-'.$activityname->name.'-'.$coursemodule->timecreated;
            if($coursemodule->completionstate && $coursemodule->completionstate != 0){
                $linkname .= "-Completed";
            }
            $a .= '<li><a target="_blank" href="'.$activitylink.'">'.$linkname.'</a></li>';
        }
        $a .= '</ul>';
        $this->content->text = $a;
        $this->content->footer = '';
        return $this->content;
    }

    function hide_header() {
        return $this->headerhidden;
    }

}


