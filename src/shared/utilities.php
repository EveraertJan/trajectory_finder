<?php
include_once '../config/vars.php';


class Utilities{
  
    public function getPaging($page, $total_rows, $records_per_page, $page_url){
  
        // paging array
        $paging_arr=array();
  
        // button for first page
        $paging_arr["first"] = $page>1 ? "{$page_url}page=1" : "";
  
        // count all products in the database to calculate total pages
        $total_pages = ceil($total_rows / $records_per_page);
  
        // range of links to show
        $range = 2;
  
        // display links to 'range of pages' around 'current page'
        $initial_num = $page - $range;
        $condition_limit_num = ($page + $range)  + 1;
  
        $paging_arr['pages']=array();
        $page_count=0;
          
        for($x=$initial_num; $x<$condition_limit_num; $x++){
            // be sure '$x is greater than 0' AND 'less than or equal to the $total_pages'
            if(($x > 0) && ($x <= $total_pages)){
                $paging_arr['pages'][$page_count]["page"]=$x;
                $paging_arr['pages'][$page_count]["url"]="{$page_url}page={$x}";
                $paging_arr['pages'][$page_count]["current_page"] = $x==$page ? "yes" : "no";
  
                $page_count++;
            }
        }
  
        // button for last page
        $paging_arr["last"] = $page<$total_pages ? "{$page_url}page={$total_pages}" : "";
  
        // json format
        return $paging_arr;
    }

    public function getDistance($curCenterX, $curCenterY, $newCenterX, $newCenterY) {
        return abs(sqrt(pow($curCenterX - $newCenterX, 2) + pow($curCenterY - $newCenterY, 2) * 1.0));
    }
    public function getVelocity($distance, $time_diff) {
        if($time_diff == 0) { 
            $time_diff = 1;
        }
        return ($distance) / ($time_diff);
    }
    public function getHeading($curCenterX, $curCenterY, $newCenterX, $newCenterY) {

      $delta_x = $newCenterX - $curCenterX;
      $delta_y = $newCenterY - $curCenterY;
      $theta_radians = atan2($delta_y, $delta_x);

      return $theta_radians;
    }
  
}
?>