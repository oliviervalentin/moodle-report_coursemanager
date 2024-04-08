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
 * Course Manager plugin strings.
 *
 * @package     report_coursemanager
 * @copyright   2022 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Course manager';
$string['title'] = 'Gestion des cours - Enseignants';

// Settings pages.
$string['configtitle'] = 'Course Manager settings';
$string['category_bin'] = 'Catégorie corbeille';
$string['teacherroledashboard'] = 'Rôle enseignant dans les cours';
$string['teacherroledashboard_desc'] = 'Définit le rôle permettant d\'afficher la liste des cours dans le tableau de bord. Par défaut, il s\'agit du rôle Enseignant de Moodle.';
$string['studentrolereport'] = 'Rôle étudiant dans les cours';
$string['studentrolereport_desc'] = 'Définit le rôle étudiant dans les cours pour le calcul des rapports. Par défaut, il s\'agit du rôle Etudiant de Moodle.';
$string['category_bin_desc'] = 'Si un enseignant demande la suppression de son cours, le cours sera déplacé dans cette catégorie avant suppression définitive par les gestionnaires de la plateforme.';
$string['total_filesize_threshold'] = 'Poids maximum d\'un cours';
$string['total_filesize_threshold_desc'] = 'Poids en Mo à partir duquel on souhaite afficher une alerte à l\'enseignant si le <b>total des fichiers du cours</b> dépasse ce seuil.';
$string['unique_filesize_threshold'] = 'Poids maximum d\'un fichier';
$string['unique_filesize_threshold_desc'] = 'Poids en Mo à partir duquel on souhaite afficher une alerte à l\'enseignant si <b>un fichier dans une activité</b> dépasse ce seuil..';
$string['last_access_teacher'] = 'Seuil dernière visite enseignant';
$string['last_access_teacher_desc'] = 'Nombre de jours sans visite d\'un enseignant à partir duquel on affiche une alerte.';
$string['last_access_student'] = 'Seuil dernière visite étudiant';
$string['last_access_student_desc'] = 'Nombre de jours sans visite d\'un étudiant à partir duquel on affiche une alerte.';
$string['delete_period'] = 'Période pour la suppression des cours';
$string['delete_period_desc'] = 'Affiche la période approximative de suppression des cours de la catégorie corbeille. Inscrire un texte comme "début juillet", ou "dans la première quinzaine de mai" pour l\'intégrer à la description de la page de suppression.';
$string['delete_send_mail'] = 'Rôles à prévenir lors de la suppression d\'un cours';
$string['delete_send_mail_desc'] = 'Sélectionnez le ou les rôles qui seront avertis par mail lors de la suppression d\'un cours';
$string['show_report_in_course'] = 'Affichage des rapports dans les cours';
$string['show_report_in_course_desc'] = 'Définit si les rapports sont affichés dans les cours et à quel endroit.';
$string['show_report_in_course_choices_none'] = 'Ne pas afficher';
$string['show_report_in_course_choices_collapse'] = 'Menu déroulant sous la barre d\'admin';
$string['show_report_in_course_choices_popover'] = 'Icônes à côté du titre';
$string['enablemailing'] = 'Activer l\'envoi du mailing aux enseignants';
$string['enablemailing_desc'] = 'Si coché, active l\'envoi des rapports de cours à chaque enseignant. La périodicité du mailing est définie par défaut
tous les 30 jours, et peut être modifiée dans les tâches programmées.';
$string['mailingintro_setting'] = 'Introduction du mail';
$string['mailingintro_setting_desc'] = 'Message d\'introduction pour le mailing envoyé aux enseignants.<br>
Vous pouvez utiliser les variables suivantes :<br>
%userfirstname% : prénom de l\'utilisateur<br>
%userlastname% : nom de l\'utilisateur<br>
%coursemanagerlink% : lien vers le gestionnaire de cours';

// Banner for courses in categorby bin.
$string['trash'] = 'Ce cours est dans la catégorie à supprimer.';

