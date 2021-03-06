<?php

namespace NilPortugues\Tests\Api\JsonApi\Integrations\Doctrine;

use NilPortugues\Api\JsonApi\JsonApiSerializer;
use NilPortugues\Api\JsonApi\JsonApiTransformer;
use NilPortugues\Api\Mapping\Mapper;
use NilPortugues\Tests\Api\JsonApi\Integrations\Doctrine\Entity\Customer;
use NilPortugues\Tests\Api\JsonApi\Integrations\Doctrine\Mappings\CustomerMapping;

class DoctrineTest extends AbstractTestCase
{
    public function testPersistAndSerializeSimpleEntity()
    {
        $newCustomer = new Customer();
        $newCustomer->setActive(true);
        $newCustomer->setName('Name 1');

        self::$entityManager->persist($newCustomer);
        self::$entityManager->flush();
        $repoCustomer = self::$entityManager->getRepository(Customer::class);
        $savedCustomer = $repoCustomer->findAll();

        $classConfig = [
            CustomerMapping::class,
        ];

        $expected = <<<JSON
			{
				"data":
					[{
						"type":"customer",
						"id":"1",
						"attributes":
							{
								"active":true,
								"id":1,
								"name":"Name 1"
							},
						"links":
							{
								"self":{"href":"http://example.com/customer/1"}
							}
					}],
				"jsonapi":
					{
						"version":"1.0"
					}
			}
JSON;
        $mapper = new Mapper($classConfig);
        $transformer = new JsonApiTransformer($mapper);
        $serializer = new JsonApiSerializer($transformer);
        $customerSerialize = $serializer->serialize($savedCustomer);

        $this->assertEquals($newCustomer->getId(), $savedCustomer[0]->getId());
        $this->assertEquals($newCustomer->getName(), $savedCustomer[0]->getName());
        $this->assertEquals(json_decode($expected, true), json_decode($customerSerialize, true));
    }
}
