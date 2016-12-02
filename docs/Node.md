# vakata\phptree\Node
This class represents a single node in the structure. Instances are usually created by the tree class.

## Methods

| Name | Description |
|------|-------------|
|[__construct](#vakata\phptree\node__construct)|Create an instance.|
|[getIndex](#vakata\phptree\nodegetindex)|Get the index of the node (among its siblings)|
|[addChild](#vakata\phptree\nodeaddchild)|Create a new child.|
|[removeChild](#vakata\phptree\noderemovechild)|Remove a child.|
|[removeChildren](#vakata\phptree\noderemovechildren)|Remove all the children of the current node.|
|[moveTo](#vakata\phptree\nodemoveto)|Move to a new parent.|
|[moveAfter](#vakata\phptree\nodemoveafter)|Move to a new location, as a sibling of node.|
|[moveBefore](#vakata\phptree\nodemovebefore)|Move to a new location, as a sibling of node.|
|[copyTo](#vakata\phptree\nodecopyto)|Copy the current node to a new location.|
|[copyAfter](#vakata\phptree\nodecopyafter)|Copy the current node to a new location, as a sibling of given node.|
|[copyBefore](#vakata\phptree\nodecopybefore)|Copy the current node to a new location, as a sibling of given node.|
|[remove](#vakata\phptree\noderemove)|Remove the current node|
|[hasParent](#vakata\phptree\nodehasparent)|Does the node have a parent.|
|[hasChildren](#vakata\phptree\nodehaschildren)|Does the node have children.|
|[isLeaf](#vakata\phptree\nodeisleaf)|Is the node a leaf node.|
|[getChildren](#vakata\phptree\nodegetchildren)|Get all children|
|[getParent](#vakata\phptree\nodegetparent)|Get all parents|
|[isChildOf](#vakata\phptree\nodeischildof)|Is the node a child of another node|
|[isParentOf](#vakata\phptree\nodeisparentof)|Is the node the parent of another node|
|[getAncestors](#vakata\phptree\nodegetancestors)|Get all node's ancestors|
|[getDescendants](#vakata\phptree\nodegetdescendants)|Get all of the node's descendants|
|[isDescendantOf](#vakata\phptree\nodeisdescendantof)|Is the node descendant of another node|
|[isAncestorOf](#vakata\phptree\nodeisancestorof)|Is the node the parent of another node|

---



### vakata\phptree\Node::__construct
Create an instance.  


```php
public function __construct (  
    array $data,  
    \vakata\phptree\Node $parent  
)   
```

|  | Type | Description |
|-----|-----|-----|
| `$data` | `array` | the node data (optional) |
| `$parent` | `\vakata\phptree\Node` | the parent of the node |

---


### vakata\phptree\Node::getIndex
Get the index of the node (among its siblings)  


```php
public function getIndex () : integer    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `integer` | the index |

---


### vakata\phptree\Node::addChild
Create a new child.  


```php
public function addChild (  
    \vakata\phptree\Node $node,  
    integer|null $index  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$node` | `\vakata\phptree\Node` | the child to add |
| `$index` | `integer`, `null` | the index to create at, defaults to `null`, meaning create as last child. |
|  |  |  |
| `return` | `self` |  |

---


### vakata\phptree\Node::removeChild
Remove a child.  


```php
public function removeChild (  
    \vakata\phptree\Node $node  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$node` | `\vakata\phptree\Node` | the child to remove |
|  |  |  |
| `return` | `self` |  |

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
| `return` | [`\vakata\phptree\Node`](Node.md) | the copied node |

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
| `return` | [`\vakata\phptree\Node`](Node.md) | the copied node |

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
| `return` | [`\vakata\phptree\Node`](Node.md) | the copied node |

---


### vakata\phptree\Node::remove
Remove the current node  


```php
public function remove ()   
```


---


### vakata\phptree\Node::hasParent
Does the node have a parent.  


```php
public function hasParent () : boolean    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `boolean` | does the node have a parent |

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


### vakata\phptree\Node::getChildren
Get all children  


```php
public function getChildren () : array    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `array` | an array of `\vakata\phptree\Node` objects |

---


### vakata\phptree\Node::getParent
Get all parents  


```php
public function getParent () : array    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `array` | an array of `\vakata\phptree\Node` objects |

---


### vakata\phptree\Node::isChildOf
Is the node a child of another node  


```php
public function isChildOf (  
    \Node $node  
) : boolean    
```

|  | Type | Description |
|-----|-----|-----|
| `$node` | `\Node` | the node to check against |
|  |  |  |
| `return` | `boolean` | is the node the parent |

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


### vakata\phptree\Node::getAncestors
Get all node's ancestors  


```php
public function getAncestors () : array    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `array` | an array of `\vakata\phptree\Node` objects |

---


### vakata\phptree\Node::getDescendants
Get all of the node's descendants  


```php
public function getDescendants () : array    
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


### vakata\phptree\Node::isAncestorOf
Is the node the parent of another node  


```php
public function isAncestorOf (  
    \Node $node  
) : boolean    
```

|  | Type | Description |
|-----|-----|-----|
| `$node` | `\Node` | the node to check against |
|  |  |  |
| `return` | `boolean` | is the node the parent |

---

