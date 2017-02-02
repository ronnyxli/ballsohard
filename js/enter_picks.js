
function addPick(currPos, nextPos, saveField) {
	
	// currPos and nextPos should be unique id's

	// advance current pick to next position 
	document.getElementById(nextPos).src = document.getElementById(currPos).src; 
	
	// fill hidden field value with current pick 
	document.getElementById(saveField).value = document.getElementById(currPos).name;
	
	// assign current pick's name to next position's name 
	document.getElementById(nextPos).name = document.getElementById(currPos).name;

	// perform checks 
	var x = document.getElementsByClassName("teamLogo2");
	var i;
	for (i = 0; i < x.length; i++) {
		
	}

}

function clearAllPicks() {
	
	// clear logos 
	var x = document.getElementsByClassName("teamLogo2");
	var i;
	for (i = 0; i < x.length; i++) {
		x[i].src = ""; 
		x[i].value = ""; 
	}
	
	// clear team picks
	var y = document.getElementsByClassName("selectTeam");
	var i;
	for (i = 0; i < x.length; i++) {
		y[i].value = ""; 
	}
	
	// clear games picks 
	var z = document.getElementsByClassName("selectGames");
	var i;
	for (i = 0; i < x.length; i++) {
		z[i].value = ""; 
	}
}


