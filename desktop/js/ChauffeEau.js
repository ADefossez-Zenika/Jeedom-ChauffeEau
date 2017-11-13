$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
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
	var ProgramationArray= new Array();
	var ConditionArray= new Array();
	$('#programationtab .ProgramationGroup').each(function( index ) {
		ProgramationArray.push($(this).getValues('.expressionAttr')[0])
	});
	$('#conditiontab .ConditionGroup').each(function( index ) {
		ConditionArray.push($(this).getValues('.expressionAttr')[0])
	});
	_eqLogic.configuration.programation=ProgramationArray;
	_eqLogic.configuration.condition=ConditionArray;
   	return _eqLogic;
}
function printEqLogic(_eqLogic) {	
	$('.ProgramationGroup').remove();
	$('.ConditionGroup').remove();
	if (typeof(_eqLogic.configuration.programation) !== 'undefined') {
		for(var index in _eqLogic.configuration.programation) {
			if( (typeof _eqLogic.configuration.programation[index] === "object") && (_eqLogic.configuration.programation[index] !== null) )
				addProgramation(_eqLogic.configuration.programation[index],$('#programationtab').find('table tbody'));
		}
	}
	if (typeof(_eqLogic.configuration.condition) !== 'undefined') {
		for(var index in _eqLogic.configuration.condition) { 
			if( (typeof _eqLogic.configuration.condition[index] === "object") && (_eqLogic.configuration.condition[index] !== null) )
				addCondition(_eqLogic.configuration.condition[index],  '{{Condition}}',$('#conditiontab').find('.div_Condition'));
		}
	}
}
function addCondition(_condition, _name, _el) {
	var div = $('<div class="form-group ConditionGroup">')
		.append($('<label class="col-sm-1 control-label">')
			.text('ET'))
		.append($('<div class="col-sm-4 has-success">')
			.append($('<div class="input-group">')
				.append($('<span class="input-group-btn">')
					.append($('<input type="checkbox" class="expressionAttr" data-l1key="enable"/>'))
					.append($('<a class="btn btn-default conditionAttr btn-sm" data-action="remove">')
						.append($('<i class="fa fa-minus-circle">'))))
				.append($('<input class="expressionAttr form-control input-sm cmdCondition" data-l1key="expression"/>'))
				.append($('<span class="input-group-btn">')
					.append($('<a class="btn btn-warning btn-sm listCmdCondition">')
						.append($('<i class="fa fa-list-alt">'))))));
        _el.append(div);
        _el.find('.ConditionGroup:last').setValues(_condition, '.expressionAttr');
	$('.conditionAttr[data-action=remove]').off().on('click',function(){
		$(this).closest('.ConditionGroup').remove();
	});
  
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
		.append($('<td>')
			.append($('<span class="input-group-btn">')
				.append($('<a class="btn btn-default ProgramationAttr btn-sm" data-action="remove">')
					.append($('<i class="fa fa-minus-circle">')))))
		.append($('<td>')
			.append($('<input type="checkbox" class="expressionAttr" data-l1key="1" />'))
			.append($('<label class="checkbox-inline">')
				.text('{{Lundi}}'))
			.append($('<input type="checkbox" class="expressionAttr" data-l1key="2" />'))
			.append($('<label class="checkbox-inline">')
				.text('{{Mardi}}'))
			.append($('<input type="checkbox" class="expressionAttr" data-l1key="3" />'))
			.append($('<label class="checkbox-inline">')
				.text('{{Mercredi}}'))
			.append($('<input type="checkbox" class="expressionAttr" data-l1key="4" />'))
			.append($('<label class="checkbox-inline">')
				.text('{{Jeudi}}'))
			.append($('<input type="checkbox" class="expressionAttr" data-l1key="5" />'))
			.append($('<label class="checkbox-inline">')
				.text('{{Vendredi}}'))
			.append($('<input type="checkbox" class="expressionAttr" data-l1key="6" />'))
			.append($('<label class="checkbox-inline">')
				.text('{{Samedi}}'))
			.append($('<input type="checkbox" class="expressionAttr" data-l1key="0" />'))
			.append($('<label class="checkbox-inline">')
				.text('{{Dimanche}}')))
		.append($('<td>')
			.append(Heure)
			.append(Minute));
        _el.append(tr);
        _el.find('tr:last').setValues(_programation, '.expressionAttr');
	$('.ProgramationAttr[data-action=remove]').off().on('click',function(){
		$(this).closest('tr').remove();
	});
}
$('.ProgramationAttr[data-action=add]').off().on('click',function(){
	addProgramation({},$(this).closest('.tab-pane').find('table'));
});
$('.conditionAttr[data-action=add]').off().on('click',function(){
	addCondition({},  '{{Condition}}',$(this).closest('.form-horizontal').find('.div_Condition'));
});
$('body').on('click','.listCmdCondition',function(){
	var el = $(this).closest('.form-group').find('.expressionAttr[data-l1key=expression]');	
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
