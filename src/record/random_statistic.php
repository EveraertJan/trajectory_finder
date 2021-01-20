<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
  
// include database and object files
include_once '../config/database.php';
include_once '../objects/record.php';
  
// instantiate database and record object
$database = new Database();
$db = $database->getConnection();
  
// initialize object
$record = new Record($db);
  
// query records
$stmt = $record->readRandom();
$num = $stmt->rowCount();
  
// check if more than 0 record found
if($num>0){
  
    // records array
    $records_arr=array();
    $records_arr["records"]=array();
  
    // retrieve our table contents
    // fetch() is faster than fetchAll()
    // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        // extract row
        // this will make $row['name'] to
        // just $name only
        extract($row);
  
        $record_item=array(
            "id" => $id,
            "person_id" => $person_id,
            "path" => $path,
            "location" => $location,
            "created_at" => $created_at
        );
  
        array_push($records_arr["records"], $record_item);
    }
  
    // set response code - 200 OK
    http_response_code(200);
  
    // show records data in json format
    echo json_encode($records_arr);
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