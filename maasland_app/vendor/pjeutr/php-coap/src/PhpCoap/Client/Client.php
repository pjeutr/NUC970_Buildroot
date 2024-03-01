<?php

namespace PhpCoap\Client;

use React\EventLoop\Loop;
use PhpCoap\PacketStream;
use PhpCoap\CoapRequest;
use PhpCoap\CoapResponse;


class Client extends \Evenement\EventEmitter
{
	private $gotAck = false;
	private $complete = false;
	
	function __construct()
	{
		$this->connector = new Connector();
	}

	function request( $method, $uri )
	{
		$coapRequest = new CoapRequest( $uri, $method, '' );
		$coapRequest->setCode( $method );
		$coapRequest->setType( CoapRequest::CON );
		
		return new Request( $this->connector, $coapRequest );
	}

	function post( $uri, $data, $callback )
	{
		$req = $this->request( CoapRequest::POST, $uri );
		$req->setPayload( $data );
		$req->on( 'response', function ( $resp ) use ($callback) {
			call_user_func( $callback, $resp->getPayload() );
		});
		$req->send();
	}

	function get( $uri, $callback )
	{
		$req = $this->request( CoapRequest::GET, $uri );

		//prevent hanging/blocking request, with a timout of 3 seconds
		$timeout = Loop::addTimer(3, function () use ($req, $uri, $callback){
		    mylogError("coapCall:TIMEOUT:".$uri);
		    mylogError($callback);
		    $req->close(); //Uncaught Error: Call to a member function close() on null 
		    //return to caller null of -1?, NOT false can mean state of door was not changed
		    call_user_func( $callback, -1);
		});

		$req->on( 'response', function ( $resp ) use ($callback, $timeout) {
			call_user_func( $callback, $resp->getPayload() );
			Loop::cancelTimer($timeout);
		});
		$req->send();
	}

}
