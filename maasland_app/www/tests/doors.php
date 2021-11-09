<?php

test_case("Doors");
  test_case_describe("Testing doors.");
  define('URL_FOR_OUTPUT_TEST', TESTS_DOC_ROOT.'doors');  
    
   function test_doors_redirect()
   {
     $url = 'http://localhost/?doors';
     
     $response = test_request($url, 'GET');
     error_log($response);
     //WERKT NIET assertion is altijd success
     assert_equal($response, '/Hello World/');
   }

  function Xtest_doors_render()
  {
    $url = $doc_root.'?test';
    $response = test_request($url, 'GET');
    echo $response;

    $response =  test_request('https://www.google.com/', 'GET');
    error_log("df");
    echo "af";
    error_log($response);
    assert_match($response, "kees");
    
    # Testing rendering with a view (inline function case)
    $view = '_test_output_html_hello_world';
    $html = render($view);
    assert_match("/Hello World/", $html);
    assert_no_match("/$q_lorem/", $html);
    $html = render($view, null, array($lorem));
    assert_no_match("/$q_lorem/", $html);
    $html = render($view, null, array('lorem' => $lorem));
    assert_match("/$q_lorem/", $html);
    
  }
  
  function test_output_layout()
  {
    $response =  test_request(TESTS_DOC_ROOT.'index.php/doors', 'GET');
    $o = <<<HTML
<html><body>
hello!</body></html>
HTML;
    assert_equal($response, $o);
  }
  
end_test_case();


# Views and Layouts

function _test_output_html_my_layout($vars){ extract($vars);?> 
<html>
<head>
	<title>Page title</title>
</head>
<body>
	<?php echo $content ?>
</body>
</html>
<?php }

function _test_output_html_hello_world($vars){ extract($vars);?> 
<p>Hello World</p>
<?php if(isset($lorem)): ?><p><?php echo $lorem?></p><?php endif;?>
<?php }
