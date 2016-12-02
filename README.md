# phptree

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Code Climate][ico-cc]][link-cc]
[![Tests Coverage][ico-cc-coverage]][link-cc]

Storing trees in a relational database. Keep in mind the tree needs to have a single root, so it is probably safe to begin with this structure (this example is mySQL, but it should be clear):
```sql
CREATE TABLE struct (
    id  int(10) unsigned NOT NULL AUTO_INCREMENT,
    lft int(10) unsigned NOT NULL,
    rgt int(10) unsigned NOT NULL,
    lvl int(10) unsigned NOT NULL,
    pid int(10) unsigned NOT NULL,
    pos int(10) unsigned NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO struct VALUES (1, 1, 2, 0, 0, 0);

# now you can use 1 as your tree root
```

## Install

Via Composer

``` bash
$ composer require vakata/phptree
```

## Usage

```php
// create an instance
$dbc = new \vakata\database\DB("mysqli://root@127.0.0.1/treedb");
$tree = new \vakata\phptree\Tree(
    $dbc,
    'tree_table',
    [ 'id' => 'id', 'parent' => 'pid', 'position' => 'pos', 'level' => 'lvl', 'left' => 'lft', 'right' => 'rgt' ]
);

// WORKING WITH NODES
$tree->getRoot()->getChildren(); // get all children of the root

$tree->getRoot()->addChild(new \vakata\phptree\Node(['key' => 'val1'])); // create a node
$tree->getRoot()->addChild(new \vakata\phptree\Node(['key' => 'val2'])); // create a node
$tree->save();
$tree->getNode(2)->moveTo($tree->getRoot(), 2);
$tree->getNode(3)->copyTo($tree->getRoot());
$tree->getNode(3)->remove();
```

Read more in the [API docs](docs/README.md)

## Testing

``` bash
$ composer test
```


## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email github@vakata.com instead of using the issue tracker.

## Credits

- [vakata][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information. 

[ico-version]: https://img.shields.io/packagist/v/vakata/phptree.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/vakata/phptree/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/vakata/phptree.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/vakata/phptree.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/vakata/phptree.svg?style=flat-square
[ico-cc]: https://img.shields.io/codeclimate/github/vakata/phptree.svg?style=flat-square
[ico-cc-coverage]: https://img.shields.io/codeclimate/coverage/github/vakata/phptree.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/vakata/phptree
[link-travis]: https://travis-ci.org/vakata/phptree
[link-scrutinizer]: https://scrutinizer-ci.com/g/vakata/phptree/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/vakata/phptree
[link-downloads]: https://packagist.org/packages/vakata/phptree
[link-author]: https://github.com/vakata
[link-contributors]: ../../contributors
[link-cc]: https://codeclimate.com/github/vakata/phptree

