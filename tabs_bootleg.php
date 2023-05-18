<?php

$pfieldstring = $_GET["player"];
$efieldstring = $_GET["enemy"];
$customtroops = $_GET["custom"];
$xf = $_GET["dimx"];
$yf = $_GET["dimy"];



//config vars
$fieldheight = $yf;
$fieldwidth = $xf*2;
$maxarchers = 6;
$maxsoldiers = 6;
$maxttroops = 12;
$validconfig = true;
$errormessage;


/*
arrays representing  individual troops:
0 = id,
1 = troop type
2 = health
3 = xpos
4 = ypos
5 = target
*/
$titles = array("Soldier", "Archer");
$symbols = array("S", "A");

$newsoldier = array(-1, 0, 10, -1, -1, -1);
$newarcher = array(-1, 1, 10, -1, -1, -1);

$newtroop = array(&$newsoldier, &$newarcher);

/*
arrays representing troop config:
0 = atk
1 = crit chance
2 = miss chance
3 = attack speed
4 = movement speed
*/

$soldierstats = array(2, 0.1, 0.1, 1, 2);
$archerstats = array(1, 0.1, 0.1, 2, 1);

$stats = array(&$soldierstats, &$archerstats);


/*
arrays representing troop config:
[0 = left x (negative)
1 = right x    
2 = top y (negative)
3 = bottom y
*/

$soldierrange = array(-1, 1, -1, 1);
$archerrange = array(-3, 3, -3, 3);

$ranges = array(&$soldierrange, &$archerrange);


$troopnames = array("Moreen", "Camille", "Corliss", "Colin", "Noble", "Anja", "Doanne", "Alexander", "Rupert", "Jerold", "Nelson", "Bedivere", "Lindon", "Bathilde", "Milton", "Levina", "Greysen", "Eadburg", "Chancey", "Bernia", "Whitney", "Dahlia", "Timothea", "Hildred", "Eadwig", "Egbert", "Stanford", "Maitane", "Winfred", "Elfreda", "Stuart", "Carlyle", "Wilona", "Delwyn", "Wyot", "Luella", "Sigourney", "Udele", "Becket", "Ebba", "Rovena", "Leland", "Shelton", "Everild", "Chaney", "Russell", "Elias", "Jess", "Sam", "Maggie", "Daniel");



/*----------CREATING NEW TROOPS FROM PLAYER SPECIFICATION----------*/
$tr = str_replace(" ", "", $customtroops);
$tr = preg_split("/\r\n|\n|\r/", $tr);
$tr = array_filter($tr);

foreach($tr as $t){

  print($t);

  if(substr_count($t, ",") != 8){

    $errormessage = "troop creation error! invalid format";
    print "<pre> $errormessage </pre>";
    $validconfig = false;

  }
  else{

    $valid = true;

    $formatted = str_replace(" ", "", $t);
    $customtroop = explode(",", $formatted);

    //checking for duplicate symbols
    foreach($symbols as $s){

      if(strcasecmp($s, $customtroop[1]) == 0){

        $errormessage = "troop creation error! symbol already in use for ". $customtroop[0];
        print "<pre> $errormessage </pre>";

        $valid = false;

      }

    }

    //checking for duplicate titles
    foreach($titles as $i){

      if(strcasecmp($i, $customtroop[0]) == 0){

        $errormessage = "troop creation error! troop title already in use for " . $customtroop[0];
        print "<pre> $errormessage </pre>";

        $valid = false;

      }

    }

    //print nl2br("\ns". ((int)($customtroop[5])  +  0 ). " " . (intval($customtroop[7]) + intval($customtroop[8])) . "e\n");

    //checking for valid ranges
    if( ((int)$customtroop[5]  > (int) $customtroop[6]) || ((int)$customtroop[7] > (int)$customtroop[8])){

      $errormessage = "troop creation error! invalid range for " . $customtroop[0];
      print "<pre> $errormessage </pre>";

      $valid = false;

    }

    if($valid){

      $titles[] = $customtroop[0];
      $symbols[] = strtoupper($customtroop[1]);

      $temp = $newsoldier;
      $temp[1] = count($symbols) - 1;
      $temp[2]= $customtroop[2];
      $newtroop[] = $temp;

      $temp = $soldierstats;
      $temp[0] = $customtroop[3];
      $temp[4] = $customtroop[4];
      $stats[] = $temp;

      $temp = $soldierrange;
      $temp[0] = $customtroop[5];
      $temp[1] = $customtroop[6];
      $temp[2] = $customtroop[7];
      $temp[3] = $customtroop[8];
      $ranges[] = $temp;

    }
    else{
      $validconfig = false;
    }



  }
}

print $validconfig;

require 'tabs_functions.php';

//impostant vars
$ff = fullfieldgen($fieldwidth, $fieldheight);
$ptroops = array();
$etroops = array();

