<?php

require_once('lib/limonade.php');
require_once 'lib/i18n.class.php';
require_once 'lib/helpers.php';
require_once '/maasland_app/vendor/autoload.php';
require_once '/maasland_app/www/lib/logic.slave.php';
//needed for firmware reset
define('ISPC_CLASS_PATH', 'lib/vlibtemplate');
require "lib/vlibtemplate/tpl.inc.php";

//need session to determing route authentication
session_start();

function configure() {
    //configure and initialize gpio 
    configureGPIO();

    //initialize database connection
    configDB();
}

function before($route = array())
{   
    //Trying to make a session persistant, mainly to preserver language choice.
    //But setting here doesn't seem te work. Do it in php.ini
    //TODO make logout timout after 1 hour?
    ini_set('session.cookie_lifetime', 2147483647);//2147483647 is absolute max
    ini_set('session.gc_maxlifetime', 2147483647);
    ini_set('session.save_path', '/maasland_app/www/sessions');
    //ini_set('session.cookie_path', '/maasland_app/www/sessions');

    //route should come with function, is not, do it manually. Needed for $route['role']
    $route = route_find(request_method(), request_uri());

    //start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }  
    //Make sure a lang session is set, default to english
    if(! isset($_SESSION["lang"]) ) {
      $_SESSION["lang"] = "en";
    }
    //Load language file
    //$i18n = new i18n();
    $i18n = new i18n('languages/lang_{LANGUAGE}.ini', 'langcache/');// language file path
    $i18n->setFallbackLang('en');
    //$i18n->setForcedLang($_SESSION["lang"]); // force english, even if another user language is available
    $i18n->setMergeFallback(false); // make keys available from the fallback language
    $i18n->init();

    mylog("l=".request_method()."_x_".request_uri()."_".php_sapi_name()."_".$_SESSION["lang"]);

    /*
      if factoryResetSwitch
        showMessage factoryeset

      //calls from master,slave or tests
      if  url = api|manage
        if from master || local || ip in controllers  
          apiCalls
        else
          error client not allowed
      //only show dashboard on master     
      if masterSwitch
        if session 
          dashboard
          ajax calls
        else
          login
      else
        showMessage slave
    */

    if (checkIfFactoryReset()) {
        //doFactoryReset(); //no proper file permissions to do from webserver
        echo messagePage(L("message_factoryreset"));
        stop_and_exit();
    }

    //calls from master,slave or tests
    if((strpos(request_uri(), "api") !== false) ||
    (strpos(request_uri(), "manage") !== false)) { 
      $mip=getMasterControllerIP();
      $rip=$_SERVER['HTTP_HOST'];
      mylog($mip." = ".$rip);
      //if($mip==$rip) {
      if(true) { //from master || local || ip in controllers //allow cli
        //TODO authentication 
        //apiCalls, do nothing, pass through
        return;
      }
      else {
        $title = "403 Forbidden";
        mylog($title.": ".$_SESSION['login']." < ".$route["role"]);
        echo messagePage(L("message_no_auth"), $title);
        stop_and_exit();
      }
    }

    //only show dashboard on master    
    if(checkIfMaster()) { 
      //need session to get in dashboard
      if(isset($_SESSION['login'])) { 
        layout('layout/default.html.php');

        //check if user role has permission to view this page
        mylog($_SESSION['login']." < ".$route["role"]);
        if($_SESSION['login'] < $route["role"]) {
          $title = "401 Unauthorized";
          mylog($title.": ".$_SESSION['login']." < ".$route["role"]);
          echo messagePageAuth(L("message_no_auth"), $title);
          stop_and_exit();
        }

      } else { //force login
        if(request_method() == "POST") {
          //Allow login POST to submit. 
        } else {
          echo login_page();
          stop_and_exit();
        }
      }
    } else {
      //if not master, show slave page
      echo messagePage(L("message_slave"));
      stop_and_exit();
    }

/*
    //Allow login POST to submit. TODO needs check on uri=login?
    if(request_method() != "POST") {
      if (checkIfFactoryReset()) {
          doFactoryReset();
          echo messagePage(L("message_factoryreset"));
          stop_and_exit();
      }
      if (strpos(request_uri(), "api") !== false) {
          //TODO authentication 
          //ajax page, do nothing
          return;
      }
      //need session to get in dashboard
      if(! checkIfMaster()) { //if not master, show slave page

        //} elseif(strpos(request_uri(), '/door/') !== false) { //allow cli
        //} elseif(request_uri() == "/door/1") { //allow cli
        echo messagePage(L("message_slave"));
        //layout('layout/default.html.php');
        stop_and_exit();
      } elseif(isset($_SESSION['login'])) { //check if user is logged in
        layout('layout/default.html.php');
      } else { //force login
        echo login_page();
        stop_and_exit();
      }
    }
*/

}

//layout('layout/default.html.php');

function after($output) {
    // $time = number_format( (float)substr(microtime(), 0, 10) - LIM_START_MICROTIME, 6);
    // $output .= "<!-- page rendered in $time sec., on " . date(DATE_RFC822)."-->";
    return $output;
}

