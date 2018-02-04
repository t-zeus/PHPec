<?php
use PHPUnit\Framework\TestCase;

require '../src/App.php';

define('JWT_SECRET','secret');

require '../src/middleware/jwt.php';


class JwtTest extends TestCase {

	function setUp(){
		$this -> app = new \PHPec\App();
		$this -> jwt =  new \PHPec\Jwt(['X-Skip'=>"skip"]);
	}
	function testAuthorization(){
		$this -> app -> req = new stdClass;
		$this -> app -> req -> header = ['X-Skip'=> '']; 
		$this -> jwt -> begin($this -> app);
		$this -> assertEquals(401,$this -> app -> status);
		//use Authorization
		$this -> app -> req = new stdClass;
		$this -> app -> req -> header = ['Authorization'=> 'xxxxx']; 
		$this -> jwt -> begin($this -> app);
		$this -> assertEquals(401,$this -> app -> status);
	}
	function testNotAuthorization(){
		$this -> app -> req = new stdClass;
		$this -> app -> req -> header = ['X-Skip'=> 'skip']; 
		$this -> jwt -> begin($this -> app);
		$this -> assertEquals(200,$this -> app -> status);
	}
	function testToken(){
		$this -> app -> req = new stdClass;
		$this -> app -> req -> header = ['X-Skip'=> 'skip']; 
		$this -> jwt -> begin($this -> app);
		$this -> app -> jwtPayload = ['uid'=>1234,'exp' => time()+7200];
		$this -> jwt -> end($this -> app);
		$this -> assertEquals(200,$this -> app -> status);
		$this -> assertArrayHasKey('Authorization', $this -> app -> resHeaders);
		//解释uid
		list(,$payload,) = explode(".",$this -> app -> resHeaders['Authorization']);
		$payload = json_decode(base64_decode($payload),true);
		$this -> assertArrayHasKey('uid', $payload);
		$this -> assertArrayHasKey('exp', $payload);
		$this -> assertArrayHasKey('iat', $payload);
		$this -> assertEquals($this -> app -> jwtPayload['uid'],$payload['uid']);
		$this -> assertEquals($this -> app -> jwtPayload['exp'],$payload['exp']);
		//auth
		$this -> app -> req = new stdClass;
		$this -> app -> req -> header = ['Authorization'=> $this -> app -> resHeaders['Authorization']]; 
		$this -> jwt -> begin($this -> app);

		$this -> assertArrayHasKey('uid', $this -> app -> reqPayload);
		$this -> assertArrayHasKey('exp', $this -> app -> reqPayload);
		$this -> assertArrayHasKey('iat', $this -> app -> reqPayload);
		$this -> assertEquals($this -> app -> reqPayload, $this -> app -> reqPayload);

	}
}
?>