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
//данные для графика (из постгреса)
$graph_arr=get_arr($indeks,$region,$year,$sex);


class PHPlot {

    /* I have removed internal variable declarations, some isset() checking was required,
     * but now the variables left are those which can be tweaked by the user. This is intended to
     * be the first step towards moving most of the Set...() methods into a subclass which will be
     * used only when strictly necessary. Many users will be able to put default values here in the
     * class and thus avoid memory overhead and reduce parsing times.
     */
    //////////////// CONFIG PARAMETERS //////////////////////

    var $is_inline = FALSE;             // FALSE = Sends headers, TRUE = sends just raw image data
    var $browser_cache = FALSE;         // FALSE = Sends headers for browser to not cache the image,
                                        // (only if is_inline = FALSE also)

    var $safe_margin = 5;               // Extra margin used in several places. In pixels

    var $x_axis_position = '';          // Where to draw both axis (world coordinates),
    var $y_axis_position = '';          // leave blank for X axis at 0 and Y axis at left of plot.

    var $xscale_type = 'linear';        // linear, log
    var $yscale_type = 'linear';

//Fonts
    var $use_ttf  = FALSE;                  // Use True Type Fonts?
    var $ttf_path = '.';                    // Default path to look in for TT Fonts.
    var $default_ttfont = 'benjamingothic.ttf';
    var $line_spacing = 4;                  // Pixels between lines.

    // Font angles: 0 or 90 degrees for fixed fonts, any for TTF
    var $x_label_angle = 0;                 // For labels on X axis (tick and data)
    var $y_label_angle = 0;                 // For labels on Y axis (tick and data)
    var $x_title_angle = 0;                 // Don't change this if you don't want to screw things up!
    var $y_title_angle = 90;                // Nor this.
    var $title_angle = 0;                   // Or this.

//Formats
    var $file_format = 'png';
    var $output_file = '';                  // For output to a file instead of stdout

//Data
    var $data_type = 'text-data';           // text-data, data-data-error, data-data, text-data-single
    var $plot_type= 'linepoints';           // bars, lines, linepoints, area, points, pie, thinbarline, squared

    var $label_scale_position = 0.5;        // Shifts data labes in pie charts. 1 = top, 0 = bottom
    var $group_frac_width = 0.7;            // value from 0 to 1 = width of bar groups
    var $bar_width_adjust = 1;              // 1 = bars of normal width, must be > 0

    var $y_precision = 1;
    var $x_precision = 1;

    var $data_units_text = '';              // Units text for 'data' labels (i.e: '¤', '$', etc.)

// Titles
    var $title_txt = '';

    var $x_title_txt = '';
    var $x_title_pos = 'plotdown';          // plotdown, plotup, both, none

    var $y_title_txt = '';
    var $y_title_pos = 'plotleft';          // plotleft, plotright, both, none


//Labels
    // There are two types of labels in PHPlot:
    //    Tick labels: they follow the grid, next to ticks in axis.   (DONE)
    //                 they are drawn at grid drawing time, by DrawXTicks() and DrawYTicks()
    //    Data labels: they follow the data points, and can be placed on the axis or the plot (x/y)  (TODO)
    //                 they are drawn at graph plotting time, by Draw*DataLabel(), called by DrawLines(), etc.
    //                 Draw*DataLabel() also draws H/V lines to datapoints depending on draw_*_data_label_lines

    // Tick Labels
    var $x_tick_label_pos = 'plotdown';     // plotdown, plotup, both, xaxis, none
    var $y_tick_label_pos = 'plotleft';     // plotleft, plotright, both, yaxis, none

    // Data Labels:
    var $x_data_label_pos = 'plotdown';     // plotdown, plotup, both, plot, all, none
    var $y_data_label_pos = 'plotleft';     // plotleft, plotright, both, plot, all, none

    var $draw_x_data_label_lines = FALSE;   // Draw a line from the data point to the axis?
    var $draw_y_data_label_lines = FALSE;   // TODO