function not_found($errno, $errstr, $errfile=null, $errline=null)
{
  $html = '<p>'
        . h($errstr)
        . '"></p>';
  set('title', 'FOUT 404');
  return html($html);
}

function server_error($errno, $errstr, $errfile=null, $errline=null)
{
  $html = '<p>'
        . $errno . "<br>"
        . $errstr . "<br>"
        . $errfile . "<br>"
        . $errline . "<br>"
        . '</p>';
  set('title', 'FOUT 500');
  return html($html);
}

dispatch_get('login', 'login_page');
dispatch_get('logout', 'logout_page');
dispatch_post('login', 'login_page_post');
function login_page() {
  return html('login.html.php');
  //return render('login.html.php', 'splash_layout.php');
}
function messagePage($message, $title = "Error") {
  set('title', $title);
  set('message', $message);
  return html('message.html.php');
}
function messagePageAuth($message = "unkown", $title = "unkown", $id = 0) {
    set('id', $id);
    set('title', $title);
    set('content', $message);
    return html('page.html.php');
}
function login_page_post() {
  //mylog('login '.$_POST['role'].' p= '.$_POST['password']);
  if($_POST['role'] == "admin") {
    //check if super role login
    if($_POST['password'] == "M44sl@ndDuo") {
      $_SESSION['login'] = ROLE::$SUPER;
      redirect_to('http://'.$_SERVER['HTTP_HOST'].'/');
    }
    //check if admin role login
    if(check_admin_password($_POST['password'])) {
      $_SESSION['login'] = ROLE::$ADMIN;
      redirect_to('http://'.$_SERVER['HTTP_HOST'].'/');
    }
  }
  //check if user role login
  if(check_password($_POST['password'])) {
    $_SESSION['login'] = ROLE::$USER;
    redirect_to('http://'.$_SERVER['HTTP_HOST'].'/');
  } else {
    flash("error", "The password was wrong");
    set('message', "The password was wrong");
    return render('login.html.php', null);
  //return render('login.html.php', 'splash_layout.php');
  }
}
function logout_page() {
  //unset($_SESSION['login']);
  $_SESSION['login'] = null;
  return render('login.html.php', null);
}


/* page called without login session */

//TODO authentication, call form master with a token? or check for master ip 
dispatch_get   ('manage/network',   'network_slave');
dispatch_get   ('manage/network/:id', 'network_slave'); //user refresh can create a get after the v put
dispatch_put   ('manage/network/:id', 'network_update');

//webapi on slave
dispatch('api/overview', 'overview_page');//used on master dashboard, to show slave status/version

//webapi / coap alternatives
dispatch_get   ('api/status/:door', 'outputStatus');// not used?
dispatch_get   ('api/output/:door/:state/', 'output');//<- open/close form master
dispatch_get   ('api/activate/:door/:duration/', 'activate');//<- pulse from master
dispatch_get   ('api/input/:input/:keycode/', 'input');
dispatch_get   ('api/function/:name/:value/', 'callFunction');



/* default user */
$role = ROLE::$USER;

//set language session
dispatch_get('lang/:lang',  'set_lang');
function set_lang() {
    mylog('http://'.$_SERVER['HTTP_HOST'].'/ == '.params('lang'));
    $_SESSION["lang"] = params('lang');
    redirect_to('http://'.$_SERVER['HTTP_HOST'].'/');
}

// main controller
dispatch('/', 'dashboard_page');
dispatch('dash', 'dashboard_page');
function dashboard_page() {
  return html('dashboard.html.php');
}

//TODO add multi_timezone
dispatch('newtime', 'holidays_page');
function holidays_page() {
  set('holidays', find_holidays());
  return html('holidays.html.php');
}

//ajax
dispatch_get   ('door/:controller/:door',  'door_open'); //<- dash pulse button
dispatch_get   ('output/:controller/:output/:state',  'switchOutput'); //<- dash open/close
dispatch_get   ('last_scanned_key.json',   'last_scanned_key'); //<- users/new
dispatch_get   ('available_controllers.json',   'available_controllers');//<- controllers/new

/* user dashboard pages */
// users controller
dispatch_get   ('users',          'users_index');
dispatch_post  ('users',          'users_create');
dispatch_get   ('users/new',      'users_new');
dispatch_get   ('users/:id/edit', 'users_edit');
dispatch_get   ('users/:id',      'users_show');
dispatch_put   ('users/:id',      'users_update');
dispatch_delete('users/:id',      'users_destroy');

// groups controller
dispatch_get   ('groups',          'groups_index');
dispatch_post  ('groups',          'groups_create');
dispatch_get   ('groups/new',      'groups_new');
dispatch_get   ('groups/:id/edit', 'groups_edit');
dispatch_get   ('groups/:id',      'groups_show');
dispatch_put   ('groups/:id',      'groups_update');
dispatch_delete('groups/:id',      'groups_destroy');
dispatch_post  ('grules',          'grules_create');
dispatch_put   ('grules/:id',      'grules_update');
dispatch_delete('grules/:id',      'grules_destroy');

