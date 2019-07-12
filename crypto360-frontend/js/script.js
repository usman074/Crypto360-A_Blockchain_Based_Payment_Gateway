
//table load
  $(document).ready( function () {
    $('#datatable').DataTable();
  } );

  //settings icon change
  var settings_bole=true;
  document.getElementById("nav_tab").addEventListener("click", function(){
      if(settings_bole==true)
      {
        document.getElementById("icon").className="fas fa-minus-circle pull-right"
        settings_bole=false;
         console.log("if block")
            }
      else
      {
        document.getElementById("icon").className="fas fa-plus-circle pull-right"
        settings_bole=true;
      }
  });

  //login modal
  function ShowLogin()
  {
    document.getElementById('id01').style.display='block';
  }

  //forget password modal
  function ShowForgetPass()
  {
    document.getElementById('id01').style.display='none';
    document.getElementById('id02').style.display='block';
  }
  //signup modal
  function ShowSignup()
  {
    document.getElementById('id03').style.display='block';
  }
  
  
