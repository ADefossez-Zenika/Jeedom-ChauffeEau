<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
function ChauffeEau_install(){
}
function ChauffeEau_update(){
	log::add('ChauffeEau','debug','Lancement du script de mise a jours'); 
	foreach(eqLogic::byType('ChauffeEau') as $eqLogic){
		$eqLogic->save();
		$listener = listener::byClassAndFunction('ChauffeEau', 'pull', array('ChauffeEau_id' => $eqLogic->getId()));
		if (is_object($listener)) 	
			$listener->remove();
		$cache = cache::byKey('ChauffeEau::OldTemp::'.$eqLogic->getId());
		if (is_object($cache)) 	
			$cache->remove();
		$cache = cache::byKey('ChauffeEau::EvalTime::'.$eqLogic->getId());
		if (is_object($cache)) 	
			$cache->remove();
	}
	log::add('ChauffeEau','debug','Fin du script de mise a jours');
}
function ChauffeEau_remove(){
}
?>
