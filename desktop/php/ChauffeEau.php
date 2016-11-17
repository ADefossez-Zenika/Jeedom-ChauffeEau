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
		<legend>{{Mes Zones}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction" data-action="add" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
				<center>
					<i class="fa fa-plus-circle" style="font-size : 7em;color:#94ca02;"></i>
				</center>
				<span style="font-size : 1.1em;position:relative; top : 23px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;;color:#94ca02"><center>Ajouter</center></span>
			</div>
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
					echo '<img src="plugins/ChauffeEau/doc/images/ChauffeEau_icon.png" height="105" width="95" />';
					echo "</center>";
					echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
					echo '</div>';
				}
			?>
		</div>
	</div>  
	<div class="col-lg-10 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
		<form class="form-horizontal">
			<fieldset>		
				<legend>
					<i class="fa fa-arrow-circle-left eqLogicAction cursor" data-action="returnToThumbnailDisplay"></i> {{Général}}  
					<i class="fa fa-cogs eqLogicAction pull-right cursor expertModeVisible" data-action="configure"></i>
					<a class="btn btn-default btn-xs pull-right expertModeVisible eqLogicAction" data-action="copy"><i class="fa fa-copy"></i>{{Dupliquer}}</a>
					<a class="btn btn-success btn-xs eqLogicAction pull-right" data-action="save"><i class="fa fa-check-circle"></i> Sauvegarder</a>
					<a class="btn btn-danger btn-xs eqLogicAction pull-right" data-action="remove"><i class="fa fa-minus-circle"></i> Supprimer</a>
				</legend> 
			</fieldset> 
		</form>	
		<div class="row" style="padding-left:25px;">
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
						<label class="col-sm-2 control-label" >{{Capacité du chauffe eau}}</label>
						<div class="col-sm-5">
							<input type="text" class="eqLogicAttr form-control" data-l1key="configuration"  data-l2key="Capacite" placeholder="{{Nom du groupe de zones}}"/>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" >{{Puissance du chauffe eau}}</label>
						<div class="col-sm-5">
							<input type="text" class="eqLogicAttr form-control" data-l1key="configuration"  data-l2key="Puissance" placeholder="{{Nom du groupe de zones}}"/>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" >{{Température Souhaité}}</label>
						<div class="col-sm-5">
							<input type="text" class="eqLogicAttr form-control" data-l1key="configuration"  data-l2key="TempSouhaite" placeholder="{{Nom du groupe de zones}}"/>
						</div>
					</div>
					<div class="form-group">
					<div class="input-group">
						<label class="col-sm-2 control-label" >{{Selectioner une commande ou estimer la temperature actuel de l'eau}}</label>
						<div class="col-sm-5">
							<a class="btn btn-warning btn-sm bt_selectCmdExpression">
								<i class="fa fa-list-alt"></i>
							</a>
						</div>
						<div class="col-lg-3">
							<input class="eqLogicAttr form-control input-sm" data-l1key="configuration"  data-l2key="TempActuel" />
						</div>
					</div>  
					</div>  
					<div class="form-group input-group">
						<label class="col-sm-2 control-label" >{{Configurer le lancement de votre chauffage}}</label>
						<div class="col-sm-5">
							<a class="btn btn-warning btn-sm ScheduleCron">
								<i class="fa fa-list-alt"></i>
							</a>
						</div>
						<div class="col-lg-3">
							<input class="eqLogicAttr form-control input-sm" data-l1key="configuration"  data-l2key="ScheduleCron" />
						</div>
					</div>
					<div class="form-group input-group">
						<label class="col-sm-2 control-label" >{{Commande d'activation du chauffe eau}}</label>
						<div class="col-sm-5">
							<a class="btn btn-warning btn-sm bt_selectCmdExpression">
								<i class="fa fa-list-alt"></i>
							</a>
						</div>
						<div class="col-lg-3">
							<input class="eqLogicAttr form-control input-sm" data-l1key="configuration"  data-l2key="Activation" />
						</div>
					</div>
					<div class="form-group input-group">
						<label class="col-sm-2 control-label" >{{Commande de desactivation du chauffe eau}}</label>
						<div class="col-sm-5">
							<a class="btn btn-warning btn-sm bt_selectCmdExpression">
								<i class="fa fa-list-alt"></i>
							</a>
						</div>
						<div class="col-lg-3">
							<input class="eqLogicAttr form-control input-sm" data-l1key="configuration"  data-l2key="Desactivation" />
						</div>
					</div>	
				</fieldset>
			</form>
		</div>
		<form class="form-horizontal">
			<fieldset>
				<div class="form-actions">
					<a class="btn btn-danger eqLogicAction" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
					<a class="btn btn-success eqLogicAction" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
				</div>
			</fieldset>
		</form>
	</div>
</div>

<?php include_file('desktop', 'ChauffeEau', 'js', 'ChauffeEau'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>
