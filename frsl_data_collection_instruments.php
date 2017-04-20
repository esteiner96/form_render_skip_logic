<?php
/*
 *Changes the left-hand menu of data collection instruments to only show
 *instruments appropriate to diagnosis
 *
 * TODO:
 *
 * 1. factor out repeated code across all fsrl hooks into a common library
 * 2. Information retrieval of patient data fails without calling redcap getdata for SDH_TYPE,
 *    SAH_TYPE and UBI_TYPE
 */

return function($project_id) {

    $URL = $_SERVER['REQUEST_URI'];
    if(preg_match('/DataEntry/', $URL) == 1) {

        //get necesary information
        $patient_id = $_GET["id"];
        $project_json = json_decode('{
                                         "control_field":{
                                            "arm_name":"visit_1_arm_1",
                                            "field_name":"patient_type"
                                         },
                                         "instruments_to_show":[
                                            {
                                               "control_field_value":"1",
                                               "instrument_names":[
                                                  "sdh_details",
                                                  "past_medical_history_sah_sdh"
                                               ]
                                            },
                                            {
                                               "control_field_value":"2",
                                               "instrument_names":[
                                                  "sah_details",
                                                  "past_medical_history_sah_sdh"
                                               ]
                                            },
                                            {
                                               "control_field_value":"3",
                                               "instrument_names":[
                                                  "medications_sah_sdh"
                                               ]
                                            }
                                         ]
                                      }'
                                      , true);

        $arm_name = $project_json['control_field']['arm_name'];
        $field_name = $project_json['control_field']['field_name'];
        $control_field_value = REDCap::getData($project_id, 'json', $patient_id, $field_name, $arm_name, null, false, false, null, null, null);
        print($control_field_value);
    } else {
        //abort the hook
        echo "<script> console.log('aborting frsl record home page') </script>";
        return;
    }
?>
    <script>

    var json = <?php echo json_encode($project_json) ?>;
    var control_field_value = <?php echo $control_field_value ?>;
    var control_field_name = "<?php echo $field_name ?>";
    var control_value;

    //set control value if it exists
    if(controlValueFound(control_field_value)){
        control_value = control_field_value[0][control_field_name];
    } else {
        control_value = false;
    }

    //checks to see if a control value has been set for the subject record
    function controlValueFound(data) {
        if (data.length == 1 && data[0].hasOwnProperty(control_field_name)) {
            return true;
        }
        return false;
    }

    function get_instrument_names_object(control_value, json) {
        for(var i = 0; i < json.instruments_to_show.length; i++)
        {
          if(json.instruments_to_show[i].control_field_value == control_value)
          {
            return json.instruments_to_show[i].instrument_names;
          }
        }
    }

    function enable_desired_forms() {
        var instruments = get_instrument_names_object(control_value, json)

        // json.instruments_to_show[index].instrument_names
        for (var instrument in instruments) {
            enable_required_forms(instruments[instrument]);
        }
    }

    function disable_all_forms(){
    	var arr = document.getElementsByClassName('formMenuList')
    	for(var i=0;i<arr.length;i++){
			document.getElementsByClassName('formMenuList')[i].style.display = 'none';
		}
    }

    function enable_required_forms(form){

    	var arr = document.getElementsByClassName('formMenuList')
    	for(var i=0; i<arr.length;i++){
    		var str = arr[i].getElementsByTagName('a')[1].getAttribute('id').match(/\[(.*?)\]/)[1];
    		if(str == form)
    			arr[i].style.display = 'block';
    	}
    }

    $('document').ready(function() {
        disable_all_forms();
        enable_desired_forms();

        });

    </script>
    <?php

}
?>