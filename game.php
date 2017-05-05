
<?php
session_start();


// List of all the objects in the game

$objects = array(
	array(
		"nom" => "Bouteille d'eau",
		"index" => 1),
	array(
		"nom" => "Bandage",
		"index" => 2),
	array(
		"nom" => "Cle du hall",
		"index" => 3),
	array(
		"nom" => "Cle de la chambre",
		"index" => 4));


// Behaviour depending on whether the user is logged or not, the command he has used, etc.

if (isset($_SESSION["username"])){
	if ($_GET['command'] === 'logout'){			//Logout
		logout_command();
	}elseif($_GET['command'] === 'go'){			// Movement
		$_SESSION['time_without_water'] -= 1;	// Increase the need of water
		if(strpos($_SESSION['rooms_historic'], '6') !== false){   //Check if the user has passed by the room 6 (crossed the window)
			$_SESSION['time_without_bandages'] -= 1;
		}
		if ($_SESSION['time_without_water'] <= 0){
			echo "<br><br> John couldn't stand for more without drinking water and passed out";		//the user loses
		}elseif($_SESSION['time_without_bandages'] <= 0){
			echo "<br><br> John started to loose too much blood and passed out";
		}else{
			if ($_GET['destination'] === '8'){				//	First locked room logic
				if($_SESSION['inventory'][2]['quantity'] >0){	//Check if he has the key
					$_SESSION['room']=$_GET['destination'];
					$_SESSION['world'][$_SESSION['room']]['visible'] = "yes";
					$_SESSION['rooms_historic'] = $_SESSION['rooms_historic'] . "-". $_SESSION['room'];
					default_logged_command();
				}
				else{
					default_logged_command();
				}

			}elseif($_GET['destination'] === '11'){			// Second and final locked room logic
				if($_SESSION['inventory'][3]['quantity'] >0){	//Check if he has the key
					$_SESSION['room']=$_GET['destination'];
					$_SESSION['world'][$_SESSION['room']]['visible'] = "yes";
					$_SESSION['rooms_historic'] = $_SESSION['rooms_historic'] . "-". $_SESSION['room'];
					default_logged_command();
				}
				else{
					default_logged_command();
				}
			}
			else{
				$_SESSION['room']=$_GET['destination'];			//Standard behaviour
				$_SESSION['world'][$_SESSION['room']]['visible'] = "yes";
				$_SESSION['rooms_historic'] = $_SESSION['rooms_historic'] . "-". $_SESSION['room'];
				default_logged_command();
			}
		}
		
	}elseif($_GET['command'] === 'take'){				// Take an object behaviour
		increase_inventory($_GET['index'],$_GET['quantity']);
		default_logged_command();
	}elseif($_GET['command'] === 'map'){
		world_to_json();
	}
	elseif($_GET['command'] === 'where'){
		position_to_json();
	}
	elseif($_GET['command'] === 'use'){					// Logic of using and object
		use_inventory($_GET['index'],$_GET['quantity']);
		decrease_inventory($_GET['index']);
		default_logged_command();
	}
	else{
		default_logged_command();
	}
}else{
	if($_GET['command'] === 'login'){			// Login
		login_command();
	}else{
		default_unlogged_command();
	}
}

/* Function called to erase all the sesion parameter */

function logout_command(){
	unset($_SESSION["username"]);
	unset($_SESSION["inventory"]);
	unset($_SESSION["world"]);
    echo 'Logged out';
    default_unlogged_command();
}

function increase_inventory($index,$quantity){

	foreach ($_SESSION['inventory'] as $key => $object) {
		if($index == $object['item']){
			$_SESSION['inventory'][$key]['quantity'] += $quantity;
		}
	}

	decrease_stuff($index,$quantity);
}

function decrease_inventory($index){

	foreach ($_SESSION['inventory'] as $key => $object) {
			if($index == $object['item']){
				$_SESSION['inventory'][$key]['quantity'] -= 1;
			}
	}

}

