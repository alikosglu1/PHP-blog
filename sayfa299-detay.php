<?php
session_start();
?>
<!DOCTYPE html>
<html lang="tr"><head><title> Blog uygulama Detay </title>
  <meta charset="utf-8" />
  <style> div{ margin:4px; padding:4px} .makale{border:1px solid silver;}
   h3,h4{text-decoration:underline;margin:4px;font-size:16pt;}
.yorum{border:1px solid silver; border-top:1px dashed silver; padding-top:10px}
  </style>
 </head>
<body>
<a href="index.php">Ana Sayfa</a><br />
<?php
/* detay.php?id=1 gibi detay görme isteği varmı? kontrol edelim */
$istek = isset($_GET['id']) ? $_GET['id'] : die('Hatalı istek');
/* Yönetici üye varmı? Bu bilgiyi elde edelim */
$uye   = isset($_SESSION['uye']) ? $_SESSION['uye'] : null;

$db = @new mysqli('localhost', 'root', '1234', 'uygulama');
if ($db->connect_errno)
    die('Bağlantı Hatası:' . $db->connect_error);

/* Tablo veri karakter yapımız utf8 dir, bunu bildirelim */
$db->set_charset("utf8");

/* Blog tablosu ve yorum tablosu için SQL sorgusunu hazırlayalım */
$blog = $db->prepare("SELECT * FROM blog LEFT JOIN yorum USING(blog_id) WHERE blog.blog_id = ?");

/* istek varsa sorgudaki ? için  veri tipini ve değişkeni tanımlayalım */
$blog->bind_param("i", $istek);

/* hazırlanan SQL sorgusunu çalıştıralım */
$blog->execute();

/* blog ve yorum tablosunun sonuçlarını döndürelim(mysqlnd) yoksa çalışmaz */
$blog_sonuc = $blog->get_result();

/* kaç adet sonuç var? öğrenelim, yorum sayısı için kullanacağız */
$sonuc_sayisi = $blog_sonuc->num_rows;

/* Bütün sonuçları bir defada elde edelim (mysqlnd) yoksa çalışmaz */
$rows = $blog_sonuc->fetch_all(MYSQLI_ASSOC);

/* Önce blok bilgilerini ekrana yazdıralım */
if (isset($rows[0])) {
    echo "<div class='makale'><h3> {$rows[0]['baslik']} </h3>
  <i> {$rows[0]['tarih']} tarihinde eklendi</i>
  <p> {$rows[0]['yazi']} </p></div>";
    
    $yorum_var = (($sonuc_sayisi - 1) == 0) ? 0 : $sonuc_sayisi;
    echo '<p>' . $yorum_var . ' yorum var</p>';
    
    /* Varsa yorumları ekrana yazdıralım */
    foreach ($rows as $row) {
        $yid = $row['yorum_id'];
        $bid = $row['blog_id'];
        $sil = ($uye == 1) ? "<a href='admin.php?yorum_sil=$yid&id=$bid'>Sil</a>" : '';
        
        echo "<div class='yorum'> $sil <b> {$row['yazan']} </b>
              <i> {$row['tarih']} </i> <div> {$row['mesaj']}</div></div>";
    }
    /* Yorum ekleme formunu ekrana yazdıralım */
    echo '<hr /><p>Yorum Yap<form method="post" action="yorum.php">
		  <input type="hidden" name="blog_id" value="' . $row['blog_id'] . '"/>
		  Ad Soyad: <input type="text" name="yazan" maxlength="10" /><br />
		  Yorumunuz: <br /><textarea rows="2" cols="30" name="mesaj"></textarea><br />
		  <input type="submit" name="yorum" value="Kaydet"/></form></p>';
} else {
    echo '<h3>Maalesef kayıtlı bir içerik bulamadık</h3>';
}
/* Sorguları ve veritabanı bağlantılarını kapatalım */
$blog->close();
$db->close();
?>
</body>
</html>
