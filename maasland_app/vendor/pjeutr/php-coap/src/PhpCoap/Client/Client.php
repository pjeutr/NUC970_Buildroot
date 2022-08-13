<?php

namespace PhpCoap\Client;

use PhpCoap\PacketStream;
use PhpCoap\CoapRequest;
use PhpCoap\CoapResponse;


class Client extends \Evenement\EventEmitter
{
	private $gotAck = false;
	private $complete = false;
	
	function __construct( \React\EventLoop\LoopInterface $loop )
	{
		$this->loop = $loop;
		$this->connector = new Connector( $loop );
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
		$timeout = $this->loop->addTimer(3, function () use ($req, $uri){
		    mylog("coapCall:TIMEOUT:".$uri);
		    $req->close();
		});

		$req->on( 'response', function ( $resp ) use ($callback, $timeout) {
			call_user_func( $callback, $resp->getPayload() );
			$this->loop->cancelTimer($timeout);
		});
		$req->send();
	}

}