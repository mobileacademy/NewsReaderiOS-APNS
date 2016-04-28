<?php

namespace App\Http\Controllers;

//require_once 'ApnsPHP/Autoload.php';

use DB;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use ApnsPHP_Abstract;
use ApnsPHP_Push;
use ApnsPHP_Message;

class PushController extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;

    function addToken($token){
        DB::insert('insert into tokens (token) values (?)', [$token] );
    
        return 'token added';
    }

    function sendNotification($message){
        $tokens = DB::table('tokens')->get();

        $push = new ApnsPHP_Push(
            ApnsPHP_Abstract::ENVIRONMENT_SANDBOX,
            'server_certificates_bundle_sandbox.pem'
        );

        $push->setRootCertificationAuthority('entrust_root_certification_authority.pem');

        set_time_limit(60);       
 
        $push->connect();
        
        foreach( $tokens as $token )
        {
            $mess = new ApnsPHP_Message($token->token);

            $mess->setText( $message );

            $mess->setBadge( 0 );

            //$mess->setSound();

            $mess->setExpiry(30);

            $mess->setCustomIdentifier($token->token);            

            $push->add($mess);       
        }

        $push->send();
        
        $push->disconnect();

        $aErrorQueue = $push->getErrors();
        
        if (!empty($aErrorQueue)) {
                var_dump($aErrorQueue);
        }

        return 'message sent';
    }

    function deleteTokens(){
        DB::table('tokens')->delete();
        return 'deleted';
    }
}
