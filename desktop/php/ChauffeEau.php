<?php
if (!isConnect('admin')) {
throw new Exception('{{401 - Accès non autorisé}}');
}
sendVarToJS('eqType', 'ChauffeEau');
$eqLogics = eqLogic::byType('ChauffeEau');
?>
<div class="row row-overflow">
	<div class="col-lg-2">
		<div class="bs-sidebar">
			<ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
				<a class="btn btn-default eqLogicAction" style="width : 50%;margin-top : 5px;margin-bottom: 5px;" data-action="add"><i class="fa fa-plus-circle"></i> {{Ajouter}}</a>
				<li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
				<?php
					foreach ($eqLogics as $eqLogic) 
						echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
				?>
			</ul>
		</div>
	</div>
	<div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
		<legend>{{Gestion}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction" data-action="add" style="background-color : #ffffff; height : 140px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
				<center>
					<i class="fa fa-plus-circle" style="font-size : 5em;color:#406E88;"></i>
				</center>
				<span style="font-size : 1.1em;position:relative; top : 23px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#406E88"><center>{{Ajouter}}</center></span>
			</div>
			<div class="cursor eqLogicAction" data-action="gotoPluginConf" style="height: 120px; margin-bottom: 10px; padding: 5px; border-radius: 2px; width: 160px; margin-left: 10px; position: absolute; left: 170px; top: 0px; background-color: rgb(255, 255, 255);">
				<center>
			      		<i class="fa fa-wrench" style="font-size : 5em;color:#767676;"></i>
			    	</center>
			    	<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#767676"><center>Configuration</center></span>
			</div>
		</div>
		<legend>{{Mes Zones}}</legend>
		<div class="eqLogicThumbnailContainer">
			<?php
				foreach ($eqLogics as $eqLogic) {
					$opacity = '';
					if ($eqLogic->getIsEnable() != 1) {
						$opacity = '
						-webkit-filter: grayscale(100%);
						-moz-filter: grayscale(100);
						-o-filter: grayscale(100%);
						-ms-filter: grayscale(100%);
						filter: grayscale(100%); opacity: 0.35;';
					}
					echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
					echo "<center>";
					echo '<img src="plugins/ChauffeEau/plugin_info/ChauffeEau_icon.png" height="105" width="95" />';
					echo "</center>";
					echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
					echo '</div>';
				}
			?>
		</div>
	</div>  
	<div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
		<a class="btn btn-success btn-sm eqLogicAction pull-right" data-action="save"><i class="fa fa-check-circle"></i> Sauvegarder</a>
		<a class="btn btn-danger btn-sm eqLogicAction pull-right" data-action="remove"><i class="fa fa-minus-circle"></i> Supprimer</a>
		<a class="btn btn-default btn-sm eqLogicAction pull-right" data-action="configure"><i class="fa fa-cogs"></i></a>
		<a class="btn btn-default btn-sm eqLogicAction pull-right expertModeVisible " data-action="copy"><i class="fa fa-copy"></i></a>
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation">
				<a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay">
					<i class="fa fa-arrow-circle-left"></i>
				</a>
			</li>
			<li role="presentation" class="active">
				<a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab" aria-expanded="true">
					<i class="fa fa-tachometer"></i> Equipement</a>
			</li>
			<li role="presentation">
				<a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab" aria-expanded="false">
					<i class="fa fa-list-alt"></i> Commandes</a>
			</li>
			<li role="presentation" class="">
				<a href="#programationtab" aria-controls="profile" role="tab" data-toggle="tab" aria-expanded="false">
					<i class="fa fa-map"></i> {{Programation}}</a>
			</li>
			<li role="presentation">
				<a href="#conditiontab" aria-controls="profile" role="tab" data-toggle="tab" aria-expanded="false">
					<i class="fa fa-list-alt"></i> {{Conditions d'exécution}}</a>
			</li>

		</ul>
		<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<form class="form-horizontal">
					<fieldset>
						<div class="form-group ">
							<label class="col-sm-2 control-label">{{Nom de la Zone}}</label>
							<div class="col-sm-5">
								<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
								<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom du groupe de zones}}"/>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label" >{{Objet parent}}</label>
							<div class="col-sm-5">
								<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
									<option value="">{{Aucun}}</option>
									<?php
										foreach (object::all() as $object) 
											echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
									?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label" ></label>
							<div class="col-sm-5">
								<label>{{Activer}}</label>
								<input type="checkbox" class="eqLogicAttr" data-label-text="{{Activer}}" data-l1key="isEnable" checked/>
								<label>{{Visible}}</label>
								<input type="checkbox" class="eqLogicAttr" data-label-text="{{Visible}}" data-l1key="isVisible" checked/>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label" >{{Capacité du chauffe eau (Litre)}}</label>
							<div class="col-sm-5">
								<input type="text" class="eqLogicAttr form-control" data-l1key="configuration"  data-l2key="Capacite" placeholder="{{Capacité du chauffe eau (Litre)}}"/>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label" >{{Puissance du chauffe eau (Watt)}}</label>
							<div class="col-sm-5">
								<input type="text" class="eqLogicAttr form-control" data-l1key="configuration"  data-l2key="Puissance" placeholder="{{Puissance du chauffe eau (Watt)}}"/>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label" >{{Température Souhaité (°C)}}</label>
							<div class="col-sm-5">
								<input type="text" class="eqLogicAttr form-control" data-l1key="configuration"  data-l2key="TempSouhaite" placeholder="{{Température Souhaité (°C) - Valeur conseillé 60°C}}"/>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label" >{{Selectioner une commande ou estimer la temperature actuel de l'eau}}</label>
							<div class="col-sm-5 input-group">
								<input class="eqLogicAttr form-control input-sm" data-l1key="configuration"  data-l2key="TempActuel" placeholder="{{Selectionner un objet Jeedom de température, ou Saisissez une valeur par defaut}}">
								<span class="input-group-btn">
									<a class="btn btn-success btn-sm bt_selectCmdExpression" >
										<i class="fa fa-list-alt"></i>
									</a>
								</span>
							</div>
						</div>  
						<div class="form-group">
							<label class="col-sm-2 control-label" >{{Commande d'activation du chauffe eau}}</label>
							<div class="col-sm-5 input-group">
								<input class="eqLogicAttr form-control input-sm" data-l1key="configuration"  data-l2key="Activation" placeholder="{{Séléctioner l'objet de commande d'activation du chauffe eau}}">
								<span class="input-group-btn">
									<a class="btn btn-success btn-sm bt_selectCmdExpression" >
										<i class="fa fa-list-alt"></i>
									</a>
								</span>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label" >{{Commande de desactivation du chauffe eau}}</label>
							<div class="col-sm-5 input-group">
								<input class="eqLogicAttr form-control input-sm" data-l1key="configuration"  data-l2key="Desactivation"placeholder="{{Séléctioner l'objet de commande de desactivation du chauffe eau}}">
								<span class="input-group-btn">
									<a class="btn btn-success btn-sm bt_selectCmdExpression" >
										<i class="fa fa-list-alt"></i>
									</a>
								</span>
							</div>
						</div>	
					</fieldset>
				</form>
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
								<i class="fa fa-question-circle tooltips" title="Saisir toutes les programmations pour la zone"></i>
							</sup>
							<a class="btn btn-success btn-xs ProgramationAttr" data-action="add" style="margin-left: 5px;">
								<i class="fa fa-plus-circle"></i>
								{{Ajouter une programmation}}
							</a>
						</legend>
					</fieldset>
				</form>
				<table id="table_programation" class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th></th>
							<th>Jour actif</th>
							<th>Heure</th>
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
								<i class="fa fa-question-circle tooltips" title="Saisir toutes les conditions d'exécution de la gestion"></i>
							</sup>
							<a class="btn btn-success btn-xs conditionAttr" data-action="add" style="margin-left: 5px;">
								<i class="fa fa-plus-circle"></i>
								{{Ajouter Condition}}
							</a>
						</legend>
						<div class="div_Condition"></div>
					</fieldset>
				</form>
			</div>
		</div>
	</div>
</div>

<?php include_file('desktop', 'ChauffeEau', 'js', 'ChauffeEau'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>
