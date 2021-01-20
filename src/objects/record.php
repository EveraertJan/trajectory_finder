<?php
class Record{
  
    // database connection and table name
    private $conn;
    private $table_name = "records";
  
    // object properties
    public $ID;
    public $person_id;
    public $path;
    public $created_at;
    public $location;
  
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
                    person_id=:person_id, path=:path, location=:location";
    
        // prepare query
        $stmt = $this->conn->prepare($query);
    
        // sanitize
        $this->person_id=htmlspecialchars(strip_tags($this->person_id));
        $this->location=htmlspecialchars(strip_tags($this->location));
    
        // bind values
        $stmt->bindParam(":person_id", $this->person_id);
        $stmt->bindParam(":path", $this->path);
        $stmt->bindParam(":location", $this->location);
    
        // execute query
        if($stmt->execute()){
            return true;
        }
    
        return false;
        
    }// used when filling up the update product form
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
        $this->person_id = $row['person_id'];
        $this->path = $row['path'];
        $this->locations = $row['locations'];
        $this->created_at = $row['created_at'];
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
                    id, person_id, path, location,  created_at
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