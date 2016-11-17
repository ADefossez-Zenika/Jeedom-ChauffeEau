<?php
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
class ChauffeEau extends eqLogic {
	public static function deamon_info() {
		$return = array();
		$return['log'] = 'ChauffeEau';
		$return['launchable'] = 'ok';
		$return['state'] = 'nok';
		$cron = cron::byClassAndFunction('ChauffeEau', 'StartChauffe');
		if (!is_object($cron)) 	
			return $return;
		$return['state'] = 'ok';
		return $return;
	}
	public static function deamon_start($_debug = false) {
		log::remove('ChauffeEau');
		self::deamon_stop();
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok') 
			return;
		if ($deamon_info['state'] == 'ok') 
			return;
		foreach(eqLogic::byType('ChauffeEau') as $ChauffeEau)
			$ChauffeEau->save();
	}
	public static function deamon_stop() {	
		$cron = cron::byClassAndFunction('ChauffeEau', 'ActionJour');
		if (is_object($cron)) 	
			$cron->remove();
	}
	public function StartChauffe($_options) {
		$ChauffeEau=eqLogic::byId($_options['id']);
		if(is_object($ChauffeEau)){
			//Activer le chauffage de l'eau
			$Commande=eqLogic::byId($ChauffeEau->getConfiguration('Activation'));
			if(is_object($Commande))
				$Commande->execute();
			//Charger la fin du chauffage
			$Schedule= $ChauffeEau->TimeToShedule($ChauffeEau->EvaluatePowerTime());
			$ChauffeEau->CreateCron($Schedule, 'EndChauffe');	
			//Lancer le prochain chauffage
			foreach(eqLogic::byType('ChauffeEau') as $ChauffeEau){
				$ChauffeEau->CreateCron($ChauffeEau->getConfiguration('ScheduleCron'), 'StartChauffe');
			}
		}
	} 	
	public function EndChauffe($_options) {		
		$ChauffeEau=eqLogic::byId($_options['id']);
		if(is_object($ChauffeEau)){
			$Commande=eqLogic::byId($ChauffeEau->getConfiguration('Desactivation'));
			if(is_object($Commande))
				$Commande->execute();
		}
	} 
	public function TimeToShedule($Time) {
		$now = new DateTime(new DateInterval($Time));
		$Shedule=date_add();
		// min heure jours mois année
		return $Shedule->format("i H d m Y");
	} 
	public function EvaluatePowerTime() {
		//Evaluation du temps necessaire au chauffage de l'eau
		$DeltaTempCmd=cmd::byKey($this->getConfiguration('TempActuel'));
		if(is_object($DeltaTempCmd))
			$DeltaTemp=$DeltaTempCmd->exeCmd();
		else
			$DeltaTemp=$this->getConfiguration('TempActuel');
		$DeltaTemp=$this->getConfiguration('TempSouhaite')-$DeltaTemp;
		$Calorie=$this->getConfiguration('Capacite')*$DeltaTemp*1.16;
		return $Calorie / $this->getConfiguration('Puissance');
	} 
	public function CreateCron($Schedule, $logicalId) {
		$cron =cron::byClassAndFunction('ChauffeEau', $logicalId);
			if (!is_object($cron)) {
				$cron = new cron();
				$cron->setClass('ChauffeEau');
				$cron->setFunction($logicalId);
				$cron->setOption(array('id' => $this->getId()));
				$cron->setEnable(1);
				$cron->setDeamon(0);
				$cron->setSchedule($Schedule);
				$cron->save();
			}
			else{
				$cron->setSchedule($Schedule);
				$cron->save();
			}
		return $cron;
	}
	public function postSave() {
		if($this->getIsEnable()){
			$cron = $this->CreateCron($this->getConfiguration('ScheduleCron'), 'StartChauffe');
		}
	}	
	public function preRemove() {
	}
}
class ChauffeEauCmd extends cmd {
   public function execute($_options = null) {	
		
	}
}
?>
