$(document).ready(function(){
	var images = ["assets/images/s1.jpg", "assets/images/s2.jpg", "assets/images/s3.jpg","assets/images/s4.jpg"];

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