Test utils
==========

This package contains some testing utilities common to various
solido suite packages.

Doctrine ORM
------------

#### EntityManagerTrait

Provides an instance of EntityManager upon a mocked DBAL connection.  
Useful to test a raw SQL composition and result hydration.

__onEntityManagerCreated__ method can be used to customize the entity manager
instance (or load/inject metadata) just after entity manager creation.

#### MockPlatform

Dummy DBAL platform for EntityManagerTrait.
 
Docrine Mongo ODM
-----------------

#### DocumentManagerTrait

Provides an instance of DocumentManager for mongo upon a mocked mongo Client.
