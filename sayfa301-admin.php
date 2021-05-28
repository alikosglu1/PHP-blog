<?php
ob_start();
session_start();
$db = @new mysqli('localhost', 'root', '1234', 'uygulama');
if ($db->connect_errno)
    die('Bağlantı Hatası:' . $db->connect_error);

/* Tablo veri karakter yapısı */
$db->set_charset("utf8");

//üye giriş yapmış mı? ve üye admin değilse, yönlendirelim
//üye tablosunda admin değeri 1, normal üye değeri 2 ile tanımlanmıştı
if (isset($_SESSION['uye'])) {
    if ($_SESSION['uye'] == 2) {
        header('Location: index.php');
    }
} else {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="tr">
 <head>
  <title> Blog uygulamam </title>
  <meta charset="utf-8" />
  <style>
   body{font-family: "Times New Roman", arial; font-size:12pt;}
   div{border:1px solid silver; margin:4px; padding:4px}
   .yorum div{border:0; border-top:1px solid silver}
</style>
 </head>
 <body>
 <a href="index.php">Ana Sayfa</a><br />
<?php
//Kayıt istek biçimlerine göre SQL sorgusu belirleyelim
if (isset($_POST['ekle'])) {
    $sql = "INSERT INTO blog(baslik, yazi) VALUES(? , ?)";
    
} else if (isset($_POST['guncelle'])) {
    $sql = "UPDATE blog SET baslik=?,yazi=? WHERE blog_id = ?";
    
} else if (isset($_GET['sil'])) {
    $sql = "DELETE FROM blog WHERE blog_id = ?";
    
} else if (isset($_GET['yorum_sil'])) {
    $sql         = "DELETE FROM yorum WHERE yorum_id = ?";
    $_GET['sil'] = $_GET['yorum_sil'];
    
}

if (isset($_POST['ekle']) || isset($_POST['guncelle'])) {
    
    //SQL sorgusunu hazırlayalım
    $stmt = $db->prepare($sql);
    if ($stmt === false)
        die('Sorgu hatası:' . $db->error);
    
    /*SQL deki ?,? için  veri tiplerini ve değişkenleri tanımlayalım */
    if ($_POST['ekle']){
        $stmt->bind_param("ss", $_POST['baslik'], $_POST['yazi']);
    }
    if ($_POST['guncelle']){
        $stmt->bind_param("ssi", $_POST['baslik'], $_POST['yazi'], $_POST['blog_id']);
    }
    //Sorguyu çalıştıralım
    $stmt->execute();
    if ($db->affected_rows < 1) {
        die('Kayıt eklenmedi');
    }
    $stmt->close();
} else if (isset($_GET['sil']) || isset($_GET['yorum_sil'])) {
    
    //Silme işlemi için SQL sorgusunu hazırlayalım
    $stmt = $db->prepare($sql);
    if ($stmt === false)
        die('Sorgu hatası:' . $db->error);
    
    /*SQL deki ? için  veri tipini ve değişkeni tanımlayalım */
    $stmt->bind_param("i", $_GET['sil']);
    
    //SQL Sorgusunu çalıştıralım
    $stmt->execute();
    if ($db->affected_rows < 1) {
        die('Kayıt silinmedi');
    }
    if (isset($_GET['yorum_sil']))
        header('Location: detay.php?id=' . $_GET['id']);
    $stmt->close();
}

if (isset($_GET['guncelle'])) {
    
    //Güncelleme isteğini elde etmek için SQL sorgusunu hazırlayalım
    $stmt = $db->prepare("SELECT * FROM blog WHERE blog_id = ?");
    
    /*SQL deki ? için  veri tipini ve değişkeni tanımlayalım */
    $stmt->bind_param("i", $_GET['guncelle']);
    
    //SQL Sorgusunu çalıştıralım
    $stmt->execute();
    
    //Sonuçları alalım
    $sonuc = $stmt->get_result();
    
    //Sonuçları sütun adlarına göre elde edelim
    $row = $sonuc->fetch_array();
    
    //Güncelleme için bilgileri forma yazalım
    echo '<h3>Kayıt güncelle</h3>
		  <form method="post" action="admin.php">
		  <input type="hidden" name="blog_id" value="' . $row['blog_id'] . '"/>
		  Başlık: <input type="text" name="baslik" value="' . $row['baslik'] . '" />
		  <br />Açıklama:<br/>
		  <textarea rows="5" cols="30" name="yazi">' . $row['yazi'] . '</textarea>
		  <br /><input type="submit" name="guncelle" value="Kaydet" />
		  </form>';
    $stmt->close();
} else {
    echo '<h3>Kayıt Ekle</h3>
		   <form method="post" action="admin.php">
		   Başlık: <input type="text" name="baslik" />
		  <br />Açıklama:<br/>
		   <textarea rows="5" cols="30" name="yazi"></textarea>
		   <br /><input type="submit" name="ekle" value="Kaydet" />
		  </form>';
}

//Blog başlıklarını listelemek için SQL sorgusunu hazırlayalım
$blog = $db->prepare("SELECT * FROM blog");

//SQL sorgusunu çalıştıralım
$blog->execute();

//blog tablosunun sonuçlarını elde edelim
$blog_sonuc = $blog->get_result();

//blog tablosunun sonuçlarını sütun adlarına göre elde edelim
//Başlıkları okutup ,güncelleme ve silme için linkleri oluşturalım
echo '<hr /><table border=1>';
while ($row = $blog_sonuc->fetch_array()) {
    echo "<tr>
		  <td>{$row['baslik']}</td><td>
		  <a href='?guncelle={$row['blog_id']}'>Güncelle</a>
		  <a href='?sil={$row['blog_id']}' onclick=\"return confirm('Silinsin mi?')\">Sil</a>
		  </td>
		  </tr>\n";
}
echo '</table>';
$blog->close();
$db->close();
?>
</body>
</html>
<?php
ob_end_flush();
?>