function decrease_stuff($index,$quantity){
	foreach ($_SESSION['world'] as $key => $room) {
		foreach ($room['stuff'] as $key2 => $object) {
			if($index == $object['index']){
				$_SESSION['world'][$key]['stuff'][$key2]['quantity']=0;
			}
		}
	}
}

function use_inventory($index,$quantity){
	if ($quantity>0){
		if($index == 1){
			$_SESSION['time_without_water'] += 3;		// Each bottle of water adds 3 more rooms to be visited
		}elseif($index == 2){
			$_SESSION['time_without_bandages'] += 999;	// Unlimited health if he uses the bandages
		}
	}

}

function default_logged_command(){
	echo 'Bonjour ' . $_SESSION['username'];
	showPosibilities();
	echo "<br><br> <a href='/game.php?command=logout'>Log out</a>";
}



function world_to_json(){
	header('Content-Type: application/json');
	echo json_encode($_SESSION['world']);
}

/* JSON to obtain the position of the user. To represent the bonhome with JavaScript */

function position_to_json(){
	header('Content-Type: application/json');
	$position_x = $_SESSION['world'][$_SESSION['room']]['points'][0][0]+$_SESSION['world'][$_SESSION['room']]['points'][1]/2;
	$position_y = $_SESSION['world'][$_SESSION['room']]['points'][0][1]+$_SESSION['world'][$_SESSION['room']]['points'][2]/2;
	$position = array(
		"x" => $position_x,
		"y" => $position_y
		);
	echo json_encode($position);
}

function show_open_headers(){
	echo "<html>
		<head>
			<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1' />
			<title>Projet INE11</title>
			<link rel='stylesheet' type='text/css' href='/style.css' />
		</head>
		<body>";

}

function show_close_headers(){

	echo "</body></html>";

}


/* 
	Main body of the screen.
	It shows all the inventory, history messages, objects available, map, and movement options 
	
*/

function showPosibilities(){
	$objects = $GLOBALS['objects'];


	// Check whether if the user has won or not
	if(array_key_exists(goal, $_SESSION['world'][$_SESSION['room']])){
		if($_SESSION['world'][$_SESSION['room']]['goal'] === 'victory'){
			echo '<br>John: Darling! I have been looking for you so much time and you were so close! <br>Daughter: Daddy! We have to get out of here! Quick!';
			echo "<br><br> <a href='/game.php?command=logout'>Log out</a>";
			return;
		}elseif ($_SESSION['world'][$_SESSION['room']]['goal'] === 'defeat'){
			echo '<br>John fell into the water and could not get out from there...';
			echo "<br><br> <a href='/game.php?command=logout'>Log out</a>";
			return;
		}
	}

	if($_SESSION['world'][$_SESSION['room']]['visible'] == 'yes'){
		echo "<div id='msg'><br><br>" . $_SESSION['world'][$_SESSION['room']]['history'] . "</div>";
	}


	echo "<br><br>Inventory:";
	foreach ($_SESSION['inventory'] as $item) {
		foreach ($objects as $object){
			if ($object['index'] == $item['item']){
				echo "<br>Tu as " . $item['quantity'] . " " . $object['nom'] . " " ;
				if($object['index'] === 1 && $item['quantity'] > 0){
					echo " <a href=/game.php?command=use&index=" . $object['index'] ."&quantity=1>Utiliser</a>";
				}
				if($object['index'] === 2 && $item['quantity'] > 0 ){
					if(strpos($_SESSION['rooms_historic'], '6') !== false){   //Check if the user has passed by the room 6 (crossed the window) to activate the need of bandages
						echo " <a href=/game.php?command=use&index=" . $object['index'] ."&quantity=1>Utiliser</a>";
					}
			}
		}
	}
		
	}

	echo "<br><br>Objects:";
	foreach ($_SESSION['world'][$_SESSION['room']]['stuff'] as $item) {
		foreach ($objects as $object) {
			if ($item['index'] == $object['index']){
				if($_SESSION['room'] == 14) { // need to return to find the key
					if (substr_count($_SESSION['rooms_historic'], '-14') > 1){
						echo "<br>Il y a " . $item['quantity'] . " " . $object['nom'];
						echo " <a href=/game.php?command=take&index=" . $item['index'] ."&quantity=" . $item['quantity'] ." >Take</a>";
					}
				}else{
					echo "<br>Il y a " . $item['quantity'] . " " . $object['nom'];
					echo " <a href=/game.php?command=take&index=" . $item['index'] ."&quantity=" . $item['quantity'] ." >Take</a>";
				}
				
			}
		}
	}

	echo "<br>Carte:";
	echo "<br><canvas id='canvas' width='500' height='300'></canvas>";
	
	/*echo "<script type='text/javascript'>
			var canvas = document.getElementById('canvas');
			var ctx=canvas.getContext('2d');
			canvas.style.backgroundColor = 'rgba(158, 167, 184, 0.2)';
			ctx.rect(20, 20, 150, 100);
			ctx.fillStyle = 'red';
			ctx.fill();
		</script>";
*/
	echo "<br><br>Movement:";
	echo "<br>Tu es dans " . $_SESSION['world'][$_SESSION['room']]['name'];

	$keys = array_keys($_SESSION['world'][$_SESSION['room']]['outs']);
	foreach($keys as $exit){
		$index = $_SESSION['world'][$_SESSION['room']]['outs'][$exit];
		echo "<br> Tu peux aller a la salle " . $_SESSION['world'][$index]['name'] . " par " . $exit;
		echo " <a href=/game.php?command=go&destination=" . $index . ">Y aller</a>";
	}
	echo "<script type='text/javascript' src='javascript.js'></script>";
}

