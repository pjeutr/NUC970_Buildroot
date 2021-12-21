<?php

namespace PhpCoap;

class CoapPdu
{
	protected $version = 1;
	protected $type = 0;
	protected $code;
	protected $messageId = 0;
	protected $token = "";
	protected $options = array();
	protected $payload = "";

	protected $byteBuffer = array();

	const CON = 0;
	const NON = 1;
	const ACK = 2;
	const RST = 3;

	const GET = '0.01';
	const POST = '0.02';
	const PUT = '0.03';
	const DELETE = '0.04';

	function __construct()
	{
		$this->messageId = self::genMessageId();
	}

	function isAck()
	{
		return ( $this->type == 0x02 );
	}

	function getType()
	{
		return $this->type;
	}

	function setType( $type )
	{
		$this->type = $type;
	}

	function setCode( $string )
	{
		$this->code = $string;
	}

	function getCode()
	{
		return $this->code;
	}

	function getMessageId()
	{
		return $this->messageId;
	}

	function setMessageId( $id )
	{
		$this->messageId = (int) $id;
	}

	function addOption( CoapOption $opt )
	{
		array_push( $this->options, $opt );
	}

	function GetOptions()
	{
		return  $this->options;
	}

	function getMessage()
	{
		$this->compile();
		$rv = '';
		foreach ( $this->byteBuffer as $value) {
			//mylog($value);
			$rv .= pack( 'C', $value );
			//mylog($value."_".$rv);
		}
		return $rv;
	}

	function genCode( $class, $detail )
	{
		$class = intval( $class ) & ( (1 << 3) - 1 );
		$detail = intval( $detail ) & ( (1 << 5) - 1 );
		return ( $class << 5 ) | ( $detail );
	}


	static function genMessageId()
	{
		list( $usec, $sec ) = explode( " ", microtime() );
		return intval( substr( $usec, 2 ) ) & ( pow( 2, 16 ) - 1);
	}

	function compile()
	{
		$i = 0;
/*
#define COAP_HEADER_VERSION(data)  ( (0xC0 & data[0])>>6    ) 1100
#define COAP_HEADER_TYPE(data)     ( (0x30 & data[0])>>4    ) 0011 
#define COAP_HEADER_TKL(data)      ( (0x0F & data[0])>>0    ) 0000 1111
#define COAP_HEADER_CLASS(data)    ( ((data[1]>>5)&0x07)    ) 0111
#define COAP_HEADER_CODE(data)     ( ((data[1]>>0)&0x1F)    ) 0001 1111
#define COAP_HEADER_MID(data)      ( (data[2]<<8)|(data[3]) )
*/

		// Header = 40 01
		$this->byteBuffer[$i] = $this->version << 6;
		$this->byteBuffer[$i] |= $this->type << 4;
		// Token TKL 4567 
		//$this->token = hexdec("1795");
		//mylog("token".$this->token);
		$this->byteBuffer[$i] |= strlen( $this->token );
		$i++;

		// Code 
		list( $class, $detail ) = explode( '.', $this->code );
		mylog("class=".$class." detail=".$detail);
		$this->byteBuffer[$i++] = $this->genCode( $class, $detail );

		// Message Id
		$this->byteBuffer[$i++] = $this->messageId >> 8;
		$this->byteBuffer[$i++] = $this->messageId & (( 1 << 8 ) - 1 );
mylog($this->byteBuffer);
		// Token
		if ( strlen( $this->token ) != 0 )
		{
			throw Exception( 'Not Implemented!' );
		}

		// Options
		CoapOption::sort( $this->options );
mylog($this->options);
		$prevNo = 0;
		foreach ($this->options as $opt )
		{
			$delta = $opt->getOptionNumber() - $prevNo;
			mylog($opt->getOptionNumber());
			mylog("delta=".$delta);
			if ( $delta < 13 )
			{
				$this->byteBuffer[$i] = $delta << 4;
				if ( $opt->length() < 13 )
				{
					//als we er 7 afhalen werkt het wel...
					$this->byteBuffer[$i] |= $opt->length() - 7;
					$lenExt = false;
				}
				else
				{
					$lenExt = ( $opt->length() > 255 ) ? 14 : 13;
					$this->byteBuffer[$i] |= $lenExt;
				}
				$i++;
			}
			else
			{
				throw Exception( "Not Implemented!" );
			}
mylog("lenExt=".$lenExt );
mylog($this->byteBuffer);
			if ( $lenExt == 13 )
			{
				//byte te veel?
				$this->byteBuffer[$i++] = $opt->length() - 13;
			}
mylog($this->byteBuffer);
			foreach( $opt->getByteArray() as $byte )
			{
				$this->byteBuffer[$i++] = $byte;
			}

			$prevNo = $opt->getOptionNumber();
		}

		if ( $this->payload !== "" )
		{
			$this->byteBuffer[$i++] = (1 << 8 ) - 1;

			foreach( unpack( 'C*', $this->payload ) as $byte )
			{
				$this->byteBuffer[$i++] = $byte;
			}
		}
mylog($this->byteBuffer);		
	}

