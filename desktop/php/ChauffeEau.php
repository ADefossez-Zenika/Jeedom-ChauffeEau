<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('ChauffeEau');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">    
   	<div class="col-xs-12 eqLogicThumbnailDisplay">
  		<legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction logoPrimary" data-action="add">
				<i class="fas fa-plus-circle"></i>
				<br>
				<span>{{Ajouter}}</span>
			</div>
      			<div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
      				<i class="fas fa-wrench"></i>
    				<br>
    				<span>{{Configuration}}</span>
  			</div>
      			<div class="cursor eqLogicAction logoSecondary" data-action="gotoExpressionTest">
      				<i class="fas fa-check"></i>
    				<br>
    				<span>{{Testeur d'expression}}</span>
  			</div>
      			<div class="cursor eqLogicAction logoSecondary" data-action="gotoHealth">
      				<i class="fas fa-medkit"></i>
    				<br>
    				<span>{{Santé}}</span>
  			</div>	
  		</div>
  		<legend><i class="fas fa-table"></i> {{Mes Zones}}</legend>
	   	<input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
		<div class="eqLogicThumbnailContainer">
    		<?php
			foreach ($eqLogics as $eqLogic) {
				$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
				echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
				echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
				echo '<br>';
				echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
				echo '</div>';
			}
		?>
		</div>
	</div>
	<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure">
					<i class="fa fa-cogs"></i>
					 {{Configuration avancée}}
				</a>
				<a class="btn btn-default btn-sm eqLogicAction" data-action="copy">
					<i class="fas fa-copy"></i>
					 {{Dupliquer}}
				</a>
				<a class="btn btn-sm btn-success eqLogicAction" data-action="save">
					<i class="fas fa-check-circle"></i>
					 {{Sauvegarder}}
				</a>
				<a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove">
					<i class="fas fa-minus-circle"></i>
					 {{Supprimer}}
				</a>
			</span>
		</div>
		<ul class="nav nav-tabs" role="tablist">
    			<li role="presentation">
				<a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay">
					<i class="fa fa-arrow-circle-left"></i>
				</a>
			</li>
    			<li role="presentation" class="active">
				<a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab">
				<i class="fa fa-tachometer"></i> 
					{{Equipement}}
				</a>
			</li>
    			<li role="presentation">
				<a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab">
					<i class="fa fa-list-alt"></i> 
					{{Commandes}}
				</a>
			</li>
			<li role="presentation" class="">
				<a href="#programationtab" aria-controls="profile" role="tab" data-toggle="tab" aria-expanded="false">
					<i class="fa fa-calendar"></i>
					 {{Programmation}}
				</a>
			</li>
			<li role="presentation">
				<a href="#conditiontab" aria-controls="profile" role="tab" data-toggle="tab" aria-expanded="false">
					<i class="fa fa-asterisk"></i>
					 {{Conditions}}
				</a>
			</li>
			<li role="presentation">
				<a href="#actionTab" aria-controls="profile" role="tab" data-toggle="tab" aria-expanded="false">
					<i class="fa fa-list-alt"></i>
					 {{Actions}}
				</a>
			</li>
  		</ul>
		<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<br/>
				<div class="col-sm-6">
    					<form class="form-horizontal">
						<legend>Général</legend>
						<fieldset>
							<div class="form-group ">
								<label class="col-sm-3 control-label">
									{{Nom du chauffe-eau}}
									<sup>
										<i class="fa fa-question-circle tooltips" title="{{Saisir le nom de votre equipement}}"></i>
									</sup>
								</label>
								<div class="col-sm-7">
									<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
									<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom du groupe de zones}}"/>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label" >
									{{Objet parent}}
									<sup>
										<i class="fa fa-question-circle tooltips" title="{{Choisir l'objet ou se trouve votre chauffe-eau}}"></i>
									</sup>
								</label>
								<div class="col-sm-7">
									<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
										<option value="">{{Aucun}}</option>
										<?php
											foreach (jeeObject::all() as $object) 
												echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
										?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">
									{{Catégorie}}
									<sup>
										<i class="fa fa-question-circle tooltips" title="{{Choisir une catégorie}}"></i>
									</sup>
								</label>
								<div class="col-sm-9">
									<?php
										foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
											echo '<label class="checkbox-inline">';
											echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
											echo '</label>';
										}
									?>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label" ></label>
								<div class="col-sm-7">
									<label>{{Activer}}</label>
									<input type="checkbox" class="eqLogicAttr" data-label-text="{{Activer}}" data-l1key="isEnable" checked/>
									<label>{{Visible}}</label>
									<input type="checkbox" class="eqLogicAttr" data-label-text="{{Visible}}" data-l1key="isVisible" checked/>
								</div>
							</div>
						</fieldset>
					</form>
				</div>
				<div class="col-sm-6">
					<form class="form-horizontal">
						<legend>Paramètre du chauffe-eau</legend>
						<fieldset>
							<div class="form-group">
								<label class="col-sm-4 control-label" >
									{{Capacité du chauffe-eau (Litres)}}
									<sup>
										<i class="fa fa-question-circle tooltips" title="{{Quel est la capacité de votre chauffe-eau}}"></i>
									</sup>
								</label>
								<div class="col-sm-7">
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration"  data-l2key="Capacite" placeholder="{{Capacité du chauffe-eau (Litres)}}"/>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" >
									{{Puissance du chauffe-eau (Watts)}}
									<sup>
										<i class="fa fa-question-circle tooltips" title="{{Quel est la puissance de votre chauffe-eau}}"></i>
									</sup>
								</label>
								<div class="col-sm-7">
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration"  data-l2key="Puissance" placeholder="{{Puissance du chauffe-eau (Watts)}}"/>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" >
									{{Simuler la température  du ballon}}
									<sup>
										<i class="fa fa-question-circle tooltips" title="{{Voulez vous simulé la température du chauffe-eau ou vous avez une sonde connecté}}"></i>
									</sup>
								</label>
								<div class="col-sm-7">
									<label>{{Activer}}</label>
									<input type="checkbox" class="eqLogicAttr" data-label-text="{{Activer}}" data-l1key="configuration" data-l2key="TempEauEstime"/>
								</div>
							</div>
							<div class="form-group TempEauReel">
								<label class="col-sm-4 control-label" >
									{{Température du chauffe-eau}}
									<sup>
										<i class="fa fa-question-circle tooltips" title="{{Sélectionner une commande de la température actuelle de l'eau}}"></i>
									</sup>
								</label>
								<div class="col-sm-7 input-group">
									<input class="eqLogicAttr form-control input-sm" data-l1key="configuration"  data-l2key="TempActuel" placeholder="{{Sélectionner un objet Jeedom de température, ou saisissez une valeur par defaut}}">
									<span class="input-group-btn">
										<a class="btn btn-success btn-sm bt_selectCmdExpression" >
											<i class="fa fa-list-alt"></i>
										</a>
									</span>
								</div>
							</div>  
							<div class="form-group TempEauReel">
								<label class="col-sm-4 control-label" >
									{{Fréquence de mise à jour de la sonde (min)}}
									<sup>
										<i class="fa fa-question-circle tooltips" title="{{A quel fréquence la sonde envoie une valeur (Mode de défaillance), ceci est le temps maximal de mise a jours avant le mode defaillance}}"></i>
									</sup>
								</label>
								<div class="col-sm-7 input-group">
									<input class="eqLogicAttr form-control input-sm" data-l1key="configuration"  data-l2key="FreqTempActuel" placeholder="{{Saisir la fréquence de mise à jour de la sonde (min)}}">

								</div>
							</div>  
							<div class="form-group TempEauEstime">
								<label class="col-sm-4 control-label" >
									{{Température de la piece}}
									<sup>
										<i class="fa fa-question-circle tooltips" title="{{Température de la piece}}"></i>
									</sup>
								</label>
								<div class="col-sm-7 input-group">
									<input class="eqLogicAttr form-control input-sm" data-l1key="configuration"  data-l2key="TempLocal" placeholder="{{Sélectionner un objet Jeedom de température, ou Saisisser une valeur par defaut}}">
									<span class="input-group-btn">
										<a class="btn btn-success btn-sm bt_selectCmdExpression" >
											<i class="fa fa-list-alt"></i>
										</a>
									</span>
								</div>
							</div> 
							<!--div class="form-group TempEauEstime">
								<label class="col-sm-4 control-label" >{{Nombre de douche}}</label>
								<div class="col-sm-7 input-group">
									<input class="eqLogicAttr form-control input-sm" data-l1key="configuration"  data-l2key="nbDouche" placeholder="{{Saisissez une valeur par defaut ou un varriable}}">
								</div>
							</div>
							<div class="form-group TempEauEstime">
								<label class="col-sm-4 control-label" >{{Nombre de bain}}</label>
								<div class="col-sm-7 input-group">
									<input class="eqLogicAttr form-control input-sm" data-l1key="configuration"  data-l2key="nbBain" placeholder="{{Saisissez une valeur par defaut ou un varriable}}">
								</div>
							</div-->  
						</fieldset>
					</form>
				</div>
				<div class="col-sm-6">
					<form class="form-horizontal">
						<legend>{{Controle du chauffe-eau}}						
							<a class="btn btn-danger btn-sm Caracterisique roundedRight">
								<i class="icon techno-courbes"></i>
								 {{Caractéristique}}
							</a>
						</legend>
						<fieldset>	
							<div class="form-group">
								<label class="col-sm-4 control-label" >
									{{Protection Bacteriologique}}
									<sup>
										<i class="fa fa-question-circle tooltips" title="{{Cette option vas automatisé le nettoyage de votre ballon}}"></i>
									</sup>
								</label>
								<div class="col-sm-7">
									<label>{{Activer}}</label>
									<input type="checkbox" class="eqLogicAttr" data-label-text="{{Activer}}" data-l1key="configuration" data-l2key="BacteryProtect" checked/>
								</div>
							</div>
							<div class="form-group BacteryProtect">
								<label class="col-sm-4 control-label" >
									{{Temps de protection}}
									<sup>
										<i class="fa fa-question-circle tooltips" title="{{Temps de déclanchement de l'alert ou la température est en zone critique 25°C et 47°C}}"></i>
									</sup>
								</label>
								<div class="col-sm-7 input-group">
									<input class="eqLogicAttr form-control input-sm" data-l1key="configuration"  data-l2key="TempsBacteryProtect" placeholder="{{Saisir un temps, en minute, qui déterminera l'activation de l'alert. Si la température est dans la plage critique de 25°C > Temp > 47°C alors on incremente le temps}}">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" >
									{{Répéter les commandes d'allumage et d'extinction}}
									<sup>
										<i class="fa fa-question-circle tooltips" title="{{Frequence de répétition des action on et off}}"></i>
									</sup>
								</label>
								<div class="col-sm-7 input-group">
									<select class="eqLogicAttr form-control input-sm" data-l1key="configuration" data-l2key="RepeatCmd">
										<option value="">{{Non}}</option>
										<option value="cron">{{Toutes les minutes}}</option>
										<option value="cron5">{{Toutes les 5 minutes}}</option>
										<option value="cron15">{{Toutes les 15 minutes}}</option>
										<option value="cron30">{{Toutes les 30 minutes}}</option>
										<option value="cronHourly">{{Toutes les heures}}</option>
										<option value="cronDaily">{{Tous les jours}}</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" >
									{{Commande d’état du chauffe-eau}}
									<sup>
										<i class="fa fa-question-circle tooltips" title="{{Selectionner une commande de retour d'etat du chauff-eau}}"></i>
									</sup>
								</label>
								<div class="col-sm-7 input-group">
									<input class="eqLogicAttr form-control input-sm" data-l1key="configuration"  data-l2key="Etat" placeholder="{{Séléctioner l'objet de commande d'etat du chauffe eau}}">
									<span class="input-group-btn">
										<a class="btn btn-success btn-sm bt_selectCmdExpression" >
											<i class="fa fa-list-alt"></i>
										</a>
									</span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" >
									{{Temps additionnel (min)}}
									<sup>
										<i class="fa fa-question-circle tooltips" title="{{Quel temps de sécurité voulez vous ajouter au temps de chauffe calculé}}"></i>
									</sup>
								</label>
								<div class="col-sm-7 input-group">
									<input class="eqLogicAttr form-control input-sm" data-l1key="configuration"  data-l2key="TempsAdditionel" placeholder="{{Saisir un temps en minute qui sera ajouté au temps de chauffage necessaire. Ceci pour garantir une chauffe complette}}">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" >
									{{Si délestage, le chauffe-eau doit :}}
									<sup>
										<i class="fa fa-question-circle tooltips" title="{{Que faire en cas de delestage en cours de cycle}}"></i>
									</sup>
								</label>
								<div class="col-sm-7">
									<select class="eqLogicAttr form-control input-sm" data-l1key="configuration" data-l2key="delestage">
										<option value="Heure">{{S'arreter a l'heure}}</option>
										<option value="Temp">{{S'arreter a la consigne}}</option>
										<option value="30">{{30 minute de chauffe}}</option>
									</select>
								</div>
							</div>	
						</fieldset>
					</form>
				</div>	
			</div>	
			<div role="tabpanel" class="tab-pane" id="commandtab">	
				<table id="table_cmd" class="table table-bordered table-condensed">
				    <thead>
					<tr>
					    <th>Nom</th>
					    <th>Paramètre</th>
					</tr>
				    </thead>
				    <tbody></tbody>
				</table>
			</div>	
			<div role="tabpanel" class="tab-pane" id="programationtab">
				<form class="form-horizontal">
					<fieldset>
						<legend>{{Les programmations de la zone :}}
							<sup>
								<i class="fa fa-question-circle tooltips" title="{{Saisir toutes les programmations pour la zone}}"></i>
							</sup>
							<a class="btn btn-success btn-sm ProgramationAttr pull-right" data-action="add" style="margin-top: 5px;">
								<i class="fa fa-plus-circle"></i>
								{{Ajouter une programmation}}
							</a>
						</legend>
					</fieldset>
				</form>
				<table id="table_programation" class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th style="width:30px;"></th>
							<th style="width:100px;">{{Jour actif}}</th>
							<th>{{Température}}</th>
							<th>{{Programmation}}</th>
							<th>{{URL}}</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
			<div role="tabpanel" class="tab-pane" id="conditiontab">
				<form class="form-horizontal">
					<fieldset>
						<legend>{{Les conditions d'exécution :}}
							<sup>
								<i class="fa fa-question-circle tooltips" title="{{Saisir toutes les conditions d'exécution de la gestion}}"></i>
							</sup>
							<a class="btn btn-success btn-xs conditionAttr" data-action="add" style="margin-left: 5px;">
								<i class="fa fa-plus-circle"></i>
								{{Ajouter une Condition}}
							</a>
						</legend>
					</fieldset>
				</form>			
				<table id="table_condition" class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th></th>
							<th>Condition</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
			<div role="tabpanel" class="tab-pane" id="actionTab">
				<form class="form-horizontal">
					<fieldset>
						<legend>{{Les actions:}}
							<sup>
								<i class="fa fa-question-circle tooltips" title="{{Saisir toutes les actions à mener à l'ouverture}}"></i>
							</sup>
							<a class="btn btn-success btn-xs ActionAttr" data-action="add" style="margin-left: 5px;">
								<i class="fa fa-plus-circle"></i>
								{{Ajouter une Action}}
							</a>
						</legend>
					</fieldset>
				</form>					
				<table id="table_action" class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th></th>
							<th>{{Action}}</th>
							<th>{{Déclencheur}}</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>		
		</div>
	</div>
</div>

<?php include_file('desktop', 'ChauffeEau', 'js', 'ChauffeEau'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>
