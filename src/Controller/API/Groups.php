<?php

namespace App\Controller\API;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\API\Exception\ValidatorException;
use App\API\Exception\NotFoundException;

class Groups extends API
{
    /**
     * @Route("/api/groups", methods={"GET"}, name="get_groups")
     */
    public function getGroups(\App\API\Groups $groups)
    {
        return $this->json($groups->getGroups());
    }

    /**
     * @Route("/api/groups", methods={"POST"}, name="create_group")
     */
    public function createGroup(\App\API\Groups $groups, Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $status = Response::HTTP_OK;

        try {
            $result = $groups->createGroup($data);
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
     * @Route("/api/groups/{id}", methods={"PUT"}, name="update_group", requirements={"id"="\d+"})
     */
    public function updateGroup(\App\API\Groups $groups, $id, Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $status = Response::HTTP_OK;
        $result = [];

        try {
            $groups->updateGroup($id, $data);
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
