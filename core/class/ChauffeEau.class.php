<?php
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
class ChauffeEau extends eqLogic {
	const _Temperatures=array(0,20,60,100);
	const _Pertes=array(0.00001,0.0003,0.01,0.09);
	const _TempsNettoyage= 4* 60 * 60;
	const _TempsNettoyageRapide= 4* 60 * 60;
	
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
			$cache = cache::byKey('ChauffeEau::Run::'.$ChauffeEau->getId());
			if (is_object($cache)) 	
				$cache->remove();
			$CartoChauffeEau = cache::byKey('ChauffeEau::DeltaTemp::'.$ChauffeEau->getId());
			if (is_object($CartoChauffeEau)) 	
				$CartoChauffeEau->remove();
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
					if(cache::byKey('ChauffeEau::Power::'.$ChauffeEau->getId())->getValue(false))
						$ChauffeEau->ActionPowerStart();
					else
						$ChauffeEau->ActionPowerStop();
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
					if(cache::byKey('ChauffeEau::Power::'.$ChauffeEau->getId())->getValue(false))
						$ChauffeEau->ActionPowerStart();
					else
						$ChauffeEau->ActionPowerStop();
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
					if(cache::byKey('ChauffeEau::Power::'.$ChauffeEau->getId())->getValue(false))
						$ChauffeEau->ActionPowerStart();
					else
						$ChauffeEau->ActionPowerStop();
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
					if(cache::byKey('ChauffeEau::Power::'.$ChauffeEau->getId())->getValue(false))
						$ChauffeEau->ActionPowerStart();
					else
						$ChauffeEau->ActionPowerStop();
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
					if(cache::byKey('ChauffeEau::Power::'.$ChauffeEau->getId())->getValue(false))
						$ChauffeEau->ActionPowerStart();
					else
						$ChauffeEau->ActionPowerStop();
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
					if(cache::byKey('ChauffeEau::Power::'.$ChauffeEau->getId())->getValue(false))
						$ChauffeEau->ActionPowerStart();
					else
						$ChauffeEau->ActionPowerStop();
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
			$ConigSchedule["url"] = network::getNetworkAccess('external') . '/plugins/ChauffeEau/core/api/jeeChauffeEau.php?apikey=' . jeedom::getApiKey('ChauffeEau') . '&id=' . $this->getId() . '&prog=' . $ConigSchedule["id"] . '&day=%DAY&heure=%H&minute=%M&seuil=%S';
			$Programation[$key]=$ConigSchedule;
		}
		$this->setConfiguration('programation', $Programation);
	}
	public function CheckChauffeEau(){
		if (!$this->getIsEnable()) 
			return;
		switch($this->getCmd(null,'etatCommut')->execCmd()){
			case 'Marche Forcée':
				if(!$this->getCmd(null,'state')->execCmd())
					$this->PowerStart();
				cache::set('ChauffeEau::Run::'.$this->getId(),true, 0);
			break;
			case 'Automatique':
				$TempSouhaite = $this->getCmd(null,'consigne')->execCmd();
				$this->EstimateTempActuel();	
				$TempActuel=$this->getCmd(null,'TempActuel')->execCmd();
				if($this->getConfiguration('BacteryProtect'))
					$this->checkBacteryProtect($TempActuel);
				$NextProg=$this->NextProg();
				if($NextProg === false)
					return;
				$NextStart = DateTime::createFromFormat("d/m/Y H:i", $this->getCmd(null,'NextStart')->execCmd());
				$NextStop = DateTime::createFromFormat("d/m/Y H:i", $this->getCmd(null,'NextStop')->execCmd());
				if(mktime() > $NextStop->getTimestamp()){
					//Action si le cycle est terminée
					$NextProg=$this->EvaluateDelestage($NextStop->getTimestamp());
					if($NextProg === false){
						$this->DispoEnd();
						return;
					}
				}elseif(mktime() > $NextStart->getTimestamp()){
					$this->checkHysteresis($TempActuel, $TempSouhaite);
				}else{
					$this->PowerStop();
				}
					
			break;
			case 'Off':
				$this->PowerStop();
				cache::set('ChauffeEau::Run::'.$this->getId(),false, 0);
			break;
			case 'Délestage':
				$this->PowerStop();
				cache::set('ChauffeEau::Delestage::'.$this->getId(),true, 0);
			break;
		}
	}
	public function checkHysteresis($Temperature, $TemperatureConsigne, $TemperatureBasse=null){
		// Regulation a +- 0.5°C		
		$Hysteresis=cache::byKey('ChauffeEau::Hysteresis::'.$this->getId())->getValue(0.5);
		if($TemperatureBasse == null)
			$TemperatureBasse = $TemperatureConsigne - $Hysteresis;
		$TemperatureHaute = $TemperatureConsigne + $Hysteresis;
		if($Temperature <= $TemperatureBasse){
			if($this->EvaluateCondition()){	
				$this->PowerStart();	
			}else{
				$this->PowerStop();
			}
		}elseif($Temperature >= $TemperatureHaute){
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
			switch($_option['event_id']){
				case str_replace('#','',$ChauffeEau->getConfiguration('TempActuel')):
					$ChauffeEau->setDeltaTemperature($_option['value']);
					$ChauffeEau->checkAndUpdateCmd('TempActuel',$_option['value']);
				break;
				case str_replace('#','',$ChauffeEau->getConfiguration('Etat')):
					log::add('ChauffeEau','info',$ChauffeEau->getHumanName().' : l\'etat du chauffe eau est passé a '.$_option['value']);
					$State=cache::byKey('ChauffeEau::Power::'.$ChauffeEau->getId());
					if(is_object($State)){
						if($_option['value'] && !$State->getValue(false))
							$ChauffeEau->checkAndUpdateCmd('etatCommut','Marche Forcée');
						if(!$_option['value'] && $State->getValue(false))
							$ChauffeEau->checkAndUpdateCmd('etatCommut','Off');
						/*if($_option['value'] && $Stat->getValue(false))
							$ChauffeEau->checkAndUpdateCmd('etatCommut','Automatique');*/
					}
					$ChauffeEau->checkAndUpdateCmd('state',$_option['value']);
				break;
			}
			$ChauffeEau->CheckChauffeEau();
		}
	}
	public function PowerStart(){
		if($this->getCmd(null,'state')->execCmd() == 1)
			return;
		cache::set('ChauffeEau::Power::'.$this->getId(),true, 0);
		//cache::set('ChauffeEau::Hysteresis::'.$this->getId(),true, 0);
		if($this->getConfiguration('Etat') == '')
			$this->checkAndUpdateCmd('state',1);
		log::add('ChauffeEau','info',$this->getHumanName().' : Alimentation électrique du chauffe-eau');
		$this->EstimateTempActuel();	
		$TempActuel=$this->getCmd(null,'TempActuel')->execCmd();
		cache::set('ChauffeEau::Start::Temperature::'.$this->getId(),$TempActuel, 0);
		cache::set('ChauffeEau::Start::Time::'.$this->getId(),time(), 0);
		cache::set('ChauffeEau::Run::'.$this->getId(),true, 0);
		$this->ActionPowerStart();
	}
	public function ActionPowerStart(){
		foreach($this->getConfiguration('Action') as $cmd){
			foreach($cmd['declencheur'] as $declencheur){
				if($declencheur == 'on')
					$this->ExecuteAction($cmd);
			}
		}
	}
	public function PowerStop(){
		if($this->getCmd(null,'state')->execCmd() == 0)
			return;
		cache::set('ChauffeEau::Power::'.$this->getId(),false, 0);
		cache::set('ChauffeEau::BacteryProtect::'.$this->getId(), false, 0);
		if($this->getConfiguration('Etat') == '')
			$this->checkAndUpdateCmd('state',0);
		log::add('ChauffeEau','info',$this->getHumanName().' : Coupure de l\'alimentation électrique du chauffe-eau');
			$this->ActionPowerStop();
	}
	public function ActionPowerStop(){
		foreach($this->getConfiguration('Action') as $cmd){
			foreach($cmd['declencheur'] as $declencheur){
				if($declencheur == 'off')
					$this->ExecuteAction($cmd);
			}
		}
	}
	public function DispoEnd(){
		cache::set('ChauffeEau::Run::'.$this->getId(),false, 0);
		cache::set('ChauffeEau::Delestage::'.$this->getId(),false, 0);
		log::add('ChauffeEau','debug',$this->getHumanName().' : Fin du cycle de chauffe');
		$this->EvaluatePowerStop();
		foreach($this->getConfiguration('Action') as $cmd){
			foreach($cmd['declencheur'] as $declencheur){
				if($declencheur == 'dispo')
					$this->ExecuteAction($cmd);
			}
		}
		log::add('ChauffeEau','debug',$this->getHumanName().'[Caracterisation Température] '.json_encode(cache::byKey('ChauffeEau::DeltaTemp::'.$this->getId())->getValue('[]')));
	}
	public function EvaluatePowerStop(){
		if($this->getCmd(null,'state')->execCmd() == 0)
			return;
		$this->PowerStop();
		$this->EstimateTempActuel();	
		$TempActuel=$this->getCmd(null,'TempActuel')->execCmd();
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
	public function EvaluateDelestage($NextStop){
		$Delestage = cache::byKey('ChauffeEau::Delestage::'.$this->getId())->getValue(false);
		if($Delestage){
			switch($this->getConfiguration('delestage')){
				case 'Temp':
					$this->EstimateTempActuel();	
					$TempActuel=$this->getCmd(null,'TempActuel')->execCmd();
					$Consigne=$this->getCmd(null,'consigne')->execCmd();
					return $NextStop + $this->EvaluatePowerTime($Consigne,$TempActuel);
				case 'Heure':
					return false;
				case '30':
					return $NextStop+(30*60);
			}
		}
		return false;
	}
	public function NextProg(){
		$validProg=false;
		$this->EstimateTempActuel();	
		$TempActuel=$this->getCmd(null,'TempActuel')->execCmd();
		$TempSouhaite=60;
		foreach($this->getConfiguration('programation') as $ConigSchedule){
			if($ConigSchedule["isSeuil"] && $ConigSchedule[date('w')]){
				$TempConsigne= jeedom::evaluateExpression($ConigSchedule["consigne"]);
				$TempSeuil= jeedom::evaluateExpression($ConigSchedule["seuil"]);
				$PowerTime=$this->EvaluatePowerTime($TempConsigne,$TempSeuil);
				$DeltaTime = round(($TempActuel - $TempSeuil) / $this->getDeltaTemperature($TempActuel));
                		$timestamp = time() + $PowerTime + $DeltaTime;
				if($nextTime == null || time() <= $timestamp){
					if($nextTime == null || $nextTime > $timestamp){
						cache::set('ChauffeEau::Hysteresis::'.$this->getId(),$ConigSchedule["hysteresis"], 0);
						$this->checkHysteresis($TempActuel, $TempConsigne, $TempSeuil);
						$validProg = false;
						$nextTime = $timestamp;
						$TempSouhaite = $TempConsigne;
					}
				}
			}
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
						break;
					}
				}
				if($nextTime == null || $nextTime > $timestamp){
					$validProg = true;
					$nextTime=$timestamp;
					$TempSouhaite= jeedom::evaluateExpression($ConigSchedule["consigne"]);
					cache::set('ChauffeEau::Hysteresis::'.$this->getId(),$ConigSchedule["hysteresis"], 0);
					$DeltaTime = $nextTime - time();
					$PowerTime=$this->EvaluatePowerTime($TempSouhaite,$this->getStartTemperature($TempSouhaite,$TempActuel,$DeltaTime));
				}
			}
		}
		if(!cache::byKey('ChauffeEau::Run::'.$this->getId())->getValue(false)){
			$this->checkAndUpdateCmd('NextStop',date('d/m/Y H:i',$nextTime));
			$this->checkAndUpdateCmd('NextStart',date('d/m/Y H:i',$nextTime-$PowerTime));
			//log::add('ChauffeEau','debug',$this->getHumanName().' : Le prochain disponibilité est '. date("d/m/Y H:i", $nextTime));
		}
		if(!cache::byKey('ChauffeEau::BacteryProtect::'.$this->getId())->getValue(false))
			$this->checkAndUpdateCmd('consigne',jeedom::evaluateExpression($TempSouhaite));
		if(!$validProg)	
			return false;
		return true;
	}
	public function EvaluatePowerTime($Consigne,$StartTemp) {	
		$PowerTime = 0;
		$TempsAdditionel = $this->BacteryProtect($StartTemp);
		$DeltaTemp = $Consigne - $StartTemp;
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
	public function checkBacteryProtect($TempActuel){
		$BacteryProtectCmd=$this->getCmd(null,'BacteryProtect');
		$BacteryProtect=$BacteryProtectCmd->execCmd();
		if($TempActuel >= 60)
			cache::set('ChauffeEau::TimeBacteryProtect::'.$this->getId(), time() - DateTime::createFromFormat("Y-m-d H:i:s", $BacteryProtectCmd->getCollectDate())->getTimestamp(), 0);
		$DeltaTime = cache::byKey('ChauffeEau::TimeBacteryProtect::'.$this->getId())->getValue(0);
		if($BacteryProtect){
			if($TempActuel >= 70 && $DeltaTime >= 1*60)
				$this->checkAndUpdateCmd('BacteryProtect',false);
			elseif($TempActuel >= 65 && $DeltaTime >= 2*60)
				$this->checkAndUpdateCmd('BacteryProtect',false);
			elseif($TempActuel >= 60 && $DeltaTime >= 30*60)
				$this->checkAndUpdateCmd('BacteryProtect',false);
		}else{
			if($TempActuel > 25 && $TempActuel < 47 && $DeltaTime > self::_TempsNettoyage){
				$this->checkAndUpdateCmd('BacteryProtect',true);
				log::add('ChauffeEau','debug',$this->getHumanName().'[BacteryProtect] La température de l\'eau est comprise entre 25°C et 47°C pendant plus de '.self::_TempsNettoyage.'s, nous allons nettoyer le ballon');
			}
			if($TempActuel > 47)
				$this->checkAndUpdateCmd('BacteryProtect',false);
		}
	}
	public function BacteryProtect($StartTemp){		
		$BacteryProtectCmd=$this->getCmd(null,'BacteryProtect');
		$BacteryProtect=$BacteryProtectCmd->execCmd();
		$TempsAdditionnel=0;
		if($this->getConfiguration('BacteryProtect') && $BacteryProtect){
			if(!cache::byKey('ChauffeEau::BacteryProtect::'.$this->getId())->getValue(false))
				log::add('ChauffeEau','debug',$this->getHumanName().'[BacteryProtect] Strategie de protection active et en cours');
			$LastUpdate=$BacteryProtectCmd->getCollectDate();
			if($LastUpdate == '')
				$LastUpdate=date('Y-m-d H:i:s');
			$DeltaTime= time() - DateTime::createFromFormat("Y-m-d H:i:s", $LastUpdate)->getTimestamp();		
			if($StartTemp >= 40 && $StartTemp < 55){
				if($DeltaTime > self::_TempsNettoyageRapide){
					if(!cache::byKey('ChauffeEau::BacteryProtect::'.$this->getId())->getValue(false))
						log::add('ChauffeEau','debug',$this->getHumanName().'[BacteryProtect] Consigne a 65°C et temps additionnel de 120s');			
					$this->checkAndUpdateCmd('consigne',65);
					$TempsAdditionnel = 2 * 60;
				}else{
					if(!cache::byKey('ChauffeEau::BacteryProtect::'.$this->getId())->getValue(false))
						log::add('ChauffeEau','debug',$this->getHumanName().'[BacteryProtect] Consigne a 60°C et temps additionnel de 32min');			
					$this->checkAndUpdateCmd('consigne',60);
					$TempsAdditionnel = 32 * 60;
				}
				cache::set('ChauffeEau::BacteryProtect::'.$this->getId(), true, 0);
			}
		}
		return $TempsAdditionnel;
	}
	public function getCartoChauffeEau() {
		$CartoChauffeEau = cache::byKey('ChauffeEau::DeltaTemp::'.$this->getId());
		if(is_object($CartoChauffeEau)){
			$Caracterisations = json_decode($CartoChauffeEau->getValue('[]'), true);
			if(count($Caracterisations) > 3){
				foreach ($Caracterisations as $Temperature => $Perte){
					$Temperatures[] = $Temperature;
					$Pertes[] = $Perte;
				}
				return array($Temperatures,$Pertes);
			}
		}
		$CartoTemperatures= self::_Temperatures;
		$CartoPertes= self::_Pertes;
		return array($CartoTemperatures,$CartoPertes);
	}
	public function setDeltaTemperature($TempActuel) {
		$TemperatureEauCmd=$this->getCmd(null,'TempActuel');
		if(is_object($TemperatureEauCmd)){
			$LastTemperatureEau = $TemperatureEauCmd->execCmd();
			$LastTemperatureEauCollectDate=DateTime::createFromFormat("Y-m-d H:i:s", $TemperatureEauCmd->getCollectDate());
			if($LastTemperatureEauCollectDate !== false){
				$DeltaTime= time() - $LastTemperatureEauCollectDate->getTimestamp();
				if($DeltaTime > 0){
					$DeltaTemp = ($LastTemperatureEau - $TempActuel) / $DeltaTime;// delta de temperature par seconde
					if($DeltaTemp > 0 ){
						$cache = cache::byKey('ChauffeEau::DeltaTemp::'.$this->getId());
						$Caracterisation = json_decode($cache->getValue('[]'), true);
						if($DeltaTemp < $Caracterisation[round($TempActuel)] * 0.95 || $DeltaTemp > $Caracterisation[round($TempActuel)] * 1.05){
							$Caracterisation[round($TempActuel)] = ($DeltaTemp + $Caracterisation[round($TempActuel)]) /2;
							cache::set('ChauffeEau::DeltaTemp::'.$this->getId(), json_encode($Caracterisation), 0);
						}
					}
				}
			}
		}
	}
	public function getDeltaTemperature($TempActuel) {
		list($CartoTemperatures,$CartoPertes)= $this->getCartoChauffeEau();
		foreach($CartoTemperatures as $key => $Temperature){
			if($TempActuel >= $Temperature && $TempActuel < $CartoTemperatures[$key+1]){
				return $CartoPertes[$key];
			}
		}
		return 0;
	}
	public function getStartTemperature($Consigne,$Temperature,$DeltaTime) {
		list($CartoTemperatures,$CartoPertes)= $this->getCartoChauffeEau();
		while($DeltaTime - $this->EvaluatePowerTime($Consigne,$Temperature) > 0){
			foreach($CartoTemperatures as $key => $CartoTemp){
				if($Temperature >= $CartoTemp && $Temperature < $CartoTemperatures[$key+1]){
					$TimeToStep= ($Temperature - $CartoTemp) / $CartoPertes[$key];
					if($TimeToStep > $DeltaTime)
						$TimeToStep=$DeltaTime;
					$Temperature -= $TimeToStep * $CartoPertes[$key];
					$DeltaTime -=$TimeToStep;
					break;
				}
			}
			if($TimeToStep == 0)
				break;
		}
		return round($Temperature,1);
	}
	public function EstimateTempActuel(){
		if($this->getConfiguration('TempEauEstime')){
			$TempActuelCmd=$this->getCmd(null,'TempActuel');
			$TempActuel=$TempActuelCmd->execCmd();
			$DeltaTime= time() - DateTime::createFromFormat("Y-m-d H:i:s", $TempActuelCmd->getCollectDate())->getTimestamp();
			if($this->getCmd(null,'state')->execCmd() == 1){
				//on augmente la température
				$Capacite = $this->getConfiguration('Capacite');
				$Puissance = $this->getPuissance();
				$Energie= $DeltaTime * $Puissance;
				$DeltaTemp = $Energie / ($Capacite*4181);
				$TempActuel += $DeltaTemp;
				if($TempActuel > 95)
					$TempActuel=95;
			}else{
				//on baisse la température
				$TempLocal=jeedom::evaluateExpression($this->getConfiguration('TempLocal'));
				//$nbDouche=jeedom::evaluateExpression($this->getConfiguration('nbDouche'));
				//$nbBain=jeedom::evaluateExpression($this->getConfiguration('nbBain'));
				$DeltaTemp= $DeltaTime * $this->getDeltaTemperature($TempActuel);
				$TempActuel -= $DeltaTemp;
				if($TempActuel < $TempLocal)
					$TempActuel = $TempLocal;
			}
			$TempActuel = round($TempActuel,1);
			if($TempActuel != $TempActuelCmd->execCmd())
				$this->checkAndUpdateCmd('TempActuel',$TempActuel);
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
			$result = evaluate($expression);
			if(!$result || !cache::byKey('ChauffeEau::Run::'.$this->getId())->getValue(false))
				log::add('ChauffeEau','info',$this->getHumanName().'[Condition] : Evaluation de la condition : ['.jeedom::toHumanReadable($Condition['expression']).'][' . trim($expression) . '] = '.$this->boolToText($result));
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
		cache::set('ChauffeEau::BacteryProtect::'.$this->getId(), false, 0);
		$listener = listener::byClassAndFunction('ChauffeEau', 'pull', array('ChauffeEau_id' => $this->getId()));
		if (is_object($listener)) 	
			$listener->remove();
      		$cache = cache::byKey('ChauffeEau::Start::Temperature::'.$this->getId());
      		if(is_object($cache))
          		$cache->remove();
      		$cache = cache::byKey('ChauffeEau::Power::'.$this->getId());
      		if(is_object($cache))
          		$cache->remove();
		if ($this->getConfiguration('Etat') != '' || (!$this->getConfiguration('TempEauEstime') && $this->getConfiguration('TempActuel') != '')){
			$listener = listener::byClassAndFunction('ChauffeEau', 'pull', array('ChauffeEau_id' => $this->getId()));
			if (!is_object($listener))
			    $listener = new listener();
			$listener->setClass('ChauffeEau');
			$listener->setFunction('pull');
			$listener->setOption(array('ChauffeEau_id' => $this->getId()));
			$listener->emptyEvent();	
			if(!$this->getConfiguration('TempEauEstime') && $this->getConfiguration('TempActuel') != '')
				$listener->addEvent($this->getConfiguration('TempActuel'));
			if ($this->getConfiguration('Etat') != '')
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
