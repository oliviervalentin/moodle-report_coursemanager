# moodle-report_coursemanager
Moodle report for helping teachers to clean and manage courses and contents


# Mode d'emploi et readme en cours de rédaction ! #

### ! Le plugin a été conçu pour Moodle 4 ! ###
Il est toutefois parfaitement possible de le faire fonctionner pour Moodle 3 : dans les fichiers course_files.php, delete_cohort.php, delete_course.php, reset.php et view.php, mettre cette ligne de code en commentaires :
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
- Tâche automatique de calcul des rapports de cours.
- Affichage des rapports directement dans chaque cours soit sous forme de menu déroulant, soit sous forme de popovers Bootstrap.

![image](https://user-images.githubusercontent.com/25584834/179756655-1732b9ed-97cf-4e9b-85d4-d65622159c31.png)


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

## Résultats selon les templates ##
Course Manager a été testé sous Moodle 3.11 et 4.1 avec différents thèmes.
L'affichage des rapport fonctionne parfaitement pour Almonbd, Boost, Boost Union, Classic, Moove et Trema.
Pour Academi, Degrade, Eguru et Klass, l'affichage sous forme d'icônes à coté du titre fonctionne, mais l'image est légèrement décalée.
Pour Adaptable, le fonctionne sous forme d'icône ne fonctionne pas, car la zone visée pour le JS du rapport n'existe pas.
