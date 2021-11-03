<?php

/*
*
*   Controller functions for KongMap
*
*/

function process_time(){
   $time = number_format( microtime(true) - LIM_START_MICROTIME, 6);
  return("<BR>Controller Function Called: " .  option('routecallback') . "<BR>Request processed in $time seconds");
}

function check_config()
{



   $kong_clusters_json =  getenv("KONGMAP_CLUSTERS_JSON");

    $kongmap_url =  getenv("KONGMAP_URL");

    if ((empty($kong_clusters_json)) || (!isset($kong_clusters_json))) {
        $kong_clusters_json =  getenv("KONGMAP_CLUSTER_JSON");
        //accommodating old docs error
    } else {
    }





    if ((empty($kongmap_url)) || (!isset($kongmap_url))) {
        $app_url = url_for();
        set('app_url', $app_url);
        option('app_url',$app_url);
    } else {
        $app_url = $kongmap_url;
        set('app_url', $app_url );
        option('app_url',$app_url . "/");
    }


    if ((empty($kong_clusters_json)) || (!isset($kong_clusters_json))) header('Location: /error/0');

    $kong_clusters = json_decode($kong_clusters_json,true);

    option('kong_clusters', $kong_clusters);


}

function map_error(){
    $errors = [
      0=>"No Cluster was defined in config or is missing configuration items. See instructions.",
      1=>"Unable to connect with Kong Admin API. Please verify your configuration's Admin API URL and RBAC token if one is used.",
    ];

    if (array_key_exists(params('num'), $errors)) $error = $errors[params('num')];
    else $error = "Some error occurred.";


    set('navbar', "");
    return html('<div class="alert alert-danger" role="alert">'.$error.'</div>');
}

function deck()
{
    check_config();
    //get cluster ID
    $kong_clusters =  option('kong_clusters');
    $cluster_index = params('cluster_index');

    if (is_numeric($cluster_index)) $array_keys = array_keys($kong_clusters);
    else header("Location: ".option('app_url')."01101011011011110110111001100111011011010110000101110000");

//print_r($array_keys);
    if (array_key_exists($cluster_index, $array_keys)) $active_cluster = $array_keys[$cluster_index];
    else return html("Please select a cluster.");
    $kongadminapi =  $kong_clusters["$active_cluster"]["kong_admin_api_url"];
    option('kongadminapi', $kongadminapi);
    //option('kong_ent_token_name')
    $kong_ent = $kong_clusters["$active_cluster"]["kong_ent"];
    $kong_ent_token = $kong_clusters["$active_cluster"]["kong_ent_token"];
    $kong_ent_token_name = $kong_clusters["$active_cluster"]["kong_ent_token_name"];
    $kong_ent_manager_url = $kong_clusters["$active_cluster"]["kong_ent_manager_url"];

    //do we need to show the entity id's with deck
    if (isset($_REQUEST['showid']) && $_REQUEST['showid'] != NULL) {
        $showid = "--with-id";
    }
    else {
        $showid = "";
    }





    if (isset($_REQUEST['workspace']) && $kong_ent == "true") {

        if ($_REQUEST['workspace'] == NULL) $workspace = "default";
        else $workspace =$_REQUEST['workspace'];

        $command = 'dump --tls-skip-verify '.$showid.' --kong-addr '. option('kongadminapi') .' -o - --format yaml --workspace ' . $workspace .' --headers '.  $kong_ent_token_name . ':' .  $kong_ent_token;
    }
    else if (isset($_REQUEST['tag']) && $_REQUEST['tag'] != NULL) {$command = 'dump --tls-skip-verify '.$showid.' --kong-addr '. option('kongadminapi') .' -o - --format yaml --select-tag '. $_REQUEST['tag'];}
    else { $command = 'dump --tls-skip-verify '.$showid.' --kong-addr '. option('kongadminapi') .' -o - --format yaml';}
    // echo getcwd() .'/controllers/deck/';
    //  exit;
    // $command = 'dump --kong-addr '. option('kongadminapi') .' -o - --format json';
    $output = shell_exec(getcwd() .'/controllers/deck/deck ' . $command);
     // echo "<pre>'/var/www/yesdev/dumpster/deck/deck ' . $command</pre>";
    //  exit;
    //return json(json_decode($output));
    send_header('Content-Type: text/plain; charset='.strtolower(option('encoding')));
    return $output;
}


