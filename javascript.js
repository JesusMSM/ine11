
var canvas = document.getElementById('canvas');
var ctx=canvas.getContext('2d');
canvas.style.backgroundColor = 'rgba(158, 167, 184, 0.2)';

var xhr = new XMLHttpRequest();
var xhr2 = new XMLHttpRequest();

/* 
    Function used to read the map in JSON format and color the rooms 
*/


xhr.onreadystatechange = function() {
    if (xhr.readyState == XMLHttpRequest.DONE) {
        var json_world = JSON.parse(xhr.responseText);
            json_world.forEach(function(room){
            if (room.hasOwnProperty("name")){
                if(room.visible === "yes"){                                 // If the user has already passed or its the first time
                    if(room.name === "cementery"){                          // Draw the cementery image if its the case
                        cementery_image = new Image();
                        cementery_image.src = 'cementerio.jpg';
                        cementery_image.onload = function(){
                            ctx.drawImage(cementery_image, 350, 100);
                            xhr2.open('GET', '/game.php?command=where', true);
                            xhr2.send(null);
                        }
                    }else{
                        ctx.fillStyle = room['color'];
                        ctx.fillRect(room['points'][0][0], room['points'][0][1], room['points'][1], room['points'][2]);
                        xhr2.open('GET', '/game.php?command=where', true);
                        xhr2.send(null);
                    }
                }
            }
        })
        
        
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

xhr.open('GET', '/game.php?command=map', true);
xhr.send(null);
