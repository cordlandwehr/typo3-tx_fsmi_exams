
function init_update_select() {
	dojo.require("dojo.NodeList-fx");
	
	// update fields on degreeprogram change
	dojo.addOnLoad(function(){
    	var field = dijit.byId("tx_fsmiexams_pi4_field");
  		dojo.connect(dijit.byId("tx_fsmiexams_pi4_degreeprogram"),"onChange",function(){
  			field.query={degreeprogram:(dijit.byId('tx_fsmiexams_pi4_degreeprogram').attr('value'))};
   		});	    
	});
	
	// update modules on field-change
	dojo.addOnLoad(function(){
    	var module = dijit.byId("tx_fsmiexams_pi4_module");
  		dojo.connect(dijit.byId("tx_fsmiexams_pi4_field"),"onChange",function(){
  			module.query={field:(dijit.byId('tx_fsmiexams_pi4_field').attr('value'))};
   		});	    
	});
}
