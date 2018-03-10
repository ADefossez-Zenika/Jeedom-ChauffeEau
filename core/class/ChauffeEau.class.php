<?php
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
class ChauffeEau extends eqLogic {
	public static function deamon_info() {
		$return = array();
		$return['log'] = 'ChauffeEau';
		$return['launchable'] = 'ok';
		$return['state'] = 'nok';
		foreach(eqLogic::byType('ChauffeEau') as $ChauffeEau){
			if($ChauffeEau->getIsEnable()){
				$cron = cron::byClassAndFunction('ChauffeEau', 'Chauffe', array('ChauffeEau_id' => $ChauffeEau->getId()));
				if (!is_object($cron)) 	
					return $return;
			}
		}
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
			$ChauffeEau->createDeamon();
	}
	public static function deamon_stop() {	
		foreach(eqLogic::byType('ChauffeEau') as $ChauffeEau){
			$cron = cron::byClassAndFunction('ChauffeEau', 'Chauffe', array('ChauffeEau_id' => $ChauffeEau->getId()));
			if (is_object($cron)) 	
				$cron->remove();
		}
	}
	public function preSave() {
		$Programation=$this->getConfiguration('programation');
		foreach($Programation as $key => $ConigSchedule){
			if($ConigSchedule["id"] == ''){
				$id=rand(0,32767);
				//while(array_search($id, array_column($this->getConfiguration('programation'), 'id')) !== FALSE)
				//	$id=rand(0,32767);
				$ConigSchedule["id"]=$id;
			}
			$ConigSchedule["url"] = network::getNetworkAccess('external') . '/plugins/reveil/core/api/jeeReveil.php?apikey=' . jeedom::getApiKey('reveil') . '&id=' . $this->getId() . '&prog=' . $ConigSchedule["id"] . '&day=%DAY&heure=%H&minute=%M';
			$Programation[$key]=$ConigSchedule;
		}
		$this->setConfiguration('programation', $Programation);
	}
	public function UpdateDynamic($id,$days,$heure,$minute){
		$Programation=$this->getConfiguration('programation');
		$key=array_search($id, array_column($Programation, 'id'));
		if($key !== FALSE){		
			for($day=0;$day<7;$day++)
				$Programation[$key][$day]=false;
			foreach(str_split($days) as $day)
				$Programation[$key][$day]=true;
			$Programation[$key]["Heure"]=$heure;
			$Programation[$key]["Minute"]=$minute;
			$this->setConfiguration('programation',$Programation);
			$this->save();
			$this->NextStart();
      			$this->refreshWidget();
		}
	}
	public function toHtml($_version = 'dashboard') {
		$replace = $this->preToHtml($_version);
		if (!is_array($replace)) 
			return $replace;
		$version = jeedom::versionAlias($_version);
		if ($this->getDisplay('hideOn' . $version) == 1)
			return '';
		foreach ($this->getCmd() as $cmd) {
			if ($cmd->getDisplay('hideOn' . $version) == 1)
				continue;
			$replace['#'.$cmd->getLogicalId().'#']= $cmd->toHtml($_version, $cmdColor);
		}
		$replace['#cmdColor#'] = ($this->getPrimaryCategory() == '') ? '' : jeedom::getConfiguration('eqLogic:category:' . $this->getPrimaryCategory() . ':' . $vcolor);
		$PowerTime=$this->EvaluatePowerTime();
		$NextStart=$this->NextStart();
		if(mktime() > $NextStart-$PowerTime)
			$replace['#Next#'] = "Début : " . date('d/m/Y H:i',$NextStart-$PowerTime);
		else
			$replace['#Next#'] = "Fin : " . date('d/m/Y H:i',$NextStart);
		$replace['#tempBallon#'] = $this->TempActuel();
		if ($_version == 'dview' || $_version == 'mview') {
			$object = $this->getObject();
			$replace['#name#'] = (is_object($object)) ? $object->getName() . ' - ' . $replace['#name#'] : $replace['#name#'];
		}
      		return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'eqLogic', 'ChauffeEau')));
  	}
	public static $_widgetPossibility = array('custom' => array(
	        'visibility' => true,
	        'displayName' => true,
	        'displayObjectName' => true,
	        'optionalParameters' => true,
	        'background-color' => true,
	        'text-color' => true,
	        'border' => true,
	        'border-radius' => true
	));
	public static function pull($_option) {
		$ChauffeEau = Volets::byId($_option['ChauffeEau_id']);
		if (is_object($ChauffeEau) && $ChauffeEau->getIsEnable()) {
			if($_option['value'] && !$ChauffeEau->getCmd(null,'state')->execCmd())
				$ChauffeEau->checkAndUpdateCmd('etatCommut',1);
			if(!$_option['value'] && $ChauffeEau->getCmd(null,'state')->execCmd())
				$ChauffeEau->checkAndUpdateCmd('etatCommut',3);
			/*if($_option['value'] && $ChauffeEau->getCmd(null,'state')->execCmd())
				$ChauffeEau->checkAndUpdateCmd('etatCommut',2);*/
			if($_option['value'])
				$ChauffeEau->checkAndUpdateCmd('state',true);
			else
				$ChauffeEau->checkAndUpdateCmd('state',false);
		}
	}
	public static function Chauffe($_options) {
		$ChauffeEau=eqLogic::byId($_options['ChauffeEau_id']);
		if (!is_object($ChauffeEau)) 
			return;
		if (!$ChauffeEau->getIsEnable()) 
			return;
		while(true){
			switch($ChauffeEau->getCmd(null,'etatCommut')->execCmd()){
				case 1:
					// Mode Forcée
					$ChauffeEau->powerStart();
				break;
				case 2:
					//Mode automatique
					if(mktime() > $ChauffeEau->NextStart()-$ChauffeEau->EvaluatePowerTime()){
						if(!$ChauffeEau->EvaluateCondition())
							continue;
						if($ChauffeEau->TempActuel() <=  $ChauffeEau->getConfiguration('TempSouhaite')){
							log::add('ChauffeEau','info','Execution de '.$ChauffeEau->getHumanName());
							$ChauffeEau->powerStart();
							if(mktime() > $ChauffeEau->NextStart())
								$ChauffeEau->powerStop();
						}else
							$ChauffeEau->powerStop();		
					}
				break;
				case 3:
					// Mode Stope
					$ChauffeEau->powerStop();
				break;
			}
		}
	}
	public function powerStart(){
		if(!$this->getCmd(null,'state')->execCmd()){
			$Commande=cmd::byId(str_replace('#','',$this->getConfiguration('Activation')));
			if(is_object($Commande)){
				log::add('ChauffeEau','info','Execution de '.$Commande->getHumanName());
				$Commande->execute();
				$this->checkAndUpdateCmd('state',true);
			}
		}
	}
	public function powerStop(){
		if($this->getCmd(null,'state')->execCmd()){
			$Commande=cmd::byId(str_replace('#','',$this->getConfiguration('Desactivation')));
			if(is_object($Commande)){
				log::add('ChauffeEau','info','Execution de '.$Commande->getHumanName());
				$Commande->execute();
				$this->checkAndUpdateCmd('state',false);
			}
		}
	}
	public function TempActuel(){
		$TempActuel=$this->getConfiguration('TempActuel');
		if(strrpos($TempActuel,'#')>0){
			$Commande=cmd::byId(str_replace('#','',$TempActuel));
			if(is_object($Commande))
				$TempActuel=$Commande->execCmd();
		}
		return $TempActuel;
	}
	public function NextStart(){
		$nextTime=null;
		foreach($this->getConfiguration('programation') as $ConigSchedule){
			$offset=0;
			if(date('H') > $ConigSchedule["Heure"])
				$offset++;
			if(date('H') == $ConigSchedule["Heure"] && date('i') >= $ConigSchedule["Minute"])	
				$offset++;
			for($day=0;$day<7;$day++){
				if($ConigSchedule[date('w')+$day+$offset]){
					$offset+=$day;
					$timestamp=mktime ($ConigSchedule["Heure"], $ConigSchedule["Minute"], 0, date("n") , date("j") , date("Y"))+ (3600 * 24) * $offset;
					break;
				}
			}
			if($nextTime == null || $nextTime > $timestamp)
				$nextTime=$timestamp;
		}
		return $nextTime;
	}
	public function EvaluatePowerTime() {
		//Evaluation du temps necessaire au chauffage de l'eau
		$DeltaTemp=$this->TempActuel();
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
			log::add('ChauffeEau','debug',$this->getHumanName().' : '.$message);
			if(!$result){
				log::add('ChauffeEau','debug',$this->getHumanName().' : Les conditions ne sont pas remplies');
				return false;
			}
		}
		return true;
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
	public function preRemove() {
		self::deamon_stop();
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
		$this->createDeamon();
	}
	public function createDeamon() {
		$cron = cron::byClassAndFunction('ChauffeEau', 'Chauffe', array('ChauffeEau_id' => $this->getId()));
		if (!is_object($cron)) 
			$cron = new cron();
		$cron->setClass('ChauffeEau');
		$cron->setFunction('Chauffe');
		$cron->setOption(array('ChauffeEau_id' => $this->getId()));
		$cron->setEnable(1);
		$cron->setDeamon(1);
		$cron->setSchedule('* * * * *');
		$cron->setTimeout('999999');
		$cron->save();
		$cron->start();
		$cron->run();
		if ($this->getConfiguration('Etat') != ''){
			$listener = listener::byClassAndFunction('ChauffeEau', 'pull', array('ChauffeEau_id' => $this->getId()));
			if (!is_object($listener))
			    $listener = new listener();
			$listener->setClass('ChauffeEau');
			$listener->setFunction('pull');
			$listener->setOption(array('ChauffeEau_id' => $this->getId()));
			$listener->emptyEvent();				
			$listener->addEvent($this->getConfiguration('Etat'));
			$listener->save();	
		}
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
	}
}
?>
