<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
function ChauffeEau_install(){
}
function ChauffeEau_update(){
	log::add('ChauffeEau','debug','Lancement du script de mise a jours'); 
	foreach(eqLogic::byType('ChauffeEau') as $eqLogic){
		$listener = listener::byClassAndFunction('ChauffeEau', 'pull', array('ChauffeEau_id' => $eqLogic->getId()));
		if (is_object($listener)) 	
			$listener->remove();
		$cache = cache::byKey('ChauffeEau::OldTemp::'.$eqLogic->getId());
		if (is_object($cache)) 	
			$cache->remove();
		$cache = cache::byKey('ChauffeEau::EvalTime::'.$eqLogic->getId());
		if (is_object($cache)) 	
			$cache->remove();
		foreach($eqLogic->getConfiguration('ActionOn') as $cmd){
			$cmd['declencheur']='on';
			$Action[]=$cmd;
		}
		foreach($eqLogic->getConfiguration('ActionOff') as $cmd){
			$cmd['declencheur']='off';
			$Action[]=$cmd;
		}
		$eqLogic->setConfiguration('Action',$Action);
		$eqLogic->setConfiguration('ActionOn',array());
		$eqLogic->setConfiguration('ActionOff',array());
		$eqLogic->save();
		//cache::set('ChauffeEau::Puissance::'.$eqLogic->getId(), json_encode(array_slice(array(intval(trim($eqLogic->getConfiguration('Puissance')))), -10, 10)), 0);
	}
	log::add('ChauffeEau','debug','Fin du script de mise a jours');
}
function ChauffeEau_remove(){
}
?>
