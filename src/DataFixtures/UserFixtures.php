<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Group;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class UserFixtures extends AbstractFixture implements DependentFixtureInterface
{
    const NUMBER_OF_USERS = 10;

    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(User::class, self::NUMBER_OF_USERS, function ($user, $index) {
            $gender = $this->faker->randomElement(['male', 'female']);

            $user->setEmail($this->faker->companyEmail());
            $user->setFirstName($this->faker->firstName($gender));
            $user->setLastName($this->faker->lastName($gender));
            $user->setState($this->faker->boolean(50));

            $groups = $this->getAll(Group::class);
            $groups = $this->faker->randomElements($groups, $this->faker->numberBetween(1, count($groups)));
            foreach ($groups as $group) {
                $user->addGroup($group);
            }
        });

        $manager->flush();
    }

    public function getDependencies()
    {
        return [GroupFixtures::class];
    }
}
