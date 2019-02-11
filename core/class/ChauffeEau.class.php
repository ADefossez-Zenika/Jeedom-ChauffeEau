<?php
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
class ChauffeEau extends eqLogic {
	public static function deamon_info() {
		$return = array();
		$return['log'] = 'ChauffeEau';
		$return['launchable'] = 'ok';
		$return['state'] = 'nok';
		foreach(eqLogic::byType('ChauffeEau') as $ChauffeEau){
			if($ChauffeEau->getIsEnable() && $ChauffeEau->getConfiguration('Etat') != ''){
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
			if($ChauffeEau->getIsEnable()){
				$ChauffeEau->CheckChauffeEau();	
				if ($ChauffeEau->getConfiguration('RepeatCmd') == "cron"){
					$State=cache::byKey('ChauffeEau::Power::'.$ChauffeEau->getId());
					if(is_object($State)){
						if($State->getValue(false))
							$ChauffeEau->PowerStart();
						else
							$ChauffeEau->PowerStop();
					}
				}
			}
		}
	}
	public static function cron5() {
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok') 
			return;
		if ($deamon_info['state'] != 'ok') 
			return;
		foreach(eqLogic::byType('ChauffeEau') as $ChauffeEau){		
			if($ChauffeEau->getIsEnable()){
				if ($ChauffeEau->getConfiguration('RepeatCmd') == "cron5"){
					$State=cache::byKey('ChauffeEau::Power::'.$ChauffeEau->getId());
					if(is_object($State)){
						if($State->getValue(false))
							$ChauffeEau->PowerStart();
						else
							$ChauffeEau->PowerStop();
					}
				}
			}
		}
	}
	public static function cron15() {
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok') 
			return;
		if ($deamon_info['state'] != 'ok') 
			return;
		foreach(eqLogic::byType('ChauffeEau') as $ChauffeEau){		
			if($ChauffeEau->getIsEnable()){
				if ($ChauffeEau->getConfiguration('RepeatCmd') == "cron15"){
					$State=cache::byKey('ChauffeEau::Power::'.$ChauffeEau->getId());
					if(is_object($State)){
						if($State->getValue(false))
							$ChauffeEau->PowerStart();
						else
							$ChauffeEau->PowerStop();
					}
				}
			}
		}
	}
	public static function cron30() {
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok') 
			return;
		if ($deamon_info['state'] != 'ok') 
			return;
		foreach(eqLogic::byType('ChauffeEau') as $ChauffeEau){		
			if($ChauffeEau->getIsEnable()){
				if ($ChauffeEau->getConfiguration('RepeatCmd') == "cron30"){
					$State=cache::byKey('ChauffeEau::Power::'.$ChauffeEau->getId());
					if(is_object($State)){
						if($State->getValue(false))
							$ChauffeEau->PowerStart();
						else
							$ChauffeEau->PowerStop();
					}
				}
			}
		}
	}	
	public static function cronHourly() {
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok') 
			return;
		if ($deamon_info['state'] != 'ok') 
			return;
		foreach(eqLogic::byType('ChauffeEau') as $ChauffeEau){		
			if($ChauffeEau->getIsEnable()){
				if ($ChauffeEau->getConfiguration('RepeatCmd') == "cronHourly"){
					$State=cache::byKey('ChauffeEau::Power::'.$ChauffeEau->getId());
					if(is_object($State)){
						if($State->getValue(false))
							$ChauffeEau->PowerStart();
						else
							$ChauffeEau->PowerStop();
					}
				}
			}
		}
	}
	public static function cronDaily() {
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok') 
			return;
		if ($deamon_info['state'] != 'ok') 
			return;
		foreach(eqLogic::byType('ChauffeEau') as $ChauffeEau){		
			if($ChauffeEau->getIsEnable()){
				if ($ChauffeEau->getConfiguration('RepeatCmd') == "cronDaily"){
					$State=cache::byKey('ChauffeEau::Power::'.$ChauffeEau->getId());
					if(is_object($State)){
						if($State->getValue(false))
							$ChauffeEau->PowerStart();
						else
							$ChauffeEau->PowerStop();
					}
				}
			}
		}
	}
	public function preSave() {
		$Programation=$this->getConfiguration('programation');
		foreach($Programation as $key => $ConigSchedule){
			if($ConigSchedule["id"] == ''){
				$id=rand(0,32767);
				$ConigSchedule["id"]=$id;
			}
			$ConigSchedule["url"] = network::getNetworkAccess('external') . '/plugins/reveil/core/api/jeeReveil.php?apikey=' . jeedom::getApiKey('reveil') . '&id=' . $this->getId() . '&prog=' . $ConigSchedule["id"] . '&day=%DAY&heure=%H&minute=%M&seuil=%S';
			$Programation[$key]=$ConigSchedule;
		}
		$this->setConfiguration('programation', $Programation);
	}
	public function CheckChauffeEau(){
		if (!$this->getIsEnable()) 
			return;
		switch($this->getCmd(null,'etatCommut')->execCmd()){
			case 'Marche Forcée':
				$this->PowerStart();
			break;
			case 'Automatique':
				$TempSouhaite = $this->getCmd(null,'consigne')->execCmd();
				$TempActuel= jeedom::evaluateExpression($this->getConfiguration('TempActuel'));
				$this->checkAndUpdateCmd('TempActuel',$TempActuel);	
				$this->CheckDeltaTemp($TempActuel);
				//if($this->getConfiguration('BacteryProtect'))
					$this->checkBacteryProtect($TempActuel);
				$NextProg=$this->NextProg();
				if($NextProg === false)
					return;
				$NextStart = DateTime::createFromFormat("d/m/Y H:i", $this->getCmd(null,'NextStart')->execCmd());
				$NextStop = DateTime::createFromFormat("d/m/Y H:i", $this->getCmd(null,'NextStop')->execCmd());
				if(mktime() > $NextStop->getTimestamp()){
					//Action si le cycle est terminée
					$NextProg=$this->EvaluateDelestage($NextStart->getTimestamp());
					if($NextStart->getTimestamp() === false){
						$this->DispoEnd();
						return;
					}
				}elseif(mktime() > $NextStart->getTimestamp()){
					$this->checkHysteresis($TempActuel, $TempSouhaite);
				}
			break;
			case 'Off':
				$this->PowerStop();
			break;
			case 'Délestage':
				$this->PowerStop();
				cache::set('ChauffeEau::Delestage::'.$this->getId(),true, 0);
			break;
		}
	}
	public function checkHysteresis($Temperature, $TemperatureConsigne, $TemperatureBasse=null){
		// Regulation a +- 0.5°C
		if($TemperatureBasse == null)
			$TemperatureBasse = $TemperatureConsigne - 0.5;
		$TemperatureHaute = $TemperatureConsigne + 0.5;
		if($Temperature < $TemperatureBasse){
			if($this->EvaluateCondition()){	
				if(!$this->getCmd(null,'state')->execCmd())
					$this->PowerStart();	
			}
		}elseif($Temperature > $TemperatureHaute){
			$this->EvaluatePowerStop();
		}
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
			log::add('ChauffeEau','info',$ChauffeEau->getHumanName().' : l\'etat du chauffe eau est passé a '.$_option['value']);
			$ChauffeEau->checkAndUpdateCmd('state',$_option['value']);
			$State=cache::byKey('ChauffeEau::Power::'.$ChauffeEau->getId());
			if(is_object($State)){
				if($_option['value'] && !$State->getValue(false))
					$ChauffeEau->checkAndUpdateCmd('etatCommut','Marche Forcée');
				if(!$_option['value'] && $State->getValue(false))
					$ChauffeEau->checkAndUpdateCmd('etatCommut','Off');
				/*if($_option['value'] && $State->getValue(false))
					$ChauffeEau->checkAndUpdateCmd('etatCommut','Automatique');*/
				cache::set('ChauffeEau::Repeat::'.$ChauffeEau->getId(),false, 0);
				$ChauffeEau->CheckChauffeEau();
			}
		}
	}
	public function PowerStart(){
		cache::set('ChauffeEau::Power::'.$this->getId(),true, 0);
		//cache::set('ChauffeEau::Hysteresis::'.$this->getId(),true, 0);
		if($this->getConfiguration('Etat') == '')
			$this->checkAndUpdateCmd('state',1);
		log::add('ChauffeEau','info',$this->getHumanName().' : Alimentation électrique du chauffe-eau');
		cache::set('ChauffeEau::Start::Temperature::'.$this->getId(),jeedom::evaluateExpression($this->getConfiguration('TempActuel')), 0);
		cache::set('ChauffeEau::Start::Time::'.$this->getId(),time(), 0);
		if(cache::byKey('ChauffeEau::Repeat::'.$this->getId())->getValue(true)){
			foreach($this->getConfiguration('Action') as $cmd){
				foreach($cmd['declencheur'] as $declencheur){
					if($declencheur == 'on')
						$this->ExecuteAction($cmd);
				}
			}
		}else{
			cache::set('ChauffeEau::Repeat::'.$this->getId(),true);
		}
	}
	public function PowerStop(){
		cache::set('ChauffeEau::Power::'.$this->getId(),false, 0);
		if($this->getConfiguration('Etat') == '')
			$this->checkAndUpdateCmd('state',0);
		log::add('ChauffeEau','info',$this->getHumanName().' : Coupure de l\'alimentation électrique du chauffe-eau');
		if(cache::byKey('ChauffeEau::Repeat::'.$this->getId())->getValue(true)){
			foreach($this->getConfiguration('Action') as $cmd){
				foreach($cmd['declencheur'] as $declencheur){
					if($declencheur == 'off')
						$this->ExecuteAction($cmd);
				}
			}
		}else{
			cache::set('ChauffeEau::Repeat::'.$this->getId(),true);
		}
	}
	public function DispoEnd(){
		cache::set('ChauffeEau::Delestage::'.$this->getId(),false, 0);
		$this->checkAndUpdateCmd('NextStop',date('d/m/Y H:i'));
		log::add('ChauffeEau','debug',$this->getHumanName().' : Temps suprieur a l\'heure programmée');
		if(cache::byKey('ChauffeEau::Repeat::'.$this->getId())->getValue(true))
			$this->EvaluatePowerStop();
		else
			cache::set('ChauffeEau::Repeat::'.$this->getId(),true);
		foreach($this->getConfiguration('Action') as $cmd){
			foreach($cmd['declencheur'] as $declencheur){
				if($declencheur == 'dispo')
					$this->ExecuteAction($cmd);
			}
		}
	}
	public function EvaluatePowerStop(){
		cache::set('ChauffeEau::Power::'.$this->getId(),false, 0);
		if($this->getCmd(null,'state')->execCmd()){
			$this->PowerStop();
			$TempActuel= jeedom::evaluateExpression($this->getConfiguration('TempActuel'));
			$this->checkAndUpdateCmd('TempActuel',$TempActuel);	
			$StartTime = cache::byKey('ChauffeEau::Start::Time::'.$this->getId());	
			$StartTemps = cache::byKey('ChauffeEau::Start::Temperature::'.$this->getId());
			$DeltaTemp=$TempActuel-$StartTemps->getValue($TempActuel);
			if($DeltaTemp > 1){
				$DeltaTime=time()-$StartTime->getValue(time());
				if($DeltaTime > 1){
					log::add('ChauffeEau','info',$this->getHumanName().' : Élévation de température de '.$DeltaTemp.'°C sur une période de '.$DeltaTime.'s');
					$Ratio = cache::byKey('ChauffeEau::Ratio::'.$this->getId());
					$value = json_decode($Ratio->getValue('[]'), true);
					$value[] =intval(round($DeltaTime/$DeltaTemp));
					cache::set('ChauffeEau::Ratio::'.$this->getId(), json_encode(array_slice($value, -10, 10)), 0);
					$this->Puissance($DeltaTemp,$DeltaTime);
				}
			}	
		}
	}
	public function EvaluateDelestage($NextProg){
		$Delestage = cache::byKey('ChauffeEau::Delestage::'.$this->getId())->getValue(false);
		if($Delestage){
			switch($this->getConfiguration('delestage')){
				case 'Temp':
					$NextProg = $NextProg + $this->EvaluatePowerTime();
					$this->checkAndUpdateCmd('NextStop',date('d/m/Y H:i',$NextProg));
				return 	$NextProg;
				case 'Heure':
				return false;
				case '30':
					$NextProg = $NextProg+(30*60);
					$this->checkAndUpdateCmd('NextStop',date('d/m/Y H:i',$NextProg));
				return $NextProg;
			}
		}
		return false;
	}
	public function CheckDeltaTemp($TempActuel){
		if(!$this->getCmd(null,'state')->execCmd()){
			$LastTemp = cache::byKey('ChauffeEau::LastTemp::'.$this->getId());	
			$DeltaTemp=$TempActuel-$LastTemp->getValue($TempActuel);
			$this->setDeltaTemp($DeltaTemp);
			if($DeltaTemp > $this->getDeltaTemp() * 0.8){//Recherche d'une chute de plus de 20%
				log::add('ChauffeEau','info',$this->getHumanName().' : Il y a un chute de température de '.$DeltaTemp.' => Vous prenez une douche');
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
		$validProg=false;
		$TempActuel=jeedom::evaluateExpression($this->getConfiguration('TempActuel'));
		$PowerTime=$this->EvaluatePowerTime();
		$TempSouhaite=60;
		foreach($this->getConfiguration('programation') as $ConigSchedule){
			$TempSouhaite= jeedom::evaluateExpression($ConigSchedule["consigne"]);
			if($ConigSchedule["isHoraire"]){
				$offset=0;
				if(date('H') > $ConigSchedule["Heure"])
					$offset++;
				if(date('H') == $ConigSchedule["Heure"] && date('i') >= $ConigSchedule["Minute"])	
					$offset++;
				for($day=0;$day<7;$day++){
					$jour=date('w')+$day+$offset;
					if($jour > 6)
						$jour= $jour-7;
					if($ConigSchedule[$jour]){
						$offset+=$day;
						$timestamp=mktime ($ConigSchedule["Heure"], $ConigSchedule["Minute"], 0, date("n") , date("j") , date("Y"))+ (3600 * 24) * $offset;
						/*if($ConigSchedule["isSeuil"]){
							if($TempActuel > $ConigSchedule["seuil"])
								continue;
						}*/
						break;
					}
				}
				if($nextTime == null || $nextTime > $timestamp){
					$validProg = true;
					$nextTime=$timestamp;
				}
			}elseif($ConigSchedule["isSeuil"] && $ConigSchedule[date('w')]){
				$validProg = false;
				$this->checkHysteresis($TempActuel, $TempSouhaite, $ConigSchedule["seuil"]);
				$nextTime = mktime()+$PowerTime;	
			}
		}
		if(!$this->getCmd(null,'state')->execCmd()){
			$this->checkAndUpdateCmd('NextStop',date('d/m/Y H:i',$nextTime));
			$this->checkAndUpdateCmd('NextStart',date('d/m/Y H:i',$nextTime-$PowerTime));
		}
		$this->checkAndUpdateCmd('consigne',jeedom::evaluateExpression($TempSouhaite));
		$this->checkAndUpdateCmd('TempActuel',$TempActuel);
		//log::add('ChauffeEau','debug',$this->getHumanName().' : Le prochain disponibilité est '. date("d/m/Y H:i", $nextTime));
		if(!$validProg)	
			return false;
		return true;
	}
	public function EvaluatePowerTime() {	
		$PowerTime = 0;
		list($DeltaTemp,$TempsAdditionel) = $this->BacteryProtect();
		if($DeltaTemp > 0){
			$Energie=$this->getConfiguration('Capacite')*$DeltaTemp*4185;
			$PowerTime = round($Energie/ $this->getPuissance());
			$PowerTime += $TempsAdditionel;	
			if($this->getConfiguration('TempsAdditionel') != '' )
				$PowerTime += $this->getConfiguration('TempsAdditionel') * 60;	
			$this->checkAndUpdateCmd('PowerTime',$PowerTime);
			$this->refreshWidget();
		}
		return $PowerTime;
	} 
	public function BacteryProtect(){		
		$TempActuel = jeedom::evaluateExpression($this->getConfiguration('TempActuel'));
		$this->checkAndUpdateCmd('TempActuel',$TempActuel);	
		if($this->getConfiguration('BacteryProtect')){
			if($TempActuel < 20 && $TempActuel > 55){
				$Temps = 0;
				$DeltaTemp = $this->getCmd(null,'consigne')->execCmd() - $TempActuel;
			}elseif($TempActuel > 40 && $TempActuel < 55){
				$Temps = 32 * 60;
				$DeltaTemp = 60 - $TempActuel;
			}else{
				$Temps = 2 * 60;
				$DeltaTemp = 65 - $TempActuel;
			}
		}else{
			$Temps=0;
			$DeltaTemp = $this->getCmd(null,'consigne')->execCmd() - $TempActuel;
		}
		return array($DeltaTemp, $Temps);
	}
	public function checkBacteryProtect($TempActuel){
		$BacteryProtect = $this->getCmd(null,'BacteryProtect')->execCmd();
		if($BacteryProtect){
			$TempsBacteryProtect = cache::byKey('ChauffeEau::BacteryProtect::Start::'.$this->getId());
			if($TempActuel > 70){
				if(!is_object($TempsBacteryProtect))
					cache::set('ChauffeEau::BacteryProtect::Start::'.$this->getId(), time(), 0);	
				if($TempsBacteryProtect->getValue(time()) - time() > 1*60)
					$this->checkAndUpdateCmd('BacteryProtect',false);
			}elseif($TempActuel > 65){
				if(!is_object($TempsBacteryProtect))
					cache::set('ChauffeEau::BacteryProtect::Start::'.$this->getId(), time(), 0);	
				if($TempsBacteryProtect->getValue(time()) - time() > 2*60)
					$this->checkAndUpdateCmd('BacteryProtect',false);
			}elseif($TempActuel > 60){
				if(!is_object($TempsBacteryProtect))
					cache::set('ChauffeEau::BacteryProtect::Start::'.$this->getId(), time(), 0);	
				if($TempsBacteryProtect->getValue(time()) - time() > 30*60)
					$this->checkAndUpdateCmd('BacteryProtect',false);
			}else{
				if(is_object($TempsBacteryProtect))
					$TempsBacteryProtect->remove();
				$TempsBacteryProtectAlert = cache::byKey('ChauffeEau::BacteryProtect::Alert::'.$this->getId());
				if($TempActuel > 25 && $TempActuel < 47){
					if(!is_object($TempsBacteryProtectAlert))
						cache::set('ChauffeEau::BacteryProtect::Alert::'.$this->getId(), time(), 0);
					if($TempsBacteryProtectAlert->getValue(time()) - time() > 4*60*60)	
						$this->checkAndUpdateCmd('BacteryProtect',true);
				}
			}
		}
	}
	public function Puissance($DeltaTemp,$DeltaTime) {
		$Energie=$this->getConfiguration('Capacite')*$DeltaTemp*4185;
		$Puissance = round($Energie/$DeltaTime);
		$this->setPuissance($Puissance);
		log::add('ChauffeEau','debug',$this->getHumanName().' : La puissance estimée du ballon est de '.$Puissance.' Watt');
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
			$_scenario = null;
			$expression = scenarioExpression::setTags($Condition['expression'], $_scenario, true);
			$message = __('Evaluation de la condition : ['.jeedom::toHumanReadable($Condition['expression']).'][', __FILE__) . trim($expression) . '] = ';
			$result = evaluate($expression);
			$message .=$this->boolToText($result);
			log::add('ChauffeEau','info',$this->getHumanName().'[Condition] : '.$message);
			if(!$result)
				return false;	
		}
		return true;
	}
	public function boolToText($value){
		if (is_bool($value)) {
			if ($value) 
				return __('Vrai', __FILE__);
			else 
				return __('Faux', __FILE__);
		} else 
			return $value;
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
	public function AddCommande($Name,$_logicalId,$Type="info", $SubType='binary',$visible,$unite='',$Template='') {
		$Commande = $this->getCmd(null,$_logicalId);
		if (!is_object($Commande)){
			$Commande = new ChauffeEauCmd();
			$Commande->setId(null);
			$Commande->setLogicalId($_logicalId);
			$Commande->setEqLogic_id($this->getId());
			$Commande->setName($Name);
			$Commande->setIsVisible($visible);
			$Commande->setType($Type);
			$Commande->setSubType($SubType);
			$Commande->setUnite($unite);
			$Commande->setTemplate('dashboard',$Template);
			$Commande->setTemplate('mobile', $Template);
			$Commande->save();
		}
		return $Commande;
	}
	public function preRemove() {
		self::deamon_stop();
	}
	public function postSave() {
		$this->AddCommande("Date de début","NextStart","info",'string',true);
		$this->AddCommande("Date de fin","NextStop","info",'string',true);
		$this->AddCommande("Temps estimé","PowerTime","info",'numeric',true,'s');
		$state=$this->AddCommande("Etat du chauffe-eau","state","info",'binary',true,'','State');
		$state->event(false);
		$state->setCollectDate(date('Y-m-d H:i:s'));
		$state->save();
		$isArmed=$this->AddCommande("Etat fonctionnement","etatCommut","info","string",true);
		$isArmed->event('Automatique');
		$isArmed->setCollectDate(date('Y-m-d H:i:s'));
		$isArmed->save();
		$Armed=$this->AddCommande("Marche forcée","armed","action","other",true);
		$Armed->setValue($isArmed->getId());
		$Armed->save();
		$Released=$this->AddCommande("Désactiver","released","action","other",true);
		$Released->setValue($isArmed->getId());
		$Released->save();
		$Auto=$this->AddCommande("Automatique","auto","action","other",true);
		$Auto->setValue($isArmed->getId());
		$Auto->save();
		$Auto=$this->AddCommande("Délestage","delestage","action","other",true);
		$Auto->setValue($isArmed->getId());
		$Auto->save();
		$this->AddCommande("Risque","BacteryProtect","info",'binary',true,'alert');
		$this->AddCommande("Température du ballon","TempActuel","info",'numeric',true,'°C');
		$this->AddCommande("Consigne appliquée","consigne","info",'numeric',true,'°C','Consigne');
		$this->createDeamon();
		cache::set('ChauffeEau::Hysteresis::'.$this->getId(),false, 0);
		$Puissance = cache::byKey('ChauffeEau::Puissance::'.$this->getId());
		if(count(json_decode($Puissance->getValue('[]'), true)) == 0)
			cache::set('ChauffeEau::Puissance::'.$this->getId(), json_encode(array(intval(trim($this->getConfiguration('Puissance'))))), 0);
		$this->CheckChauffeEau();
	}
	public function createDeamon() {
		$listener = listener::byClassAndFunction('ChauffeEau', 'pull', array('ChauffeEau_id' => $this->getId()));
		if (is_object($listener)) 	
			$listener->remove();
      		$cache = cache::byKey('ChauffeEau::Start::Temperature::'.$this->getId());
      		if(is_object($cache))
          		$cache->remove();
      		$cache = cache::byKey('ChauffeEau::Power::'.$this->getId());
      		if(is_object($cache))
          		$cache->remove();
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
			$state=cmd::byId(str_replace('#','',$this->getConfiguration('Etat')));
			if(is_object($state))
				$this->checkAndUpdateCmd('state',$state->execCmd());
		}
		$this->CheckChauffeEau();
	}
}
class ChauffeEauCmd extends cmd {
	public function execute($_options = null) {
		switch($this->getLogicalId()){
			case 'armed':
				$this->getEqLogic()->checkAndUpdateCmd('etatCommut','Marche Forcée');
			break;
			case 'released':
				$this->getEqLogic()->checkAndUpdateCmd('etatCommut','Off');
			break;
			case 'auto':
				$this->getEqLogic()->checkAndUpdateCmd('etatCommut','Automatique');
			break;
			case 'delestage':
				$this->getEqLogic()->checkAndUpdateCmd('etatCommut','Délestage');
			break;
		}
		$this->getEqLogic()->CheckChauffeEau();
	}
}
?>
