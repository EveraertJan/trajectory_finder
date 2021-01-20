<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');
  
// include database and object files
include_once '../config/database.php';
include_once '../objects/record.php';
  
// get database connection
$database = new Database();
$db = $database->getConnection();
  
// prepare record object
$record = new record($db);
  
// set ID property of record to read
$record->id = isset($_GET['id']) ? $_GET['id'] : die();
  
// read the details of record to be edited
$record->readOne();
  
if($record->id!=null){
    // create array
    $record_arr = array(
        "id" =>  $record->id,
        "person_id" => $record->person_id,
        "path" => $record->path,
        "location" => $record->location,
        "created_at" => $record->created_at
  
    );
  
    // set response code - 200 OK
    http_response_code(200);
  
    // make it json format
    echo json_encode($record_arr);
}
  
else{
    // set response code - 404 Not found
    http_response_code(404);
  
    // tell the user record does not exist
    echo json_encode(array("message" => "record does not exist."));
}
?>