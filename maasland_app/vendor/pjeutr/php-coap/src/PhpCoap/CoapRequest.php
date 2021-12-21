<?php

namespace PhpCoap;

class CoapRequest extends CoapPdu
{
	
	private $uriParts;
	private $ack = false;

	function __construct( $uri, $method, $data )
	{
		$parts = parse_url( $uri );

		if ( $parts['scheme'] != 'coap' )
		{
			throw \Exception( 'Bad Uri: ' . $uri );
		}

		$this->uriParts = $parts;

		//$this->addOption( new CoapOption( 3, $this->getHost() ) );
mylog($this->uriParts['path']);
mylog(substr( $this->uriParts['path'], 1 ));

		$pieces = explode('/', substr( $this->uriParts['path'], 1 ));


		$uri = $pieces[0].chr(strlen($pieces[1])).$pieces[1].chr(strlen($pieces[2])).$pieces[2];
mylog("uri=".$uri);
		//$uri = substr( $this->uriParts['path'];

		$this->addOption( new CoapOption( 11, $uri) );
		if ( isset( $this->uriParts['query'] ) )
		{
			$this->addOption( new CoapOption( 15, $this->uriParts['query'] ) );
		}
		mylog("_optionqs_");
mylog(json_encode($this->GetOptions(), JSON_OBJECT_AS_ARRAY));
		$this->setPayload( $data );
		parent::__construct();
	}

	function getHost()
	{
		return $this->uriParts['host'];
	}

	function getPort()
	{
		if ( isset( $this->uriParts['port'] ) )
		{
			return $this->uriParts['port'];
		}
		else
		{
			return 5683;
		}
	}

}