// holidays controller
dispatch_get   ('holidays',          'holidays_index');
dispatch_post  ('holidays',          'holidays_create');
dispatch_get   ('holidays/new',      'holidays_new');
dispatch_get   ('holidays/:id/edit', 'holidays_edit');
dispatch_get   ('holidays/:id',      'holidays_show');
dispatch_put   ('holidays/:id',      'holidays_update');
dispatch_delete('holidays/:id',      'holidays_destroy');

dispatch('ledger', 'ledger_index');
dispatch('ledger_csv', 'ledger_csv');
dispatch_delete('ledger/:id', 'ledger_destroy');

dispatch('reports', 'report_index');
dispatch('reports_csv', 'report_csv');

/* admin dashboard pages */
$role = ROLE::$ADMIN;
dispatch_get   ('controllers',          'controllers_index', $role);
dispatch_post  ('controllers',          'controllers_create', $role);
dispatch_get   ('controllers/new',      'controllers_new', $role);
dispatch_get   ('controllers/:id/edit', 'controllers_edit', $role);
dispatch_get   ('controllers/:id',      'controllers_show', $role);
dispatch_put   ('controllers/:id',      'controllers_update', $role);
dispatch_delete('controllers/:id',      'controllers_destroy', $role);
//Change input values, happens on doors form 
dispatch_put   ('controller/:id', 'input_update', $role);

// doors controller
dispatch_get   ('doors',          'doors_index', $role);
dispatch_post  ('doors',          'doors_create', $role);
dispatch_get   ('doors/new',      'doors_new', $role);
dispatch_get   ('doors/:id/edit', 'doors_edit', $role);
dispatch_get   ('doors/:id',      'doors_show', $role);
dispatch_put   ('doors/:id',      'doors_update', $role);
dispatch_delete('doors/:id',      'doors_destroy', $role);

// timezones controller
dispatch_get   ('timezones',          'timezones_index', $role);
dispatch_post  ('timezones',          'timezones_create', $role);
dispatch_get   ('timezones/new',      'timezones_new', $role);
dispatch_get   ('timezones/:id/edit', 'timezones_edit', $role);
dispatch_get   ('timezones/:id',      'timezones_show', $role);
dispatch_put   ('timezones/:id',      'timezones_update', $role);
dispatch_delete('timezones/:id',      'timezones_destroy', $role);

dispatch_get   ('settings',   'settings_index', $role);
dispatch_put   ('settings/:id', 'settings_update', $role);
dispatch_get   ('settings_download',   'settings_download', $role);
dispatch_post  ('settings_upload',   'settings_upload', $role);
dispatch_get   ('settings_replicate',   'settings_replicate', $role);

dispatch_get   ('network',   'network_index', $role);
dispatch_get   ('network/:id', 'network_index', $role); //user refresh can create a get after the v put
dispatch_put   ('network/:id', 'network_update', $role);

dispatch_get('views/status',  'views_status');
function views_status() {
  exec('/maasland_app/tests/status.php 2>&1',$output, $retval);
  return messagePageAuth("<pre>".(implode("<br>",$output))."</pre>", $title = "Status", 9);
}

dispatch_get('views/network',  'views_network');
function views_network() {
  exec('/maasland_app/tests/network.sh 2>&1',$output, $retval);
  return messagePageAuth("<pre>".(implode("<br>",$output))."</pre>", $title = "Network", 10);
}

/* super dashboard pages */
$role = ROLE::$SUPER;
dispatch('super/info', 'info_page', $role);
function info_page() {
    return phpinfo();
}
dispatch('super/opcache_reset', 'opcache_flush', $role);
function opcache_flush() {
    return opcache_reset()." <br>opcache cleaned. ";
}

dispatch('super/cleanup_db', 'cleanup_page', $role);
function cleanup_page() {
    return cleanupReports(30)." records older than 30 days, were removed. ";
}  
dispatch_get('super/update_firmware',  'update_firmware', $role);
function update_firmware() {
  echo("firmware update");
        $r = shell_exec("/maasland_app/scripts/git_replace_private.sh");
        //$r = shell_exec("/maasland_app/scripts/git_replace.sh");
        mylog($r);
    return "<pre>".($r)."</pre>";
}
//call scripts in tests directory
// http://ip/?/super/stat.sh/20
// http://ip/?/super/analysis.sh/2
dispatch_get('super/:name/:params',  'run_script', $role);
function run_script() {
  $cmd = "/maasland_app/tests/".params('name')." ".params('params');
  mylogDebug($cmd);
  exec($cmd.' 2>&1',$output, $retval);
  mylogDebug($output);
  set('id', params('id'));
  set('title', params('name'));
  set('content', "<pre>".(implode("<br>",$output))."</pre>");
  return html('page.html.php');
}
//DEV pages
dispatch_get('dev/:switch',  'set_dev', $role);
function set_dev() {
    $_SESSION["dev"] = params('switch');
    return html('dashboard.html.php');
}




try {
  //run application
  run();
} catch (PDOException $e) {
  //check for db errors 
  mylog($e);
  //TODO could give upload option, but there is no way to authenticate with a broken db
  echo messagePage(L("message_db_error"));
} catch (Exception $e) {
  mylog($e);
  echo messagePage(L("message_unkown_error"));
}

