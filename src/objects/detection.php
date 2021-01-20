<?php
class Detection{
  
    // database connection and table name
    private $conn;
    private $table_name = "detections";
  
    // object properties
    public $ID;
    public $test;
    public $detection;
    public $created_at;
    public $location;
    public $open;
    public $trajectoryid;
    public $velocity;
    public $heading;
  
    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }
        // read products
    function read(){
        // select all query
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
    
        // prepare query statement
        $stmt = $this->conn->prepare($query);
    
        // execute query
        $stmt->execute();
    
        return $stmt;
    }
        // create product
    function create(){
    
        // query to insert record
        $query = "INSERT INTO
                    " . $this->table_name . "
                SET
                    test=:test, detection=:detection, location=:location, trajectory_id=:trajectoryid, velocity=:velocity, heading=:heading";
    
        // prepare query
        $stmt = $this->conn->prepare($query);
    
        // sanitize
        $this->test=htmlspecialchars(strip_tags($this->test));
        $this->detection=htmlspecialchars(strip_tags($this->detection));
        $this->location=htmlspecialchars(strip_tags($this->location));
    
        // bind values
        $stmt->bindParam(":detection", $this->detection);
        $stmt->bindParam(":test", $this->test);
        $stmt->bindParam(":location", $this->location);
        $stmt->bindParam(":trajectoryid", $this->trajectoryid);
        $stmt->bindParam(":velocity", $this->velocity);
        $stmt->bindParam(":heading", $this->heading);
    
        // execute query
        if($stmt->execute()){
            return true;
        }
    
        return false;
        
    }// used when filling up the update product form

    function sanitize() {
        $query = "UPDATE  
                    " . $this->table_name . "
                SET open = 0
                WHERE created_at < DATE_SUB(NOW(),INTERVAL 5 MINUTE)
                AND open = 1";

        // prepare query
        $stmt = $this->conn->prepare($query);
        // execute query
        if($stmt->execute()){
            return true;
        }
    
        return false;

    }
    function getRecentTrajectories() {

        $query = "SELECT * FROM " . $this->table_name . " WHERE created_at > DATE_SUB(NOW(),INTERVAL 5 MINUTE) AND open = 1";
    
        // prepare query statement
        $stmt = $this->conn->prepare($query);
    
        // execute query
        $stmt->execute();
    
        return $stmt;
    }

    function createTrajectory() {
        $query = "INSERT INTO `trajectories` (`handle`) VALUES ('Hi')";
        $stmt = $this->conn->prepare($query);

        // execute query
        if($stmt->execute()){

            $query2 = "SELECT * FROM  `trajectories`  ORDER BY `ID` DESC LIMIT 0,1";
            $stmt2 = $this->conn->prepare($query2);  
            if($stmt2->execute()){
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);
                return $row;
            }
            return $query2;
        }
        return false;
    }
    // update trajectory
    function readOne(){
    
        // query to read single record
        $query = "SELECT
                    *
                FROM
                    " . $this->table_name . "
                WHERE
                    id = ?
                LIMIT
                    0,1";
    
        // prepare query statement
        $stmt = $this->conn->prepare( $query );
    
        // bind id of product to be updated
        $stmt->bindParam(1, $this->id);
    
        // execute query
        $stmt->execute();
    
        // get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // set values to object properties
        $this->test = $row['test'];
        $this->detection = $row['detection'];
        $this->location = $row['location'];
        $this->created_at = $row['created_at'];
        $this->trajectoryid = $row['trajectory_id'];
    }
        // delete the product
    function delete(){
    
        // delete query
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
    
        // prepare query
        $stmt = $this->conn->prepare($query);
    
        // sanitize
        $this->id=htmlspecialchars(strip_tags($this->id));
    
        // bind id of record to delete
        $stmt->bindParam(1, $this->id);
    
        // execute query
        if($stmt->execute()){
            return true;
        }
    
        return false;
    }
        // read products with pagination
    public function readPaging($from_record_num, $records_per_page){
    
        // select query
        $query = "SELECT
                    id, test, detection, location,  created_at, trajectory_id
                FROM
                    " . $this->table_name . " 
                ORDER BY created_at DESC
                LIMIT ?, ?";
    
        // prepare query statement
        $stmt = $this->conn->prepare( $query );
    
        // bind variable values
        $stmt->bindParam(1, $from_record_num, PDO::PARAM_INT);
        $stmt->bindParam(2, $records_per_page, PDO::PARAM_INT);
    
        // execute query
        $stmt->execute();
    
        // return values from database
        return $stmt;
    }
        // used for paging products
    public function count(){
        $query = "SELECT COUNT(*) as total_rows FROM " . $this->table_name . "";
    
        $stmt = $this->conn->prepare( $query );
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return $row['total_rows'];
    }
    // used when filling up the update product form
    function readRandom(){
        $possibleQueries = array(
            "SELECT
                *
            FROM
                " . $this->table_name . "
                ORDER BY rand()
                LIMIT 0, 1
           "
        );
        // query to read single record
        $query = $possibleQueries[array_rand($possibleQueries)];


        
        // prepare query statement
        $stmt = $this->conn->prepare( $query );
    
        // execute query
        $stmt->execute();
    
        return $stmt;
    }
}
?>