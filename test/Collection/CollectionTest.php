<?php

namespace Lexide\Reposition\Test\Collection;
use Lexide\Reposition\Collection\Collection;

/**
 * CollectionTest
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var MockEntity
     */
    protected $entity1;

    /**
     * @var MockEntity
     */
    protected $entity2;

    /**
     * @var MockEntity
     */
    protected $entity3;

    public function setup()
    {
        $this->entity1 = new MockEntity(1);
        $this->entity2 = new MockEntity(2);
        $this->entity3 = new MockEntity(3);
    }

    public function testAdding()
    {
        $collection = new Collection([$this->entity1]);
        $collection->setChangeTracking();

        // add an entity
        $collection->add($this->entity2);
        $this->assertEquals(2, $collection->count(), "Check the entity has been added");
        $this->assertEquals([$this->entity2], $collection->getAddedEntities(), "Check the 'added' array is correct");

        // add the same entity (should fail silently)
        $collection->add($this->entity2);
        $this->assertEquals(2, $collection->count(), "Check the duplicate entity has not been added");

        // add another entity
        $collection->add($this->entity3);
        $this->assertEquals(3, $collection->count(), "Check more than one entity has been added");
        $this->assertEquals([$this->entity2, $this->entity3], $collection->getAddedEntities(), "Check the 'added' array has more than 1 entity");
    }

    public function testRemoving()
    {
        $collection = new Collection([$this->entity1, $this->entity2]);
        $collection->setChangeTracking();
        $this->assertEquals(2, $collection->count(), "Check collection set up correctly");

        // remove an entity
        $collection->remove($this->entity1);
        $this->assertEquals(1, $collection->count(), "Check entity was removed");
        $this->assertEquals([$this->entity1], $collection->getRemovedEntities(), "Check 'removed' array is correct");

        // remove an unknown entity
        $collection->remove($this->entity3);
        $this->assertEquals(1, $collection->count(), "Check unknown entity was not removed");

        // remove another entity
        $collection->remove($this->entity2);
        $this->assertEquals(0, $collection->count(), "Check another entity is removed");
        $this->assertEquals([$this->entity1, $this->entity2], $collection->getRemovedEntities(), "Check 'removed' array contains another entity");
    }

    public function testRemoveBy()
    {
        $this->entity1->property = 1;
        $this->entity2->property = 2;
        $this->entity3->property = 3;

        $collection = new Collection([$this->entity1, $this->entity2, $this->entity3]);
        $collection->setChangeTracking();
        $this->assertEquals(3, $collection->count());

        // remove by property name
        $collection->removeBy("property", 2);
        $this->assertEquals(2, $collection->count());
        $this->assertEquals([$this->entity2], $collection->getRemovedEntities());

        // don't remove when value can't be found
        $collection->removeBy("property", 4);
        $this->assertEquals(2, $collection->count());

        // remove by full getter name
        $collection->removeBy("getProperty", 1);
        $this->assertEquals(1, $collection->count());
        $this->assertEquals([$this->entity2, $this->entity1], $collection->getRemovedEntities());

    }

    /**
     * @expectedException \Lexide\Reposition\Exception\CollectionException
     */
    public function testRemoveByUnknownProperty()
    {
        $collection = new Collection([$this->entity1, $this->entity2, $this->entity3]);
        $collection->removeBy("wtf", 1);
    }

    public function testDeduping()
    {
        // test adding then removing
        $collection = new Collection();
        $collection->setChangeTracking();

        $collection->add($this->entity1);
        $collection->add($this->entity2);
        $collection->remove($this->entity1);

        $added = $collection->getAddedEntities();
        $this->assertCount(1, $added, "Check the added entity array only has one entry");
        $this->assertCount(0, $collection->getRemovedEntities(), "Check the removed entity array has no entries");
        $this->assertSame($this->entity2, array_pop($added));

        // test removing then adding
        $collection = new Collection();
        $collection->add($this->entity1);
        $collection->add($this->entity2);
        $collection->setChangeTracking();

        $collection->remove($this->entity1);
        $collection->remove($this->entity2);
        $collection->add($this->entity2);
        $collection->add($this->entity3);

        $added = $collection->getAddedEntities();
        $removed = $collection->getRemovedEntities();
        $this->assertCount(1, $added, "Check the added entity array only has one entry");
        $this->assertCount(1, $removed, "Check the removed entity array has no entries");
        $this->assertSame($this->entity3, array_pop($added));
        $this->assertSame($this->entity1, array_pop($removed));
    }

}
