<?php
namespace vakata\phptree\test;

use vakata\phptree\Node;

class TreeTest extends \PHPUnit_Framework_TestCase
{
	protected static $db       = null;
	protected static $tree     = null;

	public static function setUpBeforeClass() {
		self::$db = new \vakata\database\DB('mysqli://root@127.0.0.1/test');
		self::$db->query("DROP TABLE IF EXISTS struct");
		self::$db->query("
			CREATE TABLE IF NOT EXISTS struct (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`lft` int(10) unsigned NOT NULL,
				`rgt` int(10) unsigned NOT NULL,
				`lvl` int(10) unsigned NOT NULL,
				`pid` int(10) unsigned NOT NULL,
				`pos` int(10) unsigned NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		self::$db->query("INSERT INTO struct VALUES (1, 1, 2, 0, 0, 0)");
	}
	public static function tearDownAfterClass() {
		self::$db->query("DROP TABLE struct");
	}
	protected function setUp() {
		// self::$db->query("TRUNCATE TABLE test;");
	}
	protected function tearDown() {
		// self::$db->query("TRUNCATE TABLE test;");
	}

	public function testConstruct() {
		self::$tree = new \vakata\phptree\Tree(self::$db, 'struct', ['id' => 'id', 'parent' => 'pid', 'position' => 'pos', 'level' => 'lvl', 'left' => 'lft', 'right' => 'rgt']);
	}
	/**
	 * @depends testConstruct
	 */
	public function testEmptyTree() {
		$this->assertEquals([], self::$tree->getRoot()->getChildren());
	}
	/**
	 * @depends testConstruct
	 */
	public function testCreate() {
		self::$tree->getRoot()->addChild(new Node());
		self::$tree->save();
		$this->assertEquals(2, self::$tree->getNode(2)->id);
		$this->assertEquals(1, self::$tree->getNode(2)->pid);
		$this->assertEquals(0, self::$tree->getNode(2)->pos);
		$this->assertEquals(2, self::$tree->getNode(2)->lft);
		$this->assertEquals(3, self::$tree->getNode(2)->rgt);
		$this->assertEquals(1, self::$tree->getNode(2)->lvl);
		$this->assertEquals([], $this->analyze());
	}
	/**
	 * @depends testCreate
	 */
	public function testCreateLeft() {
		self::$tree->getRoot()->addChild(new Node(), 0);
		self::$tree->save();
		$this->assertEquals(2, count(self::$tree->getRoot()->getChildren()));
		$this->assertEquals(0, count(self::$tree->getNode(3)->getChildren()));
		$this->assertEquals([], $this->analyze());
	}
	/**
	 * @depends testCreateLeft
	 */
	public function testCreateRight() {
		self::$tree->getRoot()->addChild(new Node(), 4);
		self::$tree->save();
		$this->assertEquals(3, count(self::$tree->getRoot()->getChildren()));
		$this->assertEquals(1, self::$tree->getNode(4)->getParent()->id);
		$this->assertEquals([], $this->analyze());
	}
	/**
	 * @depends testCreateRight
	 */
	public function testCreateInner() {
		self::$tree->getNode(2)->addChild(new Node());
		self::$tree->getNode(3)->addChild(new Node());
		self::$tree->save();
		$this->assertEquals([], $this->analyze());
	}
	/**
	 * @depends testCreateRight
	 */
	public function testDeepCreate() {
		$node = self::$tree->getNode(6);
		for ($i = 7; $i <= 30; $i++) {
			$temp = new Node();
			$node->addChild($temp);
			$node = $temp;
		}
		self::$tree->save();
		$this->assertEquals(26, self::$tree->getNode(30)->lvl);
		$this->assertEquals([], $this->analyze());
	}
	/**
	 * @depends testDeepCreate
	 */
	public function testWideCreate() {
		$node = self::$tree->getNode(4);
		$this->assertEquals(true, $node->isLeaf());
		for ($i = 0; $i < 30; $i++) {
			$temp = new Node();
			$node->addChild($temp);
		}
		self::$tree->save();
		$this->assertEquals(2, self::$tree->getNode(60)->lvl);
		$this->assertEquals(30, count(self::$tree->getNode(4)->getChildren()));
		$this->assertEquals(false, self::$tree->getNode(4)->isLeaf());
		$this->assertEquals([], $this->analyze());
	}
	/**
	 * @depends testWideCreate
	 */
	public function testReorderRight() {
		self::$tree->getNode(2)->moveTo(self::$tree->getNode(1), 10);
		self::$tree->save();
		$this->assertEquals([], $this->analyze());
	}
	/**
	 * @depends testReorderRight
	 */
	public function testReorderLeft() {
		self::$tree->getNode(2)->moveTo(self::$tree->getNode(1), 0);
		self::$tree->save();
		$this->assertEquals([], $this->analyze());
	}
	/**
	 * @depends testDeepCreate
	 */
	public function testMoveLeft() {
		self::$tree->getNode(40)->moveTo(self::$tree->getNode(1), 0);
		self::$tree->save();
		$this->assertEquals([], $this->analyze());
	}
	/**
	 * @depends testMoveLeft
	 */
	public function testMoveRight() {
		self::$tree->getNode(40)->moveTo(self::$tree->getNode(4), 0);
		self::$tree->save();
		$this->assertEquals([], $this->analyze());
	}
	/**
	 * @depends testDeepCreate
	 */
	public function testCopyLeft() {
		self::$tree->getNode(20)->copyTo(self::$tree->getNode(1), 0);
		self::$tree->save();
		$this->assertEquals([], $this->analyze());
	}
	/**
	 * @depends testMoveLeft
	 */
	public function testCopyRight() {
		self::$tree->getNode(40)->copyTo(self::$tree->getNode(4), 100);
		self::$tree->save();
		$this->assertEquals([], $this->analyze());
	}
	/**
	 * @depends testCopyRight
	 */
	public function testRemove() {
		self::$tree->getNode(40)->remove();
		self::$tree->save();
		$this->assertEquals([], $this->analyze());
		self::$tree->getNode(30)->remove();
		self::$tree->save();
		$this->assertEquals([], $this->analyze());
		self::$tree->getNode(2)->remove();
		self::$tree->save();
		$this->assertEquals([], $this->analyze());
		self::$tree->getNode(3)->remove();
		self::$tree->save();
		$this->assertEquals([], $this->analyze());
		self::$tree->getNode(4)->remove();
		self::$tree->save();
		$this->assertEquals([], $this->analyze());
		self::$tree->getRoot()->getChildren()[0]->remove();
		self::$tree->save();
		$this->assertEquals([], $this->analyze());
	}

	public function analyze()
	{
		$report = [];
		if ((int)self::$db->one("SELECT COUNT(id) AS res FROM struct WHERE pid = 0") !== 1) {
			$report[] = "No or more than one root node.";
		}
		if ((int)self::$db->one("SELECT lft AS res FROM struct WHERE pid = 0") !== 1) {
			$report[] = "Root node's left index is not 1.";
		}
		if ((int)self::$db->one("
			SELECT
				COUNT(id) AS res
			FROM struct s
			WHERE
				pid != 0 AND
				(SELECT COUNT(id) FROM struct WHERE id = s.pid) = 0") > 0
		) {
			$report[] = "Missing parents.";
		}
		if (
			(int)self::$db->one("SELECT MAX(rgt) AS res FROM struct") / 2 !=
			(int)self::$db->one("SELECT COUNT(id) AS res FROM struct")
		) {
			$report[] = "Right index does not match node count.";
		}
		if (
			(int)self::$db->one("SELECT COUNT(DISTINCT rgt) AS res FROM struct") !=
			(int)self::$db->one("SELECT COUNT(DISTINCT lft) AS res FROM struct")
		) {
			$report[] = "Duplicates in nested set.";
		}
		if (
			(int)self::$db->one("SELECT COUNT(DISTINCT id) AS res FROM struct") !=
			(int)self::$db->one("SELECT COUNT(DISTINCT lft) AS res FROM struct")
		) {
			$report[] = "Left indexes not unique.";
		}
		if (
			(int)self::$db->one("SELECT COUNT(DISTINCT id) AS res FROM struct") !=
			(int)self::$db->one("SELECT COUNT(DISTINCT rgt) AS res FROM struct")
		) {
			$report[] = "Right indexes not unique.";
		}
		if (
			(int)self::$db->one("
				SELECT
					s1.id AS res
				FROM struct s1, struct s2
				WHERE
					s1.id != s2.id AND
					s1.lft = s2.rgt
				LIMIT 1")
		) {
			$report[] = "Nested set - matching left and right indexes.";
		}
		if (
			(int)self::$db->one("
				SELECT
					id AS res
				FROM struct s
				WHERE
					pos >= (
						SELECT
							COUNT(id)
						FROM struct
						WHERE pid = s.pid
					)
				LIMIT 1") ||
			(int)self::$db->one("
				SELECT
					s1.id AS res
				FROM struct s1, struct s2
				WHERE
					s1.id != s2.id AND
					s1.pid = s2.pid AND
					s1.pos = s2.pos
				LIMIT 1")
		) {
			$report[] = "Positions not correct.";
		}
		if ((int)self::$db->one("
			SELECT
				COUNT(id) FROM struct s
			WHERE
				(
					SELECT
						COUNT(id)
					FROM struct
					WHERE
						rgt < s.rgt AND
						lft > s.lft AND
						lvl = s.lvl + 1
				) !=
				(
					SELECT
						COUNT(*)
					FROM struct
					WHERE
						pid = s.id
				)")
		) {
			$report[] = "Adjacency and nested set do not match.";
		}
		return $report;
	}
}
