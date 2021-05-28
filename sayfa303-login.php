<?php
session_start();
$hata='';
if(isset($_POST['eposta']) && isset($_POST['sifre'])){
$db = @new mysqli('localhost', 'root', '1234', 'uygulama');
if ($db->connect_errno)  die('Bağlantı Hatası:' . $db->connect_error);

/* Tablo veri karakter yapısı */
$db->set_charset("utf8");

$stmt  = $db->prepare("SELECT * FROM uye WHERE email=? AND sifre=MD5(?)");
if ($stmt === false) die('Sorgu hatası:'. $db->error);

/*SQL deki ? için  veri tipini ve değişkeni tanımlayalım */
$stmt->bind_param("ss", $_POST['eposta'],$_POST['sifre']);

//SQL Sorgusunu çalıştıralım
$stmt->execute();

//Sonucu elde edelim
$sonuc = $stmt->get_result();

//email ve şifre doğru ise SESSION ataması yapalım ve
//admin.php yönlendirelim
if($sonuc->num_rows){
   $row = $sonuc->fetch_array();
   $_SESSION['uye'] = $row['durum'];
   $_SESSION['ad']  = $row['ad'];
   header('Location: admin.php');
  }else{
    $hata='<h3>Eposta veya şifre hatalı</h3>';
  }
}
?>
<!DOCTYPE html>
<html lang="tr">
 <head>
  <title> Giriş </title>
  <meta charset="utf-8" />
 </head>
 <body>
 <h2>Giriş Yap</h2>
 <?php echo $hata; ?>
<form method="post" action="">
  <input type="text" name="eposta" />E-posta<br />
  <input type="text" name="sifre" />Şifre<br />
  <input type="submit" value="Giriş" />
</form>
</body>
</html>