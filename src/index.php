<?php

    // set response code - 400 bad request
    http_response_code(200);
  
    // tell the user
    echo json_encode(array("message" => "hello there"));

  ?>