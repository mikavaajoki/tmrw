/* global uci_importer */

jQuery(function(){
    jQuery('.RegField_iCheck, input[type=radio]:not(".noicheck"), input[type=checkbox]:not(".noicheck")').iCheck({
        checkboxClass: 'icheckbox_square-green',
        radioClass: 'iradio_square-green',
        increaseArea: '20%' // optional
    });
    jQuery('[data-toggle="tooltip"]').tooltip()
});

jQuery(function(){
jQuery('.upgrade_pro_checkbox').parent().addClass('disabled');
});

jQuery(function(){
    jQuery("#fileupload").on('click', function(e){
        e.preventDefault();
        jQuery("#upload_file:hidden").trigger('click');
    });
});

jQuery(function() {
    var check_upload_dir = document.getElementById('is_found').value;
    if(check_upload_dir == "dir not found") {
       jQuery('<p/>').text("Warning: Sorry. There is no uploads directory Please create it with write permission.").appendTo('#warning');
       jQuery('#warning p').css('color', 'red');
       jQuery('#warning').css('font-weight', 'bold'); 
       jQuery('#warning').css('display', 'block');
       jQuery('#warningsec').css('display', 'block');
       jQuery('#panel upload-view').css('visibility', 'hidden');
       jQuery('.bhoechie-tab-container').css('visibility', 'hidden');
       jQuery('.list-inline pull-right mb10').css('visibility', 'hidden');
       jQuery('.clearfix').css('visibility', 'hidden');
       jQuery('.row').css('visibility', 'hidden');
       jQuery('.panel-body').css('display', 'none');
       return false;
    }
    var check_permission = document.getElementById('is_perm_found').value;
    if(check_permission == "perm not found") {
        jQuery('<p/>').text("Warning: Sorry. There is no permission for your uploads directory. Please provide the write permission.").appendTo('#warning');
        jQuery('#warning p').css('color', 'red');
        jQuery('#warning').css('font-weight', 'bold');
        jQuery('#warning').css('display', 'block');
        jQuery('#warningsec').css('display', 'block');
        jQuery('#panel upload-view').css('visibility', 'hidden');
        jQuery('.bhoechie-tab-container').css('visibility', 'hidden');
        jQuery('.list-inline pull-right mb10').css('visibility', 'hidden');
        jQuery('.clearfix').css('visibility', 'hidden');
        jQuery('.row').css('visibility', 'hidden');
        jQuery('.panel-body').css('display', 'none');
   }
});

// example use
var timer;

jQuery(document).ready(function(e)
{
    timer = new _timer
    (
        function(time)
        {
            if(time == 0)
            {
                timer.stop();
                swal('Warning!', 'Time Out.', 'warning')
            }
        }
    );
    timer.reset(0);
    timer.mode(0);
});

function _timer(callback)
{
    var time = 0;     //  The default time of the timer
    var mode = 1;     //    Mode: count up or count down
    var status = 0;    //    Status: timer is running or stoped
    var timer_id;    //    This is used by setInterval function

    // this will start the timer ex. start the timer with 1 second interval timer.start(1000)
    this.start = function(interval)
    {
        interval = (typeof(interval) !== 'undefined') ? interval : 1000;

        if(status == 0)
        {
            status = 1;
            timer_id = setInterval(function()
            {
                switch(mode)
                {
                    default:
                        if(time)
                        {
                            time--;
                            generateTime();
                            if(typeof(callback) === 'function') callback(time);
                        }
                        break;

                    case 1:
                        if(time < 86400)
                        {
                            time++;
                            generateTime();
                            if(typeof(callback) === 'function') callback(time);
                        }
                        break;
                }
            }, interval);
        }
    }

    //  Same as the name, this will stop or pause the timer ex. timer.stop()
    this.stop =  function()
    {
        if(status == 1)
        {
            status = 0;
            clearInterval(timer_id);
        }
    }

    // Reset the timer to zero or reset it to your own custom time ex. reset to zero second timer.reset(0)
    this.reset =  function(sec)
    {
        sec = (typeof(sec) !== 'undefined') ? sec : 0;
        time = sec;
        generateTime(time);
    }

    // Change the mode of the timer, count-up (1) or countdown (0)
    this.mode = function(tmode)
    {
        mode = tmode;
    }

    // This methode return the current value of the timer
    this.getTime = function()
    {
        return time;
    }

    // This methode return the current mode of the timer count-up (1) or countdown (0)
    this.getMode = function()
    {
        return mode;
    }

    // This methode return the status of the timer running (1) or stoped (1)
    this.getStatus
    {
        return status;
    }

    // This methode will render the time variable to hour:minute:second format
    function generateTime()
    {
        var second = time % 60;
        var minute = Math.floor(time / 60) % 60;
        var hour = Math.floor(time / 3600) % 60;

        second = (second < 10) ? '0' + second : second;
        minute = (minute < 10) ? '0' + minute : minute;
        hour = (hour < 10) ? '0' + hour : hour;

        jQuery('div.event-summary span.second').html(second);
        jQuery('div.event-summary span.minute').html(minute);
        jQuery('div.event-summary span.hour').html(hour);
    }
}

function show_upload(id) {
    for(var i=1; i<=4; i++) {
        if(parseInt(id) == parseInt(i)) {
            set_importMethod(id);
            if(parseInt(i) == 4 || parseInt(i) == 3 || parseInt(i) == 2 || parseInt(i) == 1) {
                document.getElementById('division'+id).style.display = "";
                document.getElementById('displaysection').style.display = "none";
            }
        } else {
        }
    }
}

function set_importMethod(id) {
    var import_method = '';
    if(id == 1)
        import_method = 'desktop';
    else if(id == 2)
        import_method = 'ftp';
    else if(id == 3)
        import_method = 'url';
    else if(id == 4)
        import_method = 'server';
    else
        import_method = 'desktop';
    jQuery('#import_method').val(import_method);
}

function upload_method(){
    var formData = new FormData();
    var filesArray = jQuery('#upload_file').prop('files')[0];
    formData.append('files', filesArray);
    formData.append('action','upload_actions');
    document.getElementById('division1').style.display = "none";
    document.getElementById('displaysection').style.display = '';
    jQuery('#loader-icon').show();
    jQuery("#progress-bar").width('0%');
    jQuery.ajax({
        type: 'post',
        url: ajaxurl,
        data: formData,
        contentType: false,
        cache: false,
        processData: false,
        target:   '#targetLayer',
        xhr: function(){
            //upload Progress
            var xhr = jQuery.ajaxSettings.xhr();
            if (xhr.upload) {
                xhr.upload.addEventListener('progress', function(event) {
                    var percent = 0;
                    var position = event.loaded || event.position;
                    //var position = event.position;
                    var total = event.total;
                    if (event.lengthComputable) {
                        percent = Math.ceil(position / total * 100);
                    }
                    //update progressbar
                    jQuery("#progress-div" + " #progress-bar").css("width", + percent +"%");
                }, true);
            }
            return xhr;
        },
        success: function (uploaded_file_info) {
            uploaded_file_info = JSON.parse(uploaded_file_info);
            jQuery.each(uploaded_file_info, function(objkey, objval){
                jQuery.each(objval, function(o_key, file){
                    document.getElementById('file_name').value = file.name;
                    document.getElementById('uploaded_name').value = file.uploadedname;
                    var file_extn = file.name.split(".");
                    var check_file = file_extn[file_extn.length - 1];
                    document.getElementById('file_extension').value = check_file;
                    var get_current_action = jQuery( '#form_import_file' ).attr( 'action' );
                    if(check_file != "csv" && check_file != "zip" && check_file != "txt") {
                        warning = 'Un Supported File Format';
                        swal({
                                title: warning,
                                text: "You will not be able to upload this file!",
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonColor: "#DD6B55",
                                confirmButtonText: "Upload file again?",
                                closeOnConfirm: false
                            },
                            function(){
                                jQuery('#displaysection').css('display', 'none');
                                jQuery('#division1').css('display', '');
                                swal("Deleted!", "Your uploaded file has been deleted.", "success");
                            });
                        document.getElementById('upload_file').value="";
                        return false;
                    }
                    if(check_file == "zip"){
                        jQuery.ajax({
                            type: 'POST',
                            url: ajaxurl,
                            data: {
                                'action': 'upload_zipfile_handler',
                                'eventkey': file.eventkey,
                                'import_method':'desktop'
                            },
                            success: function (uploaded_zip_info) {
                                uploaded_zip_info = JSON.parse(uploaded_zip_info);
                                document.getElementById('choose_file').innerHTML = uploaded_zip_info['data'];
                                jQuery('#modal_zip').modal('show');
                            }
                        });
                    } else {
                        var version = file_extn[0].split("-");
                        var current_version = version[version.length - 1];
                        document.getElementById('file_version').value = current_version;
                        if (file.size > 1024 && file.size < (1024 * 1024)) {
                            var fileSize = (file.size / 1024).toFixed(2) + ' kb';
                        }
                        else if (file.size > (1024 * 1024)) {
                            var fileSize = (file.size / (1024 * 1024)).toFixed(2) + ' mb';
                        }
                        else {
                            var fileSize = (file.size) + ' byte';
                        }
			var max_filesize = document.getElementById('upload_max').value;  
                       	var max_size = 'Please increase the upload_max_filesize in php.ini \n (Or) \n Upload the csv file below ' +  max_filesize + '.';   
                       	if(fileSize == '0 byte') {
                               warning = 'Un Supported File Format';
                           swal({
                                title: 'Sorry your filesize is exceeded.',
                                text:  max_size,
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonColor: "#DD6B55",
                                confirmButtonText: "Upload file again?",
                                closeOnConfirm: false
                            },
                            function(){
                                jQuery('#displaysection').css('display', 'none');
                                jQuery('#division1').css('display', '');
                                swal("Deleted!", "Your uploaded file has been deleted.", "success");
                            });
                        	document.getElementById('upload_file').value="";
                        	return false;
                       	}
                        jQuery("#filenamedisplay").empty();
                        jQuery('<label/>').text((file.uploadedname) + ' - ' + fileSize).appendTo('#filenamedisplay');
                    }
                    if(check_file != 'zip') {
                        jQuery.ajax({
                            type: 'POST',
                            url: ajaxurl,
                            data: {
                                'action': 'set_post_types',
                                'filekey': file.eventkey,
                                'uploadedname': file.uploadedname
                            },
                            success: function (priority_result) {
                                console.log(priority_result);
                                if(priority_result != '') {
                                    priority_result = JSON.parse(priority_result);
                                    if (priority_result['is_template'] == 'yes') {
                                        var action = get_current_action + '&eventkey=' + file.eventkey;
                                    } else {
                                        var splitaction = get_current_action.split("&");
                                        var action = splitaction[0] + '&step=mapping_config&istemplate=no&eventkey=' + file.eventkey;
                                    }
                                   jQuery('.selectpicker').selectpicker('val', priority_result['type']);
                                   var checkvalue = jQuery('.selectpicker').val();
                                   if(checkvalue == null)
                                    jQuery('.selectpicker').selectpicker('val', 'Posts');
                                    if(priority_result['type'] == 'Users'){
                                        if(document.getElementById('check_user_import').value == 'no'){
                                            document.getElementById('user_import_warning').style.display = 'block';
                                        }
                                    }
                                } else {
                                    var splitaction = get_current_action.split("&");
                                    var action = splitaction[0] + '&step=mapping_config&istemplate=no&eventkey=' + file.eventkey;
                                }
                                jQuery('#form_import_file').attr('action', action);
                                jQuery('.continue-btn').attr('disabled', false);
                            }
                        });
                    }
                })
            })
        },
        error: function (errorThrown) {
            console.log(errorThrown);
        }
    });
}

