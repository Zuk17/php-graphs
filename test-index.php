<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>

<title>FORM</title>
</head>
<body>


<TABLE width=750 >
<tr valign=top><td align=center>

<form action="index.php" method="post">
<TABLE>
<?php
$indeks=6022;
$region=30;
$year=1999;
$sex='alt';
extract ($_POST, EXTR_OVERWRITE);
?>
<tr>
  <td colspan=2 align=center><h2>График</h2></td></tr>
<tr>
  <td>Year</td>
  <td><input type="text" name="year" value="<?php echo $year; ?>" size="5"/></td></tr>
<tr>
  <td>Index of station</td>
  <td><input type="text" name="indeks" value="<?php echo $indeks; ?>" size="5"/></td></tr>
<tr>
  <td>Number of region</td>
  <td><input type="text" name="region" value="<?php echo $region; ?>" size="5"/></td></tr>
<tr>
  <td><INPUT TYPE="radio" NAME="sex" VALUE="alt" <?php if ($sex=='alt') echo "CHECKED"; ?>>Среднее</td>
  <td><INPUT TYPE="radio" NAME="sex" VALUE="alt_m" <?php if ($sex=='alt_m') echo "CHECKED"; ?>>Медиана</td></tr>
<tr>
  <td colspan=2><input name="submit" type="submit" value="Show graph"></td></tr>
</TABLE>
</form>
</td><td>


<form action="correlation.php" method="post">
<TABLE>
<tr>
  <td colspan=2 align=center><h2>Таблицы R</h2></td></tr>
<tr>
  <td>Index of station</td>
  <td><input type="text" name="indeks" value="6022" size="5"/></td></tr>
<tr>
  <td colspan=2><input name="submit" type="submit" value="Show tables"></td></tr>
</TABLE>
</form>
</td></tr></table>

<?php

echo "<IMG SRC='graphs.php?year=$year&amp;indeks=$indeks&amp;region=$region&amp;sex=$sex&amp;submit=Show+graph'><br>
      <IMG SRC='graphs1.php?year=$year&amp;indeks=$indeks&amp;region=$region&amp;sex=$sex&amp;submit=Show+graph'>";

?>
</body>
</html>