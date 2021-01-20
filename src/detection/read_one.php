<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');
  
// include database and object files
include_once '../config/database.php';
include_once '../objects/detection.php';
  
// get database connection
$database = new Database();
$db = $database->getConnection();
  
// prepare detection object
$detection = new detection($db);
  
// set ID property of detection to read
$detection->id = isset($_GET['id']) ? $_GET['id'] : die();
  
// read the details of detection to be edited
$detection->readOne();
  
if($detection->id!=null){
    // create array
    $detection_arr = array(
        "id" =>  $detection->id,
        "test" => $detection->test,
        "detection" => $detection->detection,
        "location" => $detection->location,
        "created_at" => $detection->created_at
  
    );
  
    // set response code - 200 OK
    http_response_code(200);
  
    // make it json format
    echo json_encode($detection_arr);
}
  
else{
    // set response code - 404 Not found
    http_response_code(404);
  
    // tell the user detection does not exist
    echo json_encode(array("message" => "detection does not exist."));
}
?>