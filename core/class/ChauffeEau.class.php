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
				continue;
			switch($ChauffeEau->getCmd(null,'etatCommut')->execCmd()){
				case 1:
					// Mode Forcée
					$ChauffeEau->PowerStart();
				break;
				case 2:
					//Mode automatique
					$TempSouhaite = cache::byKey('ChauffeEau::TempSouhaite::'.$this->getId())->getValue(60);
					$TempActuel= jeedom::evaluateExpression($ChauffeEau->getConfiguration('TempActuel'));
					$ChauffeEau->CheckDeltaTemp($TempActuel);
					$NextProg = cache::byKey('ChauffeEau::Stop::Time::'.$ChauffeEau->getId())->getValue(0);
					if($NextProg == 0){
						$NextProg=$ChauffeEau->NextProg();
						if($NextProg != null)
							cache::set('ChauffeEau::Stop::Time::'.$ChauffeEau->getId(),$NextProg, 0);
						else 
							continue;							
					}
					if(mktime() > $NextProg){
						cache::set('ChauffeEau::Stop::Time::'.$ChauffeEau->getId(),0, 0);
						log::add('ChauffeEau','debug',$ChauffeEau->getHumanName().' : Temps supperieur a l\'heure programmée');
						$ChauffeEau->EvaluatePowerStop();
						continue;
					}
					$PowerTime=$ChauffeEau->EvaluatePowerTime();
					if(mktime() > $NextProg-$PowerTime+60){	//Heure actuel > Heure de dispo - Temps de chauffe + Pas d'integration
						if($ChauffeEau->EvaluateCondition()){
							if($TempActuel <=  $TempSouhaite){
								log::add('ChauffeEau','info',$ChauffeEau->getHumanName().' : La température actuel est de '.$TempActuel.'°C et nous desirons atteindre '.  $TempSouhaite.'°C');		
								log::add('ChauffeEau','info',$ChauffeEau->getHumanName().' : Temps de chauffage estimé est de '.$PowerTime.' s');
								$ChauffeEau->PowerStart();
							}
						}	
					}else{
						$StartTemps = cache::byKey('ChauffeEau::Start::Temps::'.$ChauffeEau->getId());
						$DeltaTemp=$TempActuel-$StartTemps->getValue(0);
						if($DeltaTemp > 1 && cache::byKey('ChauffeEau::Hysteresis::'.$ChauffeEau->getId())->getValue(false)){
							if($ChauffeEau->EvaluateCondition()){
								if($TempActuel >=  $TempSouhaite)
									$ChauffeEau->EvaluatePowerStop();
								continue;
							}	
						}
						$ChauffeEau->EvaluatePowerStop();
					}
						
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
		$replace['#Consigne#'] = cache::byKey('ChauffeEau::TempSouhaite::'.$this->getId())->getValue(60);
		$replace['#tempBallon#'] = jeedom::evaluateExpression($this->getConfiguration('TempActuel'));
		$NextProg=$this->NextProg();
		if($replace['#Consigne#'] < $replace['#tempBallon#'])
			$replace['#NextStart#'] = "L'eau n'a pas besoin d'etre chauffé";
		else
			$replace['#NextStart#'] = date('d/m/Y H:i',$NextProg-$PowerTime);
		$replace['#NextStop#'] = date('d/m/Y H:i',$NextProg);
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
		log::add('ChauffeEau','debug','Evenement sur le retour d\'etat : '.json_encode($_option));
		$ChauffeEau = eqLogic::byId($_option['ChauffeEau_id']);
		if (is_object($ChauffeEau) && $ChauffeEau->getIsEnable()) {
			$State=cache::byKey('ChauffeEau::Power::'.$ChauffeEau->getId());
			if(is_object($State)){
				if($_option['value'] && !$State->getValue(false))
					$ChauffeEau->checkAndUpdateCmd('etatCommut',1);
				if(!$_option['value'] && $State->getValue(false))
					$ChauffeEau->checkAndUpdateCmd('etatCommut',3);
				/*if($_option['value'] && $State->getValue(false))
					$ChauffeEau->checkAndUpdateCmd('etatCommut',2);*/
			}
			log::add('ChauffeEau','info',$ChauffeEau->getHumanName().' : l\'etat du chauffe eau est passé a '.$_option['value']);
			if($_option['value'])
				$ChauffeEau->checkAndUpdateCmd('state',1);
			else	
				$ChauffeEau->checkAndUpdateCmd('state',0);
		}
	}
	public function PowerStart(){
		cache::set('ChauffeEau::Power::'.$this->getId(),true, 0);
		if(!$this->getCmd(null,'state')->execCmd()){
			cache::set('ChauffeEau::Hysteresis::'.$this->getId(),true, 0);
			if($this->getConfiguration('Etat') == '')
				$this->checkAndUpdateCmd('state',1);
			log::add('ChauffeEau','info',$this->getHumanName().' : Alimentation électrique du chauffe-eau');
			cache::set('ChauffeEau::Start::Temps::'.$this->getId(),jeedom::evaluateExpression($this->getConfiguration('TempActuel')), 0);
			cache::set('ChauffeEau::Start::Time::'.$this->getId(),time(), 0);
			foreach($this->getConfiguration('ActionOn') as $cmd){
				$this->ExecuteAction($cmd);
			}
		}
	}
	public function PowerStop(){
		cache::set('ChauffeEau::Power::'.$this->getId(),false, 0);
		if($this->getCmd(null,'state')->execCmd()){
			cache::set('ChauffeEau::Hysteresis::'.$this->getId(),false, 0);
			if($this->getConfiguration('Etat') == '')
				$this->checkAndUpdateCmd('state',0);
			log::add('ChauffeEau','info',$this->getHumanName().' : Coupure de l\'alimentation électrique du chauffe-eau');
			foreach($this->getConfiguration('ActionOff') as $cmd){
				$this->ExecuteAction($cmd);
			}
		}
	}
	public function EvaluatePowerStop(){
		cache::set('ChauffeEau::Power::'.$this->getId(),false, 0);
		if($this->getCmd(null,'state')->execCmd()){
			$this->PowerStop();
			$StartTime = cache::byKey('ChauffeEau::Start::Time::'.$this->getId());	
			$StartTemps = cache::byKey('ChauffeEau::Start::Temps::'.$this->getId());
			$TempActuel= jeedom::evaluateExpression($this->getConfiguration('TempActuel'));
			$DeltaTemp=$TempActuel-$StartTemps->getValue(0);
			if($DeltaTemp > 1){
				$DeltaTime=time()-$StartTime->getValue(0);
				log::add('ChauffeEau','info',$this->getHumanName().' : Le chauffe eau a montée de '.$DeltaTemp.'°C sur une periode de '.$DeltaTime.'s');
				$Ratio = cache::byKey('ChauffeEau::Ratio::'.$this->getId());
				$value = json_decode($Ratio->getValue('[]'), true);
				$value[] =intval(round($DeltaTime/$DeltaTemp));
				cache::set('ChauffeEau::Ratio::'.$this->getId(), json_encode(array_slice($value, -10, 10)), 0);
				$this->Puissance($DeltaTemp,$DeltaTime);
				
			}	
		}
	}
	public function CheckDeltaTemp($TempActuel){
		if(!$this->getCmd(null,'state')->execCmd()){
			$LastTemp = cache::byKey('ChauffeEau::LastTemp::'.$this->getId());	
			$DeltaTemp=$TempActuel-$LastTemp->getValue($TempActuel);
			$this->setDeltaTemp($DeltaTemp);
			if($DeltaTemp > $this->getDeltaTemp()){
				log::add('ChauffeEau','info',$this->getHumanName().' : Il y a un chutte de température de '.$DeltaTemp.' => Vous prenez une douche');
			}	
		}
		cache::set('ChauffeEau::LastTemp::'.$this->getId(),$TempActuel, 0);
	}	
	public function setDeltaTemp($DeltaTemp) {
		$cache = cache::byKey('ChauffeEau::DeltaTemp::'.$this->getId());
		$value = json_decode($cache->getValue('[]'), true);
		$Moyenne=intval(trim($this->getDeltaTemp()));
		if($DeltaTemp > $Moyenne * 1.1)
			$DeltaTemp =$Moyenne * 1.1;
		elseif($DeltaTemp < $Moyenne * 0.9)
			$DeltaTemp =$Moyenne * 0.9;
		$value[] =intval(trim($DeltaTemp));
		cache::set('ChauffeEau::DeltaTemp::'.$this->getId(), json_encode(array_slice($value, -10, 10)), 0);
	}
	public function getDeltaTemp() {
		$cache = cache::byKey('ChauffeEau::DeltaTemp::'.$this->getId());
		$value = json_decode($cache->getValue('[]'), true);
		return round(array_sum($value)/count($value),0);
	}
	public function NextProg(){
		$nextTime=null;
		$TempSouhaite=null;
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
								continue;
						}
						$TempSouhaite=$ConigSchedule["consigne"];
						break;
					}
				}
				if($nextTime == null || $nextTime > $timestamp){
					$nextTime=$timestamp;
					cache::set('ChauffeEau::TempSouhaite::'.$this->getId(),jeedom::evaluateExpression($TempSouhaite), 0);
				}
			}elseif($ConigSchedule["isSeuil"] && $ConigSchedule[date('w')]){
				if(jeedom::evaluateExpression($this->getConfiguration('TempActuel')) <= $ConigSchedule["seuil"]){
					log::add('ChauffeEau','info',$this->getHumanName().' : Lancement du cycle d\'Hysteresis');
					cache::set('ChauffeEau::Hysteresis::'.$this->getId(),true, 0);
					cache::set('ChauffeEau::TempSouhaite::'.$this->getId(),jeedom::evaluateExpression($ConigSchedule["consigne"]), 0);
					$nextTime = mktime()+(60*60*24);
				}
			}
		}
		//log::add('ChauffeEau','debug',$this->getHumanName().' : Le prochain disponibilité est '. date("d/m/Y H:i", $nextTime));
		return $nextTime;
	}
	public function EvaluatePowerTime() {		
		$DeltaTemp = cache::byKey('ChauffeEau::TempSouhaite::'.$this->getId())->getValue(60);
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
		$Moyenne=intval(trim($this->getPuissance()));
		if($Puissance > $Moyenne * 1.1)
			$Puissance =$Moyenne * 1.1;
		elseif($Puissance < $Moyenne * 0.9)
			$Puissance =$Moyenne * 0.9;
		$value[] =intval(trim($Puissance));
		cache::set('ChauffeEau::Puissance::'.$this->getId(), json_encode(array_slice($value, -10, 10)), 0);
	}
	public function getPuissance() {
		$cache = cache::byKey('ChauffeEau::Puissance::'.$this->getId());
		$value = json_decode($cache->getValue('[]'), true);
		$value[] = intval(trim($this->getConfiguration('Puissance')));
		return round(array_sum($value)/count($value),0);
	}
	public function EvaluateCondition(){
		foreach($this->getConfiguration('condition') as $Condition){		
			if (isset($Condition['enable']) && $Condition['enable'] == 0)
				continue;
			$expression = jeedom::evaluateExpression($Condition['expression']);
			$message = __('Evaluation de la condition : ['.jeedom::toHumanReadable($Condition['expression']).'][', __FILE__) . trim($expression) . '] = ';
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
			log::add('ChauffeEau','debug','Exécution de '.jeedom::toHumanReadable($cmd['cmd']));
		} catch (Exception $e) {
			log::add('ChauffeEau', 'error', __('Erreur lors de l\'éxecution de ', __FILE__) . $cmd['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
		}		
	}
	public function AddCommande($Name,$_logicalId,$Type="info", $SubType='binary',$visible,$Template='') {
		$Commande = $this->getCmd(null,$_logicalId);
		if (!is_object($Commande))
		{
			$Commande = new ChauffeEauCmd();
			$Commande->setId(null);
			$Commande->setLogicalId($_logicalId);
			$Commande->setEqLogic_id($this->getId());
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
		$state=$this->AddCommande("Etat du chauffe-eau","state","info", 'binary',true);
		$state->event(false);
		$state->setCollectDate(date('Y-m-d H:i:s'));
		$state->save();
		$isArmed=$this->AddCommande("Etat fonctionnement","etatCommut","info","numeric",false);
		$isArmed->event(2);
		$isArmed->setCollectDate(date('Y-m-d H:i:s'));
		$isArmed->save();
		$Armed=$this->AddCommande("Marche forcée","armed","action","other",true,'Commutateur');
		$Armed->setValue($isArmed->getId());
		$Armed->save();
		$Released=$this->AddCommande("Désactiver","released","action","other",true,'Commutateur');
		$Released->setValue($isArmed->getId());
		$Released->save();
		$Auto=$this->AddCommande("Automatique","auto","action","other",true,'Commutateur');
		$Auto->setValue($isArmed->getId());
		$Auto->save();
		$this->createDeamon();
		cache::set('ChauffeEau::Hysteresis::'.$this->getId(),false, 0);
		$Puissance = cache::byKey('ChauffeEau::Puissance::'.$this->getId());
		if(count(json_decode($Puissance->getValue('[]'), true)) == 0)
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
