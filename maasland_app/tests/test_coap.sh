#!/bin/bash


    # // button_1 = 170 
    # // button_2 = 169 
    # // sensor_1 = 168 
    # // sensor_2 = 45
    # // writer > coap://master/input/1/1234 >  handleUserAccess
    # // getGPIO < checkOutput < coap://slave/status/66-2 < checkOutput
    # // setGPIO < operateOutput < coap://slave/output/2/1/2-10 < handleUserAcess
    # // setGPIO < activateOutput < coap://slave/activate/2/5/2-10 < checkAndHandleInput
    # // door_1 = 68
    # // door_2 = 66
    # // alarm_1 = 65
    # // alarm_2 = 66

echo "Test coap calls ${BASH_VERSION}..."
#coap-client -m get coap://192.168.178.137/.well-known/core

master="127.0.0.1"
#master="192.168.178.184"
slave="192.168.178.179"

echo "Test Slave"
#match2 
# coap-client -m get coap://$slave/status_68-66
# coap-client -m get coap://$slave/output_1_1
# coap-client -m get coap://$slave/output_1_0
# coap-client -m get coap://$slave/activate_2_6_2-10

#match4 
coap-client -m get coap://$slave/status_66-67-69-70 #show status of sensors
coap-client -m get coap://$slave/output_1_1
coap-client -m get coap://$slave/output_1_0
coap-client -m get coap://$slave/output_3_1
coap-client -m get coap://$slave/output_3_0
coap-client -m get coap://$slave/activate_1_6_3-11

echo "Test Master"
coap-client -m get coap://$master/x_3
coap-client -m get coap://$master/x_1_3333
coap-client -m get coap://$master/x_2_2310811




#coming from the master going to slave
# coap-client -m get coap://$master/output/2/1/2-10
# echo "-> gpio status door1-2 reader_leds1-2 68-66-2-10"
# coap-client -m get coap://192.168.178.137/status/68-66-2-10
# echo "-> Close Door 2"
# coap-client -m get coap://192.168.178.137/output/2/0/2-10
# #will become obsolete
# echo "-> Activate Door 2 and leds on both readers for 1s "
# coap-client -m get coap://192.168.178.137/activate/2/1/2-10

#going to master
# echo "-> button 1 pressed "
# coap-client -m get coap://192.168.178.137/input/3
# echo "-> button 2 pressed "
# coap-client -m get coap://192.168.178.137/input/4
# echo "-> reader 1 with code 3333 - Cinderella - redirects to " 
# coap-client -m get coap://192.168.178.137/input/1/3333
# echo "-> reader 2 with code 3333 - Cinderella"
# coap-client -m get coap://192.168.178.137/input/2/3333



# * coap-client -m get coap://192.168.178.137/output/2/1 = Door 2 for open
# * coap-client -m get coap://192.168.178.137/output/1/0 = Door 1 for close
# * TODO-coap-client -m get coap://192.168.178.137/output/1/2|on|off/2-10-66-79 = Door 1 for 2 seconds
# * coap-client -m get coap://192.168.178.137/activate/1/2 = Door 1 for 2 seconds
# * coap-client -m get coap://192.168.178.137/activate/2/5/2-10-66-79 = Door 2 all leds and buzzer for 5 seconds

