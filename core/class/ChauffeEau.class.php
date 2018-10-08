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
		foreach(eqLogic::byType('ChauffeEau') as $ChauffeEau)
			$ChauffeEau->CheckChauffeEau();
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
	public function CheckChauffeEau(){
		if (!$this->getIsEnable()) 
			return;
		switch($this->getCmd(null,'etatCommut')->execCmd()){
			case 'armed':
				// Mode Forcée
				$this->PowerStart();
			break;
			case 'auto':
				//Mode automatique
				$TempSouhaite = $this->getCmd(null,'consigne')->execCmd();
				$TempActuel= jeedom::evaluateExpression($this->getConfiguration('TempActuel'));
				$this->CheckDeltaTemp($TempActuel);
				$NextProg = cache::byKey('ChauffeEau::Stop::Time::'.$this->getId())->getValue(0);
				$Delestage = cache::byKey('ChauffeEau::Delestage::'.$this->getId())->getValue(false);
				if($NextProg == 0){
					$NextProg=$this->NextProg();
					if($NextProg != null){
						$this->checkAndUpdateCmd('NextStop',date('d/m/Y H:i',$NextProg));
						cache::set('ChauffeEau::Stop::Time::'.$this->getId(),$NextProg, 0);
					}else 
						continue;							
				}
				$PowerTime=$this->EvaluatePowerTime();
				$NextStart=$NextProg-$PowerTime;
				$this->checkAndUpdateCmd('NextStart',date('d/m/Y H:i',$NextStart));
				if(mktime() > $NextProg){
					if($Delestage && $this->getConfiguration('delestage') == 'Heure')
						cache::set('ChauffeEau::Delestage::'.$this->getId(),false, 0);
					if($Delestage && $this->getConfiguration('delestage') == '30'){
						$this->checkAndUpdateCmd('NextStop',date('d/m/Y H:i',$NextProg+(30*60)));
						cache::set('ChauffeEau::Stop::Time::'.$this->getId(),$NextProg+(30*60), 0);
						continue;
					}
					if(!cache::byKey('ChauffeEau::Delestage::'.$this->getId())->getValue(false)){
						cache::set('ChauffeEau::Stop::Time::'.$this->getId(),0, 0);
						$this->checkAndUpdateCmd('NextStop',date('d/m/Y H:i'));
						log::add('ChauffeEau','debug',$this->getHumanName().' : Temps supperieur a l\'heure programmée');
						$this->EvaluatePowerStop();
						foreach($this->getConfiguration('Action') as $cmd){
							foreach($cmd['declencheur'] as $declencheur){
								if($declencheur == 'dispo')
									$this->ExecuteAction($cmd);
							}
						}
						continue;
					}
				}
				if(mktime() > $NextProg-$PowerTime+60){	//Heure actuel > Heure de dispo - Temps de chauffe + Pas d'integration
					if($this->EvaluateCondition()){
						if($TempActuel <=  $TempSouhaite){
							log::add('ChauffeEau','info',$this->getHumanName().' : La température actuel est de '.$TempActuel.'°C et nous desirons atteindre '.  $TempSouhaite.'°C');		
							log::add('ChauffeEau','info',$this->getHumanName().' : Temps de chauffage estimé est de '.$PowerTime.' s');
							$this->PowerStart();
						}
					}	
				}else{
					$StartTemps = cache::byKey('ChauffeEau::Start::Temps::'.$this->getId());
					$DeltaTemp=$TempActuel-$StartTemps->getValue(0);
					if($DeltaTemp > 1 && cache::byKey('ChauffeEau::Hysteresis::'.$this->getId())->getValue(false)){
						if($this->EvaluateCondition()){
							if($TempActuel >=  $TempSouhaite){									
								if($Delestage && $this->getConfiguration('delestage') == 'Temp')
									cache::set('ChauffeEau::Delestage::'.$this->getId(),false, 0);
								$this->EvaluatePowerStop();
							}
							continue;
						}	
					}
					$this->EvaluatePowerStop();
				}

			break;
			case 'released':
				// Mode Stop
				$this->PowerStop();
			break;
			case 'delestage':
				// Mode Délestage
				$this->PowerStop();
				cache::set('ChauffeEau::Delestage::'.$this->getId(),true, 0);
			break;
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
	public function toHtml($_version = 'dashboard') {
		$replace = $this->preToHtml($_version);
		if (!is_array($replace)) 
			return $replace;
		$version = jeedom::versionAlias($_version);
		$cmdColor = ($this->getPrimaryCategory() == '') ? '' : jeedom::getConfiguration('eqLogic:category:' . $this->getPrimaryCategory() . ':' . $vcolor);
		$replace['#cmdColor#'] = $cmdColor;
		if ($this->getDisplay('hideOn' . $version) == 1)
			return '';
		foreach ($this->getCmd() as $cmd) {
			if ($cmd->getDisplay('hideOn' . $version) == 1)
				continue;
			$replace['#'.$cmd->getLogicalId().'#']= $cmd->toHtml($_version, $cmdColor);
		}
		$replace['#tempBallon#'] = jeedom::evaluateExpression($this->getConfiguration('TempActuel'));
		/*$PowerTime=$this->EvaluatePowerTime();		
		$NextProg = cache::byKey('ChauffeEau::Stop::Time::'.$this->getId())->getValue(0);
		if($NextProg==0)
			$NextProg=$this->NextProg();
		if($PowerTime<0)
			$replace['#NextStart#'] = "L'eau n'a pas besoin d'etre chauffé";
		else
			$replace['#NextStart#'] = date('d/m/Y H:i',$NextProg-$PowerTime);
		$replace['#NextStop#'] = date('d/m/Y H:i',$NextProg);*/
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
			foreach($this->getConfiguration('Action') as $cmd){
				foreach($cmd['declencheur'] as $declencheur){
					if($declencheur == 'on')
						$this->ExecuteAction($cmd);
				}
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
			foreach($this->getConfiguration('Action') as $cmd){
				foreach($cmd['declencheur'] as $declencheur){
					if($declencheur == 'off')
						$this->ExecuteAction($cmd);
				}
			}
		}
	}
	public function EvaluatePowerStop(){
		cache::set('ChauffeEau::Power::'.$this->getId(),false, 0);
		if($this->getCmd(null,'state')->execCmd()){
			$this->PowerStop();
			$TempActuel= jeedom::evaluateExpression($this->getConfiguration('TempActuel'));
			$StartTime = cache::byKey('ChauffeEau::Start::Time::'.$this->getId());	
			$StartTemps = cache::byKey('ChauffeEau::Start::Temps::'.$this->getId());
			$DeltaTemp=$TempActuel-$StartTemps->getValue($TempActuel);
			if($DeltaTemp > 1){
				$DeltaTime=time()-$StartTime->getValue(time());
				if($DeltaTime > 1){
					log::add('ChauffeEau','info',$this->getHumanName().' : Le chauffe eau a montée de '.$DeltaTemp.'°C sur une periode de '.$DeltaTime.'s');
					$Ratio = cache::byKey('ChauffeEau::Ratio::'.$this->getId());
					$value = json_decode($Ratio->getValue('[]'), true);
					$value[] =intval(round($DeltaTime/$DeltaTemp));
					cache::set('ChauffeEau::Ratio::'.$this->getId(), json_encode(array_slice($value, -10, 10)), 0);
					$this->Puissance($DeltaTemp,$DeltaTime);
				}
			}	
		}
	}
	public function CheckDeltaTemp($TempActuel){
		if(!$this->getCmd(null,'state')->execCmd()){
			$LastTemp = cache::byKey('ChauffeEau::LastTemp::'.$this->getId());	
			$DeltaTemp=$TempActuel-$LastTemp->getValue($TempActuel);
			$this->setDeltaTemp($DeltaTemp);
			if($DeltaTemp > $this->getDeltaTemp()){
				//log::add('ChauffeEau','info',$this->getHumanName().' : Il y a un chutte de température de '.$DeltaTemp.' => Vous prenez une douche');
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
					$jour=date('w')+$day+$offset;
					if($jour > 6)
						$jour= $jour-7;
					if($ConigSchedule[$jour]){
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
					$this->checkAndUpdateCmd('consigne',jeedom::evaluateExpression($TempSouhaite));
				}
			}elseif($ConigSchedule["isSeuil"] && $ConigSchedule[date('w')]){
				if(jeedom::evaluateExpression($this->getConfiguration('TempActuel')) <= $ConigSchedule["seuil"]){
					log::add('ChauffeEau','info',$this->getHumanName().' : Lancement du cycle d\'Hysteresis');
					cache::set('ChauffeEau::Hysteresis::'.$this->getId(),true, 0);
					$nextTime = mktime()+(60*60*24);					
					$this->checkAndUpdateCmd('consigne',jeedom::evaluateExpression($ConigSchedule["consigne"]));
				}
			}
		}
		//log::add('ChauffeEau','debug',$this->getHumanName().' : Le prochain disponibilité est '. date("d/m/Y H:i", $nextTime));
		return $nextTime;
	}
	public function EvaluatePowerTime() {		
		$DeltaTemp = $this->getCmd(null,'consigne')->execCmd();
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
		}
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
		$this->AddCommande("Date début","NextStart","info", 'string',true);
		$this->AddCommande("Date de fin","NextStop","info", 'string',true);
		$this->AddCommande("Consigne appliquée","consigne","info", 'numeric',true,'Consigne');
		$state=$this->AddCommande("Etat du chauffe-eau","state","info", 'binary',true,'State');
		$state->event(false);
		$state->setCollectDate(date('Y-m-d H:i:s'));
		$state->save();
		$isArmed=$this->AddCommande("Etat fonctionnement","etatCommut","info","string",true);
		$isArmed->event('auto');
		$isArmed->setCollectDate(date('Y-m-d H:i:s'));
		$isArmed->save();
		$Armed=$this->AddCommande("Marche forcée","armed","action","other",true,'');
		$Armed->setValue($isArmed->getId());
		$Armed->save();
		$Released=$this->AddCommande("Désactiver","released","action","other",true,'');
		$Released->setValue($isArmed->getId());
		$Released->save();
		$Auto=$this->AddCommande("Automatique","auto","action","other",true,'');
		$Auto->setValue($isArmed->getId());
		$Auto->save();
		$Auto=$this->AddCommande("Délestage","delestage","action","other",true,'');
		$Auto->setValue($isArmed->getId());
		$Auto->save();
		$this->createDeamon();
		cache::set('ChauffeEau::Hysteresis::'.$this->getId(),false, 0);
		$Puissance = cache::byKey('ChauffeEau::Puissance::'.$this->getId());
		if(count(json_decode($Puissance->getValue('[]'), true)) == 0)
			cache::set('ChauffeEau::Puissance::'.$this->getId(), json_encode(array_slice(array(intval(trim($this->getConfiguration('Puissance')))), -10, 10)), 0);
		$this->CheckChauffeEau();
	}
	public function createDeamon() {
		$listener = listener::byClassAndFunction('ChauffeEau', 'pull', array('ChauffeEau_id' => $this->getId()));
		if (is_object($listener)) 	
			$listener->remove();
      		$cache = cache::byKey('ChauffeEau::Start::Temps::'.$this->getId());
      		if(is_object($cache))
          		$cache->remove();
      		$cache = cache::byKey('ChauffeEau::Stop::Time::'.$this->getId());
      		if(is_object($cache))
          		$cache->remove();
      		$cache = cache::byKey('ChauffeEau::Power::'.$this->getId());
      		if(is_object($cache))
          		$cache->remove();
      		$cache = cache::byKey('ChauffeEau::Hysteresis::'.$this->getId());
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
				$this->getEqLogic()->checkAndUpdateCmd('etatCommut','armed');
			break;
			case 'released':
				$this->getEqLogic()->checkAndUpdateCmd('etatCommut','released');
			break;
			case 'auto':
				$this->getEqLogic()->checkAndUpdateCmd('etatCommut','auto');
			break;
			case 'delestage':
				$this->getEqLogic()->checkAndUpdateCmd('etatCommut','delestage');
			break;
		}
	}
}
?>
