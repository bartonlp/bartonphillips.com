<?php
require_once(getenv("SITELOADNAME"));

echo "<h1>Serialize/Unserialize Demo</h1>";
echo "<p>Test1 and Testx are two classes.</p>";

class Test1 {
    public $public = 1;
    protected $protected = 2;
    private $private = 3;
    public $str ="This is Test1";

    public function showP() {
      return "Test1 protected: $this->protected<br>";
    }
    public function setP($x) {
      $this->protected = $x;
    }
}

class Testx extends Test1 {
  public function set($x) {
    echo "In Testx protected: $this->protected<br>";
    $this->protected = $x;
    echo "In Textx protected: $this->protected<br>";
    echo "In Textx setP(88)<br>";
    $this->setP(88);
  }
  public function showOurP() {
    return $this->protected . "<br>";
  }
}

$classes = <<<EOF
class Test1 {
    public \$public = 1;
    protected \$protected = 2;
    private \$private = 3;
    public \$str ="This is Test1";
    public function showP() {
      return "Test1 protected: \$this->protected<br>";
    }
    public function setP(\$x) {
      \$this->protected = \$x;
    }
}

class Testx extends Test1 {
  public function set(\$x) {
    echo "In Testx protected: \$this->protected<br>";
    \$this->protected = \$x;
    echo "In Textx protected: \$this->protected<br>";
    \$this->setP(88);
  }
  public function showOurP() {
    return \$this->protected . "<br>";
  }
}
EOF;

echo "<pre>".escapeltgt($classes)."</pre>";

echo <<<EOF
<p>Now instantiate Test1 as \$t1 and serialize it as \$tt.
Then unserialize \$tt.
</p>
EOF;

$t1 = new Test1;

$tt1 = serialize($t1);
echo "t1 serialized: $tt1<br>";
$x = unserialize($tt1);
vardump("t1 unserialized", $x);

$tx = new Testx;
echo "showP: " .$t1->showP();
echo "t1->setP(9)<br>";
$t1->setP(9);
echo "t1->showP: " . $t1->showP();
echo "tx->set(17)<br>";
$tx->set(17);
echo "t1->showP: " . $t1->showP();
echo "tx->showOurP: " . $tx->showOurP();

echo "<p>unserialize \$tt.</p>";

$x = unserialize($tt1);
vardump("t1 unserialized", $x);

echo "<p>serialize \$tx then unserialize the results.</p>";

$x = serialize($tx);
echo "tx serialized: $x<br>";

$x = unserialize($x);
vardump("tx unserialized", $x);
