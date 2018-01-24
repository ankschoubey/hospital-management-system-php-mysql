$(document).ready(function(){
	var images = ["images/s1.jpg", "images/s2.jpg", "images/s3.jpg","images/s4.jpg"];

	var i = 1;
	var max = images.length;

	function changeImage(){ 
		document.getElementById("slider").src = images[i++];
		
		if(i==max){
			i=0;
		}
	}

	setInterval(function(){changeImage()}, 3000); 
});