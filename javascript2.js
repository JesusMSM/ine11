
var canvas = document.getElementById('canvas');
var ctx=canvas.getContext('2d');
canvas.style.backgroundColor = 'rgba(158, 167, 184, 0.2)';
/*base_image = new Image();
base_image.src = 'bonhomme.png';
ctx.drawImage(base_image, 50, 50);*/
/*ctx.rect(20, 20, 150, 100);
ctx.fillStyle = 'green';
ctx.fill();*/

var xhr = new XMLHttpRequest();
var xhr2 = new XMLHttpRequest();

xhr.onreadystatechange = function() {
    if (xhr.readyState == XMLHttpRequest.DONE) {
    	var json_world = JSON.parse(xhr.responseText);
/*

    	for (var i = 0; i < json_world.length; i++){
		    var room = json_world[i];
		    ctx.fillStyle = room['color'];
	        ctx.fillRect(room['points'][0][0], room['points'][0][1], room['points'][1], room['points'][2]);
		    
		}
       */ json_world.forEach(function(room){
        	if (room.hasOwnProperty("name")){
	        	ctx.fillStyle = room['color'];
	        	ctx.fillRect(room['points'][0][0], room['points'][0][1], room['points'][1], room['points'][2]);
        	}
        })
        xhr2.open('GET', '/hello.php?command=where', true);
		xhr2.send(null);
        
    }
}


xhr2.onreadystatechange = function() {
    if (xhr2.readyState == XMLHttpRequest.DONE) {
    	var json_position = JSON.parse(xhr2.responseText);
        base_image = new Image();
 		base_image.src = 'bonhomme.png';
  		base_image.onload = function(){
    		ctx.drawImage(base_image, json_position.x-25, json_position.y-25);
 	 	}
    }
}

xhr.open('GET', '/hello.php?command=map', true);
xhr.send(null);

