<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
function ChauffeEau_install(){
}
function ChauffeEau_update(){
	log::add('ChauffeEau','debug','Lancement du script de mise a jours'); 
	foreach(eqLogic::byType('ChauffeEau') as $eqLogic){
    if($eqLogic->getConfiguration('Activation')!=''){
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
    }
		$eqLogic->save();
	}
	log::add('ChauffeEau','debug','Fin du script de mise a jours');
}
function ChauffeEau_remove(){
}
?>
