<?php
$con = mysql_connect('localhost','root','root');
$db_con = mysqli_select_db('uberridedelivery',$con);

$sql = "SELECT * FROM language_label WHERE vValue = ' '";
$db_lang1 = mysqli_query($condbc,$sql);
$count = mysqli_num_rows($db_lang1);
$cnt = 0;
if($count > 0)
{
	while ( $row = mysqli_fetch_array($db_lang1))
	{
		#echo "<br> id = ".$row['LanguageLabelId'];
		$sql1 = mysqli_query($conn,"SELECT * FROM language_label WHERE vLabel = '".$row['vLabel']."' AND vCode = 'EN'");
		$db_lang_v = mysqli_fetch_array($sql1);

		$sql2 = "UPDATE language_label SET vValue = '".$db_lang_v['vValue']."' WHERE LanguageLabelId = ".$row['LanguageLabelId'];
		$db_lang_1 = mysqli_query($condbc,$sql2);
		$count1 = mysqli_affected_rows($db_lang_1);
		if($count1 == 1)
		{
			$cnt++;
		}
	}
}
#echo "cnt = ".$cnt;
?>