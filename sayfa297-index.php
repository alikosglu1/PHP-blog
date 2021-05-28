<!DOCTYPE html>
<html lang="tr">
 <head>
  <title> Blog uygulamam </title>
  <meta charset="utf-8" />
  <style>
   div{ margin:4px; padding:4px}
   .makale{border:1px solid silver;}
   h3,h4{text-decoration:underline;margin:4px;font-size:16pt;}
</style>
 </head>
 <body>
 <a href="login.php">Giriş Yap</a><h2>Blog Uygulamam</h2>
 <hr />
<?php
$db = @new mysqli('localhost', 'root', '1234', 'uygulama');
if ($db->connect_errno)
    die('Bağlantı Hatası:' . $db->connect_error);

/* Tablo veri karakter yapımız utf8 dir, bunu bildirelim */
$db->set_charset("utf8");

/* Sayfalama yapmak için Blog tablosundaki toplam kayıt sayısını alalım */
$toplam = $db->query("SELECT count(*) FROM blog");

/* Kaç akyıt var? toplam kayıt sayısını elde edelim */
$sayfa_sayisi = $toplam->fetch_row();

/* Sorgu ile işimiz bittiğine göre kapatalım */
$toplam->close();

$limit = 2;
/* Kaç kayıt gösterilsin? sayfalama için kayıt sayısı */

/* sayfalama için index.php?id=1 gibi istek gelmezse 0 kabul et */
$ofset = isset($_GET['id']) ? $_GET['id'] : 0;

/* Blog, yorum tablosu sonuçlarını limit ve ofset değerine göre elde edelim */
$blog = $db->prepare("SELECT blog.* , COUNT( yorum.yorum_id ) AS yorumadet
FROM blog LEFT JOIN yorum USING ( blog_id ) GROUP BY blog.blog_id LIMIT ? OFFSET ?");

/* SQL sorgusundaki ? ve ? için veri tipini ve değişkenleri tanımlayalım */
$blog->bind_param("ii", $limit, $ofset);
/* hazırlanan SQL sorgusunu çalıştıralım */
$blog->execute();

/* Sonuçlarını döndürelim (mysqlnd) yoksa çalışmaz */
$blog_sonuc = $blog->get_result();

/* Sonuçları sütun adlarına göre elde edelim (mysqlnd) yoksa çalışmaz */
while ($row = $blog_sonuc->fetch_array()) {
    
    /* içeriğin uzunluğu 50 den büyük ise detay için link oluşturalım */
    if (strlen(strip_tags($row['yazi'])) >= 50) {
        $row['yazi'] = substr(strip_tags($row['yazi']), 0, 50);
        $row['yazi'] .= "...<a href='detay.php?id={$row['blog_id']}'>Devamı</a>";
    }
    /* içeriği ekrana yazdıralım */
    echo "<div class='makale'><h3>{$row['baslik']}</h3>
    <i>{$row['tarih']} tarihinde eklendi</i>
    <p>{$row['yazi']}</p>";
    /* Yorum varsa sayısını yazalım */
    echo "<p>{$row['yorumadet']} yorum var</p></div>\n";
}
//Sayfalama için linkleri oluşturalım
if ($sayfa_sayisi[0] > $limit) {
    $x = 0;
    for ($i = 0; $i < $sayfa_sayisi[0]; $i += $limit) {
        $x++;
        echo "<a href='?id=$i'>[ $x ]</a>";
    }
}
$blog->close();
$db->close();
?>
</body>
</html>