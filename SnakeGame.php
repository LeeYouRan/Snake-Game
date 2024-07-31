<?php
namespace snake;
/**
 * php贪吃蛇
 */
class snake{
  /**
   * 构造方法
   */
  public function __construct(){
    $this->app="http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
  }
  /**
   * 读取session模拟的虚拟缓存显示到屏幕上
   * @return [type] [description]
   */
  public function print(){
    $score=!$this->get("score")?0:$this->get("score");//得分
    //html长字符串
    $html=<<<MAP_STRING
    <meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<title>snake</title>
<style>
table{
    background-color:#000;
  }
  table td{
    padding:1px 1px 1px 1px;
    width:16px;height:12px;
    color:red;
  }
  table tr{
    background-color:#000;
  }
  .sn{
    background:#fff;
  }
  .bright{background:#fff;}
  button{font-size:20px;}
  marquee{width:400px;height:400px;font-size:50px;color:red;background:#000;text-align:center;}
</style>
<p>php贪吃蛇->[分数：{$score}]</p>
MAP_STRING;
    $map="<table>";
    //初始化高亮区域$bright，包括蛇体和食物
    if(!$this->get("snake")){
      $bright=[];
    }else{
      $bright=array_merge($this->get("snake"),[$this->get("food")]);
    }
    //标记蛇体和食物高亮
    for($i=0;$i<30;$i++){
      $map.="<tr>";
      for($j=0;$j<30;$j++){
        if(in_array([$j,$i],$bright)){
          $map.="<td class='bright'></td>";
        }else{
          $map.="<td></td>";
        }
      }
      $map.="</tr>";
    }
    $map.="</table>";
    //控制区域长字符串
    $controll=<<<CONTROLL
    <a href="{$this->app}?isOn=on"><button>start</button></a>
    ........................
    <a href="{$this->app}?isOn=on&direction=up"><button>up</button></a>
    <br><a href="{$this->app}?isOn=off"><button>stop</button></a>
    ...............
    <a href="{$this->app}?isOn=on&direction=left"><button>left</button></a>
    <a href="{$this->app}?isOn=on&direction=right"><button>right</button></a>
    <br>............................................
    <a href="{$this->app}?isOn=on&direction=down"><button>down</button></a>
CONTROLL;
    if(isset($_GET["isOn"])&&$_GET["isOn"]=="on"){
      header("refresh: 1");//每一秒刷新一次页面
    }
    if(isset($_GET["msg"])){
      //收到游戏结束的消息
      echo $html."<marquee direction=up>{$_GET["msg"]}</marquee>".$controll;
    }else{
      //游戏画面显示
      echo $html.$map.$controll;
    }
  }
  /**
   * 设置虚拟显存session中的数据
   * @param [type] $k [description]
   * @param [type] $v [description]
   */
  public function set($k,$v){
    $_SESSION[$k]=$v;
  }
  /**
   * 读取虚拟缓存session中的数据
   * @param  [type] $k [description]
   * @return [type]    [description]
   */
  public function get($k){
    return isset($_SESSION[$k])?$_SESSION[$k]:false;
  }
  /**
   * 贪吃蛇算法，添头去尾、吃食物、撞墙判断、咬自己判断
   * @return [type] [description]
   */
  public function cpu(){
    session_start();
    //游戏若暂停状态则不需计算不需修改虚拟缓存
    if(!(isset($_GET["isOn"])&&$_GET["isOn"]=="on")){
      return;
    }
    //初始化蛇体和食物
    if(!$this->get("snake")){
      $this->set("snake",[
        [29,29]
      ]);
      $this->set("score",0);
      $this->getFood();
      return;
    }
    //初始化运动方向
    if(!isset($_GET["direction"])){
      $this->set("direction","left");
    }else{
      $this->set("direction",$_GET["direction"]);
    }
    $snake=$this->get("snake");
    //计算蛇头坐标
    switch($this->get("direction")){
      case "up":{
        $snakeHead=[
          $snake[0][0],
          $snake[0][1]-1
        ];
        break;
      }
      case "down":{
        $snakeHead=[
          $snake[0][0],
          $snake[0][1]+1
        ];
        break;
      }
      case "left":{
        $snakeHead=[
          $snake[0][0]-1,
          $snake[0][1]
        ];
        break;
      }
      case "right":{
        $snakeHead=[
          $snake[0][0]+1,
          $snake[0][1]
        ];
        break;
      }
    }
    //咬到自己，游戏结束
    if(in_array($snakeHead,$snake)){
      $this->gameOver();
      return;
    }
    //添加蛇头坐标
    array_unshift($snake,$snakeHead);
    //撞墙，游戏结束
    if($snake[0][0]<0||$snake[0][1]<0||$snake[0][0]>29||$snake[0][1]>29){
      $this->gameOver();
      return;
    }
    //咬到食物得一分
    if(in_array($this->get("food"),$snake)){
      $this->getFood();
      $this->set("score",$this->get("score")+1);
    }else{
      unset($snake[count($snake)-1]);
    }
    $this->set("snake",$snake);
  }
  /**
   * 取得食物
   * @return [type] [description]
   */
  public function getFood(){
    $food=[mt_rand(1, 29),mt_rand(1, 29)];
    $this->set("food",$food);
    if(in_array($food,$this->get("snake"))){
      $this->getFood();
    }
  }
  /**
   * 游戏结束
   * @return [type] [description]
   */
  public function gameOver(){
    session_unset();
    header("location:".$this->app."?msg=gameover");
  }
  /**
   * 程序入口
   * @return [type] [description]
   */
  public function main(){
    $this->cpu();
    $this->print();
  }
}
(new snake())->main();
