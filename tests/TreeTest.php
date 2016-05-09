<?php
namespace vakata\phptree\test;

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
		self::$tree = new \vakata\phptree\Tree(self::$db, 'struct');
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
		self::$tree->create();
		$this->assertEquals(2, self::$tree->node(2)->id);
		$this->assertEquals(1, self::$tree->node(2)->parent);
		$this->assertEquals(0, self::$tree->node(2)->position);
		$this->assertEquals(2, self::$tree->node(2)->left);
		$this->assertEquals(3, self::$tree->node(2)->right);
		$this->assertEquals(1, self::$tree->node(2)->level);
		$this->assertEquals([], $this->analyze());
	}
	/**
	 * @depends testCreate
	 */
	public function testCreateLeft() {
		self::$tree->create(1, 0);
		$this->assertEquals(2, count(self::$tree->getRoot()->getChildren()));
		$this->assertEquals([], $this->analyze());
	}
	/**
	 * @depends testCreateLeft
	 */
	public function testCreateRight() {
		self::$tree->create(1);
		$this->assertEquals(3, count(self::$tree->getRoot()->getChildren()));
		$this->assertEquals([], $this->analyze());
	}
	/**
	 * @depends testCreateRight
	 */
	public function testCreateInner() {
		self::$tree->create(2);
		self::$tree->create(3);
		$this->assertEquals([], $this->analyze());
	}
	/**
	 * @depends testCreateRight
	 */
	public function testDeepCreate() {
		$id = 2;
		for ($i = 0; $i < 30; $i++) {
			$id = self::$tree->create($id);
		}
		$this->assertEquals(31, self::$tree->node($id)->level);
		$this->assertEquals([], $this->analyze());
	}
	/**
	 * @depends testDeepCreate
	 */
	public function testWideCreate() {
		$this->assertEquals(true, self::$tree->node(4)->isLeaf());
		for ($i = 0; $i < 30; $i++) {
			$id = self::$tree->create(4);
		}
		$this->assertEquals(2, self::$tree->node($id)->level);
		$this->assertEquals(30, self::$tree->node(4)->getChildrenCount());
		$this->assertEquals(false, self::$tree->node(4)->isLeaf());
		$this->assertEquals([], $this->analyze());
	}
	/**
	 * @depends testWideCreate
	 */
	public function testReorderRight() {
		self::$tree->move(2, 1, 10);
		$this->assertEquals([], $this->analyze());
	}
	/**
	 * @depends testReorderRight
	 */
	public function testReorderLeft() {
		self::$tree->move(2, 1, 0);
		$this->assertEquals([], $this->analyze());
	}
	/**
	 * @depends testDeepCreate
	 */
	public function testMoveLeft() {
		self::$tree->move(40, 1, 0);
		$this->assertEquals([], $this->analyze());
	}
	/**
	 * @depends testMoveLeft
	 */
	public function testMoveRight() {
		self::$tree->move(40, 4, 0);
		$this->assertEquals([], $this->analyze());
	}
	/**
	 * @depends testDeepCreate
	 */
	public function testCopyLeft() {
		$id = self::$tree->copy(40, 1, 0);
		$this->assertEquals([], $this->analyze());
		self::$tree->remove($id);
		$this->assertEquals([], $this->analyze());
	}
	/**
	 * @depends testMoveLeft
	 */
	public function testCopyRight() {
		self::$tree->copy(40, 4, 100);
		$this->assertEquals([], $this->analyze());
	}
	/**
	 * @depends testCopyRight
	 */
	public function testRemove() {
		self::$tree->remove(40);
		$this->assertEquals([], $this->analyze());
		self::$tree->remove(30);
		$this->assertEquals([], $this->analyze());
		self::$tree->remove(2);
		$this->assertEquals([], $this->analyze());
		self::$tree->remove(3);
		$this->assertEquals([], $this->analyze());
		self::$tree->remove(4);
		$this->assertEquals([], $this->analyze());
		$this->assertEquals([], self::$tree->getRoot()->getChildren());
	}

	public function analyze()
	{
		$report = [];
		//if ((int)self::$db->one("SELECT COUNT(id) AS res FROM struct WHERE pid = 0") !== 1) {
		//	$report[] = "No or more than one root node.";
		//}
		//if ((int)self::$db->one("SELECT lft AS res FROM struct WHERE pid = 0") !== 1) {
		//	$report[] = "Root node's left index is not 1.";
		//}
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
