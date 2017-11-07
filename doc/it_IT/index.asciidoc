= Gestion des chauffe-eaux

== Description
Ce plugin permet de gérer votre chauffe-eau.
Il va estimer le temps nécessaire pour une chauffe complète de votre ballon.

== Paramétrage
Comme avec tous les plugins jeedom, nous allons commencer par créer un équipement.		
Après l'avoir nommé, nous allons pouvoir le paramétrer.		

=== Configuration générale Jeedom		
		
image::../images/ConfigurationGeneral.jpg[]		
* `Nom` : le nom a déjà été paramétré, mais vous avez la possibilité de le changer.		
* `Objet parent` : ce paramètre permet d'ajouter l'équipement dans un objet Jeedom.		
* `Catégorie` : déclare l'équipement dans une catégorie.		
* `Visible` : permet de rendre l'équipement visible dans le Dashboard.		
* `Activer` : permet d'activer l'équipement.		
* `Capacité du chauffe-eau (Litre)` : indiquez le volume de votre chauffe-eau
* `Puissance du chauffe-eau (Watt)` : indiquez la puissance de votre chauffe-eau
* `Température Souhaitée (°C)` : indiquez la température à atteindre
* `Sélectionnez une commande ou estimez la température actuelle de l'eau` : indiquez la température au moment de la chauffe ou choisissez un objet Jeedom représentant la valeur
* `Configurez le lancement de votre chauffage` : heure d'allumage de votre chauffe-eau, c'est au format `cron`
* `Commande d'activation du chauffe-eau` : sélectionnez la commande _on_ de votre chauffe-eau
* `Commande de désactivation du chauffe-eau` : sélectionnez la commande _off_ de votre chauffe-eau