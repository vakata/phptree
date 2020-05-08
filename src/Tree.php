<?php

namespace vakata\phptree;

use vakata\database\DBInterface;

/**
 * This class maintains a tree structure in a database using both the adjacency and nested set models.
 */
class Tree implements \JsonSerializable
{
    protected $root;
    protected $id;
    protected $map = [];

    /**
     * Create an instance
     * @param  DBInterface $db  A database connection instance
     * @param  string           $tb     the table name where the tree will be stored
     * @param  array            $fields a map containing the column names for: id, left, right, level, parent, position
     * @param  integer|null     $root   the root of the tree (defaults to `null` for autodetect)
     * @param  self
     */
    public static function fromDatabase(DBInterface $db, $tb, array $fields = [], int $root = null): self
    {
        $lft = null;
        $rgt = null;
        $lvl = null;
        $dat = null;
        if (isset($root)) {
            $temp = $db->one("SELECT * FROM {$tb} WHERE " . $fields['id'] . " = ?", (int)$root);
            if (!$temp) {
                throw new TreeException('Specified root node not found');
            }
            if (isset($fields['level'])) {
                $lvl = (int)$temp[$fields['level']];
            }
            if (isset($fields['left']) && isset($fields['right'])) {
                $lft = (int)$temp[$fields['left']];
                $rgt = (int)$temp[$fields['right']];
                $dat = $db->all(
                    "SELECT * FROM {$tb} WHERE " . $fields['left'] . " >= ? AND " . $fields['right'] . " <= ?",
                    [ $lft, $rgt ]
                );
            }
        }
        if (!isset($dat)) {
            $dat = $db->all("SELECT * FROM {$tb}");
        }
        foreach ($dat as $k => $v) {
            foreach ($fields as $kk => $vv) {
                if ($kk == 'parent' && $v[$vv] == null) {
                    $v[$vv] = null;
                } elseif ($kk == 'parent' && isset($root) && (int)$v[$fields['id']] === $root) {
                    $v[$vv] = null;
                } elseif ($kk == 'position' && isset($root) && (int)$v[$fields['id']] === $root) {
                    $v[$vv] = 0;
                } elseif ($kk == 'left' && isset($lft)) {
                    $v[$vv] = (int)$v[$vv] - ($lft - 1);
                } elseif ($kk == 'right' && isset($lft)) {
                    $v[$vv] = (int)$v[$vv] - ($lft - 1);
                } elseif ($kk == 'level' && isset($lvl)) {
                    $v[$vv] = (int)$v[$vv] - $lvl;
                } else {
                    $v[$vv] = in_array($kk, ['id','left','right','level','parent','position']) ?
                        (int)$v[$vv] :
                        $v[$vv];
                }
            }
            $dat[$k] = $v;
        }
        if (isset($fields['id']) && isset($fields['parent'])) {
            return self::fromAdjacencyArray(
                $dat,
                $fields['id'],
                $fields['parent'],
                $fields['position'] ?? null,
                $root
            );
        }
        return self::fromNestedSetArray(
            $dat,
            $fields['id'],
            $fields['left'],
            $fields['right']
        );
    }
    public static function fromAdjacencyArray(
        array $nodes = [],
        string $id = 'id',
        string $parent = 'parent',
        string $position = null,
        int $rootID = null
    ): self
    {
        $nodes = array_values($nodes);
        usort($nodes, function ($a, $b) use ($parent, $position) {
            return $a[$parent] < $b[$parent] ? -1 : (
                $a[$parent] > $b[$parent] ? 1 : ($position ? $a[$position] <=> $b[$position] : 0)
            );
        });
        $temp = [];
        $root = null;
        foreach ($nodes as $node) {
            $temp[$node[$id]] = new Node($node);
        }
        foreach ($nodes as $node) {
            if (isset($rootID) && $node[$id] === $rootID) {
                $root = $temp[$node[$id]];
            } elseif (!isset($rootID) && !isset($node[$parent])) {
                $root = $temp[$node[$id]];
            } else {
                if (isset($temp[$node[$parent]])) {
                    $temp[$node[$id]]->moveTo($temp[$node[$parent]]);
                }
            }
        }
        if (!isset($root)) {
            throw new TreeException('No root node found');
        }
        $tree = new self($id);
        $tree->setRoot($root);
        return $tree;
    }
    public static function fromNestedSetArray(
        array $nodes = [],
        string $id = 'id',
        string $left = 'left',
        string $right = 'right'
    ): self
    {
        $nodes = array_values($nodes);
        usort($nodes, function ($a, $b) use ($left) {
            return $a[$left] <=> $b[$left];
        });
        $tempL = [];
        $tempR = [];
        foreach ($nodes as $node) {
            $n = new Node($node);
            $tempL[$node[$left]] = $n;
            $tempR[$node[$right]] = $n;
        }
        if (!isset($tempL[1])) {
            throw new TreeException('No root node found');
        }
        $root = $tempL[1];
        foreach ($tempL as $left => $node) {
            if (isset($tempL[$left - 1])) {
                $node->moveTo($tempL[$left - 1]);
            }
            if (isset($tempR[$left - 1])) {
                $node->moveAfter($tempR[$left - 1]);
            }
        }
        $tree = new self($id);
        $tree->setRoot($root);
        return $tree;
    }
    public function __construct(string $id = 'id')
    {
        $this->id = $id;
        $this->root = new Node();
    }
    /**
     * Get the root node
     * @return \\vakata\phptree\Node  the root node object
     */
    public function getRoot(): Node
    {
        return $this->root;
    }
    public function setRoot(Node $root): self
    {
        $this->root = $root;
        $this->remap();
        return $this;
    }
    public function remap(): self
    {
        $this->map = [];
        $field = $this->id;
        $this->map[$this->root->{$field}] = $this->root;
        foreach ($this->root->getDescendants() as $node) {
            $this->map[$node->{$field}] = $node;
        }
        return $this;
    }
    public function getNode(int $id): ?Node
    {
        if (!isset($this->map[$id])) {
            $this->remap();
        }
        return $this->map[$id] ?? null;
    }
    public function toArray(bool $includeNodes = true): array
    {
        $temp = $this->root->export(1, $this->id);
        if (!$includeNodes) {
            foreach ($temp as $k => $v) {
                unset($temp[$k]['node']);
            }
        }
        return $temp;
    }
    public function toDatabase(DBInterface $db, $tb, array $fields = []): array
    {
        $cur = [];
        $new = [];
        $mod = [];
        $rem = [];
        foreach ($this->toArray() as $node) {
            $struct = [];
            foreach ($fields as $k => $v) {
                if (in_array($k, ['id','left','right','level','parent','position'])) {
                    $struct[$v] = $node['struct'][$k];
                    $node['node']->{$v} = $node['struct'][$k];
                }
            }
            if ($node['struct']['id'] && !$node['node']->isCopy()) {
                $cur[$node['struct']['id']] = array_merge(
                    $node['data'],
                    $struct
                );
            } else {
                $new[] = [
                    'data' => array_merge(
                        $node['data'],
                        $struct
                    ),
                    'node' => $node['node']
                ];
            }
        }
        foreach ($db->get("SELECT * FROM {$tb}", null, $fields['id']) as $k => $v) {
            $k = (int)$k;
            if (!isset($cur[$k])) {
                $rem[] = $k;
            } else {
                foreach ($v as $kk => $vv) {
                    if ($cur[$k][$kk] != $vv) {
                        $mod[$k] = $cur[$k];
                        break;
                    }
                }
                unset($cur[$k]);
            }
        }
        if (count($cur)) {
            throw new TreeException('Items removed from tree');
        }
        $trans = $db->begin();
        if (count($rem)) {
            $db->query("DELETE FROM {$tb} WHERE {$fields['id']} IN (??)", [$rem]);
        }
        foreach ($mod as $k => $v) {
            $sql = [];
            $par = [];
            foreach ($v as $kk => $vv) {
                if ($kk === $fields['id']) {
                    continue;
                }
                $sql[] = $kk . ' = ?';
                $par[] = $vv;
            }
            $sql = implode(', ', $sql);
            $par[] = $k;
            $db->query("UPDATE {$tb} SET {$sql} WHERE {$fields['id']} = ?", $par);
        }
        $add = [];
        foreach ($new as $k => $v) {
            $f = [];
            foreach ($v['data'] as $kk => $vv) {
                if ($kk === $fields['id']) {
                    continue;
                }
                $f[$kk] = $kk === $fields['parent'] ?
                    $v['node']->getParent()->{$fields['id']} :
                    $vv;
            }
            $id = $db->table($tb)->insert($f)[$fields['id']];
            $v['node']->{$fields['id']} = (int)$id;
            $add[] = $id;
        }
        if (isset($fields['parent'])) {
            foreach ($new as $k => $v) {
                $v['node']->{$fields['parent']} = $v['node']->getParent()->id;
            }
        }
        $db->commit($trans);
        $this->remap();
        return [ 'created' => $add, 'changed' => array_keys($mod), 'removed' => $rem ];
    }

    public function __sleep()
    {
        return [ 'id', 'root' ];
    }
    public function __wakeup()
    {
        $this->remap();
    }
    public function jsonSerialize()
    {
        return $this->toArray(false);
    }
}
