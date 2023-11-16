# moodle-report_coursemanager
Moodle report for helping teachers to clean and manage courses and contents

## Description ##

This report is intended for teachers as a tool for managing their courses. Based on native functions
as well as other functions specifically developed for this report, this tool aims to:
- To open up new possibilities that do not go against role permissions (Delete the course);
- To offer “shortcut” functions to clean your course (bulk unenrollment for cohorts, simplified reset, cleaning recommendations...);
- To offer tools to better visualize your data and encourage cleaning.

If offers for teachers:
- a complete report for teachers as a dashboard, listing all courses where user is enrolled as teacher ;
- automated taks for calculating reports and mailing these reports for teachers ;

The plugin is completed with special tools for admins:
- course report for courses without teachers;
- files distribution depending on their components, directly from table files;
- special tool to reset assigns from submissions uploaded by unenrolled users. 

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/report/coursemanager

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## Who to give access to Course Manager dashboard for teachers ##
The complete dashboard is accessible by giving direct access link to view.php file. 

To provide this access, the easiest way is to add an item in the main navigation using the custom menu items
in Site administration > Presentation > Theme settings and adding a link pointing to /report/coursemanager/view.php

The menu is then displayed for all users, teachers and students alike. However, the report will only show data if
the user who consults it is registered as Teacher in at least one course.

## Displays according to themes ##
Course Manager report has been tested for Moodle 4.0 with several templates.

Reports display has been tested with templates Almonbd, Boost, Boost Union, Classic, Moove et Trema.
Templates Academi, Degrade, Eguru et Klass : icon display next to title works, although icons are slightly shifted.
Template Adaptable : icon display next to title doesn't work. The zone called by JS doesn't exist in this theme.

## License ##

2022 Olivier VALENTIN

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.
