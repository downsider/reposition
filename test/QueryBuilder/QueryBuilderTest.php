<?php

namespace Lexide\Reposition\Tests\QueryBuilder;

use Lexide\Reposition\QueryBuilder\QueryBuilder;
use Lexide\Reposition\QueryBuilder\TokenSequencerInterface;

class QueryBuilderTest extends \PHPUnit_Framework_TestCase {

    protected $tokenFactory;

    public function setUp()
    {
        $this->tokenFactory = \Mockery::mock("Lexide\\Reposition\\QueryBuilder\\QueryToken\\TokenFactory");
        $this->tokenFactory->shouldReceive("create")->andReturnUsing(function($type) {return $type;});
    }

    /**
     * @dataProvider queryStartProvider
     *
     * @param string $method
     * @param string $expectedType
     */
    public function testQueryStarts($method, $expectedType)
    {
        $entity = "entity";
        $metadata = \Mockery::mock("Lexide\\Reposition\\Metadata\\EntityMetadata")->shouldReceive("getEntity")->andReturn($entity)->getMock();

        $qb = new QueryBuilder($this->tokenFactory);
        /** @var TokenSequencerInterface $query */
        $query = $qb->{$method}($metadata);

        $this->assertEquals($expectedType, $query->getType());
        $this->assertEquals($entity, $query->getEntityName());
    }

    public function queryStartProvider()
    {
        return [
            [
                "find",
                QueryBuilder::TYPE_FIND
            ],
            [
                "save",
                QueryBuilder::TYPE_SAVE
            ],
            [
                "update",
                QueryBuilder::TYPE_UPDATE
            ],
            [
                "delete",
                QueryBuilder::TYPE_DELETE
            ]
        ];
    }

    public function testSequencingNestedExpressions()
    {
        $qb = new QueryBuilder($this->tokenFactory);
        $qb = $qb->closure(
            $qb->ref("field")->op("!=")->val("value1")->andL()
               ->ref("field")->op("!=")->val("value2")
        )->orL()
        ->closure(
            $qb->ref("field2")->op("!=")->val("value1")->andL()
               ->ref("field2")->op("!=")->val("value2")
        );

        $this->assertCount(19, $qb->getSequence());
    }

}
