HWai
====

For combining different data sources to create webservice responses
efficiently and manageable.

The problem
---

When managing a RESTful api you want responses to be consistent and
composable, for example fetching a user:

```
{
    "id": 1,
    "name": "John",
    "friend_count": 30
}
```

This often requires the combination of fetching a user and adding an
`friend_count`. While this is manageable for a single user,
when you want to include a `user` object inside of another object, you
have to make sure these fields are included to be consistent.

While your api grows it is harder to see how every object is composed,
this is where HWai comes into play.

HWai allows you to point to routes providing certain data, each of these
routes can refer to one another and compose their data.

Basic usage
-----------

First of all we setup a router

```
$router = new Router();
```

Then register your first data fetching route, which will fetch a bunch
of users from the database. Note that `$path[1]` contains an array with results
that matched `/[0-9]+/` so you can combine the query.

```
$route = Route::build(['users', '/[0-9]+/'], function($path) {
    $users = User::whereIn('id', $path[1])->get();
    
    $result = [];
    foreach($users as $user) {
        $result[] = Data::build(
            ['users', $user->getId()],
            [
               'id' => $user->getId(),
               'name' => $user->getName()
            ]
        );
    }
    
    return $result;
});
```

To fetch data you can request data from a route:

```
$router->fetch(['users', [1,2]]);
```

Which will result in the array:

```
[
    [
        "id' => 1,
        "name' => "Name of user 1",
    ],
    [
        "id' => 2,
        "name' => "Name of user 2",
    ]
]
```

Composing
---

Let's take the example from Basic usage but in this case we add the unread count

```
$route = Route::build(['users', '/[0-9]+/'], function($path) {
    $users = User::whereIn('id', $path[1])->get();
    
    $result = [];
    foreach($users as $user) {
        $result[] = Data::build(
            ['users', $user->getId()],
            [
               'id' => $user->getId(),
               'name' => $user->getName()
    }
    
    $unreadCounts = Counters::whereIn('user_id', $path[1])->get();
    foreach($unreadCounts as $unreadCount) {
        $result[] = Data::build(
            ['users', $unreadCount->getUserId()],
            [
               'unread_count' => $unreadCount->getValue()
            ]
        );
    }
    
    return $result;
});
```

Since the first argument of `Data::build` (the route) will be the same
for fetching user data, as well as for fetching the unread counts; e.g. 
`['users', 1]`. HWai knows it can combine the results of those two.

```
[
    [
        "id' => 1,
        "name" => "Name of user 1",
        "unread_count" => 123
    ],
    [
        "id' => 2,
        "name" => "Name of user 2",
        "unread_count" => 0
    ]
]
```

References
----------

Now imagine we have users who belong to a company, so you'll have an
endpoint called `companies/1` to fetch a single company, but you'll
also have an endpoint `users/2` to fetch a single user. Since every
user has only one company and the company specification doesn't change 
that much, you'll probably want to include the company directly within
the user object, as so:

```
[
    "id" => 2,
    "name" => "John",
    "company" => {
        "id" => 1,
        ...
    }
]
```

Now a problem arises, you have a definition what a company looks like
in your api, but you want to keep them in sync between the `companies/1`
and `users/1` endpoint. This is where references come in.

```
$route = Route::build(['users', '/[0-9]+/'], function($path) {
    $users = User::whereIn('id', $path[1])->get();
    
    $result = [];
    foreach($users as $user) {
        $result[] = Data::build(
            ['users', $user->getId()],
            [
               'id' => $user->getId(),
               'name' => $user->getName(),
               'company' => new ReferenceToSingle(['companies', $user->getCompany()])
           ]
    }
   
    return $result;
});
```

This assumes you have registered another route `['companies', '/[0-9]+/']`
which will return companies. HWai will resolve the reference by fetching
data from this route and merge it with the user object.

Since HWai first combines al data it will also combine all references
to check if it can combine calls to your `['companies', '/[0-9]+/']`
route in case you fetched multiple users, and possibly multiple companies.
Meaning it will make only one round trip to your `['companies', '/[0-9]+/']`
route!

License
---------
Copyright 2016 Label305 B.V.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

[http://www.apache.org/licenses/LICENSE-2.0](http://www.apache.org/licenses/LICENSE-2.0)

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