function deck_edit()
{






    check_config();
    //get cluster ID
    $kong_clusters =  option('kong_clusters');
    $cluster_index = params('cluster_index');
    $dbless = params('dbless');
    $navbar = '<a class="btn btn-secondary btn-sm" onclick="window.close()">Close Window and Return To Cluster Map</a>';
    //$navbar = ' ';

   set('navbar', $navbar);
    if (is_numeric($cluster_index)) $array_keys = array_keys($kong_clusters);
    else header("Location: ".option('app_url')."01101011011011110110111001100111011011010110000101110000");

    if (array_key_exists($cluster_index, $array_keys)) $active_cluster = $array_keys[$cluster_index];
    else return html("Please select a cluster.");

    $cluster_name = $array_keys[$cluster_index];
    $kongadminapi =  $kong_clusters["$active_cluster"]["kong_admin_api_url"];
    option('kongadminapi', $kongadminapi);
    //option('kong_ent_token_name')
    $kong_ent = $kong_clusters["$active_cluster"]["kong_ent"];
    $kong_ent_token = $kong_clusters["$active_cluster"]["kong_ent_token"];
    $kong_ent_token_name = $kong_clusters["$active_cluster"]["kong_ent_token_name"];
    $kong_ent_manager_url = $kong_clusters["$active_cluster"]["kong_ent_manager_url"];
    $canedit = $kong_clusters["$active_cluster"]["kong_edit_config"];

    $rando = 'kong.yaml';  //rando file name

    if (($_SERVER['REQUEST_METHOD'] == "POST") && ($canedit == "true")){

        if ($dbless == 0){
            //run deck
            chdir(getcwd(). '/controllers/deck');
            $file_handle = fopen("kong.yaml", 'w');
            //  echo $_POST['kongconfig'];
            fwrite($file_handle, $_POST['kongconfig']);
            fclose($file_handle);
            //  chmod($rando,777);


            $shell = getcwd() .'/run_deck.sh ' . $rando . ' ' . $kongadminapi;
            $newc=  $shell;




            if (isset($_REQUEST['workspace']) && $_REQUEST['workspace'] != NULL) {
                $command = "sync --tls-skip-verify --verbose 2 --config kong.yaml --kong-addr $kongadminapi"  .' --headers '.  $kong_ent_token_name . ':' .  $kong_ent_token;
            }
            //else if (isset($_REQUEST['tag']) && $_REQUEST['tag'] != NULL) {$command = 'dump --kong-addr '. option('kongadminapi') .' -o - --format json --select-tag '. $_REQUEST['tag'];}
            else { $command = "sync --tls-skip-verify --config kong.yaml --kong-addr $kongadminapi";}


            $output = exec(getcwd() .'/deck ' .$command,$out);

            $newout = "";


            //clean out file
            $file_handle = fopen("kong.yaml", 'w');
            fwrite($file_handle, "Emptied");
            fclose($file_handle);


            if ($out == NULL) {
                $newout = "An error occurred, please view the application logs to see what went sideways.";
            } else {
                foreach ($out as $zz) {
                    $newout .= $zz . "\n";
                }
            }


            return html("<h4>decK Results</h4><pre style='padding:10px;border-radius:5px;' class='json-editor-blackbord'>". htmlspecialchars($newout) . $output . "</pre>");
        }
        else {
            //run through config endpoint
            $pconfig = $_POST['kongconfig'];

            $fields = array(
                'config' => "$pconfig");

            $response = fsl_curl("$kongadminapi/config", "POST", "FORM",  NULL, http_build_query($fields),   NULL, NULL,NULL,  NULL, array(option('kong_ent_token_name') . ":" . option('kong_ent_token') ) );


            //check responses
            if ($response[0] >299)      return html('<div class="alert alert-danger" role="alert">'."There was an error contacting your Kong Admin API: " . $response[2]  .'</div>');
            if ($response[0] <200)      return html('<div class="alert alert-danger" role="alert">'."There was an error contacting your Kong Admin API. Check your configuration. " .'</div>');

            return html("<h4>Declarative Config Update Results</h4><pre style='padding:10px;border-radius:5px;' class='json-editor-blackbord'>". "Configuration Applied Successfully. " . "</pre>");
        }


    }
    else{

        $dbmessage = (($dbless == 1) ? 'DB-Less': 'Database');

        //do we need to show the entity id's with deck
        if (isset($_REQUEST['showid']) && $_REQUEST['showid'] != NULL) {
            $showid = "--with-id";
            $showid_check = "checked";  //for UI toggle
            $search_editor = ($_REQUEST['showid'] == "true" ? "": "editor.find('".$_REQUEST['showid']."');");


           // $search_editor = "editor.find('".$_REQUEST['showid']."');";
        }
            else {
                $showid = "";
                $search_editor = "";
                $showid_check = "";
            }




        if (isset($_REQUEST['workspace']) && $kong_ent == "true"){
            if ($_REQUEST['workspace'] == NULL) $workspace = "default";
            else $workspace =$_REQUEST['workspace'];
            $workspace_name = $workspace . " workspace";
            $command = 'dump --tls-skip-verify --kong-addr '. option('kongadminapi') .' -o - --format yaml '.$showid.' --workspace ' . $workspace .' --headers '.  $kong_ent_token_name . ':' .  $kong_ent_token;
        }
        else if (isset($_REQUEST['tag']) && $_REQUEST['tag'] != NULL) {$command = 'dump --tls-skip-verify '.$showid.' --kong-addr '. option('kongadminapi') .' -o - --format yaml --select-tag '. $_REQUEST['tag'];}
        else {
            $workspace = NULL;
            $workspace_name = "default workspace";
            $command = 'dump --tls-skip-verify '.$showid.' --kong-addr '. option('kongadminapi') .' -o - --format yaml';
        }


        if ($canedit == "true"){
            //set edit button and json editor flags to edit
            $savebutton = '<BR><button type="submit" class="btn btn-primary btn-sm" id="save" >Save New Declarative Configuration</button>';
            $editmessage = "EDITABLE";
            $setreadonly = "false";
        } else {
            $savebutton = "<BR>";
            $canedit = "false";
            $editmessage = "READ ONLY";
            $setreadonly = "true";
        }

        $output = shell_exec(getcwd() .'/controllers/deck/deck ' . $command);

        $script = "    <pre id='json-display'></pre>";


        $html = ' 
<style type="text/css" media="screen">
    #editor { 
        width: 95vw;
        height: 75vh;
    }
</style>
</head>
<body>

<div id="editor">'.$output.'</div>
  <script src="https://ajaxorg.github.io/ace-builds/src-noconflict/ace.js" crossorigin="anonymous" ></script>  
 
<script>
    var editor = ace.edit("editor");
    editor.resize(true);
    editor.setTheme("ace/theme/twilight");
    editor.session.setMode("ace/mode/yaml");
        editor.setOptions({
        autoScrollEditorIntoView: true
    });
    
    //editor.findNext();
    editor.setReadOnly('.$setreadonly.')
    var textarea = $(' . "'" . 'textarea[name="kongconfig"]' . "'" .');
    editor.getSession().on("change", function () {
    textarea.val(editor.getSession().getValue());});
    '.$search_editor.'
    
    
  function toggleId(){
  if (document.getElementById(\'toggleid\').checked) 
  {
             if (window.location.search.includes("workspace") == true){
                         window.location = window.location.pathname + window.location.search + "&showid=true";
              } else {
                  window.location = window.location.pathname + "?showid=true";
              }
           
  } else 
  {
      
      if (window.location.search.includes("showid") == true){
              //remove showid param
              var params = new URLSearchParams(window.location.search);
              //alert(params.toString());
              params.delete("showid");
              window.location = window.location.pathname + "?" + params;
      } else {
                  if (window.location.search.includes("workspace") == true){
                         window.location = window.location.pathname + window.location.search + "";
              } else {
                  window.location = window.location.pathname + "";
              }
      }
  }
}     

</script>
 ';

$confirmjs = '<script>  
$(\'#editform\').on(\'submit\', function(e){
    // prevent the normal form submission
    e.preventDefault();
    var currentForm = this;

    bootbox.confirm(\'<h4>Are you sure you want to save?</h4>Clicking OK will save this config to your Kong Cluster. Cancel if you do not wish to proceed.\', function(result){
        // result will be a true or false value
        if(result){
            /* Submit the form via ajax? */
            currentForm.submit();
        }
    })

});


</script>';


        $textarea = '

<form method="post" onsubmit="" id="editform"><textarea name="kongconfig" id="json-input" style="display:none;width:75vw;height:75vh">'.$output.'</textarea>Current Declarative Config for <BR><b style="font-size: x-large"> '.$cluster_name.' </b><span class="badge badge-warning">'.$editmessage.'</span> <span class="badge badge-info">'.$dbmessage.'</span> <span class="badge badge-info" style="background-color: purple">'.$workspace_name.'</span> 
<BR><div class="custom-control custom-switch">
  <input type="checkbox" class="custom-control-input" id="toggleid" ' . $showid_check . ' onclick="toggleId()">
  <label class="custom-control-label" for="toggleid">Show Entity ID\'s</label>
</div><BR> '. $html . $savebutton.'  <a href="../../export/'.$cluster_index.'?workspace='.$workspace.'" target=_blank class="btn btn-secondary   btn-sm"  >Export Config without ID\'s</a> 
<a href="../../export/'.$cluster_index.'?workspace='.$workspace.'&showid=true" target=_blank class="btn btn-secondary   btn-sm"  >Export Config with ID\'s</a> 
<a href="'.url_for('/').'" class="btn btn btn-dark btn-sm" onclick="window.close()">Cancel</a>
</form>'.$script.'  ' . $confirmjs
        ;

        return html($textarea);

    }


    //return html(json_decode($output));
}