function choose_file_from_zip(filename, external_url, import_method) {
    if(import_method == 'desktop'){
        hidediv = 'division1';
    }else if(import_method == 'url'){
        hidediv = 'division3';
    }else if(import_method == 'ftp'){
        hidediv = 'division2';
    }else if(import_method == 'server'){
        hidediv = 'division4';
    }
    var postdata = new Array({'external_file_url':external_url,'import_method':import_method});
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
            'action': 'external_file_actions',
            'postdata': postdata,
        },
        target:   '#targetLayer',
        xhr: function(){
            //upload Progress
            var xhr = jQuery.ajaxSettings.xhr();
            if (xhr.upload) {
                xhr.upload.addEventListener('progress', function(event) {
                    var percent = 0;
                    var position = event.loaded || event.position;
                    //var position = event.position;
                    var total = event.total;
                    if (event.lengthComputable) {
                        percent = Math.ceil(position / total * 100);
                    }
                    //update progressbar
                    jQuery("#progress-div" + " #progress-bar").css("width", + percent +"%");
                }, true);
            }
            return xhr;
        },
        success: function (data) {
            data = JSON.parse(data);
            if(data['Success'] == 'Success!') {
                document.getElementById('file_name').value = data['filename'];
                document.getElementById('uploaded_name').value = data['uploaded_name'];
                document.getElementById('file_version').value = data['version'];
                var get_current_action = jQuery( '#form_import_file' ).attr( 'action' );
                //var action = get_current_action + '&eventkey=' + data['eventkey'];
                //jQuery('#form_import_file').attr('action', action);
                document.getElementById('file_extension').value = data['extension'];
                document.getElementById('displaysection').style.display = "";
                document.getElementById(hidediv).style.display = "none";
                jQuery("#filenamedisplay").empty();
                jQuery('<label/>').text((data['filename']) + ' - ' + data['filesize']).appendTo('#filenamedisplay');
                jQuery.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        'action': 'set_post_types',
                        'filekey': data['eventkey'],
                        'uploadedname': data['filename']
                    },
                    success: function (result) {
                        var result = JSON.parse(result);
                        if(result != '') {
                            if (result['is_template'] == 'yes') {
                                var action = get_current_action + '&eventkey=' + data['eventkey'];
                            } else {
                                var splitaction = get_current_action.split("&");
                                var action = splitaction[0] + '&step=mapping_config&istemplate=no&eventkey=' + data['eventkey'];
                            }
                            jQuery('.selectpicker').selectpicker('val', result['type']);
                        } else {
                            var splitaction = get_current_action.split("&");
                            var action = splitaction[0] + '&step=mapping_config&istemplate=no&eventkey=' + data['eventkey'];
                        }
                        jQuery('#form_import_file').attr('action', action);
                        jQuery('.continue-btn').attr('disabled', false);
                        //var type = JSON.parse(type);
                        //document.getElementById('search_dropdowns').value = type;
                    }
                });
            } else {
                warning = data['Failure'];
                //notice_warning(warning);
                swal(waiting);
                document.getElementById('upload_file').value="";
                return false;
            }

        }, error: function (errorThrown) {
            console.log(errorThrown);
        }
    });
}

function server_method(){
    /*jQuery('#filename').html('post.csv');
     document.getElementById('displaysection').style.display = "";
     document.getElementById('division4').style.display = "none";*/
}

function toggle_func(id){
    if(id != 'types_custom_fields' && id != 'acf_fields' && id != 'pods_custom_fields' && id != 'cctm_custom_fields' && id != 'acf_repeater_fields' && id != 'acf_pro_fields' && id != 'yoast_seo_fields'){
	    jQuery('#'+id+'toggle').slideToggle('slow');
	    jQuery('#icon'+id).toggleClass("icon-circle-down").toggleClass("icon-circle-up");
	    jQuery('#'+id).toggleClass("text-primary");
    }
}

/** VAlidate Custom Field Choice Text **/
function validate_options(prefix,mappingcount) {
    var option_id = prefix + '_type_options'+mappingcount;
    var option_text = document.getElementById(option_id).value;
    var pattern = new RegExp('^([A-Za-z0-9]\s?)+([,]\s?([A-Za-z0-9]\s?)+)*$');
    var match = pattern.test(option_text);
    if(match){
        document.getElementById(prefix+'Register'+mappingcount).disabled=false;
        document.getElementById(option_id).style.removeProperty("border");
        return true;
    }
    else{
        document.getElementById(option_id).style.border = '3px solid #FF0000';
        document.getElementById(option_id).value = '';
        document.getElementById(prefix+'Register'+mappingcount).disabled=true;
        return false;
    }
}


/** Delete ACF Pro Fields **/
function Delete_acf_pro_fields(prefix,import_type,core_count) {
    // Removed: Delete register field for ACF Pro
    return true;
}

/** Delete ACF Free Fields **/
function Delete_acf_free_fields(prefix,import_type,core_count) {
    // Removed: Delete register field for ACF Free
    return true;
}

/** Delete Pods Fields **/
function Delete_pods_fields(prefix,import_type,core_count) {
    // Removed: Delete register field for PODS
    return true;
}

/** Delete Types Fiels **/
function Delete_types_fields(prefix,import_type,core_count) {
    // Removed: Delete register field for Toolset Types
    return true;
}
/** Validate the Custom Field data's
 * Call the Field's Choice for radio button,select and checkbox field types
 **/
function Validate_CF_types(id,prefix,mappingcount) {
    // Removed: Register field validation for ACF, Types & PODS
    return true;
}

/**
 * Show the UI for Relational Field of PODS
 **/

function show_PODS_relational_options(id,prefix,mappingcount,import_type) {
    // Removed: PODS relational field
    return true;
}

/** Show th UI of ACF Field Choices for Select,Checkbox and radio button
 *  Show the UI of ACF Field Choices for User
 **/
function show_ACF_options(id,prefix,mappingcount) {
    // Removed: ACF optional field
    return true;
}


/**
 * Register the Custom Fields such as ACF,PODS and TYPES
 * It calls the First UI of Field Registration
 **/
function addCustomfield(prefix,slug,eventkey) {
    if(prefix == "CORECUSTFIELDS") {
    jQuery(".mapping-select-div").click(function(){
    jQuery(".mapping-select-close-div").hide();
    });
    jQuery(".mapping-static-formula-group").click(function(){
    jQuery(".mapping-select-close-div").hide();
    });
    var table = document.getElementById(slug+'_table');
    var core_count = document.getElementById(slug+'_count').value;
    var row = table.insertRow(-1);
    var mappingcount = document.getElementById(slug+'_count').value;
    row.id = prefix+'_tr_count'+core_count;
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
            'action': 'uci_picklist_handler',
            'prefix' : prefix,
            'slug' : slug,
            'count' : core_count,
            'eventkey' : eventkey,
        },
        success: function (data) {
            document.getElementById(prefix+'_tr_count'+core_count).innerHTML = data;
            if(prefix == 'CORECUSTFIELDS'){
                jQuery('#'+prefix+'Delete'+core_count).css('display', '');
                document.getElementById(slug+'_count').value = parseInt(core_count) + 1;
                document.getElementById('h1').value = parseInt(mappingcount) + 1;
                jQuery('.selectpicker').selectpicker('refresh');
            }
        },
        error: function (errorThrown) {
            console.log(errorThrown);
        }
    });
    } else {
	 swal('Warning!', 'Please upgrade to PRO for Register the Custom fields.', 'warning')	
    }
}

// Disabled the ACF Pro Fieldtype Options
function disable_acf_fields(id,slug){
    // Removed: Disable option for ACF field
}

/**
 * It is used to set the data for register the WordPress Core Custom Fields
 **/
