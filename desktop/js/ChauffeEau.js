function addCmdToTable(_cmd) {
	
}
$('body').on( 'click','.bt_selectCmdExpression', function() {
	var el = $(this).closest('.input-group').find('.eqLogicAttr');
	jeedom.cmd.getSelectModal({cmd: {type: 'info'},eqLogic: {eqType_name : ''}}, function (result) {
		 el.value(result.human);
	});
});  
$('body').on('click','.ScheduleCron',function(){
  var el = $(this).closest('.input-group').find('.eqLogicAttr');
  jeedom.getCronSelectModal({},function (result) {
    el.value(result.value);
  });
});