<?php

namespace vakata\phptree;

class Node
{
    protected $data = [];
    protected $tree = null;

    public function __construct(Tree $tree, array $data)
    {
        $this->data = $data;
        $this->tree = $tree;
    }
    public function __get($k)
    {
        return isset($data[$k]) ? $data[$k] : null;
    }

    public function addChild($index = 0)
    {
        $this->tree->create($this->id, $index);
        return $this->tree->node($this->id);
    }
    public function removeChild($index)
    {
        $this->getChild($index)->remove();
        return $this->tree->node($this->id);
    }
    public function moveTo(Node $parent, $index = 0)
    {
        $this->tree->move($this->$id, $parent->getID(), $index);
        return $this->tree->node($this->id);
    }
    public function moveAfter(Node $reference)
    {
        $this->tree->move($this->id, $reference->getParent(), $reference->getIndex() + 1);
        return $this->tree->node($this->id);
    }
    public function moveBefore(Node $reference)
    {
        $this->tree->move($this->id, $reference->getParent(), $reference->getIndex());
        return $this->tree->node($this->id);
    }
    public function copyTo(Node $parent, $index = 0)
    {
        $id = $this->tree->copy($this->$id, $parent->getID(), $index);
        return $this->tree->node($id);
    }
    public function copyAfter(Node $reference)
    {
        $id = $this->tree->copy($this->id, $reference->getParent(), $reference->getIndex() + 1);
        return $this->tree->node($id);
    }
    public function copyBefore(Node $reference)
    {
        $id = $this->tree->copy($this->id, $reference->getParent(), $reference->getIndex());
        return $this->tree->node($id);
    }
    public function remove()
    {
        $this->tree->remove($this->id);
    }
    public function empty()
    {
        foreach ($this->getChildren() as $node) {
            $node->remove();
        }
        return $this->tree->node($this->id);
    }
    public function isLeaf()
    {
        return $this->hasChildren() === false;
    }
    public function hasChildren()
    {
        return $this->right - $this->left > 1;
    }
    public function getChildren()
    {
        return $this->tree->children($this->id);
    }
    public function getChild($index)
    {
        $children = $this->getChildren();
        if (!isset($children[$index])) {
            throw new Exception('Invalid child index');
        }
        return $children[$index];
    }
    public function getParent()
    {
        return $this->tree->node($this->parent);
    }
    public function getIndex()
    {
        return $this->position;
    }
    public function getID()
    {
        return $this->id;
    }
    public function getChildrenCount()
    {
        return count($this->getChildren());
    }
    public function getDescendants($depth = null)
    {
        return $this->tree->descendants($this->left, $this->right, $depth ? $this->level + $depth : null);
    }
    public function getDescendantsCount()
    {
        return ($this->right - $this->left - 1) / 2;
    }
    public function getParents()
    {
        return $this->tree->parents($this->left, $this->right);
    }
    public function isDescendantOf(Node $node)
    {
        foreach ($this->getParents() as $parent) {
            if ($parent->getID() === $node->getID()) {
                return true;
            }
        }
        return false;
    }
    public function isChildOf(Node $node)
    {
        return $this->parent === $node->getID();
    }
    public function isParentOf(Node $node)
    {
        return $node->isChildOf($this);
    }
    public function isAncestorOf($node)
    {
        return $node->isDescendantOf($this);
    }
}
