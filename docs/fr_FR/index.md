Description
==========

Ce plugin permet de gérer votre chauffe-eau.
Il va estimer le temps nécessaire pour une chauffe complète de votre ballon.
Si votre installation est equipé d'une sonde de température, le plugin stopera la chauffe des qu'il attendra sa température désiré.
Aprés l'heure programmée, le plugin stopera le chauffage et attendera le prochain crénaux reduit du temps de chauffage calculé.

Paramettrage d'un chauffe-eau
==========	

![introduction01](../images/ConfigurationGeneral.jpg)	

Parametre général
---

* Nom  : le nom a déjà été paramétré, mais vous avez la possibilité de le changer.		
* Objet parent : ce paramètre permet d'ajouter l'équipement dans un objet Jeedom.		
* Catégorie : déclare l'équipement dans une catégorie.		
* Visible : permet de rendre l'équipement visible dans le Dashboard.		
* Activer : permet d'activer l'équipement.		

Parametre du chauffe eaux
---

* Capacité du chauffe-eau (Litre) : indiquez le volume de votre chauffe-eau
* Puissance du chauffe-eau (Watt) : indiquez la puissance de votre chauffe-eau
* Température Souhaitée (°C) : indiquez la température à atteindre, ou saisiez une formule
* Sélectionnez une commande ou estimez la température actuelle de l'eau : indiquez la température au moment de la chauffe ou choisissez un objet Jeedom représentant la valeur, ou saisiez une formule

Controle du chauffe eau
---

* Commande d'activation du chauffe-eau : sélectionnez la commande _on_ de votre chauffe-eau
* Commande de désactivation du chauffe-eau : sélectionnez la commande _off_ de votre chauffe-eau
* Commande d'etat du chauffe-eau : sélectionnez la commande d'etat de votre chauffe-eau afin de permetre au plugin de se mettre a jours

Programation
==========
Nous avans la possibilité de cree plusieurs programation  de notre chauffe eau. 
L'heure choisi correspondera a la fin maximal du chauffage de l'eau.
Pour chaque programation une url de reconfiguration est disponible pour le liée avec d'autre equipement.

![introduction01](../images/ConfigurationProgramation.jpg)	

L'url de reprogrammation se presente sous la forme
URL_Jeedom/plugins/ChauffeEau/core/api/jeeChauffeEau.php?apikey=APIKEY&id=ID&prog=IDcmd&day=%DAY&heure=%H&minute=%M
Les champs "URL_Jeedom, APIKEY, ID, IDcmd sont automatiquement complété pour chaque URL.
Il sera imperatif de personlaiser cette url en remplace les parametre par les informations a complété :

- %DAY : Les jours de declanchement (0 = Dimanche, 1 = Lundi, ...)
- %H : L'heure de declanchement du reveil
- %M : La minite de declanchement du reveil

Condition
==========
Afin de pouvoir filtrer les declanchements du ChauffeEau nous avons la possibilité de lui ajouté des conditions d'execution.
Par exemple je suis en vacance, je ne veux donc pas que le chauffe-eau se declanche

![introduction01](../images/ConfigurationCondition.jpg)

Cliquer sur "Ajouter une condition" et configurer votre condition
Chaque condition de la liste formera un ET