    // Label types: (for tick, data and plot labels)
    var $x_label_type = '';                 // data, time. Leave blank for no formatting.
    var $y_label_type = '';                 // data, time. Leave blank for no formatting.
    var $x_time_format = '%H:%m:%s';        // See http://www.php.net/manual/html/function.strftime.html
    var $y_time_format = '%H:%m:%s';        // SetYTimeFormat() too...

    // Skipping labels
    var $x_label_inc = 1;                   // Draw a label every this many (1 = all) (TODO)
    var $y_label_inc = 1;
    var $_x_label_cnt = 0;                  // internal count FIXME: work in progress

    // Legend
    var $legend = '';                       // An array with legend titles
    var $legend_x_pos = '';
    var $legend_y_pos = '';


//Ticks
    var $x_tick_length = 5;                 // tick length in pixels for upper/lower axis
    var $y_tick_length = 5;                 // tick length in pixels for left/right axis

    var $x_tick_cross = 3;                  // ticks cross x axis this many pixels
    var $y_tick_cross = 3;                  // ticks cross y axis this many pixels

    var $x_tick_pos = 'plotdown';           // plotdown, plotup, both, xaxis, none
    var $y_tick_pos = 'plotleft';           // plotright, plotleft, both, yaxis, none

    var $num_x_ticks = '';
    var $num_y_ticks = '';

    var $x_tick_inc = '';                   // Set num_x_ticks or x_tick_inc, not both.
    var $y_tick_inc = '';                   // Set num_y_ticks or y_tick_inc, not both.

    var $skip_top_tick = FALSE;
    var $skip_bottom_tick = FALSE;
    var $skip_left_tick = FALSE;
    var $skip_right_tick = FALSE;

//Grid Formatting
    var $draw_x_grid = FALSE;
    var $draw_y_grid = TRUE;

    var $dashed_grid = TRUE;
    var $grid_at_foreground = FALSE;        // Chooses whether to draw the grid below or above the graph

//Colors and styles       (all colors can be array (R,G,B) or named color)
    var $color_array = 'small';             // 'small', 'large' or array (define your own colors)
                                            // See rgb.inc.php and SetRGBArray()
    var $i_border = array(194, 194, 194);
    var $plot_bg_color = 'white';
    var $bg_color = 'white';
    var $label_color = 'black';
    var $text_color = 'black';
    var $grid_color = 'black';
    var $light_grid_color = 'gray';
    var $tick_color = 'black';
    var $title_color = 'black';
    var $data_colors = array('SkyBlue', 'green', 'orange', 'blue', 'orange', 'red', 'violet', 'azure1');
    var $error_bar_colors = array('SkyBlue', 'green', 'orange', 'blue', 'orange', 'red', 'violet', 'azure1');
    var $data_border_colors = array('black');

    var $line_widths = 1;                  // single value or array
    var $line_styles = array('solid', 'solid', 'dashed');   // single value or array
    var $dashed_style = '2-4';              // colored dots-transparent dots

    var $point_sizes = array(5,5,3);         // single value or array
    var $point_shapes = array('diamond');   // rect, circle, diamond, triangle, dot, line, halfline, cross

    var $error_bar_size = 5;                // right and left size of tee
    var $error_bar_shape = 'tee';           // 'tee' or 'line'
    var $error_bar_line_width = 1;          // single value (or array TODO)

    var $plot_border_type = 'sides';        // left, sides, none, full
    var $image_border_type = 'none';        // 'raised', 'plain', 'none'

    var $shading = 5;                       // 0 for no shading, > 0 is size of shadows in pixels

