<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

abstract class AbstractFixture extends Fixture
{
    /** @var ObjectManager */
    private $manager;

    /** @var Generator */
    protected $faker;

    abstract protected function loadData(ObjectManager $manager);

    public function load(ObjectManager $manager)
    {
        if (!property_exists($this->referenceRepository, 'myRefs')) {
            $this->referenceRepository->myRefs = [];
        }

        $this->manager = $manager;
        $this->faker = Factory::create();
        $this->loadData($manager);
    }

    protected function createMany(string $className, int $count, callable $factory, ?string $namespace = null)
    {
        $entityname = $this->makeEntityName($className, $namespace);

        if (!array_key_exists($entityname, $this->referenceRepository->myRefs)) {
            $this->referenceRepository->myRefs[$entityname] = 0;
        }

        $from = $this->referenceRepository->myRefs[$entityname];
        for ($i = $from; $i < $from + $count; ++$i) {
            $entity = new $className();
            $factory($entity, $i);
            $this->manager->persist($entity);

            $name = $this->makeEntityName($className, $namespace, $i);
            $this->addReference($name, $entity);

            ++$this->referenceRepository->myRefs[$entityname];
        }
    }

    protected function getMany(string $className, int $count = 0, ?string $namespace = null): array
    {
        $result = [];

        $entityname = $this->makeEntityName($className, $namespace);
        $count = $count ? $count : $this->referenceRepository->myRefs[$entityname];

        for ($i = 0; $i < $count; ++$i) {
            $name = $this->makeEntityName($className, $namespace, $i);
            $result[] = $this->getReference($name);
        }

        return $result;
    }

    protected function getAll(string $className, ?string $namespace = null)
    {
        return $this->getMany($className, 0, $namespace);
    }

    protected function makeEntityName(string $className, ?string $namespace, int $i = 0): string
    {
        return  $className.'_'.($namespace ? $namespace.'_' : '').$i;
    }
}