function SetWPRegisterData(id, mappingcount, prefix) {
    var field_name = document.getElementById(id).value;
    if(field_name != ''){
        document.getElementById(id).style.border = '1px solid #B0B0B0 ';
        field_name = field_name.toLowerCase();
        field_name = field_name.replace(/\s/g, "_").replace(/\./g,"_");
        var field_label= prefix+"CustomField"+mappingcount;
        document.getElementById(field_label).innerHTML="[Name: "+field_name+"]";
        document.getElementById(prefix+'__fieldname'+mappingcount).value = field_name;
        jQuery('#' + prefix + '__mapping' + mappingcount).prop('disabled', false);
        jQuery('#' + prefix + '__mapping' + mappingcount).selectpicker('refresh');
    }
    else{
        document.getElementById(id).style.border = '3px solid #FF0000';
        //alert('Please enter the value');
        swal('Warning!', 'Please enter the field name', 'warning')
        jQuery('#' + prefix + '__mapping' + mappingcount).prop('disabled', true);
        jQuery('#' + prefix + '__mapping' + mappingcount).selectpicker('refresh');
    }
}


function Show_RegisteredUI(prefix, slug, core_count) {
    var mappingcount = document.getElementById('h1').value;
    //document.getElementById(prefix+'_tdc_count'+core_count).style.display = '';
    document.getElementById(prefix+'_tdg_count'+core_count).style.display = '';
    document.getElementById(prefix+'_tdd_count'+core_count).style.display = '';
    document.getElementById(prefix+'_tdh_count'+core_count).style.display = '';
    document.getElementById(prefix+'_tdi_count'+core_count).style.display = '';
    document.getElementById(prefix+'Delete'+core_count).style.display = '';
    document.getElementById(prefix+'newrow'+core_count).style.display = 'none';
    if(slug == 'pods_custom_fields' || slug == 'acf_pro_fields' || slug == 'types_custom_fields' || slug == 'acf_fields'){
        document.getElementById(slug+'_count').value = parseInt(core_count) + 1;
        document.getElementById('h1').value = parseInt(mappingcount) + 1;
        jQuery('#'+prefix+'__mapping'+core_count).selectpicker('refresh');
    }
}

function Close_RegisterUI(prefix,core_count) {
    var rowid = prefix+'_tr_count'+core_count;
    var row = document.getElementById(rowid);
    row.parentNode.removeChild(row);
}

function is_emptyCF(id,mappingcount,prefix) {
    var fieldData = document.getElementById(id).value;
    if(fieldData == ''){
        document.getElementById(id).style.border = '3px solid #FF0000';
        swal('Warning!', 'Please enter the field details.', 'warning')
        document.getElementById(prefix+'Register'+mappingcount).disabled = true;
    }
    else{
        document.getElementById(id).style.removeProperty("border");
        document.getElementById(prefix+'Register'+mappingcount).disabled = false;
    }
}

function validateCF(prefix,mappingcount,slug) {
    var field_type = document.getElementById(prefix + '_datatype_' + mappingcount).value;
    var field_label = document.getElementById(prefix + 'ui__CustomFieldLabel' + mappingcount).value;
    var field_name = document.getElementById(prefix + 'ui__CustomFieldName' + mappingcount).value;
    var field_info = true;
    if(field_type == '--Select--') {
        document.getElementById(prefix+'Register'+mappingcount).disabled = true;
        field_info = false;
    }
    else {
        if(field_type == 'select' || field_type == 'checkbox' || field_type == 'radio') {
            field_info = validate_options(prefix,mappingcount);
        }
    }
    if(field_label == '' || field_name == '' || field_info == false) {
        document.getElementById(prefix+'Register'+mappingcount).disabled = true;
        document.getElementById(prefix + 'ui__CustomFieldLabel' + mappingcount).value = '';
        document.getElementById(prefix + 'ui__CustomFieldName' + mappingcount).value = ''
        swal('Warning!', "Don't leave as empty the following information [Label, Name & Field Type].", 'warning')
        return false;
    }
    else {
        document.getElementById(prefix+'Register'+mappingcount).disabled = false;
        return true;
    }
}

jQuery(document).ready(function () {
    var url = window.location.href;

    jQuery.urlParam = function(name){
        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(url);
        return results[1] || 0;
    }
    var activeModule = jQuery.urlParam('page');

    var pages = [];
    pages[1] = 'sm-uci-dashboard';
    pages[2] = 'sm-uci-import';
    pages[3] = 'sm-uci-managers';
    pages[4] = 'sm-uci-export';
    pages[5] = 'sm-uci-settings';
    pages[6] = 'sm-uci-addons';
    pages[7] = 'sm-uci-support';

    for(var i=1; i<=7; i++){
        if(activeModule == pages[i]) {
            jQuery('#menu'+i).addClass("nav-tab-active");
        }
        else {
            jQuery('#menu'+i).removeClass('nav-tab-active');
        }
    }
    jQuery('#from-date').datepicker({
        dateFormat: 'yy-mm-dd',
    });
    jQuery('#to-date').datepicker({
        dateFormat: 'yy-mm-dd',
    });
    jQuery('#search').keyup(function() {
        var template_name = jQuery('#search').val();
        var template_table = document.getElementById('templates');
        var filename = document.getElementById("filename").value;
        if(template_table != null) {
            template_table.innerHTML = '';
        }
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            async: false,
            data: {
                'action': 'search_template',
                'templatename': template_name,
                'filename' : filename
            },
            success: function (data) {
                var template_list = JSON.parse(data);
                generate_row(template_list);
            },
            error: function (errorThrown) {
                console.log(errorThrown);
            }
        });
    });
});

function notice_warning(warning) {
    var divid = "wp_warning";
    jQuery('html,body').animate({
            scrollTop: jQuery("#" + divid).height},
        'slow');
    document.getElementById('wp_warning').style.display = '';
    document.getElementById('wp_warning').innerHTML = warning;
    jQuery("#wp_warning").fadeOut(7000);
}

function schedule_rightnow(){
    var currentlimit = document.getElementById('currentlimit').value;
    if(parseInt(currentlimit) == 1) {
        jQuery( "#smack_uci_timer_count_up" ).click();
        jQuery( "#smack_uci_timer_start").click();
    }
    igniteImport();
}


function leavePage() {
    var myEvent = window.attachEvent || window.addEventListener;
    var chkevent = window.attachEvent ? 'onbeforeunload' : 'beforeunload'; /// make IE7, IE8 compatable

    myEvent(chkevent, function(e) { // For >=IE7, Chrome, Firefox
        var confirmationMessage = 'edssdds ';  // a space
        (e || window.event).returnValue = confirmationMessage;
        return confirmationMessage;
    });
}

function igniteImport() {
    var config_data = getImportConfiguration(); //import_configuration();
    // When closing browser window alert for stay on page or leave page
    window.onbeforeunload = function(){
         return "Do you want to leave?"
    }
    jQuery(window).unload(function(){
	var currentURL = location.protocol + '//' + location.host + location.pathname + '?page=sm-uci-import';
            window.location = currentURL;
	});
    var eventkey =  document.getElementById('eventkey').value;
    var import_type = document.getElementById('import_type').value;
    var totalcount = document.getElementById('totalcount').value;
    var currentlimit = document.getElementById('currentlimit').value;
    jQuery('#current').html('Current Processing Record: ' + currentlimit);
    var importlimit = document.getElementById('importlimit').value;
    var remaining = parseInt(totalcount) - parseInt(currentlimit);
    jQuery('#remaining').html('Remaining Record: ' + remaining);
    var inserted = document.getElementById('inserted').value;
    var updated = document.getElementById('updated').value;
    var skipped = document.getElementById('skipped').value;
    var limit = document.getElementById('limit').value;
    var total = parseInt(totalcount) + 1;
    var startLimit = currentlimit;
    var endLimit = parseInt(importlimit) + parseInt(currentlimit);
    var main_mode = document.getElementById('main_mode').value;
    var msg1 = null;
    if(main_mode == "on"){
        msg1 = "Maintenance mode enabled";
     }

    var postData = new Array();
    postData = {
        'event_key': eventkey,
        'import_type': import_type,
        'importMethod': 'normalimport',
        //'eventMode': 'Insert',
        'startLimit': startLimit,
        'endLimit': endLimit,
        'Limit' : limit,
        'totalcount':totalcount,
        'inserted': inserted,
        'updated': updated,
        'skipped': skipped,
        'duplicate_headers': config_data['headers'],
    }
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        dataType: 'json',
        data: {
            'action': 'parseDataToImport',
            'postData': postData,
        },
        success: function (response) {
            console.log(response);
            currentlimit = parseInt(currentlimit) + parseInt(importlimit);
            document.getElementById('currentlimit').value = currentlimit;
            document.getElementById('logsection').style.display = "";
            document.getElementById('inserted').value = response.inserted;
            document.getElementById('updated').value = response.updated;
            document.getElementById('skipped').value = response.skipped;
            jQuery('#innerlog').prepend(jQuery(response.eventLog + "<br>"));
            if(currentlimit == 2 && msg1 != null){
              jQuery('#innerlog').append(jQuery("<p style='margin-left:10px;color:green;'>"+msg1+"</p>"));
            }
            if(currentlimit == total) {
               var msg = 'Import Successfully Completed';
                document.getElementById('continue_import').style.display = 'none';
                document.getElementById('new_import').style.display = '';
                document.getElementById('terminate_now').style.display='none';
                jQuery('#innerlog').prepend(jQuery("<p style='margin-left:10px;color:green;'>"+msg+"</p>"));
                jQuery( "#smack_uci_timer_stop").click();
                document.getElementById('dwnld_log_link').style.display = "";
                return false;
            } else {
                if(jQuery('#terminate_action').val() == 'continue') {
                    setTimeout(function () {
                        igniteImport()
                    }, 0);
                } else {
                    jQuery( "#smack_uci_timer_stop").click();
                    return false;
                }
                document.getElementById('dwnld_log_link').style.display = "";
            }
        },
        error: function (errorThrown) {
            console.log(errorThrown);
        }
    });
}