// Dashboard.
$string['table_course_name'] = 'Nom du cours';
$string['table_course_state'] = 'Etat';
$string['table_files_weight'] = 'Poids des fichiers';
$string['table_enrolled_cohorts'] = 'Cohortes';
$string['table_enrolled_students'] = 'Etudiants';
$string['table_enrolled_teachers'] = 'Enseignants';
$string['table_recommendation'] = 'Recommandations';
$string['table_actions'] = 'Actions';
$string['empty_settings'] = 'Certains paramètres du plugin sont manquants ; contactez l\'administrateur Moodle';

// Actions.
$string['menudeletecourse'] = 'Mettre à la corbeille';
$string['menucoursefilesinfo'] = 'Voir les fichiers';
$string['menureset'] = 'Réinitialiser';
$string['menuenrolcohorts'] = 'Ajouter des cohortes';
$string['menuunenrolcohorts'] = 'Désinscrire les cohortes';
$string['menucourseparameters'] = 'Paramètres du cours';
$string['menurestorecourse'] = 'Restaurer le cours';

// Alerts and reports on dashboard.
$string['course_state_visible'] = 'Visible';
$string['course_state_hidden'] = 'Caché';
$string['course_state_trash'] = 'Corbeille';
$string['see_advices'] = 'Résumé des recommandations';
$string['advices_for_course'] = 'Préconisations pour le cours ';
$string['total_filesize_alert'] = '<b>Le poids de ce cours est important.</b><br />Nous vous invitons à consulter <a href="course_files.php?courseid={$a->courseid}">l\'interface de gestion des fichiers de ce cours</a> pour voir les éléments les plus lourds, et éventuellement y apporter des solutions.';
$string['empty_course_alert'] = '<b>Ce cours semble vide.</b><br />Actuellement, ce cours ne contient que le forum natif de Moodle. Si ce cours n\'a pas vocation à évoluer, nous vous invitons à le supprimer.';
$string['last_access_multiple_teacher_alert'] = '<b>Pas de visites d\'enseignants depuis plus de {$a->limit_visit} mois.</b><br />Aucun  enseignant inscrits n\'a visité ce cours durant cette période. Si personne ne l\'utilise, pensez à le supprimer.';
$string['last_access_unique_teacher_alert'] = '<b>Vous n\'avez pas visité ce cours depuis plus de {$a->limit_visit} mois.</b><br />Aucun autre enseignant n\'y figure. Si vous n\'utilisez plus ce cours, pensez à le supprimer.';
$string['last_access_student_alert'] = '<b>Pas de visites d\'étudiants depuis plus de {$a->limit_visit} mois.</b><br />Aucun étudiant inscrit à votre cours n\'a visité ce cours durant cette période. Si ce cours n\'a plus d\'utilité , pensez à le supprimer.';
$string['empty_student_alert'] = '<b>Pas d\'étudiants inscrits.</b><br />Aucun utilisateur n\'est inscrit dans ce cours avec le rôle Étudiant. Si ce cours n\'est pas utilisé, pensez à le supprimer.';
$string['orphan_submissions_alert'] = '<b>Il y a des devoirs "orphelins" représentant {$a->filesize} Mo.</b><br />Actuellement, ce cours contient un ou plusieurs devoirs avec des dépôts invisibles, déposés par des étudiants désinscrits du cours. Ces fichiers peuvent représenter un poids important, pensez à réinitialiser le devoir ou à supprimer l\'activité .';
$string['no_advices'] = '<b>Pas de préconisations spécifiques.</b><br />Il n\'y a pas de remarques particulières sur ce cours quant à son poids ou son utilisation.';
$string['no_course_to_show'] = '<h2>Pas de cours</h2>Vous n\'êtes inscrit dans aucun cours avec un rôle permettant de l\'administrer avec cette interface.';
$string['closereportmodal'] = 'Fermer la fenêtre de rapport';

// Page for course deletion.
$string['title_move_confirm'] = 'Demande de suppression du cours';
$string['move_confirm'] = '
<div class="alert alert-danger"><h5>Lisez attentivement les indications ci-dessous !</h5></div>
<p><b>Cette page vous permet de demander la suppression du cours que vous n\'utilisez plus.</b><br/>Si vous confirmez la suppression, le cours sera dans un premier temps caché aux étudiants, puis déplacé dans une catégorie de Moodle servant de corbeille. Il sera ensuite supprimé par les administrateurs Moodle <b>{$a->delete_period}</b>.</p>

