<?php

ini_set('log_errors', 'on');  //ログを取るか
ini_set('error_log', 'php.log');  //ログの出力ファイルを指定
session_start(); //セッション使う

// モンスター達格納用
$monsters = array();
//$human = array();
// 性別クラス
class Sex
{
  const MAN = 1;
  const WOMAN = 2;
  const OKAMA = 3;
}
// 抽象クラス（生き物クラス）
abstract class Creature
{
  protected $name;
  protected $hp;
  protected $attackMin;
  protected $attackMax;
  abstract public function sayCry();
  public function setName($str)
  {
    $this->name = $str;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setHp($num)
  {
    $this->hp = $num;
  }
  public function getHp()
  {
    return $this->hp;
  }
  public function attack($targetObj)
  {
    $attackPoint = mt_rand($this->attackMin, $this->attackMax);
    if (!mt_rand(0, 9)) { //10分の1の確率でクリティカル
      $attackPoint = $attackPoint * 3;
      $attackPoint = (int) $attackPoint;
      History::set($this->getName() . 'のクリティカルヒット!!');
    }
    $targetObj->setHp($targetObj->getHp() - $attackPoint);
    History::set($attackPoint . 'ポイントのダメージ！');
  }
}
// 人クラス
class Human extends Creature
{
  protected $sex;
  protected $healMin;
  protected $healMax;
  // コンストラクタ
  public function __construct($name, $sex, $hp, $attackMin, $attackMax, $healMin, $healMax)
  {
    $this->name = $name;
    $this->sex = $sex;
    $this->hp = $hp;
    $this->attackMin = $attackMin;
    $this->attackMax = $attackMax;
    $this->healMin = $healMin;
    $this->healMax = $healMax;
  }
  public function setSex($num)
  {
    $this->sex = $num;
  }
  public function getSex()
  {
    return $this->sex;
  }
  // 勇者クラスだけが使えるかいふく
  //   public function getHeal(){
  //    return $this->heal;
  //  }
  public function heal($human)
  {
    $healPoint = mt_rand($this->healMin, $this->healMax);
    History::set($this->name . 'は回復魔法を唱えた！');
    $human->setHp($human->getHp() + $healPoint);
    History::set($healPoint . '回復した！');
  }
  public function sayCry()
  {
    History::set($this->name . 'が叫ぶ！');
    switch ($this->sex) {
      case Sex::MAN:
        History::set('ぐはぁっ！');
        break;
      case Sex::WOMAN:
        History::set('きゃっ！');
        break;
      case Sex::OKAMA:
        History::set('もっと！♡');
        break;
    }
  }
}
// モンスタークラス
class Monster extends Creature
{
  // プロパティ
  protected $img;
  // コンストラクタ
  public function __construct($name, $hp, $img, $attackMin, $attackMax)
  {
    $this->name = $name;
    $this->hp = $hp;
    $this->img = $img;
    $this->attackMin = $attackMin;
    $this->attackMax = $attackMax;
  }
  // ゲッター
  public function getImg()
  {
    return $this->img;
  }
  public function sayCry()
  {
    History::set($this->name . 'が叫ぶ！');
    History::set('はうっ！');
  }
}
// 魔法を使えるモンスタークラス
class MagicMonster extends Monster
{
  private $magicAttack;
  function __construct($name, $hp, $img, $attackMin, $attackMax, $magicAttack)
  {
    parent::__construct($name, $hp, $img, $attackMin, $attackMax);
    $this->magicAttack = $magicAttack;
  }
  public function getMagicAttack()
  {
    return $this->magicAttack;
  }
  public function attack($targetObj)
  {
    if (!mt_rand(0, 4)) { //5分の1の確率で魔法攻撃
      History::set($this->name . 'の魔法攻撃!!');
      $targetObj->setHp($targetObj->getHp() - $this->magicAttack);
      History::set($this->magicAttack . 'ポイントのダメージを受けた！');
    } else {
      parent::attack($targetObj);
    }
  }
}
interface HistoryInterface
{
  public static function set($str);
  public static function clear();
}
// 履歴管理クラス（インスタンス化して複数に増殖させる必要性がないクラスなので、staticにする）
class History implements HistoryInterface
{
  public static function set($str)
  {
    // セッションhistoryが作られてなければ作る
    if (empty($_SESSION['history'])) $_SESSION['history'] = '';
    // 文字列をセッションhistoryへ格納
    $_SESSION['history'] .= $str . '<br>';
  }
  public static function clear()
  {
    unset($_SESSION['history']);
  }
}

// インスタンス生成
$human = new Human('アリューゼ', Sex::MAN, 5000, 400, 1200, 100, 600);
$monsters[] = new Monster('スケルトン', 1000, 'img/bone.png', 200, 400);
$monsters[] = new MagicMonster('シュメルケ', 3000, 'img/dorako.png', 200, 600, mt_rand(500, 1000));
$monsters[] = new Monster('ゴブリン', 2000, 'img/gobb.png', 300, 500);
$monsters[] = new MagicMonster('ガーゴイル', 4000, 'img/akuma.png', 500, 800, mt_rand(600, 1200));
$monsters[] = new Monster('シド', 1500, 'img/honekonbou.png', 300, 600);
$monsters[] = new Monster('マタンゴ', 1000, 'img/kinoko.png', 100, 300);
$monsters[] = new Monster('クレイジー', 1200, 'img/tougisya.png', 200, 300);
$monsters[] = new Monster('イカレポンチ', 1800, 'img/nondakure.png', 300, 500);
$monsters[] = new MagicMonster('魔帝ワードナー', 5000, 'img/darkp.png', 1000, 2000, mt_rand(2000, 3000));

function createMonster()
{
  global $monsters;
  $monster =  $monsters[mt_rand(0, 8)];
  History::set($monster->getName() . 'が現れた！');
  $_SESSION['monster'] =  $monster;
}
function createHuman()
{
  global $human;
  $_SESSION['human'] =  $human;
}
function init()
{
  History::clear();
  History::set('初期化します！');
  $_SESSION['knockDownCount'] = 0;
  createHuman();
  createMonster();
}
function gameOver()
{
  $_SESSION = array();
}


//1.post送信されていた場合
if (!empty($_POST)) {
  $attackFlg = (!empty($_POST['attack'])) ? true : false;
  $healFlg = (!empty($_POST['heal'])) ? true : false;
  $startFlg = (!empty($_POST['start'])) ? true : false;
  error_log('POSTされた！');

  if ($startFlg) {
    History::set('ゲームスタート！');
    init();
  } elseif ($attackFlg) {
    // 攻撃するを押した場合

    // モンスターに攻撃を与える
    History::set($_SESSION['human']->getName() . 'の攻撃！');
    $_SESSION['human']->attack($_SESSION['monster']);
    $_SESSION['monster']->sayCry();

    // モンスターが攻撃をする
    History::set($_SESSION['monster']->getName() . 'の攻撃！');
    $_SESSION['monster']->attack($_SESSION['human']);
    $_SESSION['human']->sayCry();

    // 自分のhpが0以下になったらゲームオーバー
    if ($_SESSION['human']->getHp() <= 0) {
      gameOver();
    } elseif ($_SESSION['monster']->getHp() <= 0) {
      // hpが0以下になったら、別のモンスターを出現させる
      History::set($_SESSION['monster']->getName() . 'を倒した！');
      createMonster();
      $_SESSION['knockDownCount'] = $_SESSION['knockDownCount'] + 1;
    }
  } elseif ($healFlg) {
    // 自分のHPを回復させる
    $_SESSION['human']->heal($_SESSION['human']);

    // モンスターが攻撃する
    History::set($_SESSION['monster']->getName() . 'の攻撃');
    $_SESSION['monster']->attack($_SESSION['human']);
    $_SESSION['human']->sayCry();

    // 自分のHPが0以下になったらゲームオーバー
    if ($_SESSION['human']->getHp() <= 0) {
      gameOver();
    }
  } else { //逃げるを押した場合
    History::set('逃げた！');
    createMonster();
  }
  $_POST = array();
}

?>

