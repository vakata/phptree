<?php

namespace vakata\phptree;

use vakata\database\DatabaseInterface;

class Tree
{
    protected $db = null;
    protected $tb = '';
    protected $root = 1;
    protected $fields = [
        'id'            => 'id',
        'left'          => 'lft',
        'right'         => 'rgt',
        'level'         => 'lvl',
        'parent'        => 'pid',
        'position'      => 'pos'
    ];
    protected $select = '';

    public function __construct(DatabaseInterface $db, $tb, $root = 1, array $fields = [])
    {
        $this->db = $db;
        $this->tb = $tb;
        $this->fields = array_merge($this->fields, $fields);
        $this->select = [];
        foreach ($this->fields as $k => $v) {
            $this->select[] = $v . ' AS s_' . $k;
        }
        $this->select = implode(',', $this->select);
        $this->root = $this->node($root);
    }

    public function getRoot()
    {
        return $this->root;
    }

    public function node($id)
    {
        $temp = $this->db->one(
            "SELECT {$this->select} FROM {$this->tb} WHERE {$this->fields['id']} = ?",
            $id
        );
        if ($temp === null) {
            throw new TreeException('Invalid node ID');
        }
        return new Node($this, $temp);
    }
    public function parents($lft, $rgt)
    {
        $temp = [];
        foreach ($this->db->all(
            "SELECT {$this->select} FROM {$this->tb}
             WHERE {$this->fields['left']} < ? AND {$this->fields['right']} > ?
             ORDER BY {$this->fields['level']} DESC",
            [ $lft, $rgt ]
        ) as $data) {
            $temp[] = new Node($this, $data);
        }
        return $temp;
    }
    public function children($id)
    {
        $temp = [];
        foreach ($this->db->all(
            "SELECT {$this->select} FROM {$this->tb}
             WHERE {$this->fields['parent']} = ?
             ORDER BY {$this->fields['position']}",
            $id
        ) as $data) {
            $temp[] = new Node($this, $data);
        }
        return $temp;
    }
    public function descendants($lft, $rgt, $lvl = null)
    {
        $temp = [];
        foreach ($this->db->all(
            "SELECT {$this->select} FROM {$this->tb}
             WHERE {$this->fields['left']} > ? AND {$this->fields['right']} < ?
            " . ($lvl ? " AND level <= ?" : "") . "
             ORDER BY {$this->fields['left']} ASC",
            $lvl ? [ $lft, $rgt, $lvl ] : [ $lft, $rgt ]
        ) as $data) {
            $temp[] = new Node($this, $data);
        }
        return $temp;
    }