	static function fromBinString( $binString )
	{
		$pdu = new self();
		
		$buf = unpack( 'C*', $binString );
		
		$i = 1;
		$pdu->version = $buf[$i] >> 6;
		$pdu->type = ( $buf[$i] >> 4 ) & 0x03;

		$tkl = $buf[$i] & 15;

		$i++;
		$pdu->code = sprintf( '%01d', $buf[$i] >> 5 );
		$pdu->code .= '.' . sprintf( '%02d', $buf[$i] & 0x07 );

		$i+= 2;
		$pdu->messageId = ( $buf[$i-1] << 8 ) | ( $buf[$i] );

		if ( $tkl > 0 )
		{
			for ( $i = $i; $i<= $tkl; $i++ )
			{
				$pdu->token .= unpack( 'C', $buf[$i] );
			}
		}

		$i += $tkl + 1;

		if ( isset( $buf[$i] ) )
		{
			$prev = 0;

			while ( $i <= count( $buf ) && $buf[ $i ] != 0xFF )
			{
mylog($buf);		
mylog($i." previous=".$prev );		
				$prev = $pdu->parseOption( $buf, $i, $prev );
			}

			if ( $i<= count( $buf ) && $buf[$i] == 0xFF )
			{
				$pdu->readPayload( $buf, $i+1 );
			}

		}
		else
		{
			// empty
		}
		return $pdu;

	}

	function parseOption( $buf, &$start, $prevNo )
	{
		$i = $start;
		$optNo = ( $buf[$i] >> 4 );
		$optLen = $buf[$i] & 0x0F;

		$i++;

		if ( $optNo == 13 )
		{
			$optNo = 13 + $buf[$i];
			$i++;
		} else if ( $optNo == 14 )
		{
			$optNo = 269 + ( $buf[$i] << 8 ) + $buf[ $i + 1 ];
			$i++;
		}

		$optNo += $prevNo;

		if ( $optLen == 13 )
		{
			$optLen = 13 + $buf[$i];
			$i++;
		} 

		if ( $optLen == 14 )
		{
			$optLen = 269 + ( $buf[$i] << 8 ) + $buf[ $i + 1 ];
			$i++;
		}

		$value = "";
mylog($buf);
		for ( $j = 0; $j < $optLen; $j++ )
		{			
mylog('$buf['.$i.' + '.$j.']');	
			//PHP Notice:  Undefined offset: 124 in /maasland_app/vendor/pjeutr/php-coap/src/PhpCoap/CoapPdu.php on line 266
			//hier gaat het fout
			//dus hier er 5 af
			$value .= sprintf( '%c', $buf[$i + $j]);
		}
		$i += $j;

		$start = $i;

		$opt = new CoapOption( $optNo, $value );
		array_push( $this->options, $opt );

		return $optNo;
	}

	function readPayload( $buf, $start )
	{
		$this->payload = "";

		for( $i = $start; $i<=count($buf); $i++ )
		{
			$this->payload .= pack( 'C', $buf[$i] );
		}
	}

	function getPayload()
	{
		return $this->payload;
	}

	function setPayload( $string )
	{
		$this->payload = $string;
	}
}
