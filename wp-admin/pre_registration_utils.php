<?php 

/* check security after */
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

define('ABSPATH' ,'/home/u0164126/public_html/'); 

include_once ABSPATH . 'wp-load.php';
include_once ABSPATH . 'wp-includes/pluggable.php';
include_once ABSPATH . 'wp-includes/functions.php';

if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return '' === $needle || false !== strpos($haystack, $needle);
    }
}


if(!$_POST){
    return false;
}

function check_tc($tc_info = array()){
   $tcno = trim($tc_info['tcid']);
   $ad = trim($tc_info['first_name']);
   $soyad= trim($tc_info['last_name']);
   $dogumtarihi = trim($tc_info['birthyear']);

   $ad=str_replace(array('i','ı','ş','ğ','ö','ç','ü'), array('İ','I','Ş','Ğ','Ö','Ç','Ü'),$ad);
   $soyad=str_replace(array('i','ı','ş','ğ','ö','ç','ü'), array('İ','I','Ş','Ğ','Ö','Ç','Ü'),$soyad);
   $ad=mb_convert_case($ad, MB_CASE_UPPER, "UTF-8");
   $soyad=mb_convert_case($soyad, MB_CASE_UPPER, "UTF-8");

    
   $bilgiler=array("TCKimlikNo"=>$tcno,"Ad"=>$ad,"Soyad"=>$soyad,"DogumYili"=>$dogumtarihi);
   $baglan = new SoapClient("https://tckimlik.nvi.gov.tr/Service/KPSPublic.asmx?WSDL", 
	array('stream_context'=> stream_context_create(
    array('ssl'=> array('verify_peer'=>false,'verify_peer_name'=>false, 'allow_self_signed' => true)))));
   $sonuc = $baglan->TCKimlikNoDogrula($bilgiler);
    
   if($sonuc->TCKimlikNoDogrulaResult){
			return true;
	    }else{
			return false;
    }
    
}

function mailsend($studentInfo = array()){
    require_once ABSPATH . 'wp-includes/class-phpmailer.php';
    require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
    require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
    require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
  
    $headers = "Content-Type: text/html; charset=UTF-8";
    $address_il =$studentInfo['address_il'];
    $address_ilce = $studentInfo['address_ilce'];
    $address_street =$studentInfo['address_street'];
    $address_semt  =$studentInfo['address_semt'];
    $address_daire_no  = $studentInfo['address_daire_no'];
    $address_kat   = $studentInfo['address_kat'];
    
    $address_all = "İl:{$address_il} İlçe:{$address_ilce} Cadde/Mahalle:{$address_street} Semt:{$address_semt} Kat:{$address_kat} Daire No:{$address_daire_no}";
    
    $mail = new PHPMailer\PHPMailer\PHPMailer( true );
    //$mail->SMTPDebug = SMTP::DEBUG_OFF;
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'canorculturizm1997@gmail.com';                     //SMTP username
    $mail->Password   = 'gmedykvjqgcckqix';                               //SMTP password
    //$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
    $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
    //Recipients
    $mail->setFrom('wordpress@canorcul.com', 'Mailer');
    $mail->addAddress('sezerceadres@gmail.com', 'Recipient'); //Add a recipient cilem@canorcul.com
    $mail->addCustomHeader($headers);
    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = 'Yeni Bir Talep Geldi';
    $mail->Body    = "
            <!DOCTYPE html>
<html>
    <head>
        <style>
        table {
          font-family: arial, sans-serif;
          border-collapse: collapse;
        }
        
        td, th {
          border: 1px solid #dddddd;
          text-align: left;
          padding: 8px;
        }
        
        tr:nth-child(even) {
          background-color: #dddddd;
        }
        </style>
    </head>
        <body>
        <h4>Web Sitesinden Doldurulan Ögrenci Form Bilgieri Asagidaki Gibidir</h4>
            <table>
              <tr>
                <th>Ögrenci bilgileri</th>
                 <th>Veli bilgileri</th>
              </tr>
              <tr>
               	<td>TC:{$studentInfo['tcid']}</td>
                <td>Veli TC:{$studentInfo['parent_tcid']}</td>
              </tr>
            <tr>
               <td>Adi&Soyadi: {$studentInfo['first_name']} - {$studentInfo['last_name']}</td>
               <td>Veli Ad:{$studentInfo['parent_full_name']} </td>
            </tr>
             <tr>
               	 <td>Dogum Tarihi:{$studentInfo['birthyear']}</td>
                	<td>Veli Soyadi:{$studentInfo['parent_full_name']}</td>
             </tr>
                <tr>
                	<td>Okulu:{$studentInfo['school']} </td>
                    <td>Veli gsm:{$studentInfo['parent_gsm']}</td>
                </tr>
              <tr>
                <td>Sınıfı:{$studentInfo['period']}</td>
                <td>Veli E-mail:{$studentInfo['parent_mail']}</td>
              </tr>
              <tr>
                <td>GSM:{$studentInfo['gsm']}</td>
              </tr>
              <tr>
                <td>Adres:{$address_all}</td>
              </tr>
            </table>
        </body>
</html>";
    //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
   try{
    $mail->send();
    return true;
   } catch (Exception $e) {
          //$message['error'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";;
           return  false;
        }
}