function terminateImport() {
    jQuery( "#smack_uci_timer_stop").click();
    document.getElementById('continue_import').style.display = '';
    document.getElementById('new_import').style.display = '';
    document.getElementById('terminate_now').style.display='none';
    document.getElementById('terminate_action').value = 'terminate';
}

function continueImport() {
    jQuery( "#smack_uci_timer_start").click();
    var tot_no_of_records = document.getElementById('totalcount').value;
    var current_limit = document.getElementById('currentlimit').value;

    if (parseInt(current_limit) > parseInt(tot_no_of_records)) {
        document.getElementById('terminate_now').style.display = "none";
    } else {
        document.getElementById('terminate_now').style.display = "";
    }
    document.getElementById('continue_import').style.display = "none";
    document.getElementById('new_import').style.display = 'none';
    document.getElementById('terminate_action').value = 'continue';

    setTimeout(function () {
        igniteImport()
    }, 0);
}

function show_fileEvents(id) {
    //document.getElementById("file_events"+id).style.display = '';
}

function hide_fileEvents(id) {
    //document.getElementById("file_events"+id).style.display = 'none';
}

function addexportfilter(id) {
    if(document.getElementById(id).checked == true) {
        if(id == 'getdataforspecificperiod') {
            document.getElementById('specificperiodexport').style.display = '';
            //document.getElementById('periodstartfrom').style.display = '';
            document.getElementById('postdatefrom').style.display = '';
            //document.getElementById('periodendto').style.display = '';
            document.getElementById('postdateto').style.display = '';
        }
        else if(id == 'getdatawithspecificstatus') {
            document.getElementById('specificstatusexport').style.display = '';
            //document.getElementById('status').style.display = '';
            //document.getElementById('specific_status').style.display = '';
        }
        else if(id == 'getdatabyspecificauthors') {
            document.getElementById('specificauthorexport').style.display = '';
            //document.getElementById('authors').style.display = '';
            //document.getElementById('specific_authors').style.display = '';
        }
        else if(id == 'getdatabasedonexclusions') {
            document.getElementById('exclusiongrouplist').style.display = '';
        }
        else if(id == 'getdatawithdelimiter'){
            document.getElementById('delimiterstatus').style.display = '';

        }
    } else if (document.getElementById(id).checked == false) {
        if(id == 'getdataforspecificperiod') {
            document.getElementById('specificperiodexport').style.display = 'none';
            //document.getElementById('periodstartfrom').style.display = 'none';
            document.getElementById('postdatefrom').style.display = 'none';
            //document.getElementById('periodendto').style.display = 'none';
            document.getElementById('postdateto').style.display = 'none';
        }
        else if(id == 'getdatawithspecificstatus') {
            document.getElementById('specificstatusexport').style.display = 'none';
            //document.getElementById('status').style.display = 'none';
            document.getElementById('specific_status').style.display = 'none';
        }
        else if(id == 'getdatabyspecificauthors') {
            document.getElementById('specificauthorexport').style.display = 'none';
            //document.getElementById('authors').style.display = 'none';
            document.getElementById('specific_authors').style.display = 'none';
        }
        else if(id == 'getdatabasedonexclusions') {
            document.getElementById('exclusiongrouplist').style.display = 'none';
        }
        else if(id == 'getdatawithdelimiter'){
            document.getElementById('delimiterstatus').style.display = 'none';
        }
    }
}

function export_module() {
    var get_selected_module = document.getElementsByName('export_type');
    var customlist = document.getElementById('export_post_type').value;
    var customtaxonomy = document.getElementById('export_taxo_type').value;
    for (var i = 0, length = get_selected_module.length; i < length; i++) {
        if (get_selected_module[i].checked) {
            // do whatever you want with the checked radio
            // only one radio can be logically checked, don't check the rest
            if(get_selected_module[i].value == 'CustomPosts'){
                if(customlist == '--Select--'){
                    var warning="Please choose any post type from the Custom post list";
                    swal("Warning!", warning, "warning")
                    return false;
                }
            }
            if(get_selected_module[i].value == 'Taxonomies'){
                if(customtaxonomy == '--Select--'){
                    var warning="Please choose any taxonomy from the Custom taxonomy list";
                    swal("Warning!", warning, "warning")
                    return false;
                }
            }
            return true;
        }
    }
    var warning="Please choose one module to export the records!";
    swal("Warning!", warning, "warning")
    return false;
}


function save_template(import_type){
    /*******************************************
     --select-- value row delete function Start
     *******************************************/
    flag = '';
    jQuery('.search_dropdown_mapping').each(function() {
        var selectid = this.id;
        var trdata = selectid.split("s2id_");
        if(typeof(trdata[1]) != 'undefined') {
        } else {
            var mapid = selectid;
            var wpdata = mapid.split("__mapping");
            var wpid = wpdata[0]+'__fieldname'+wpdata[1];
            var trrowid = wpdata[0]+'_tr_count'+wpdata[1];
            //is templateid checkbox features
            var numcheckid = wpdata[0]+'_num_'+wpdata[1];
            var numchecklen = document.getElementById(numcheckid);
            if(numchecklen != null){
                var isnumcheck = document.getElementById(numcheckid).checked;
            }
            //is templateid checkbox features
            var wpvalue = document.getElementById(wpid).value;
            var csvid = wpdata[0]+'__mapping'+wpdata[1];
            var csvvalue = document.getElementById(csvid).value;
            if(wpvalue == 'post_title' && csvvalue == '--select--' && wpid == 'CORE__fieldname'+wpdata[1]) {
                flag = 'post_title';
            } else if(wpvalue == 'user_login' && csvvalue == '--select--' && wpid == 'CORE__fieldname'+wpdata[1]) {
                flag = 'user_login';
            } else if(wpvalue == 'user_email' && csvvalue == '--select--' && wpid == 'CORE__fieldname'+wpdata[1]) {
                flag = 'user_email';
            } else if(wpvalue == 'role' && csvvalue == '--select--' && wpid == 'CORE__fieldname'+wpdata[1]) {
                flag = 'user_role';
            } else if(wpvalue == 'name' && csvvalue == '--select--' && wpid == 'CORE__fieldname'+wpdata[1]) {
                flag = 'name';
            } else if(wpvalue == 'page_id' && csvvalue == '--select--' && wpid == 'CORE__fieldname'+wpdata[1]) {
                flag = 'page_id';
            } else {
                //is templateid checkbox features
                if( numchecklen != null && isnumcheck != true && csvvalue != '--select--') {
                    jQuery('#'+csvid).attr("disabled", "disabled");
                    jQuery('#'+wpid).attr("disabled", "disabled");
                    //is templateid checkbox features
                } else if(csvvalue == '--select--') {
                    jQuery('#'+trrowid).remove();
                }
            }
        }
    });
    if(flag != '') {
        var warning = flag+' is Required. Please map the fields to proceed.';
        swal("Warning!", warning, "warning")
        return false;
    }
    //reorder the tr td select attr id name
    var group = new Array("core_fields", "wordpress_custom_fields","acf_fields", "acf_pro_fields","acf_repeater_fields","cctm_custom_fields","types_custom_fields","pods_custom_fields","all-in-one_seo_fields","yoast_seo_fields","wp_e-commerce_custom_fields","custom_fields_by_wp-members","billing_and_shipping_information","terms_and_taxonomies");
    for(var i = 0; i <= group.length; i++) {
        jQuery('#'+group[i]+'_table tr').each(function(index, row) {
            if(row.id != '' || row.id != null){
                //_count hidden value assign start
                var no = 0 + index;
                document.getElementById(group[i] + '_count').value = no;
                //_count hidden value assign end
                var tdata = row.id.split("_tr_count");
                var index = index - 1;
                jQuery('#'+tdata[0]+'_tr_count'+tdata[1]).attr('id', tdata[0]+'_tr_count'+index);
                jQuery('#'+tdata[0]+'_tdc_count'+tdata[1]).attr('id', tdata[0]+'_tdc_count'+index);
                jQuery('#'+tdata[0]+'_tdg_count'+tdata[1]).attr('id', tdata[0]+'_tdg_count'+index);
                jQuery('#'+tdata[0]+'__fieldname'+tdata[1]).attr('id', tdata[0]+'__fieldname'+index);
                jQuery('#'+tdata[0]+'__fieldname'+tdata[1]).attr('name', tdata[0]+'__fieldname'+index);
                jQuery('#'+tdata[0]+'__mapping'+tdata[1]).attr('id', tdata[0]+'__mapping'+index);
                jQuery('#'+tdata[0]+'__mapping'+tdata[1]).attr('name', tdata[0]+'__mapping'+index);
                jQuery('#'+tdata[0]+'_staticbutton_mapping'+tdata[1]).attr('id', tdata[0]+'_staticbutton_mapping'+index);
                jQuery('#'+tdata[0]+'_formulabutton_mapping'+tdata[1]).attr('id', tdata[0]+'_formulabutton_mapping'+index);
                jQuery('#'+tdata[0]+'_customdispdiv_mapping'+tdata[1]).attr('id', tdata[0]+'_customdispdiv_mapping'+index);
            }
        });
    }
    /*******************************************
     --select-- value row delete function End
     *******************************************/
    var template_option = jQuery('input[name = template]:checked').val();
    /*var validate_flag = CheckRequiredCF(import_type);
     if(validate_flag == 1)
     return false;*/

    if(template_option == 'auto_save') {
        var template_name = jQuery('#templatename').val();
        if(template_name != '') {
            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                async: false,
                data: {
                    'action': 'check_templatename',
                    'templatename': template_name,
                },
                success: function (data) {
                    if (data != 0) {
                        jQuery('#templatename').val('');
                        var warning = "Template name already exists, Please enter another name";
                        swal("Warning!", warning, "warning")
                        return false;
                    }
                    jQuery('#mapping_section').submit();
                },
                error: function (errorThrown) {
                    console.log(errorThrown);
                }
            });
        } else {
            var warning = "Please enter the template name";
            swal("Warning!", warning, "warning")
        }
    } else {
        jQuery('#mapping_section').submit();
        return true;
    }
    return false;
}

