<?php

namespace vakata\phptree;

/**
 * This class represents a single node in the structure. Instances are usually created by the tree class.
 */
class Node
{
    protected $data = [];
    protected $tree = null;

    /**
     * Create an instance.
     * @param  vakata\phptree\Tree  $tree the parent tree instance
     * @param  array                $data the node data
     */
    public function __construct(Tree $tree, array $data)
    {
        $this->tree = $tree;
        $this->data = $data;
        foreach ($this->data as $f => $v) {
            $this->data[preg_replace('(^s_)', '', $f)] = $v;
        }
    }
    public function __get($k)
    {
        return isset($this->data[$k]) ? $this->data[$k] : null;
    }
    /**
     * Create a new child.
     * @param  integer|null  $index the index to create at, defaults to `null`, meaning create as last child.
     * @return vakata\phptree\Node  the newly created node
     */
    public function addChild($index = null)
    {
        $this->tree->create($this->id, $index);
        return $this->tree->node($this->id);
    }
    /**
     * Remove a child.
     * @param  integer      $index the index of the child to remove
     */
    public function removeChild($index)
    {
        $this->getChild($index)->remove();
    }
    /**
     * Move to a new parent.
     * @param  vakata\phptree\Node  $parent the new parent
     * @param  integer|null         $index  the new position to move to, defaults to `null`, meaning as the last child
     * @return self
     */
    public function moveTo(Node $parent, $index = null)
    {
        $this->tree->move($this->id, $parent->getID(), $index);
        return $this->tree->node($this->id);
    }
    /**
     * Move to a new location, as a sibling of node.
     * @param  vakata\phptree\Node  $reference the node to move next to
     * @return self
     */
    public function moveAfter(Node $reference)
    {
        $this->tree->move($this->id, $reference->getParent()->getID(), $reference->getIndex() + 1);
        return $this->tree->node($this->id);
    }
    /**
     * Move to a new location, as a sibling of node.
     * @param  vakata\phptree\Node  $reference the node to move next to
     * @return self
     */
    public function moveBefore(Node $reference)
    {
        $this->tree->move($this->id, $reference->getParent()->getID(), $reference->getIndex());
        return $this->tree->node($this->id);
    }
    /**
     * Copy the current node to a new location.
     * @param  vakata\phptree\Node  $parent the new parent
     * @param  integer|null         $index  the new position to copy to, defaults to `null`, meaning as the last child
     * @return vakata\phptree\Node  the newly create node
     */
    public function copyTo(Node $parent, $index = null)
    {
        $id = $this->tree->copy($this->$id, $parent->getID(), $index);
        return $this->tree->node($id);
    }
    /**
     * Copy the current node to a new location, as a sibling of given node.
     * @param  vakata\phptree\Node  $reference the reference node
     * @return vakata\phptree\Node  the newly create node
     */
    public function copyAfter(Node $reference)
    {
        $id = $this->tree->copy($this->id, $reference->getParent()->getID(), $reference->getIndex() + 1);
        return $this->tree->node($id);
    }
    /**
     * Copy the current node to a new location, as a sibling of given node.
     * @param  vakata\phptree\Node  $reference the reference node
     * @return vakata\phptree\Node  the newly create node
     */
    public function copyBefore(Node $reference)
    {
        $id = $this->tree->copy($this->id, $reference->getParent()->getID(), $reference->getIndex());
        return $this->tree->node($id);
    }
    /**
     * Remove the current node
     */
    public function remove()
    {
        $this->tree->remove($this->id);
    }
    /**
     * Remove all the children of the current node.
     * @return self
     */
    public function removeChildren()
    {
        foreach ($this->getChildren() as $node) {
            $node->remove();
        }
        return $this->tree->node($this->id);
    }
    /**
     * Is the node a leaf node.
     * @return boolean is the node a leaf
     */
    public function isLeaf()
    {
        return $this->hasChildren() === false;
    }
    /**
     * Does the node have children.
     * @return boolean     does the node have children
     */
    public function hasChildren()
    {
        return $this->right - $this->left > 1;
    }
    /**
     * Get all children.
     * @return array      an array of `\vakata\phptree\Node` objects
     */
    public function getChildren()
    {
        return $this->tree->children($this->id);
    }
    /**
     * Get a specific child by its index.
     * @param  integer   $index the child's index
     * @return \vakata\phptree\Node          the child
     */
    public function getChild($index)
    {
        $children = $this->getChildren();
        if (!isset($children[$index])) {
            throw new TreeException('Invalid child index');
        }
        return $children[$index];
    }
    /**
     * Get the parent of the node.
     * @return \vakata\phptree\Node    the parent node
     */
    public function getParent()
    {
        return $this->tree->node($this->parent);
    }
    /**
     * Get the node's position index.
     * @return integer   the position of the node among its siblings
     */
    public function getIndex()
    {
        return $this->position;
    }
    /**
     * Get the ID.
     * @return integer the node ID
     */
    public function getID()
    {
        return $this->id;
    }
    /**
     * Get the children count.
     * @return integer           the children count
     */
    public function getChildrenCount()
    {
        return count($this->getChildren());
    }
    /**
     * Get all descendants up to an optional depth
     * @param  integer|null  $depth optional max depth (counting from the current node) to include
     * @return array                an array of `\vakata\phptree\Node` objects
     */
    public function getDescendants($depth = null)
    {
        return $this->tree->descendants($this->left, $this->right, $depth ? $this->level + $depth : null);
    }
    /**
     * Get the count of all descendants
     * @return integer              the descendant count
     */
    public function getDescendantsCount()
    {
        return ($this->right - $this->left - 1) / 2;
    }
    /**
     * Get all parents
     * @return array     an array of `\vakata\phptree\Node` objects
     */
    public function getParents()
    {
        return $this->tree->parents($this->left, $this->right);
    }
    /**
     * Is the node descendant of another node
     * @param  Node           $node the node to check against
     * @return boolean              is the node a descendant
     */
    public function isDescendantOf(Node $node)
    {
        foreach ($this->getParents() as $parent) {
            if ($parent->getID() === $node->getID()) {
                return true;
            }
        }
        return false;
    }
    /**
     * Is the node child of another node
     * @param  Node           $node the node to check against
     * @return boolean              is the node a child
     */
    public function isChildOf(Node $node)
    {
        return $this->parent === $node->getID();
    }
    /**
     * Is the node the parent of another node
     * @param  Node           $node the node to check against
     * @return boolean              is the node the parent
     */
    public function isParentOf(Node $node)
    {
        return $node->isChildOf($this);
    }
    /**
     * Is the node an ancestor of another node
     * @param  Node           $node the node to check against
     * @return boolean              is the node an ancestor
     */
    public function isAncestorOf($node)
    {
        return $node->isDescendantOf($this);
    }
}
