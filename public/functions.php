<?php

function render($template, $data, $return_string = false){
  $output = null;
  if($template && VitalMustache::$engine){
    $output = VitalMustache::$engine->render($template, $data);

    if(!$return_string){
      echo $output;
    }
  }

  return $output;
}
