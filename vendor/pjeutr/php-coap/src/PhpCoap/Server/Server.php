<?php

namespace PhpCoap\Server;

use PhpCoap\PacketStream;

class Server extends \Evenement\EventEmitter
{
	private $sessions = array();

	function __construct()
	{
	}

	function receiveUDP( $port, $host = '127.0.0.1' )
	{
		$this->sock = stream_socket_server( sprintf( 'udp://%s:%s', $host, $port ), $errno, $errstr, STREAM_SERVER_BIND );

		if ( $this->sock === false )
		{
			throw \Exception( sprintf( "Error( %s ) : %s", $errno, $errstr ) );
		}

		$this->packetStream = new PacketStream( $this->sock );
		$this->packetStream->on( 'packet', array( $this, 'handlePacket' ) );
	}

	function receiveTCP( $port, $host = '127.0.0.1' )
	{
		$this->sock = stream_socket_server( sprintf( 'tcp://%s:%s', $host, $port ), $errno, $errstr );

		if ( $this->sock === false )
		{
			throw \Exception( sprintf( "Error( %s ) : %s", $errno, $errstr ) );
		}

		$this->packetStream = new PacketStream( $this->sock );
		$this->packetStream->on( 'packet', array( $this, 'handlePacket' ) );
	}

	function handlePacket( $pkt, $peer )
	{
		mylogDebug($this->sessions);
		if (! array_key_exists( $peer, $this->sessions ) )
		{
			$this->sessions[ $peer ] = new RequestHandler( $this->packetStream, $peer  );
			$this->sessions[ $peer ]->on( 'complete', function() use ( $peer ) {
				mylog("handlePacket complete");
				unset( $this->sessions[ $peer ] );
			});
			$this->sessions[ $peer ]->on( 'request', function() {
				mylog("handlePacket request");
				$this->emit( 'request', func_get_args() );
			});
		}

		$this->sessions[ $peer ]->handlePacket( $pkt );
	}
}