// function to check all in mapping section

function select_All(id,groupname,tableid)
{
    var tid = tableid+'_table';
    var count = jQuery('#'+tid+ '> tbody > tr').length;
    var trcount = count - 1;
    //var count = document.getElementById(groupname+'_count').value;
    for(var i=0;i<trcount;i++) {
        var check = document.getElementById('id'+groupname).checked;
        if(check == true ) {
            document.getElementById(groupname+'_num_' + i).checked = true;
        }
        else {
            document.getElementById(groupname+'_num_' + i).checked = false;
        }
    }
}

/** Validate the mandatory custom fields in mapping section **/
function CheckRequiredCF(import_type) {
    var required_fields;
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        async: false,
        data: {
            'action': 'check_CFRequiredFields',
            'import_type': import_type,
        },
        success: function (data) {
            required_fields = JSON.parse(data);
        },
        error: function (errorThrown) {
            console.log(errorThrown);
        }
    });
    var validate_flag = Validate_Fields(required_fields);
    return validate_flag;
}

/** Findout the mandatory field in mapping section and alert the user **/
function Validate_Fields(required_fields) {
    var headers = document.getElementsByClassName('wpfields');
    var i,required_flag = 0;
    var widget_slug =['core_fields','wordpress_custom_fields','acf_fields','types_custom_fields','pods_custom_fields','terms_and_taxonomies'];
    var prefix = ['CORE','CORECUSTFIELDS','ACF','TYPES','PODS','TERMS'];
    var required_fieldInfo = '';
    for(i = 0;i<widget_slug.length;i++) {
        var core_count = document.getElementById(widget_slug[i] + '_count');
        if(core_count != null) {
            core_count = core_count.value;
        }
        var j;
        for(j = 0;j<core_count;j++) {
            field_name = document.getElementById(prefix[i] + '__fieldname' + j).value;
            mapping_data = document.getElementById(prefix[i] + '__mapping' + j);
            mapping_data = mapping_data[0].value;
            for(k = 0;k<required_fields.length;k++) {
                if(required_fields[k] == field_name && mapping_data == '--select--') {
                    if(required_fieldInfo == '') {
                        required_fieldInfo = field_name;
                    }
                    else {
                        required_fieldInfo +=  ',' + field_name;
                    }
                    required_flag = 1;
                }
            }
        }
    }
    if(required_fieldInfo != '')
    //alert(required_fieldInfo + ' is required field. Please map the data');
        swal('Warning!', required_fieldInfo + ' is manadatory field. Please map that before proceed your import.', 'warning')
    return required_flag;
}


function dwn_external_img(id,name) {
    var dwnimg_val = 'disable';
    if(name== 'download_extimg' && id == 'download_extimg') {
        var dwnimg_val = 'enable';
        jQuery('#dwn_externalimg_label').removeClass("not_dwn_externalimg");
        jQuery('#dwn_externalimg_label').addClass("dwn_externalimg");
        jQuery('#not_dwn_externalimg_label').addClass("not_dwn_externalimg");
        jQuery('#not_dwn_externalimg_label').removeClass("dwn_externalimg");
    }
    else if(name== 'download_extimg' && id == '') {
        var dwnimg_val = 'disable';
        jQuery('#not_dwn_externalimg_label').removeClass("not_dwn_externalimg");
        jQuery('#not_dwn_externalimg_label').addClass("dwn_externalimg");
        jQuery('#dwn_externalimg_label').addClass("not_dwn_externalimg");
        jQuery('#dwn_externalimg_label').removeClass("dwn_externalimg");
    }
}

function displayselect(id){
    if(id == 'img_namechange'){
        document.getElementById("renamevalue").disabled = false;
    }
    else {
        document.getElementById("renamevalue").disabled = true;
    }
}

function filezipopen()
{
    var advancemedia = document.getElementById('advance_media_handling').checked;
    if(advancemedia == true)
        document.getElementById('filezipup').style.display = '';
    else
        document.getElementById('filezipup').style.display = 'none';

}

function Inline_upload(filename) {
    var allowedextension = {'.zip': 1};
    var match = /\..+$/;
    var ext = filename.match(match);
    if (allowedextension[ext]) {
        return true;
    }
    else {
        document.getElementById('inlineimages').value="";
        var warning = 'Please Upload Zip Files';
        swal("Warning!", warning, "warning")
        return false;
    }
    var eventkey = document.getElementById('eventkey').value;
    var form_data = new FormData();
    var filesArray = jQuery('#inlineimages').prop('files')[0];
    form_data.append('file', filesArray);
    form_data.append('action','inlineimage_upload');
    form_data.append('eventkey', eventkey);
    jQuery.ajax({
//                      url: url, // point to server-side PHP script
        url: ajaxurl,
        // dataType: 'text',  // what to expect back from the PHP script, if anything
        cache: false,
        contentType: false,
        processData: false,
        data: form_data,
        type: 'post',
        success: function(response){

            if(response == 'Uploaded file size exceeds the MAX Size in php.ini') {
                var warning = 'Error Cannot upload';
                swal("Warning!", warning, "warning")
                //document.getElementById('ajaximage').style.display = 'none';
            } else {
                document.getElementById('extn_label').style.backgroundColor = "#f5f5f5";
                document.getElementById('extn_label').innerHTML = 'Upload Completed';
                document.getElementById('extn_label').style.textAlign = 'center';
                //document.getElementById('ajaximage').style.display = 'none';
            }
        },error: function (errorThrown) {
            console.log(errorThrown);
        }
    });
}

function get_wpimg_size(id){
    if(id == 'suggested_size'){
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action': 'get_mediaimg_size',
            },
            success: function (data) {
                data = JSON.parse(data);
                jQuery('#disp_thumbnail_w').val(data['thumbnail']['width']);
                jQuery('#disp_thumbnail_h').val(data['thumbnail']['height']);
                jQuery('#disp_medium_w').val(data['medium']['width']);
                jQuery('#disp_medium_h').val(data['medium']['height']);
                jQuery('#disp_large_w').val(data['large']['width']);
                jQuery('#disp_large_h').val(data['large']['height']);
            },
            error: function (errorThrown) {
                console.log(errorThrown);
            }
        });
    }
    else{
        document.getElementById("disp_thumbnail_w").value="";
        document.getElementById("disp_thumbnail_h").value="";
        document.getElementById("disp_medium_w").value="";
        document.getElementById("disp_medium_h").value="";
        document.getElementById("disp_large_w").value="";
        document.getElementById("disp_large_h").value="";
    }
}

/** Dynamic row for Templates **/
function generate_row(templatelist){
    set_widgetheight(templatelist.length);
    var template_table = document.getElementById("templates");
    for(var i=0;i<templatelist.length;i++){
        if(typeof templatelist[i]['rowcount'] !== 'undefined') {
            var table_row_count = jQuery('#templates tr').length;
            var template_row_count = parseInt(templatelist[i]['rowcount']);
            jQuery('#template_row_count').val(template_row_count);
            if(table_row_count >= template_row_count){
                return false;
            }
        }
        var template_row = template_table.insertRow(-1);
        //var template_id = template_row.insertCell(0);
        var template_name = template_row.insertCell(0);
        //var template_file = template_row.insertCell(2);
        var template_module = template_row.insertCell(1);
        var template_date = template_row.insertCell(2);
        var template_action = template_row.insertCell(3);

        //template_id.innerHTML = templatelist[i]['id'];
        template_name.innerHTML = templatelist[i]['name'];
        //template_file.innerHTML = templatelist[i]['file'];
        template_module.innerHTML = templatelist[i]['module'];
        template_date.innerHTML = templatelist[i]['createdat'];
        template_action.innerHTML = templatelist[i]['use_template'];

        //template_id.className = "template_id";
        template_name.className = "template_name";
        //template_file.className = "template_file";
        template_module.className = "template_module";
        template_date.className = "template_date";
        template_action.className = "template_button";
    }
}

/** Set the height based on Record count in suggested template page **/
function set_widgetheight(count) {
    if(count === undefined) {
        count = jQuery('#templates tr').length;
    }
    table_row_count = jQuery('#templates tr').length;
    if(table_row_count >=5) {
        jQuery('#scroll_template').height('250');
        return true;
    }
    if(count <= 0) {
        var empty_template = "<p class = 'empty_template'>No Templates Found</p>";
        jQuery('#scroll_template').html(empty_template);
        jQuery('#scroll_template').height('70');
        return false;
    }
    else {
        if(document.getElementById('templates') == null) {
            var template_table = document.createElement('table');
            var parent_div = document.getElementById('scroll_template');
            template_table.setAttribute('id','templates');
            template_table.className = "table table-striped";
            parent_div.appendChild(template_table);
            jQuery('.empty_template').remove();
        }
    }
    if(count <= 5) {
        var initial_height = 50;
        jQuery('#scroll_template').height(initial_height * count);
    }
    else {
        jQuery('#scroll_template').height('250');
    }
}