function default_unlogged_command(){

	echo "<html>
	<form id='login' action='game.php' method='get' accept-charset='UTF-8'>
		<fieldset>
			<legend>Login</legend>
			<label for='username' >Username:</label>
			<input type='text' name='name' id='name' maxlength='50' />
			<input type='hidden' name='command' value='login' />
			<input type='submit' value='Log in' onClick='game.php'>
		</fieldset>
	</form>
</html>";
}

function unknown_command(){

}

function login_command(){
	$_SESSION['username'] = $_GET['name'];
	$_SESSION['room']=0;
	$_SESSION['time_without_water']=3; 			
	$_SESSION['time_without_bandages']=2; 
	$_SESSION['rooms_historic']="0";
	$_SESSION['inventory']=array(
		array(
			"item" => 1,
			"quantity" => 0),
		array(
			"item" => 2,
			"quantity" => 0),
		array(
			"item" => 3,
			"quantity" => 0),
		array(
			"item" => 4,
			"quantity" => 0));

	$_SESSION['world'] = array(
		array(
			"name" => "hall",
			"history" => "John is a retired police officer. Her daughter was kidnapped 4 years ago. Since then, he hasn't stopped searching for her.\nOne hour ago he received an anonymous phone call.\nSomeone had seen his daughter in a house not far from his own house. Without wasting one more second, John started to run towards this house.\nExhausted, he arrives to the house and opens the first door...",
			"color" => "#002D40",
			"visible" => "yes",
			"points" => [[0,0], 200, 100],    // Origin, width, height
			"outs" => array (
							"porte sud" => 1,
							"porte ferme a cle" => 8,
							"porte sud droite" => 4)),
		array(
			"name" => "petite salle",
			"color" => "#FE414D",
			"history" => "John: Looks like I will need some water if I want to keep on searching",
			"visible" => "no",
			"stuff" => array(					
			array(
				"index" => 1,
				"quantity" => 5 )
				),
			"points" => [[0,100], 100, 100],
		   	"outs" => array (
		   					"porte nord" => 0,
							"porte sud" => 2)),
		array(
			"name" => "chambre",
			"color" => "#E8FEFF",
			
			"visible" => "no",
			"points" => [[0,200], 100, 50],
			"outs" => array (
							"porte est" => 5,
							"porte sud" => 3)),
		array(
			"name" => "petite salle",
			"color" => "#002D40",
			"history" => "John: Interesting... There's only one door but two windows...",
			"visible" => "no",
			"points" => [[0,250], 100, 50],
			"outs" => array (
							"fenetre est" => 6,
							"porte nord" => 2,
							"fenetre sud" => 7)),
		array(
			"name" => "chambre",
			"color" => "#1BA5B7",
			"history" => "John: Looks like I will need some water if I want to keep on searching",
			"visible" => "no",
			"points" => [[100,100], 100, 100],
			"outs" => array (
							"porte nord" => 0,
							"porte sud" => 5)),
		array(
			"name" => "petite salle",
			"color" => "#002D40",
			"visible" => "no",
			"stuff" => array(					
						array(
							"index" => 2,
							"quantity" => 1 )
							),
			"points" => [[100,200], 100, 50],
			"outs" => array (
							"porte ouest" => 2,
							"porte nord" => 4)),
		array(
			"name" => "chambre",
			"color" => "#FE414D",
			"history" => "John: Damn! I got cut with the crystals of that window... I'd need some bandages... But my daughter can't wait",
			"visible" => "no",
			"stuff" => array(
							array(
							"index" => 3,
							"quantity" => 1)
							),
			"points" => [[100,250], 100, 50],
			"outs" => array (
							"porte nord" => 5)),
		array(
			"name" => "??",
			"color" => "#1BA5B7",
			"visible" => "no",
			"points" => [[0,300], 500, 200],
			"goal" => "defeat", 
			"outs" => array (
							"fenetre Jaune" => 0,
							"fenetre verte" => 1)), // quitar salidas
		array(
			"name" => "petite salle",
			"color" => "#1BA5B7",
			"history" => "John: Look like I'm getting closer!",
			"visible" => "no",
			"points" => [[200,0], 300, 100],
			"outs" => array (
							"porte sud" => 9,
							"porte sud droite" => 12,
							"escaliers" => 13,
							"porte jardin" => 14)),
		array(
			"name" => "chambre",
			"color" => "#002D40",
			"visible" => "no",
			"points" => [[200,100], 50, 50],
			"outs" => array (
							"porte nord" => 8,
							"porte sud" => 10,
							"porte est" => 12)),
		array(
			"name" => "petite salle",
			"color" => "#FE414D",
			"visible" => "no",
			"points" => [[200,150], 50, 50],
			"outs" => array (
							"porte ferme a cle" => 11,
							"porte nord" => 9)),

		array(
			"name" => "chambre",
			"color" => "#002D40",
			"visible" => "no",
			"points" => [[200,225], 100, 75],
			"goal" => "victory",
			"outs" => array (
							"porte x" => 8,
							"porte y" => 10,
							"porte z" => 12)),
		array(
			"name" => "petite salle",
			"color" => "#E8FEFF",
			"visible" => "no",
			"points" => [[250,100], 50, 50],
			"outs" => array (
							"porte nord" => 8,
							"porte ouest" => 9)),
		array(
			"name" => "stairs",
			"color" => "pink",
			"visible" => "no",
			"points" => [[300,100], 50, 200],
			"outs" => array (
							"escaliers" => 8,
							"porte" => 15)),
		array(
			"name" => "cementery",
			"color" => "blue",
			"history" => "John: Something is wrong in this house. Who has a cementery in his own house? I think I will need to come back here",
			"stuff" => array(
							array(
							"index" => 4,
							"quantity" => 1)
							),
			"visible" => "no",
			"points" => [[350,100], 150, 150],
			"outs" => array (
							"porte" => 8)),
		array(
			"name" => "Boat",
			"color" => "orange",
			"history" => "John: I think I see something in the water...",
			"visible" => "#002D40",
			"points" => [[350,250], 150, 50],
			"outs" => array (
							"escaliers" => 13,
							"sauter" => 7))

);


    default_logged_command();
}

?>
