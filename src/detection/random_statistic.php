<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
  
// include database and object files
include_once '../config/database.php';
include_once '../objects/detection.php';
  
// instantiate database and detection object
$database = new Database();
$db = $database->getConnection();
  
// initialize object
$detection = new detection($db);
  
// query detections
$stmt = $detection->readRandom();
$num = $stmt->rowCount();
  
// check if more than 0 detection found
if($num>0){
  
    // detections array
    $detections_arr=array();
    $detections_arr["detections"]=array();
  
    // retrieve our table contents
    // fetch() is faster than fetchAll()
    // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        // extract row
        // this will make $row['name'] to
        // just $name only
        extract($row);
  
        $detection_item=array(
            "id" => $id,
            "test" => $test,
            "detection" => $detection,
            "location" => $location,
            "created_at" => $created_at
        );
  
        array_push($detections_arr["detections"], $detection_item);
    }
  
    // set response code - 200 OK
    http_response_code(200);
  
    // show detections data in json format
    echo json_encode($detections_arr);
}
  else{
  
    // set response code - 404 Not found
    http_response_code(404);
  
    // tell the user no products found
    echo json_encode(
        array("message" => "Nothing found.")
    );
}
?>