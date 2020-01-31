<?php

namespace App\Helpers;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;

class JwtAuth{
    
    function __construct() {
        $this->key = '0H98f7fh70|@!~@DFdvd78g88hv_SdfSD3vh08|@dshb';
    }
    
    public function signUp($email, $password, $getToken = false){
        $user = User::where([
            'email' => $email
        ])->first();
        
        if(is_object($user) && password_verify($password, $user->password)){
            
                $token = array(
                    'sub'   => $user->id,
                    'email' => $user->email,
                    'name'  => $user->name,
                    'surname'  => $user->surname,
                    'iat'   => time(),
                    'exp'   => time() + (7*24*60*60)
                );
                $jwt = JWT::encode($token, $this->key);

                if($getToken){
                    $data = $jwt;
                }
                else{
                    $data = JWT::decode($jwt, $this->key, ['HS256']);
                }
        }
        else{
            return false;
        }
        
        return $data;
    }
    
    public function checkToken($jwt, $getIdentity = false){
        $auth = false;
        
        try{
            $jwt = str_replace(['"',"'"],['',''], $jwt);
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
        } catch (\UnexpectedValueException $ex) {
            $auth = false;
        } catch (\DomainException $ex){
            $auth = false;
        } catch (Exception $ex) {
            $auth = false;
        }
        
        if(!empty($decoded) && is_object($decoded) && isset($decoded->sub) ){
            $auth = true;
            if($getIdentity) return $decoded;
        }else{
            $auth = false;
        } 
        
        return $auth;
    }
    
}