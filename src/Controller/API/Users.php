<?php

namespace App\Controller\API;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\API\Exception\ValidatorException;
use App\API\Exception\NotFoundException;

class Users extends API
{
    /**
     * @Route("/api/users", methods={"GET"}, name="get_users")
     */
    public function getUsers(\App\API\Users $users)
    {
        return $this->json($users->getUsers());
    }

    /**
     * @Route("/api/users", methods={"POST"}, name="create_user")
     */
    public function createUser(\App\API\Users $users, Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $status = Response::HTTP_OK;

        try {
            $result = $users->createUser($data);
        } catch (ValidatorException $e) {
            $result = $e->getViolations();
            $status = Response::HTTP_UNPROCESSABLE_ENTITY;
        } catch (\Exception $e) {
            $result = [['error' => $e->getMessage()]];
            $status = Response::HTTP_UNPROCESSABLE_ENTITY;
        } catch (\TypeError $e) {
            $result = [['error' => 'Type error.']];
            $status = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        return $this->json($result, $status);
    }

    /**
     * @Route("/api/users/{id}", methods={"GET"}, name="get_user", requirements={"id"="\d+"})
     */
    public function getUser(\App\API\Users $users, $id)
    {
        return $this->json($users->getUser($id));
    }

    /**
     * @Route("/api/users/{id}", methods={"PUT"}, name="update_user", requirements={"id"="\d+"})
     */
    public function updateUser(\App\API\Users $users, $id, Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $status = Response::HTTP_OK;
        $result = [];

        try {
            $users->updateUser($id, $data);
        } catch (NotFoundException $e) {
            $result = [['error' => $e->getMessage()]];
            $status = Response::HTTP_NOT_FOUND;
        } catch (ValidatorException $e) {
            $result = $e->getViolations();
            $status = Response::HTTP_UNPROCESSABLE_ENTITY;
        } catch (\Exception $e) {
            $result = [['error' => $e->getMessage()]];
            $status = Response::HTTP_UNPROCESSABLE_ENTITY;
        } catch (\TypeError $e) {
            $result = [['error' => 'Type error.']];
            $status = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        return $this->json($result, $status);
    }
}