function inspect()
{
    check_config();
    //get route
        $offset = 0;
    $kong_clusters =  option('kong_clusters');
   // print_r($kong_clusters);

    $kong_clusters =  option('kong_clusters');
    $cluster_index = params('cluster_index');
    $dbless = params('dbless');

    $navbar = '

  <a href="'.url_for('/').'" class="btn btn-secondary btn-sm" onclick="window.close()">Close Window and Return To Cluster Map</a>

';

    set('navbar', $navbar);
    if (is_numeric($cluster_index)) $array_keys = array_keys($kong_clusters);
    else return html("Invalid request.");
   if (array_key_exists($cluster_index, $array_keys)) $active_cluster = $array_keys[$cluster_index];
   else return html("Invalid request.");
    $kongadminapi =  $kong_clusters["$active_cluster"]["kong_admin_api_url"];
    option('kongadminapi', $kongadminapi);
    $kong_ent = $kong_clusters["$active_cluster"]["kong_ent"];
    option('kong_ent',$kong_ent);
    $kong_ent_token = $kong_clusters["$active_cluster"]["kong_ent_token"];
    option('kong_ent_token',$kong_ent_token);
    $kong_ent_token_name = $kong_clusters["$active_cluster"]["kong_ent_token_name"];
    option('kong_ent_token_name',$kong_ent_token_name);
    $kong_ent_manager_url = $kong_clusters["$active_cluster"]["kong_ent_manager_url"];


    $theroute = set_or_default('theroute', params('route'), "");




        if (option('kong_ent') == "true") $links = '<HR> <div class="alert alert-info" role="alert">Click on any node to view details, export configs, and more.</div>';

        $workspace = (option('kong_ent') == "true" ? "/" .params('workspace') . "": "");
       $kongadminapi = $kongadminapi . $workspace;
    // echo $kongadminapi .'/routes/'.$theroute;
        $response = fsl_curl("$kongadminapi/routes/$theroute", "GET", "JSON",  NULL, NULL,   NULL, NULL,NULL,  NULL, array(option('kong_ent_token_name') . ":" . option('kong_ent_token') ) );
        //check responses
        if ($response[0] >299)      return html('<div class="alert alert-danger" role="alert">'."There was an error contacting your Kong Admin API: " . $response[2] .'</div>');
        if ($response[0] <200)      return html('<div class="alert alert-danger" role="alert">'."There was an error contacting your Kong Admin API. Check your configuration. " .'</div>');

    $route = json_decode($response[2],true);

        //check response
    if ($route['service']['id'] == NULL)      return html('<div class="alert alert-danger" role="alert">'."Invalid response received. Check your Admin API URL in your  cluster configuration." .'</div>');


    $service_id = $route['service']['id']; //this is service
    $arrows = "";
  $plugin_array = array();

    if (($kong_ent == "true") && ($kong_ent_manager_url != "null"))  {
        $isent = 'yes';
        $kmurl = $kong_ent_manager_url . $workspace . "/routes/" . $route['id'];
    } else{
        $isent = "no";
        $kmurl = '';
    }


    $routeconf = json2html::jsonToDebug($response[2]);
    $routeconf = rawurlencode($routeconf);

$parrows = '<div style="display:inline-block" class="arrow-container" onclick=showdata(\''.$routeconf.'\',"Route",\''.$isent.'\',\''.$kmurl.'\',\'\',\''.$route['id'].'\')>
    <div id="zz" class="arrow-left-route"></div>
    <div id="'.$route['id'].'" class="arrow-ctr-route">'.$route['name'].'</div>
    <div id="zz" class="arrow-right-route"></div>
</div>';
        $service_plugins = array();
        // print_r($route);
    $offset = $offset -30;

    // get plugins for route and add array

    $response = fsl_curl("$kongadminapi/routes/".$route['id']."/plugins", "GET", "JSON",  NULL, NULL,   NULL, NULL,NULL,  NULL, array(option('kong_ent_token_name') . ":" . option('kong_ent_token') ) );
    $rplugins = json_decode($response[2],true);

    //merge array
    $plugin_array = $rplugins;

    // get service
        $response = fsl_curl("$kongadminapi/services/$service_id", "GET", "JSON",  NULL, NULL,   NULL, NULL,NULL,  NULL, array(option('kong_ent_token_name') . ":" . option('kong_ent_token') ) );
        $service = json_decode($response[2],true);
        //$service_id = $service['service']['id']; //this is service



    // get upstream targets if any
    $response = fsl_curl("$kongadminapi/upstreams/".$service['host']."/targets", "GET", "JSON",  NULL, NULL,   NULL, NULL,NULL,  NULL, array(option('kong_ent_token_name') . ":" . option('kong_ent_token') ) );
    //$service_id = $service['service']['id']; //this is
    if ($response[0] > 400) {
        $has_targets = "no";
    }
    else {
        $has_targets = "yes";
        $upstreams = json_decode($response[2],true);
    }


    // get plugins for service and add to plugins array

    $response = fsl_curl("$kongadminapi/services/".$service['id']."/plugins", "GET", "JSON",  NULL, NULL,   NULL, NULL,NULL,  NULL, array(option('kong_ent_token_name') . ":" . option('kong_ent_token') ) );
  unset($rplugins);
  $rplugins = json_decode($response[2],true);
    //$plugin_array = array_merge($rplugins,$plugin_array2);

    foreach($rplugins['data'] as $plugin){

      array_push($plugin_array['data'],$plugin);

    }

    // get global plugins, and add to array
    $response = fsl_curl("$kongadminapi/plugins", "GET", "JSON",  NULL, NULL,   NULL, NULL,NULL,  NULL, array(option('kong_ent_token_name') . ":" . option('kong_ent_token') ) );
    $obj = json_decode($response[2],true);
    //$plugin_array = array_merge($plugin_array,$obj);
    $global_plugins = array();
    foreach($obj['data'] as $plugin){
        //check to see if it is not related to route or service before proceeding
        if (($plugin['route'] == NULL) && ($plugin['service'] == NULL)){
            array_push($plugin_array['data'],$plugin);
        } else{
            // do nothing
        }

    }


    //re-sort plugins by execution
    $priority = load_plugins();
    usort($plugin_array['data'], function ($a, $b) use ($priority) {
        return $priority[$a['name']] - $priority[$b['name']];
    });

    // print_r($my_array_to_sort);

   $plugin_array['data']  = array_reverse($plugin_array['data'] );

   // print_r($plugin_array);

      foreach($plugin_array['data'] as $plugin){

          $plugconf = json_encode($plugin);
          $plugconf = json2html::jsonToDebug($plugconf);
        //  echo $plugconf;
        // exit;
            if (($plugin['name'] == "pre-function") || ($plugin['name'] == "post-function")) {
                $title = "Serverless";
                $plugconf = rawurlencode($plugconf);
            }
          else if (($plugin['name'] == "req0uest-termination") || ($plugin['name'] == "pxost-function")) {
              $title = "Terminator";
              $plugconf = rawurlencode($plugconf);
          }
            else {
                $plugconf = rawurlencode($plugconf);
                $title = "Plugin";
            }

        if (($kong_ent == "true") && ($kong_ent_manager_url != "null")) {
            $isent = 'yes';
            $kmurl = $kong_ent_manager_url . $workspace . "/plugins/" . $plugin['name'] ."/". $plugin['id'];
        } else{
            $isent = "no";
            $kmurl = '';
        }

        //set color class
        $colorclass = ($plugin['enabled'] == true ? '': '-disabled');

        //set plugin scope
        if (($plugin['consumer'] == NULL) && ($plugin['route'] == NULL) && ($plugin['service'] == NULL)) { $scope = "Global";}
          else if (($plugin['route'] != NULL) && ($plugin['service'] == NULL)) { $scope = "Route";}
          else if (($plugin['route'] == NULL) && ($plugin['service'] != NULL)) { $scope = "Service";}
          else {$scope = "Consumer";}

          //  $plugconf = json_encode($plugin['config']);
        $arrows .= '<div style="display:inline-block;position:relative;left:'. $offset .'px" class="arrow-container" onclick=showdata(\''.$plugconf.'\',"'.$title.'",\''.$isent.'\',\''.$kmurl.'\',\''.$scope.'\',\''.$plugin['id'].'\')>
                    <div id="zz" class="arrow-left-plugin'.$colorclass.'"></div>
                    <div id="'.$plugin['id'].'" class="arrow-ctr-plugin'.$colorclass.'">'.$plugin['name'].' </div>
                    <div id="zz" class="arrow-right-plugin'.$colorclass.'"></div>
                    </div>';


        $offset = $offset -30;

    }


    if (($kong_ent == "true") && ($kong_ent_manager_url != "null")) {
        $isent = 'yes';
        $kmurl = $kong_ent_manager_url . $workspace . "/services/" . $service['id'];
    } else{
        $isent = "no";
        $kmurl = '';
    }

    $servconf = json2html::jsonToDebug(json_encode($service));
    if ($has_targets == "yes") $servconf .=  "<h4>Upstream Targets</h4>" . json2html::jsonToDebug(json_encode($upstreams));

    $servconf = rawurlencode($servconf);

    //set service arrow
    $sarrows = '<div style="display:inline-block;position:relative;left:' . $offset . 'px" class="arrow-container" onclick=showdata(\'' . $servconf . '\',"Service",\''.$isent.'\',\''.$kmurl.'\',\'\',\''.$service['id'].'\')>
                <div id="zz" class="arrow-left-service"></div>
                <div id="'.$service['id'].'" class="arrow-ctr-service">' . $service['name'] . ' </div>
                <div id="zz" class="arrow-right-service"></div>
                </div>';
    $offset = $offset - 30;



    // sort plugins by execution priority

    // print out flow diagram
  //  $config = '<hr><a class="btn btn-info btn-sm" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample" style="width:100%;">Full Config</a><div class="collapse" id="collapseExample">  <div class="card card-body"> Details </div></div>';

//collapse plugins if offset greater than 200.

    if ($offset < -300) {
        $plugindisplay = "none";
    $expandlogic = '    if (document.getElementById(id).style.display == \'inline-block\'){   
document.getElementById(id).style.display = \'none\';          
     } else{
    document.getElementById(id).style.display = \'inline-block\';
     }';
    }
    else {
        $plugindisplay = "inline-block";
        $expandlogic = "";
    }


    $style = '
