<?php

function get_arr ($indeks,$region,$year)
{
    $conn_string = "host=10.0.0.104 port=5432 dbname=alt user=postgres password=qwerty";
    $dbconn = pg_connect($conn_string);
    if (!$dbconn) {
        echo "An error occured.\n";
        exit;
    }
$query_string = "select date(average_data.date_time) as date, (average_data.alt-river_regions.avg), (filter_data.alt-river_regions.avg), contact_data.level
                            from average_data,contact_data,filter_data,river_regions
                                 where date(average_data.date_time)=contact_data.data AND river_regions.reg_numb=$region
                                 AND date_part('year',filter_data.date_time)=$year
                                 AND date(filter_data.date_time)=contact_data.data AND date(filter_data.date_time)=date(filter_data.date_time)
                                 AND average_data.reg_numb=$region AND contact_data.indeks=$indeks AND filter_data.reg_numb=$region";
eval("\$query_string = \"$query_string\";");

$result = pg_query($dbconn, $query_string);
if (!$result) {
    echo "An error occured.\n";
    exit;

    pg_close($dbconn);
}

  $i=0;
//  echo "<br>";
  $k=pg_num_rows($result);
  while ($i<$k)
  {
      $row = pg_fetch_array($result, $i,PGSQL_NUM);
      $graph_arr[]= array (strtotime($row[0]),$row[1],$row[2],$row[3]);
      //if ($i>10) break;
      $i++;
  }
  pg_close($dbconn);
  return $graph_arr;
}

function cor_graph($graph_arr,$x,$y)
{
    $xsr=0;$ysr=0;
    $xsr2=0;$ysr2=0;
    $num=count($graph_arr);
    if ($num==0) return 0;
    for ($i=0;$i<$num;$i++)
    {
        $xsr+=$graph_arr[$i][$x];
        $ysr+=$graph_arr[$i][$y];
    }
    $xsr=$xsr/$num;$ysr=$ysr/$num;
    for ($i=0;$i<$num;$i++)
    {
        $xsr2+=($graph_arr[$i][$x]-$xsr)*($graph_arr[$i][$x]-$xsr);
        $ysr2+=($graph_arr[$i][$y]-$ysr)*($graph_arr[$i][$y]-$ysr);
    }

    $r=0;
    for ($i=0;$i<$num;$i++)
        $r+=($graph_arr[$i][$x]-$xsr)*($graph_arr[$i][$y]-$ysr);

    $r=$r/sqrt($xsr2*$ysr2);
    return round($r*100, 2);
}

$indeks=6022;
extract ($_POST, EXTR_OVERWRITE);



    $conn_string = "host=10.0.0.104 port=5432 dbname=alt user=postgres password=qwerty";
    $dbconn = pg_connect($conn_string);
    if (!$dbconn)
    {
        echo "An error occured.\n";
        exit;
    }
    $result1 = pg_query($dbconn, "select reg_numb from river_regions group by reg_numb order by reg_numb");

    if (!$result1)
    {
        echo "An error occured.\n";
        exit;
        pg_close($dbconn);
    }
    $i=0;
    $k=pg_num_rows($result1);
    while ($i<$k)
    {
      $row = pg_fetch_array($result1, $i,PGSQL_NUM);
      $region_arr[]= array ($row[0]);
      $i++;
    }
    pg_close($dbconn);

    $num_r=count($region_arr);
for ($j=0;$j<$num_r;$j++)
{
    $region=$region_arr[$j][0];
echo "<table border=1><tr><td colspan=4 align=center>Correlation, % (reg $region,$indeks)</td></tr><tr><td>year</td><td>alt</td><td>alt_avg</td><td>contact</td></tr>";
for ($i=1999;$i<2002;$i++)
{
    $graph_arr=get_arr($indeks,$region,$i);
    $r=cor_graph($graph_arr,1,3);
    $k=cor_graph($graph_arr,2,3);
    $t=cor_graph($graph_arr,3,3);
    echo "<tr><td>$i</td><td>$r</td><td>$k</td><td>$t</td></tr>";
}
echo "</table><br>";
}
?>