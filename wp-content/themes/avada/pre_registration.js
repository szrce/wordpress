var $j = jQuery.noConflict();

$j(document).ready(function($) {
    // here you can use $ again

    $("#error-pre-registration").hide();
    $("#success-pre-registration").hide();
    
    //$("#success-pre-registration").attr("style","background-color:#6abf16;color:#ffffff;border-color:#ffffff;border-width:1px;");
    //$("#error-pre-registration").attr("style","background-color:#bf1e16;color:#ffffff;border-color:#ffffff;border-width:1px;");

    $("#parent_infos*").hide();
    $("#parent").click(function(){
      $("#parent_infos*").show();
    });
    
    
   $("#pre_registeration").submit(function(f){
       f.preventDefault();
       
       
    
        var formdata =  {
           birthyear  : $("input[name='birthyear']").val(),
           tcid : $("input[name='tcid']").val(),
           first_name : $("input[name='first_name']").val(),
           last_name : $("input[name='last_name']").val(),
           school : $("input[name='school']").val(),
           period : $("input[name='period']").val(),
           gsm : $("input[name='gsm']").val(),
           address : $("textarea[name='address']").val(),
           parent : $("input[name='parent']").val(),
           parent_tcid : $("input[name='parent_tcid']").val(),
           parent_full_name : $("input[name='parent_full_name']").val(),
           parent_gsm : $("input[name='parent_gsm']").val(),
           parent_mail : $("input[name='parent_mail']").val()
        }

        $.post("https://www.canorcul.com/wp-admin/pre_registration_utils.php",
          {
            formdata: formdata
          },
          function(data, status){
           
            var obj = jQuery.parseJSON(JSON.stringify(data));
            if(obj.status == 'failed'){
                $("#success-pre-registration").hide();
                $("#error-pre-registration").show();
                $("#error-pre-registration").text(obj.msg);
            }
            
            if(obj.status == 'success'){
                $("#error-pre-registration").hide();
                $("#success-pre-registration").show();
                $("#success-pre-registration").text(obj.msg);
                
            }
            console.log(obj);
          });



    });
    
  
});

1
