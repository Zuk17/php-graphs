<?php


class PHP_GD{
    //определние констант(можно изменять)
    var $x=640;
    var $y=480;  //устанавливаем расмер изображения:x,y

    var $font="times.ttf"; //шрифт
    //названия
    var $title;
    var $title_y;
    var $title_x;
    //подписи к осям
    var $label_x;
    var $label_y;
    var $axis;
    var $native_encoding='CP1251';

    var $title_coord=array ("h"=>0,"w"=>0,"xh"=>0,"xw"=>0,"yh"=>0,"yw"=>0);
    var $background_color;
    var $grid_width=1;
    var $grid_style;
    var $grid_show=FALSE;



    var $rgb_array = array(
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
    var $num_points;
    var $num_col;
    var $graph_arr;


    var $line_width=3;
    var $dot_size=10;

//////////////////////////////////////////////////////
//BEGIN CODE
//////////////////////////////////////////////////////

    /*!
     * Constructor: Setup img resource, colors and size of the image, and font sizes.
     *
     * \param which_width       int    Image width in pixels.
     * \param which_height      int    Image height in pixels.
     * \param which_output_file string Filename for output.
     * \param which_input_fule  string Path to a file to be used as background.
     */
    function PHP_GD($which_width, $which_height, $color_background='white')
    {
        register_shutdown_function(array(&$this, '_PHP_GD'));

        $this->x = $which_width;
        $this->y = $which_height;
        $this->im= ImageCreate($this->x, $this->y);
            if (! $this->im)
                $this->PrintError('PHP_GD(): Could not create image resource.');

        $this->background_color=$this->SetIndexColor($color_background);
        ImageColorTransparent($this->im,$this->background_color);
        $this->SetTitle();
        $this->SetTitleX();
        $this->SetTitleY();
        $this->SetLabelX();
        $this->SetLabelY();
        $this->SetAxis();
    }

    function _PHP_GD ()
    {
        ImageDestroy($this->im);
        return;
    }

    function SetIndexColor($which_color)
    {
        list ($r, $g, $b) = $this->rgb_array["$which_color"];  //Translate to RGB
        $index = ImageColorExact($this->im, $r, $g, $b);
        if ($index == -1) {
            return ImageColorResolve($this->im, $r, $g, $b);
        } else {
            return $index;
        }
    }

    function SetAxis($axis_width=3,$num_ticks_x=10,$num_ticks_y=10,$which_color='black',$which_length=5,$zero=10)
    {
        $num_ticks_x=($num_ticks_x<1)?1:$num_ticks_x;
        $num_ticks_y=($num_ticks_y<1)?1:$num_ticks_y;
        $this->axis=array("width"=>$axis_width,
                          "num_ticks_x"=>$num_ticks_x,
                          "num_ticks_y"=>$num_ticks_y,
                          "color"=>$this->SetIndexColor($which_color),
                          "tick_length"=>$which_length,
                          "zero"=>$zero);
    }

    function SetGrid($width=1,$color='grey',$which_style='dashed',$space_dashes=5)
    {
        $this->grid_show=TRUE;
        $dashes = ($which_style=='dashed') ? array_fill(0, $space_dashes, $this->SetIndexColor($color)): array($this->SetIndexColor($color));
        $spaces = ($which_style=='dashed') ? array_fill($space_dashes, $space_dashes, $this->background_color): array();
        $this->grid_width=$width;
        $this->grid_style=(array_merge($dashes, $spaces));
    }

    function SetLabelX($size=10,$angle=45,$color='black')
    {
        $this->label_x=array("size"=>$size,"angle"=>$angle,"color"=>$this->SetIndexColor($color));
    }

    function SetLabelY($size=10,$angle=0,$color='black',$precision=1)
    {
        $this->label_y=array("size"=>$size,"angle"=>$angle,"color"=>$this->SetIndexColor($color),"precision"=>$precision);
    }

    function SetLineStile()
    {

    }

    function SetTitle($text='',$size=20,$angle=0,$which_color='black')
    {
        $this->title=array("text"=>iconv($this->native_encoding,'UTF-8',$text),
                           "size"=>$size,
                           "angle"=>$angle,
                           "color"=>$this->SetIndexColor($which_color));

        $coord= imageftbbox ( $this->title["size"], $this->title["angle"], $this->font, $this->title["text"]);
        $this->title_coord["h"]=abs($coord[1]-$coord[5])+5;
        $this->title_coord["w"]=abs($coord[0]-$coord[4]);
    }

    function SetTitleX($text='',$size=16,$angle=0,$which_color='black')
    {
        $this->title_x=array("text"=>iconv($this->native_encoding,'UTF-8',$text),
                             "size"=>$size,
                             "angle"=>$angle,
                             "color"=>$this->SetIndexColor($which_color));

        $coord= imageftbbox ( $this->title_x['size'], $this->title_x['angle'], $this->font, $this->title_x['text']);
        $this->title_coord["xh"]=abs($coord[1]-$coord[5])+3;
        $this->title_coord["xw"]=abs($coord[0]-$coord[4]);
    }

    function SetTitleY($text='',$size=16,$angle=90,$which_color='black')
    {
        $this->title_y=array("text"=>iconv($this->native_encoding,'UTF-8',$text),
                             "size"=>$size,
                             "angle"=>$angle,
                             "color"=>$this->SetIndexColor($which_color));

        $coord= imageftbbox ( $this->title_y['size'], 0, $this->font, $this->title_y['text']);
        $this->title_coord["yh"]=abs($coord[0]-$coord[4])+4;
        $this->title_coord["yw"]=abs($coord[1]-$coord[5]);
    }

    function SetDataValues($graph_arr)
    {
        $this->graph_arr=$graph_arr;
        //определение параметров массива
        $this->num_points=count($this->graph_arr);
        $this->num_col=count($this->graph_arr[0]);
    }


    function CalcMinMax()
    {
        //определение диапазонов измерения (в реальных координатах)
        $y_min=$this->graph_arr[0][1];$y_max=$this->graph_arr[0][1];
        $x_min=$this->graph_arr[0][0];$x_max=$this->graph_arr[0][0];
        for ($i=1;$i<=$this->num_points;$i++)
        {
              for ($j=1;$j<=$this->num_col-1;$j++)
              {
                  if ($y_min>$this->graph_arr[$i-1][$j])  {$y_min=$this->graph_arr[$i-1][$j];}
                  if ($y_max<$this->graph_arr[$i-1][$j])  {$y_max=$this->graph_arr[$i-1][$j];}
              }
              if ($x_min>$this->graph_arr[$i-1][0])  $x_min=$this->graph_arr[$i-1][0];
              if ($x_max<$this->graph_arr[$i-1][0])  $x_max=$this->graph_arr[$i-1][0];
        }
        $this->y_min=$y_min;
        $this->y_max=$y_max;
        $this->x_min=mktime(0, 0, 0, strftime("%m",$x_min), 1, strftime("%Y",$x_min));
        $this->x_max=mktime(0, 0, 0, strftime("%m",$x_max)+1, 1, strftime("%Y",$x_max));

        //вычислем ширину и высоту подписей к графикам
        for ($i=0;$i<=$this->axis["num_ticks_x"];$i++)
        {
            $value=$this->x_min+$i*abs($this->x_max-$this->x_min)/$this->axis["num_ticks_x"];
            $coord= imageftbbox ($this->label_x["size"], $this->label_x["angle"], $this->font, strftime("%Y-%m-%d",$value));
            if ($i==0) $this->label_h=abs($coord[1]-$coord[5]);
            if ($this->label_h<abs($coord[1]-$coord[5])) $this->label_h=abs($coord[1]-$coord[5]);
        }
        for ($i=0;$i<=$this->axis["num_ticks_y"];$i++)
        {
            $value=round($this->y_min+$i*abs($this->y_max-$this->y_min)/$this->axis["num_ticks_y"],$this->axis["precision"]);
            $coord= imageftbbox ($this->label_y["size"], $this->label_y["angle"], $this->font, $value);
            if ($i==0) $this->label_w=abs($coord[0]-$coord[4]);
            if ($this->label_w<abs($coord[0]-$coord[4])) $this->label_w=abs($coord[0]-$coord[4]);
        }
        $this->label_w+=10;

        //вычисление коэффициентов перехода от координат к точкам
        $this->ny=(($this->y-$this->title_coord["h"]-$this->title_coord["xh"]-$this->label_h)/abs($this->y_max-$this->y_min));
        if ($this->y_max<=0) {$this->zero_value["y"]=$this->title_coord["h"]+20;}
        elseif ($this->y_min>=0) {$this->zero_value["y"]=$this->y-$this->title_coord["xh"]-$this->label_h-20;}
        else {$this->zero_value["y"]=$this->title_coord["h"]+$this->y_max*$this->ny;}

        $this->nx=(($this->x-$this->title_coord["yw"]-$this->label_w)/abs($this->x_max-$this->x_min));
        $this->zero_value["x"]=$this->title_coord["yw"]+$this->label_w+5;

        $this->tick_value_y=($this->y-$this->title_coord["h"]-$this->title_coord["xh"]-$this->label_h)/$this->axis["num_ticks_y"];
        $this->tick_value_x=($this->x-$this->zero_value["x"]-30)/$this->axis["num_ticks_x"];
    }
//------------------------------------------------------------------------>
    function DrawTitle()
    {
        //непосредственно отрисовываем названия
        imagefttext ($this->im, $this->title["size"], $this->title["angle"], floor(abs($this->x-$this->title_coord["w"])/2), $this->title_coord["h"]-8, $this->title["color"], $this->font, $this->title["text"]);
        imagefttext ($this->im, $this->title_y["size"], $this->title_y["angle"], $this->title_coord["yw"]-4, floor(($this->y+$this->title_coord["h"]-$this->title_coord["xh"]+$this->title_coord["yh"]-$this->label_h)/2), $this->title_y["color"], $this->font, $this->title_y["text"]);
        imagefttext ($this->im, $this->title_x["size"], $this->title_x["angle"], floor(($this->x+$this->title_coord["yw"]-$this->title_coord["xw"]+$this->label_w)/2), $this->y-8,$this->title_x["color"], $this->font, $this->title_x["text"]);
    }

    function DrawAxis()
    {
        //Начало координат
        imagefilledellipse ($this->im, $this->zero_value["x"], $this->zero_value["y"], $this->axis["zero"], $this->axis["zero"], $this->axis["color"]);

        //Рисуем ось ординат с подписями
        for ($i=0;$i<=$this->axis["num_ticks_y"];$i++)
        {
            ImageSetThickness($this->im, $this->axis["width"]);
            imageline($this->im,$this->zero_value["x"]-$this->axis["tick_length"],$this->title_coord["h"]+$i*$this->tick_value_y,$this->zero_value["x"]+$this->axis["tick_length"],$this->title_coord["h"]+$i*$this->tick_value_y, $this->axis["color"]);
            ImageSetThickness($this->im, 1);
            $value=round($this->y_max-$i*abs($this->y_max-$this->y_min)/$this->axis["num_ticks_y"],$this->label_y["precision"]);
            $coord= imageftbbox ($this->label_y["size"], $this->label_y["angle"], $this->font, $value);
            imagefttext ($this->im, $this->label_y["size"], $this->label_y["angle"], $this->zero_value["x"]-abs($coord[0]-$coord[4])-10, $this->title_coord["h"]+$i*$this->tick_value_y+abs($coord[1]-$coord[5])/2, $this->label_y["color"], $this->font, $value);
        }

        //Рисуем ось абсцисс с подписями
        for ($i=0;$i<=$this->axis["num_ticks_x"];$i++)
        {
            ImageSetThickness($this->im, $this->axis["width"]);
            imageline($this->im,$this->zero_value["x"]+$i*$this->tick_value_x, $this->zero_value["y"]-$this->axis["tick_length"],$this->zero_value["x"]+$i*$this->tick_value_x,$this->zero_value["y"]+$this->axis["tick_length"],$this->axis["color"]);
            ImageSetThickness($this->im, 1);
            $value=$this->x_min+$i*abs($this->x_max-$this->x_min)/$this->axis["num_ticks_x"];
            $coord= imageftbbox ($this->label_x["size"], $this->label_x["angle"], $this->font, strftime("%Y-%m-%d",$value));
            imagefttext ($this->im, $this->label_x["size"], $this->label_x["angle"], $this->zero_value["x"]+$i*$this->tick_value_x-abs($coord[0]-$coord[4])/2, $this->y-$this->title_coord["xh"]-2,$this->label_x["color"], $this->font, strftime("%Y-%m-%d",$value));
        }
        ImageSetThickness($this->im, $this->axis["width"]);
        imageline($this->im, $this->zero_value["x"], $this->title_coord["h"], $this->zero_value["x"], $this->y-$this->title_coord["xh"]-$this->label_h,$this->axis["color"]);
        imageline($this->im, $this->zero_value["x"], $this->zero_value["y"], $this->x, $this->zero_value["y"],$this->axis["color"]);
        ImageSetThickness($this->im, 1);
    }

    function DrawGrid()
    {
        imagesetstyle($this->im, $this->grid_style);
        ImageSetThickness($this->im, $this->grid_width);
        for ($i=0;$i<=$this->axis["num_ticks_y"];$i++)
        {
            imageline ($this->im,$this->zero_value["x"],$this->title_coord["h"]+$i*$this->tick_value_y,$this->x,$this->title_coord["h"]+$i*$this->tick_value_y,IMG_COLOR_STYLED);//Grid
        }

        for ($i=0;$i<=$this->axis["num_ticks_x"];$i++)
        {
            imageline ($this->im,$this->zero_value["x"]+$i*$this->tick_value_x,$this->title_coord["h"],$this->zero_value["x"]+$i*$this->tick_value_x,$this->y-$this->title_coord["xh"]-$this->label_h,IMG_COLOR_STYLED);//Grid
        }
        ImageSetThickness($this->im, 1);
    }

    function DrawBorder()
    {
        //Рисуем рамку вокруг изображения
       //imagerectangle ($this->im, 0, 0, $this->x-1, $this->y-1, $this->title["color"]);
    }
//------------------------------------------------------------------------>
    function DrawDyn()
    {
        $this->SetGrid(3,'red','dashed',10);
        imagesetstyle($this->im, $this->grid_style);
        //А теперь займемся динамикой
        for ($i=1;$i<=$this->num_points;$i++)
        {
            ImageSetThickness($this->im, $this->line_width);
            if ($i!=$this->num_points) imageline($this->im,floor($this->zero_value["x"]+$this->nx*($this->graph_arr[$i-1][0]-$this->x_min)), floor($this->zero_value["y"]-$this->ny*$this->graph_arr[$i-1][1]),
                    floor($this->zero_value["x"]+$this->nx*($this->graph_arr[$i][0]-$this->x_min)), floor($this->zero_value["y"]-$this->ny*$this->graph_arr[$i][1]),$this->SetIndexColor('grey'));
            if ($i!=$this->num_points) imageline($this->im,floor($this->zero_value["x"]+$this->nx*($this->graph_arr[$i-1][0]-$this->x_min)), floor($this->zero_value["y"]-$this->ny*$this->graph_arr[$i-1][2]),
                    floor($this->zero_value["x"]+$this->nx*($this->graph_arr[$i][0]-$this->x_min)), floor($this->zero_value["y"]-$this->ny*$this->graph_arr[$i][2]),$this->SetIndexColor('blue'));
            if ($i!=$this->num_points) imageline($this->im,floor($this->zero_value["x"]+$this->nx*($this->graph_arr[$i-1][0]-$this->x_min)), floor($this->zero_value["y"]-$this->ny*$this->graph_arr[$i-1][3]),
                    floor($this->zero_value["x"]+$this->nx*($this->graph_arr[$i][0]-$this->x_min)), floor($this->zero_value["y"]-$this->ny*$this->graph_arr[$i][3]),IMG_COLOR_STYLED);
            ImageSetThickness($this->im, 1);
            imagefilledellipse($this->im,floor($this->zero_value["x"]+$this->nx*($this->graph_arr[$i-1][0]-$this->x_min)),floor($this->zero_value["y"]-$this->ny*$this->graph_arr[$i-1][1]), $this->dot_size, $this->dot_size, $this->SetIndexColor('grey'));
            imagefilledellipse($this->im,floor($this->zero_value["x"]+$this->nx*($this->graph_arr[$i-1][0]-$this->x_min)),floor($this->zero_value["y"]-$this->ny*$this->graph_arr[$i-1][2]), $this->dot_size, $this->dot_size, $this->SetIndexColor('blue'));
            imagefilledellipse($this->im,floor($this->zero_value["x"]+$this->nx*($this->graph_arr[$i-1][0]-$this->x_min)),floor($this->zero_value["y"]-$this->ny*$this->graph_arr[$i-1][3]), $this->dot_size, $this->dot_size, $this->SetIndexColor('red'));
        }
    }

//------------------------------------------------------------------------>
    function DrawStats()
    {
        //отрисовывание статики
        if ($this->grid_show)  $this->DrawGrid();
        $this->DrawAxis();
        $this->DrawTitle();
        $this->DrawBorder();
    }

    function DrawGraph()
    {
        $this->CalcMinMax();
        $this->DrawStats();
        $this->DrawDyn();
        Imagepng($this->im);
    }
    function WriteGraph($path)
    {
        $this->CalcMinMax();
        $this->DrawStats();
        $this->DrawDyn();
        Imagepng($this->im,"$path");
    }

}


?>
