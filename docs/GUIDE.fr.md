# Guide InteractEmbed

## Ajouter un contenu web

1. Activez le mode édition sur une page qui accepte les blocs.
2. Ajoutez **InteractEmbed — Bloc de contenus interactifs**.
3. Choisissez **Adresse web ou iframe**.
4. Collez une adresse HTTP/HTTPS ou le code iframe fourni par le service.
5. Choisissez le format, le profil de sécurité et les options d'affichage, puis enregistrez.

InteractEmbed reconnaît notamment YouTube, Google Docs/Slides/Forms/Sheets, Canva, Genially, Padlet et Wayground. Le site distant doit autoriser l'affichage dans une iframe.

Choisissez **Exiger un clic avant de charger le contenu** lorsque le visiteur doit décider avant qu’une connexion soit établie avec le fournisseur externe. Choisissez **Carte compacte** pour afficher un panneau de lancement léger dans une colonne étroite; le lecteur complet se déploie après le clic.

L’administrateur peut autoriser des domaines génériques supplémentaires dans les réglages du plugin. L’option permissive autorisant toutes les URL génériques devrait être réservée aux sites où tous les éditeurs de blocs sont fiables.

## Présenter le contenu

Le champ **Introduction ou description accessible** permet d’ajouter une consigne, un objectif pédagogique ou une solution de remplacement textuelle avant le lecteur. Une **Image de couverture** facultative peut personnaliser le mode carte et le chargement au clic. Sa description est obligatoire afin que l’information demeure accessible.

## Utiliser le guide inclus

Choisissez **Guide interactif inclus** comme source pour afficher le manuel HTML5 livré avec InteractEmbed. Le guide sélectionne automatiquement le français, l’anglais ou l’espagnol selon la langue Moodle et propose également son propre sélecteur. Il n’est jamais ajouté automatiquement et ne contacte aucun service externe.

## Ajouter un paquet HTML5

Choisissez **Paquet HTML5**, puis déposez soit une archive ZIP unique, soit les fichiers décompressés avec leurs sous-dossiers. Ne conservez jamais le ZIP et ses fichiers décompressés simultanément dans le sélecteur Moodle. Le plus simple est de déposer uniquement le ZIP sans utiliser l’action **Décompresser**. Le fichier de démarrage est normalement `index.html`; indiquez un autre chemin au besoin.

Le profil **Standard** isole le paquet de Moodle. Le profil **Paquet fiable**, réservé à l'administrateur, ne doit servir qu'aux fichiers que vous contrôlez.

## Réutiliser le lecteur dans le même Moodle

Activez d’abord **Afficher le code d’intégration** dans les paramètres du bloc. Dépliez ensuite **Code d’intégration** sous le bloc, copiez le code, puis collez-le dans une zone HTML fiable du même Moodle. Le lecteur conserve la session et les permissions du bloc original. Le script joint ajuste automatiquement sa hauteur.

Ce code ne rend jamais public un bloc privé et ne contourne jamais la connexion au site ou l'accès au cours.

## Administration du site

Ouvrez **Administration du site > Plugins > Blocs > InteractEmbed** pour configurer les valeurs par défaut des nouveaux blocs, les types de sources autorisés, l’affichage du code d’intégration, le chargement obligatoire sur clic, les domaines supplémentaires et les limites de sécurité ZIP.

Les valeurs par défaut initialisent seulement les nouveaux blocs et demeurent modifiables. Les politiques du site sont imposées à tous les blocs. Désactiver un type de source rend donc également indisponibles les blocs existants de ce type jusqu’au rétablissement de la politique. Désactiver le code d’intégration masque l’outil de copie, sans briser volontairement les codes iframe déjà placés ailleurs dans le même Moodle.

## Résoudre les problèmes courants

- **Impossible d’enregistrer le paquet :** gardez uniquement le ZIP ou uniquement les fichiers décompressés.
- **Fichier de démarrage introuvable :** vérifiez la casse et le chemin complet, par exemple `presentation/index.html`.
- **Source distante vide ou refusée :** vérifiez les permissions du lien de partage et si le fournisseur autorise l’affichage dans une iframe.
- **Contenu indisponible :** vérifiez les politiques InteractEmbed du site, les permissions du contexte et l’accès invité lorsque le bloc se trouve sur l’accueil.
