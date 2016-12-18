
Create a graph with all packages installed (Packages, packages requires, root package require-dev)

Export to a file graph-composer.svg in svg format to the root of your project, at the same place as the composer.json
```bash
./vendor/bin/graph-composer export
```

Or
```bash
./vendor/bin/graph-composer export .
```

Export to a specific path
```bash
./vendor/bin/graph-composer export . my/custom/path.svg
```

Export to a file graph-composer.png in png format.
```bash
./vendor/bin/graph-composer export . graph-composer.png
```

Export to a file graph-composer.jpeg in jpeg format.
```bash
./vendor/bin/graph-composer export . graph-composer.jpeg
```

## Create a graph and filter packages to display

### Do not display root dev dependencies (require-dev) 
```bash
./vendor/bin/graph-composer --no-root-dev-dep export
```

### Create a graph with all packages installed except one
```bash
./vendor/bin/graph-composer --no-packages="vendorA/packageA" export
```

### Create a graph with all packages installed except those indicated 
```bash
./vendor/bin/graph-composer --no-packages="vendorA/packageA vendorA/packageB vendorB/packageA" export
```

### Create a graph with only display package installed indicated 
```bash
./vendor/bin/graph-composer --packages="vendorA/packageA vendorA/packageB vendorB/packageA" export
```

### Create a graph with all packages installed except those of vendors indicated 
```bash
./vendor/bin/graph-composer --no-vendors="vendorA vendorB" export
```

### Create a graph with only packages installed from vendors indicated 
```bash
./vendor/bin/graph-composer --vendors="vendorA vendorB" export
```

### Combine the options above: display all packages installed from vendorA except packageB  
```bash
./vendor/bin/graph-composer --vendors="vendorA" --no-packages="vendorA/packageB" export
```

## Create a graph and filter dependencies to display

### Note that the following command displays only packages installed from vendorA but all dependencies from all vendors 
```bash
./vendor/bin/graph-composer --vendors="vendorA" --vendors-dep="vendorA"
```

### Create a graph with only packages installed from vendorA and dependencies from vendorA too 
```bash
./vendor/bin/graph-composer --vendors="vendorA" --dep-vendors="vendorA" export
```

### Create a graph with only packages installed from vendorA and all dependencies except those from vendorB and vendorC and the dependency vendorD/packageA 
```bash
./vendor/bin/graph-composer --vendors="vendorA" --no-dep-vendors="vendorA vendorB vendorC" --no-dep-packages="vendorD/packageA" export
```

## Create a graph with only some package having a specific tag in composer.json "extra" key. 

### Create a graph with package having in composer.json "extra" key a tag "type" with the value "business". Concretely, this mean you create a graph with only your business packages.

```bash
./vendor/bin/graph-composer --tags-pathes="type" --tags-values="business" export
```

### Create a graph with package having in composer.json "extra" key
* a tag "type" with the value "business"
* or this same tag "type" with the value "data-mining"

Concretely, this mean you create a graph with only your business and data-mining packages.

```bash
./vendor/bin/graph-composer 
--tags-pathes="type type" --tags-values="business data-mining" export
```

### Create a graph without package having in composer.json "extra" key
* a tag "type" with the value "bridge"
* or a tag "source_code" with the value "python"
* or this same tag "source_code" with the value "ruby"

```bash
./vendor/bin/graph-composer 
--no-tags-pathes="type source_code source_code" --no-tags-values="bridge python ruby"
```

A composer extra could look like below:
```json
{
	"extra": {
		"type": "business",
		"source_code": "python"
	}
}
```

Tags can be "deep".

```bash
./vendor/bin/graph-composer 
--tags-pathes="tag.type tag.type" --tags-values="business data-mining" export
```

A corresponding composer extra could look like below:
```json
{
	"extra": {
		"tag": {
			"type": "business",
			"source_code": "python"
		}
	}
}
```


Off course, you can still combine options.

Available options are:
* `format`
* `packages`
* `no-packages`
* `vendors`
* `no-vendors`
* `dep-packages`
* `no-dep-packages`
* `dep-vendors`
* `no-dep-vendors`
* `no-root-dev-dep`

Available formats are:
* `svg`
* `png`
* `jpeg`

## Create several graphes by packages or by vendors

### Create several graphes to a directory named `graph-composer` in svg format (that is the default format), one by packages indicated. Each graph display one package and all its dependency package.
```bash
./vendor/bin/graph-composer --separate-graph-packages="vendorA/packageA vendorA/packageB" multi-export
```

### Create several graphes to a directory named `graph-composer` in png format, one by vendor indicated. Each graph displays all vendor packages.
```bash
./vendor/bin/graph-composer --separate-graph-vendors="vendorA vendorB" --format="png" multi-export
```

### Combine with filters
```bash
./vendor/bin/graph-composer --no-packages="vendorA/packageB" --separate-graph-vendors="vendorA vendorB" --format="png" multi-export
```

Note that some combinations have no sense. For the moment, there is no control of the consistency of the combinations. It's to you to be carefull. This will be fixed later.

## Relies heavily on:
* [clue/graph-composer](https://github.com/clue/graph-composer)
* [jms/composer-deps-analyzer](https://github.com/schmittjoh/composer-deps-analyzer)