$printables = "";

//convert the enemy & player fields into an array representation
$pfield = explode("\n", $pfieldstring);
$efield = explode("\n", $efieldstring);


/*----------CHECKING IF PLAYER FIELD IS VALID----------*/
//checking height of field 
if ((count($pfield) - 1) != $fieldheight) {

  $errormessage = "error! invalid field height";
  print "<pre> $errormessage </pre>";
  $validconfig = false;

}

$archercount = 0;
$soldiercount = 0;
$pcount = 0;

foreach ($pfield as $p) {

  //don't check last line
  if($pcount == $fieldheight){
    break;
  }

  //checking width of field
  if ((strlen($p) - 1) != $fieldwidth) {

    $errormessage = "error! invalid field width of player field on line " . ($pcount + 1) . " " . strlen($p);
    print "<pre> $errormessage </pre>";
    $validconfig = false;

  }

  //checking if the spacing (# of spaces) is correct
  if (substr_count($p, " ")!= (($fieldwidth) / 2)) {

    $errormessage = "error! invalid field spacing of player field on line " . ($pcount + 1) . " " . substr_count($p, " ");
    print "<pre> $errormessage </pre>";
    $validconfig = false;

  }

  //counting number of archers/soldiers
  $archercount += substr_count($p, "a");
  $soldiercount += substr_count($p, "s");
  $pcount++;
}

foreach ($efield as $p) {

  //don't check last line
  if($pcount = $fieldheight){

    break;

  }

  //checking width of field
  if ((strlen($p) - 1) != $fieldwidth) {

    $errormessage = "error! invalid field width of enemy field on line " . ($pcount + 1) . " " . strlen($p);
    print "<pre> $errormessage </pre>";
    $validconfig = false;

  }

  //checking if the spacing (# of spaces) is correct
  if (substr_count($p, " ")!= (($fieldwidth) / 2)) {

    $errormessage = "error! invalid field spacing of enemy field on line " . ($pcount + 1) . " " . substr_count($p, " ");
    print "<pre> $errormessage </pre>";
    $validconfig = false;

  }

  //counting number of archers/soldiers
  $archercount += substr_count($p, "a");
  $soldiercount += substr_count($p, "s");
  $pcount++;
}


//error message if there are too many archers
if ($archercount > $maxarchers) {

  $errormessage = "error! Too many archers";
  print "<pre> $errormessage </pre>";
  $validconfig = false;

}

//error message if there are too many soldiers
if ($soldiercount > $maxsoldiers) {

  $errormessage = "error! Too many soldiers";
  print "<pre> $errormessage </pre>";
  $validconfig = false;

}

//error message if there are too many troops
if (($archercount + $soldiercount) > $maxttroops) {

  $errormessage = "error! Too many troops";
  print "<pre> $errormessage </pre>";
  $validconfig = false;

}

//unsetting temporary vars
unset($soldiercount);
unset($archercount);
unset($pfstring);
unset($efstring);
unset($errormessage);
unset($pcount);