jQuery(function(){
    jQuery('#duplicate').on('ifChecked', function(){
	jQuery('#duplicate_headers').show();
        document.getElementById('duplicate_conditions').disabled = false;
        jQuery('.selectpicker').selectpicker('refresh');
    });

    jQuery('#duplicate').on('ifUnchecked', function(){
	jQuery('#duplicate_headers').hide();
        jQuery('.selectpicker').selectpicker('refresh');
    });
});

jQuery(function(){
    jQuery('#schedule').on('ifChecked', function(){
        swal('Warning!', 'Please upgrade to PRO for the scheduling this event.', 'warning')
    });
    jQuery('#checkbox1').on('ifChecked', function(){
        swal('Warning!', 'Please upgrade to PRO for the scheduling this event.', 'warning')
    });
  jQuery('#schedule').on('ifUnchecked', function(){
       swal('Warning!', 'Please upgrade to PRO for the scheduling this event.', 'warning')
    });

jQuery('#use_existing_images').on('ifChecked', function(){

        swal('Warning!', 'Please upgrade to PRO for advanced media handling.', 'warning')
    });
jQuery('#existing_items').on('ifChecked', function(){
    console.log('ds');
        swal('Warning!', 'Please upgrade to PRO for advanced media handling.', 'warning')
    });
jQuery('#overwrite_existing_images').on('ifChecked', function(){
        swal('Warning!', 'Please upgrade to PRO for advanced media handling.', 'warning')
    });
/* media sizes */
jQuery('#thumbnail_size').on('ifChecked', function(){
        swal('Warning!', 'Please upgrade to PRO for advanced media handling.', 'warning')
    });
    jQuery('#medium_size').on('ifChecked', function(){
        swal('Warning!', 'Please upgrade to PRO.', 'warning')
    });
    jQuery('#medium_size').on('ifChecked', function(){
        swal('Warning!', 'Please upgrade to PRO.', 'warning')
    });
    jQuery('#medium_large_size').on('ifChecked', function(){
        swal('Warning!', 'Please upgrade to PRO for advanced media handling.', 'warning')
    });
    jQuery('#large_size').on('ifChecked', function(){
        swal('Warning!', 'Please upgrade to PRO for advanced media handling.', 'warning')
    });
    jQuery('#media_seo_title').on('ifChecked', function(){
        swal('Warning!', 'Please upgrade to PRO for advanced media handling.', 'warning')
    });
    jQuery('#media_seo_caption').on('ifChecked', function(){
        swal('Warning!', 'Please upgrade to PRO for advanced media handling.', 'warning')
    });
    jQuery('#media_seo_alttext').on('ifChecked', function(){
        swal('Warning!', 'Please upgrade to PRO for advanced media handling.', 'warning')
    });
    jQuery('#media_seo_description').on('ifChecked', function(){
        swal('Warning!', 'Please upgrade to PRO for advanced media handling.', 'warning')
    });
    jQuery('#change_media_file_name').on('ifChecked', function(){
        swal('Warning!', 'Please upgrade to PRO for advanced media handling.', 'warning')
    });

});

function toggle_configdetails(id) {
    if(id == 'duplicate'){
        if(jQuery('#'+id).is(':checked')){
            jQuery('#duplicate_headers').show();
            document.getElementById('duplicate_conditions').disabled = false;
        }
        else
            jQuery('#duplicate_headers').hide();
    }
    else if(id == 'import_specific'){
        if(jQuery('#'+id).is(':checked')){
            jQuery('#specific_records').show();
        }
        else
            jQuery('#specific_records').hide();
    }
    else if(id == 'serverlimit'){
        if(jQuery('#'+id).is(':checked')){
            jQuery('#record_limits').show();
        }
        else
            jQuery('#record_limits').hide();
    }
    else if(id == 'schedule'){
        if(jQuery('#'+id).is(':checked')){
            //jQuery('#schedule_import').show();
            swal('Warning!', 'Please upgrade to PRO for the scheduling this event.', 'warning')
            jQuery('#ignite_import').fadeOut('slow', function(){
                jQuery('#schedule_import_btn').fadeIn();
            });
        }
        else{
            jQuery('#schedule_import').hide();
            jQuery('#schedule_import_btn').hide();
            jQuery('#ignite_import').show();
        }
    }
}

function show_advance_options(){
    jQuery('#display_serverlimit').slideToggle(2000);
    jQuery('#display_specificrecords').slideToggle(2000);
}

function show_custom_sizes(){
    jQuery('#custom_width').slideToggle(1000);
    jQuery('#custom_height').slideToggle(1000);
}


function getImportConfiguration () {
    var headers = offset = limit = '';
    if(jQuery("#duplicate").is(':checked')) {
        headers = jQuery("#duplicate_headers option:selected").text();
        //duplicate_headers = get_headerdata(headers);
    }
    if(jQuery("#import_specific").is(':checked')) {
        var offset = jQuery("#import_specific_records").val();
    }
    if(jQuery("#serverlimit").is(':checked')) {
        var limit = jQuery('#limit option:selected').text();
    }
    var configData = {
        'headers' : headers,
        'offset' : offset,
        'limit' : limit,
    }
    return configData;
}

function get_headerdata(headers) {
    var eventkey =  document.getElementById('eventkey').value;
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        async: false,
        data: {
            'action': 'get_headerData',
            'eventkey': eventkey,
            'headers': headers,
        },
        success: function (data) {
            data = JSON.parse(data);
            return data;
        },
        error: function (errorThrown) {
            console.log(errorThrown);
        }
    });
}

// Validation for specific records
function enableDisableImportButton(){
    var check = false;
    var importlimit = jQuery("#import_specific_records").val();
    var numbers = /^[0-9]+\-[0-9]+/;
    if (importlimit != '' && importlimit != 0 && importlimit.match(numbers))
        check = true;
    var import_specific_records = jQuery("#import_specific_records").val();
}

function igniteExport() {
    var exclusion_header_list = JSON.parse( "" || "{}");
    var filterOptions = new Array('getdatawithdelimiter', 'getdataforspecificperiod', 'getdatawithspecificstatus', 'getdatabyspecificauthors', 'getdatabasedonexclusions');
    var items = jQuery("form :input").map(function(index, elm) {
        return {id: elm.id, name: elm.name, type:elm.type, value: jQuery(elm).val()};
    });
    jQuery.each(items, function(i, d){
        if(d.name != '' && d.name != null && d.name != '_token' && d.type == 'checkbox') {
            if(jQuery.inArray(d.name, filterOptions) == -1) {
                if (jQuery('#' + d.id).prop( "checked" )) {
                    exclusion_header_list[d.name] = true; //d.type;
                }
            }
        }
    });
    console.log(exclusion_header_list);
    var module = jQuery('#moduletobeexport').val();
    var is_custom_delimiter = false;
    if(jQuery('#getdatawithdelimiter').prop( "checked" )) {
        is_custom_delimiter = true;
    }
    var delimiter = jQuery('#postwithdelimiter').val();
    var optional_delimiter = jQuery('#other_delimiter').val();
    var optionalType = jQuery('#optional_type').val();
    var offset = jQuery('#offset').val();
    var limit = jQuery('#limit').val();
    var total_row_count = jQuery('#total_row_count').val();
    var is_data_for_specific_period = false;
    if(jQuery('#getdataforspecificperiod').prop( "checked" )) {
        is_data_for_specific_period = true;
    }
    var from_date = jQuery('#postdatefrom').val();
    var to_date = jQuery('#postdateto').val();
    var is_data_for_specific_status = false;
    if(jQuery('#getdatawithspecificstatus').prop( "checked" )) {
        is_data_for_specific_status = true;
    }
    var specific_status = jQuery('#specific_status').val();
    var is_data_for_specific_authors = false;
    if(jQuery('#getdatabyspecificauthors').prop( "checked" )) {
        is_data_for_specific_authors = true;
    }
    var specific_authors = jQuery('#specific_authors').val();
    var is_data_with_specific_exclusions = false;
    if(jQuery('#getdatabasedonexclusions').prop( "checked" )) {
        is_data_with_specific_exclusions = true;
    }
    var conditions = {
        'delimiter': {
            'is_check': is_custom_delimiter,
            'delimiter': delimiter,
            'optional_delimiter': optional_delimiter,
        },
        'specific_period': {
            'is_check': is_data_for_specific_period,
            'from': from_date,
            'to': to_date,
        },
        'specific_status': {
            'is_check': is_data_for_specific_status,
            'status': specific_status,
        },
        'specific_authors': {
            'is_check': is_data_for_specific_authors,
            'author': specific_authors,
        },
    };
    var eventExclusions = {
        'is_check': is_data_with_specific_exclusions,
        'exclusion_headers': exclusion_header_list,
    };
    var fileName = jQuery('#export_filename').val();
    if( fileName == '') {
        var warning = "Please Enter Filename";
        swal("Warning!", warning, "warning");
        return false;
    }
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        dataType: "json",
        //async: false,
        data: {
            'action': 'parseDataToExport',
            'module': module,
            'optionalType': optionalType,
            'conditions': conditions,
            'eventExclusions': eventExclusions,
            'fileName': fileName,
            'offset': offset,
            'limit': limit,
        },
        success: function (response) {
            //var new_offset = parseInt(data.offset) + parseInt(data.limit);
            if(response != null) {
                jQuery('input[type="button"]').prop('disabled', true);
                jQuery("a#download_file_link").css('display', '');
                jQuery("#download_file").css('display', '');
                jQuery('#download_file').prop('disabled', false);
                jQuery("a#download_file_link").attr("href", response.exported_file);
                jQuery('#offset').val(response.new_offset);
                if (parseInt(response.total_row_count) < parseInt(response.new_offset)) {
                    jQuery('#wpwrap').waitMe('hide');
                    jQuery("html, body").animate({ scrollTop: 0 }, "slow");
                    return false;
                }
                igniteExport();
            }
            console.log (response);
        },
        error: function (errorThrown) {
            console.log(errorThrown);
        }
    });
}