<p> Notez que vous aurez toujours accès à ce cours avant sa suppression définitive, si vous souhaitez récupérer des éléments. De même, vous aurez également toujours la possibilité de restaurer le cours hors de la corbeille depuis l\'interface de gestion de vos cours.</p>

<p> Avant de mettre votre cours à la corbeille, nous vous conseillons  de sauvegarder si besoin les fichiers contenus dans le cours ainsi que la banque de questions. <b>Une fois votre cours supprimé, ces éléments seront définitivement perdus et non récupérables.</b></p><br />
';
$string['delete_several_teachers'] = '<h4><i class="fa fa-exclamation-triangle"></i> ATTENTION ! D\'AUTRES ENSEIGNANTS SONT INSCRITS DANS CE COURS !</h4>
Si vous n\'avez pas informé vos collègues de la suppression de ce cours, nous vous invitons à le faire dès à présent.<br/>La demande de suppression d\'un cours enverra en effet une <b>alerte
à tous les enseignants listés ci-dessous</b> pour leur signifier que vous avez demandé à supprimer ce cours.<br /><br />
<h5>Autres enseignants de ce cours</h5>';
$string['delete_wish'] = 'Que souhaitez-vous faire ?';
$string['button_move_confirm'] = 'Confirmer la suppression';
$string['button_save_questionbank'] = 'Sauvegarder la banque de questions';
$string['button_save_course'] = 'Sauvegarder tout le cours';
$string['mail_subject_delete'] = 'Cours supprimé - {$a->course}';
$string['mail_message_delete_oneteacher'] = 'Bonjour,<br />
Le cours {$a->course} a été déplacé dans la catégorie Corbeille, en attente de sa suppression définitive qui interviendra {$a->delete_period}. Dans l\'intervalle, ce cours reste accessible, notamment si vous souhaitez y récupérer des ressources.<br />
Si vous souhaitez le conserver, vous pouvez le restaurer hors de la corbeille depuis l\'interface de gestion de vos cours.';
$string['mail_message_delete_main_teacher'] = 'Bonjour,<br />
Le cours {$a->course} a été déplacé dans la catégorie Corbeille, en attente de sa suppression définitive qui interviendra {$a->delete_period}. Dans l\'intervalle, ce cours reste accessible, notamment si vous souhaitez y récupérer des ressources.<br />
Si vous souhaitez le conserver, vous pouvez le restaurer hors de la corbeille depuis l\'interface de gestion de vos cours.<br />
NOTE : ce cours contenait {$a->count_teacher} autres utilisateurs inscrits comme Enseignants. Un message leur a également été adressé pour leur indiquer que vous
êtes à l\'origine de cette suppression. Etant également enseignants, ces personnes pourront également restaurer ce cours ou y récupérer des ressources.';
$string['mail_message_delete_other_teacher'] = 'Bonjour,<br />
Le cours {$a->course}, dans lequel vous êtes inscrit⋅e comme enseignant⋅e, a été déplacé dans la catégorie Corbeille par {$a->deleter}, en attente de sa suppression définitive qui interviendra {$a->delete_period}. Dans l\'intervalle, ce cours reste accessible, notamment si vous souhaitez y récupérer des ressources.<br />
Si vous souhaitez le conserver, vous pouvez le restaurer hors de la corbeille depuis l\'interface de gestion de vos cours, sous réserve de prévenir les autres enseignants.<br />';
$string['delete_already_moved'] = 'Ce cours est déjà déplacé dans la catégorie Corbeille.';

// Page - Course restore.
$string['title_restore_confirm'] = 'Restauration d\'un cours de la Corbeille';
$string['restore_confirm'] = '<p>Cette page vous permet de restaurer un cours initialement prévu pour être supprimé, en le déplaçant
hors de la catégorie Corbeille de Moodle.</p>
<p class="alert alert-info"><i class="fa fa-info-circle"></i> NOTE : ne restaurez pas votre cours dans la catégorie <b>"{$a->trash_category}"</b> : cette catégorie sert précisément de corbeille et son contenu sera définitivement supprimé <b>{$a->delete_period}</b>.</p>';
$string['button_restore_confirm'] = 'Confirmer la restauration';
$string['restore_already_moved'] = 'Ce cours n\'est pas dans la catégorie Corbeille !.';
$string['error_category'] = 'Mauvaise catégorie sélectionnée';
$string['select_restore_category'] = 'Choisissez la catégorie de restauration du cours';

