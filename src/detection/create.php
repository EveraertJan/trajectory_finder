<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// get database connection
include_once '../config/database.php';
include_once '../shared/utilities.php';

include_once '../config/vars.php';

// instantiate detection object
include_once '../objects/detection.php';

$database = new Database();
$db = $database->getConnection();

$detection = new detection($db);

$utilities = new Utilities();

// get posted data
$data = json_decode(file_get_contents("php://input"));
// make sure data is not empty
if(
  !empty($data->test) &&
  !empty($data->detection) &&
  !empty($data->location)
){

  $status = 0;
  $response = array();


    // set detection property values
  $detection->test = $data->test;
  $detection->detection = $data->detection;
  $detection->location = $data->location;
  $detection->trajectoryid = -1;
  
  
  $detection_trans = fixJSON($data->detection);
  $decoded_detection = json_decode($detection_trans);
  $detectionCenterX = $decoded_detection->CenterX;
  $detectionCenterY = $decoded_detection->CenterY;

  array_push($response, 't:'.time().', x:'.$detectionCenterX.', y:'.$detectionCenterY);
  

  // close too old trajectories, everything open after 15 minutes
  if($detection->sanitize()) {

    // get still open and recent trajectories
    $trajectories = $detection->getRecentTrajectories();

    // assemble possible matches
    $possible = array();
    while ($row = $trajectories->fetch(PDO::FETCH_ASSOC)){
      // now we have all the open paths 
      // we should check if they are near
      $while_detection = fixJSON($row['detection']);
      $while_decoded_detection = json_decode($while_detection);

      $whileCenterX = $while_decoded_detection->CenterX;
      $whileCenterY = $while_decoded_detection->CenterY;

      $distance = $utilities->getDistance( $whileCenterX, $whileCenterY, $detectionCenterX, $detectionCenterY);

      $time_diff = abs(time() - strtotime($row['created_at']));

      $velocity = $utilities->getVelocity($distance, $time_diff);

      $heading = $utilities->getHeading($detectionCenterX, $detectionCenterY, $whileCenterX, $whileCenterY);


      array_push($response, "with (d, md, t, mt) ".$distance.', '.$max_dist.', '.$time_diff.', '.$max_time);

      // matches close enough
      if($distance < $max_dist && $time_diff < $max_time) {
        $row['distance'] = $distance;
        $row['time_diff'] = $time_diff;
        $row['heading'] = $heading;
        $row['velocity'] = $velocity;
        $row['selected'] = 1;
        array_push($possible, $row);
      }
    }

    if(count($possible) == 0) {
      // there are no oither ones to match with, create new trajectory 
      $newTrajectory = $detection->createTrajectory();
      $trajectoryID = $newTrajectory['ID'];

      $detection->trajectoryid = $trajectoryID;
      $detection->velocity = -2;
      $detection->heading = -2;
      array_push($response, "created a new one");
    
    }

    if(count($possible) == 1 ) {

      $onlyPossible = $possible[0];

      $trajectoryID = $onlyPossible['trajectory_id'];
      $detection->trajectoryid = $trajectoryID;
      
      //calculate vel and dir;
      $only_detect_trans = fixJSON($onlyPossible['detection']);
      $only_decoded_detection = json_decode($only_detect_trans);

      $onlyCenterX = $only_decoded_detection->CenterX;
      $onlyCenterY = $only_decoded_detection->CenterY;


      $distance = sqrt(pow($onlyCenterX - $detectionCenterX, 2) + 
                    pow($onlyCenterY - $detectionCenterY, 2) * 1.0);

      $time_diff = abs(strtotime($onlyPossible['created_at']) - time());
      

      // velocity & heading
      $velocity = $utilities->getVelocity($distance, $time_diff);
      $heading = $utilities->getHeading($detectionCenterX, $detectionCenterY, $onlyCenterX, $onlyCenterY);


      // save vel and dir
      $detection->velocity = round($velocity, 4);
      $detection->heading = round($heading, 4);
      array_push($response, "there was only 1");

      array_push($response, 'v: '.$velocity.', h:'.$heading.', t:'.$time_diff, ', x:'.$detectionCenterX.', y:'.$detectionCenterY.'-');

    }

    if(count($possible)>1) {
      // what if more counts are present?
      $closest;
      $closestM = 10000;
      // go over each possibility
      foreach ($possible as $pos) {
          // project the following
          $detect_trans_for = fixJSON($pos['detection']);
          $for_detection = json_decode($detect_trans_for);

          $nextX = $for_detection->CenterX - ($pos["time_diff"] * $pos["velocity"]) * cos($pos["heading"]);
          $nextY = $for_detection->CenterY - ($pos["time_diff"] * $pos["velocity"]) * sin($pos["heading"]);

          $pos['nextX'] = $nextX;
          $pos['nextY'] = $nextY;



          // get difference
          $distanceFromReality = $utilities->getDistance($nextX, $nextY, $curCenterX, $curCenterY);
          array_push($response, $pos["velocity"]."v:t".$pos["time_diff"]." next: x".$pos['nextX']. ": y" . $pos['nextY']." cur: (".$for_detection->CenterX.":".$for_detection->CenterY.")".$distanceFromReality);

          if($distanceFromReality <= $closestM) {
            // save the thing
            $closest = $pos;
            $closestM = $distanceFromReality;
          }
          array_push($response, "dist:".$distanceFromReality);
      }
      array_push($response, "where multiple, I picked one");
      // array_push($response, $closest);



      $detection->trajectoryid = $closest["trajectory_id"];
      $detection->velocity = $closest["velocity"];
      $detection->heading = -$closest["heading"];

      array_push($response, $closest);

      array_push($response, 'v: '.$closest->velocity.', h:'.$closest->heading.', x:'.$curCenterX.', y:'.$curCenterY.'-');
    }

  } else {
    http_response_code(300);
    echo json_encode(array("message" => "ran into an issue with sanitising"));
  }



    // create the detection
  if($detection->create()){
    // array_push($response, $detection);

        // set response code - 201 created
    http_response_code(201);

        // tell the user
    echo json_encode($response);
  }
  
    // if unable to create the detection, tell the user
  else{

        // set response code - 503 service unavailable
    http_response_code(503);
    array_push($response, "ERROR CREATING");
    array_push($response, $detection);

    echo json_encode($response);
  }
}

// tell the user data is incomplete
else{

    // set response code - 400 bad request
  http_response_code(400);
  
    // tell the user
  echo json_encode(array("message" => "Unable to create detection. Data is incomplete.".json_encode($data).!empty($data->test) .
  !empty($data->detection) .
  !empty($data->location) .
  !empty($data->trajectoryid)));
}

function fixJSON($json) {
    $regex = <<<'REGEX'
~
    "[^"\\]*(?:\\.|[^"\\]*)*"
    (*SKIP)(*F)
  | '([^'\\]*(?:\\.|[^'\\]*)*)'
~x
REGEX;

    return preg_replace_callback($regex, function($matches) {
        return '"' . preg_replace('~\\\\.(*SKIP)(*F)|"~', '\\"', $matches[1]) . '"';
    }, $json);
}
?>