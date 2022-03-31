<?php

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

extract ($_GET, EXTR_OVERWRITE);
extract ($_POST, EXTR_OVERWRITE);



include('./phplot.php');

//Define the object
$graph =& new PHPlot(700,500);

//set data
$graph_arr=get_arr($indeks,$region,$year,$sex);

$graph->SetDataValues($graph_arr);
//$graph->SetPlotAreaWorld(strtotime("01 Jan $year"),"",strtotime("31 Dec $year"),"");
$graph->SetXTickIncrement(2679000);
//Set titles
$graph->SetTitle("Variance of waterlevel ($region region, $indeks hydrostation)");
$n_point=(count($graph_arr));
$graph->SetXTitle("time (number of points=$n_point)");
$graph->SetYTitle("level $year");

//$graph->SetUseTTF('./times.ttf');

$graph->SetXLabelType("time");
$graph->SetXLabelAngle(90);
$graph->SetXTimeFormat("%d %b %y");

$which_data = array("blue");
$which_border = array("red");
$graph->SetDataColors(array("grey","red","blue"),$which_border);
$graph->SetPointShape(array('rect','diamond','dot'));
$graph->SetLineStyles('solid');
$graph->SetLineWidth(array(2,3,3));
$graph->SetPointSize(array(8,10,10));

$r=cor_graph($graph_arr,1,3);
$k=cor_graph($graph_arr,2,3);
$t=cor_graph($graph_arr,3,3);
//set legend
$legend=array("alt  R=$r %","alt_avg  R=$k %","contact  R=$t %");
$graph->SetLegend($legend);

//$graph->SetFont('x_label','times.ttf',25);

//Draw it
$graph->DrawGraph();
$graph->PrintImage();

?>