<?php

namespace PhpCoap;

class PacketStream extends \Evenement\EventEmitter
{
	protected $writable = true;
	protected $readable = true;
	protected $sock;

	const MAX_PACKET_SIZE = 4096;


	function __construct( $sock )
	{
		$this->sock = $sock;
		$this->buffer = new PacketBuffer( $this->sock );

		$this->buffer->on( 'sent', function() {
			$this->emit( 'sent', func_get_args() );
		});

		$this->buffer->on( 'sent-all', function() {
			$this->emit( 'sent-all', func_get_args() );
		});

		$this->resume();
	}


	function send( $packet, $peer = null )
	{
		$this->buffer->send( $packet, $peer );
	}

	function resume()
	{
		\React\EventLoop\Loop::addReadStream( $this->sock, array( $this, 'handleRecv' ) );
	}

	function pause()
	{
		\React\EventLoop\Loop::removeReadStream( $this->sock );
	}

	function handleRecv( $sock )
	{
		$pkt = stream_socket_recvfrom( $sock, self::MAX_PACKET_SIZE, 0, $peer );

		if ( $pkt == false )
		{
			$this->emit( 'error', array( "Reading packet from $peer failed" ) );
			return;
		}

		if ( $pkt  !=  "" )
		{
			$this->emit( 'packet', array( $pkt, $peer, $this ) );
		}

	}

	function close()
	{
		$this->buffer->close();
		@fclose( $this->sock );
		\React\EventLoop\Loop::removeReadStream( $this->sock );
		$this->buffer->removeAllListeners();
        $this->removeAllListeners();
	}
}