function shownotification(msg, alerts) {
    var newclass;
    var divid = "notification_wp_csv";
    document.getElementById('notification_wp_csv').style.display = '';
    var height = jQuery(document).height() - jQuery(window).height();
    if (alerts == 'success')
        newclass = "alert alert-success";
    else if (alerts == 'danger')
        newclass = "alert alert-danger";
    else if (alerts == 'warning')
        newclass = "alert alert-warning";
    else
        newclass = "alert alert-info";

    jQuery('#' + divid).removeClass()
    jQuery('#' + divid).html(msg);
    jQuery('#' + divid).addClass(newclass);
    // Scroll
    jQuery('html,body').animate({
            scrollTop: jQuery("#" + divid).height},
        'slow');
    jQuery("#notification_wp_csv").fadeOut(7000);
}




function download_log(id) {
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        async: false,
        data: {
            'action': 'downloadLog',
            'id': id,
        },
        success: function (data) {
            data = JSON.parse(data);
            swal('Warning!', data['notice'], 'warning')
        },
        error: function (errorThrown) {
            console.log(errorThrown);
        }
    });
}


//Static Method CSV Headers value Concatenation
function static_method(id, prefix, mappingcount, staticheader){
    var group    = prefix;
    var buttonid = id;
    var divid = group+'_statictext_mapping'+mappingcount;
    document.getElementById(group+'_formulabutton_mapping'+mappingcount).disabled = true;
    mappingvalue = document.getElementById(group+'__mapping'+mappingcount).value;
    if(mappingvalue == 'header_manip'){
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action': 'static_formula_method_handler',
                'group': group,
                'mappingcount' : mappingcount,
                'buttonid' : buttonid,
            },
            success: function (data) {
                document.getElementById(group+'_customdispdiv_mapping'+mappingcount).innerHTML = data;
                jQuery('#'+group+'_customdispdiv_mapping'+mappingcount).slideDown(400).find("textarea").focus();
                if(staticheader != '' && staticheader != null){
                    document.getElementById(divid).value = staticheader;
                }
            },
            error: function (errorThrown) {
                console.log(errorThrown);
            }
        });
    }else{
        var warning = 'Choose Header Manipulation in the Dropdown';
        swal("Warning!", warning, "warning")
    }
}
//Formula Method CSV Headers value Calculation
function formula_method(id, prefix, mappingcount, formulaheader){
    var group    = prefix;
    var buttonid = id;
    var divid = group+'_formulatext_mapping'+mappingcount;
    document.getElementById(group+'_staticbutton_mapping'+mappingcount).disabled = true;
    mappingvalue = document.getElementById(group+'__mapping'+mappingcount).value;
    if(mappingvalue == 'header_manip'){
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action': 'static_formula_method_handler',
                'group': group,
                'mappingcount' : mappingcount,
                'buttonid' : buttonid,
            },
            success: function (data) {
                document.getElementById(group+'_customdispdiv_mapping'+mappingcount).innerHTML = data;
                jQuery('#'+group+'_customdispdiv_mapping'+mappingcount).slideDown(400).find("textarea").focus();
                if(formulaheader != '' && formulaheader != null){
                    document.getElementById(divid).value = formulaheader;
                }
            },
            error: function (errorThrown) {
                console.log(errorThrown);
            }
        });
    }else{
        var warning = 'Choose Header Manipulation in the Dropdown';
        swal("Warning!", warning, "warning")
    }
}

function static_formula_divclose(group, mapping) {
    jQuery('#'+group+'_customdispdiv_mapping'+mapping).hide(400);
}

function static_validator(id) {
    var val = jQuery('#'+id).val();
    var check = /{([0-9 a-z _]+)}([0-9 a-z _]*)([\+ \- \* \/]*)/;
    if(val.match(check)) {
        document.getElementById(id).style.width= '100%';
        document.getElementById(id).style.border= '3px solid #33CC33';
    }
    else {
        document.getElementById(id).style.width= '100%';
        document.getElementById(id).style.border= '3px solid #FF0000';
    }
}
function formula_validator(id) {
    var val = jQuery('#'+id).val();
    var check = /{([0-9 a-z _]+)}([^a-z 0-9])([\+ \- \* \/]*)/;
    if(val.match(check)) {
        document.getElementById(id).style.width= '100%';
        document.getElementById(id).style.border= '3px solid #33CC33';
    }
    else {
        document.getElementById(id).style.width= '100%';
        document.getElementById(id).style.border= '3px solid #FF0000';
    }
}
//Form view settings division selection
function settings_div_selection(id) {
    for(var i=1;i<=4;i++) {
        if(parseInt(id) == parseInt(i)) {
            set_importMethod(id);
            if(parseInt(i) == 1) {
                document.getElementById('left_sidebar').style.height = '300px';
                document.getElementById('rightside_content').style.height = '100%';
                document.getElementById('import_holder').style.height = '100%';
                //document.getElementById('displaysection').style.display="none";
                //document.getElementById('upload_file').value="";
            }else if( parseInt(i) == 2){
                //document.getElementById('left_sidebar').style.height = '500px';
                document.getElementById('rightside_content').style.height = '400px';
                document.getElementById('import_holder').style.height = '400px';
            }else if( parseInt(i) == 3){
                // document.getElementById('left_sidebar').style.height = '1168px';
                document.getElementById('rightside_content').style.height = '100%';
                document.getElementById('import_holder').style.height = '100%';
            }else if( parseInt(i) == 4){
                // document.getElementById('left_sidebar').style.height = '665px';
                document.getElementById('rightside_content').style.height = '665px';
                document.getElementById('import_holder').style.height = '665px';
            }
            jQuery('#'+id).removeClass( "bg-leftside" );
            jQuery('#'+id).addClass( "selected" );
            jQuery('#'+id).addClass( "right-arrow" );
            document.getElementById('division'+id).style.display="";
        }else{
            jQuery('#'+i).removeClass( "selected" );
            jQuery('#'+i).removeClass( "right-arrow" );
            jQuery('#'+i).addClass( "bg-leftside" );
            document.getElementById('division'+i).style.display="none";

        }
    }
}
//options_savein_ajax
function saveoptions(id,name){
//alert(id+name);
    if(id!='main_check_rollback' && id!='main_check_import_off' && id!='main_check_import_on'){
        var value =  document.getElementById(id).checked;
        if(value == true)
            value = 'on';
        else
            value = 'off';
    }
    //cmb2_customization
    if(name == 'cmb2') {
        value = document.getElementById(id).value;
    }
    if (name == 'main_mode_text') {
        value = document.getElementById(id).value;
    }

    if(id=='main_mode_config')
        name = 'enable_main_mode';

    if(id=='rollback_mode_config')
        name = 'rollback_mode';

    if(id == 'main_check_import_off'){
        name = 'enable_main_mode';
        value = 'off';
    }

    if(id == 'main_check_import_on'){
        name = 'enable_main_mode';
        value = 'on';
    } 

    if(id=='main_check_rollback'){
        name = 'rollback_mode';
        value = '';
    }
//alert(name+value);

    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            'action': 'options_savein_ajax',
            'option': name,
            'value': value,
        },
        success: function (response) {
            if( id != 'main_check_import_on'  && id != 'main_check_rollback' && id != 'image-handling-btn' && id != 'gallery-support-btn'){
                if(name == 'enable_main_mode' || id == 'main_check_import_off' || id == 'rollback_mode_config')
                    window.location.reload();
                else
                    swal('Success!', 'Settings successfully updated.', 'success')
            }
            // if (id == 'main_check_import') {
            //     swal('Success!', 'Maintenance mode disabled.', 'success')
            // }
            // if (id == 'main_check_rollback') {
            //     swal('Success!', 'Roll back mode disabled.', 'success')
            // }
        }
    });

}

function pro_feature() {
    swal('Warning!', 'This feature is available only in PRO.', 'warning')
}



// Enable / Disable Media SEO Options
function enable_media_seo_headers(key) {
    if(jQuery('#img_seo_mapping_for_'+key).is(':disabled')) {
        jQuery('#img_seo_mapping_for_' + key).prop('disabled', false);
    } else {
        jQuery('#img_seo_mapping_for_' + key).prop('disabled', true);
    }
}

function enable_mapping_fields(prefix, row_no, id) {
    var selected_option = jQuery('#'+ id + ' option:selected').val();
    jQuery('#' + prefix + "_num_" + row_no).iCheck('uncheck');
    if(selected_option != '--select--')
        jQuery('#' + prefix + "_num_" + row_no).iCheck('check');
    if(selected_option == '--select--')
        jQuery('#' + prefix + "_num_" + row_no).iCheck('uncheck');
}

jQuery(function () {
    jQuery("#image-handling-btn").click(function () {
        if (jQuery(this).is(":checked")) {
            jQuery("#image-handling-btn-opt").slideDown();
        } else {
            jQuery("#image-handling-btn-opt").slideUp();
        }
    });
});

/* Export data with auto delimiters */
jQuery(function(){
    jQuery('#getdatawithdelimiter').on('ifChecked', function(){
        jQuery('#delimiterstatus').show();
    });

    jQuery('#getdatawithdelimiter').on('ifUnchecked', function(){
        jQuery('#delimiterstatus').hide();
    });
});

/* Export data for the specific period */
jQuery(function(){
    jQuery('#getdataforspecificperiod').on('ifChecked', function(){
        jQuery('#specificperiodexport').show();
    });

    jQuery('#getdataforspecificperiod').on('ifUnchecked', function(){
        jQuery('#specificperiodexport').hide();
    });
});