/*------------CONFIGURE FIELD FOR PLAY------------*/
if ($validconfig) {

  //print "field intialized!";

  //creating empty player field based off of specifications given
  for ($i = 0; $i < $fieldheight; $i++) {

    $bigfield[$i] = substr($pfield[$i], 0, -1) . $efield[$i];

  }

  $bfstring = implode("\n", $bigfield);
  //print "<pre>$bfstring</pre>";

  //initializing players...
  shuffle($troopnames);

  //iterate through each column in field
  for ($y = 0; $y < count($bigfield); $y += 1) {
    
    //iterate through each character in row, skipping spaces
    for ($x = 0; $x < strlen($bigfield[$y]); $x += 2) {

      $tile = $bigfield[$y][$x];

      //check if current position is empty (. = empty)
      if(!(strcmp($tile, ".") == 0)){

        //if not empty iterate through known character symbols
        //to check if character is a troop.
        for($c = 0; $c < count($symbols); $c++){

          //print nl2br( "symbol checked: ". $symbols[$c]." tile: ".$tile. "count: " . $c ." \n");

          if(strcasecmp($symbols[$c], $tile) == 0){

            //is troop an enemy or ally character?
            if(ctype_upper($tile)){

              //create new enemy
              //print nl2br($c . " <--" . $tile . "\n");
              $troop = $newtroop[$c];
              $troop[0] = count($etroops) + 12;
              $troop[3] = $x;
              $troop[4] = $y;
              $etroops[] = $troop;
              $temp = $titles[$c];

              //print nl2br("added enemy $temp!\n");
              //print nl2br(" coords(" . $troop[3] . ", " . $troop[4] . ")\n");

            }
            else{

              //create new ally
              $troop = $newtroop[$c];
              $troop[0] = count($ptroops);
              $troop[3] = $x;
              $troop[4] = $y;
              $ptroops[] = $troop;
              $temp = $titles[$c];

              //print nl2br("added ally $temp!\n");
              //print nl2br(" coords(" . $troop[3] . ", " . $troop[4] . ")");

            }
            
            break;
          }
        }
      }
    }
  }

  //debug code
  if (0) {

    $count = count($ptroops);
    $printables = $printables . "Total troops: $count!\n";

    foreach ($ptroops as $t) {

      $troops = implode(" ", $t);
      $printables = $printables . "$troops \n";

    }
  }


  /*---------------TIME FOR BATTLE---------------*/
  $printables = $printables . "\ntime for battle! \nEnemy troops: " . count($etroops) . "\nPlayer troops: " . count($ptroops) . "\n";

  //maximum number of rounds to prevent infinite loops
  $loops = 120;

  $testarr = array();

  //while each side has at least one troop, continue battle
  while ((count($ptroops) > 0 && count($etroops) > 0) && $loops > 0) {

    $pcount = 0;
    $ecount = 0;
    $totcount = 0;

    $printables = $printables . "\nRound start! \n";

    //lists information about player and enemy troops
    if (0) {

      $printables = $printables . "Enemy troops:\n";
      $tl = "";

      foreach ($etroops as $e) {

        $tl = $tl . implode(", ", $e) . "\n";
      }
      $printables = $printables . $tl;
      
      $printables = $printables . "Player troops:\n";
      $tl = "";

      foreach ($ptroops as $e) {

        $tl = $tl . implode(", ", $e) . "\n";
      }
      $printables = $printables . $tl;

    }

    //randomizes move order
    shuffle($ptroops);
    shuffle($etroops);

    //loops until every troop has performed an action
    while ($pcount < (count($ptroops)) || ($ecount < count($etroops))) {

      //enemy and player troops take turns moving. Enemy moves first
      if ($totcount % 2 == 0 && $ecount <= count($etroops)) {

        //enemy attacks (if hp is greater than 0)
        if ($etroops[$ecount][2] > 0) {

          $temp = $titles[$etroops[$ecount][1]]." ".$troopnames[$etroops[$ecount][0]];
          $printables = $printables . "<font color='red'>$temp attacks: </font>"; 

          //validates target and attacks!
          $etroops[$ecount][5] = checktarget($etroops[$ecount], false);
          atk($etroops[$ecount], false);
          
          $printables = $printables . "\n";

        }
        //if hp is less than 0, then remove troop from array.
        else{

          $bigfield[$etroops[$ecount][4]][$etroops[$ecount][3]] = '.';
          array_splice($etroops, $ecount, 1);

        }

        //advance number of enemies that have taken an action
        $ecount++;

        //Player moves second
      } else if ($totcount % 2 == 1 && $ecount <= count($ptroops)) {

        //player attacks!
        if ($ptroops[$pcount][2] > 0) {

          $temp = $titles[$ptroops[$pcount][1]]. " " . $troopnames[$ptroops[$pcount][0]];
          $printables = $printables . "<font color='green'>$temp attacks: </font>";

          //validates target and attacks
          $ptroops[$pcount][5] = checktarget($ptroops[$pcount], true);
          atk($ptroops[$pcount], true);
         
          $printables = $printables . "\n";

        }
        //remove player troop if hp is less than 0
        else{

          $bigfield[$ptroops[$pcount][4]][$ptroops[$pcount][3]] = '.';
          array_splice($ptroops, $pcount, 1);  

        }

        //advance number of player troops that have taken an action
        $pcount++;
      }

      $totcount++;
    }

    $printables = $printables . implode("\n", array_filter($bigfield));
    $printables = $printables . "\nround done!\n";


    $testarr[] = implode("<br>", array_filter($bigfield));

    $loops--;

  }

  $printables = $printables . "\ndone!\n";

  //checking if player won or lost. If time runs out and enemy troops still remain, player loses.
  if (count($etroops) > 0) {

    $printables = $printables . "YOU LOST!\n";
    $donemessage = 'YOU LOST!';

  } else {

    $printables = $printables . "YOU WON!\n";
    $donemessage = 'YOU WON!';

  }
$temp = json_encode($testarr);
$cnt = count($testarr);

//HTML that styles the page
echo "
<body style= 'background-color:#f06b6b;'>
      <div align = 'center'>
      <h1> WAR!! </h1>
";

//Javascript that animates the battle
echo "
<p id='tst'> Start! </p>
<p id = 'done'> </p>
<script>
const temp = $temp;
var i = 0;

var intv = setInterval(move, 600);

function move(){
    var curr = temp[i];
    i += 1;

    if(i >= $cnt){
        document.getElementById('done').innerHTML = '$donemessage';
        clearInterval(intv);
    }

    document.getElementById('tst').innerHTML = curr;
}
</script>";

//"frame-by-frame" play of the battle
echo nl2br($printables);

}
?>