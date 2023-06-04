# Cypher Query Builder

Create Cypher queries in a fluent and easy to understand way via an opinionated builder pattern.

## Examples


### Simple pattern match example:

This query finds all the colleagues of Alice and Bob:

```php
use PhpGraphGroup\CypherQueryBuilder\QueryBuilder;

$results = QueryBuilder::fromNode('a:Person')
    ->matchingRelationship('a', 'IS_COLLEAGUE', 'b:Person')
    ->whereIn('a.name', ['Alice', 'Bob'])
    ->return('b.name AS name') // Notice of the present tense implies the query gets executed immediately.
```

The builder runs this query and returns the results:
    
```cypher
MATCH (a:Person)-[:IS_COLLEAGUE]->(b:Person)
WHERE a.name IN $names
RETURN b.name AS name
```

### Simple Create example:

Creates a new Person and makes it the colleague of Alice.

```php
use PhpGraphGroup\CypherQueryBuilder\QueryBuilder;

$results = QueryBuilder::new()
    ->matchingNode('a:Person')
    ->where('a.name', '=', 'Alice')
    ->creatingNode('b:Person')
    ->creatingRelationship('a', 'IS_COLLEAGUE', 'b')
    ->create(['b.name' => 'Bob'])
```

The builder runs this query:
    
```cypher
MATCH (a:Person)
WHERE a.name = $param0
CREATE (b:Person), (a)-[:IS_COLLEAGUE]->(b)
SET b.name = $param1
```

### Simple Update example:


## How it works

Cypher is an incredible query language that offers great flexibility. However, this flexibility can make it difficult to understand and maintain queries, especially in the form of a query builder. 

This library aims to make it easier to write and maintain Cypher queries by providing a fluent builder pattern that is understandable.

It specifically limits the possibilities so the builder is easier to reason about and use. All database actions are still possible, but chaining long and complex clauses after another is not possible anymore. 

This is because the main angle and opinion this query builder takes is that long complex multi-clausal queries are not preferred. It is difficult to understand and there is a big chance the performance of the queries will degrade.

The builder has only one of each clause available, and the position of these clauses is fixed:

```text
MATCH { match patterns }
OPTIONAL MATCH { optional match patterns }
WHERE { where conditions }
CALL { subquery }

DELETE { deleted variables }
DETACH DELETE { deleted variables }

REMOVE { removed properties & labels }
CREATE { create patterns }
SET { set assignments }
MERGE { merge pattern }
ON CREATE SET { set assignments }
ON MATCH SET { set assignments }

RETURN { return expressions }
ORDER BY { ASC|DESC } { order expressions }
SKIP { skip count }
LIMIT { limit count }
```

The query builder will only run certain parts of the query depending on which methods you are calling. The methods are intuitive and easy to understand:

| Method    | Runs                                                                     |
|-----------|--------------------------------------------------------------------------|
| get()     | MATCH - OPTIONAL MATCH - CALL - WHERE - RETURN - ORDER BY - SKIP - LIMIT |
| create()  | MATCH - WHERE - CREATE - SET                                             |
| update()  | MATCH - WHERE - SET                                                      |
| merge()   | MATCH - WHERE - MERGE - ON CREATE - ON MATCH                             |
| execute() | - ALL CLAUSES -                                                          |

There are some cases where UNWIND is used behind the scenes to allow for mass insertions, but that is beyond the scope of this introduction.

## Variable Usage

Variables are used to refer to nodes, relationships and aliases. An alias can refer to a property, node, relationship or function call result.

Because of the way the builder structures the query, Cypher will not allow variable reassignment. To maintain flexibility and to allow for raw statements, the query builder to not check for either existance or reassignment of variables. Only after the query is built and sent to the server will an error occur.


## Property Usage

Properties refer to properties on a variable. That means that a user should use the dot notation to unambiguously refer to a property. If the does not use a dot notation and refers just to the property, the builder will use it to refer properties on the variable of the entry node or relationship of the builder.

```php
use PhpGraphGroup\CypherQueryBuilder\QueryBuilder;

// Refer unambiguously to the property 'name' on the variable 'p'
$name = QueryBuilder::from('Person', 'p')
    ->where('p.name', '=', 'Alice')
    ->returning('p.name')
    ->only()
// Runs like:
// MATCH (p:Person) WHERE p.name = $param0 RETURN p.name AS name LIMIT 1

// Refer to the property of p without using the dot notation.
$lastNames = QueryBuilder::from('Person', 'p')
    ->where('name', '=', 'Alice')
    ->pluck('lastName')
// Runs like:
// MATCH (p:Person) WHERE p.name = $param0 RETURN p.lastName AS lastName

// Automatically generate a name based on the Label and get al the friends of Alice for over a year.
$friends = QueryBuilder::from('Person')
    ->matchingRelationship('person', 'FRIENDS_WITH', 'friend', 'friendsWith')
    ->matchingNode('Person', 'friend')
    ->where('friendsWith.since', '<=', (new DateTime())->sub(new DateInterval('P1Y')))
    ->andWhere('person.name', '=', 'Alice')
    ->return('friend.name AS name', 'friendsWith.since AS friendsSince')
// Runs like:
// MATCH (person:Person)-[friendsWith:FRIENDS_WITH]->(friend:Person) WHERE friendsWith <= $param0 AND person.name = $param1 RETURN friend.name AS name, friendsWith.since AS friendsSince
```


