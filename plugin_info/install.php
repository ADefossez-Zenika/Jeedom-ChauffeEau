<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
function ChauffeEau_install(){
}
function ChauffeEau_update(){
	log::add('ChauffeEau','debug','Lancement du script de mise a jours'); 
	foreach(eqLogic::byType('ChauffeEau') as $ChauffeEau){
		$listener = listener::byClassAndFunction('ChauffeEau', 'pull', array('ChauffeEau_id' => $ChauffeEau->getId()));
		if (is_object($listener)) 	
			$listener->remove();
		$cache = cache::byKey('ChauffeEau::DeltaTemp::'.$ChauffeEau->getId());
		if (is_object($cache)) 	
			$cache->remove();
		$cache = cache::byKey('ChauffeEau::Puissance::'.$ChauffeEau->getId());
		if (is_object($cache)) 	
			$cache->remove();
		$ChauffeEau->save();
	}
	log::add('ChauffeEau','debug','Fin du script de mise a jours');
}
function ChauffeEau_remove(){
}
?>
