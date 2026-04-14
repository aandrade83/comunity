

window.onload = (event) => {
  
   console.log('The page has fully loaded');
  
   $("#loginBtn").click(function(){
    
     var pass = $("#pass").val();
     var user = $("#user").val();
     
     $("#loginMsg").hide();

     login(user,pass);

     $(this).css("transform", "translateX(10px)"); // Mueve el botón 10px a la derecha
  });


}

function login(user,pass){
  
 console.log('https://lab.lacallecr.com/VV/apps/login/proccess/actions.php?ac=login&user='+user+'&pass='+pass)  ;
 fetch('https://lab.lacallecr.com/VV/apps/login/proccess/actions.php?ac=login&user='+user+'&pass='+pass)
		.then(function(response) {
		  return response.json();//json
		}).then(function(data){
      console.log(data.login);
      if(data.login == 1){

        if(data.total >= 1){
        window.location.href = 'https://lab.lacallecr.com/VV/apps/Forum/pending.php';
        } else{
          window.location.href = 'https://lab.lacallecr.com/VV/apps/Forum/index.php';
        }
      }
       if(data.login == 2){
        $("#loginMsg").show();
       
      }
      if(data.login == 3){
         $("#loginMsg").show();
       
      }

		}).catch(function(error){
			alert("A system error was detected");
		});

}




