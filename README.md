# Eloquent Object ID

MongoDB-style BSON's ObjectId for Eloquent and Laravel.

## Installation

Install the package via Composer:

```bash
composer require slime-systems/eloquent-object-id
```

## The Why

Before diving into how to use ObjectIDs, it's important to understand why they are so useful. The ObjectID is a remarkable invention, offering significant benefits beyond a simple unique identifier.

### The Basic

An ObjectID is a type of identifier, similar to Eloquent’s default incremental ID, used to reference a specific entity in the system.

ObjectIDs are powerful because they can be generated independently and in a decentralized manner by any party. This eliminates the need to rely on a database or a centrally managed registry during ID creation. The generated IDs are designed to be sufficiently unique for most use cases, making collisions highly unlikely.

### ObjectID is More Than a Generic ID

ObjectIDs inherently contain a timestamp. This property is extremely useful, for example, when performing time-based queries:

Suppose we want to count the cats registered in the past week:

```php
$end = Carbon::now('Asia/Tokyo')->startOfWeek();
$start = ObjectId::fromTime($end->subWeek(), unique: false);
$end = ObjectId::fromTime($end, unique: false);

$lastWeekCatCount = Cat::where('id', '>=', OI::val($start))
    ->where('id', '<', OI::val($end))
    ->count();

// Don't worry about the utility functions just yet; we'll cover them shortly.
```

If the ObjectID is set as the primary key, there is no need for a separate index to perform these time-based lookups. This query will be as optimized as one using an indexed `created_at` field, entirely avoiding a full table scan.

Essentially, ObjectIDs offer the querying power of timestamps directly within the identifier.

### ObjectID is Also More Than Just a Timestamp

The ability to use the ID for chronological and offset-based queries is crucial for efficient pagination (known as keyset or cursor pagination).

A common, but highly unoptimized, way to paginate with an offset in Eloquent is:
```php
Cat::orderBy('created_at', 'asc')
    ->skip(20000 * $perPage) // Fetch page 20,000 of the registry
    ->limit($perPage)
    ->get();
```
The `skip` (or `OFFSET`) clause prevents database indexes from being fully utilized, leading to very slow queries on large datasets.

A better approach is to use the last known ID to fetch the next page, which works great with unique, chronologically ordered incremental IDs:

```php
Cat::where('id', '>', $lastKnownCat->id)
    ->orderBy('id')
    ->limit($perPage)
    ->get();
```

This query is fast because the `where('id', '>', ...)` condition can efficiently leverage the primary key index for lookup.

Attempting the same with only a timestamp field has a critical flaw:

```php
Cat::where('created_at', '>', $lastKnownCat->created_at)
    ->orderBy('created_at')
    ->limit($perPage)
    ->get();
```

While this could be fast if `created_at` is indexed, the logic is broken because the `created_at` timestamp is not guaranteed to be unique. If two entities are created in the same second, this query might incorrectly skip or miss records.

Guess what else is chronologically ordered and guaranteed to be unique?

```php
Cat::where('id', '>', OI::val($lastKnownCat->id))
    ->orderBy('id')
    ->limit($perPage)
    ->get();
```

That's right: __ObjectID__!

### Various Ways to Utilize This Invention

As demonstrated, the ObjectID gives you the best of what both a timestamp and a unique, incremental ID have to offer. There is more than one way to utilize this powerful identifier. Have fun explore its full potential!

#### Note on Usage

In the following section, I will use ObjectID as a drop-in replacement for Eloquent's incremental ID.

While I do provide the helper function `OI::setDefault`, I personally do not believe this is the most effective or interesting way to utilize ObjectIDs.

If you bring forth the ObjectID's full potential, you'll likely find yourself not needing to use `OI::setDefault` at all.

## Usage

### Database Migration

Use the `objectId` column type in your migrations. This creates a binary column suitable for storing the ObjectId.

```php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

Schema::create('cats', function (Blueprint $table) {
    $table->objectId('id')->primary(); // <- here
    $table->string('name');
});
```

### Model Setup

In your Eloquent model, cast the field using `SlimeSystems\EloquentObjectId\ObjectIdCast`.

```php
use Illuminate\Database\Eloquent\Model;
use SlimeSystems\EloquentObjectId\ObjectIdCast;

class Cat extends Model
{
    public $timestamps = false;
    protected $guarded = []; // for demonstration purposes

    protected $casts = [
        'id' => ObjectIdCast::class,
    ];
}
```

### Auto-generating IDs

Models can be configured to autogenerate an ObjectId. An example using the `OI::setDefault` helper in the `booted` method is provided below.

```php
use Illuminate\Database\Eloquent\Model;
use SlimeSystems\EloquentObjectId\ObjectIdCast;
use SlimeSystems\EloquentObjectId\OI;

class Cat extends Model
{
    protected $casts = [
        'id' => ObjectIdCast::class,
    ];

    protected static function booted()
    {
        static::creating(OI::setDefault('id')); // <- here
    }
}
```

### Accessing the ID from models

The ID will be automatically cast to `SlimeSystems\ObjectId` when retrieved from the database.

```php
use SlimeSystems\ObjectId;

// Create with auto-generated ID
$cat = Cat::create(['name' => 'Luna']);

// Create with explicit ID
$id = new ObjectId;
Cat::create(['id' => $id, 'name' => 'Peanut']);

// Examples
$cat = Cat::latest()->first();
$cat->id->toString();  // a hex string
$id->equals($cat->id); // true
```

You can access all `SlimeSystems\ObjectId` methods documented at https://github.com/slime-systems/php-object-id.

### Querying

`OI::val()` can be used to ensure that ObjectId is correctly formatted for compatibility with Eloquent queries.

```php
use SlimeSystems\EloquentObjectId\OI;
use SlimeSystems\ObjectId;

$someId = new ObjectId;

// Find by ID
$cat = Cat::find(OI::val($someId));

// Comparison queries
$cats = Cat::where('id', '>', OI::val($someId))->get();

// `::val` also ensures compatibility with hexadecimal and binary formats of ObjectId
$cat = Cat::find(OI::val('0123456789abcdef1011121')); // <- this works, but probably doesn't make sense in terms of value

// Please note that the ID object itself is not compatible with Eloquent
$cat = Cat::find($someId); // <- this doesn't work; Eloquent won't understand what to do with the object.
```

## License

This library is open-sourced software licensed under the [BSD-2-Clause license](./LICENSE.md).