<style>
.arrow-container > div {
    float: left;
    font-size: small;
    cursor: pointer;;
}

.arrow-left-route {
    display:inline-block;
    width: 0; 
	height: 0; 
	border-top: 40px solid lightgreen;
	border-bottom: 40px solid lightgreen;
	border-left: 40px solid transparent;
}

.arrow-ctr-route {
    display: inline-block;
    min-width:50px;
    padding-left: 5px;
    background:lightgreen;
    min-height:80px;
    line-height: 80px;
    position:relative;
    text-align: center;
}
.arrow-right-route {
	display:inline-block;
	height: 0; 
	border-top: 40px solid transparent;
	border-bottom: 40px solid transparent;
	border-left: 40px solid lightgreen;
    position:relative;
}

 .arrow-left-service {
    display:inline-block;
    width: 0; 
	height: 0; 
	border-top: 40px solid skyblue;
	border-bottom: 40px solid skyblue;
	border-left: 40px solid transparent;
}

.arrow-ctr-service {
    display: inline-block;
    min-width:50px;
    padding-left: 5px;
    background:skyblue;
    min-height:80px;
    line-height: 80px;
    position:relative;
    text-align: center;
}
.arrow-right-service {
	display:inline-block;
	height: 0; 
	border-top: 40px solid transparent;
	border-bottom: 40px solid transparent;
	border-left: 40px solid skyblue;
    position:relative;
}

 .arrow-left-plugin {
    display:inline-block;
    width: 0; 
	height: 0; 
	border-top: 40px solid pink;
	border-bottom: 40px solid pink;
	border-left: 40px solid transparent;
}

.arrow-ctr-plugin {
    display: '.$plugindisplay.';
    min-width:50px;
    padding-left: 5px;
    background:pink;
    min-height:80px;
    line-height: 80px;
    position:relative;
    text-align: center;
}
.arrow-right-plugin {
	display:inline-block;
	height: 0; 
	border-top: 40px solid transparent;
	border-bottom: 40px solid transparent;
	border-left: 40px solid pink;
    position:relative;
}

 .arrow-left-plugin-disabled {
    display:inline-block;
    width: 0; 
	height: 0; 
	border-top: 40px solid #fff3f5;
	border-bottom: 40px solid #fff3f5;
	border-left: 40px solid transparent;
}

.arrow-ctr-plugin-disabled {
    display: '.$plugindisplay.';
    min-width:50px;
    padding-left: 5px;
    background:#fff3f5;
    min-height:80px;
    line-height: 80px;
    position:relative;
    text-align: center;
}
.arrow-right-plugin-disabled {
	display:inline-block;
	height: 0; 
	border-top: 40px solid transparent;
	border-bottom: 40px solid transparent;
	border-left: 40px solid #fff3f5;
    position:relative;
}
</style>';





$card = '<BR><BR>

 


