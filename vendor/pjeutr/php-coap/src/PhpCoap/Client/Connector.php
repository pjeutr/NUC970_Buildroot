<?php

namespace PhpCoap\Client;

use React\Promise;
use React\Promise\Deferred;
use React\EventLoop\Loop;
use PhpCoap\PacketStream;
use PhpCoap\CoapRequest;
use PhpCoap\CoapResponse;


class Connector extends \Evenement\EventEmitter
{

	function __construct()
	{
	}

	function create( $host, $port )
	{
		$deferred = new Deferred();

		$sock = stream_socket_client( sprintf( 'udp://%s:%s', $host, $port ), $errno, $errstr );

		if ( $sock == false )
		{
			$this->emit( 'error', array( $errno, $errstr ) );
			return;
		}

		Loop::addWriteStream( $sock, function( $sock ) use ( $deferred ) {
			Loop::removeWriteStream( $sock );

			$deferred->resolve( $sock );
		});

		return $deferred->promise()->then( array( $this, 'handleConnect' ) );

	}

	function handleConnect( $sock )
	{
		return new PacketStream( $sock );
	}
}