<?php

namespace App\DataFixtures;

use App\Entity\Group;
use Doctrine\Common\Persistence\ObjectManager;

class GroupFixtures extends AbstractFixture
{
    const NUMBER_OF_GROUPS = 3;

    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(Group::class, self::NUMBER_OF_GROUPS, function ($group, $index) {
            $group->setName($this->faker->lexify('Group ?????'));
        });

        $manager->flush();
    }
}
