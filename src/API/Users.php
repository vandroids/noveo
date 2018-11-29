<?php

namespace App\API;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\API\Exception\ValidatorException;
use App\API\Exception\NotFoundException;
use App\Entity\User;
use App\Entity\Group;

class Users
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

    public function getUsers()
    {
        return $this->manager->getRepository(\App\Entity\User::class)->findAll();
    }

    public function createUser($data)
    {
        $user = new User();
        $user->setEmail($this->propertyAccessor->getValue($data, '[email]'));
        $user->setFirstname($this->propertyAccessor->getValue($data, '[firstname]'));
        $user->setLastname($this->propertyAccessor->getValue($data, '[lastname]'));
        $user->setState($this->propertyAccessor->getValue($data, '[state]'));
        $this->validate($user);

        return $this->save($user, $data);
    }

    public function getUser($id)
    {
        $user = $this->manager->getRepository(User::class)->findOneBy(['id' => $id]);

        return $user;
    }

    public function updateUser($id, $data)
    {
        $user = $this->manager->getRepository(User::class)->findOneBy(['id' => $id]);
        if (null === $user) {
            throw new NotFoundException('User not found.');
        }

        $user->setEmail($this->propertyAccessor->getValue($data, '[email]') ?? $user->getEmail());
        $user->setFirstname($this->propertyAccessor->getValue($data, '[firstname]') ?? $user->getFirstname());
        $user->setLastname($this->propertyAccessor->getValue($data, '[lastname]') ?? $user->getLastname());
        $user->setState($this->propertyAccessor->getValue($data, '[state]') ?? $user->getState());
        $this->validate($user);

        return $this->save($user, $data);
    }

    protected function validate(User $user)
    {
        $errors = $this->validator->validate($user);
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

    protected function save(User $user, ?array $data = [])
    {
        try {
            $this->manager->getConnection()->beginTransaction();

            // Save user.
            $this->manager->persist($user);
            $this->manager->flush();

            // Update user groups.
            $groups = $this->propertyAccessor->getValue($data, '[groups]');
            if (is_array($groups)) {
                foreach ($user->getGroups() as $group) {
                    $user->removeGroup($group);
                }

                $this->manager->flush();

                foreach ($groups as $group_id) {
                    $group = $this->manager->getRepository(Group::class)->findOneBy(['id' => $group_id]);

                    if ($group instanceof Group) {
                        $user->addGroup($group);
                    }
                }

                $this->manager->flush();
            }

            $this->manager->getConnection()->commit();
        } catch (\Exception $e) {
            $this->manager->getConnection()->rollBack();

            throw new \Exception('Persistence layer exception.');
        }

        return [
            'id' => $user->getId(),
        ];
    }
}
