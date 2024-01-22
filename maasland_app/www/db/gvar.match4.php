<?php
/*
*   GVAR (gpio variables)
*   contains software to hardware tranlations
*/

class GVAR
{
    //mylog('Load gpio voor Flexeria board'.PHP_EOL);
    public static $BOARD_TYPE = "Match4: FlexeriaDuo";
    public static $DASHBOARD_VERSION = "1.7.6";

    //outputs 71 32
    public static $GPIO_DOOR1 = 71; //NUC980_PC7
    public static $GPIO_DOOR2 = 68; //NUC980_PC4
    public static $GPIO_ALARM1 = 65; //NUC980_PC1
    public static $GPIO_ALARM2 = 64; //NUC980_PC0
    public static $RD1_GLED_PIN = 3; //NUC980_PA3  //reader1 gled output 
    public static $RD2_GLED_PIN = 11; //NUC980_PA11  //reader2 gled output
    public static $BUZZER_PIN = 138; //NUC980_PE10  //buzzer output
    public static $RUNNING_LED = 40; //NUC980_PB8  //running led - 0 = on / 1 = off
    public static $OUT12V_PIN = 79; //NUC980_PC15  //output 12v control output

    //inputs
    public static $GPIO_BUTTON1 = 10; //NUC980_PA10
    public static $GPIO_BUTTON2 = 2; //NUC980_PA2
    public static $GPIO_DOORSTATUS1 = 70; //NUC980_PC6 
    public static $GPIO_DOORSTATUS1N = 69; //NUC980_PC5 
    public static $GPIO_DOORSTATUS2 = 67; //NUC980_PC3
    public static $GPIO_DOORSTATUS2N = 66; //NUC980_PC2
    public static $GPIO_MASTER = 38; //NUC980_PB6 - Master Slave switch
    public static $GPIO_FIRMWARE = 32; //NUC980_PB0 - Reset Firmware switch

}
