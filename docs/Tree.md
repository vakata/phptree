# vakata\phptree\Tree
This class maintains a tree structure in a database using both the adjacency and nested set models.

## Methods

| Name | Description |
|------|-------------|
|[__construct](#vakata\phptree\tree__construct)|Create an instance|
|[getRoot](#vakata\phptree\treegetroot)|Get the root node|
|[getNode](#vakata\phptree\treegetnode)|Get a node by its ID - used internally|

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


### vakata\phptree\Tree::getNode
Get a node by its ID - used internally  


```php
public function getNode (  
    mixed $id  
) : \vakata\phptree\Node    
```

|  | Type | Description |
|-----|-----|-----|
| `$id` | `mixed` | the node id |
|  |  |  |
| `return` | [`\vakata\phptree\Node`](Node.md) | the node object |

---

