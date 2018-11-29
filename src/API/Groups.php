<?php

namespace App\API;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\API\Exception\ValidatorException;
use App\API\Exception\NotFoundException;
use App\Entity\Group;
use App\Entity\User;

class Groups
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    public function __construct(ObjectManager $manager, ValidatorInterface $validator)
    {
        $this->manager = $manager;
        $this->validator = $validator;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    public function getGroups()
    {
        return $this->manager->getRepository(\App\Entity\Group::class)->findAll();
    }

    public function createGroup($data)
    {
        $group = new Group();
        $group->setName($this->propertyAccessor->getValue($data, '[name]'));
        $this->validate($group);

        return $this->save($group);
    }

    public function updateGroup($id, $data)
    {
        $group = $this->manager->getRepository(\App\Entity\Group::class)->findOneBy(['id' => $id]);
        if (null === $group) {
            throw new NotFoundException('Group not found.');
        }

        $group->setName($this->propertyAccessor->getValue($data, '[name]') ?? $group->getName());
        $this->validate($group);

        return $this->save($group, $data);
    }

    protected function validate(Group $group)
    {
        $errors = $this->validator->validate($group);
        if (count($errors) > 0) {
            $violations = [];
            foreach ($errors as $error) {
                $violations[] = [
                    'path' => $error->getPropertyPath(),
                    'error' => $error->getMessage(),
                ];
            }
            throw new ValidatorException('Validation error', 0, null, $violations);
        }
    }

    protected function save(Group $group, ?array $data = [])
    {
        try {
            $this->manager->getConnection()->beginTransaction();

            // Save group.
            $this->manager->persist($group);
            $this->manager->flush();

            // Update group users.
            $users = $this->propertyAccessor->getValue($data, '[users]');
            if (is_array($users)) {
                foreach ($group->getUsers() as $user) {
                    $group->removeUser($user);
                }

                $this->manager->flush();

                foreach ($users as $user_id) {
                    $user = $this->manager->getRepository(User::class)->findOneBy(['id' => $user_id]);

                    if ($user instanceof User) {
                        $group->addUser($user);
                    }
                }

                $this->manager->flush();
            }

            $this->manager->getConnection()->commit();
        } catch (\Exception $e) {
            $this->manager->getConnection()->rollBack();

            throw new \Exception('Persistence layer exception.'.$e->getMessage());
        }

        return [
            'id' => $group->getId(),
        ];
    }
}
