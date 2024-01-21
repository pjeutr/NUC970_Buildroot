<?php

require_once('lib/limonade.php');
require_once 'lib/i18n.class.php';
require_once 'lib/helpers.php';
require_once '/maasland_app/vendor/autoload.php';
require_once '/maasland_app/www/lib/logic.slave.php';

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
      if  url = api
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
    (strpos(request_uri(), "tests") !== false) ||
    (strpos(request_uri(), "manage") !== false)) { 
      if(true) { //from master || local || ip in controllers //allow cli
        //TODO authentication 
        //apiCalls, do nothing, pass through
        return;
      }
      else {
        echo "error client not allowed";
      }
    }

    //only show dashboard on master    
    if(checkIfMaster()) { 
      //need session to get in dashboard
      if(isset($_SESSION['login'])) { 
        layout('layout/default.html.php');
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
function messagePage($message) {
  set('message', $message);
  return html('message.html.php');
}
function login_page_post() {
  //TODO geen sanitize check
  if(check_password($_POST['password'])) {
    $_SESSION['login'] = $_POST['role'];
    mylog($_SESSION);
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
dispatch('reports', 'report_index');
dispatch('reports_csv', 'report_csv');
dispatch('ledger', 'ledger_index');
dispatch('ledger_csv', 'ledger_csv');
dispatch_delete('ledger/:id', 'ledger_destroy');

//DEV pages
dispatch_get('tests/:name/:params',  'run_script');
function run_script() {
  $cmd = "/maasland_app/tests/".params('name')." ".params('params');
  echo("tests: ".$cmd);
	$r = shell_exec($cmd);
	mylog($r);
    return "<pre>".($r)."</pre>";
}
dispatch_get('dev/:switch',  'set_dev');
function set_dev() {
    $_SESSION["dev"] = params('switch');
    return html('dashboard.html.php');
}
dispatch('manage/info', 'info_page');
function info_page() {
    return phpinfo();
}
dispatch('manage/opcache_reset', 'opcache_flush');
function opcache_flush() {
    return opcache_reset()." <br>opcache cleaned. ";
}
dispatch('manage/cleanup_db', 'cleanup_page');
function cleanup_page() {
    return cleanupReports(30)." records older than 30 days, were removed. ";
}
dispatch_get('manage/update_firmware',  'update_firmware');
function update_firmware() {
  echo("firmware update");
        $r = shell_exec("/maasland_app/scripts/git_replace_private.sh");
        //$r = shell_exec("/maasland_app/scripts/git_replace.sh");
        mylog($r);
    return "<pre>".($r)."</pre>";
}

// main controller
dispatch_get   ('gpio/:id/:state',  'gpio_state');
dispatch_get   ('gpio_key',  'gpio_key');

dispatch_get   ('settings',   'settings_index');
dispatch_put   ('settings/:id', 'settings_update');
dispatch_get   ('settings_download',   'settings_download');
dispatch_post  ('settings_upload',   'settings_upload');
dispatch_get   ('settings_replicate',   'settings_replicate');

dispatch_get   ('network',   'network_index');
dispatch_put   ('network/:id', 'network_update');

//webapi
dispatch('api/version', 'version_page');
function version_page() {
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/x-javascript; charset='.strtolower(option('encoding')));
    return json_encode(["version" => GVAR::$DASHBOARD_VERSION]);
}
dispatch('api/overview', 'overview_page');
function overview_page() {
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/x-javascript; charset='.strtolower(option('encoding')));
    return json_encode([
      "version" => GVAR::$DASHBOARD_VERSION,
      "1" => getOutputStatus(1),
      "2" => getOutputStatus(2),
      "3" => getOutputStatus(3),
      "4" => getOutputStatus(4)
    ]);
}
//webapi / coap alternatives
dispatch_get   ('api/cleanup', 'checkCleanupReports');
dispatch_get   ('api/status/:door', 'outputStatus');
dispatch_get   ('api/output/:door/:state/', 'output');
dispatch_get   ('api/activate/:door/:duration/:gpios/', 'activate');
dispatch_get   ('api/input/:input/:keycode/', 'input');
dispatch_get   ('api/function/:name/:value/', 'callFunction');

//ajax
dispatch_get   ('door/:controller/:door',  'door_open'); //->coap
dispatch_get   ('output/:controller/:output/:state',  'switchOutput'); //->coap
dispatch_get   ('last_reports',   'last_reports');
dispatch_get   ('last_scanned_key.json',   'last_scanned_key');
dispatch_get   ('available_controllers.json',   'available_controllers');

// controllers controller
dispatch_get   ('controller/:id/input/:input/', 'controller_input');
dispatch_put   ('controller/:id', 'input_update');

dispatch_get   ('controllers',          'controllers_index');
dispatch_post  ('controllers',          'controllers_create');
dispatch_get   ('controllers/new',      'controllers_new');
dispatch_get   ('controllers/:id/edit', 'controllers_edit');
dispatch_get   ('controllers/:id',      'controllers_show');
dispatch_put   ('controllers/:id',      'controllers_update');
dispatch_delete('controllers/:id',      'controllers_destroy');

// doors controller
dispatch_get   ('doors',          'doors_index');
dispatch_post  ('doors',          'doors_create');
dispatch_get   ('doors/new',      'doors_new');
dispatch_get   ('doors/:id/edit', 'doors_edit');
dispatch_get   ('doors/:id',      'doors_show');
dispatch_put   ('doors/:id',      'doors_update');
dispatch_delete('doors/:id',      'doors_destroy');

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

// timezones controller
dispatch_get   ('timezones',          'timezones_index');
dispatch_post  ('timezones',          'timezones_create');
dispatch_get   ('timezones/new',      'timezones_new');
dispatch_get   ('timezones/:id/edit', 'timezones_edit');
dispatch_get   ('timezones/:id',      'timezones_show');
dispatch_put   ('timezones/:id',      'timezones_update');
dispatch_delete('timezones/:id',      'timezones_destroy');

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