<script type="text/javascript">
function showdata(data,type,isent,kmurl,scope,id) {
   // data = "hello";
    //  var str = JSON.stringify(data);
    
   
'.$expandlogic.'
    
     if (isent == "yes"){   
        var button = \'<a href="\' + kmurl + \'" style="width:250px;" target=_blank class="btn btn-info btn-sm">View \' + type + \' In Kong Manager</a> \';
         var kmurl2 = "'. option('app_url') . 'deck/edit/'.$cluster_index.'/'.$dbless.'?showid=" + id + "&workspace='.ltrim($workspace, "/") .'";
                button = button + \'<a href="\' + kmurl2 + \'" style="width:250px;" target=_blank class="btn btn-dark btn-sm">View/Edit Config</a><BR><BR>\';
   
          
     } else{
        //var button = "";
        var kmurl2 = "'. option('app_url') . 'deck/edit/'.$cluster_index.'/'.$dbless.'?showid=" + id;
                var button = \'<a href="\' + kmurl2 + \'" style="width:250px;" target=_blank class="btn btn-dark btn-sm">View/Edit Config</a><BR><BR>\';
   
     }
    

     if ((type == "Serverless") || (type == "Terminator")){ 
         type == "Plugin";
        data = decodeURI(data);
        var newdata = data.replaceAll("%3A", ":");
        data = newdata.replaceAll("%2C", ",");
        newdata = data.replaceAll("%3D", "=");
        data = newdata.replaceAll("%2F", "/");
     //document.getElementById(\'eventSpan\').innerHTML =  button + "<h4>"+ type + \' Details <span class="badge badge-secondary">\' + scope + \'</span></h4><textarea disabled style=\"width:100%;  background-color:#f1f1f1;  border:none; height: 50vh;\"> \' +  data + "</textarea> " + data;
          document.getElementById(\'eventSpan\').innerHTML =  button + "<h4>"+ type + \' Details <span class="badge badge-secondary">\' + scope + \'</span></h4>\' + data;
 
         // document.getElementById(\'eventSpan\').innerHTML =  data; 
       //  editor.setValue(JSON.stringify(data, null, "\t"));

     } else if ((type == "Plugin") ) {
                 data = decodeURI(data);
        var newdata = data.replaceAll("%3A", ":");
        data = newdata.replaceAll("%2C", ",");
        newdata = data.replaceAll("%3D", "=");
        data = newdata.replaceAll("%2F", "/");
     document.getElementById(\'eventSpan\').innerHTML =  button + "<h4>"+ type + \' Details <span class="badge badge-secondary">\' + scope + \'</span></h4>\' + data;
//editor.setValue(JSON.stringify(str, null, "\t"));
     } else{
               data = decodeURI(data);
        var newdata = data.replaceAll("%3A", ":");
        data = newdata.replaceAll("%2C", ",");
        newdata = data.replaceAll("%3D", "=");
        data = newdata.replaceAll("%2F", "/");
     document.getElementById(\'eventSpan\').innerHTML =  button + "<h4>"+ type + \' Details </h4>\' + data;
         // document.getElementById(\'eventSpan\').innerHTML =  data;     
     }

}
</script>
<style type="text/css" media="screen">
    #editor { 
        width: 95vw;
        min-height:30px;
        max-height:300px;
    }
</style>
 

<div class="card" style="     ">

    <div class="card-body" id="eventSpan" style="background-color: #f1f1f1; ">
    Click on a section above to see more details or to expand or collapse the element. Plugins (if any) are displayed in order of execution.
    

 
    </div>
    
 


</div>';
//print_r($plugin_array);
    return html("<h4>Your Endpoint Analysis:</h4><BR>$style <span style='white-space: nowrap'>$parrows$arrows$sarrows </span>$card");
}


function map2()
{
    check_config();
    $kong_clusters =  option('kong_clusters');
    $cluster_index = params('cluster_index');

    if (is_numeric($cluster_index)) $array_keys = array_keys($kong_clusters);
    else header("Location: ".option('app_url')."01101011011011110110111001100111011011010110000101110000");
//print_r($array_keys);
    if (array_key_exists($cluster_index, $array_keys)) {
        $active_cluster = $array_keys[$cluster_index];
        $kongadminapi =  $kong_clusters["$active_cluster"]["kong_admin_api_url"];
        option('kongadminapi', $kongadminapi);
        //option('kong_ent_token_name')
        $kong_ent = $kong_clusters["$active_cluster"]["kong_ent"];
        $kong_ent_token = $kong_clusters["$active_cluster"]["kong_ent_token"];
        $kong_ent_token_name = $kong_clusters["$active_cluster"]["kong_ent_token_name"];
        $kong_ent_manager_url = $kong_clusters["$active_cluster"]["kong_ent_manager_url"];
        $canedit = $kong_clusters["$active_cluster"]["kong_edit_config"];
        $editbutton = ""; //set edit buttons NULL

        $navbar = '
<script>
function loading() {
    var x = document.getElementById("dropdownMenuButton");
        x.innerHTML = \' <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span> Loading...\';
  }
</script>

            <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"   ">
                Select Cluster
            </button> 
</button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';

        $cluster_key = 0;
        foreach ($array_keys as $value) {
            $active = (($cluster_index == $cluster_key) ? 'active': '');
            $navbar .=  '<a onclick="loading()" class="dropdown-item '.$active.'" href="./'.$cluster_key.'">'.$value.'</a>';
            $cluster_key++;
        }
        $navbar .= '</div></div>';
        set('navbar', $navbar);
    }
    else {
        $navbar = '
<script>
function loading() {
    var x = document.getElementById("dropdownMenuButton");
        x.innerHTML = \' <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span> Loading...\';
  }
</script>

            <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"   ">
                Select Cluster
            </button> 
</button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';

        $cluster_key = 0;
        foreach ($array_keys as $value) {
            $active = (($cluster_index == $cluster_key) ? 'active': '');
            $navbar .=  '<a onclick="loading()" class="dropdown-item '.$active.'" href="./'.$cluster_key.'">'.$value.'</a>';
            $cluster_key++;
        }
        $navbar .= '</div></div>';
        set('navbar', $navbar);
        return html("Please select a cluster.");
    }



    //if not a valid cluster index then ask user to choose a cluster

    fsl_session_set('crop','Yummyd Limonade');
    set_or_default('name', params('who'), "everybody");
    $time = number_format( microtime(true) - LIM_START_MICROTIME, 6);


    //get cluster info
    $response = fsl_curl("$kongadminapi", "GET", "JSON",  NULL, NULL,   NULL, NULL,NULL,  NULL, array($kong_ent_token_name . ":" . $kong_ent_token ) );

    //print_r($response);

    //check response
    if ($response[0] >299)      return html('<div class="alert alert-danger" role="alert">'."There was an error contacting your Kong Admin API: " . $response[2] .'</div>');
    if ($response[0] <200)      return html('<div class="alert alert-danger" role="alert">'."There was an error contacting your Kong Admin API. Check your configuration. " .'</div>');

    $obj = json_decode($response[2],true);

    //check if admin api
    if ($obj['version'] == NULL)      return html('<div class="alert alert-danger" role="alert">'."Admin URL supplied ($kongadminapi) is not a valid Kong Admin API URL." .'</div>');

    //check if DBLess
    if ($obj['configuration']['database'] == "off") { $dbless = 1; $dbhost = ""; }
    else { $dbless = 0; $dbhost = "<BR>DB Host: <code>" .  $obj['configuration']['pg_host'] . "</code>";}


    $cluster_config_details = "Cluster Name: <code>" . $active_cluster . "</code><BR>Admin API: <code>" .  $kongadminapi . "</code>";
    $clusterinfo = "<b>Cluster Info</b><BR>".$cluster_config_details."<BR>Hostname: <code>" . $obj['hostname'] . "</code><BR>Version: <code>" .  $obj['version'] . "</code><BR>Database: <code>" .  $obj['configuration']['database'] . "</code>" . $dbhost;




    if ($kong_ent == "true") $links = '<HR><div class="alert alert-info" role="alert"> Click on any node to view details, export configs, and more.</div>';
    else $links = '<HR> <a href="deck/edit/'.$cluster_index.'/'.$dbless.'" target=_blank class="btn btn-secondary   btn-sm" style="width:100%">View/Edit Cluster Config</a><BR><BR>';


    $clusterdetails = "<h4>Cluster Details</h4> ".$cluster_config_details."<BR>Hostname: <code>" . $obj['hostname'] . "</code><BR>Version: <code>" .  $obj['version'] . "</code><BR>Database: <code>" .  $obj['configuration']['database'] . "</code>" . $dbhost . $links;
//    set('cluster_info', $clusterdetails);

    $legend = "";
    //$nodes ="$legend { id: 1, label: \"".$active_cluster."\", size:55, shape: 'image', image: 'https://2tjosk2rxzc21medji3nfn1g-wpengine.netdna-ssl.com/wp-content/themes/konghq/assets/img/home/kong-icon.svg',details:'".$details."',color:'white',title:'".$clusterinfo."' },";
    $nodes = "";
    $idcounter = 2;
    $connections = "";
    $servicecounter = 0;
    $workspacecounter = 0;
    $total_services = 0;
    $total_routes = 0;
    $total_plugins = 0;

    //fetch each workspace
    $wresponse = fsl_curl("$kongadminapi/workspaces", "GET", "JSON",  NULL, NULL,   NULL, NULL,NULL,  NULL, array($kong_ent_token_name . ":" . $kong_ent_token ) );
    $wobj = json_decode($wresponse[2],true);
    //  $wobj= array("data"=>array(array("name"=>"default",),));
    //   print_r($wresponse[2]);

    if ($kong_ent == "false") $wobj= array("data"=>array(array("name"=>"default",),)); //set one workspace for OSS



    //process each workspace
    foreach($wobj['data'] as $witem) {
        $kongadminapi = option('kongadminapi');
        if ($kong_ent == "false"){
            //just do OSS calls
            $kongadminapi = $kongadminapi;
            $wsname = "default";
            $workspacecounter = 1;
            $links = "";
        } else{
            // do Enterprise workspace routines
            $wsname = $witem['name'];
            $kongmanager_url= $kong_ent_manager_url . "/" . $wsname;
            $links = '<HR> <a href="deck/edit/'.$cluster_index.'/'.$dbless.'?workspace='.$wsname.'" target=_blank class="btn btn-secondary  btn-sm" style="width:100%;">View/Edit Workspace Config</a>';

            if ($kong_ent_manager_url != "null") $links .= '<HR> <a href="'.$kongmanager_url.'/dashboard" style="width:100%;" target=_blank class="btn btn-info btn-sm">View In Kong Manager</a>';

            $details = '<h4>Workspace Details</h4>Name: '.$wsname . $links;
            // $kongadminapi =  $witem['name'] . '/';
            $kongadminapi = $kongadminapi . '/' . $wsname;
            $nodes .=  "{ id: $idcounter, label: '". $witem['name'] ."',  shape: 'hexagon', color:'mediumpurple',title:'".$wsname."',details:'".$details."',shadow:true,kongtype:'service',kongws:'".$wsname."'},";
            $connections .= "{ from: 1, to: $idcounter, color:{color:'silver', highlight: 'mediumpurple' }},";
            $workspacecounter = $idcounter;
            $idcounter++;
            $links = "";
        }

        //fetch global plugins
        $response = fsl_curl("$kongadminapi/plugins", "GET", "JSON",  NULL, NULL,   NULL, NULL,NULL,  NULL, array($kong_ent_token_name . ":" . $kong_ent_token ) );
        $obj = json_decode($response[2],true);
        $global_plugins = array();

        foreach($obj['data'] as $ritem){
            $total_plugins++;
            //check to see if it is not related to route or service before proceeding
            if (($ritem['route'] == NULL) && ($ritem['service'] == NULL)){
                array_push($global_plugins,$ritem);

            } else{
                // do nothing
            }


        }

        //fetch services
        $response = fsl_curl("$kongadminapi/services", "GET", "JSON",  NULL, NULL,   NULL, NULL,NULL,  NULL, array($kong_ent_token_name . ":" . $kong_ent_token ) );
        $obj = json_decode($response[2],true);


        //process each service
        foreach($obj['data'] as $item) {
            $total_services++;
            // $hosts .= $item['name'] . '<br />';
            $servicename = (empty($item['name']) ? 'NULL': $item['name']);
            $title = "<b>Service Info</b><BR>Service URL: <code>" . $item['protocol'] . '://' . $item['host'] . ':' . $item['port'] . $item['path'] .   '</code><BR>Tags: <code>' . json_encode($item['tags']).  '</code>';
            if (($kong_ent == "true") && ($kong_ent_manager_url != "null")) $links = '<hr><a href="'.$kongmanager_url.'/services/'.$servicename.'" style="width:100%;" target=_blank class="btn btn-info btn-sm">View In Kong Manager</a>';
            if ($canedit == "true") $editbutton = '<a href="deck/edit/'.$cluster_index.'/'.$dbless.'?showid='.$item['id'].'&workspace='.$wsname.'" style="width:100%;" target=_blank class="btn btn-secondary btn-sm">Edit Service Config</a>';
            $details = " <h4>Service Details</h4>Name: <code>". $servicename ."</code><BR>Service URL: <code>" . $item['protocol'] . '://' . $item['host'] . ':' . $item['port'] . $item['path'] . '</code><BR>Updated: <code>' . $item['updated_at']  . '</code><BR>Tags: <code>' . json_encode($item['tags']).  '</code><BR><BR><div class="alert alert-info" role="alert">Run endpoint analysis to see full service details.</div>' . $editbutton . $links ;
            $nodes .=  "{ id: $idcounter, label: '".$servicename."',  shape: 'square', color:'skyblue',title:'".$title."',details:'".$details."',shadow:true,kongtype:'service',kongws:'".$wsname."'},";
            $connections .= "{ from: $workspacecounter, to: $idcounter, color:{color:'silver', highlight: 'deepskyblue'}},";
            $servicecounter = $idcounter;
            $idcounter++;

            $service_plugins = array();
            //for each service get plugins
            $rresponse = fsl_curl("$kongadminapi/services/".$item['id']."/plugins", "GET", "JSON",  NULL, NULL,   NULL, NULL,NULL,  NULL, array($kong_ent_token_name . ":" . $kong_ent_token ) );
            $robj = json_decode($rresponse[2],true);
            foreach($robj['data'] as $ritem){
                array_push($service_plugins,$ritem);


            }

            $rresponse = "";
            $robj = "";

            //for each service get routes
            $rresponse = fsl_curl("$kongadminapi/services/".$item['id']."/routes", "GET", "JSON",  NULL, NULL,   NULL, NULL,NULL,  NULL, array($kong_ent_token_name . ":" . $kong_ent_token ) );
            $robj = json_decode($rresponse[2],true);
            foreach($robj['data'] as $ritem){
                $total_routes++;
                //echo $ritem['name'];
                $rtitle = "<b>Route Info</b><BR>Host: <code>" . json_encode($ritem['hosts'])  . '</code><BR>Path: <code>' . json_encode($ritem['paths']) . '</code><BR>Protocol: <code>' . json_encode($ritem['protocols']) . '</code><BR>Methods: <code>' . json_encode($ritem['methods']) . '</code><BR>Headers: <code>' . json_encode($ritem['headers']).   '</code><BR>Tags: <code>' . json_encode($item['tags']).  '</code>';
                if (($kong_ent == "true") && ($kong_ent_manager_url != "null")) $links = '<HR> <a href="'.$kongmanager_url.'/routes/'. $ritem['id'] .'" style="width:100%;" target=_blank class="btn btn-info btn-sm">View In Kong Manager</a>';
                $routeinspectorlink = '<HR> <a  style="width:100%;" onclick=window.open("inspect/' . $wsname . '/' . $ritem['id'] . '/' . $cluster_index .'/' . $dbless . '") class="btn btn-success btn-sm">Analyze Endpoint</a>';
                if ($canedit == "true") $editbutton = '<a href="deck/edit/'.$cluster_index.'/'.$dbless.'?showid='.$ritem['id'].'&workspace='.$wsname.'" style="width:100%;" target=_blank class="btn btn-secondary btn-sm">Edit Route Config</a>';

                $details = "<h4>Route Info</h4> Name: <code>" . $ritem['name'] . "</code><BR>Host: <code>" . json_encode($ritem['hosts'])  . '</code><BR>Path: <code>' . json_encode($ritem['paths']) . '</code><BR>Protocol: <code>' . json_encode($ritem['protocols']) . '</code><BR>Methods: <code>' . json_encode($ritem['methods']) . '</code><BR>Headers: <code>' . json_encode($ritem['headers']).  '</code><BR>Tags: <code>' . json_encode($ritem['tags']).  '</code><BR><BR><div class="alert alert-info" role="alert">Run endpoint analysis to see full route details.</div>'  . $editbutton . $routeinspectorlink  . $links ;

                $routename = (empty($ritem['name']) ? 'NULL': $ritem['name']);
                $nodes .=  "{ id: $idcounter, label: '".$routename."',  shape: 'dot', color:'lightgreen',title:'".$rtitle."',details:'".$details."',shadow:true,kongtype:'route',kongws:'".$wsname."' },";
                $connections .= "{ from: $servicecounter, to: $idcounter,     color:{color:'silver', highlight: 'MediumSeaGreen'}},";
                $routecounter = $idcounter;
                $idcounter++;

                //for each route get plugins
                $prresponse = fsl_curl("$kongadminapi/routes/".$ritem['id']."/plugins", "GET", "JSON",  NULL, NULL,   NULL, NULL,NULL,  NULL, array($kong_ent_token_name . ":" . $kong_ent_token ) );
                $probj = json_decode($prresponse[2],true);
                foreach($probj['data'] as $pritem){

                    //echo $ritem['name'];
                    $prtitle = "<b>Plugin Info</b><BR>Name: <code>" . ($pritem['name'])  . '</code><BR>ID: <code>' . ($pritem['id']) . "</code>";
                    if (($kong_ent == "true") && ($kong_ent_manager_url != "null")) $links = $links = '<HR> <a href="'.$kongmanager_url.'/plugins/'. $pritem['name'] . '/' . $pritem['id'].'" style="width:100%;" target=_blank class="btn btn-info btn-sm">View In Kong Manager</a>';
                    if ($canedit == "true") $editbutton = '<a href="deck/edit/'.$cluster_index.'/'.$dbless.'?showid='.$pritem['id'].'&workspace='.$wsname.'" style="width:100%;" target=_blank class="btn btn-secondary btn-sm">Edit Plugin Config</a>';

                    //$details = "<h4>Plugin Info</h4>Name: " . ($pritem['name'])  . '<BR>ID: ' . ($pritem['id']) . $links;
                    $color = ($pritem['enabled'] == true ? 'pink': '#fff3f5');
                    $enabled_text = ($pritem['enabled'] == true ? 'true': 'false');
                    $scope = "Route";
                    $config = $routeinspectorlink .'<HR><div class="alert alert-info" role="alert">Run endpoint analysis to see full plugin details.</div>';
                    $details = "<h4>Plugin Details</h4>Name: <code>" . ($pritem['name'])  . "</code><BR>Scope: <code>$scope</code><BR>ID: <code>" . ($pritem['id']) . "</code><BR>Enabled: <code>" . $enabled_text . "</code><BR><BR>" . $editbutton .  $links . $config;
                    $nodes .=  "{ id: $idcounter, label: '".$pritem['name']."',  shape: 'diamond', color:'$color',title:'".$prtitle."',details:'".$details."',shadow:true,kongtype:'plugin',kongws:'".$wsname."' },";
                    //  $connections .= "{ from: $routecounter, to: $idcounter, color:{color:'navy'}},";
                    $connections .= "{ from: $idcounter, to: $routecounter, dashes:true, color:{color:'lightgreen', highlight: 'lightgreen'}},";
                    //  $connections .= "{ from: $idcounter, to: $servicecounter, color:{color:'navy'}},";
                    $idcounter++;
                }
                //   $connections .= "{ from: $idcounter, to: $servicecounter, color:{color:'navy'}},";

                //add global plugins
                foreach($global_plugins as $pritem){
                    //echo $ritem['name'];

                    $prtitle = "<b>Plugin Info</b><BR>Name: <code>" . ($pritem['name'])  . '</code><BR>ID: <code>' . ($pritem['id']) . "</code>";
                    if (($kong_ent == "true") && ($kong_ent_manager_url != "null")) $links ='<HR> <a href="'.$kongmanager_url.'/plugins/'. $pritem['name'] . '/' . $pritem['id'].'" style="width:100%;" target=_blank class="btn btn-info btn-sm">View In Kong Manager</a>';
                    if ($canedit == "true") $editbutton = '<a href="deck/edit/'.$cluster_index.'/'.$dbless.'?showid='.$pritem['id'].'&workspace='.$wsname.'" style="width:100%;" target=_blank class="btn btn-secondary btn-sm">Edit Plugin Config</a>';
                    $color = ($pritem['enabled'] == true ? 'pink': '#fff3f5');
                    $enabled_text = ($pritem['enabled'] == true ? 'true': 'false');
                    $scope = ((($pritem['route'] == NULL) && ($pritem['service'] == NULL) && ($pritem['consumer'] == NULL)) ? 'Global': 'Consumer');
                    $config = $routeinspectorlink .'<HR><div class="alert alert-info" role="alert">Run endpoint analysis to see full plugin details.</div>';
                    $details = "<h4>Plugin Details</h4>Name: <code>" . ($pritem['name'])  . "</code><BR>Scope: <code>$scope</code><BR>ID: <code>" . ($pritem['id']) . "</code><BR>Enabled: <code>" . $enabled_text . "</code><BR><BR>" . $editbutton . $links . $config;
                    $nodes .=  "{ id: $idcounter, label: '".$pritem['name']."',  shape: 'diamond', color:'$color',title:'".$prtitle."',details:'".$details."',shadow:true,kongtype:'plugin',kongws:'".$wsname."' },";
                    //  $connections .= "{ from: $routecounter, to: $idcounter, color:{color:'navy'}},";
                    $connections .= "{ from: $idcounter, to: $routecounter, dashes:true,color:{color:'silver', highlight: 'silver'}},";
                    //  $connections .= "{ from: $idcounter, to: $servicecounter, color:{color:'navy'}},";
                    $idcounter++;
                }

                //add service plugins
                foreach($service_plugins as $pritem){
                    //echo $ritem['name'];

                    $prtitle = "<b>Plugin Info</b><BR>Name: <code>" . ($pritem['name'])  . '</code><BR>ID: <code>' . ($pritem['id']) . "</code>";
                    if (($kong_ent == "true") && ($kong_ent_manager_url != "null")) $links = $links = '<HR> <a href="'.$kongmanager_url.'/plugins/'. $pritem['name'] . '/' . $pritem['id'].'" target=_blank style="width:100%;" class="btn btn-info btn-sm">View In Kong Manager</a>';
                    if ($canedit == "true") $editbutton = '<a href="deck/edit/'.$cluster_index.'/'.$dbless.'?showid='.$pritem['id'].'&workspace='.$wsname.'" style="width:100%;" target=_blank class="btn btn-secondary btn-sm">Edit Plugin Config</a>';

                    //$details = "<h4>Plugin Info</h4>Name: " . ($pritem['name'])  . '<BR>ID: ' . ($pritem['id']) . $links;
                    $color = ($pritem['enabled'] == true ? 'pink': '#fff3f5');
                    $enabled_text = ($pritem['enabled'] == true ? 'true': 'false');
                    $scope = "Service";
                    $config = $routeinspectorlink .'<HR><div class="alert alert-info" role="alert">Run endpoint analysis to see full plugin details.</div>';
                    $details = "<h4>Plugin Details</h4>Name: <code>" . ($pritem['name'])  . "</code><BR>Scope: <code>$scope</code><BR>ID: <code>" . ($pritem['id']) . "</code><BR>Enabled: <code>" . $enabled_text . "</code><BR><BR>" . $editbutton . $links . $config;
                    $nodes .=  "{ id: $idcounter, label: '".$pritem['name']."',  shape: 'diamond', color:'$color',title:'".$prtitle."',details:'".$details."',shadow:true,kongtype:'plugin',kongws:'".$wsname."' },";
                    //  $connections .= "{ from: $routecounter, to: $idcounter, color:{color:'navy'}},";
                    $connections .= "{ from: $idcounter, to: $routecounter, dashes:true,color:{color:'skyblue', highlight: 'skyblue'}},";
                    //  $connections .= "{ from: $idcounter, to: $servicecounter, color:{color:'navy'}},";
                    $idcounter++;
                }
            }
        }
    }

    $clusterdetails .= '<ul class="list-group">  <li class="list-group-item">  <span class="badge badge-secondary" style="float:right;background-color: skyblue;color:#595959">'.$total_services.'</span>Services</li><li class="list-group-item"><span class="badge badge-secondary" style="float:right;background-color: lightgreen;color:#595959">'.$total_routes.'</span>Routes</li><li class="list-group-item"><span class="badge badge-secondary" style="float:right;background-color: pink;color:#595959">'.$total_plugins.'</span>Plugins</li></ul>';
    set('cluster_info', $clusterdetails);
    $nodes .=     "$legend { id: 1, label: \"".$active_cluster."\", size:55, shape: 'image', image: 'https://2tjosk2rxzc21medji3nfn1g-wpengine.netdna-ssl.com/wp-content/themes/konghq/assets/img/home/kong-icon.svg',details:'".$clusterdetails."',color:'white',title:'".$clusterinfo."' },";
    set('nodes',$nodes);
    set('connections',$connections);
    return render("mapview.php");
}

function load_plugins(){
  $plugins = [
    "pre-function"=>10000000000,
"correlation-id"=>100001,
"zipkin"=>100000,
"exit-transformer"=>9999,
"bot-detection"=>2500,
"cors"=>2000,
"session"=>1900,
"oauth2-introspection"=>1700,
"application-registration"=>1007,
"mtls-auth"=>1006,
"kubernetes-sidecar-injector"=>1006,
"jwt"=>1005,
"degraphql"=>1005,
"oauth2"=>1004,
"vault-auth"=>1003,
"key-auth"=>1003,
"key-auth-enc"=>1003,
"ldap-auth"=>1002,
"ldap-auth-advanced"=>1002,
"basic-auth"=>1001,
"openid-connect"=>1000,
"hmac-auth"=>1000,
"request-validator"=>999,
"jwt-signer"=>999,
"ip-restriction"=>990,
"request-size-limiting"=>951,
"acl"=>950,
"collector"=>903,
"rate-limiting-advanced"=>902,
"graphql-rate-limiting-advanced"=>902,
"rate-limiting"=>901,
"response-ratelimiting"=>900,
"request-transformer-advanced"=>802,
"request-transformer"=>801,
"response-transformer-advanced"=>800,
"route-transformer-advanced"=>800,
"response-transformer"=>800,
"kafka-upstream"=>751,
"aws-lambda"=>750,
"azure-functions"=>749,
"graphql-proxy-cache-advanced"=>100,
"proxy-cache-advanced"=>100,
"proxy-cache"=>100,
"forward-proxy"=>50,
"prometheus"=>13,
"canary"=>13,
"http-log"=>12,
"statsd"=>11,
"statsd-advanced"=>11,
"datadog"=>10,
"file-log"=>9,
"udp-log"=>8,
"tcp-log"=>7,
"loggly"=>6,
"kafka-log"=>5,
"syslog"=>4,
"request-termination"=>2,
"post-function"=>-1000,
  ];

  return $plugins;
}
class json2html {

    //usage echo json2html::jsonToDebug($jsonText);
    public static function jsonToDebug($jsonText = '')
    {
        $arr = json_decode($jsonText, true);
        $html = "";
        if ($arr && is_array($arr)) {
            $html .= self::_arrayToHtmlTableRecursive($arr);
        }
        return $html;
    }

    private static function _arrayToHtmlTableRecursive($arr) {
        $str = " <pre><table class='table  table-striped table-sm' style='width:auto'><tbody>";
        foreach ($arr as $key => $val) {
            $str .= "<tr>";
            $str .= "<td>$key</td>";
            $str .= "<td>";
            if (is_array($val)) {
                if (!empty($val)) {
                    $str .= self::_arrayToHtmlTableRecursive($val);
                }
            } else {
                $val2 = rawurlencode($val);
                $str .= "<strong>$val</strong>";
                 //echo $val2 .  " ";
            }
            $str .= "</td></tr>";
        }
        $str .= "</tbody></table></pre> ";

        return $str;
    }

}

?>