# vakata\phptree\Tree
This class maintains a tree structure in a database using both the adjacency and nested set models.

## Methods

| Name | Description |
|------|-------------|
|[__construct](#vakata\phptree\tree__construct)|Create an instance|
|[getRoot](#vakata\phptree\treegetroot)|Get the root node|
|[node](#vakata\phptree\treenode)|Get a node by its ID - used internally|
|[parents](#vakata\phptree\treeparents)|Get all parents by left / right indexes. Used internally.|
|[children](#vakata\phptree\treechildren)|Get a list of children by ID. Used internally|
|[descendants](#vakata\phptree\treedescendants)|Get all descendants by left, right indexes and optional depth. Used internally.|
|[create](#vakata\phptree\treecreate)|Create a new node.|
|[move](#vakata\phptree\treemove)|Move a node to another place in the tree.|
|[copy](#vakata\phptree\treecopy)|Copy a node to another place in the tree.|
|[remove](#vakata\phptree\treeremove)|Remove a node by ID.|

---



### vakata\phptree\Tree::__construct
Create an instance  


```php
public function __construct (  
    \DatabaseInterface $db,  
    string $tb,  
    integer $root,  
    array $fields  
)   
```

|  | Type | Description |
|-----|-----|-----|
| `$db` | `\DatabaseInterface` | A database connection instance |
| `$tb` | `string` | the table name where the tree will be stored |
| `$root` | `integer` | the root of the tree (defaults to `1`) |
| `$fields` | `array` | a map containing the column names for: id, left, right, level, parent, position |

---


### vakata\phptree\Tree::getRoot
Get the root node  


```php
public function getRoot () : \vakata\phptree\Node    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | [`\vakata\phptree\Node`](Node.md) | the root node object |

---


### vakata\phptree\Tree::node
Get a node by its ID - used internally  


```php
public function node (  
    integer $id  
) : \vakata\phptree\Node    
```

|  | Type | Description |
|-----|-----|-----|
| `$id` | `integer` | the node id |
|  |  |  |
| `return` | [`\vakata\phptree\Node`](Node.md) | the node object |

---


### vakata\phptree\Tree::parents
Get all parents by left / right indexes. Used internally.  


```php
public function parents (  
    integer $lft,  
    integer $rgt  
) : array    
```

|  | Type | Description |
|-----|-----|-----|
| `$lft` | `integer` | the left index |
| `$rgt` | `integer` | the right index |
|  |  |  |
| `return` | `array` | an array of all parent `vakata\phptree\Node` objects |

---


### vakata\phptree\Tree::children
Get a list of children by ID. Used internally  


```php
public function children (  
    integer $id  
) : array    
```

|  | Type | Description |
|-----|-----|-----|
| `$id` | `integer` | the ID |
|  |  |  |
| `return` | `array` | an array of children `vakata\phptree\Node` objects |

---


### vakata\phptree\Tree::descendants
Get all descendants by left, right indexes and optional depth. Used internally.  


```php
public function descendants (  
    integer $lft,  
    integer $rgt,  
    integer|null $lvl  
) : array    
```

|  | Type | Description |
|-----|-----|-----|
| `$lft` | `integer` | the left index |
| `$rgt` | `integer` | the right index |
| `$lvl` | `integer`, `null` | the max depth to include, optional - defaults to `null` |
|  |  |  |
| `return` | `array` | an array of descendant `vakata\phptree\Node` objects |

---


### vakata\phptree\Tree::create
Create a new node.  


```php
public function create (  
    integer|null $parent,  
    integer|null $position  
) : integer    
```

|  | Type | Description |
|-----|-----|-----|
| `$parent` | `integer`, `null` | the parent to create in, `null` means create in the root node |
| `$position` | `integer`, `null` | the position to create at, `null` means as last child |
|  |  |  |
| `return` | `integer` | the ID of the created node |

---


### vakata\phptree\Tree::move
Move a node to another place in the tree.  


```php
public function move (  
    integer $id,  
    integer $parent,  
    integer|null $position  
)   
```

|  | Type | Description |
|-----|-----|-----|
| `$id` | `integer` | the ID of the node to move |
| `$parent` | `integer` | the new parent ID |
| `$position` | `integer`, `null` | the position to move to, defaults to `null`, which means move as last child |

---


### vakata\phptree\Tree::copy
Copy a node to another place in the tree.  


```php
public function copy (  
    integer $id,  
    integer $parent,  
    integer|null $position  
)   
```

|  | Type | Description |
|-----|-----|-----|
| `$id` | `integer` | the ID of the node to copy |
| `$parent` | `integer` | the new parent ID |
| `$position` | `integer`, `null` | the position to copy to, defaults to `null`, which means copy as last child |

---


### vakata\phptree\Tree::remove
Remove a node by ID.  


```php
public function remove (  
    integer $id  
)   
```

|  | Type | Description |
|-----|-----|-----|
| `$id` | `integer` | the ID of the node to remove |

---

