<?php

/*----------USEFUL FUNCTIONS FOR TABS----------*/

//generates 1/2 of the field
function halffieldgen(&$width, &$height)
{
  $row     = "";
  $columns = "";
  for ($i = 0; $i < $width; $i++) {
    $row = $row . ". ";
  }
  for ($i = 0; $i < $height; $i++) {
    $columns = $columns . $row . "\n";
  }
  return $columns;
}

//generates full field
function fullfieldgen(&$width, &$height)
{
  $row     = "";
  $columns = "";
  print $columns;
  for ($i = 0; $i < $width*2; $i++) {
    $row = $row . ".\t";
  }
  for ($i = 0; $i < $height / 2; $i++) {
    $columns = $columns . $row . "\n";
  }
  return $columns;

}


//checks that target is valid/assigns new target
function checktarget(&$troop, $isplayer)
{
  global $etroops, $ptroops;

  if ($isplayer == true) {
    $enemies = &$etroops;

  } else {
    $enemies = &$ptroops;
  }

  //print "checking target!";

  //checks if target is assigned.
  if ($troop[5] > 0) {
    $found = false;

    for ($i = 0; $i < count($enemies); $i++) {

      if ($enemies[$i][0] == $troop[5]) {

        //if target is dead, delete enemy from enemy array and unassign target from troop
        if ($enemies[$i][2] < 0) {

          array_splice($enemies, $i, 1);
          $troop[5] = -1;

        }

        $found = true;
        break;
      }

      //if troop is not in array, remove as target.
      if(!$found){
        $troop[5] = -1;
      }
    }
  }

  //if target is unassigned, choose new target (closest enemy)
  //$troop[5] < 0

  //finds the closest enemy as new target. 
  if (true) {

    $closest = $enemies[0];
    //print "R ".$closest[0]." R";
    //iterate through enemy array and calculate enemy distance from player
    for ($i = 0; $i < count($enemies); $i++) {

      $dist  = sqrt(pow($closest[3] - $troop[3], 2) + pow($closest[4] - $troop[4], 2));
      $edist = sqrt(pow($enemies[$i][3] - $troop[3], 2) + pow($enemies[$i][4] - $troop[4], 2));
      //print( $dist . ", , " . $edist);

      //if new enemy is closer to target, reassign as closest
      if ($dist > $edist && $enemies[$i][2] > 0) {

        $closest = $enemies[$i];

      }
    }

    //echo "assigned enemy: ".$closest[0];

    return $closest[0];

  }
}