function userAdd($user_info = array()){
    global $wpdb;

   $stc = trim($user_info['tcid']);
   $sfullname= trim($user_info['first_name'].$user_info['last_name']);
   //$slastname= $user_info['last_name'];
   $sgsm= trim($user_info['gsm']);
   $ptc = trim($user_info['parent_tcid']);
   $pfullname= trim($user_info['parent_full_name']);
   $pgsm= trim($user_info['parent_gsm']);
   $address = trim($user_info['address']);
   $sschool = trim($user_info['school']);
   $sdegree = trim($user_info['period']);
   $today = date("y-m-d");  
   $table_name = 'pre_registration_for_student';
   $data = array(
        'student_tc' => $stc,
        'student_name' => $sfullname,
        'student_gsm' => $sgsm,
        'parent_tc' => $ptc,
        'parent_name' => $pfullname,
        'parent_gsm' => $pgsm,
        'address' => $address,
        'student_school'=> $sschool,
        'student_degree' => $sdegree,
        'date' => $today 
   );

    // Insert data into the database
   $result = $wpdb->insert($table_name, $data);
   
   if ($result) {
        return true;
   } else {
         return false;
   }
}

function userCheck($tcid){
    global $wpdb;
    $querystr =" SELECT student_tc from pre_registration_for_student where student_tc='{$tcid}'";
    $usertc = $wpdb->get_results($querystr, OBJECT);
    return $usertc;
}

if($_POST){
    $error = false;
    foreach($_POST['formdata'] as $fname=> $formdata){
        if(empty($formdata)){
            $error[$fname] = 'this area is empty please check it';
        }
    }
  
    if(is_array($error)){
        return false;
    }
    

   $parent_fullname = explode(" ",$_POST['formdata']['parent_full_name']);
   $parent_infos = array(
       'tcid' => $_POST['formdata']['parent_tcid'],
       'first_name' => $parent_fullname[0],
       'last_name' => $parent_fullname[1],
       'birthyear' => $_POST['formdata']['parent_birtyear']
      );
   if(userCheck($_POST['formdata']['tcid'])){
        $err = "Talebiniz daha önceden alınmış tekrar talep oluşturmak için lüften iletişim kurun.";
         echo json_encode(
                array(
                    'status' =>'failed',
                    'msg'=>$err
                ));
        return false;
   }
   if(!check_tc($_POST['formdata'])){
       $err = "Tc Kimlik Bilgileriniz Doğrulanmadı!";
        echo json_encode(
                array(
                    'status' =>'failed',
                    'msg'=>$err
                ));
        return false;
   }
   if($_POST['formdata']['tcid'] == $_POST['formdata']['parent_tcid']){
        $err = "Veli Bilgileri Öğrenci Bilgileri ile Aynı Olamaz!";
        echo json_encode(
                array(
                    'status' =>'failed',
                    'msg'=>$err
                ));
        return false;
   }
   if(!check_tc($parent_infos)){
       $err = "Veli TC Kimlik Bilgileri Doğrulanmadı!";
        echo json_encode(
                array(
                    'status' =>'failed',
                    'msg'=>$err
                ));
        return false;
   }
   
  /* $address = $_POST['formdata']['address'];
   $is_exist_this_keyword = array('mh'=>'mahalle','cd'=>'cadde','sk'=>'sokak','no','d'=>'daire','semt','/');

    
    $error_msg = false;
    foreach($is_exist_this_keyword as $key=>$is_exist){
        if($is_exist == '/'){
        
              $is_exist = 'il/ilce';
            }
        if(!str_contains($address,$is_exist) && !str_contains($address,$key) && !str_contains($address,'cadde') ){
            $error_msg .= "<br>* {$is_exist}"; 
         
        }
    }
    
    if($error_msg){
        $errors_addr = "Asagidaki gerekli olan adres bilgileri girilmedi lutfen kontrol edin";
        $errors_addr .= $error_msg;
        
        echo json_encode(
        array(
            'status' =>'failed',
            'msg'=> $errors_addr
        ));
        
        
        return false;
    }*/
   
   
   if(!userAdd($_POST['formdata'])){
       $err = "Bir Hata Oluştu Tekrar Deneyiniz!";
        echo json_encode(
                array(
                    'status' =>'failed',
                    'msg'=>$err
                ));
        return false;
   }
  if(!mailsend($_POST['formdata'])){
       $err = "talebiniz iletilemedi lutfen tekrar deneyin!";
        echo json_encode(
                array(
                    'status' =>'failed',
                    'msg'=>$err
                ));
        return false;
   }
 
    
        echo json_encode(
        array(
            'status' =>'success',
            'msg'=>'Talebiniz Alındı, Kısa Süre İçersinde Geri Dönüş Yapılacaktır.'
        ));
  
}


?>
