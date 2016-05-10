# vakata\phptree\Node
This class represents a single node in the structure. Instances are usually created by the tree class.

## Methods

| Name | Description |
|------|-------------|
|[__construct](#vakata\phptree\node__construct)|Create an instance.|
|[addChild](#vakata\phptree\nodeaddchild)|Create a new child.|
|[removeChild](#vakata\phptree\noderemovechild)|Remove a child.|
|[moveTo](#vakata\phptree\nodemoveto)|Move to a new parent.|
|[moveAfter](#vakata\phptree\nodemoveafter)|Move to a new location, as a sibling of node.|
|[moveBefore](#vakata\phptree\nodemovebefore)|Move to a new location, as a sibling of node.|
|[copyTo](#vakata\phptree\nodecopyto)|Copy the current node to a new location.|
|[copyAfter](#vakata\phptree\nodecopyafter)|Copy the current node to a new location, as a sibling of given node.|
|[copyBefore](#vakata\phptree\nodecopybefore)|Copy the current node to a new location, as a sibling of given node.|
|[remove](#vakata\phptree\noderemove)|Remove the current node|
|[removeChildren](#vakata\phptree\noderemovechildren)|Remove all the children of the current node.|
|[isLeaf](#vakata\phptree\nodeisleaf)|Is the node a leaf node.|
|[hasChildren](#vakata\phptree\nodehaschildren)|Does the node have children.|
|[getChildren](#vakata\phptree\nodegetchildren)|Get all children.|
|[getChild](#vakata\phptree\nodegetchild)|Get a specific child by its index.|
|[getParent](#vakata\phptree\nodegetparent)|Get the parent of the node.|
|[getIndex](#vakata\phptree\nodegetindex)|Get the node's position index.|
|[getID](#vakata\phptree\nodegetid)|Get the ID.|
|[getChildrenCount](#vakata\phptree\nodegetchildrencount)|Get the children count.|
|[getDescendants](#vakata\phptree\nodegetdescendants)|Get all descendants up to an optional depth|
|[getDescendantsCount](#vakata\phptree\nodegetdescendantscount)|Get the count of all descendants|
|[getParents](#vakata\phptree\nodegetparents)|Get all parents|
|[isDescendantOf](#vakata\phptree\nodeisdescendantof)|Is the node descendant of another node|
|[isChildOf](#vakata\phptree\nodeischildof)|Is the node child of another node|
|[isParentOf](#vakata\phptree\nodeisparentof)|Is the node the parent of another node|
|[isAncestorOf](#vakata\phptree\nodeisancestorof)|Is the node an ancestor of another node|

---



### vakata\phptree\Node::__construct
Create an instance.  


```php
public function __construct (  
    \vakata\phptree\Tree $tree,  
    array $data  
)   
```

|  | Type | Description |
|-----|-----|-----|
| `$tree` | `\vakata\phptree\Tree` | the parent tree instance |
| `$data` | `array` | the node data |

---


### vakata\phptree\Node::addChild
Create a new child.  


```php
public function addChild (  
    integer|null $index  
) : \vakata\phptree\Node    
```

|  | Type | Description |
|-----|-----|-----|
| `$index` | `integer`, `null` | the index to create at, defaults to `null`, meaning create as last child. |
|  |  |  |
| `return` | [`\vakata\phptree\Node`](Node.md) | the newly created node |

---


### vakata\phptree\Node::removeChild
Remove a child.  


```php
public function removeChild (  
    integer $index  
)   
```

|  | Type | Description |
|-----|-----|-----|
| `$index` | `integer` | the index of the child to remove |

---


### vakata\phptree\Node::moveTo
Move to a new parent.  


```php
public function moveTo (  
    \vakata\phptree\Node $parent,  
    integer|null $index  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$parent` | `\vakata\phptree\Node` | the new parent |
| `$index` | `integer`, `null` | the new position to move to, defaults to `null`, meaning as the last child |
|  |  |  |
| `return` | `self` |  |

---


### vakata\phptree\Node::moveAfter
Move to a new location, as a sibling of node.  


```php
public function moveAfter (  
    \vakata\phptree\Node $reference  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$reference` | `\vakata\phptree\Node` | the node to move next to |
|  |  |  |
| `return` | `self` |  |

---


### vakata\phptree\Node::moveBefore
Move to a new location, as a sibling of node.  


```php
public function moveBefore (  
    \vakata\phptree\Node $reference  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$reference` | `\vakata\phptree\Node` | the node to move next to |
|  |  |  |
| `return` | `self` |  |

---


### vakata\phptree\Node::copyTo
Copy the current node to a new location.  


```php
public function copyTo (  
    \vakata\phptree\Node $parent,  
    integer|null $index  
) : \vakata\phptree\Node    
```

|  | Type | Description |
|-----|-----|-----|
| `$parent` | `\vakata\phptree\Node` | the new parent |
| `$index` | `integer`, `null` | the new position to copy to, defaults to `null`, meaning as the last child |
|  |  |  |
| `return` | [`\vakata\phptree\Node`](Node.md) | the newly create node |

---


### vakata\phptree\Node::copyAfter
Copy the current node to a new location, as a sibling of given node.  


```php
public function copyAfter (  
    \vakata\phptree\Node $reference  
) : \vakata\phptree\Node    
```

|  | Type | Description |
|-----|-----|-----|
| `$reference` | `\vakata\phptree\Node` | the reference node |
|  |  |  |
| `return` | [`\vakata\phptree\Node`](Node.md) | the newly create node |

---


### vakata\phptree\Node::copyBefore
Copy the current node to a new location, as a sibling of given node.  


```php
public function copyBefore (  
    \vakata\phptree\Node $reference  
) : \vakata\phptree\Node    
```

|  | Type | Description |
|-----|-----|-----|
| `$reference` | `\vakata\phptree\Node` | the reference node |
|  |  |  |
| `return` | [`\vakata\phptree\Node`](Node.md) | the newly create node |

---


### vakata\phptree\Node::remove
Remove the current node  


```php
public function remove ()   
```


---


### vakata\phptree\Node::removeChildren
Remove all the children of the current node.  


```php
public function removeChildren () : self    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `self` |  |

---


### vakata\phptree\Node::isLeaf
Is the node a leaf node.  


```php
public function isLeaf () : boolean    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `boolean` | is the node a leaf |

---


### vakata\phptree\Node::hasChildren
Does the node have children.  


```php
public function hasChildren () : boolean    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `boolean` | does the node have children |

---


### vakata\phptree\Node::getChildren
Get all children.  


```php
public function getChildren () : array    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `array` | an array of `\vakata\phptree\Node` objects |

---


### vakata\phptree\Node::getChild
Get a specific child by its index.  


```php
public function getChild (  
    integer $index  
) : \vakata\phptree\Node    
```

|  | Type | Description |
|-----|-----|-----|
| `$index` | `integer` | the child's index |
|  |  |  |
| `return` | [`\vakata\phptree\Node`](Node.md) | the child |

---


### vakata\phptree\Node::getParent
Get the parent of the node.  


```php
public function getParent () : \vakata\phptree\Node    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | [`\vakata\phptree\Node`](Node.md) | the parent node |

---


### vakata\phptree\Node::getIndex
Get the node's position index.  


```php
public function getIndex () : integer    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `integer` | the position of the node among its siblings |

---


### vakata\phptree\Node::getID
Get the ID.  


```php
public function getID () : integer    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `integer` | the node ID |

---


### vakata\phptree\Node::getChildrenCount
Get the children count.  


```php
public function getChildrenCount () : integer    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `integer` | the children count |

---


### vakata\phptree\Node::getDescendants
Get all descendants up to an optional depth  


```php
public function getDescendants (  
    integer|null $depth  
) : array    
```

|  | Type | Description |
|-----|-----|-----|
| `$depth` | `integer`, `null` | optional max depth (counting from the current node) to include |
|  |  |  |
| `return` | `array` | an array of `\vakata\phptree\Node` objects |

---


### vakata\phptree\Node::getDescendantsCount
Get the count of all descendants  


```php
public function getDescendantsCount () : integer    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `integer` | the descendant count |

---


### vakata\phptree\Node::getParents
Get all parents  


```php
public function getParents () : array    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `array` | an array of `\vakata\phptree\Node` objects |

---


### vakata\phptree\Node::isDescendantOf
Is the node descendant of another node  


```php
public function isDescendantOf (  
    \Node $node  
) : boolean    
```

|  | Type | Description |
|-----|-----|-----|
| `$node` | `\Node` | the node to check against |
|  |  |  |
| `return` | `boolean` | is the node a descendant |

---


### vakata\phptree\Node::isChildOf
Is the node child of another node  


```php
public function isChildOf (  
    \Node $node  
) : boolean    
```

|  | Type | Description |
|-----|-----|-----|
| `$node` | `\Node` | the node to check against |
|  |  |  |
| `return` | `boolean` | is the node a child |

---


### vakata\phptree\Node::isParentOf
Is the node the parent of another node  


```php
public function isParentOf (  
    \Node $node  
) : boolean    
```

|  | Type | Description |
|-----|-----|-----|
| `$node` | `\Node` | the node to check against |
|  |  |  |
| `return` | `boolean` | is the node the parent |

---


### vakata\phptree\Node::isAncestorOf
Is the node an ancestor of another node  


```php
public function isAncestorOf (  
    \Node $node  
) : boolean    
```

|  | Type | Description |
|-----|-----|-----|
| `$node` | `\Node` | the node to check against |
|  |  |  |
| `return` | `boolean` | is the node an ancestor |

---

