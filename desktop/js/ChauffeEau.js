$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#table_programation").sortable({axis: "y", cursor: "move", items: ".ProgramationGroup", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#table_condition").sortable({axis: "y", cursor: "move", items: ".ConditionGroup", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#table_action").sortable({axis: "y", cursor: "move", items: ".ActionGroup", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$('.eqLogicAction[data-action=gotoExpressionTest]').off().on('click', function () {
	$('#md_modal').dialog({title: "{{Testeur d'expression}}"});
	$("#md_modal").load('index.php?v=d&modal=expression.test').dialog('open');
});
$('.eqLogicAction[data-action=gotoHealth]').off().on('click', function () {
	$('#md_modal').dialog({title: "{{Santé des Chauffe-Eau}}"});
	$('#md_modal').load('index.php?v=d&plugin=ChauffeEau&modal=health').dialog('open');
});
$('body').on('click','.Caracterisique',function(){
	var eqId = $('.eqLogicAttr[data-l1key=id]').val();
	$('#md_modal').dialog({title: "{{Caractéristique du Chauffe-Eau}}"});
	$('#md_modal').load('index.php?v=d&plugin=ChauffeEau&modal=Caracterisique&id='+eqId).dialog('open');
});
function addCmdToTable(_cmd) {
	var tr =$('<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">');
	tr.append($('<td>')
		.append($('<input type="hidden" class="cmdAttr form-control input-sm" data-l1key="id">'))
		.append($('<input class="cmdAttr form-control input-sm" data-l1key="name" value="' + init(_cmd.name) + '" placeholder="{{Name}}" title="Name">')));
	var parmetre=$('<td>');	
	parmetre.append($('<span class="type" type="' + init(_cmd.type) + '">')
			.append(jeedom.cmd.availableType()));
	parmetre.append($('<span class="subType" subType="'+init(_cmd.subType)+'">'));
	if (is_numeric(_cmd.id)) {
		parmetre.append($('<a class="btn btn-default btn-xs cmdAction" data-action="test">')
			.append($('<i class="fa fa-rss">')
				.text('{{Tester}}')));
	}
	parmetre.append($('<a class="btn btn-default btn-xs cmdAction tooltips" data-action="configure">')
		.append($('<i class="fa fa-cogs">')));
	parmetre.append($('<div>')
		.append($('<span>')
			.append($('<label class="checkbox-inline">')
				.append($('<input type="checkbox" class="cmdAttr checkbox-inline" data-size="mini" data-label-text="{{Historiser}}" data-l1key="isHistorized" checked/>'))
				.append('{{Historiser}}')
				.append($('<sup>')
					.append($('<i class="fa fa-question-circle tooltips" style="font-size : 1em;color:grey;">')
					.attr('title','Souhaitez vous Historiser les changements de valeur'))))));
	parmetre.append($('<div>')
		.append($('<span>')
			.append($('<label class="checkbox-inline">')
				.append($('<input type="checkbox" class="cmdAttr checkbox-inline" data-size="mini" data-label-text="{{Afficher}}" data-l1key="isVisible" checked/>'))
				.append('{{Afficher}}')
				.append($('<sup>')
					.append($('<i class="fa fa-question-circle tooltips" style="font-size : 1em;color:grey;">')
					.attr('title','Souhaitez vous afficher cette commande sur le dashboard'))))));
	tr.append(parmetre);
	$('#table_cmd tbody').append(tr);
	$('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
	jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
}
$('body').on( 'click','.bt_selectCmdExpression', function() {
	var el = $(this).closest('.input-group').find('.eqLogicAttr');
	jeedom.cmd.getSelectModal({cmd: {type: ''},eqLogic: {eqType_name : ''}}, function (result) {
		 el.value(result.human);
	});
});  
function saveEqLogic(_eqLogic) {
	_eqLogic.configuration.programation=new Object();
	_eqLogic.configuration.condition=new Object();
	_eqLogic.configuration.Action=new Object();
	var ProgramationArray= new Array();
	var ConditionArray= new Array();
	var ActionArray= new Array();
	$('#programationtab .ProgramationGroup').each(function( index ) {
		ProgramationArray.push($(this).getValues('.expressionAttr')[0])
	});
	$('#conditiontab .ConditionGroup').each(function( index ) {
		ConditionArray.push($(this).getValues('.expressionAttr')[0])
	});
	$('#actionTab .ActionGroup').each(function( index ) {
		ActionArray.push($(this).getValues('.expressionAttr')[0])
	});
	_eqLogic.configuration.programation=ProgramationArray;
	_eqLogic.configuration.condition=ConditionArray;
	_eqLogic.configuration.Action=ActionArray;
   	return _eqLogic;
}
function printEqLogic(_eqLogic) {	
	$('.ProgramationGroup').remove();
	$('.ConditionGroup').remove();
	$('.ActionGroup').remove();
	if (typeof(_eqLogic.configuration.programation) !== 'undefined') {
		for(var index in _eqLogic.configuration.programation) {
			if( (typeof _eqLogic.configuration.programation[index] === "object") && (_eqLogic.configuration.programation[index] !== null) )
				addProgramation(_eqLogic.configuration.programation[index],$('#programationtab').find('table tbody'));
		}
	}
	if (typeof(_eqLogic.configuration.condition) !== 'undefined') {
		for(var index in _eqLogic.configuration.condition) { 
			if( (typeof _eqLogic.configuration.condition[index] === "object") && (_eqLogic.configuration.condition[index] !== null) )
				addCondition(_eqLogic.configuration.condition[index],$('#conditiontab').find('table tbody'));
		}
	}
	if (typeof(_eqLogic.configuration.Action) !== 'undefined') {
		for(var index in _eqLogic.configuration.Action) { 
			if( (typeof _eqLogic.configuration.Action[index] === "object") && (_eqLogic.configuration.Action[index] !== null) )
				addAction(_eqLogic.configuration.Action[index],$('#actionTab').find('table tbody'));
		}
	}
}
function addProgramation(_programation,  _el) {
	var Heure=$('<select class="expressionAttr form-control" data-l1key="Heure" >');
    var Minute=$('<select class="expressionAttr form-control" data-l1key="Minute" >');
	var number = 0;
    while (number < 24) {
		Heure.append($('<option value="'+number+'">')
			.text(number));
    	number++;
	}
  	number = 0;
    while (number < 60) {
		Minute.append($('<option value="'+number+'">')
			.text(number));
    	number++;
	}
	var tr = $('<tr class="ProgramationGroup">')
		.append($('<td class="form-horizontal">')
			.append($('<span class="input-group-btn">')
				.append($('<a class="btn btn-default ProgramationAttr btn-sm" data-action="remove">')
					.append($('<i class="fa fa-minus-circle">'))))
		       	.append($('<span class="expressionAttr" data-l1key="id">')))
		.append($('<td class="form-horizontal">')
			.append($('<div class="form-group">')
				.append($('<div class="col-sm-7">')
					.append($('<label class="checkbox-inline">')
						.append($('<input type="checkbox" class="expressionAttr" data-l1key="1">'))
						.append('{{Lundi}}')))
				.append($('<div class="col-sm-7">')
					.append($('<label class="checkbox-inline">')
						.append($('<input type="checkbox" class="expressionAttr" data-l1key="2">'))
						.append('{{Mardi}}')))
				.append($('<div class="col-sm-7">')
					.append($('<label class="checkbox-inline">')
						.append($('<input type="checkbox" class="expressionAttr" data-l1key="3">'))
						.append('{{Mercredi}}')))
				.append($('<div class="col-sm-7">')
					.append($('<label class="checkbox-inline">')
						.append($('<input type="checkbox" class="expressionAttr" data-l1key="4">'))
						.append('{{Jeudi}}')))
				.append($('<div class="col-sm-7">')
					.append($('<label class="checkbox-inline">')
						.append($('<input type="checkbox" class="expressionAttr" data-l1key="5">'))
						.append('{{Vendredi}}')))
				.append($('<div class="col-sm-7">')
					.append($('<label class="checkbox-inline">')
						.append($('<input type="checkbox" class="expressionAttr" data-l1key="6">'))
						.append('{{Samedi}}')))
				.append($('<div class="col-sm-7">')
					.append($('<label class="checkbox-inline">')
						.append($('<input type="checkbox" class="expressionAttr" data-l1key="0" />'))
						.append('{{Dimanche}}')))))
		.append($('<td class="form-horizontal">')
			.append($('<div class="form-group">')
				.append($('<label class="col-sm-4 control-label">')
					.text('{{Consigne}}')
					.append($('<sup>')
						.append($('<i class="fa fa-question-circle tooltips" title="{{Saisir la température de consigne}}"></i>'))))
				.append($('<div class="col-sm-7">')
					.append($('<input type="text" class="expressionAttr form-control" data-l1key="consigne" placeholder="{{Température de consigne}}">'))))
			.append($('<div class="form-group">')
				.append($('<label class="col-sm-4 control-label">')
					.text('{{Hysteresis}}')
					.append($('<sup>')
						.append($('<i class="fa fa-question-circle tooltips" title="{{Saisir le seuil a +- la température de consigne}}"></i>'))))				
				.append($('<div class="col-sm-7">')
					.append($('<input class="expressionAttr form-control input-sm" data-l1key="hysteresis" placeholder="{{Température de déclenchement}}"/>'))))
			.append($('<div class="form-group">')
				.append($('<label class="col-sm-4 control-label">')
					.text('{{Régulation}}')
					.append($('<sup>')
						.append($('<i class="fa fa-question-circle tooltips" title="{{Activer la regulation avec un seuil de température basse}}"></i>'))))				
				.append($('<div class="col-sm-7">')
					.append($('<label class="checkbox-inline">')
						.append($('<input type="checkbox" class="expressionAttr" data-l1key="isSeuil">'))
						.append('{{Activer}}'))))
			.append($('<div class="form-group">')
				.append($('<label class="col-sm-4 control-label">')
					.text('{{Température}}')
					.append($('<sup>')
						.append($('<i class="fa fa-question-circle tooltips" title="{{Saisir la température de consigne basse}}"></i>'))))				
				.append($('<div class="col-sm-7">')
					.append($('<input class="expressionAttr form-control input-sm" data-l1key="seuil" placeholder="{{Température de déclenchement}}"/>')))))
		.append($('<td class="form-horizontal">')
						.append($('<div class="form-group">')
				.append($('<label class="col-sm-4 control-label">')
					.text('{{Régulation}}')
					.append($('<sup>')
						.append($('<i class="fa fa-question-circle tooltips" title="{{Activer la régulation par horaire}}"></i>'))))				
				.append($('<div class="col-sm-7">')
					.append($('<label class="checkbox-inline">')
						.append($('<input type="checkbox" class="expressionAttr" data-l1key="isHoraire">'))
						.append('{{Activer}}'))))
			.append($('<div class="form-group">')
				.append($('<label class="col-sm-4 control-label">')
					.text('{{Heure de disponibilité}}')
					.append($('<sup>')
						.append($('<i class="fa fa-question-circle tooltips" title="{{Choisir l\'heure de mise a disposition de l\'eau chaude}}"></i>'))))
				.append($('<div class="col-sm-7">')
					.append(Heure)
					.append(Minute))))
		.append($('<td>')
			.append($('<div class="input-group">')
				.append($('<input class="expressionAttr form-control input-sm cmdAction" data-l1key="url">'))
				.append($('<span class="input-group-btn">')
					  .append($('<a class="btn btn-success btn-sm CopyClipboard" title="{{Copier dans le presse papier}}">')
						    .append($('<i class="fa fa-copy">'))))));
        _el.append(tr);
        _el.find('tr:last').setValues(_programation, '.expressionAttr');
	$('.CopyClipboard').off().on('click',function(){
		$(this).closest('td').find('.expressionAttr[data-l1key=url]').select().val();
		document.execCommand("copy");
	});
	$('.ProgramationAttr[data-action=remove]').off().on('click',function(){
		$(this).closest('tr').remove();
	});
	$('.expressionAttr[data-l1key=isSeuil]').off().on('change',function(){
		if($(this).checked)
			$('.expressionAttr[data-l1key=isHoraire]').val(false);
	});
	$('.expressionAttr[data-l1key=isHoraire]').off().on('change',function(){
		if($(this).checked)
			$('.expressionAttr[data-l1key=isSeuil]').val(false);
	});
}
function addCondition(_condition,_el) {
	var tr = $('<tr class="ConditionGroup">')
		.append($('<td>')
			.append($('<input type="checkbox" class="expressionAttr" data-l1key="enable" checked/>')))
		.append($('<td>')
			.append($('<div class="input-group">')
				.append($('<span class="input-group-btn">')
					.append($('<a class="btn btn-default conditionAttr btn-sm" data-action="remove">')
						.append($('<i class="fa fa-minus-circle">'))))
				.append($('<input class="expressionAttr form-control input-sm cmdCondition" data-l1key="expression"/>'))
				.append($('<span class="input-group-btn">')
					.append($('<a class="btn btn-warning btn-sm listCmdCondition">')
						.append($('<i class="fa fa-list-alt">'))))));

        _el.append(tr);
        _el.find('tr:last').setValues(_condition, '.expressionAttr');
	$('.conditionAttr[data-action=remove]').off().on('click',function(){
		$(this).closest('tr').remove();
	});  
}
function addAction(_action,  _el) {
	var tr = $('<tr class="ActionGroup">');
	tr.append($('<td>')
		.append($('<input type="checkbox" class="expressionAttr" data-l1key="enable" checked/>')));		
	tr.append($('<td>')
		.append($('<div class="input-group">')
			.append($('<span class="input-group-btn">')
				.append($('<a class="btn btn-default ActionAttr btn-sm" data-action="remove">')
					.append($('<i class="fa fa-minus-circle">'))))
			.append($('<input class="expressionAttr form-control input-sm cmdAction" data-l1key="cmd"/>'))
			.append($('<span class="input-group-btn">')
				.append($('<a class="btn btn-success btn-sm listAction" title="Sélectionner un mot-clé">')
					.append($('<i class="fa fa-tasks">')))
				.append($('<a class="btn btn-success btn-sm listCmdAction data-type="action"">')
					.append($('<i class="fa fa-list-alt">')))))	
		.append($('<div class="actionOptions">')
	       		.append($(jeedom.cmd.displayActionOption(init(_action.cmd, ''), _action.options)))));
	tr.append(addParameters());
	_el.append(tr);
        _el.find('tr:last').setValues(_action, '.expressionAttr');
	_el.find('tr:last .DawnSimulatorEngine').hide();
	$('.ActionAttr[data-action=remove]').off().on('click',function(){
		$(this).closest('tr').remove();
	});
}
function addParameters() {
	return $('<td>')
		.append($('<select class="expressionAttr form-control custom-select cmdAction" data-l1key="declencheur" multiple>')
			.append($('<option value="on">')
				.text('{{Allumage du chauffe-eau}}'))
			.append($('<option value="off">')
				.text('{{Extinction du chauffe-eau}}'))
			.append($('<option value="dispo">')
				.text('{{Heure de dispo}}'))
			.append($('<option value="DefaillanceSonde">')
				.text('{{Defaillance Sonde}}')));		
}
$('.ActionAttr[data-action=add]').off().on('click',function(){
	addAction({},$(this).closest('.tab-pane').find('table'));
});
$('.ProgramationAttr[data-action=add]').off().on('click',function(){
	addProgramation({},$(this).closest('.tab-pane').find('table'));
});
$('.conditionAttr[data-action=add]').off().on('click',function(){
	addCondition({},$(this).closest('.tab-pane').find('table'));
});
$("body").on('change',".expressionAttr[data-l1key=isSeuil]",function(){
	if($(this).is(':checked'))
		$(this).closest('td').find('.expressionAttr[data-l1key=seuil]').show();
	else			
		$(this).closest('td').find('.expressionAttr[data-l1key=seuil]').hide();
});
$("body").on('change',".expressionAttr[data-l1key=isHoraire]",function(){
	if($(this).is(':checked')){
		$(this).closest('td').find('.expressionAttr[data-l1key=Heure]').show();
		$(this).closest('td').find('.expressionAttr[data-l1key=Minute]').show();
	}else{			
		$(this).closest('td').find('.expressionAttr[data-l1key=Heure]').hide();
		$(this).closest('td').find('.expressionAttr[data-l1key=Minute]').hide();
	}
});
$('.TempEauReel').show();
$('.TempEauEstime').hide();

$(".eqLogicAttr[data-l2key=BacteryProtect]").off().on('change',function(){
	if($(this).is(':checked'))
		$('.BacteryProtect').show();
	else			
		$('.BacteryProtect').hide();
});
$(".eqLogicAttr[data-l2key=TempEauEstime]").off().on('change',function(){
	if($(this).is(':checked')){
		$('.TempEauReel').hide();
		$('.TempEauEstime').show();
	}else{			
		$('.TempEauReel').show();
		$('.TempEauEstime').hide();
	}
});
$("body").on('click', ".listAction", function() {
	var el = $(this).closest('tr').find('.expressionAttr[data-l1key=cmd]');
	jeedom.getSelectActionModal({}, function (result) {
		el.value(result.human);
		jeedom.cmd.displayActionOption(el.value(), '', function (html) {
			el.closest('td').find('.actionOptions').html(html);
		});
	});
}); 
$("body").on('click', ".listCmdAction", function() {
	var el = $(this).closest('tr').find('.expressionAttr[data-l1key=cmd]');
	jeedom.cmd.getSelectModal({cmd: {type: 'action'}}, function (result) {
		el.value(result.human);
		jeedom.cmd.displayActionOption(el.value(), '', function (html) {
			el.closest('td').find('.actionOptions').html(html);
		});
	});
});
$('body').on('click','.listCmdCondition',function(){
	var el = $(this).closest('.input-group').find('.expressionAttr[data-l1key=expression]');	
	jeedom.cmd.getSelectModal({cmd: {type: 'info'}}, function (result) {
		var message = 'Aucun choix possible';
		if(result.cmd.subType == 'numeric'){
			message = '<div class="row">  ' +
			'<div class="col-md-12"> ' +
			'<form class="form-horizontal" onsubmit="return false;"> ' +
			'<div class="form-group"> ' +
			'<label class="col-xs-5 control-label" >'+result.human+' {{est}}</label>' +
			'             <div class="col-xs-3">' +
			'                <select class="conditionAttr form-control" data-l1key="operator">' +
			'                    <option value="==">{{égal}}</option>' +
			'                  <option value=">">{{supérieur}}</option>' +
			'                  <option value="<">{{inférieur}}</option>' +
			'                 <option value="!=">{{différent}}</option>' +
			'            </select>' +
			'       </div>' +
			'      <div class="col-xs-4">' +
			'         <input type="number" class="conditionAttr form-control" data-l1key="operande" />' +
			'    </div>' +
			'</div>' +
			'<div class="form-group"> ' +
			'<label class="col-xs-5 control-label" >{{Ensuite}}</label>' +
			'             <div class="col-xs-3">' +
			'                <select class="conditionAttr form-control" data-l1key="next">' +
			'                    <option value="">rien</option>' +
			'                  <option value="OU">{{ou}}</option>' +
			'            </select>' +
			'       </div>' +
			'</div>' +
			'</div> </div>' +
			'</form> </div>  </div>';
		}
		if(result.cmd.subType == 'string'){
			message = '<div class="row">  ' +
			'<div class="col-md-12"> ' +
			'<form class="form-horizontal" onsubmit="return false;"> ' +
			'<div class="form-group"> ' +
			'<label class="col-xs-5 control-label" >'+result.human+' {{est}}</label>' +
			'             <div class="col-xs-3">' +
			'                <select class="conditionAttr form-control" data-l1key="operator">' +
			'                    <option value="==">{{égale}}</option>' +
			'                  <option value="matches">{{contient}}</option>' +
			'                 <option value="!=">{{différent}}</option>' +
			'            </select>' +
			'       </div>' +
			'      <div class="col-xs-4">' +
			'         <input class="conditionAttr form-control" data-l1key="operande" />' +
			'    </div>' +
			'</div>' +
			'<div class="form-group"> ' +
			'<label class="col-xs-5 control-label" >{{Ensuite}}</label>' +
			'             <div class="col-xs-3">' +
			'                <select class="conditionAttr form-control" data-l1key="next">' +
			'                    <option value="">{{rien}}</option>' +
			'                  <option value="OU">{{ou}}</option>' +
			'            </select>' +
			'       </div>' +
			'</div>' +
			'</div> </div>' +
			'</form> </div>  </div>';
		}
		if(result.cmd.subType == 'binary'){
			message = '<div class="row">  ' +
			'<div class="col-md-12"> ' +
			'<form class="form-horizontal" onsubmit="return false;"> ' +
			'<div class="form-group"> ' +
			'<label class="col-xs-5 control-label" >'+result.human+' {{est}}</label>' +
			'            <div class="col-xs-7">' +
			'                 <input class="conditionAttr" data-l1key="operator" value="==" style="display : none;" />' +
			'                  <select class="conditionAttr form-control" data-l1key="operande">' +
			'                       <option value="1">{{Ouvert}}</option>' +
			'                       <option value="0">{{Fermé}}</option>' +
			'                       <option value="1">{{Allumé}}</option>' +
			'                       <option value="0">{{Éteint}}</option>' +
			'                       <option value="1">{{Déclenché}}</option>' +
			'                       <option value="0">{{Au repos}}</option>' +
			'                       </select>' +
			'                    </div>' +
			'                 </div>' +
			'<div class="form-group"> ' +
			'<label class="col-xs-5 control-label" >{{Ensuite}}</label>' +
			'             <div class="col-xs-3">' +
			'                <select class="conditionAttr form-control" data-l1key="next">' +
			'                  <option value="">{{rien}}</option>' +
			'                  <option value="OU">{{ou}}</option>' +
			'            </select>' +
			'       </div>' +
			'</div>' +
			'</div> </div>' +
			'</form> </div>  </div>';
		}

		bootbox.dialog({
			title: "{{Ajout d'une nouvelle condition}}",
			message: message,
			buttons: {
				"Ne rien mettre": {
					className: "btn-default",
					callback: function () {
						el.atCaret('insert', result.human);
					}
				},
				success: {
					label: "Valider",
					className: "btn-primary",
					callback: function () {
    						var condition = result.human;
						condition += ' ' + $('.conditionAttr[data-l1key=operator]').value();
						if(result.cmd.subType == 'string'){
							if($('.conditionAttr[data-l1key=operator]').value() == 'matches'){
								condition += ' "/' + $('.conditionAttr[data-l1key=operande]').value()+'/"';
							}else{
								condition += ' "' + $('.conditionAttr[data-l1key=operande]').value()+'"';
							}
						}else{
							condition += ' ' + $('.conditionAttr[data-l1key=operande]').value();
						}
						condition += ' ' + $('.conditionAttr[data-l1key=next]').value()+' ';
						el.atCaret('insert', condition);
						if($('.conditionAttr[data-l1key=next]').value() != ''){
							el.click();
						}
					}
				},
			}
		});
	});
});
