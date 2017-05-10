<?php

namespace vakata\phptree;

use vakata\database\DBInterface;

/**
 * This class maintains a tree structure in a database using both the adjacency and nested set models.
 */
class Tree
{
    protected $db;
    protected $tb;
    protected $root;
    protected $fields;

    /**
     * Create an instance
     * @param  DBInterface $db     A database connection instance
     * @param  string            $tb     the table name where the tree will be stored
     * @param  integer           $root   the root of the tree (defaults to `1`)
     * @param  array             $fields a map containing the column names for: id, left, right, level, parent, position
     */
    public function __construct(DBInterface $db, $tb, array $fields = [])
    {
        $this->db = $db;
        $this->tb = $tb;
        $this->fields = $fields;
        $this->load();
    }
    public function load()
    {
        $dat = array_map(
            function ($v) {
                foreach ($this->fields as $kk => $vv) {
                    $v[$vv] = ($kk == 'parent' && $v[$vv] == null) ?
                        null :
                        (in_array($kk, ['id','left','right','level','parent','position']) ? (int)$v[$vv] : $v[$vv]); 
                }
                return $v;
            },
            $this->db->all("SELECT * FROM {$this->tb}")
        ); 
        if (isset($this->fields['id']) && isset($this->fields['parent'])) {
            $this->root = Node::fromAdjacencyArray(
                $dat,
                $this->fields['id'],
                $this->fields['parent'],
                $this->fields['position'] ?? null
            );
        } else {
            $this->root = Node::fromNestedSetArray(
                $dat,
                $this->fields['id'],
                $this->fields['left'],
                $this->fields['right']
            );
        }
    }
    /**
     * Get the root node
     * @return \vakata\phptree\Node  the root node object
     */
    public function getRoot()
    {
        return $this->root;
    }
    /**
     * Get a node by its ID - used internally
     * @param  int $id the node id
     * @return \vakata\phptree\Node     the node object
     */
    public function getNode(int $id)
    {
        $field = $this->fields['id'];
        if ($this->root->{$field} === $id) {
            return $this->root;
        }
        return array_values(array_filter(
            $this->root->getDescendants(),
            function ($v) use ($field, $id) { return $v->{$field} === $id; }
        ))[0] ?? null;
    }
    public function save()
    {
        $cur = [];
        $new = [];
        $mod = [];
        $rem = [];
        foreach ($this->root->export(1, $this->fields['id']) as $node) {
            $struct = [];
            foreach ($this->fields as $k => $v) {
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
        foreach ($this->db->get("SELECT * FROM {$this->tb}", null, $this->fields['id']) as $k => $v) {
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
        $trans = $this->db->begin();
        if (count($rem)) {
            $this->db->query("DELETE FROM {$this->tb} WHERE {$this->fields['id']} IN (??)", [$rem]);
        }
        foreach ($mod as $k => $v) {
            $sql = [];
            $par = [];
            foreach ($v as $kk => $vv) {
                if ($kk === $this->fields['id']) {
                    continue;
                }
                $sql[] = $kk . ' = ?';
                $par[] = $vv;
            }
            $sql = implode(', ', $sql);
            $par[] = $k;
            $this->db->query("UPDATE {$this->tb} SET {$sql} WHERE {$this->fields['id']} = ?", $par);
        }
        $add = [];
        foreach ($new as $k => $v) {
            $fields = [];
            foreach ($v['data'] as $kk => $vv) {
                if ($kk === $this->fields['id']) {
                    continue;
                }
                $fields[$kk] = $kk === $this->fields['parent'] ?
                    $v['node']->getParent()->{$this->fields['id']} :
                    $vv;
            }
            $id = $this->db->table($this->tb)->insert($fields)[$this->fields['id']];
            $v['node']->{$this->fields['id']} = (int)$id;
            $add[] = $id;
        }
        $this->db->commit($trans);
        //$this->load();
        return [ 'created' => $add, 'changed' => array_keys($mod), 'removed' => $rem ];
    }
}