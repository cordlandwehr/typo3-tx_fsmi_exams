
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
  		dojo.connect(dijit.byId("fsmi_exams_field"),"onChange",function(){
  			dijit.byId("fsmi_exams_module0").query={field:(dijit.byId('fsmi_exams_field').attr('value'))};
  			dijit.byId("fsmi_exams_module1").query={field:(dijit.byId('fsmi_exams_field').attr('value'))};
  			dijit.byId("fsmi_exams_module2").query={field:(dijit.byId('fsmi_exams_field').attr('value'))};
  			dijit.byId("fsmi_exams_module3").query={field:(dijit.byId('fsmi_exams_field').attr('value'))};
   		});	    
	});
	
	// update module1
	dojo.addOnLoad(function(){
  		dojo.connect(dijit.byId("fsmi_exams_module0"),"onChange",function(){
  			dijit.byId("fsmi_exams_module1").setAttribute("disabled",false);
   		});	    
	});
	
	// update module2
	dojo.addOnLoad(function(){
  		dojo.connect(dijit.byId("fsmi_exams_module1"),"onChange",function(){
  			dijit.byId("fsmi_exams_module2").setAttribute("disabled",false);
   		});	    
	});
	
	// update module3
	dojo.addOnLoad(function(){
  		dojo.connect(dijit.byId("fsmi_exams_module2"),"onChange",function(){
  			dijit.byId("fsmi_exams_module3").setAttribute("disabled",false);
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
  		dojo.connect(dijit.byId("fsmi_exams_module"),"onChange",function(){
  			dijit.byId("fsmi_exams_lecture0").query={module:(dijit.byId('fsmi_exams_module').attr('value'))};
  			dijit.byId("fsmi_exams_lecture1").query={module:(dijit.byId('fsmi_exams_module').attr('value'))};
  			dijit.byId("fsmi_exams_lecture2").query={module:(dijit.byId('fsmi_exams_module').attr('value'))};
   		});	    
	});
	
	// update exam-name on lecture-change
	dojo.addOnLoad(function(){
    	var exam = dojo.byId("fsmi_exams_name");
  		dojo.connect(dijit.byId("fsmi_exams_lecture0"),"onChange",function(){
  			exam.value=dijit.byId('fsmi_exams_lecture0').attr('displayedValue');
   		});	    
	});
	
	// update lecture1
	dojo.addOnLoad(function(){
  		dojo.connect(dijit.byId("fsmi_exams_lecture0"),"onChange",function(){
  			dijit.byId("fsmi_exams_lecture1").setAttribute("disabled",false);
   		});	    
	});
	
	// update lecture2
	dojo.addOnLoad(function(){
  		dojo.connect(dijit.byId("fsmi_exams_lecture1"),"onChange",function(){
  			dijit.byId("fsmi_exams_lecture2").setAttribute("disabled",false);
   		});	    
	});
	
	// update lecturer1
	dojo.addOnLoad(function(){
  		dojo.connect(dijit.byId("fsmi_exams_lecturer0"),"onChange",function(){
  			dijit.byId("fsmi_exams_lecturer1").setAttribute("disabled",false);
   		});	    
	});
	
	// update lecturer2
	dojo.addOnLoad(function(){
  		dojo.connect(dijit.byId("fsmi_exams_lecturer1"),"onChange",function(){
  			dijit.byId("fsmi_exams_lecturer2").setAttribute("disabled",false);
   		});	    
	});
}