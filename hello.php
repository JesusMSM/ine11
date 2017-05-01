<?php
session_start();

$objects = array(
	array(
		"nom" => "Bouteille d'eau",
		"index" => 1),
	array(
		"nom" => "Bandage",
		"index" => 2));


if (isset($_SESSION["username"])){
	if ($_GET['command'] === 'logout'){
		logout_command();
	}elseif($_GET['command'] === 'go'){
		$_SESSION['room']=$_GET['destination'];
		default_logged_command();
	}elseif($_GET['command'] === 'take'){
		increase_inventory($_GET['index'],$_GET['quantity']);
		default_logged_command();
	}elseif($_GET['command'] === 'map'){
		world_to_json();
	}
	elseif($_GET['command'] === 'where'){
		position_to_json();
	}
	else{
		default_logged_command();
	}
}else{
	if($_GET['command'] === 'login'){
		login_command();
	}else{
		default_unlogged_command();
	}
}



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

function decrease_stuff($index,$quantity){
	foreach ($_SESSION['world'] as $key => $room) {
		foreach ($room['stuff'] as $key2 => $object) {
			if($index == $object['index']){
				$_SESSION['world'][$key]['stuff'][$key2]['quantity']=0;
			}
		}
	}
}

function default_logged_command(){
	echo 'Bonjour ' . $_SESSION['username'];
	showPosibilities();
	echo "<br><br> <a href='/hello.php?command=logout'>Log out</a>";
}

function login_command(){
	$_SESSION['username'] = $_GET['name'];
	$_SESSION['room']=0;
	$_SESSION['inventory']=array(
		array(
			"item" => 1,
			"quantity" => 0),
		array(
			"item" => 2,
			"quantity" => 0));

	$_SESSION['world'] = array(
		array(
			"name" => "chambre Jaune",
			"color" => "yellow",
			"points" => [[0,0], 100, 100],    // Origin, width, height
			"stuff" => array(
						array(
							"index" => 2,
							"quantity" => 1 ),
						array(
							"index" => 1,
							"quantity" => 2 )
							),
			"outs" => array (
							"porte" => 1,
							"fenetre" => 2)),
		array(
			"name" => "chambre Verte",
			"color" => "green",
			"points" => [[100,0], 100, 100],
			"goal" => "victory",
			"stuff" => array(
							"index" => 1,
							"quantity" => 1 ),
		   	"outs" => array (
		   					"porte" => 0,
							"fenetre" => 2)),
		array(
			"name" => "jardin",
			"color" => "blue",
			"points" => [[50,100], 100, 100],
			"goal" => "defeat",
			"outs" => array (
							"fenetre Jaune" => 0,
							"fenetre verte" => 1))
);


    default_logged_command();
}

function world_to_json(){
	header('Content-Type: application/json');
	echo json_encode($_SESSION['world']);
}

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

function showPosibilities(){

	$objects = $GLOBALS['objects'];

	if(array_key_exists(goal, $_SESSION['world'][$_SESSION['room']])){
		if($_SESSION['world'][$_SESSION['room']]['goal'] === 'victory'){
			echo '<br>Tu as gagne';
		}elseif ($_SESSION['world'][$_SESSION['room']]['goal'] === 'defeat'){
			echo '<br>Tu as perdu';
		}
	}

	echo "<br><br>Inventory:";
	foreach ($_SESSION['inventory'] as $item) {
		foreach ($objects as $object){
			if ($object['index'] == $item['item']){
				echo "<br>Tu as " . $item['quantity'] . " " . $object['nom'];
			}
		}
		
	}

	echo "<br><br>Objects:";
	foreach ($_SESSION['world'][$_SESSION['room']]['stuff'] as $item) {
		foreach ($objects as $object) {
			if ($item['index'] == $object['index']){
				echo "<br>Il y a " . $item['quantity'] . " " . $object['nom'];
				echo " <a href=/hello.php?command=take&index=" . $item['index'] ."&quantity=" . $item['quantity'] ." >Take</a>";
			}
		}
	}

	echo "<br>Carte:";
	echo "<br><canvas id='canvas' width='300' height='300'></canvas>";
	
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
		echo " <a href=/hello.php?command=go&destination=" . $index . ">Y aller</a>";
	}
	echo "<script type='text/javascript' src='javascript.js'></script>";
}

function default_unlogged_command(){
	echo "<html>
	<form id='login' action='hello.php' method='get' accept-charset='UTF-8'>
		<fieldset>
			<legend>Login</legend>
			<label for='username' >Username:</label>
			<input type='text' name='name' id='name' maxlength='50' />
			<input type='hidden' name='command' value='login' />
			<input type='submit' value='Log in' onClick='hello.php'>
		</fieldset>
	</form>
</html>";
}

function unknown_command(){

}


?>


