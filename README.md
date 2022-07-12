# moodle-report_coursemanager
Moodle report for helping teachers to clean and manage courses and contents

# Version en cours de dévelopement #
Pour toute question sur le fonctionnement du code, me contacter directement.

### !! Version fonctionnant uniquement sur Moodle 4.0 !! ###
Pour faire tourner le code sur des versions antérieures (3.11 et moins) : dans les fichiers course_files.php, delete_cohort.php, delete_course.php, reset.php et view.php, mettre ce code en commentaires :
// $PAGE->set_secondary_navigation(false);

## Description ##
Ce rapport est à destination des enseignants en tant qu'outil de gestion de leurs cours. En se basant sur des fonctions 
natives ainsi que sur d'autres fonctions spécifiquement élaborées pour ce rapport, cet outil a pour vocation : 
-	D’ouvrir de nouvelles possibilités qui ne vont pas à l’encontre des permissions de rôle (Supprimer le cours) ;
-	De proposer des fonctions « raccourcies » pour nettoyer son cours (désinscription massive de cohortes, réinitialisation simplifiée, préconisations de nettoyage) ;
-	D’offrir des outils dans le but de mieux visualiser ses données et d’inciter à faire le ménage, notamment en utilisant des techniques « nudge ».

## Installation ##
1) Ajouter le dossier « coursemanager » dans le dossier report de Moodle. L’installation se déroule comme pour un plugin Moodle.
2) Récupérer le code dans le fichier test.js et le coller dans la zone "HTML additionnel", dans la rubrique Présentation.

## Donner accès au rapport ##
Pour donner accès au rapport, le plus simple est d’ajouter un élément dans la navigation principale en utilisant les custom menu items 
dans Administration du site > Présentation > Réglages thème. On peut alors rajouter un lien pointant vers /report/coursemanager/view.php
Le menu s’affiche alors pour tous les utilisateurs, enseignants comme étudiants. Néanmoins, le rapport n’affichera de données que si 
l’utilisateur qui le consulte est inscrit avec le rôle Enseignant dans au moins un cours.
