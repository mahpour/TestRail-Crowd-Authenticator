<?php
require_once('Crowd.php');
ini_set('display_errors',0);

function authenticate_user($name, $password)
{
    $config = array('app_name'       => 'testrail',
                'app_credential' => 'testrail',
                'service_url'    => 'http://localhost:8095/crowd/services/SecurityServer?wsdl');

    $crowd = new Crowd($config);
    $app_token = $crowd->authenticateApplication();
    
    if (!$app_token) 
      throw new AuthException('Connecting to the crowd security service failed');

    $princ_token = $crowd->authenticatePrincipal($name, $password, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']); 
    if (!$princ_token) 
      return new AuthResultDenied();
    
    $princ = $crowd->findPrincipalByToken($princ_token);
    $email = "";
    
    foreach($princ->attributes->SOAPAttribute as $a=>$b){
      if ($b->name=="displayName") $fullName = $b->values->string;
      if ($b->name=="mail") $email = $b->values->string;
    }
 
    $result = new AuthResultSuccess($email);
    $result->create_account = false;
    $result->name = $fullName;      
    $result->is_admin = true;        
    
    return $result;

}