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
<tr>
  <td>Год</td>
  <td><select name="year">
<?php
    $dbconn = pg_connect("host=10.0.0.104 port=5432 dbname=alt user=postgres password=qwerty") or die ("test");

    $query_string = "select date_part('year',date_time) from average_data group by date_part('year',date_time) order by date_part('year',date_time)";
    eval("\$query_string = \"$query_string\";");
    $result = pg_query($dbconn, $query_string) or die("Query failed");

    $i=0;
  $k=pg_num_rows($result);
  while ($i<$k)
  {
      $row = pg_fetch_array($result, $i,PGSQL_NUM);
      if ($row[0]==$year) echo "<OPTION SELECTED>$row[0]";
      else echo "<OPTION>$row[0]";
      $i++;
  }
  pg_close($dbconn);
?>
  </select></td></tr>
<tr>
  <td>Гидропост</td>
  <td><select name="indeks">
<?php
    $dbconn = pg_connect("host=10.0.0.104 port=5432 dbname=alt user=postgres password=qwerty") or die ("test");

    $query_string = "select indeks from contact_list order by indeks";
    eval("\$query_string = \"$query_string\";");
    $result = pg_query($dbconn, $query_string) or die("Query failed");

    $i=0;
  $k=pg_num_rows($result);
  while ($i<$k)
  {
      $row = pg_fetch_array($result, $i,PGSQL_NUM);
      if ($row[0]==$indeks) echo "<OPTION SELECTED>$row[0]";
      else echo "<OPTION>$row[0]";
      $i++;
  }
  pg_close($dbconn);
?>
  </select></td></tr>
<tr>
  <td>Регион</td>
  <td><select name="region">
<?php
    $dbconn = pg_connect("host=10.0.0.104 port=5432 dbname=alt user=postgres password=qwerty") or die ("test");

    $query_string = "select reg_numb from average_data group by reg_numb order by reg_numb";
    eval("\$query_string = \"$query_string\";");
    $result = pg_query($dbconn, $query_string) or die("Query failed");

    $i=0;
  $k=pg_num_rows($result);
  while ($i<$k)
  {
      $row = pg_fetch_array($result, $i,PGSQL_NUM);
      if ($row[0]==$region) echo "<OPTION SELECTED>$row[0]";
      else echo "<OPTION>$row[0]";
      $i++;
  }
  pg_close($dbconn);
?>
  </select></td></tr>
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
  <td>Гидропост</td>
  <td><select name="indeks">
<?php
    $dbconn = pg_connect("host=10.0.0.104 port=5432 dbname=alt user=postgres password=qwerty") or die ("test");

    $query_string = "select indeks from contact_list order by indeks";
    eval("\$query_string = \"$query_string\";");
    $result = pg_query($dbconn, $query_string) or die("Query failed");

    $i=0;
  $k=pg_num_rows($result);
  while ($i<$k)
  {
      $row = pg_fetch_array($result, $i,PGSQL_NUM);
      if ($row[0]==$indeks) echo "<OPTION SELECTED>$row[0]";
      else echo "<OPTION>$row[0]";
      $i++;
  }
  pg_close($dbconn);
?>
  </select></td></tr>
  <td colspan=2><input name="submit" type="submit" value="Show tables"></td></tr>
</TABLE>
</form>
</td></tr></table>

<?php
echo "<IMG SRC='graphs.php?year=$year&amp;indeks=$indeks&amp;region=$region&amp;sex=$sex&amp;submit=Show+graph'>";
echo "<IMG SRC='graphs.php?year=$year&amp;indeks=$indeks&amp;region=$region&amp;sex=$sex&amp;output_graph=1&amp;submit=Show+graph', HEIGHT=0, WIDTH=0>";
?>
</body>
</html>