// Page - Files information.
$string['coursesize'] = 'Taille du cours';
$string['coursereport'] = 'EXPLICATIONS - A venir !';
$string['totalsize'] = 'Poids total des fichiers du cours : ';
$string['watchedfilessize'] = 'Poids des fichiers les plus surveillés : ';
$string['watchedfilessizedetails'] = 'Ces fichiers les plus surveillés proviennent des activités les plus utilisées : Dossier, Fichier, Forum, Devoir et Étiquette.';
$string['plugin'] = 'Type d\'activité';
$string['size'] = 'Poids en Mo';
$string['comment'] = 'Commentaires et conseils';
$string['number_of_files'] = 'Nombre de fichiers';
$string['warn_heavy_assign'] = 'Les devoirs suivants représentent un poids important de fichiers :';
$string['warn_heavy_assign_help'] = '<b>Pensez à surveiller ce devoir</b> : lorsque les dépôts des étudiants auront été récupérés, n\'oubliez pas de supprimer cette activité
ou de réinitialiser votre cours afin de ne pas l\'encombrer.';
$string['empty_files_course'] = 'Ce cours ne semble pas contenir de fichiers actuellement. Aucune action n\'est à envisager.';
$string['warn_big_files'] = 'Les fichiers suivants présentent un <b>poids importants</b> :';
$string['warn_big_files_help'] = 'Si le poids d\'un fichier est important, voici quelques conseils pour le réduire, ou atténuer son impact sur Moodle :<ul>
<li>zippez votre fichier ;</li>
<li>s\'il contient des images, réduisez leur résolution pour dimuner leur taille ;</li>
<li>déposez votre fichier sur le repository OwnCloud ;</li>
<li></li>
</ul>';
$string['warn_videos'] = 'Les fichiers suivants sont des <b>vidéos</b>, pensez à les déplacer :';
$string['warn_videos_help'] = 'Les vidéos doivent de préférence être d\'abord déposées sur la WebTV de l\'université, puis insérées dans votre cours
à l\'aide de l\'activité UbiCast. Pour plus d\'informations, consultez le tutoriel d\'aide de la WebTV.';
$string['warn_orphans'] = 'Les devoirs suivants comportant des <b>fichiers orphelins</b> :';
$string['warn_orphans_help'] = '<p>Si un étudiant a déposé un devoir mais qu\'il n\'est plus inscrit dans le cours, son fichier n\'apparaitra plus dans
l\'interface de l\'activité, mais en réalité il est toujours présent !</p>
<p>Pour y remédier, vous devez : <ul>
<li>soit supprimer l\'activité Devoir contenant les fichiers orphelins ;</li>
<li>soit réinitialiser l\'ensemble de l\'activité Devoir pour tout votre cours.</li></ul></p>';
$string['global_chart'] = 'Répartition des poids de fichiers par activité';
$string['warn_recyclebin'] = '<p class="alert alert-info"><i class="fa fa-info-circle"></i> <b>Attention</b> : la Corbeille de cours est activée sur ce site. Si vous
avez supprimé des fichiers, ils seront comptabilisés tant que la corbeille n\'est pas vidée par un enseignant ou par la tâche synchronisée de Moodle.</p>';

