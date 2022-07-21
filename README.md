# moodle-report_coursemanager
Moodle report for helping teachers to clean and manage courses and contents

# Version en cours de dévelopement #
Pour toute question sur le fonctionnement du code, me contacter directement.

### !! Version prévue sur Moodle 4.0 !! ###
Pour faire tourner le code sur des versions antérieures (3.11 et moins) : dans les fichiers course_files.php, delete_cohort.php, delete_course.php, reset.php et view.php, mettre ce code en commentaires :
// $PAGE->set_secondary_navigation(false);

## Description ##
Ce rapport est à destination des enseignants en tant qu'outil de gestion de leurs cours. En se basant sur des fonctions 
natives ainsi que sur d'autres fonctions spécifiquement élaborées pour ce rapport, cet outil a pour vocation : 
-	D’ouvrir de nouvelles possibilités qui ne vont pas à l’encontre des permissions de rôle (Supprimer le cours) ;
-	De proposer des fonctions « raccourcies » pour nettoyer son cours (désinscription massive de cohortes, réinitialisation simplifiée, préconisations de nettoyage) ;
-	D’offrir des outils dans le but de mieux visualiser ses données et d’inciter à faire le ménage, notamment en utilisant des techniques « nudge ».

![image](https://user-images.githubusercontent.com/25584834/179756387-804fa248-e542-4bd8-b8d7-920bf9e8fe0f.png)
![image](https://user-images.githubusercontent.com/25584834/179756572-ab7bdb9e-00a2-4db9-9960-754194af59c5.png)

## Fonctionnalités ##

- Liste complète des cours où l'utilisateur est inscrit en tant qu'enseignant.
- Filtrage des cours par type de cours et recherche par nom de cours.
- Calcul du poids des fichiers contenus dans un cours, avec graph de répartition. Recommendations spécifiques pour les fichiers lourds, les vidéos, les devoirs volumineux ou contenant des fichiers d'utilisateurs non inscrits au cours.
- Recommandations automatiques sur le poids du cours, l'absence de visites (étudiants ou eneignants), l'absence de contenus, l'absece d'inscrits.
- Possibilité de déplacer un cours dans une catégorie-corbeille.
- Réinitialisation de cours simplifiée.
- Désinscription massive des cohortes inscrites.
- Evénements liés à différentes actions, afin de mesurer l'utilisation du plugin.

![image](https://user-images.githubusercontent.com/25584834/179756655-1732b9ed-97cf-4e9b-85d4-d65622159c31.png)

## Installation ##
Ajouter le dossier « coursemanager » dans le dossier report de Moodle. L’installation se déroule comme pour un plugin Moodle.

Idéalement, le code JavaScript du fichier test.js, qui sert au filtrage des cours par nom, sera intégré via Grunt (en projet).

## Comment donner accès au rapport ? ##
Pour donner accès au rapport, le plus simple est d’ajouter un élément dans la navigation principale en utilisant les custom menu items 
dans Administration du site > Présentation > Réglages thème. On peut alors rajouter un lien pointant vers /report/coursemanager/view.php
Le menu s’affiche alors pour tous les utilisateurs, enseignants comme étudiants. Néanmoins, le rapport n’affichera de données que si 
l’utilisateur qui le consulte est inscrit avec le rôle Enseignant dans au moins un cours.
