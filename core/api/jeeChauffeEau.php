<?php
header('Content-type: application/json');
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
if (!jeedom::apiAccess(init('apikey'), 'ChauffeEau')) {
	echo __('Clef API non valide, vous n\'êtes pas autorisé à effectuer cette action (ChauffeEau)', __FILE__);
	die();
}
$eqlogic = ChauffeEau::byId(init('id'));
if (!is_object($eqlogic)) {
	throw new Exception(__('Commande ID reveil inconnu : ', __FILE__) . init('id'));
}
if ($eqlogic->getEqType_name() != 'ChauffeEau') {
	throw new Exception(__('Cette commande n\'est pas de type ChauffeEau : ', __FILE__) . init('id'));
}
$eqlogic->UpdateDynamic(init('prog'),init('day'),init('heure'),init('minute'),init('seuil'));
return true;
?>
