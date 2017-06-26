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
			$ChauffeEau->ActiveMode();
	}
	public static function deamon_stop() {	
		foreach(eqLogic::byType('ChauffeEau') as $ChauffeEau){
			$cron = cron::byClassAndFunction('ChauffeEau', 'StartChauffe', array('id' => $ChauffeEau->getId()));
			if (is_object($cron)) 	
				$cron->remove();
			$cron = cron::byClassAndFunction('ChauffeEau', 'EndChauffe', array('id' => $ChauffeEau->getId()));
			if (is_object($cron)) 	
				$cron->remove();
		}
	}
	
	public function toHtml($_version = 'dashboard') {
		if ($this->getIsEnable() != 1) {
			return '';
		}
		$version = jeedom::versionAlias($_version);
		if ($this->getDisplay('hideOn' . $version) == 1) {
			return '';
		}
		$vcolor = 'cmdColor';
		if ($version == 'mobile') {
			$vcolor = 'mcmdColor';
		}
		$cmdColor='';
		$NextChauffe='';
		$tempBallon='';
		/*$cron = cron::byClassAndFunction('ChauffeEau', 'StartChauffe', array('id' => $this->getId()));
		if (is_object($cron)) 	
			$NextChauffe='Début : '.$cron->getNextRunDate();
		$cron = cron::byClassAndFunction('ChauffeEau', 'EndChauffe', array('id' => $this->getId()));
		if (is_object($cron)) 	
			$NextChauffe='Fin : '.$cron->getNextRunDate();*/
		$Temp=$this->getConfiguration('TempActuel');
		if(strrpos($Temp,'#')>0){
			$Commande=cmd::byId(str_replace('#','',$Temp));
			if(is_object($Commande))
				$tempBallon=$Commande->exeCmd().'°C';
		}
		$cmdColor = ($this->getPrimaryCategory() == '') ? '' : jeedom::getConfiguration('eqLogic:category:' . $this->getPrimaryCategory() . ':' . $vcolor);
		$replace_eqLogic = array(
			'#id#' => $this->getId(),
			'#background_color#' => $this->getBackgroundColor(jeedom::versionAlias($_version)),
			'#humanname#' => $this->getHumanName(),
			'#name#' => $this->getName(),
			'#height#' => $this->getDisplay('height', 'auto'),
			'#width#' => $this->getDisplay('width', 'auto'),
			'#cmdColor#' => $cmdColor,
			'#NextChauffe#' => $NextChauffe,
			'#tempBallon#' => $tempBallon
		);
		foreach ($this->getCmd() as $cmd) {
			if ($cmd->getDisplay('hideOn' . $version) == 1) 
				continue;
			$replace_eqLogic['#'.$cmd->getLogicalId().'#']= $cmd->toHtml($_version, $cmdColor);
		}
		return $this->postToHtml($_version, template_replace($replace_eqLogic, getTemplate('core', jeedom::versionAlias($version), 'eqLogic', 'ChauffeEau')));
	}
	public static $_widgetPossibility = array('custom' => array(
	        'visibility' => true,
	        'displayName' => true,
	        'optionalParameters' => true,
	));
	public static function StartChauffe($_options) {
		$ChauffeEau=eqLogic::byId($_options['id']);
		if (is_object($ChauffeEau) && $ChauffeEau->getIsEnable()) {
			$Etat=$ChauffeEau->getCmd(null,'etatCommut');
			if(!is_object($Etat))
				break;	
			$State=$Etat->execCmd();
			if($State == 3)
				break;
			log::add('ChauffeEau','info','Debut de l\'activation du chauffe eau '.$ChauffeEau->getHumanName());
			$Commande=cmd::byId(str_replace('#','',$ChauffeEau->getConfiguration('Activation')));
			if(is_object($Commande) && $ChauffeEau->EvaluateCondition()){
				log::add('ChauffeEau','info','Execution de '.$Commande->getHumanName());
				$Commande->execute();
				$ChauffeEau->checkAndUpdateCmd('state',true);
			}   
			if($State == 2){
				$PowerTime=$ChauffeEau->EvaluatePowerTime();
				log::add('ChauffeEau','info','Estimation du temps d\'activation '.$PowerTime);
				$Schedule= $ChauffeEau->TimeToShedule($PowerTime);
				$ChauffeEau->CreateCron($Schedule, 'EndChauffe');	
				//Lancer le prochain chauffage
				foreach(eqLogic::byType('ChauffeEau') as $ChauffeEau){
					$ChauffeEau->CreateCron($ChauffeEau->getConfiguration('ScheduleCron'), 'StartChauffe');
				}
			}
		}
	}
	public static function EndChauffe($_options) {		
		$ChauffeEau=eqLogic::byId($_options['id']);
		if(is_object($ChauffeEau)){
			log::add('ChauffeEau','info','Fin de l\'activation du chauffe eau '.$ChauffeEau->getHumanName());
			$Commande=cmd::byId(str_replace('#','',$ChauffeEau->getConfiguration('Desactivation')));
			if(is_object($Commande) /*&& $ChauffeEau->EvaluateCondition()*/){
				log::add('ChauffeEau','info','Execution de '.$Commande->getHumanName());
				$Commande->execute();
				$ChauffeEau->checkAndUpdateCmd('state',false);
			}
		}
	} 
	public function TimeToShedule($Time) {
		$Heure=round($Time/3600);
		$Minute=round(($Time-($Heure*3600))/60);
		$Shedule = new DateTime();
		$Shedule->add(new DateInterval('PT'.$Time.'S'));
		//$Shedule->setTime($Heure, $Minute);
		// min heure jours mois année
		return  $Shedule->format("i H d m *");
	} 
	public function EvaluatePowerTime() {
		//Evaluation du temps necessaire au chauffage de l'eau
		$DeltaTemp=$this->getConfiguration('TempActuel');
		if(strrpos($DeltaTemp,'#')>0){
			$Commande=cmd::byId(str_replace('#','',$DeltaTemp));
			if(is_object($Commande))
				$DeltaTemp=$Commande->exeCmd();
		}
		$DeltaTemp=$this->getConfiguration('TempSouhaite')-$DeltaTemp;
		$Energie=$this->getConfiguration('Capacite')*$DeltaTemp*4185;
		return round($Energie/ $this->getConfiguration('Puissance'));
	} 
	public function EvaluateCondition(){
		foreach($this->getConfiguration('condition') as $condition){		
			if (isset($condition['enable']) && $condition['enable'] == 0)
				continue;
			$_scenario = null;
			$expression = scenarioExpression::setTags($condition['expression'], $_scenario, true);
			$message = __('Evaluation de la condition : [', __FILE__) . trim($expression) . '] = ';
			$result = evaluate($expression);
			if (is_bool($result)) {
				if ($result) {
					$message .= __('Vrai', __FILE__);
				} else {
					$message .= __('Faux', __FILE__);
				}
			} else {
				$message .= $result;
			}
			log::add('ChauffeEau','info',$this->getHumanName().' : '.$message);
			if(!$result){
				log::add('ChauffeEau','info',$this->getHumanName().' : Les conditions ne sont pas remplies');
				return false;
			}
		}
		return true;
	}
	public function ActiveMode(){
		$Commande = $this->getCmd(null,'etatCommut');
		if (!is_object($Commande))
			break;
		switch($Commande->execCmd()){
			case '1':
				log::add('ChauffeEau','info',$this->getHumanName().' : Passage en mode forcé');
				$cron = cron::byClassAndFunction('ChauffeEau', 'StartChauffe', array('id' => $this->getId()));
				if (is_object($cron)) 	
					$cron->remove();
				$cron = cron::byClassAndFunction('ChauffeEau', 'EndChauffe', array('id' => $this->getId()));
				if (is_object($cron)) 	
					$cron->remove();
				ChauffeEau::StartChauffe(array('id' => $this->getId()));
			break;				
			case '2':
				log::add('ChauffeEau','info',$this->getHumanName().' : Passage en mode automatique');
	   			$this->CreateCron($this->getConfiguration('ScheduleCron'), 'StartChauffe');
			break;
			case '3':
				log::add('ChauffeEau','info',$this->getHumanName().' : Désactivation du Chauffe eau');
				$cron = cron::byClassAndFunction('ChauffeEau', 'StartChauffe', array('id' => $this->getId()));
				if (is_object($cron)) 	
					$cron->remove();
				$cron = cron::byClassAndFunction('ChauffeEau', 'EndChauffe', array('id' => $this->getId()));
				if (is_object($cron)) 	
					$cron->remove();
				ChauffeEau::EndChauffe(array('id' => $this->getId()));
			break;
	   	}}
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
	public static function AddCommande($eqLogic,$Name,$_logicalId,$Type="info", $SubType='binary',$visible,$Template='') {
		$Commande = $eqLogic->getCmd(null,$_logicalId);
		if (!is_object($Commande))
		{
			$Commande = new ChauffeEauCmd();
			$Commande->setId(null);
			$Commande->setLogicalId($_logicalId);
			$Commande->setEqLogic_id($eqLogic->getId());
		}
		$Commande->setName($Name);
		$Commande->setIsVisible($visible);
		$Commande->setType($Type);
		$Commande->setSubType($SubType);
   		$Commande->setTemplate('dashboard',$Template );
		$Commande->setTemplate('mobile', $Template);
		$Commande->save();
		return $Commande;
	}
	public function postSave() {
		$state=self::AddCommande($this,"Etat du chauffe-eau","state","info", 'binary',true);
		$state->event(false);
		$state->setCollectDate(date('Y-m-d H:i:s'));
		$state->save();
		$isArmed=self::AddCommande($this,"Etat fonctionnement","etatCommut","info","numeric",false);
		$isArmed->event(2);
		$isArmed->setCollectDate(date('Y-m-d H:i:s'));
		$isArmed->save();
		$Armed=self::AddCommande($this,"Marche forcé","armed","action","other",true,'Commutateur');
		$Armed->setValue($isArmed->getId());
		$Armed->save();
		$Released=self::AddCommande($this,"Desactiver","released","action","other",true,'Commutateur');
		$Released->setValue($isArmed->getId());
		$Released->save();
		$Auto=self::AddCommande($this,"Automatique","auto","action","other",true,'Commutateur');
		$Auto->setValue($isArmed->getId());
		$Auto->save();
		
		$this->ActiveMode();
	}
}
class ChauffeEauCmd extends cmd {
	public function execute($_options = null) {
		switch($this->getLogicalId()){
			case 'armed':
				$this->getEqLogic()->checkAndUpdateCmd('etatCommut',1);
			break;
			case 'released':
				$this->getEqLogic()->checkAndUpdateCmd('etatCommut',3);
			break;
			case 'auto':
				$this->getEqLogic()->checkAndUpdateCmd('etatCommut',2);
			break;
		}
		$this->getEqLogic()->ActiveMode();
	}
}
?>
