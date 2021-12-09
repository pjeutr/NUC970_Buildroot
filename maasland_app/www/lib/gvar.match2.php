<?php
/*
*   GVAR (gpio variables)
*   contains software to hardware tranlations
*/

class GVAR2
{
    //error_log("Load gpio voor Match board");
    public static $BOARD_TYPE = "Match2: Dev1";

    //outputs
    public static $GPIO_DOOR1 = 68; //NUC980_PC4
    public static $GPIO_DOOR2 = 66; //NUC980_PC2
    public static $GPIO_ALARM1 = 65; //NUC980_PC2
    public static $GPIO_ALARM2 = 66; //fake same as door
    public static $RD1_GLED_PIN = 2; //NUC980_PA2   //reader1 gled output
    public static $RD2_GLED_PIN = 10; //NUC980_PA10  //reader2 gled output
    public static $BUZZER_PIN = 79; //NUC980_PC15  //buzzer output
    public static $RUNNING_LED = 40; //NUC980_PB8  //running led
    public static $OUT12V_PIN = 138; //NUC980_PE10  //output 12v control output

    //inputs
    public static $GPIO_BUTTON1 = 170; //NUC980_PF10
    public static $GPIO_BUTTON2 = 169; //NUC980_PF9 - CAT_PIN //contact input
    public static $GPIO_DOORSTATUS1 = 170;//168; //NUC980_PF8 - PSU_PIN //psu input
    public static $GPIO_DOORSTATUS1N = 36; //NUC980_PB4 SENSE_IN
    public static $GPIO_DOORSTATUS2 = 45; //NUC980_PB13 - TAMPER_PIN //tamp input
    public static $GPIO_DOORSTATUS2N = 63; //NUC980_PC0 - ARM_IN
    public static $GPIO_MASTER = 140; //NUC980_PE12 - Master Slave switch
	public static $GPIO_FIRMWARE = 64; //NUC980_PC0 - ARM_IN

}
