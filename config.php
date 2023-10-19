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
 * Config for Lyon 3 template and Teacher Courses Dashboard.
 *
 * @package    report_coursemanager
 * @copyright  2022 Olivier VALENTIN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die();

$THEME->name = 'coursemanager';                                                                                                             
$THEME->sheets = [];                                                                                                                                                          
$THEME->editor_sheets = [];                                                                                                         
$THEME->parents = ['boost'];                                                                                                        
$THEME->enable_dock = false;                                                                                                        
$THEME->yuicssmodules = array();                                                                                                    
$THEME->rendererfactory = 'theme_overridden_renderer_factory';                                                                      
$THEME->requiredblocks = '';   
$THEME->addblockposition = BLOCK_ADDBLOCK_POSITION_FLATNAV;
$THEME->scss = function($theme) {                                                                                                   
    return report_coursemanager_get_main_scss_content($theme);                                                                               
};