    public function create($parent = null, $position = null)
    {
        $parent = $parent === null ? $this->root : $this->node((int)$parent);
        $position = $position === null ? $parent->getChildrenCount() : min((int)$position, $parent->getChildrenCount());

        $sql = array();
        $par = array();
        
        // update positions of all next elements
        $sql[] = "UPDATE {$this->tb} 
                  SET 
                    {$this->fields['position']} = {$this->fields['position']} + 1 
                  WHERE 
                    {$this->fields['parent']} = ? AND 
                    {$this->fields['position']} >= ?";
        $par[] = [ $parent->id, $position ];

        // update left indexes
        $left = ($position >= $parent->getChildrenCount() ? $parent->right : $parent->getChild($position)->left);
        $sql[] = "UPDATE {$this->tb} 
                  SET 
                    {$this->fields['left']} = {$this->fields['left']} + 2
                  WHERE 
                    {$this->fields['left']} >= ?";
        $par[] = [ $left ];

        // update right indexes
        $right = ($position >= $parent->getChildrenCount() ? $parent->right : $parent->getChild($position)->left + 1);
        $sql[] = "UPDATE {$this->tb} 
                  SET 
                    {$this->fields['right']} = {$this->fields['right']} + 2
                  WHERE 
                    {$this->fields['right']} >= ?";
        $par[] = [ $right ];

        // insert new node in structure
        $sql[] = "INSERT INTO {$this->tb} (".implode(",", $this->fields).")
                  VALUES (".implode(',', array_fill(0, count($this->fields), '?')).")";
        $tmp = array();
        foreach ($this->fields as $k => $v) {
            switch ($k) {
                case 'id':
                    $tmp[] = null;
                    break;
                case 'left':
                    $tmp[] = (int)$left;
                    break;
                case 'right':
                    $tmp[] = (int)$left + 1;
                    break;
                case 'level':
                    $tmp[] = (int)$parent->level + 1;
                    break;
                case 'parent':
                    $tmp[] = $parent->id;
                    break;
                case 'position':
                    $tmp[] = $position;
                    break;
                default:
                    $tmp[] = null;
            }
        }
        $par[] = $tmp;

        $trans = $this->db->begin();
        $last = null;
        try {
            foreach ($sql as $k => $v) {
                $last = $this->db->query($v, $par[$k])->insertId();
            }
            $this->db->commit($trans);
            return (int)$last;
        } catch (\Exception $e) {
            $this->db->rollback($trans);
            throw $e;
        }
    }
    public function move($id, $parent, $position = null)
    {
        $id = $this->node((int)$id);
        $parent = $this->node((int)$parent);
        $position = $position === null ? $parent->getChildrenCount() : min((int)$position, $parent->getChildrenCount());
        if ($id->parent == $parent->id && (int)$position > $id->position) {
            $position ++;
        }
        $position = min((int)$position, $parent->getChildrenCount());

        if ($id->left < $parent->left && $id->right > $parent->right) {
            throw new TreeException('Could not move parent inside child');
        }

        $tmp = [];
        $tmp[] = $id->id;
        foreach ($id->getDescendants() as $node) {
            $tmp[] = (int)$node->id;
        }
        $width = (int)$id->right - (int)$id->left + 1;

        $sql = [];
        $par = [];

        // prepare new parent - update positions of all next elements
        $sql[] = "UPDATE {$this->tb} 
                  SET 
                    {$this->fields['position']} = {$this->fields['position']} + 1 
                  WHERE 
                    {$this->fields['id']} != ? AND 
                    {$this->fields['parent']} = ? AND 
                    {$this->fields['position']} >= ?";
        $par[] = [ $id->id, $parent->id, $position ];

        // prepare new parent - update left indexes
        $left = $position >= $parent->getChildrenCount() ? $parent->right : $parent->getChildren()[$position]->left;
        $sql[] = "UPDATE {$this->tb} 
                  SET 
                    {$this->fields['left']} = {$this->fields['left']} + ? 
                  WHERE 
                    {$this->fields['left']} >= ? AND 
                    {$this->fields['id']} NOT IN (".implode(',', $tmp).")";
        $par[] = [ $width, $left ];

        // prepare new parent - update right indexes
        $right = $position >= $parent->getChildrenCount() ? $parent->right : $parent->getChildren()[$position]->left + 1;
        $sql[] = "UPDATE {$this->tb} 
                  SET 
                    {$this->fields['right']} = {$this->fields['right']} + ? 
                  WHERE
                    {$this->fields['right']} >= ? AND 
                    {$this->fields['id']} NOT IN (".implode(',', $tmp).")";
        $par[] = [ $width, $right ];

        // move the node and children - left, right and level
        $diff = $left - (int)$id->left;
        if ($diff > 0) {
            $diff = $diff - $width;
        }
        $ldiff = ((int)$parent->level + 1) - (int)$id->level;
        $sql[] = "UPDATE {$this->tb} 
                  SET 
                    {$this->fields['right']} = {$this->fields['right']} + ?, 
                    {$this->fields['left']} = {$this->fields['left']} + ?, 
                    {$this->fields['level']} = {$this->fields['level']} + ? 
                  WHERE {$this->fields['id']} IN (".implode(',', $tmp).") ";
        $par[] = [ $diff, $diff, $ldiff ];

        // move the node and children - position and parent
        $sql[] = "UPDATE {$this->tb} 
                  SET
                    {$this->fields['position']} = ?, 
                    {$this->fields['parent']} = ? 
                  WHERE 
                    {$this->fields['id']} = ?";
        $par[] = [ $position, $parent->id, $id->id ];

        // clean old parent - position of all next elements
        $sql[] = "UPDATE {$this->tb} 
                  SET 
                    {$this->fields['position']} = {$this->fields['position']} - 1 
                  WHERE 
                    {$this->fields['parent']} = ? AND 
                    {$this->fields['position']} > ?";
        $par[] = [ $id->parent, $id->position ];

        // clean old parent - left indexes
        $sql[] = "UPDATE {$this->tb} 
                  SET 
                    {$this->fields['left']} = {$this->fields['left']} - ? 
                  WHERE 
                    {$this->fields['left']} > ? AND 
                    {$this->fields['id']} NOT IN (".implode(',', $tmp).")";
        $par[] = [ $width, $id->right ];

        // clean old parent - right indexes
        $sql[] = "UPDATE {$this->tb} 
                  SET 
                    {$this->fields['right']} = {$this->fields['right']} - ? 
                  WHERE 
                    {$this->fields['right']} > ? AND 
                    {$this->fields['id']} NOT IN (".implode(',', $tmp).")";
        $par[] = [ $width, $id->right ];

        $trans = $this->db->begin();
        try {
            foreach ($sql as $k => $v) {
                $this->db->query($v, $par[$k]);
            }
            $this->db->commit($trans);
        } catch (\Exception $e) {
            $this->db->rollback($trans);
            throw $e;
        }
    }
    public function copy($id, $parent, $position = null)
    {
        $id = $this->node((int)$id);
        $parent = $this->node((int)$parent);
        $position = $position === null ? $parent->getChildrenCount() : min((int)$position, $parent->getChildrenCount());

        $tmp = [];
        $tmp[] = $id->id;
        foreach ($id->getDescendants() as $node) {
            $tmp[] = (int)$node->id;
        }
        $width = (int)$id->right - (int)$id->left + 1;

        $sql = [];
        $par = [];

        // prepare new parent - update positions of all next elements
        $sql[] = "UPDATE {$this->tb} 
                  SET 
                    {$this->fields['position']} = {$this->fields['position']} + 1 
                  WHERE 
                    {$this->fields['parent']} = ? AND 
                    {$this->fields['position']} >= ?";
        $par[] = [ $parent->id, $position ];

        // prepare new parent - update left indexes
        $left = $position >= $parent->getChildrenCount() ? $parent->right : $parent->getChild($position)->left;
        $sql[] = "UPDATE {$this->tb} 
                  SET 
                    {$this->fields['left']} = {$this->fields['left']} + ? 
                  WHERE 
                    {$this->fields['left']} >= ?";
        $par[] = [ $width, $left ];

        // prepare new parent - update right indexes
        $right = $position >= $parent->getChildrenCount() ? $parent->right : $parent->getChild($position)->left + 1;
        $sql[] = "UPDATE {$this->tb} 
                  SET 
                    {$this->fields['right']} = {$this->fields['right']} + ? 
                  WHERE 
                    {$this->fields['right']} >= ?";
        $par[] = [ $width, $right ];

        // move the element and children - left, right and level
        $diff = $left - (int)$id->left;
        if ($diff <= 0) {
            $diff = $diff - $width;
        }
        $ldiff = ((int)$parent->level + 1) - (int)$id->level;

        // move the element and children - build all fields
        $fields = array_combine($this->fields, $this->fields);
        unset($fields['id']);
        $fields[$this->fields["left"]] = $this->fields["left"]." + ? ";
        $fields[$this->fields["right"]] = $this->fields["right"]." + ? ";
        $fields[$this->fields["level"]] = $this->fields["level"]." + ? ";
        $sql[] = "INSERT INTO {$this->tb} (".implode(',', array_keys($fields)).") 
                  SELECT ".implode(',', array_values($fields))." FROM {$this->tb} 
                  WHERE {$this->fields['id']} IN (".implode(",", $tmp).")
                    ORDER BY {$this->fields['level']} ASC";
        $par[] = [ $diff, $diff, $ldiff ];

        $trans = $this->db->begin();
        try {
            foreach ($sql as $k => $v) {
                $iid = $this->db->query($v, $par[$k])->insertId();
            }
            $this->db->query(
                "UPDATE {$this->tb} 
                 SET 
                    {$this->fields['position']} = ?, 
                    {$this->fields['parent']} = ? 
                 WHERE {$this->fields['id']} = ?",
                [ $position, $parent->id, $iid ]
            );

            // manually fix all parents
            $new = $this->db->all(
                "SELECT * FROM {$this->tb} 
                 WHERE 
                    {$this->fields['left']} > ? AND 
                    {$this->fields['right']} < ? AND 
                    {$this->fields['id']} != ? 
                 ORDER BY {$this->fields['left']}",
                [ $left, ($left + $width - 1), $iid ]
            );
            $parents = array();
            foreach ($new as $node) {
                if (!isset($parents[$node[$this->fields["left"]]])) {
                    $parents[$node[$this->fields["left"]]] = $iid;
                }
                for ($i = $node[$this->fields["left"]] + 1; $i < $node[$this->fields["right"]]; $i++) {
                    $parents[$i] = $node[$this->fields["id"]];
                }
            }
            foreach ($new as $k => $node) {
                $this->db->query(
                    "UPDATE {$this->tb} SET {$this->fields['parent']} = ? WHERE {$this->fields['id']} = ?",
                    [$parents[$node[$this->fields["left"]]], (int)$node[$this->fields["id"]]]
                );
            }
            $this->db->commit($trans);
            return $iid;
        } catch (\Exception $e) {
            $this->db->rollback($trans);
            throw $e;
        }
    }
    public function remove($id)
    {
        $id = $this->node($id);

        $sql = [];
        $par = [];
        // deleting node and its children from structure
        $sql[] = "DELETE FROM {$this->tb} 
                  WHERE 
                    {$this->fields['left']} >= ? AND 
                    {$this->fields['right']} <= ?";
        $par[] = [ $id->left, $id->right];

        // shift left indexes of nodes right of the node
        $sql[] = "UPDATE {$this->tb} 
                  SET 
                    {$this->fields['left']} = {$this->fields['left']} - ? 
                  WHERE 
                    {$this->fields['left']} > ?";
        $par[] = [ $id->right - $id->left + 1, $id->right ];

        // shift right indexes of nodes right of the node and the node's parents
        $sql[] = "UPDATE {$this->tb} 
                  SET 
                    {$this->fields['right']} = {$this->fields['right']} - ? 
                  WHERE 
                    {$this->fields['right']} > ?";
        $par[] = [ $id->right - $id->left + 1, $id->left ];

        // Update position of siblings below the deleted node
        $sql[] = "UPDATE {$this->tb} 
                  SET 
                    {$this->fields['position']} = {$this->fields['position']} - 1 
                  WHERE 
                    {$this->fields['parent']} = ? AND 
                    {$this->fields['position']} > ?";
        $par[] = [ $id->parent, $id->position ];

        $trans = $this->db->begin();
        try {
            foreach ($sql as $k => $v) {
                $this->db->query($v, $par[$k]);
            }
            $this->db->commit($trans);
        } catch (\Exception $e) {
            $this->db->rollback($trans);
            throw $e;
        }
    }
    /*
    public function analyze()
    {
        $report = [];
        if ((int)$this->db->one("SELECT COUNT(".$this->fields["id"].") AS res FROM ".$this->tb." WHERE ".$this->fields["parent"]." = 0") !== 1) {
            $report[] = "No or more than one root node.";
        }
        if ((int)$this->db->one("SELECT ".$this->fields["left"]." AS res FROM ".$this->tb." WHERE ".$this->fields["parent"]." = 0") !== 1) {
            $report[] = "Root node's left index is not 1.";
        }
        if ((int)$this->db->one("
            SELECT
                COUNT(".$this->fields['id'].") AS res
            FROM ".$this->tb." s
            WHERE
                ".$this->fields["parent"]." != 0 AND
                (SELECT COUNT(".$this->fields['id'].") FROM ".$this->tb." WHERE ".$this->fields["id"]." = s.".$this->fields["parent"].") = 0") > 0
        ) {
            $report[] = "Missing parents.";
        }
        if (
            (int)$this->db->one("SELECT MAX(".$this->fields["right"].") AS res FROM ".$this->tb) / 2 !=
            (int)$this->db->one("SELECT COUNT(".$this->fields["id"].") AS res FROM ".$this->tb)
        ) {
            $report[] = "Right index does not match node count.";
        }
        if (
            (int)$this->db->one("SELECT COUNT(DISTINCT ".$this->fields["right"].") AS res FROM ".$this->tb) !=
            (int)$this->db->one("SELECT COUNT(DISTINCT ".$this->fields["left"].") AS res FROM ".$this->tb)
        ) {
            $report[] = "Duplicates in nested set.";
        }
        if (
            (int)$this->db->one("SELECT COUNT(DISTINCT ".$this->fields["id"].") AS res FROM ".$this->tb) !=
            (int)$this->db->one("SELECT COUNT(DISTINCT ".$this->fields["left"].") AS res FROM ".$this->tb)
        ) {
            $report[] = "Left indexes not unique.";
        }
        if (
            (int)$this->db->one("SELECT COUNT(DISTINCT ".$this->fields["id"].") AS res FROM ".$this->tb) !=
            (int)$this->db->one("SELECT COUNT(DISTINCT ".$this->fields["right"].") AS res FROM ".$this->tb)
        ) {
            $report[] = "Right indexes not unique.";
        }
        if (
            (int)$this->db->one("
                SELECT
                    s1.".$this->fields["id"]." AS res
                FROM ".$this->tb." s1, ".$this->tb." s2
                WHERE
                    s1.".$this->fields['id']." != s2.".$this->fields['id']." AND
                    s1.".$this->fields['left']." = s2.".$this->fields['right']."
                LIMIT 1")
        ) {
            $report[] = "Nested set - matching left and right indexes.";
        }
        if (
            (int)$this->db->one("
                SELECT
                    ".$this->fields["id"]." AS res
                FROM ".$this->tb." s
                WHERE
                    ".$this->fields['position']." >= (
                        SELECT
                            COUNT(".$this->fields["id"].")
                        FROM ".$this->tb."
                        WHERE ".$this->fields['parent']." = s.".$this->fields['parent']."
                    )
                LIMIT 1") ||
            (int)$this->db->one("
                SELECT
                    s1.".$this->fields["id"]." AS res
                FROM ".$this->tb." s1, ".$this->tb." s2
                WHERE
                    s1.".$this->fields['id']." != s2.".$this->fields['id']." AND
                    s1.".$this->fields['parent']." = s2.".$this->fields['parent']." AND
                    s1.".$this->fields['position']." = s2.".$this->fields['position']."
                LIMIT 1")
        ) {
            $report[] = "Positions not correct.";
        }
        if ((int)$this->db->one("
            SELECT
                COUNT(".$this->fields["id"].") FROM ".$this->tb." s
            WHERE
                (
                    SELECT
                        COUNT(".$this->fields["id"].")
                    FROM ".$this->tb."
                    WHERE
                        ".$this->fields["right"]." < s.".$this->fields["right"]." AND
                        ".$this->fields["left"]." > s.".$this->fields["left"]." AND
                        ".$this->fields["level"]." = s.".$this->fields["level"]." + 1
                ) !=
                (
                    SELECT
                        COUNT(*)
                    FROM ".$this->tb."
                    WHERE
                        ".$this->fields["parent"]." = s.".$this->fields["id"]."
                )")
        ) {
            $report[] = "Adjacency and nested set do not match.";
        }
        return $report;
    }
    public function reconstruct() {
        $this->db->query(
            "CREATE TEMPORARY TABLE temp_tree (
                {$this->fields['id']} INTEGER NOT NULL,
                {$this->fields['parent']} INTEGER NOT NULL,
                {$this->fields['position']} INTEGER NOT NULL
             )"
        );
        $this->db->query(
            "INSERT INTO temp_tree 
             SELECT {$this->fields['id']}, {$this->fields['parent']}, {$this->fields['position']} 
             FROM {$this->tb}"
        );

        $this->db->query(
            "CREATE TEMPORARY TABLE temp_stack (
                {$this->fields["id"]} INTEGER NOT NULL,
                {$this->fields["left"]} INTEGER,
                {$this->fields["right"]} INTEGER,
                {$this->fields["level"]} INTEGER,
                stack_top INTEGER NOT NULL,
                {$this->fields["parent"]} INTEGER,
                {$this->fields["position"]} INTEGER
             )"
        );

        $counter = 2;
        $maxcounter = (int)$this->db->one("SELECT COUNT(*) FROM temp_tree") * 2;
        $currenttop = 1;
        $this->db->query(
            "INSERT INTO temp_stack 
             SELECT 
                {$this->fields["id"]}, 
                1, 
                NULL, 
                0, 
                1, 
                {$this->fields["parent"]}, 
                {$this->fields["position"]} 
             FROM temp_tree 
             WHERE {$this->fields["parent"]} = 0"
        );
        $this->db->query("DELETE FROM temp_tree WHERE {$this->fields["parent"]} = 0");

        while ($counter <= $maxcounter) {
            if (!$this->db->query("" .
                "SELECT " .
                    "temp_tree.".$this->fields["id"]." AS tempmin, " .
                    "temp_tree.".$this->fields["parent"]." AS pid, " .
                    "temp_tree.".$this->fields["position"]." AS lid " .
                "FROM temp_stack, temp_tree " .
                "WHERE " .
                    "temp_stack.".$this->fields["id"]." = temp_tree.".$this->fields["parent"]." AND " .
                    "temp_stack.stack_top = ".$currenttop." " .
                "ORDER BY temp_tree.".$this->fields["position"]." ASC LIMIT 1"
            )) { return false; }

            if ($this->db->nextr()) {
                $tmp = $this->db->f("tempmin");

                $q = "INSERT INTO temp_stack (stack_top, ".$this->fields["id"].", ".$this->fields["left"].", ".$this->fields["right"].", ".$this->fields["level"].", ".$this->fields["parent"].", ".$this->fields["position"].") VALUES(".($currenttop + 1).", ".$tmp.", ".$counter.", NULL, ".$currenttop.", ".$this->db->f("pid").", ".$this->db->f("lid").")";
                if (!$this->db->query($q)) {
                    return false;
                }
                if (!$this->db->query("DELETE FROM temp_tree WHERE ".$this->fields["id"]." = ".$tmp)) {
                    return false;
                }
                $counter++;
                $currenttop++;
            }
            else {
                if (!$this->db->query("" .
                    "UPDATE temp_stack SET " .
                        "".$this->fields["right"]." = ".$counter.", " .
                        "stack_top = -stack_top " .
                    "WHERE stack_top = ".$currenttop
                )) { return false; }
                $counter++;
                $currenttop--;
            }
        }

        $temp_fields = $this->fields;
        unset($temp_fields["parent"]);
        unset($temp_fields["position"]);
        unset($temp_fields["left"]);
        unset($temp_fields["right"]);
        unset($temp_fields["level"]);
        if (count($temp_fields) > 1) {
            if (!$this->db->query("" .
                "CREATE TEMPORARY TABLE temp_tree2 " .
                    "SELECT ".implode(", ", $temp_fields)." FROM ".$this->tb." "
            )) { return false; }
        }
        if (!$this->db->query("TRUNCATE TABLE ".$this->tb."")) {
            return false;
        }
        if (!$this->db->query("" .
            "INSERT INTO ".$this->tb." (" .
                    "".$this->fields["id"].", " .
                    "".$this->fields["parent"].", " .
                    "".$this->fields["position"].", " .
                    "".$this->fields["left"].", " .
                    "".$this->fields["right"].", " .
                    "".$this->fields["level"]." " .
                ") " .
                "SELECT " .
                    "".$this->fields["id"].", " .
                    "".$this->fields["parent"].", " .
                    "".$this->fields["position"].", " .
                    "".$this->fields["left"].", " .
                    "".$this->fields["right"].", " .
                    "".$this->fields["level"]." " .
                "FROM temp_stack " .
                "ORDER BY ".$this->fields["id"].""
        )) {
            return false;
        }
        if (count($temp_fields) > 1) {
            $sql = "" .
                "UPDATE ".$this->tb." v, temp_tree2 SET v.".$this->fields["id"]." = v.".$this->fields["id"]." ";
            foreach ($temp_fields as $k => $v) {
                if ($k == "id") {
                    continue;
                }
                $sql .= ", v.".$v." = temp_tree2.".$v." ";
            }
            $sql .= " WHERE v.".$this->fields["id"]." = temp_tree2.".$this->fields["id"]." ";
            if (!$this->db->query($sql)) {
                return false;
            }
        }
        // fix positions
        $nodes = $this->db->get("SELECT ".$this->fields['id'].", ".$this->fields['parent']." FROM ".$this->tb." ORDER BY ".$this->fields['parent'].", ".$this->fields['position']);
        $last_parent = false;
        $last_position = false;
        foreach ($nodes as $node) {
            if ((int)$node[$this->fields['parent']] !== $last_parent) {
                $last_position = 0;
                $last_parent = (int)$node[$this->fields['parent']];
            }
            $this->db->query("UPDATE ".$this->tb." SET ".$this->fields['position']." = ".$last_position." WHERE ".$this->fields['id']." = ".(int)$node[$this->fields['id']]);
            $last_position++;
        }
        return true;
    }
    */
}