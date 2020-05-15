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
 * Renderer for use with the aliases output
 *
 * @package    core
 * @subpackage aliases
 * @copyright  2012 onwards Totara Learning Solutions Ltd {@link http://www.totaralms.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Yuliya Bozhko <yuliya.bozhko@totaralms.com>
 */

require_once($CFG->libdir.'/aliaslib.php');
require_once($CFG->libdir.'/tablelib.php');

class local_alias_renderer extends plugin_renderer_base {

    /**
     * @param \local_alias\output\alias_collection $aliases array
     * return HTML generater
     */
    public function render_alias_collection(local_alias\output\alias_collection $aliases){
        $paging = new paging_bar(
            $aliases->totalcount,
            $aliases->page,
            $aliases->perpage,
            $this->page->url,
            'page'
        );
        $htmlpagingbar = $this->render($paging);
        $table = new html_table();
        $table->attributes['class'] = 'collection';
        $table->head = $aliases->titlecolumn;
        $table->colclasses = $aliases->titlecolumn;
        foreach($aliases->records as $alias){
            $table->data[] = array(
                html_writer::link(new moodle_url('edit.php', array('id' => $alias->id), null), $alias->id),
                $alias->friendly,
                $alias->destination
            );
        }
        $htmltable = html_writer::table($table);
        return $htmltable . $htmlpagingbar;
    }
}