//attack algorithm for troops
function atk(&$troop, $isplayer)
{

  global $symbols, $ranges, $stats, $troopnames;
  global $bigfield, $etroops, $ptroops, $printables;

  //determines which troop array will be used as the enemy & which as the current actor
  if ($isplayer === true) {

    $troops  = &$ptroops;
    $enemies = &$etroops;

  } else {

    $troops  = &$etroops;
    $enemies = &$ptroops;

  }

  //find target object from enemies array
  foreach ($enemies as &$e) {

    //echo nl2br("checking ".$e[0]." against ".$troop[5]. "\n");
    if ($e[0] == $troop[5]) {

      $target = &$e;
      //echo nl2br("found!\n");
      break;

    }
  }

  //check to make sure target was found
  if (!isset($target)) {

    $printables = $printables . "Target not found!";
    $troop[5] = -1;

  }

  //range/stats selector for troop
  $stats = &$stats[$troop[1]];
  $range = &$ranges[$troop[1]];

  //returns troop stats
  if (0) {

    $printables = $printables . "stats: " . implode(", ", $stats) . "\n";
    $printables = $printables . "range: " . implode(", ", $range) . "\n";
    $printables = $printables . "location: ($troop[3], $troop[4]\n";
    $printables = $printables . "target location: ($target[3], $target[4]\n";

  }

  //checks if target is range by comparing troop range to target location
  $trooprange = array($troop[3] + $range[1] * 2, $troop[3] + $range[2] * 2, $troop[4] + $range[3] * 2, $troop[4] + $range[4] * 2);

  //print "is target in range? "
  //print implode(", ", $trooprange)." &";
  //print "$target[3], $target[4]";

  if ($target[3] <= $trooprange[0] && $target[3] >= $trooprange[1] && $target[4] <= $trooprange[2] && $target[4] >= $trooprange[3]) {

    //print "target is in range! ";

    $target[2] -= $stats[0];
    $temp = $troopnames[$target[0]];

    $printables = $printables . "attacked target <font color='blue'>$temp</font>: hp $target[2]";

    //checks if current target is dead
    if ($target[2] <= 0) {

      //deleting target from battlefield
      $bigfield[$target[4]][$target[3]] = '.';
      $printables = $printables . "target died ";
      $troop[5] = -1;

      //deleting target from array of troops
      $temp     = array_search($target, $enemies);

      if ($temp === false) {

        $printables = $printables . "error! Target could not be found for deletion! ";

      } else {

        array_splice($enemies, $temp, 1);
        $printables = $printables . "dead enemy removed! ";

      }
    }
  }

  //if target isnt in range, move towards target
  else {
    $tname = $troopnames[$target[0]];

    $printables = $printables . "target $tname is not in range! ";

    /*
    Calculates the best to worst direction to move in: 
    {towards target (furthest distance), towards target (shorter distance), away (shorter), away (further)}
    then moves based on best available option
    */
    $xgap = $target[3] - $troop[3]; //positive value means that troop is further right
    $ygap = $target[4] - $troop[4]; //positive value means that troop is further down


    //adds possible moves to array in order of best to worst move.
    //array index 0 represents x, 1 represents y
    if ($xgap > $ygap) {

      $mvodr = array(array(2, 0));
      array_push($mvodr, array(0, abs($ygap) * 1 / $ygap));
      array_push($mvodr, array(0, abs($ygap) * -1 / $ygap));
      array_push($mvodr, array(abs($xgap) * -2 / $xgap, 0));

    } else {

      $mvodr = array(array(0, abs($ygap) * 1 / $ygap));
      array_push($mvodr, array(abs($xgap) * 2 / $xgap, 0));
      array_push($mvodr, array(abs($xgap) * -2 / $xgap, 0));
      array_push($mvodr, array(0, abs($ygap) * -1 / $ygap));

    }

    //prints out the determined move order
    if(0){

      $printables = $printables . "move order: ";
      foreach ($mvodr as $m) {
        $printables = $printables . "(";
        $printables = $printables . implode(",", $m);
        $printables = $printables . ") ";
      }

    }

    //iterates through best to worst move, checking if possible
    foreach ($mvodr as $m) {

      //if target is in range, do nothing
      if (abs($xgap) <= ($range[1] * 2) && abs($ygap) <= $range[3]) {
      }
      else{

        //checking if move is out of bounds
        if(count($bigfield) > $troop[4] + $m[1] && $troop[4] + $m[1] >= 0 && strlen($bigfield[$troop[4] + $m[1]]) > $troop[3] + $m[0] && $troop[3] + $m[0] >= 0){

          $temp = $bigfield[$troop[4] + $m[1]][$troop[3] + $m[0]];

        }
        else{

          $temp = -1;

        }
      }

      //check if space is empty
      if ($temp == '.') {

        //if space is empty, move troop to that position
        $bigfield[$troop[4]][$troop[3]] = '.';
        $troop[3] += $m[0];
        $troop[4] += $m[1];
        
        if ($isplayer) {

          $bigfield[$troop[4]][$troop[3]] = strtolower($symbols[$troop[1]]);

        } else {

          $bigfield[$troop[4]][$troop[3]] = strtoupper($symbols[$troop[1]]);

        }
  
        //echo "troop moved to (" . $m[0] . ", " . $m[1] . ")! ";
        break;
      }
    }
  }
}
?>