###############################################################
#															  #
#				Projet INE11 - Jesus Sanchez				  #					
#															  #
###############################################################


This is the readme of the project of the cours INE11. It is a game based on PHP and JavaScript as proposed by the teachers.
My game is a maze where a retired police officer has to find his lost daughter. In order to make it harder for the user, I have added 2 lock doors and theirs keys as well as water and bandages.
If the user does not drink water for 3 room movements he dies/passes out. There is a room that is accesible only by a window. In this room there is a key that lets the user progress through the map. 

However, the character will get hurt when entering through the window so he will need to use the bandages. It is not too complicated because the user will always find the bandages (they are after the room with the key) but the message that John (the character) might change the user's mind. There are one room and one boat where the user can die if he jumps into the water (even if it is not obvious for the user).

In addition, the second key is placed in the cementery but the user will not be able to find it the first time he goes there. As John says when he enters, the user will have to return there, and the second time he enters he will be able to find it. 

With this key, he can finally go inside the room where his daughter is locked and complete the game.

The rooms are only visible only if the user has already passed by them to make it harder. This is done using JavaScript.

A solution map is provided due the complexity of the game.

The map JSON has been modified to provide the data like this: [origin.x, origin.y, width, height] because its the format asked by the canvas.

JavaScript is used for the map colouring and the position of the "bonhomme" as well as for the cementery image.