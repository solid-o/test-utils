Test utils
==========

This package contains some testing utilities common to various
solido suite packages.

Functional tests
----------------

#### JsonResponseTrait

Provides assertions to check content and deep properties of a returned json response.
The response has to be provided by a static `getResponse` method and must be an instance of
`Symfony\Component\HttpFoundation\Response`.

##### Property paths

The assertions in this trait allow checking deep properties into the returned JSON passing a
property path to the assertion method.

Dots (`.`) are used to access object properties, while brackets (`[]`) are used to access
array indexes.

Example: `user.emails[1]` should return the second element of the `emails` property of the
`user` object.

A special property path `.` can be used to indicate the entire json object or array
contained in the response.

##### Assertions

`assertJsonResponse(string $message = '')`

Asserts that the response contains a JSON __and__ has a `Content-Type` header containing `application/json`.
If the response content cannot be decoded, the assertion will fail.

`assertJsonResponsePropertiesExist(array $expected, string $message = '')`

Asserts that the JSON response contains all the properties in the `$expected` array. The properties
are specified as property paths.

`assertJsonResponsePropertyExists(string $propertyPath, string $message = '')`

Asserts that one property path exists in the response object.

`assertJsonResponsePropertyDoesNotExist(string $propertyPath, string $message = '')`

Asserts that one property path does not exist in the response object.

`assertJsonResponsePropertyEquals($expected, string $propertyPath, string $message = '')`

Asserts that the value in property path equals the `$expected` value.
PHPUnit `IsEqual` constraint is used to check the values equality.

`assertJsonResponsePropertyNotEquals($expected, string $propertyPath, string $message = '')`

Asserts that the value in property path does not equal the `$expected` value.

`assertJsonResponsePropertyIsType(string $expected, string $propertyPath, string $message = '')`

Asserts that the value in property path is of the specified type.
Type can be a FQCN or builtin type (array, bool, float, int, null, object, resource, string, scalar, callable).
PHPUnit `IsType` constraint is used to check the property type.

`assertJsonResponsePropertyIsArray(string $propertyPath, string $message = '')`

Short-hand method for `assertJsonResponsePropertyIsType('array', $propertyPath, $message)`

`assertJsonResponsePropertyCount(int $expected, string $propertyPath, string $message = '')`

Asserts that the value in property path is countable and its count
is equal to `$expected`.

`assertJsonResponsePropertyContains($expected, string $propertyPath, string $message = '')`

Asserts the specific response property contains the expected value.

Examples:
- `["Hello", "world", "!"]` contains "world"
- `[{one: "Hello"}]` contains `{ one: 'Hello' }`

`assertJsonResponsePropertyNotContains($unexpected, string $propertyPath, string $message = '')`

Asserts the specific response property does not contain the expected value.

`assertJsonResponsePropertyContainsString(string $expected, string $propertyPath, string $message = '')`

Asserts that the property is a string and contains the given value.

#### ResponseStatusTrait

Provides assertions to check the status code of a `Response` object retrieved by
a static `getResponse` method.

`assertResponseIs(int $expectedCode, string $message = '')`

Asserts that the response code is exactly the one passed in `$expectedCode`.

Short-hand assertions:

- `assertResponseIsOk(string $message = '')` - Expects status code 200
- `assertResponseIsCreated(string $message = '')` - Expects status code 201
- `assertResponseIsAccepted(string $message = '')` - Expects status code 202
- `assertResponseIsNoContent(string $message = '')` - Expects status code 204
- `assertResponseIsBadRequest(string $message = '')` - Expects status code 400
- `assertResponseIsUnauthorized(string $message = '')` - Expects status code 401
- `assertResponseIsPaymentRequired(string $message = '')` - Expects status code 402
- `assertResponseIsForbidden(string $message = '')` - Expects status code 403
- `assertResponseIsNotFound(string $message = '')` - Expects status code 404
- `assertResponseIsMethodNotAllowed(string $message = '')` - Expects status code 405
- `assertResponseIsPreconditionFailed(string $message = '')` - Expects status code 412
- `assertResponseIsUnprocessableEntity(string $message = '')` - Expects status code 422

Assertions that checks multiple status codes:

- `assertResponseIsRedirect(string $message = '')` - Expects status code 3xx
- `assertResponseIsNotRedirect(string $message = '')` - Expects status code not to be 3xx
- `assertResponseIsSuccessful(string $message = '')` - Expects status code 2xx
- `assertResponseIsNotSuccessful(string $message = '')` - Expects status code not to be 2xx

#### FunctionalTestTrait

Provides convenient methods to perform a request on a Symfony `WebTestCase`.
Includes `ResponseStatusTrait` and `JsonResponseTrait`.

Doctrine ORM
------------

#### EntityManagerTrait

Provides an instance of EntityManager upon a mocked DBAL connection.  
Useful to test a raw SQL composition and result hydration.

__onEntityManagerCreated__ method can be used to customize the entity manager
instance (or load/inject metadata) just after entity manager creation.

#### MockPlatform

Dummy DBAL platform for EntityManagerTrait.
 
Doctrine Mongo ODM
------------------

#### DocumentManagerTrait

Provides an instance of DocumentManager for mongo upon a mocked mongo Client.

Elastica ODM
------------

#### DocumentManagerTrait

Provides an instance of DocumentManager for elastica upon a mocked elastica Client.
