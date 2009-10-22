
function init_update_select_lecture() {
	dojo.require("dojo.NodeList-fx");
	
	// update fields on degreeprogram change
	dojo.addOnLoad(function(){
    	var field = dijit.byId("fsmi_exams_field");
  		dojo.connect(dijit.byId("fsmi_exams_degreeprogram"),"onChange",function(){
  			field.query={degreeprogram:(dijit.byId('fsmi_exams_degreeprogram').attr('value'))};
   		});	    
	});
	
	// update modules on field-change
	dojo.addOnLoad(function(){
    	var module = dijit.byId("fsmi_exams_module");
  		dojo.connect(dijit.byId("fsmi_exams_field"),"onChange",function(){
  			module.query={field:(dijit.byId('fsmi_exams_field').attr('value'))};
   		});	    
	});
}

function init_update_select_exam() {
	dojo.require("dojo.NodeList-fx");
	
	
	// update fields on degreeprogram change
	dojo.addOnLoad(function(){
    	var field = dijit.byId("fsmi_exams_field");
  		dojo.connect(dijit.byId("fsmi_exams_degreeprogram"),"onChange",function(){
  			field.query={degreeprogram:(dijit.byId('fsmi_exams_degreeprogram').attr('value'))};
   		});	    
	});
	
	// update modules on field-change
	dojo.addOnLoad(function(){
    	var module = dijit.byId("fsmi_exams_module");
  		dojo.connect(dijit.byId("fsmi_exams_field"),"onChange",function(){
  			module.query={field:(dijit.byId('fsmi_exams_field').attr('value'))};
   		});	    
	});

	// update lecture on module-change
	dojo.addOnLoad(function(){
    	var lecture = dijit.byId("fsmi_exams_lecture");
  		dojo.connect(dijit.byId("fsmi_exams_module"),"onChange",function(){
  			lecture.query={module:(dijit.byId('fsmi_exams_module').attr('value'))};
   		});	    
	});
	
	// update exam-name on lecture-change
	dojo.addOnLoad(function(){
    	var exam = dojo.byId("fsmi_exams_name");
  		dojo.connect(dijit.byId("fsmi_exams_lecture"),"onChange",function(){
  			exam.value=dijit.byId('fsmi_exams_lecture').attr('displayedValue');
   		});	    
	});
}