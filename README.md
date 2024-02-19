# moodle-report_coursemanager
Moodle report for helping teachers to clean and manage courses and contents

## Description ##

Course Manager is mainly adressed to teachers : it gives them a tool to manage their courses (shortcuts, trash, unenroll cohorts…) and reports concerning their courses : weight, empty courses, no students enrolled…
For admins, Course Manager gives some special tools, with the same goal : clean Moodle from courses problems : orphan submissions, courses without teachers…

The main goal is to raise awarness among teachers of what they store in their courses, if their courses are alive or not… and encourage them to clean Moodle by :

-    opening functions which don’t  go against system permissions (such a trash category) ;
-    giving shortcuts function to clean course (reports, bulk uneroll cohorts, simple reinit) ;
-    offering visualization tools to encourage to clean courses.

## Teacher dashboard ##

This tool offers a view of every course a teacher is enrolled in. Each course display informations (visible course, weight, number of studens and teachers enrolled…), reports list, and offers tool such as shortcuts to enroll functions, put course in trash category…

## Which reports are calculated ? ##
An automatic task running at night will calculate reports that will be shown to teachers in dashboard and in each course if chosen :
-	Heavy courses : fix a limit for what will be considered as heavy (in Mo)
-	Empty courses : courses with only native forum
-	No students : courses wihtout any students enrolled
-	No visits : courses with no visits by students, but by teachers too !
-	Orphan submissions : warns teachers if some assigns contain submissions from unenrolled students.

This plugin offers too a mailing list that will send every month a complete report to each teacher, and remind them courses concerned by those reports.

## Which reports for admin ? ##

Three special tools are available for admins in Site admin > Report section :
-	Filearea report : calculates files weight according to their file area, that can help to detect problems.
-	Orphan submission : this report offers possibility to clean assigns from submissions uploaded by enrolled students in a course which is not reinitialized. Those files can represent a very heavy weight !
-	Courses without teachers : this report lists all courses without teachers, and offers a tool to move them in trash category.

## Settings ##

This plugin offers possibility to set every threshold that will trigger a report :
-	Limit weight for a file or course ;
-	Roles concerned (teacher and student)
-	Number of days without visit for teachers and students

Admin can decide too if reports will be pushed in each course (next to title or under title).

## How to give access to teacher dashbord ? ##

To provide this access, the easiest way is to add an item in the main navigation using the custom menu items
in Site administration > Presentation > Theme settings and adding a link pointing to /report/coursemanager/view.php

The menu is then displayed for all users, teachers and students alike. However, the report will only show data if
the user who consults it is registered as Teacher in at least one course.

Reports display has been tested with several Moodle templates such as Almonbd, Boost, Boost Union, Classic, Moove and Trema.
Themes Academi, Degrade, Eguru and Klass : the icon display mode works, showing a small gap for icons
Theme Adaptable : icon display mode doesn’t work, because targetted zone doesn’t exist in this template.

## Installing via uploaded ZIP file ##

Log in to your Moodle site as an admin and go to Site administration > Plugins > Install plugins.
Upload the ZIP file with the plugin code. You should only be prompted to add extra details if your plugin type is not automatically detected.
Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

{your/moodle/dirroot}/report/coursemanager

Afterwards, log in to your Moodle site as an admin and go to Site administration > Notifications to complete the installation.

Alternatively, you can run

php admin/cli/upgrade.php

to complete the installation from the command line.

## License ##

2022 Olivier VALENTIN

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