// Page for global reset.
$string['reset_info'] = '<p class="alert alert-success"><i class="fa fa-info-circle"></i> La fonction de réinitialisation d\'un cours permet de supprimer une série de données personnelles rattachées à votre cours. <b>La réinitialisation ne supprimera pas vos activités ou ressources disponibles dans votre cours</b>. Elle sert uniquement à retirer certaines données de votre cours.</p>
<p>En lançant la réinitialisation depuis cette page, cela entraînera la suppression :<ul>
<li>des <b>suivis d\'achèvements</b> des ressources et activités ;</li>
<li>des <b>notes dans le carnet de notes</b> ;</li>
<li>des <b>groupes et groupements</b> ;</li>
<li>des <b>dépôts étudiants dans les activités Devoir</b> ;</li>
<li>des <b>messages de forum</b> ;</li>
<li>des <b>tentatives dans les Tests</b> ;</li>
<li> des <b>cohortes inscrites</b> à votre cours (<span class="text-danger"><b>ATTENTION</b> : la fonction peut prendre un certain temps suivant le nombre de cohortes et d\'étudiants inscrits !).</span></li></ul></p>
<p>Aucune autre données ne sera supprimée.</p>
<p>Si vous ne souhaitez pas réinitialiser tous ces éléments ou si vous souhaitez vider d\'autres contenus (wiki, bases de données...), il vous est
 possible de <a href="/course/view.php?id="><b>sélectionner les éléments que vous souhaitez réinitialiser</b></a> depuis la fonction disponible dans votre cours.<br />
Vous pouvez également choisir de <b>désinscrire uniquement vos cohortes</b> sans toucher aux contenus. Il est à noter que désinscrire des étudiants fait également disparaître
 les données des étudiants.</p>
<h5 class="alert alert-primary"><i class="fa fa-question-circle-o"></i> <b>Que souhaitez-vous faire ?</b></h5>
';
$string['reset_result'] = '<p><b>Votre cours a été réinitialisé.</b></p>
<p><b>Rappel</b> : aucune ressource ou activité n\'a été supprimée. Seules les données personnelles des activités devoirs, quiz et forum, ainsi que les notes et
 les cohortes ont été retirées. Si vous souhaitez réinitialiser d\'autres activités (wiki, feedback, base de données...), utilisez la fonction de réinitialisation
 depuis votre cours.</p>';

// Page of bulk unenroll cohorts.
$string['title_delete_cohort_confirm'] = 'Désinscription des cohortes';
$string['delete_cohort_confirm'] = 'A l\'aide de cette fonction, vous avez la possibilité de désinscrire les étudiants en supprimant les cohortes invitées dans votre cours.<br />
Pour rappel, la suppression des cohortes fera également disparaître les données des étudiants telles que les messages de forum ou les notes.<br />
<b>IMPORTANT : </b> désinscrire les cohortes ne supprime pas les devoirs déposés, tentatives de QCM etc. qui peuvent
représenter une place importante dans votre cours. Pensez à également réinitialiser votre cours !
<p class="alert alert-primary"><i class="fa fa-question-circle-o"></i> <b>ATTENTION :</b> selon le nombre de cohortes à désinscrire, ce processus peut prendre du temps.</p>
';
$string['button_delete_cohort_confirm'] = 'Désinscrire les cohortes';
$string['no_cohort'] = '<p class="alert alert-info">Aucune cohorte n\'est actuellement inscrite dans ce cours !</p>';

// Admin dashboard page.
$string['admin_course_managerinfo'] = '<p>Cette page donne accès aux différents outils du plugin Course Manager réservés aux administrateurs Moodle. Pour accéder aux paramètres du plugin, rendez-vous dans Plugins > Rapports > Course Manager.</p>';
$string['table_tool_name'] = '<p>Outil</p>';
$string['table_tool_description'] = '<p>Description</p>';

// Admin page for orphan submissions.
$string['title_admin_orphan_submissions'] = '<b>Gestion des dépôts de devoirs orphelins</b>';
$string['table_assign_name'] = 'Nom du devoir';
$string['table_files_count'] = 'Nombre de fichiers cachés';
$string['admin_orphan_submissions_info'] = '<p>Les devoirs orphelins désignent des activités Devoir contenant des dépôts effectués par un utilisateur qui n\'est plus incrit dans le cours.
 <b>Ces dépôts sont alors invisibles pour les enseignants, qui ne peut les faire apparaître qu\'en réinscrivant les utilisateurs concernés.</b> Si une activité Devoir est réutilisée chaque année
 sans réinitialiser le cours, le poids de ces fichiers peut rapidement devenir important.<br />
 Cette page permet de supprimer ces devoirs invisibles <b>sans toucher aux devoirs déposés par des utilisateurs actuellement inscrits au cours.</b></p>';
$string['deleteorphansubmissionsconfirm'] = 'Souhaitez-vous supprimer définitivement les dépôts orphelins de ce devoir ? Cette action est irréversible.';
$string['noassign'] = 'Il n\'y a aucune activité Devoir dans les cours de cette plateforme.';
$string['deleteorphans'] = 'Supprimer les devoirs orphelins';

// Admin page for files distribution in files table.
$string['title_admin_files_distribution'] = '<b>Répartition des fichiers par zones</b>';
$string['admin_files_distribution_info'] = '<p>Cet outil offre un aperçu des différentes zones de stockage de la table des fichiers, et le poids total des fichiers qui y sont rattachés.
 Il permet ainsi de détecter une éventuelle anomalie, sur une zone anormalement chargée, ou d\'avoir un simple panorama des zones les plus sollicitées.</p>';
$string['filesdistributiontablecomponent'] = 'Composant';
$string['filesdistributiontotalweight'] = 'Poids total des fichiers en Mo';
$string['filesdistributiontotalfiles'] = 'Nombre de fichiers';

// Admin page for courses without teachers.
$string['title_admin_no_teacher_courses'] = '<b>Gestion des cours sans enseignants</b>';
$string['admin_no_teacher_courses_info'] = '<p>Les cours peuvent se retrouver sans enseignant si la personne qui détenait ce rôle se désinscrit intentionnellement ou non du cours,
 ou si son compte n\'existe plus. Cet outil permet de recenser les cours concernés, et éventuellement de les basculer dans la catégorie Cours sans enseignants.</p>';
$string['adminnoteachercoursesnote'] = '<ul class="alert alert-info"><li>Les informations concernant le poids du cours et le nombre de modules ne sont pas calculés en temps réel,
 mais proviennent des rapports automatiques.</li><li>Les informations concernant le dernier log enseignant sont déduites à partir du champ edulevel de la table des
 logs et sont à titre indicatif. Les permissions modifiées dans un cours peuvent fausser ce résultat.</li></ul>';
$string['tablecountenrolledstudents'] = 'Etudiants';
$string['tablelastaccess'] = 'Dernier accès au cours';
$string['tablehascontents'] = 'Nombre de contenus du cours';
$string['tablecourseweight'] = 'Poids du cours';
$string['tablecountmodules'] = 'Nombre de modules';
$string['tablelastteacherlog'] = 'Dernier log enseignant';
$string['tablelastteacher'] = 'Dernier enseignant actif';
$string['deletecoursewithoutteachersconfirm'] = 'Souhaitez-vous déplacer ce cours dans la catégorie Corbeille définie par Course Manager ?';
$string['emptytablenoteacherincourses'] = 'Pas de résultats : tous les cours contiennent au moins un enseignant';

// Admin page for statistics.
$string['title_admin_stats'] = '<b>Statistiques</b>';
$string['admin_stats_info'] = '<p>Cette page propose des statistiques généralement basée sur les rapports produits par Course Manager.
Il propose également des informations concernant certains aspects étudiés par le plugin, comme les courses sans enseignants ou le nombre de cours dans la corbeille.</p>';
$string['stats_title_courses'] = 'Statistiques des cours';
$string['stats_count_courses'] = 'Nombre de cours';
$string['stats_count_courses_desc'] = 'Nombre total de cours Moodle sur votre instance.';
$string['stats_count_courses_trash'] = 'Cours à la corbeille';
$string['stats_count_courses_trash_desc'] = 'Nombre de cours se trouvant dans la catégorie Corbeille de Course Manager.';
$string['stats_weight_courses_trash'] = 'Poids de la corbeille';
$string['stats_weight_courses_trash_desc'] = 'Poids total des fichiers des cours de la catégorie Corbeille de Course Manager.';
$string['stats_title_contents'] = 'Contenus et poids des cours';
$string['stats_heavy_courses'] = 'Cours lourds';
$string['stats_heavy_courses_desc'] = 'Cours pesant plus de {$a->totalfilesizethreshold} Mo (limite définie dans les paramètres de Course Manager).';
$string['stats_empty_courses'] = 'Cours vides';
$string['stats_empty_courses_desc'] = 'Nombre de cours contenant uniquement le forum des annonces.';
$string['stats_courses_orphan_submissions'] = 'Cours contenant des devoirs orphelins';
$string['stats_courses_orphan_submissions_desc'] = 'Nombre de cours avec des Devoirs contenant des fichiers déposés par des étudiants désinscrits.';
$string['stats_weight_courses_orphan_submissions'] = 'Poids des devoirs orphelins';
$string['stats_weight_courses_orphan_submissions_desc'] = 'Somme des fichiers considérés comme devoirs orphelins';
$string['stats_heaviest_course'] = 'Cours le plus lourd';
$string['stats_heaviest_course_desc'] = 'Cours pesant le plus lourd sur votre instance d\'après les rapports Course Manager.';
$string['stats_title_enrolls_visits'] = 'Inscriptions et visites';
$string['stats_count_courses_without_teachers'] = 'Cours sans enseignants';
$string['stats_count_courses_without_teachers_desc'] = 'Nombre de cours sans utilisateurs inscrits comme enseignant';
$string['stats_count_courses_without_visit_teachers'] = 'Cours sans visite d\'enseignants';
$string['stats_count_courses_without_visit_teachers_desc'] = 'Nombre de cours où aucun enseignant ne s\'est connecté depuis plus de {$a->lastaccessteacher} jours.';
$string['stats_count_courses_without_students'] = 'Cours sans étudiants';
$string['stats_count_courses_without_students_desc'] = 'Nombre de cours sans utilisateurs inscrits comme étudiant.';
$string['stats_count_courses_without_visit_students'] = 'Cours sans visite d\'étudiants';
$string['stats_count_courses_without_visit_students_desc'] = 'Nombre de cours où aucun étudiant ne s\'est connecté depuis plus de {$a->lastaccessstudent} jours.';


// Confirmation alert on dashboard.
$string['confirm_cohort_unenrolled_title'] = 'Cohortes supprimées';
$string['confirm_cohort_unenrolled_message'] = 'Les cohortes ont bien été désinscrites du cours.';
$string['confirm_course_deleted_title'] = 'Cours supprimé';
$string['confirm_course_deleted_message'] = 'Le cours a été déplacé dans la corbeille et sera définitivement supprimé ';
$string['confirm_course_restored_title'] = 'Cours restauré';
$string['confirm_course_restored_message'] = 'Le cours a été déplacé hors de la corbeille et ne sera plus supprimé. Note : le cours restauré n\'est pas visible
 par les étudiants, modifiez les paramètres du cours si nécessaire.';

// Events.
$string['course_dashboard_viewed'] = 'Tableau de bord Enseignants consulté';
$string['course_trash_moved'] = 'Cours déplacé dans la catégorie Corbeille';
$string['course_files_viewed'] = 'Page des fichiers de cours consultée';
$string['course_global_reset'] = 'Cours réinitialisé';
$string['course_cohort_unenrolled'] = 'Cohortes désinscrites';
$string['course_restored'] = 'Cours restauré hors de la catégorie Corbeille';

// Other.
$string['capability_problem'] = 'Vous n\'avez pas la permission d\'afficher cette page.';
$string['unknown'] = 'Inconnu';

// Dashboard filters.
$string['text_filter'] = 'Saisissez quelques caractères du nom du cours';
$string['all_courses'] = 'Tous les cours';
$string['no_content'] = 'Pas de contenus';
$string['no_visit_student'] = 'Pas de visites d\'étudiant';
$string['no_visit_teacher'] = 'Pas de visites d\'enseignants';
$string['no_student'] = 'Aucun étudiant inscrit';
$string['heavy_course'] = 'Poids important';
$string['orphan_submissions_button'] = 'Devoirs orphelins';
$string['ok'] = 'Aucune remarque';

// Tasks.
$string['runreportstask'] = 'Calcul des rapports pour Course Manager';
$string['mailingtask'] = 'Mailing des rapports aux enseignants';

// Warning displays in courses.
$string['course_alert_heavy'] = 'Le <b>poids des fichiers</b> rattachés à ce cours est actuellement de <b>{$a->size} Mo</b>. Pensez à faire du tri vos documents, à réinitialiser les activités ou à déplacer les fichiers volumineux.<br /><b>{$a->heavy_link}</b>';
$string['course_alert_no_visit_teacher'] = 'Aucun <b>enseignant</b> n\'a visité ce cours <b>depuis plus de {$a->no_teacher_time} jours</b>. Pensez à supprimer ce cours si vous ne l\'utilisez plus.<br /><b>{$a->delete_link}</b>';
$string['course_alert_no_visit_student'] = 'Aucun <b>étudiant</b> n\'a visité ce cours <b>depuis plus de {$a->no_student_time} jours</b>. S\'il n\'a pas vocation à être conserver, pensez à le supprimer ou à le réinitialiser.<br /><b>{$a->delete_link} | {$a->reset_link}</b>';
$string['course_alert_no_student'] = '<b>Aucun étudiant n\'est actuellement inscrit dans ce cours</b>. Si ce cours n\'a plus vocation à accueillir des utilisateurs, pensez à le supprimer !<br /><b>{$a->delete_link}</b>';
$string['course_alert_empty'] = 'Ce cours ne propose <b>aucun contenu</b> en dehors du forum de base. Pensez à le supprimer s\'il n\'est pas utilisé. <br /><b>{$a->delete_link}</b>';
$string['course_alert_orphan_submissions'] = 'Ce cours contient des activités Devoir, contenant des dépôts effectués par des étudiants qui ne sont plus inscrits au cours. Ces dépôts invisibles peuvent représenter un poids important ! Pensez à réinitialiser ces activités.</b>';
$string['more_information'] = 'Plus d\'informations';
$string['text_link_delete'] = 'Supprimer ce cours';
$string['text_link_reset'] = 'Réinitialiser';
$string['collapse_show_report'] = 'Afficher le rapport';

// Mailing.
$string['mailingtitle'] = 'Course Manager - Rapport de vos cours';
$string['mailingintro'] = '<p>Bonjour %userfirstname%,</p>
<p>Course Manager est un rapport dédié aux enseignants pour gérer leurs cours, qui propose une série d\'outils et de conseils pour leur entretien. Ces conseils vous sont résumés dans le mail ci-dessous, qui vous indique les cours concernés pour chaque type de rapport.</p>
<p><b>Ces informations sont des conseils généraux et non un avertissement. Leur but est d\'attirer votre attention sur certains aspects de la gestion de vos cours, et de donner des clés pour une bonne pratique de l\'utilisation de Moodle.</b></p>
<p>Pour plus d\'informations, consultez également votre espace <a href="%coursemanagerlink%">Course Manager</a>';
$string['mailingoutro'] = '<p>Pour plus d\'informations sur ces rapports, contactez le support.';
$string['mailingddescreportempty'] = 'Les cours de cette liste ne proposent aucun contenu, à l\'exception du forum de base.';
$string['mailingddescreportnovisitstudent'] = 'Les cours suivants n\'ont pas enregistré de visite d\'étudiants depuis une longue période.</b> ';
$string['mailingddescreportnostudent'] = 'Il n\'y a aucun étudiant inscrit dans les cours suivants.';
$string['mailingddescreportnovisitteacher'] = 'Les cours suivants n\'ont pas enregistré de visite d\'enseignants depuis une longue période';
$string['mailingddescreportheavy'] = 'Le poids des fichiers rattachés à ce cours est important. Cliquez sur un cours pour afficher la décomposition des fichiers.';
$string['mailingddescreportorphansubmissions'] = 'Les activités Devoir de ces cours contiennent des dépôts effectués par des étudiants qui ne sont plus inscrits. Ces dépôts invisibles peuvent représenter un poids important !';

// Privacy.
$string['privacy:no_data_reason'] = 'Le plugin Course Manager n\'enregistre aucune donnée personnelle. Il établit des rapports à partir de données non personnelles résultant de l\'étude des contenus des cours (paramétrages, nombre d\'étudiants, nombre d\'activités...).';

// Capabilities.
$string['coursemanager:viewreport'] = 'Voir les rapports dans les cours si activé';
$string['coursemanager:admintools'] = 'Utiliser les outils admin dans la section Rapports';
