<?php

namespace vakata\phptree;

/**
 * This class represents a single node in the structure. Instances are usually created by the tree class.
 */
class Node
{
    public static function copy(Node $original): Node
    {
        $node = clone $original;
        $node->copied = $original;
        $node->parent = null;
        $node->children = array_map(function ($v) use ($node) {
            $v = Node::copy($v);
            $v->parent = $node;
            return $v;
        }, $node->children);
        return $node;
    }

    protected $data = [];
    protected $parent = null;
    protected $copied = null;
    protected $children = [];

    /**
     * Create an instance.
     * @param  array                $data   the node data (optional)
     * @param  \vakata\phptree\Node  $parent the parent of the node
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }
    public function __get($k)
    {
        return $this->data[$k] ?? null;
    }
    public function __set($k, $v)
    {
        $this->data[$k] = $v;
    }
    /**
     * Get the index of the node (among its siblings)
     * @return integer the index
     */
    public function getIndex(): int
    {
        if ($this->parent === null) {
            return 0;
        }
        return array_search($this, $this->parent->children, true);
    }
    /**
     * Create a new child.
     * @param  \vakata\phptree\Node  $node the child to add
     * @param  integer|null  $index the index to create at, defaults to `null`, meaning create as last child.
     * @return self
     */
    public function addChild(Node $child, int $index = null): self
    {
        if ($index === null) {
            $index = count($this->children);
        }
        if ($child->parent !== null) {
            $child->parent->removeChild($child);
        }
        array_splice($this->children, $index, 0, [$child]);
        $child->parent = $this;
        return $this;
    }
    /**
     * Remove a child.
     * @param  \vakata\phptree\Node  $node the child to remove
     * @return self
     */
    public function removeChild(Node $child): self
    {
        $this->children = array_values(
            array_filter($this->children, function ($v) use ($child) { return $v !== $child; })
        );
        if ($child->parent === $this) {
            $child->parent = null;
        }
        return $this;
    }
    /**
     * Remove all the children of the current node.
     * @return self
     */
    public function removeChildren(): self
    {
        foreach ($this->children as $child) {
            $child->parent = null;
        }
        $this->children = [];
        return $this;
    }
    /**
     * Move to a new parent.
     * @param  \vakata\phptree\Node  $parent the new parent
     * @param  integer|null         $index  the new position to move to, defaults to `null`, meaning as the last child
     * @return self
     */
    public function moveTo(Node $parent, $index = null): self
    {
        $parent->addChild($this, $index);
        return $this;
    }
    /**
     * Move to a new location, as a sibling of node.
     * @param  \vakata\phptree\Node  $reference the node to move next to
     * @return self
     */
    public function moveAfter(Node $reference): self
    {
        if ($reference->parent === null) {
            throw new TreeException('Invalid reference node');
        }
        $this->moveTo($reference->parent, $reference->getIndex() + 1);
        return $this;
    }
    /**
     * Move to a new location, as a sibling of node.
     * @param  \vakata\phptree\Node  $reference the node to move next to
     * @return self
     */
    public function moveBefore(Node $reference): self
    {
        if ($reference->parent === null) {
            throw new TreeException('Invalid reference node');
        }
        $this->moveTo($reference->parent, $reference->getIndex());
        return $this;
    }
    /**
     * Copy the current node to a new location.
     * @param  \vakata\phptree\Node  $parent the new parent
     * @param  integer|null         $index  the new position to copy to, defaults to `null`, meaning as the last child
     * @return \vakata\phptree\Node  the copied node
     */
    public function copyTo(Node $parent, $index = null): Node
    {
        $copy = Node::copy($this);
        $copy->moveTo($parent, $index);
        return $copy;
    }
    /**
     * Copy the current node to a new location, as a sibling of given node.
     * @param  \vakata\phptree\Node  $reference the reference node
     * @return \vakata\phptree\Node  the copied node
     */
    public function copyAfter(Node $reference): Node
    {
        $copy = Node::copy($this);
        $copy->moveAfter($reference);
        return $copy;
    }
    /**
     * Copy the current node to a new location, as a sibling of given node.
     * @param  \vakata\phptree\Node  $reference the reference node
     * @return \vakata\phptree\Node  the copied node
     */
    public function copyBefore(Node $reference): Node
    {
        $copy = Node::copy($this);
        $copy->moveBefore($reference);
        return $copy;
    }
    /**
     * Remove the current node
     */
    public function remove(): self
    {
        if ($this->parent !== null) {
            $this->parent->removeChild($this);
        }
        return $this;
    }
    /**
     * Does the node have a parent.
     * @return boolean     does the node have a parent
     */
    public function hasParent(): bool
    {
        return $this->parent !== null;
    }
    /**
     * Does the node have children.
     * @return boolean     does the node have children
     */
    public function hasChildren(): bool
    {
        return count($this->children) > 0;
    }
    /**
     * Is the node a leaf node.
     * @return boolean is the node a leaf
     */
    public function isLeaf(): bool
    {
        return $this->hasChildren() === false;
    }
    /**
     * Get all children
     * @return array<Node>                an array of `\vakata\phptree\Node` objects
     */
    public function getChildren(): array
    {
        return $this->children;
    }
    /**
     * Get the node parent
     */
    public function getParent(): ?Node
    {
        return $this->parent;
    }
    /**
     * Is the node a child of another node
     * @param  Node           $node the node to check against
     * @return boolean              is the node the parent
     */
    public function isChildOf(Node $node): bool
    {
        return $this->parent === $node;
    }
    /**
     * Is the node the parent of another node
     * @param  Node           $node the node to check against
     * @return boolean              is the node the parent
     */
    public function isParentOf(Node $node): bool
    {
        return $node->parent === $this;
    }
    /**
     * Get all node's ancestors
     * @return array<Node>     an array of `\vakata\phptree\Node` objects
     */
    public function getAncestors(): array
    {
        $parents = [];
        $reference = $this;
        while ($reference->parent !== null) {
            $parents[] = $reference->parent;
            $reference = $reference->parent;
        }
        return $parents;
    }
    /**
     * Get all of the node's descendants
     * @return array<Node>     an array of `\vakata\phptree\Node` objects
     */
    public function getDescendants(): array
    {
        $descendants = $this->children;
        foreach ($this->children as $child) {
            foreach ($child->getDescendants() as $descendant) {
                $descendants[] = $descendant;
            }
        }
        return $descendants;
    }
    /**
     * Is the node descendant of another node
     * @param  Node           $node the node to check against
     * @return boolean              is the node a descendant
     */
    public function isDescendantOf(Node $node): bool
    {
        $reference = $this;
        while ($reference->parent !== null) {
            if ($reference->parent === $node) {
                return true;
            }
            $reference = $reference->parent;
        }
        return false;
    }
    /**
     * Is the node the parent of another node
     * @param  Node           $node the node to check against
     * @return boolean              is the node the parent
     */
    public function isAncestorOf(Node $node): bool
    {
        return $node->isDescendantOf($this);
    }
    public function isCopy(): bool
    {
        return $this->copied !== null;
    }
    public function getOriginal(): Node
    {
        return $this->copied;
    }
    public function export(int $left = 1, string $id = 'id'): array
    {
        $nodes = [];
        $running = $left + 1;
        foreach ($this->children as $k => $child) {
            $nodes = array_merge($nodes, $child->export($running, $id));
            $nodes[count($nodes) - 1]['struct']['position'] = $k;
            $running = $nodes[count($nodes) - 1]['struct']['right'] + 1;
        }
        $nodes[] = [
            'data' => $this->data,
            'node' => $this,
            'struct' => [
                'id'       => $this->{$id},
                'parent'   => $this->parent ? $this->parent->{$id} : null,
                'position' => 0,
                'level'    => count($this->getAncestors()),
                'left'     => $left,
                'right'    => $running
            ]
        ];
        if ($left === 1) {
            usort($nodes, function ($a, $b) { return $a['struct']['left'] < $b['struct']['left'] ? -1 : 1; });
        }
        return $nodes;
    }
}
