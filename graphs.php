<?php
//header ("Content-type: image/png");

function get_arr ($indeks,$region,$year,$sex)
{
 //   $conn_string = "host=10.0.0.104 port=5432 dbname=alt user=postgres password=qwerty";
    $dbconn = pg_connect("host=10.0.0.104 port=5432 dbname=alt user=postgres password=qwerty") or die ("test");

    $query_string = "select date(average_data.date_time) as date, (average_data.$sex-river_regions.avg), (filter_data.$sex-river_regions.avg), contact_data.level
                            from average_data,contact_data,filter_data,river_regions
                                 where date(average_data.date_time)=contact_data.data AND river_regions.reg_numb=$region
                                 AND date_part('year',filter_data.date_time)=$year
                                 AND date(filter_data.date_time)=contact_data.data AND date(filter_data.date_time)=date(filter_data.date_time)
                                 AND average_data.reg_numb=$region AND contact_data.indeks=$indeks AND filter_data.reg_numb=$region";
    eval("\$query_string = \"$query_string\";");
    $result = pg_query($dbconn, $query_string) or die("Query failed");

    $i=0;

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

function get_indeks ($indeks)
{
 //   $conn_string = "host=10.0.0.104 port=5432 dbname=alt user=postgres password=qwerty";
    $dbconn = pg_connect("host=10.0.0.104 port=5432 dbname=alt user=postgres password=qwerty") or die ("test");

    $query_string = "select * from contact_list where indeks=$indeks";
    eval("\$query_string = \"$query_string\";");
    $result = pg_query($dbconn, $query_string) or die("Query failed");

    $i=0;
    $row = pg_fetch_array($result, $i,PGSQL_NUM);
    $result= trim(iconv( "UTF-8", "CP1251", "$row[1]"));

  pg_close($dbconn);
  return $result;
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
    $xsr=$xsr/$num;$ysr=$ysr/$num;$r=0;
    for ($i=0;$i<$num;$i++)
    {
        $xsr2+=($graph_arr[$i][$x]-$xsr)*($graph_arr[$i][$x]-$xsr);
        $ysr2+=($graph_arr[$i][$y]-$ysr)*($graph_arr[$i][$y]-$ysr);
        $r+=($graph_arr[$i][$x]-$xsr)*($graph_arr[$i][$y]-$ysr);
    }

    $r=$r/sqrt($xsr2*$ysr2);
    return round($r*100, 2);
}
$indeks=6022;
$region=30;
$year=1999;
$sex='alt';
$output_graph=0;


extract ($_GET, EXTR_OVERWRITE);
extract ($_POST, EXTR_OVERWRITE);


include('./php_gd.php');

$graph =& new PHP_GD(700,500);
$graph->SetDataValues(get_arr($indeks,$region,$year,$sex));
$n_point=(count(get_arr($indeks,$region,$year,$sex)));

//$font="times.ttf"; //шрифт

//названия
/*$graph->SetTitle("Уровень реки в регионе $region с гидропостом $indeks",20,0);
$graph->SetTitleY("Уровень, см",16,90);
$graph->SetTitleX("Время ($n_point измерений за $year год)",16,0);
$graph->SetAxis(3,10,10,'SkyBlue',5,10);
$graph->SetGrid(3,'pink','dashed',10);
//$graph->SetGrid();
$graph->SetLabelX(10,45,'grey');
$graph->SetLabelY(10,45,'salmon');
//$graph->SetLineStyle(1,'peru','dashed');*/

$r1=cor_graph(get_arr($indeks,$region,$year,$sex),1,3);
$r2=cor_graph(get_arr($indeks,$region,$year,$sex),2,3);

$name=(get_indeks($indeks));
if ($output_graph==0)
{
$graph->SetTitle("Уровень реки в регионе $region с гидропостом $name",20,0,'black');
$graph->SetTitleY("Уровень, см",16,90);
$graph->SetTitleX("Время (Ravg=$r1, Rfilt=$r2)",16,0);
$graph->SetGrid();
$graph->DrawGraph();
}
else
{
$graph->SetTitleY("Уровень, см",16,90);
$graph->SetTitleX("Время",16,0);
$graph->SetGrid();
$graph->WriteGraph("D:/$region-$year-$name,Ravg=$r1,Rfilt=$r2.png");
//$graph->WriteGraph("D:/1.png");
}

?>