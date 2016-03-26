<?php
class linotp extends rcube_plugin
{
  // registered tasks for this plugin.
  public $task = 'login|logout';

  // Dynalogin server and port
  private $linotp_server; 
  private $linotp_port;
  private $linotp_emergencypw;

  function init()
  {
    $rcmail = rcmail::get_instance();
    
    // check whether the "global_config" plugin is available,
    // otherwise load the config manually.
    $plugins = $rcmail->config->get('plugins');
    $plugins = array_flip($plugins);
    if (!isset($plugins['global_config'])) {
      $this->load_config();
    }
    
    // load plugin configuration.
    $this->linotp_server = $rcmail->config->get('linotp_server', 'localhost');
    $this->linotp_port = $rcmail->config->get('linotp_port', 443);
    $this->linotp_emergencypw = $rcmail->config->get('linotp_emergencypw', '');
    
    // login form modification hook.
    $this->add_hook('template_object_loginform', array($this,'linotp_loginform'));

    // register hooks.
    $this->add_hook('authenticate', array($this, 'authenticate'));
  }
  
  function linotp_loginform($content)
  {
    // load localizations.
    $this->add_texts('localization', true);
    
    // import javascript client code.
    $this->include_script('linotp.js');
    
    return $content;
  }
  
  function authenticate($args)
  {  
    $this->authenticate_args = $args;

    $user = $args['user'];
	$pass = $args['pass'];
    $code = get_input_value('_code', RCUBE_INPUT_POST);

    if (!self::linotp_auth($user, $pass, $code, $this->linotp_server, $this->linotp_port, $this->linotp_emergencypw))
    {
      write_log('errors', 'linotp: OTP verfication failed');
      $args['abort'] = true;
    }

    return $args;
  }
  
  function linotp_auth($user, $pass, $code, $server, $port, $emergencypw)
  {
	$sock = fsockopen("ssl://".$server, $port, $errno, $errstr, 30);
	if (!$sock) {
		write_log('errors',"Network error: $errstr ($errno)");
		if ($code == $emergencypw){
			write_log('errors',"Allow user $user due to emergency password");
			return 1;
		}
		write_log('errors',"Disallow user $user due to network error");
		return 0;
	}

	$data = "user=" . urlencode($user) . "&pass=" . urlencode($pass.$code);
	$request = "POST /validate/check HTTPS/1.1\r\n";
	$request .= "Host: ".$server."\r\n";
	$request .= "Content-type: application/x-www-form-urlencoded\r\n";
	$request .= "Content-length: " . strlen($data) . "\r\n";
	$request .= "Connection: close\r\n\r\n";
	fputs($sock, $request);
	fputs($sock, $data);

	$headers = "";
	while ($str = trim(fgets($sock, 4096)))
	$headers .= "$str\n";
	$body = "";
	while (!feof($sock))
	$body .= fgets($sock, 4096);

	fclose($sock);
	
	$pos = strpos ( $body , "\"value\": true");
	if (!$pos) {
		write_log('errors', $user." not authorized");
		return 0;
	}
	if ($pos > 0){
		return 1;
	}
     return 0;
  }

}