    var $draw_plot_area_background = FALSE;
    var $draw_broken_lines = FALSE;          // Tells not to draw lines for missing Y data.

//определние констант(можно изменять)
$x=640;$y=480;  //устанавливаем размер изображения:x,y
$font="times.ttf"; //шрифт
//названия
$title="TitleMain";$title_size=20;$title_angle=0;
$title_y="TitleY";$title_y_size=18; $title_y_angle=90;
$title_x="TitleX";$title_x_size=18; $title_x_angle=0;
$title_color='black';
//подписи к осям
$label_x_size=10; $label_x_angle=45;
$label_y_size=10; $label_y_angle=0;
$axis_width=1; //толщина оси
$axis_color='black';
//количество ticks
$num_ticks_y=10;
$num_ticks_x=10;

$line_width=3;
//определение параметров массива
$num_points=count($graph_arr);
$num_col=count($graph_arr[0]);

//поехали
$im = imagecreate ($x, $y);
$white = imagecolorallocate($im,255,255,255);

//объявляем цвета
    function SetRGBArray ($which_color_array)
    {
            $rgb_array = array(
                'white'          => array(255, 255, 255),
                'snow'           => array(255, 250, 250),
                'PeachPuff'      => array(255, 218, 185),
                'ivory'          => array(255, 255, 240),
                'lavender'       => array(230, 230, 250),
                'black'          => array(  0,   0,   0),
                'DimGrey'        => array(105, 105, 105),
                'gray'           => array(190, 190, 190),
                'grey'           => array(190, 190, 190),
                'navy'           => array(  0,   0, 128),
                'SlateBlue'      => array(106,  90, 205),
                'blue'           => array(  0,   0, 255),
                'SkyBlue'        => array(135, 206, 235),
                'cyan'           => array(  0, 255, 255),
                'DarkGreen'      => array(  0, 100,   0),
                'green'          => array(  0, 255,   0),
                'YellowGreen'    => array(154, 205,  50),
                'yellow'         => array(255, 255,   0),
                'orange'         => array(255, 165,   0),
                'gold'           => array(255, 215,   0),
                'peru'           => array(205, 133,  63),
                'beige'          => array(245, 245, 220),
                'wheat'          => array(245, 222, 179),
                'tan'            => array(210, 180, 140),
                'brown'          => array(165,  42,  42),
                'salmon'         => array(250, 128, 114),
                'red'            => array(255,   0,   0),
                'pink'           => array(255, 192, 203),
                'maroon'         => array(176,  48,  96),
                'magenta'        => array(255,   0, 255),
                'violet'         => array(238, 130, 238),
                'plum'           => array(221, 160, 221),
                'orchid'         => array(218, 112, 214),
                'purple'         => array(160,  32, 240),
                'azure1'         => array(240, 255, 255),
                'aquamarine1'    => array(127, 255, 212)
                );
        return TRUE;
    }

$red = imagecolorallocate ($im, 255, 0, 0);
$black = imagecolorallocate ($im, 0, 0, 0);
$grey = imagecolorallocate ($im,190, 190, 190);
$blue = imagecolorallocate ($im,0, 0, 255);
ImageColorTransparent($im,$white);

//Рисуем рамку вокруг изображения
imagerectangle ($im, 0, 0, $x-1, $y-1, $black);




//Вычисляем координаты заголовока и заголовков осей
$title_coord= imageftbbox ( $title_size, $title_angle, $font, $title);
$title_h=abs($title_coord[1]-$title_coord[7])+8;
$title_w=abs($title_coord[0]-$title_coord[2])+4;
$title_y_coord= imageftbbox ( $title_y_size, 0, $font, $title_y);
$title_yw=abs($title_y_coord[1]-$title_y_coord[7])+10;
$title_yh=abs($title_y_coord[0]-$title_y_coord[2])+4;
$title_x_coord= imageftbbox ( $title_x_size, $title_x_angle, $font, $title_x);
$title_xh=abs($title_x_coord[1]-$title_x_coord[7])+10;
$title_xw=abs($title_x_coord[0]-$title_x_coord[2])+4;
unset ($title_coord);
unset ($title_y_coord);
unset ($title_x_coord);

//определение диапазонов измерения (в реальных координатах)
$y_min=$graph_arr[0][1];$y_max=$graph_arr[0][1];
$x_min=$graph_arr[0][0];$x_max=$graph_arr[0][0];
for ($i=1;$i<=$num_points;$i++)
{
  for ($j=1;$j<=$num_col-1;$j++)
  {
      if ($y_min>$graph_arr[$i-1][$j])  {$y_min=$graph_arr[$i-1][$j];}
      if ($y_max<$graph_arr[$i-1][$j])  {$y_max=$graph_arr[$i-1][$j];}
  }
  if ($x_min>$graph_arr[$i-1][0])  $x_min=$graph_arr[$i-1][0];
  if ($x_max<$graph_arr[$i-1][0])  $x_max=$graph_arr[$i-1][0];
}
$x_min=mktime(0, 0, 0, strftime("%m",$x_min), 1, strftime("%Y",$x_min));
$x_max=mktime(0, 0, 0, strftime("%m",$x_max)+1, 1, strftime("%Y",$x_max));

//вычислем ширину и высоту подписей к графикам
for ($i=0;$i<=$num_ticks_x;$i++)
{
    $value=$x_min+$i*abs($x_max-$x_min)/$num_ticks_x;
    $title_coord= imageftbbox ($label_x_size, $label_x_angle, $font, strftime("%Y-%m-%d",$value));
    if ($i==0) $label_h=abs($title_coord[1]-$title_coord[5]);
    if ($label_h<abs($title_coord[1]-$title_coord[5])) $label_h=abs($title_coord[1]-$title_coord[5]);
}
for ($i=0;$i<=$num_ticks_y;$i++)
{
    $value=$y_min+$i*abs($y_max-$y_min)/$num_ticks_y;
    $title_coord= imageftbbox ($label_y_size, $label_y_angle, $font, strftime($value));
    if ($i==0) $label_w=abs($title_coord[0]-$title_coord[4]);
    if ($label_w<abs($title_coord[0]-$title_coord[4])) $label_w=abs($title_coord[0]-$title_coord[4]);
}
$label_w+=10;

//вычисление коэффициентов перехода от координат к точкам
$ny=(($y-$title_h-$title_xh-$label_h)/abs($y_max-$y_min));
if ($y_max<=0) {$zero_value_y=$title_h+20;}
    elseif ($y_min>=0) {$zero_value_y=$y-$title_xh-$label_h-20;}
        else {$zero_value_y=$title_h+$y_max*$ny;}

$nx=(($x-$title_yw-$label_w)/abs($x_max-$x_min));
$zero_value_x=$title_yw+$label_w+5;

$tick_value_y=($y-$title_h-$title_xh-$label_h)/$num_ticks_y;
$tick_value_x=($x-$zero_value_x-30)/$num_ticks_x;


//отрисовывание статики
//непосредственно отрисовываем названия
imagefttext ($im, $title_size, $title_angle, floor(($x-$title_w)/2), $title_h-4, $red, $font, $title);
imagefttext ($im, $title_y_size, $title_y_angle, $title_yw-2, floor(($y+$title_h-$title_xh+$title_yh-$label_h)/2), $red, $font, $title_y);
imagefttext ($im, $title_x_size, $title_x_angle, floor(($x-$title_yw+$title_xw-$label_w)/2), $y-5, $red, $font, $title_x);
//Начало координат
imagefilledellipse ($im, $zero_value_x, $zero_value_y, 10, 10, $black);
//Рисуем ось ординат с подписями

$style = array($grey, $grey, $grey, $grey, $grey, $white, $white, $white, $white, $white);
imagesetstyle($im, $style);
for ($i=0;$i<=$num_ticks_y;$i++)
{
    imageline ($im,$zero_value_x,$title_h+$i*$tick_value_y,$x,$title_h+$i*$tick_value_y,IMG_COLOR_STYLED);
    imagefilledrectangle($im,$zero_value_x-5,$title_h+$i*$tick_value_y-$axis_width,$zero_value_x+5,$title_h+$i*$tick_value_y+$axis_width,$black);
    $value=$y_max-$i*abs($y_max-$y_min)/$num_ticks_y;
    $title_coord= imageftbbox ($label_y_size, $label_y_angle, $font, $value);
    imagefttext ($im, $label_y_size, $label_y_angle, $zero_value_x-abs($title_coord[0]-$title_coord[4])-10, $title_h+$i*$tick_value_y+abs($title_coord[1]-$title_coord[5])/2, $red, $font, $value);
}
//Рисуем ось абсцисс с подписями
$style = array($grey, $white);
imagesetstyle($im, $style);
for ($i=0;$i<=$num_ticks_x;$i++)
{
    imageline ($im,$zero_value_x+$i*$tick_value_x,$title_h,$zero_value_x+$i*$tick_value_x,$y-$title_xh-$label_h,IMG_COLOR_STYLED);
    imagefilledrectangle($im,$zero_value_x+$i*$tick_value_x-$axis_width,$zero_value_y-5,$zero_value_x+$i*$tick_value_x+$axis_width,$zero_value_y+5,$black);
    $value=$x_min+$i*abs($x_max-$x_min)/$num_ticks_x;
    $title_coord= imageftbbox ($label_x_size, $label_x_angle, $font, strftime("%Y-%m-%d",$value));
    imagefttext ($im, $label_x_size, $label_x_angle, $zero_value_x+$i*$tick_value_x-abs($title_coord[0]-$title_coord[4])/2, $y-$title_xh-2, $red, $font, strftime("%Y-%m-%d",$value));
}
imagefilledrectangle($im, $zero_value_x-$axis_width, $title_h, $zero_value_x+$axis_width, $y-$title_xh-$label_h, $black);
imagefilledrectangle($im, $zero_value_x, $zero_value_y-$axis_width, $x, $zero_value_y+$axis_width, $black);

//А теперь займемся динамикой
  $dot_size=10;
for ($i=1;$i<=$num_points;$i++)
{
   ImageSetThickness($im, $line_width);
    if ($i!=$num_points) imageline($im,floor($zero_value_x+$nx*($graph_arr[$i-1][0]-$x_min)), floor($zero_value_y-$ny*$graph_arr[$i-1][1]),
                    floor($zero_value_x+$nx*($graph_arr[$i][0]-$x_min)), floor($zero_value_y-$ny*$graph_arr[$i][1]),$grey);
    if ($i!=$num_points) imageline($im,floor($zero_value_x+$nx*($graph_arr[$i-1][0]-$x_min)), floor($zero_value_y-$ny*$graph_arr[$i-1][2]),
                    floor($zero_value_x+$nx*($graph_arr[$i][0]-$x_min)), floor($zero_value_y-$ny*$graph_arr[$i][2]),$blue);
    if ($i!=$num_points) imageline($im,floor($zero_value_x+$nx*($graph_arr[$i-1][0]-$x_min)), floor($zero_value_y-$ny*$graph_arr[$i-1][3]),
                    floor($zero_value_x+$nx*($graph_arr[$i][0]-$x_min)), floor($zero_value_y-$ny*$graph_arr[$i][3]),$red);
   ImageSetThickness($im, 1);
    //ImageArc($im,$zero_value_x+$nx*$graph_arr[$i][0],$zero_value_y+$ny*$graph_arr[$i][1], $dot_size, $dot_size, 0, 360, $red);
    imagefilledellipse($im,floor($zero_value_x+$nx*($graph_arr[$i-1][0]-$x_min)),floor($zero_value_y-$ny*$graph_arr[$i-1][1]), $dot_size, $dot_size, $grey);
    imagefilledellipse($im,floor($zero_value_x+$nx*($graph_arr[$i-1][0]-$x_min)),floor($zero_value_y-$ny*$graph_arr[$i-1][2]), $dot_size, $dot_size, $blue);
    imagefilledellipse($im,floor($zero_value_x+$nx*($graph_arr[$i-1][0]-$x_min)),floor($zero_value_y-$ny*$graph_arr[$i-1][3]), $dot_size, $dot_size, $red);

}
for ($i=1;$i<=$num_points;$i++)
{

}

//razmetka dokymenta
/*
imagerectangle ($im, 0, 0, $x, $title_h, $black);//title
imagerectangle ($im, 0, $title_h, $title_yw, $y, $black);//title_y
imagerectangle ($im, 0, $y-$title_xh, $x, $y, $black);//title_x
imagerectangle ($im, $title_yw, $title_h, $title_yw+$label_w, $y, $black);//label_y
imagerectangle ($im, 0, $y-$title_xh-$label_h, $x, $y-$title_xh, $black);//label_x
*/

imagepng($im);
ImageDestroy($im);
/*
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
  */
?>