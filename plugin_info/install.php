<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
function ChauffeEau_install(){
}
function ChauffeEau_update(){
	log::add('ChauffeEau','debug','Lancement du script de mise a jours'); 
	foreach(eqLogic::byType('ChauffeEau') as $eqLogic){
	/*	if($eqLogic->getConfiguration('Activation')!=''){
			$ActionOn[]['cmd']=$eqLogic->getConfiguration('Activation');
			$ActionOn[]['enable']=true;
			$eqLogic->setConfiguration('ActionOn',$ActionOn);
			$eqLogic->setConfiguration('Activation','');
		}
		if($eqLogic->getConfiguration('Desactivation')!=''){
			$ActionOff[]['cmd']=$eqLogic->getConfiguration('Desactivation');
			$ActionOff[]['enable']=true;
			$eqLogic->setConfiguration('ActionOff',$ActionOff);
			$eqLogic->setConfiguration('Desactivation','');
		}*/
		$eqLogic->save();
	/*	$cron = cron::byClassAndFunction('ChauffeEau', 'Chauffe', array('ChauffeEau_id' => $eqLogic->getId()));
		if (is_object($cron)) 	
			$cron->remove();*/
		
		$cache = cache::byKey('ChauffeEau::OldTemp::'.$this->getId());
		if (is_object($cache)) 	
			$cache->remove();
		$cache = cache::byKey('ChauffeEau::EvalTime::'.$this->getId());
		if (is_object($cache)) 	
			$cache->remove();
	}
	log::add('ChauffeEau','debug','Fin du script de mise a jours');
}
function ChauffeEau_remove(){
}
?>
