# moodle-report_coursemanager
Moodle report for helping teachers to clean and manage courses and contents


# mode d'emploi et readme en cours de rédaction ! #

### !! Version fonctionnant uniquement sur Moodle 4.0 !! ###
Pour versions antérieures (3.11 et moins) : dans les fichiers course_files.php, delete_cohort.php, delete_course.php, reset.php et view.php, mettre ce code en commentaires :
// $PAGE->set_secondary_navigation(false);

## Description ##

Ce rapport est à destination des enseignants en tant qu'outil de gestion de leurs cours. En se basant sur des fonctions 
natives ainsi que sur d'autres fonctions spécifiquement élaborées pour ce rapport, cet outil a pour vocation : 
-	D’ouvrir de nouvelles possibilités qui ne vont pas à l’encontre des permissions de rôle (Supprimer le cours) ;
-	De proposer des fonctions « raccourcies » pour nettoyer son cours (désinscription massive de cohortes, réinitialisation simplifiée, préconisations de nettoyage) ;
-	D’offrir des outils dans le but de mieux visualiser ses données et d’inciter à faire le ménage, notamment en utilisant des techniques « nudge ».

Le plugin propose :
- une page de rapport complet pour l'enseignant (voir "Donner accès au rapport) avec la liste de tous les cours auxquels l'utilisateur est inscrit en tant qu'enseignant ;
- le calcul automatique des rapports via une tâche Moodle. Dans l'idéal, à paramétrer toutes les 3-4 heures (retours appréciés)
- l'affichage sur la page d'accueil du cours des rapports (uniquement visible pour les enseignants).

## Installation ##
Ajouter le dossier « coursemanager » dans le dossier report de Moodle. L’installation se déroule comme pour un plugin Moodle.

## Donner accès au rapport ##
Le tableau complet des cours est accessible en se rendant directement à l'url du fichier view.php du plugin. A noter que l'accès à ce tableau n'est pas primordial si on souhaite afficher les rapports uniquement dans les cours.

Pour donner accès au rapport, le plus simple est d’ajouter un élément dans la navigation principale en utilisant les custom menu items 
dans Administration du site > Présentation > Réglages thème et en rajoutant un lien pointant vers /report/coursemanager/view.php
Le menu s’affiche alors pour tous les utilisateurs, enseignants comme étudiants. Néanmoins, le rapport n’affichera de données que si 
l’utilisateur qui le consulte est inscrit avec le rôle Enseignant dans au moins un cours.

# Version en cours de dévelopement #
Pour toute question sur le fonctionnement du code, me contacter directement.
Les textes du fichier de traduction ainsi que les éléments analysés peuvent être directement rattachés à la politique du service TICE que j'occupe. N'hésitez pas à me faire vos retours sur des pistes d'améliorations !
