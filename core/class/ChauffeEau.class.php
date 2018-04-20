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
				$listener = listener::byClassAndFunction('ChauffeEau', 'pull', array('ChauffeEau_id' => $ChauffeEau->getId()));
				if (!is_object($listener))	
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
			$listener = listener::byClassAndFunction('ChauffeEau', 'pull', array('ChauffeEau_id' => $ChauffeEau->getId()));
			if (is_object($listener)) 	
				$listener->remove();
		}
	}
	public static function cron() {	
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok') 
			return;
		if ($deamon_info['state'] != 'ok') 
			return;
		foreach(eqLogic::byType('ChauffeEau') as $ChauffeEau){
			if (!$ChauffeEau->getIsEnable()) 
				return;
			switch($ChauffeEau->getCmd(null,'etatCommut')->execCmd()){
				case 1:
					// Mode Forcée
					$ChauffeEau->powerStart();
				break;
				case 2:
					//Mode automatique
					$NextProg=$ChauffeEau->NextProg();
					if($NextProg != null){
						$TempSouhaite = jeedom::evaluateExpression($ChauffeEau->getConfiguration('TempSouhaite'));
						$TempActuel= jeedom::evaluateExpression($ChauffeEau->getConfiguration('TempActuel'));
						$StartTemps = cache::byKey('ChauffeEau::Start::Temps::'.$ChauffeEau->getId());
						$DeltaTemp=$StartTemps->getValue(0)-$TempActuel;
						$PowerTime=$ChauffeEau->EvaluatePowerTime();
						if(mktime() > $NextProg-$PowerTime+60){	//Heure actuel > Heure de dispo - Temps de chauffe + Pas d'integration
							if(mktime() > $NextProg){
								log::add('ChauffeEau','debug',$ChauffeEau->getHumanName().' : Temps supperieur a l\'heure programmée');
								$ChauffeEau->PowerStop();
								break;
							}
							log::add('ChauffeEau','debug',$ChauffeEau->getHumanName().' : Temps de chauffage nécessaire pour atteindre la température souhaité est de '.$PowerTime.' s');
							$StartTime = cache::byKey('ChauffeEau::Start::Time::'.$ChauffeEau->getId());	
							$DeltaTime=time()-$StartTime->getValue(0);
							log::add('ChauffeEau','info',$ChauffeEau->getHumanName().' : Le chauffe eau a montée de '.$DeltaTemp.'°C sur une periode de '.$DeltaTime.'s');
							if($ChauffeEau->EvaluateCondition()){
								if($TempActuel <=  $TempSouhaite){
									log::add('ChauffeEau','info','Execution de '.$ChauffeEau->getHumanName());
									$ChauffeEau->powerStart();
								}else{
									cache::set('ChauffeEau::Hysteresis::'.$ChauffeEau->getId(),false, 0);
									$ChauffeEau->EvaluatePowerStop($DeltaTemp);
								}
							}else
								$ChauffeEau->EvaluatePowerStop($DeltaTemp);	
						}else
							$ChauffeEau->EvaluatePowerStop($DeltaTemp);
					}else
						$ChauffeEau->PowerStop();
				break;
				case 3:
					// Mode Stope
					$ChauffeEau->PowerStop();
				break;
			}
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
			$ConigSchedule["url"] = network::getNetworkAccess('external') . '/plugins/reveil/core/api/jeeReveil.php?apikey=' . jeedom::getApiKey('reveil') . '&id=' . $this->getId() . '&prog=' . $ConigSchedule["id"] . '&day=%DAY&heure=%H&minute=%M&seuil=%S';
			$Programation[$key]=$ConigSchedule;
		}
		$this->setConfiguration('programation', $Programation);
	}
	public function UpdateDynamic($id,$days,$heure,$minute,$seuil){
		$Programation=$this->getConfiguration('programation');
		$key=array_search($id, array_column($Programation, 'id'));
		if($key !== FALSE){		
			for($day=0;$day<7;$day++)
				$Programation[$key][$day]=false;
			foreach(str_split($days) as $day)
				$Programation[$key][$day]=true;
			$Programation[$key]["isSeuil"]=false;
			$Programation[$key]["isHoraire"]=false;
			if($heure !='' && $minute !=''){
				$Programation[$key]["Heure"]=$heure;
				$Programation[$key]["Minute"]=$minute;
				$Programation[$key]["isHoraire"]=true;
			}
			if($seuil !=''){
				$Programation[$key]["seuil"]=$seuil;
				$Programation[$key]["isSeuil"]=true;
			}
			$this->setConfiguration('programation',$Programation);
			$this->save();
			$this->NextProg();
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
		$NextProg=$this->NextProg();
		$replace['#NextStart#'] = date('d/m/Y H:i',$NextProg-$PowerTime);
		$replace['#NextStop#'] = date('d/m/Y H:i',$NextProg);
		$replace['#Consigne#'] = jeedom::evaluateExpression($this->getConfiguration('TempSouhaite'));
		$replace['#tempBallon#'] = jeedom::evaluateExpression($this->getConfiguration('TempActuel'));
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
			$ChauffeEau->checkAndUpdateCmd('state',$_option['value']);
		}
	}
	public function powerStart(){
		if(!$this->getCmd(null,'state')->execCmd()){
			$this->checkAndUpdateCmd('state',true);
			log::add('ChauffeEau','info',$this->getHumanName().' : Alimentation électrique du chauffe-eau');
			cache::set('ChauffeEau::Start::Temps::'.$this->getId(),jeedom::evaluateExpression($this->getConfiguration('TempActuel')), 0);
			cache::set('ChauffeEau::Start::Time::'.$this->getId(),time(), 0);
			foreach($this->getConfiguration('ActionOn') as $cmd){
				$this->ExecuteAction($cmd);
			}
		}
	}
	public function PowerStop(){
		if($this->getCmd(null,'state')->execCmd()){
			$this->checkAndUpdateCmd('state',false);
			log::add('ChauffeEau','info',$this->getHumanName().' : Coupure de l\'alimentation électrique du chauffe-eau');
			foreach($this->getConfiguration('ActionOff') as $cmd){
				$this->ExecuteAction($cmd);
			}
		}
	}
	public function EvaluatePowerStop($DeltaTemp){
		if($this->getCmd(null,'state')->execCmd()){
			$StartTime = cache::byKey('ChauffeEau::Start::Time::'.$this->getId());	
			if($DeltaTemp > 1){
				$DeltaTime=time()-$StartTime->getValue(0);
				log::add('ChauffeEau','info',$this->getHumanName().' : Le chauffe eau a montée de '.$DeltaTemp.'°C sur une periode de '.$DeltaTime.'s');
				$this->Puissance($DeltaTemp,$DeltaTime);
			}	
			$this->PowerStop();
		}
	}
	public function NextProg(){
		if(cache::byKey('ChauffeEau::Hysteresis::'.$this->getId())->getValue(false))
			return mktime()+$this->EvaluatePowerTime();
		$nextTime=null;
		foreach($this->getConfiguration('programation') as $ConigSchedule){
			if($ConigSchedule["isHoraire"]){
				$offset=0;
				if(date('H') > $ConigSchedule["Heure"])
					$offset++;
				if(date('H') == $ConigSchedule["Heure"] && date('i') >= $ConigSchedule["Minute"])	
					$offset++;
				for($day=0;$day<7;$day++){
					if($ConigSchedule[date('w')+$day+$offset]){
						$offset+=$day;
						$timestamp=mktime ($ConigSchedule["Heure"], $ConigSchedule["Minute"], 0, date("n") , date("j") , date("Y"))+ (3600 * 24) * $offset;
						if($ConigSchedule["isSeuil"]){
							if(jeedom::evaluateExpression($this->getConfiguration('TempActuel')) > $ConigSchedule["seuil"])
								return mktime()-60;
							cache::set('ChauffeEau::Hysteresis::'.$this->getId(),true, 0);
						}
						break;
					}
				}
				if($nextTime == null || $nextTime > $timestamp)
					$nextTime=$timestamp;
			}elseif($ConigSchedule["isSeuil"] && $ConigSchedule[date('w')]){
				if(jeedom::evaluateExpression($this->getConfiguration('TempActuel')) < $ConigSchedule["seuil"]){
					$nextTime=mktime()+100;
					cache::set('ChauffeEau::Hysteresis::'.$this->getId(),true, 0);
				}
			}
		}
		//log::add('ChauffeEau','debug',$this->getHumanName().' : Le prochain disponibilité est '. date("d/m/Y H:i", $nextTime));
		return $nextTime;
	}
	public function EvaluatePowerTime() {
		//Evaluation du temps necessaire au chauffage de l'eau
		$DeltaTemp = jeedom::evaluateExpression($this->getConfiguration('TempSouhaite'));
		$DeltaTemp-= jeedom::evaluateExpression($this->getConfiguration('TempActuel'));
		$Energie=$this->getConfiguration('Capacite')*$DeltaTemp*4185;
		$PowerTime = round($Energie/ $this->getPuissance());
		$this->refreshWidget();
		return $PowerTime;
	} 
	public function Puissance($DeltaTemp,$DeltaTime) {
		$Energie=$this->getConfiguration('Capacite')*$DeltaTemp*4185;
		$Puissance = round($Energie/$DeltaTime);
		$this->setPuissance($Puissance);
		/*$this->setConfiguration('Puissance',$Puissance);
		$this->save();*/
		log::add('ChauffeEau','debug',$this->getHumanName().' : La puissance estimé du ballon est de '.$Puissance);
	} 
	public function setPuissance($Puissance) {
		$cache = cache::byKey('ChauffeEau::Puissance::'.$this->getId());
		$value = json_decode($cache->getValue('[]'), true);
		$moyenne=$this->getPuissance();
		if($Puissance > $moyenne * 1.3)
			$Puissance =$Puissance * 1.3;
		elseif($Puissance < $moyenne * 0.7)
			$Puissance =$Puissance * 0.7;
		$value[] =$Puissance;
		cache::set('ChauffeEau::Puissance::'.$this->getId(), json_encode(array_slice($value, -10, 10)), 0);
	}
	public function getPuissance() {
		$cache = cache::byKey('ChauffeEau::Puissance::'.$this->getId());
		$value = json_decode($cache->getValue('[]'), true);
		$value[] = intval(trim($this->getConfiguration('Puissance')));
		return round(array_sum($value)/count($value),0);
	}
	public function EvaluateCondition(){
		foreach($this->getConfiguration('condition') as $condition){		
			if (isset($condition['enable']) && $condition['enable'] == 0)
				continue;
			$expression = jeedom::evaluateExpression($condition['expression']);
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
	public function ExecuteAction($cmd) {
		if (isset($cmd['enable']) && $cmd['enable'] == 0)
			return;
		try {
			$options = array();
			if (isset($cmd['options'])) 
				$options = $cmd['options'];
			scenarioExpression::createAndExec('action', $cmd['cmd'], $options);
			log::add('ChauffeEau','debug','Exécution de '.$cmd['cmd']);
		} catch (Exception $e) {
			log::add('ChauffeEau', 'error', __('Erreur lors de l\'éxecution de ', __FILE__) . $cmd['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
		}		
	}
	public static function AddCommande($eqLogic,$Name,$_logicalId,$Type="info", $SubType='binary',$visible,$Template='') {
		$Commande = $eqLogic->getCmd(null,$_logicalId);
		if (!is_object($Commande))
		{
			$Commande = new ChauffeEauCmd();
			$Commande->setId(null);
			$Commande->setLogicalId($_logicalId);
			$Commande->setEqLogic_id($eqLogic->getId());
			$Commande->setName($Name);
			$Commande->setIsVisible($visible);
			$Commande->setType($Type);
			$Commande->setSubType($SubType);
			$Commande->setTemplate('dashboard',$Template );
			$Commande->setTemplate('mobile', $Template);
			$Commande->save();
		}
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
		$Armed=self::AddCommande($this,"Marche forcée","armed","action","other",true,'Commutateur');
		$Armed->setValue($isArmed->getId());
		$Armed->save();
		$Released=self::AddCommande($this,"Désactiver","released","action","other",true,'Commutateur');
		$Released->setValue($isArmed->getId());
		$Released->save();
		$Auto=self::AddCommande($this,"Automatique","auto","action","other",true,'Commutateur');
		$Auto->setValue($isArmed->getId());
		$Auto->save();
		$this->createDeamon();
		cache::set('ChauffeEau::Hysteresis::'.$this->getId(),false, 0);
		$cache = cache::byKey('ChauffeEau::Puissance::'.$this->getId());
		if(count(json_decode($cache->getValue('[]'), true)) == 0)
			cache::set('ChauffeEau::Puissance::'.$this->getId(), json_encode(array_slice(array(intval(trim($this->getConfiguration('Puissance')))), -10, 10)), 0);
	}
	public function createDeamon() {
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