  <!DOCTYPE html>
  <html>

  <head>
    <meta charset="utf-8">
    <title>ホームページのタイトル</title>
    <link rel="stylesheet" type="text/css" href="style.css" />
    <link href="https://fonts.googleapis.com/css?family=Titillium+Web:400,700&display=swap" rel="stylesheet" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

  </head>

  <body>
    <div style="padding:15px; position:relative;">
      <?php if (empty($_SESSION)) { ?>
        <h1 style="margin-top:60px;">GAME START ?</h1>
        <form method="post">
          <input type="submit" name="start" value="&#x261E;はじめる">
        </form>
      <?php } elseif (!empty($_SESSION)) { ?>
        <h2><?php echo $_SESSION['monster']->getName() . 'が現れた!!'; ?></h2>
        <div style="height: 500px;">
          <img src="<?php echo $_SESSION['monster']->getImg(); ?>" style="width:120px; height:500px; width:300px; margin:40px auto 0 auto; display:block;">
        </div>
        <p style="font-size:20px; text-align:center;">モンスターのHP：<?php echo $_SESSION['monster']->getHp(); ?></p>
        <p>倒したモンスター数：<?php echo $_SESSION['knockDownCount']; ?></p>
        <p>アリューゼの残りHP：<?php echo $_SESSION['human']->getHp(); ?></p>
        <form method="post">
          <input type="submit" name="heal" value="▶かいふく">
          <input type="submit" name="attack" value="▶たたかう">
          <input type="submit" name="escape" value="▶とんずら">
          <input type="submit" name="start" value="▶はじめから">
        </form>
      <?php } elseif ($_SESSION['knockDownCount'] = 10) { ?>
        <div class="game-clear">
          <h1>GAME CLEAR</h1>
          <img src="" alt="">
        </div>
      <?php } ?>
      <div class="message-area" style="position:absolute; right:-350px; top:0; color:white; width: 300px;">
        <p class="history"><?php echo (!empty($_SESSION['history'])) ? $_SESSION['history'] : ''; ?></p>
      </div>

    </div>

  </body>

  </html>
