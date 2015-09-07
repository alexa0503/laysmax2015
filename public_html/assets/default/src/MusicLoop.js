var music = new Audio();
var eat = new Audio();
function musicLoopInit(){
	setInterval( function(){ 
		if(music.currentTime>10){
			music.currentTime = 0; 
			music.play();
		} 
	},200);
	//music.currentTime = 40;
}
var music_source;
var eat_source;
if (music.canPlayType('audio/mpeg;')) {
	//bg music
    music.type= 'audio/mpeg';
    music_source = 'music/WinterLoop.mp3';
    //eat chips
    eat.type = 'audio/mpeg';
    eat_source = 'music/eatChip.mp3';

} else {
    music.type= 'audio/ogg';
    music_source = 'music/WinterLoop.ogg';
       //eat chips
    eat.type = 'audio/ogg';
    eat_source = 'music/eatChip.ogg';
}
