<?php 
set('id', 10);
set('title', 'GPIO');
?>

<div class="content">
    <div class="container-fluid">
       <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">

<h4 class="card-title">GPIO controller</h4>
<p class="card-category">Turn leds on/off on the match board</p>

<a class="btn btn-danger btn-sm" href="/?/gpio/40/1">Off</a>
<a class="btn btn-info btn-sm" href="/?/gpio/40/0">On</a>
D29 Running led     PB8 40
<br>
<a class="btn btn-info btn-sm" href="/?/gpio/138/1">On</a>
<a class="btn btn-danger btn-sm" href="/?/gpio/138/0">Off</a>
12v control output PE10 138
<br>
<a class="btn btn-info btn-sm" href="/?/gpio/2/1">On</a>
<a class="btn btn-danger btn-sm" href="/?/gpio/2/0">Off</a>
Reader1 GLED     PA2 02
<br>
<a class="btn btn-info btn-sm" href="/?/gpio/1/1">On</a>
<a class="btn btn-danger btn-sm" href="/?/gpio/1/0">Off</a>
Reader1 RLED     PA3 03
<br>
<a class="btn btn-info btn-sm" href="/?/gpio/10/1">On</a>
<a class="btn btn-danger btn-sm" href="/?/gpio/10/0">Off</a>
<b>Reader2 GLED     PA10 10</b>
<br>
<a class="btn btn-info btn-sm" href="/?/gpio/11/1">On</a>
<a class="btn btn-danger btn-sm" href="/?/gpio/11/0">Off</a>
Reader2 RLED     PA11 11
<br>
    

<table colspan="4"><tr><td colspan="2">
    <table border="2">
    <tr><td colspan="1">
    <b>J6 TAMPER & PSU</b><br>
    D14 Tamper led  PB13 45<br>
    <a class="btn btn-info btn-sm" href="/?/gpio/45/1">On</a>
    <a class="btn btn-danger btn-sm" href="/?/gpio/45/0">Off</a>
    <br>
    D15 PSU led     PF8 168<br>
    <a class="btn btn-info btn-sm" href="/?/gpio/168/1">On</a>
    <a class="btn btn-danger btn-sm" href="/?/gpio/168/0">Off</a>
    </td><td colspan="1"><pre>
    1 GND
    2 Tamper NO
    3 PSU NO               
    </pre></td></tr>

    <tr><td colspan="1">
    <b>J5 CONTACT</b><br>
    D13 CTA led     PF9 169<br>
    <a class="btn btn-info btn-sm" href="/?/gpio/169/1">On</a>
    <a class="btn btn-danger btn-sm" href="/?/gpio/169/0">Off</a>
    </td><td colspan="1"><pre>
    1 CTA_NO
    2 GND                         
    </pre></td></tr>

    <tr><td colspan="1">
    <b> J4 BUTTON</b><br>
    D12 Button led    ? ?<br>
    </td><td colspan="1"><pre>
    1 12V (fused 500mA)
    2 Button led       
    3 Button NO
    4 GND                        
    </pre></td></tr>

    <tr><td colspan="1">
    <b> J8 RS485 CAT5</b><br>
    D18 485_led   ? ?<br>
    </td><td><pre>
    1 A
    2 B
    3 Z
    4 Y
    5 GND                      
    </pre></td></tr>
    </table>

</td><td colspan="1">
    <table border="1">
    <tr><td>
    <b>J2 RELAY 1</b><br>
    D6 PC4 68<br>
    <a class="btn btn-info btn-sm" href="/?/gpio/68/1">On</a>
    <a class="btn btn-danger btn-sm" href="/?/gpio/68/0">Off</a>
    </td><td colspan="1"><pre>
    1 NC
    2 NO
    3 Common                          
    </pre></td></tr>

    <tr><td colspan="1">
    <b>J3 RELAY 2</b><br>
    D7 Relay2    PC2 66<br>
    <a class="btn btn-info btn-sm" href="/?/gpio/66/1">On</a>
    <a class="btn btn-danger btn-sm" href="/?/gpio/66/0">Off</a>
    <br>
    D11 Sounder led     PC3 67<br>
    <a class="btn btn-info btn-sm" href="/?/gpio/67/1">On</a>
    <a class="btn btn-danger btn-sm" href="/?/gpio/67/0">Off</a>
    </td><td colspan="1"><pre>
    1 NC
    2 NO
    3 Common
    4 Sounder output                         
    </pre></td></tr>

    <tr><td colspan="1">
    <b> J7 ALARM RELAY</b><br>
    D5 Relay alarm  ? ?<br>
    D16 Sense led PB4 36<br>
    <a class="btn btn-info btn-sm" href="/?/gpio/36/1">On</a>
    <a class="btn btn-danger btn-sm" href="/?/gpio/36/0">Off</a>
    <br>
    D17 Arm led   ? ?<br>
    </td><td colspan="1"><pre>
    1 GND
    2 Sense         
    3 Arm
    4 Alarm NC
    5 Alarm NO
    6 Alarm Common                    
    </pre></td></tr>

    </table>

</td></tr></table>

                    </div>
                </div>
            </div>
        </div>              
    </div>
</div>