/* Export data with the specific status */
jQuery(function(){
    jQuery('#getdatawithspecificstatus').on('ifChecked', function(){
        jQuery('#specificstatusexport').show();
    });

    jQuery('#getdatawithspecificstatus').on('ifUnchecked', function(){
        jQuery('#specificstatusexport').hide();
    });
});

/*  Export data by specific authors */
jQuery(function(){
    jQuery('#getdatabyspecificauthors').on('ifChecked', function(){
        jQuery('#specificauthorexport').show();
    });

    jQuery('#getdatabyspecificauthors').on('ifUnchecked', function(){
        jQuery('#specificauthorexport').hide();
    });
});

/*  Export data based on specific exclusions */

jQuery(function(){
    jQuery('#getdatabasedonexclusions').on('ifChecked', function(){
        jQuery('#exclusiongrouplist').show();
    });

    jQuery('#getdatabasedonexclusions').on('ifUnchecked', function(){
        jQuery('#exclusiongrouplist').hide();
    });
});

/* add custom field */

jQuery(function () {
    jQuery("#custom-size-add").bind("click", function () {
        var div = jQuery("<tr />");
	div.html(GetDynamicTextBox(""));
        jQuery("#TextBoxContainer").append(div);
    });
    jQuery("body").on("click", ".remove", function () {
        jQuery(this).closest("tr").remove();
    });
});


function send_support_email() {
    document.getElementById('loading-image').style.display = "block";
    var email = document.getElementById('email').value;
    if(email == '') {
	swal('Warning!', 'Please Enter your email' , 'warning')
	document.getElementById('loading-image').style.display = "none";
	return false; }
    var query = document.getElementById('query').value;
    var message = document.getElementById('message').value;
    if(message == '') {
	swal('Warning!', 'Please Enter your query', 'warning') 
	document.getElementById('loading-image').style.display = "none";
        return false; }
     jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            'action': 'sendmail',
            'email': email,
            'query': query,
            'message': message
        },
        success: function (response) {
	        document.getElementById('loading-image').style.display = "none";
            if(response == "Please draft a mail to support@smackcoders.com. If you doesn't get any acknowledgement within an hour!") {
                swal('Warning!', response, 'warning')
            } else {
                swal('Success!', 'Thanks for submitting the query.', 'success')
            }
        }
    });

}
jQuery(function(){
    //Sliding Textbox option for radio Switch
    jQuery('.wp_ultimate_slide').on('ifChecked', function(){
        var key = jQuery(this).data("key");
        var source = jQuery('.source-' + jQuery(this).attr('id'));
        if(key == true){
            source.slideDown();
        } else if (key == false) {
            jQuery(this).parents('.wp_ultimate_container').find('.set_from_csv').slideUp();
        }
    });

    jQuery('.wp_ultimate_slide').on('ifChecked', function(){
        var key_select = jQuery(this).data("select");
        var select_source = jQuery('.select-' + jQuery(this).attr('id'));
        if(key_select == true){
            select_source.slideDown();
        } else if (key_select == false) {
            jQuery(this).parents('.wp_ultimate_container').find('.slide_select').slideUp();
        }
    });

    // toggle SlideUp/SlideDown Textbox
    jQuery('.wp_ultimate_toggle').on('ifChecked', function(){
        jQuery(this).parents('.wp_ultimate_toggle_container').find('.wp_ultimate_toggle_target').slideDown();
    });
    jQuery('.wp_ultimate_toggle').on('ifUnchecked', function(){
        jQuery(this).parents('.wp_ultimate_toggle_container').find('.wp_ultimate_toggle_target').slideUp();
    });
});


jQuery(window).scroll(function () {
    var threshold = 100;
    //jQuery("#test").html(jQuery(window).scrollTop());
    if (jQuery(window).scrollTop() >= threshold)
        jQuery('#mapping-sidebar').addClass('sidebar-fixed');
    else
        jQuery('#mapping-sidebar').removeClass('sidebar-fixed');
    var check = jQuery("#mapping-content").height() - jQuery("#mapping-sidebar").height()-21;
    if (jQuery(window).scrollTop() >= check){
        jQuery('#mapping-sidebar').addClass('bottom');
    }
    else{
        jQuery('#mapping-sidebar').removeClass('bottom');
    }


    if (jQuery(document).scrollTop() >  50){
        jQuery('.mapping-sidebar-content-section').css({'max-height': (jQuery(window).height() - 147) + 'px' });
    }
    else{
        jQuery('.mapping-sidebar-content-section').css({'max-height': (jQuery(window).height() - 220) + 'px' });
    }

    // jQuery(document).ready(function() {
    //   function setHeight() {
    //     windowHeight = jQuery(window).innerHeight()-500;
    //     jQuery('.mapping-sidebar-content-section').css('max-height', windowHeight);
    //   };
    //   setHeight();

    //   jQuery(window).resize(function() {
    //     //setHeight();
    //   });
    // });
});

function send_subscribe_email() {
    document.getElementById('loading-img-subs').style.display = "block";
    var email = document.getElementById('subscribe_email').value;
    if(email == '') {
	swal('Warning!', 'Please Enter your email', 'warning')
        document.getElementById('loading-img-subs').style.display = "none";
        return false; }
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            'action': 'send_subscribe_email',
            'subscribe_email': email,
        },
        success: function (response) {
            document.getElementById('loading-img-subs').style.display = "none";
            if(response == "Please draft a mail to support@smackcoders.com. If you doesn't get any acknowledgement within an hour!") {
                swal('Warning!', response, 'warning')
            } else {
                swal('Success!', 'Thanks for Subscribing.', 'success')
            }
        }
    });
}

function GetDynamicTextBox(value) {
	swal('Warning!', 'Please Upgrade the PRO for Advance Media Handling.', 'warning')
}

function reload_to_new_import() {
    var currentURL = location.protocol + '//' + location.host + location.pathname + '?page=sm-uci-import';
    window.location = currentURL;
}
jQuery(function(){
    jQuery(".mapping-select-div").click(function(){
        jQuery(".mapping-select-close-div").hide();
    });

    jQuery(".mapping-static-formula-group").click(function(){
        jQuery(".mapping-select-close-div").hide();
    });
});

function dismiss_notices(type) {
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            'action': 'dismiss_notices',
            'notice': type,
        },
        success: function (response) {
            //document.getElementById('loading-image').style.display = "none";
            //swal('Success!', 'Thanks for submitting the query.', 'success')
        }
    });
}
function mapping_type(type) {
    var url = window.location.href;
    var getUrlParameter = function getUrlParameter(sParam) {
        var sPageURL = decodeURIComponent(window.location.search.substring(1)),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;

        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');

            if (sParameterName[0] === sParam) {
                return sParameterName[1] === undefined ? true : sParameterName[1];
            }
        }
    };

    var get_type = getUrlParameter('mapping_type');
    if(get_type === undefined && type == 'normal') {
        window.location.replace(url + '&mapping_type=normal');
        //jQuery.param.querystring(window.location.href, 'mapping_type=normal');
    } else {
        if(type == 'normal') {
            window.location.replace(url + '&mapping_type=normal');
        } else {
            window.location.replace(url + '&mapping_type=advanced');
            //jQuery.param.querystring(window.location.href, 'mapping_type=advanced');
        }
    }
   }

var dragableDroppable = function() {
        jQuery(".draggable").draggable({
         //revert: true,
         helper: 'clone',
         containment: "document",
         helper: function() {
            return jQuery(this).clone().appendTo('body').css({
                'zIndex': 5
            });
        },
         start: function(event, ui) {
            jQuery(this).fadeTo('fast', 0.5);
         },
         stop: function(event, ui) {
            jQuery(this).fadeTo(0, 1);
         }
      });
      jQuery(".droppable").droppable({
         hoverClass: 'active',
         drop: function(event, ui) {
            this.value += '{' + jQuery(ui.draggable).text() + '}';
         }
      });
};
function retrieve_record(action, value) {
    jQuery('.route-loader-container').addClass('active');
    var current_record = jQuery('#current_row').val();
    var eventKey = jQuery('#event_key').val();
    var row_no = 0;
    if(action == 'prev') {
        row_no = parseInt(current_record) - 1;
    } else if(action == '') {
        row_no = parseInt(value);
    } else {
        row_no = parseInt(current_record) + 1;
    }
    var total_no_of_records = jQuery('#total_no_of_records').val();
    if(row_no > total_no_of_records) {
        row_no = total_no_of_records;
    } else if (row_no <= 0) {
        row_no = 1;
    }
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        dataType: 'json',
        data: {
            'action': 'retrieve_record',
            'row_no': parseInt(row_no),
            'event_key': eventKey,
        },
        success: function (response) {
            //swal('Success!', 'Thanks for submitting.', 'success')
            var html = '';
            jQuery('ul.uci_mapping_attr_value').empty();
            jQuery.each(response, function(key, val){
                if(val.length > 150) val = val.substring(0, 150) + '<span style="color: red;"> [more]</span>';
                html = '<div class="uci_mapping_csv_column"><li draggable="true" ondragstart="drag(event)" class="uci_csv_column_header" title="' + key + '"style="color: #00A699; font-weight: 600;">' + key + '</li> <li class="uci_csv_column_val" style="border-right: none;">' + val + '</li></div>';
                jQuery('ul.uci_mapping_attr_value').append(html);
                jQuery('li.uci_csv_column_header').addClass('draggable');
                dragableDroppable();
            });
            jQuery('#current_row').val(row_no);
	    //jQuery('ul.uci_mapping_attr_value').empty();
            //jQuery('ul.uci_mapping_attr_value').append(html);
            jQuery('.route-loader-container').removeClass('active');
        }
    });
}

function removeRow(row_id) {
    jQuery("#"+row_id).remove();
}

